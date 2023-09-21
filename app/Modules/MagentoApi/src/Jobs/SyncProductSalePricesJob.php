<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Models\MagentoProductInventoryComparisonView;
use App\Modules\MagentoApi\src\Models\MagentoProductPricesComparisonView;
use App\Modules\MagentoApi\src\Services\MagentoService;

class SyncProductSalePricesJob extends UniqueJob
{
    public function handle()
    {
        $connectionIds = MagentoConnection::query()
            ->where(['is_enabled' => true])
            ->whereNotNull('pricing_source_warehouse_id')
            ->get()
            ->pluck('id');

        MagentoProductPricesComparisonView::query()
            ->where(['product_id' => 406430])
            ->whereIn('modules_magento2api_connection_id', $connectionIds)
            ->whereNotNull('special_prices_fetched_at')
            ->whereRaw('(
                IFNULL(magento_sale_price, 0) != expected_sale_price
                OR magento_sale_price_start_date != expected_sale_price_start_date
                OR magento_sale_price_end_date != expected_sale_price_end_date
                OR magento_sale_price IS NULL
                OR magento_sale_price_start_date IS NULL
                OR magento_sale_price_end_date IS NULL
            )')
            ->with('magentoConnection')
            ->chunkById(10, function ($products) {
                collect($products)->each(function (MagentoProductPricesComparisonView $comparison) {
                    MagentoService::updateSalePrice(
                        $comparison->magentoConnection,
                        $comparison->sku,
                        $comparison->expected_sale_price,
                        $comparison->expected_sale_price_start_date->format('Y-m-d H:i:s'),
                        $comparison->expected_sale_price_end_date->format('Y-m-d H:i:s'),
                        $comparison->magento_store_id
                    );

                    $comparison->magentoProduct->update([
                        'special_prices_fetched_at'     => null,
                        'special_prices_raw_import'     => null,
                        'magento_sale_price'            => null,
                        'magento_sale_price_start_date' => null,
                        'magento_sale_price_end_date'   => null,
                    ]);
                });
            }, 'modules_magento2api_products_id');
    }
}
