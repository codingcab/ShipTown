<?php

namespace App\Modules\InventoryQuantityReserved\src\Listeners;

use App\Events\InventoryReservation\InventoryReservationDeletedEvent;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;

class InventoryReservationDeletedEventListener
{
    public function handle(InventoryReservationDeletedEvent $event): void
    {
        QuantityReservedService::recalculateQuantityReserved(collect([$event->inventoryReservation->inventory_id]));
    }
}
