<?php

namespace App\Modules\MagentoApi\src\Jobs\Maintenance;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class ClearPricingInformationJob extends UniqueJob
{
    public function handle()
    {
        DB::statement("
            UPDATE modules_magento2api_products

            INNER JOIN modules_magento2api_connections
                ON modules_magento2api_connections.id = modules_magento2api_products.connection_id
                AND modules_magento2api_connections.pricing_source_warehouse_id IS NULL

            SET
                modules_magento2api_products.magento_price = null,
                modules_magento2api_products.magento_sale_price = null,
                modules_magento2api_products.magento_sale_price_start_date = null,
                modules_magento2api_products.magento_sale_price_end_date = null,
                modules_magento2api_products.base_prices_fetched_at = null,
                modules_magento2api_products.base_prices_raw_import = null,
                modules_magento2api_products.special_prices_fetched_at = null,
                modules_magento2api_products.special_prices_raw_import = null

            WHERE (
                modules_magento2api_products.magento_price is null
                OR modules_magento2api_products.magento_sale_price is null
                OR modules_magento2api_products.magento_sale_price_start_date is null
                OR modules_magento2api_products.magento_sale_price_end_date is null
                OR modules_magento2api_products.base_prices_fetched_at is null
                OR modules_magento2api_products.base_prices_raw_import is null
                OR modules_magento2api_products.special_prices_fetched_at is null
                OR modules_magento2api_products.special_prices_raw_import is null
            )
        ");
    }
}
