<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Models\InventoryReservation;
use App\Models\Product;
use App\Modules\ActiveOrdersInventoryReservations\src\Events\ConfigurationUpdatedEvent;

class ConfigurationUpdatedEventListener
{
    public function handle(ConfigurationUpdatedEvent $event): void
    {
        InventoryReservation::query()
            ->where('custom_uuid', 'like', 'order_id_%')
            ->get()
            ->each
            ->delete();
    }
}
