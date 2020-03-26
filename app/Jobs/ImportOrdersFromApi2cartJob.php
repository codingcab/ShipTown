<?php

namespace App\Jobs;

use App\Managers\CompanyConfigurationManager;
use App\Models\Order;
use App\Modules\Api2cart\src\Orders;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;

class ImportOrdersFromApi2cartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var bool
     */
    public $finishedSuccessfully;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        $this->finishedSuccessfully = false;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        // initialize variables
        $params = [
            'params' => 'force_all',
            'sort_by' => 'modified_at',
            'sort_direction' => 'asc',
            'count' => 999,
            'modified_from' => '2020-01-01 00:00:00',
        ];

        $api2cart_store_key = CompanyConfigurationManager::getBridgeApiKey();
        $orderToImportCollection = [];

        // pull orders
        $webOrdersCollection = Orders::getOrdersCollection($api2cart_store_key, $params);

        // transforms orders
        foreach ($webOrdersCollection['order'] as $order) {
            $orderToImportCollection[] = [
                'order_number' => $order['order_id'],
                'original_json' => $order,
                'products' => Arr::has($order, 'order_products')
                    ? $this->convertProducts($order['order_products'])
                    : [],
            ];
        }

        // save orders
        foreach ($orderToImportCollection as $order) {
            Order::query()->updateOrCreate(
                [
                    "order_number" => $order['order_number'],
                ],
                array_merge(
                    $order,
                    ['order_as_json' => $order]
                )
            );
        }

        // finalize
        $this->finishedSuccessfully = true;
    }

    public function convertProducts(array $products) {

        $result = [];

        foreach ($products as $product) {
            $result[] = [
                'sku' => $product['model'],
                'price' => $product['price'],
                'quantity' => $product['quantity']
            ];
        }

        return $result;
    }
}
