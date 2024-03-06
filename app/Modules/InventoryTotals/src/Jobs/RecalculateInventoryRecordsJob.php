<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\RecalculateInventoryRequestEvent;
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

            $recordsUpdated = DB::update('
                UPDATE inventory

                INNER JOIN inventory_movements_to_recalculate
                  ON inventory.id = inventory_movements_to_recalculate.inventory_id

                SET
                    inventory.recount_required      = 0,
                    inventory.recalculated_at       = now(),
                    inventory.last_sequence_number  = (SELECT sequence_number FROM inventory_movements WHERE inventory_id = inventory.id AND sequence_number IS NOT NULL ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1),
                    inventory.quantity              = IFNULL((SELECT quantity_after FROM inventory_movements WHERE inventory_id = inventory.id ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1), 0),
                    inventory.last_movement_id      = (SELECT id FROM inventory_movements WHERE inventory_id = inventory.id ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1),
                    inventory.first_movement_at     = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id),
                    inventory.last_movement_at      = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id),
                    inventory.first_counted_at      = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = "stocktake"),
                    inventory.last_counted_at       = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = "stocktake"),
                    inventory.in_stock_since        = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_before = 0),
                    inventory.first_sold_at         = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = "sale"),
                    inventory.last_sold_at          = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = "sale"),
                    inventory.first_received_at     = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_delta > 0),
                    inventory.last_received_at      = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_delta > 0),
                    inventory.updated_at            = now(),
                    inventory.quantity_reserved     = (SELECT IFNULL(SUM(quantity_reserved), 0) FROM inventory_reservations WHERE inventory_id = inventory.id)
            ');

            $inventoryRecordsIds = DB::table('inventory_movements_to_recalculate')->pluck('inventory_id');

            if ($inventoryRecordsIds->count() > 0) {
                RecalculateInventoryRequestEvent::dispatch($inventoryRecordsIds);
            }

            Log::info('Job processing', [
                'job' => self::class,
                'recordsUpdated' => $recordsUpdated
            ]);

            usleep(50000); // 0.05 seconds
        } while ($recordsUpdated > 0);
    }
}
