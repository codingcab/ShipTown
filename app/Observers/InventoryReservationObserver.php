<?php

namespace App\Observers;

use App\Events\InventoryReservation\InventoryReservationCreatedEvent;
use App\Events\InventoryReservation\InventoryReservationDeletedEvent;
use App\Events\InventoryReservation\InventoryReservationUpdatedEvent;

class InventoryReservationObserver
{
    public function created($model): void
    {
        InventoryReservationCreatedEvent::dispatch($model);
    }

    public function updated($model): void
    {
        InventoryReservationUpdatedEvent::dispatch($model);
    }

    public function deleted($model): void
    {
        InventoryReservationDeletedEvent::dispatch($model);
    }
}
