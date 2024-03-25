<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;

class EveryMinuteEventListener
{
    public function handle()
    {
        ProcessImportedOrdersJob::dispatch();
    }
}
