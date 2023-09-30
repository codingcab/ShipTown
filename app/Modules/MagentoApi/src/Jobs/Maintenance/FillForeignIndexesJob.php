<?php

namespace App\Modules\MagentoApi\src\Jobs\Maintenance;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class FillForeignIndexesJob extends UniqueJob
{
    public function handle()
    {
        do {
            $recordsUpdated = $this->fillInventoryTotalsByWarehouseTagId(5000);
            usleep(100 * 1000); // 100ms
        } while ($recordsUpdated > 0);

        do {
            $recordsUpdated = $this->fillProductPricesId(5000);
            usleep(100 * 1000); // 100ms
        } while ($recordsUpdated > 0);
    }

    private function fillInventoryTotalsByWarehouseTagId(int $limit = 1000): int
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempTable');

        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
                SELECT inventory_totals_by_warehouse_tag.id as inventory_totals_by_warehouse_tag_id, modules_magento2api_products.id as modules_magento2api_products_id
                FROM modules_magento2api_products

                INNER JOIN modules_magento2api_connections
                  ON modules_magento2api_connections.id = modules_magento2api_products.connection_id
                  AND modules_magento2api_connections.inventory_totals_tag_id IS NOT NULL

                LEFT JOIN inventory_totals_by_warehouse_tag
                  ON inventory_totals_by_warehouse_tag.product_id = modules_magento2api_products.product_id
                  AND inventory_totals_by_warehouse_tag.tag_id = modules_magento2api_connections.inventory_totals_tag_id

                WHERE
                  modules_magento2api_products.inventory_totals_by_warehouse_tag_id IS NULL

                LIMIT ?;
        ", [$limit]);

        return DB::update("
            UPDATE modules_magento2api_products
            INNER JOIN tempTable
                ON tempTable.modules_magento2api_products_id = modules_magento2api_products.id
            SET
                modules_magento2api_products.inventory_totals_by_warehouse_tag_id = tempTable.inventory_totals_by_warehouse_tag_id,
                modules_magento2api_products.inventory_synced_at = null
        ");
    }

    private function fillProductPricesId(int $limit = 1000): int
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempTable');

        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
                SELECT products_prices.id as product_price_id, modules_magento2api_products.id as modules_magento2api_products_id
                FROM modules_magento2api_products

                INNER JOIN modules_magento2api_connections
                  ON modules_magento2api_connections.id = modules_magento2api_products.connection_id
                  AND modules_magento2api_connections.pricing_source_warehouse_id IS NOT NULL

                INNER JOIN products_prices
                  ON products_prices.product_id = modules_magento2api_products.product_id
                  AND products_prices.warehouse_id = modules_magento2api_connections.pricing_source_warehouse_id

                WHERE
                    modules_magento2api_products.product_price_id IS NULL

                LIMIT ?;
        ", [$limit]);

        return DB::update("
            UPDATE modules_magento2api_products
            INNER JOIN tempTable
                ON tempTable.modules_magento2api_products_id = modules_magento2api_products.id
            SET
                modules_magento2api_products.product_price_id = tempTable.product_price_id,
                modules_magento2api_products.pricing_synced_at = null
        ");
    }
}
