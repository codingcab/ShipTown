<?php

namespace App\Modules\InventoryMovementsStatistics\src\Listeners;

use App\Events\SyncRequestedEvent;
use App\Modules\InventoryMovementsStatistics\src\Jobs\EnsureCorrectLastSoldAtJob;
use App\Modules\InventoryMovementsStatistics\src\Jobs\UpdateInventoryMovementsStatisticsTableJob;

class SyncRequestedEventListener
{
    public function handle(SyncRequestedEvent $event)
    {
        EnsureCorrectLastSoldAtJob::dispatch();
        UpdateInventoryMovementsStatisticsTableJob::dispatch();
    }
}