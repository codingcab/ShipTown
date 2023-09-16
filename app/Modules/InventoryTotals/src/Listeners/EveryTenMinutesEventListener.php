<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\UpdateTotalsByWarehouseTagTableJob;

class EveryTenMinutesEventListener
{
    public function handle()
    {
        EnsureTotalsByWarehouseTagRecordsExistJob::dispatch();
        UpdateTotalsByWarehouseTagTableJob::dispatch();
    }
}
