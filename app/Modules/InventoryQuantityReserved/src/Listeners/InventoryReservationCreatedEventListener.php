<?php

namespace App\Modules\InventoryQuantityReserved\src\Listeners;

use App\Events\InventoryReservation\InventoryReservationCreatedEvent;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;

class InventoryReservationCreatedEventListener
{
    public function handle(InventoryReservationCreatedEvent $event): void
    {
        QuantityReservedService::recalculateQuantityReserved(collect([$event->inventoryReservation->inventory_id]));
    }
}
