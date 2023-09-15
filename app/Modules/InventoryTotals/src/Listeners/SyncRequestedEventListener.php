<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\LastCountedAtJob;
use App\Modules\InventoryTotals\src\Jobs\UpdateTotalsByWarehouseTagTableJob;

class SyncRequestedEventListener
{
    public function handle()
    {
        LastCountedAtJob::dispatch();
        EnsureTotalsByWarehouseTagRecordsExistJob::dispatch();
        UpdateTotalsByWarehouseTagTableJob::dispatch();
    }
}
