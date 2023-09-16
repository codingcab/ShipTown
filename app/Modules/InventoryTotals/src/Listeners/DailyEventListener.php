<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\FirstMovementAtJob;
use App\Modules\InventoryTotals\src\Jobs\LastCountedAtJob;

class DailyEventListener
{
    public function handle()
    {
        FirstMovementAtJob::dispatch();
        LastCountedAtJob::dispatch();
    }
}
