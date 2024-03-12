<?php

namespace App\Modules\Maintenance\src\Listeners\DailyEvent;

use App\Events\EveryDayEvent;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllInventoryRecordsExistsJob;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllProductPriceRecordsExistsJob;
use App\Modules\Maintenance\src\Jobs\Products\FixQuantityAvailableJob;

class RunDailyMaintenanceJobsListener
{
    public function handle(EveryDayEvent $event): void
    {
        EnsureAllInventoryRecordsExistsJob::dispatchAfterResponse();
        EnsureAllProductPriceRecordsExistsJob::dispatchAfterResponse();
        FixQuantityAvailableJob::dispatchAfterResponse();
    }
}
