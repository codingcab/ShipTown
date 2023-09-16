<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\UpdateTotalsByWarehouseTagTableJob;

class EveryTenMinutesEventListener
{
    public function handle()
    {
        EnsureTotalsRecordsExistJob::dispatch();

        EnsureTotalsByWarehouseTagRecordsExistJob::dispatch();
        UpdateTotalsByWarehouseTagTableJob::dispatch();
    }
}
