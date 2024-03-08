<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Heartbeat;
use App\Modules\Api2cart\src\Api\Orders;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ImportOrdersJobs extends UniqueJob
{
    public bool $finishedSuccessfully;

    private Api2cartConnection $api2cartConnection;

    public function __construct(Api2cartConnection $api2cartConnection)
    {
        $this->api2cartConnection = $api2cartConnection;
        $this->finishedSuccessfully = false;
    }

    public function uniqueId(): string
    {
        return implode('-', [parent::uniqueId(), $this->api2cartConnection->id]);
    }

    public function handle(): void
    {
        $batchSize = 100;

        do {
            $recordsImported = $this->importOrders($this->api2cartConnection, $batchSize);

            // sleep for a second to avoid API2CART rate limits
            sleep(1);
        } while ($recordsImported > 0);

        // finalize
        $this->finishedSuccessfully = true;
    }

    private function importOrders(Api2cartConnection $api2cartConnection, int $batchSize): int
    {
        // initialize params
        $params = [
            'params'         => 'force_all',
            'created_from'   => '2022-01-01 00:00:00',
            'sort_by'        => 'modified_at',
            'sort_direction' => 'asc',
            'count'          => $batchSize,
        ];

        if ($api2cartConnection->magento_store_id) {
            $params['store_id'] = $api2cartConnection->magento_store_id;
        }

        if (isset($api2cartConnection->last_synced_modified_at)) {
            $params = Arr::add(
                $params,
                'modified_from',
                $api2cartConnection->last_synced_modified_at
            );
        }

        $orders = Orders::get($api2cartConnection->bridge_api_key, $params);

        if ($orders === null) {
            Log::warning("API2CART: Could not fetch orders");
            return 0;
        }

        info('API2CART: Imported orders', ['count' => count($orders)]);

        $this->saveOrders($api2cartConnection, $orders);

        Heartbeat::query()->updateOrCreate([
            'code' => implode('_', ['api2cart', 'ImportOrdersJob', $api2cartConnection->getKey()])
        ], [
            'error_message' => 'Web orders not fetched for last hour',
            'expires_at' => now()->addHour()
        ]);

        return count($orders);
    }

    /**
     * @param Api2cartConnection $api2cartConnection
     * @param array              $ordersCollection
     */
    private function saveOrders(Api2cartConnection $api2cartConnection, array $ordersCollection): void
    {
        foreach ($ordersCollection as $order) {
            Api2cartOrderImports::query()->updateOrCreate([
                'connection_id' => $api2cartConnection->getKey(),
                'api2cart_order_id' => data_get($order, 'id'),
                'when_processed' => null,
            ], [
                'raw_import' => $order,
            ]);

            $this->updateLastSyncedTimestamp($api2cartConnection, $order);
        }
    }

    /**
     * @param Api2cartConnection $connection
     * @param $order
     */
    private function updateLastSyncedTimestamp(Api2cartConnection $connection, $order): void
    {
        if (empty($order)) {
            return;
        }

        $lastTimeStamp = Carbon::createFromFormat(
            $order['modified_at']['format'],
            $order['modified_at']['value']
        );

        $connection->refresh();
        $connection->update([
            'last_synced_modified_at' => max($lastTimeStamp->addSecond(), $connection->last_synced_modified_at)
        ]);
    }
}
