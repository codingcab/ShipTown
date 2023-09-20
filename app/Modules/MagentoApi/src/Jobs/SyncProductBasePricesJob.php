<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Models\MagentoProductPricesComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;

class SyncProductBasePricesJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()->where(['is_enabled' => true])->get()->pluck('id');

        MagentoProductPricesComparisonView::query()
            ->whereIn('magento_connection_id', $connectionIds)
            ->whereNotNull('base_prices_fetched_at')
            ->whereRaw('IFNULL(magento_price, 0) != expected_price')
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
            }, 'modules_magento2api_products_id');
    }
}
