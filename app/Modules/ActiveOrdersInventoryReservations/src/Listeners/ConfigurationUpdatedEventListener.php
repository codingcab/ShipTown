<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Listeners;

use App\Models\InventoryReservation;
use App\Modules\ActiveOrdersInventoryReservations\src\Events\ConfigurationUpdatedEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;

class ConfigurationUpdatedEventListener
{
    public function handle(ConfigurationUpdatedEvent $event): void
    {
        InventoryReservation::query()
            ->where('custom_uuid', 'like', ReservationsService::UUID_PREFIX . '%')
            ->get()
            ->each
            ->delete();
    }
}
