<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\LastCountedAtJob;
use App\Modules\InventoryTotals\src\Jobs\UpdateTotalsByWarehouseTagTableJob;
use App\Modules\InventoryTotals\src\Jobs\UpdateTotalsTableJob;

class SyncRequestedEventListener
{
    public function handle()
    {
        LastCountedAtJob::dispatch();

        EnsureTotalsRecordsExistJob::dispatch();
        UpdateTotalsTableJob::dispatch();

        EnsureTotalsByWarehouseTagRecordsExistJob::dispatch();
        UpdateTotalsByWarehouseTagTableJob::dispatch();
    }
}
