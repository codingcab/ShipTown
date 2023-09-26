<?php

namespace App\Modules\MagentoApi\src\Jobs\Sync;

use App\Abstracts\UniqueJob;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
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
            ->whereIn('modules_magento2api_connection_id', $connectionIds)
            ->whereRaw('(
                special_prices_fetched_at IS NOT NULL
                AND (
                    sale_price IS NULL
                    OR sale_price_start_date IS NULL
                    OR sale_price_end_date IS NULL
                    OR sale_price != expected_sale_price
                    OR sale_price_start_date != expected_sale_price_start_date
                    OR sale_price_end_date != expected_sale_price_end_date
                )
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
                        'price'                         => null,
                        'price_start_date'              => null,
                        'price_end_date'                => null,
                    ]);
                });
            }, 'modules_magento2api_products_id');
    }
}
