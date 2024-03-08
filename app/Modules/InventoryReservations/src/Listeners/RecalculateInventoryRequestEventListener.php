<?php

namespace App\Modules\InventoryReservations\src\Listeners;

use Illuminate\Support\Facades\DB;

class RecalculateInventoryRequestEventListener
{
    public function handle($event)
    {
        $inventoryIds = implode(',', $event->inventoryRecordsIds->toArray());

        $sql = "
            UPDATE inventory
            SET
                recalculated_at = NOW(),
                updated_at = NOW(),
                quantity_reserved = (SELECT IFNULL(SUM(quantity_reserved), 0) FROM inventory_reservations WHERE inventory_id = inventory.id)
            WHERE id IN ($inventoryIds)
        ";

        DB::statement($sql);
    }
}
