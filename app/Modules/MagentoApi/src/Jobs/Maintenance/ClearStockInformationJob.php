<?php

namespace App\Modules\MagentoApi\src\Jobs\Maintenance;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class ClearStockInformationJob extends UniqueJob
{
    public function handle()
    {
        DB::statement("
            UPDATE modules_magento2api_products

            INNER JOIN modules_magento2api_connections
                ON modules_magento2api_connections.id = modules_magento2api_products.connection_id
                AND modules_magento2api_connections.inventory_totals_tag_id IS NULL

            SET
                modules_magento2api_products.quantity = null,
                modules_magento2api_products.stock_items_fetched_at = null,
                modules_magento2api_products.stock_items_raw_import = null

            WHERE (
                modules_magento2api_products.quantity is null
                OR modules_magento2api_products.stock_items_fetched_at is null
                OR modules_magento2api_products.stock_items_raw_import is null
            )
        ");
    }
}
