<?php

namespace App\Modules\InventoryQuantityReserved\src\Listeners;

use Illuminate\Support\Facades\DB;

class RecalculateInventoryRequestEventListener
{
    public function handle($event): void
    {
        $inventoryIds = implode(',', $event->inventoryRecordsIds->toArray());

        $sql = "
            UPDATE inventory
            SET
                updated_at = NOW(),
                quantity_reserved = (SELECT IFNULL(SUM(quantity_reserved), 0) FROM inventory_reservations WHERE inventory_id = inventory.id)
            WHERE id IN ($inventoryIds)
        ";

        DB::statement($sql);
    }
}
