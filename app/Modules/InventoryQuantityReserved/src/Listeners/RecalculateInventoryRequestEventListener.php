<?php

namespace App\Modules\InventoryQuantityReserved\src\Listeners;

use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;

class RecalculateInventoryRequestEventListener
{
    public function handle(RecalculateInventoryRequestEvent $event): void
    {
        QuantityReservedService::recalculateQuantityReserved($event->inventoryRecordsIds);
    }
}
