<?php

namespace App\Modules\InventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\Inventory;
use App\Modules\InventoryReservations\src\Jobs\UpdateInventoryQuantityReservedJob;
use App\Modules\InventoryReservations\src\Models\InventoryReservation;
use App\Modules\InventoryReservations\src\Services\ReservationsService;

class OrderProductUpdatedEventListener
{
    /**
     * Handle the event.
     *
     * @param OrderProductUpdatedEvent $event
     *
     */
    public function handle(OrderProductUpdatedEvent $event)
    {
        $inventoryReservationUuid = implode('_', ['order_product_id', $event->orderProduct->getKey()]);

        $inventoryReservation = InventoryReservation::where('custom_uuid', $inventoryReservationUuid)->first();
        $inventoryReservation->update([
            'quantity_reserved' => $event->orderProduct->quantity_to_ship,
        ]);

        $inventory = Inventory::where('id', $inventoryReservation->inventory_id)->first();

        ReservationsService::recalculateTotalQuantityReserved($inventory->id);

        // todo - in what circumstances would product_id be null?
        if ($event->orderProduct->product_id === null) {
            return;
        }

        UpdateInventoryQuantityReservedJob::dispatchSync($event->orderProduct->product_id);
    }

}
