<?php

namespace App\Modules\MagentoApi\src\Jobs\Sync;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use App\Modules\MagentoApi\src\Models\MagentoProductPricesComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;

class SyncProductBasePricesJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('pricing_source_warehouse_id')
            ->get();

        MagentoProduct::query()
            ->whereIn('connection_id', $connectionIds->pluck('id'))
            ->whereNull('pricing_synced_at')
            ->with(['magentoConnection', 'product', 'inventoryTotalsByWarehouseTag'])
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProductPricesComparisonView $comparison) {
                    MagentoService::updateBasePrice(
                        $comparison->magentoConnection,
                        $comparison->sku,
                        $comparison->expected_price,
                        $comparison->magento_store_id
                    );

                    $comparison->magentoProduct->update([
                        'base_prices_fetched_at' => null,
                        'base_prices_raw_import' => null,
                        'magento_price'          => null,
                    ]);
                });
            });
    }
}
