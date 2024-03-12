<?php

namespace App\Modules\InventoryQuantityReserved\src\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuantityReservedService
{
    public static function recalculateQuantityReserved(Collection $inventoryRecordsIds): void
    {
        $inventoryIds = implode(',', $inventoryRecordsIds->toArray());

        DB::affectingStatement("
            UPDATE inventory
            SET
                updated_at = NOW(),
                quantity_reserved = (SELECT IFNULL(SUM(quantity_reserved), 0) FROM inventory_reservations WHERE inventory_id = inventory.id)
            WHERE id IN ($inventoryIds)
        ");
    }
}
