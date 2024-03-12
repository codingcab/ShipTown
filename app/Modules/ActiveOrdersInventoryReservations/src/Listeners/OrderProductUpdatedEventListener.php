<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\InventoryReservation;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class OrderProductUpdatedEventListener
{
    public function handle(OrderProductUpdatedEvent $event): void
    {
        if ($event->orderProduct->product_id === null) {
            return;
        }

        $event->orderProduct->refresh();

        InventoryReservation::query()
            ->where('custom_uuid', ReservationsService::getUuid($event->orderProduct))
            ->first()
            ->update(['quantity_reserved' => $event->orderProduct->quantity_to_ship]);
    }
}
