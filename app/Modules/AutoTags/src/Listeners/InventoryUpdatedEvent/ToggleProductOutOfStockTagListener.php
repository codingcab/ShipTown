<?php

namespace App\Modules\AutoTags\src\Listeners\InventoryUpdatedEvent;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Modules\AutoTags\src\Jobs\ToggleOutOfStockTagJob;

class ToggleProductOutOfStockTagListener
{
    /**
     * Handle the event.
     *
     * @param InventoryUpdatedEvent $event
     *
     * @return void
     */
    public function handle(InventoryUpdatedEvent $event)
    {
        ToggleOutOfStockTagJob::dispatch($event->getInventory()->product_id)
            ->delay(60);
    }
}
