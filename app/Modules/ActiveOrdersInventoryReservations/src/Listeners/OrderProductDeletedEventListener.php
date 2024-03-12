<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Events\OrderProduct\OrderProductDeletedEvent;
use App\Models\InventoryReservation;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class OrderProductDeletedEventListener
{
    public function handle(OrderProductDeletedEvent $event): void
    {
        if ($event->orderProduct->product_id === null) {
            return;
        }

        InventoryReservation::query()
            ->where('custom_uuid', ReservationsService::getUuid($event->orderProduct))
            ->first()
            ->delete();
    }
}
