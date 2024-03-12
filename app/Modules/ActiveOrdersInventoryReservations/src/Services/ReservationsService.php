<?php

namespace App\Modules\InventoryReservations\src\Services;

use App\Models\Inventory;
use App\Models\InventoryReservation;

class ReservationsService
{
    public static function recalculateTotalQuantityReserved(array $inventory_ids): void
    {
        foreach ($inventory_ids as $inventory_id) {
            $newTotalQuantityReserved = InventoryReservation::query()
                ->where(['inventory_id' => $inventory_id])
                ->sum('quantity_reserved');

            Inventory::where(['id' => $inventory_id])->update(['quantity_reserved' => $newTotalQuantityReserved]);
        }
    }

    public static function generateOrderProductUuid(int $order_id, int $order_product_id): string
    {
        return "order_id_{$order_id};order_product_id_{$order_product_id}";
    }

    public static function getProductIdFromUuid(string $uuid): int
    {
        $productString = explode(';', $uuid)[1];
        $parts = explode('_', $productString);
        return end($parts);
    }
}
