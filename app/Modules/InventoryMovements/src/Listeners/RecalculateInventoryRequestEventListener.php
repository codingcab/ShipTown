<?php

namespace App\Modules\InventoryMovements\src\Listeners;

use Illuminate\Support\Facades\DB;

class RecalculateInventoryRequestEventListener
{
    public function handle($event)
    {
        $inventoryIds = implode(',', $event->inventoryRecordsIds->toArray());

        $sql = "
            UPDATE inventory
            SET
                last_sequence_number = (SELECT sequence_number FROM inventory_movements WHERE inventory_id = inventory.id AND sequence_number IS NOT NULL ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1),
                quantity = IFNULL((SELECT quantity_after FROM inventory_movements WHERE inventory_id = inventory.id ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1), 0),
                last_movement_id = (SELECT id FROM inventory_movements WHERE inventory_id = inventory.id ORDER BY occurred_at DESC, sequence_number DESC LIMIT 1),
                first_movement_at = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id),
                last_movement_at = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id),
                first_counted_at = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'stocktake'),
                last_counted_at = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'stocktake'),
                in_stock_since = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_before = 0),
                first_sold_at = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'sale'),
                last_sold_at = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND type = 'sale'),
                first_received_at = (SELECT MIN(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_delta > 0),
                last_received_at = (SELECT MAX(occurred_at) FROM inventory_movements WHERE inventory_id = inventory.id AND quantity_delta > 0),
                updated_at = now()
            WHERE id IN ($inventoryIds)
        ";

        DB::statement($sql);
    }
}
