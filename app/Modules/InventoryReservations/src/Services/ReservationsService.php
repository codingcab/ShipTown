<?php

namespace App\Modules\InventoryReservations\src\Services;

use App\Models\Inventory;
use App\Modules\InventoryReservations\src\Models\InventoryReservation;

class ReservationsService
{
    public static function recalculateTotalQuantityReserved(int $inventory_id): mixed
    {
        $newTotalQuantityReserved = InventoryReservation::query()
            ->where(['inventory_id' => $inventory_id])
            ->sum('quantity_reserved');

        Inventory::where(['id' => $inventory_id])->update(['quantity_reserved' => $newTotalQuantityReserved]);

        return $newTotalQuantityReserved;

//        CREATE TEMPORARY TABLE tempTable AS
//SELECT inventory_id, SUM(quantity_reserved) as quantity_reserved_total
//FROM inventory_reservations
//
//WHERE product_id IN (SELECT product_id FROM order_products WHERE order_id = 123)
//GROUP BY inventory_id;
//
//
//UPDATE inventory
//
//INNER JOIN tempTable
//  ON tempTable.inventory_id = inventory.id
//
//SET quantity_reserved = tempTable.quantity_reserved_total
    }
}
