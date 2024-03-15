<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Services;

use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\OrderProduct;

class ReservationsService
{
    const UUID_PREFIX = 'module_active_orders_inventory_reservations';

    public static function recalculateTotalQuantityReserved(array $inventory_ids): void
    {
        foreach ($inventory_ids as $inventory_id) {
            $newTotalQuantityReserved = InventoryReservation::query()
                ->where(['inventory_id' => $inventory_id])
                ->sum('quantity_reserved');

            Inventory::where(['id' => $inventory_id])->update(['quantity_reserved' => $newTotalQuantityReserved]);
        }
    }

    public static function getUuid(OrderProduct $orderProduct): string
    {
        return implode(';', [
            self::UUID_PREFIX,
            "order_id:". $orderProduct->order_id,
            "order_product_id:". $orderProduct->getKey()
        ]);
    }
}
