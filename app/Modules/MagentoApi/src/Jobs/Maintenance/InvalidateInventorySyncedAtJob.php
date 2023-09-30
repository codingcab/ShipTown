<?php

namespace App\Modules\MagentoApi\src\Jobs\Maintenance;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class InvalidateInventorySyncedAtJob extends UniqueJob
{
    public function handle()
    {
        do {
            $recordsUpdated = $this->invalidateInventorySyncedAt(5000);
            usleep(100 * 1000); // 100ms
        } while ($recordsUpdated > 0);
    }

    private function invalidateInventorySyncedAt(int $limit = 1000): int
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempTable');

        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
                SELECT
                    modules_magento2api_products.id as modules_magento2api_products_id
                FROM modules_magento2api_products

                INNER JOIN inventory_totals_by_warehouse_tag
                    ON inventory_totals_by_warehouse_tag.id = modules_magento2api_products.inventory_totals_by_warehouse_tag_id

                WHERE modules_magento2api_products.inventory_synced_at IS NOT NULL
                    AND modules_magento2api_products.stock_items_fetched_at IS NOT NULL
                    AND inventory_totals_by_warehouse_tag.quantity_available != modules_magento2api_products.quantity

                LIMIT ?;
        ", [$limit]);

        return DB::update("
            UPDATE modules_magento2api_products

            INNER JOIN tempTable
                ON tempTable.modules_magento2api_products_id = modules_magento2api_products.id

            SET
                modules_magento2api_products.inventory_synced_at = NULL
        ");
    }
}
