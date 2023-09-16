<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsureTotalsByWarehouseTagRecordsExistJob extends UniqueJob
{
    public function handle()
    {
        $maxRounds = 100;
        $maxID = Inventory::query()->max('id');
        $batchSize = ceil($maxID / $maxRounds);

        do {
            $minID = $maxID - $batchSize;

            Log::debug('EnsureTotalsByWarehouseTagRecordsExistJob: rounds left ' . $maxRounds);

            DB::statement("DROP TEMPORARY TABLE IF EXISTS tempTable;");

            DB::statement("
                CREATE TEMPORARY TABLE tempTable AS
                SELECT
                 DISTINCT taggables.tag_id, inventory.product_id, taggables.taggable_id as warehouse_id

                FROM inventory

                INNER JOIN taggables
                  ON taggables.taggable_type = 'App\\\\Models\\\\Warehouse'
                  AND taggables.taggable_id = inventory.warehouse_id

                LEFT JOIN inventory_totals_by_warehouse_tag
                  ON inventory_totals_by_warehouse_tag.tag_id = taggables.tag_id
                  AND inventory_totals_by_warehouse_tag.product_id = inventory.product_id

                WHERE inventory.id BETWEEN ? AND ?
                AND inventory_totals_by_warehouse_tag.id is null
            ", [$minID, $maxID]);

            DB::insert("
                INSERT INTO inventory_totals_by_warehouse_tag (
                    tag_id,
                    product_id,
                    quantity,
                    quantity_reserved,
                    quantity_available,
                    quantity_incoming,
                    max_inventory_updated_at,
                    created_at,
                    updated_at
                )
                SELECT
                    tempTable.tag_id as tag_id,
                    tempTable.product_id as product_id,
                    SUM(inventory.quantity) as quantity,
                    SUM(inventory.quantity_reserved) as quantity_reserved,
                    SUM(inventory.quantity_available) as quantity_available,
                    SUM(inventory.quantity_incoming) as quantity_incoming,
                    MAX(inventory.updated_at) as max_inventory_updated_at,
                    NOW() as created_at,
                    NOW() as updated_at

                FROM tempTable
                INNER JOIN inventory
                    ON inventory.product_id = tempTable.product_id
                    AND inventory.warehouse_id = tempTable.warehouse_id

                GROUP BY tempTable.tag_id, tempTable.product_id;
            ");
            $maxRounds--;
            $maxID = $minID;
            Log::debug('EnsureTotalsByWarehouseTagRecordsExistJob: records created ' . DB::table('tempTable')->count());
            sleep(1);
        } while (DB::table('tempTable')->count() > 0 and $maxRounds > 0);
    }
}
