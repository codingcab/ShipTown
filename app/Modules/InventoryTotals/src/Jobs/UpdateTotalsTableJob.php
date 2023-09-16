<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateTotalsTableJob extends UniqueJob
{
    public function handle()
    {
        do {
            $recordsUpdated = $this->recalculateTotals();

            Log::debug('Processing job', ['job' => self::class, 'records_updated' => $recordsUpdated]);

            usleep(100000); // 0.1 sec
        } while ($recordsUpdated > 0);
    }

    private function recalculateTotals(): int
    {
        DB::statement("DROP TEMPORARY TABLE IF EXISTS tempTable;");
        DB::statement("DROP TEMPORARY TABLE IF EXISTS tempInventoryTotals;");

        DB::statement("
            CREATE TEMPORARY TABLE tempTable AS
                SELECT
                    inventory_totals.product_id, NOW() as calculated_at
                FROM inventory_totals

                WHERE calculated_at IS NULL

                LIMIT 500;
        ");

        DB::statement("
            CREATE TEMPORARY TABLE tempInventoryTotals AS
                SELECT
                     tempTable.product_id as product_id,
                     GREATEST(0, FLOOR(SUM(inventory.quantity))) as quantity,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_reserved))) as quantity_reserved,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_available))) as quantity_available,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_incoming))) as quantity_incoming,
                     MAX(inventory.updated_at) as max_inventory_updated_at,
                     tempTable.calculated_at as calculated_at,
                     NOW() as created_at,
                     NOW() as updated_at

                FROM tempTable

                LEFT JOIN inventory
                    ON inventory.product_id = tempTable.product_id

                GROUP BY tempTable.product_id, tempTable.calculated_at;
        ");

        return DB::update("
            UPDATE inventory_totals

            INNER JOIN tempInventoryTotals
                ON tempInventoryTotals.product_id = inventory_totals.product_id

            SET
                inventory_totals.quantity = tempInventoryTotals.quantity,
                inventory_totals.quantity_reserved = tempInventoryTotals.quantity_reserved,
                inventory_totals.quantity_available = tempInventoryTotals.quantity_available,
                inventory_totals.quantity_incoming = tempInventoryTotals.quantity_incoming,
                inventory_totals.max_inventory_updated_at = tempInventoryTotals.max_inventory_updated_at,
                inventory_totals.calculated_at = tempInventoryTotals.calculated_at,
                inventory_totals.updated_at = NOW();
        ");
    }
}
