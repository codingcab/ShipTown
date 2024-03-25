<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;

class EveryFiveMinutesEventListener
{
    public function handle()
    {
        DispatchImportOrdersJobs::dispatch();
    }
}
