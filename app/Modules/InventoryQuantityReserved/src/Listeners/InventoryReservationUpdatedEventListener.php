<?php

namespace App\Modules\InventoryQuantityReserved\src\Listeners;

use App\Events\InventoryReservation\InventoryReservationUpdatedEvent;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;

class InventoryReservationUpdatedEventListener
{
    public function handle(InventoryReservationUpdatedEvent $event): void
    {
         QuantityReservedService::recalculateQuantityReserved(collect([$event->inventoryReservation->inventory_id]));
    }
}
