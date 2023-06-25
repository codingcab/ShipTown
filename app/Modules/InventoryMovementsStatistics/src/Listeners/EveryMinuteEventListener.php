<?php

namespace App\Modules\InventoryMovementsStatistics\src\Listeners;

use App\Modules\InventoryMovementsStatistics\src\Jobs\EnsureCorrectLastSoldAtJob;
use App\Modules\InventoryMovementsStatistics\src\Jobs\UpdateInventoryMovementsStatisticsTableJob;

class EveryMinuteEventListener
{
    public function handle()
    {
        EnsureCorrectLastSoldAtJob::dispatch();
        UpdateInventoryMovementsStatisticsTableJob::dispatch();
    }
}
