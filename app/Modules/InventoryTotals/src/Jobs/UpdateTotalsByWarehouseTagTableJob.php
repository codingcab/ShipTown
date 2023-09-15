<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class UpdateTotalsByWarehouseTagTableJob extends UniqueJob
{
    public function handle()
    {
        DB::statement("DROP TEMPORARY TABLE IF EXISTS tempTable;");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS tempInventoryTotalsByWarehouseTag;");

        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
                SELECT
                    DISTINCT inventory_totals_by_warehouse_tag.tag_id, inventory_totals_by_warehouse_tag.product_id
                FROM inventory_totals_by_warehouse_tag

               LEFT JOIN taggables
                  ON taggables.tag_id = inventory_totals_by_warehouse_tag.tag_id
                  AND taggables.taggable_type = 'App\\\\Models\\\\Warehouse'

               INNER JOIN inventory
                    ON inventory.product_id = inventory_totals_by_warehouse_tag.product_id
                    AND inventory.warehouse_id = taggables.taggable_id
                    AND inventory.updated_at > inventory_totals_by_warehouse_tag.max_inventory_updated_at

                LIMIT 100;
        ");

        DB::statement("
            CREATE TEMPORARY TABLE tempInventoryTotalsByWarehouseTag AS
                SELECT
                     tempTable.tag_id as tag_id,
                     tempTable.product_id as product_id,
                     GREATEST(0, FLOOR(SUM(inventory.quantity))) as quantity,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_reserved))) as quantity_reserved,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_available))) as quantity_available,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_incoming))) as quantity_incoming,
                     MAX(inventory.updated_at) as max_inventory_updated_at,
                     NOW() as created_at,
                     NOW() as updated_at

                FROM tempTable

                LEFT JOIN taggables
                  ON taggables.tag_id = tempTable.tag_id
                  AND taggables.taggable_type = 'App\\\\Models\\\\Warehouse'

                LEFT JOIN inventory
                  ON inventory.product_id = tempTable.product_id
                  AND inventory.warehouse_id = taggables.taggable_id

                GROUP BY tempTable.tag_id, tempTable.product_id;
        ");

        DB::update("
            UPDATE inventory_totals_by_warehouse_tag

            INNER JOIN tempInventoryTotalsByWarehouseTag
                ON tempInventoryTotalsByWarehouseTag.tag_id = inventory_totals_by_warehouse_tag.tag_id
                AND tempInventoryTotalsByWarehouseTag.product_id = inventory_totals_by_warehouse_tag.product_id

            SET
                inventory_totals_by_warehouse_tag.quantity = tempInventoryTotalsByWarehouseTag.quantity,
                inventory_totals_by_warehouse_tag.quantity_reserved = tempInventoryTotalsByWarehouseTag.quantity_reserved,
                inventory_totals_by_warehouse_tag.quantity_available = tempInventoryTotalsByWarehouseTag.quantity_available,
                inventory_totals_by_warehouse_tag.quantity_incoming = tempInventoryTotalsByWarehouseTag.quantity_incoming,
                inventory_totals_by_warehouse_tag.max_inventory_updated_at = tempInventoryTotalsByWarehouseTag.max_inventory_updated_at,
                inventory_totals_by_warehouse_tag.updated_at = NOW();
        ");
    }
}
