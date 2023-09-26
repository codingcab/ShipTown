<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Modules\MagentoApi\src\Jobs\Maintenance\ClearPricingInformationJob;
use App\Modules\MagentoApi\src\Jobs\Maintenance\ClearStockInformationJob;
use App\Modules\MagentoApi\src\Jobs\Maintenance\EnsureProductRecordsExistJob;

class EveryDayEventListener
{
    public function handle()
    {
        EnsureProductRecordsExistJob::dispatch();
        ClearPricingInformationJob::dispatch();
        ClearStockInformationJob::dispatch();
    }
}
