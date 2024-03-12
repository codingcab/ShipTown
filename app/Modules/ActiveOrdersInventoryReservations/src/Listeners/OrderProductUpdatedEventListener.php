<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class OrderProductUpdatedEventListener
{
    public function handle(OrderProductUpdatedEvent $event): void
    {
        if ($event->orderProduct->product_id === null) {
            return;
        }

        $uuid = ReservationsService::generateOrderProductUuid($event->orderProduct->order->getKey(), $event->orderProduct->getKey());
        $inventoryReservation = InventoryReservation::where('custom_uuid', $uuid)->first();
        $inventoryReservation->update([
            'quantity_reserved' => $event->orderProduct->quantity_to_ship,
        ]);
        $inventory = Inventory::where('id', $inventoryReservation->inventory_id)->first();
        ReservationsService::recalculateTotalQuantityReserved([$inventory->id]);
    }
}
