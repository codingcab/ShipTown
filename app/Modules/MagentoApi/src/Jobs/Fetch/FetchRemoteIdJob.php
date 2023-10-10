<?php

namespace App\Modules\MagentoApi\src\Jobs\Fetch;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Api\Magento2Api;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Services\Magento2Integration;
use Illuminate\Support\Facades\Log;

class FetchRemoteIdJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('inventory_totals_tag_id')
            ->get()
            ->pluck('id');

        MagentoProduct::query()
            ->whereIn('connection_id', $connectionIds)
            ->whereNull('exists_in_magento')
            ->whereNull('remote_id')
            ->with(['magentoConnection', 'product', 'inventoryTotalsByWarehouseTag'])
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProduct $magentoProduct) {
                    $response = Magento2Api::api($magentoProduct->magentoConnection)
                        ->get('products/' . $magentoProduct->product->sku);

                    if ($response === null) {
                        return;
                    }

                    if ($response->notFound()) {
                        $magentoProduct->update([
                            'remote_id' => 0,
                            'exists_in_magento' => false,
                        ]);

                        return;
                    }

                    if ($response->failed()) {
                        return;
                    }

                    $magentoProduct->update([
                        'remote_id' => $response->json()['id'],
                        'exists_in_magento' => true,
                    ]);
                });

                Log::debug('Job processing', [
                    'job' => self::class,
                    'products_fetched' => $products->count(),
                ]);
            });
    }
}
