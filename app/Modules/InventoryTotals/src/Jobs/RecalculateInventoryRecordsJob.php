<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\Inventory\RecalculateInventoryRequestEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RecalculateInventoryRecordsJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            Schema::dropIfExists('inventory_movements_to_recalculate');

            DB::statement('
                CREATE TEMPORARY TABLE inventory_movements_to_recalculate AS
                SELECT id as inventory_id
                FROM inventory
                WHERE inventory.recount_required = 1
                LIMIT 10
            ');

            $inventoryRecordsIds = DB::table('inventory_movements_to_recalculate')->pluck('inventory_id');

            if ($inventoryRecordsIds->count() > 0) {
                RecalculateInventoryRequestEvent::dispatch($inventoryRecordsIds);
            }

            $recordsUpdated = DB::update('
                UPDATE inventory
                INNER JOIN inventory_movements_to_recalculate
                  ON inventory.id = inventory_movements_to_recalculate.inventory_id
                SET
                    inventory.recount_required      = 0,
                    inventory.recalculated_at       = now(),
                    inventory.updated_at            = now()
            ');

            Log::info('Job processing', [
                'job' => self::class,
                'recordsUpdated' => $recordsUpdated
            ]);

            usleep(50000); // 0.05 seconds
        } while ($recordsUpdated > 0);
    }
}
