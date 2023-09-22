<?php

namespace App\Modules\MagentoApi\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class UpdateInventorySyncedAt extends UniqueJob
{
    public function handle()
    {
        DB::statement("
            UPDATE modules_magento2api_products

            INNER JOIN modules_magento2api_connections
                ON modules_magento2api_connections.id = modules_magento2api_products.connection_id
                AND modules_magento2api_connections.inventory_source_warehouse_tag_id IS NOT NULL

            INNER JOIN inventory_totals_by_warehouse_tag
                ON inventory_totals_by_warehouse_tag.product_id = modules_magento2api_products.product_id
                AND inventory_totals_by_warehouse_tag.tag_id = modules_magento2api_connections.inventory_source_warehouse_tag_id

            SET inventory_synced_at = null
            WHERE stock_items_fetched_at IS NOT NULL
                AND inventory_synced_at IS NOT NULL
                AND inventory_totals_by_warehouse_tag.quantity_available != modules_magento2api_products.quantity
        ");
    }
}
