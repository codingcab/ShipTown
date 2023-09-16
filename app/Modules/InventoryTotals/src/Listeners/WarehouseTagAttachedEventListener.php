<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Models\Configuration;

class WarehouseTagAttachedEventListener
{
    public function handle($event)
    {
        Configuration::query()->update([
            'totals_by_warehouse_tag_max_inventory_id_checked' => 0
        ]);

        EnsureTotalsByWarehouseTagRecordsExistJob::dispatch();
    }
}
