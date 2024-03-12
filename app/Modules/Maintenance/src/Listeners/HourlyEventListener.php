<?php

namespace App\Modules\Maintenance\src\Listeners;

use App\Modules\Maintenance\src\Jobs\EnsureProductSkusPresentInAliasesJob;

class HourlyEventListener
{
    public function handle()
    {
        EnsureProductSkusPresentInAliasesJob::dispatch();
    }
}
