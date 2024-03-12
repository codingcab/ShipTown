<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Observers;

use App\Modules\ActiveOrdersInventoryReservations\src\Events\ConfigurationUpdatedEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;

class ConfigurationObserver
{
    public function updated(Configuration $configuration): void
    {
        ConfigurationUpdatedEvent::dispatch($configuration);
    }
}
