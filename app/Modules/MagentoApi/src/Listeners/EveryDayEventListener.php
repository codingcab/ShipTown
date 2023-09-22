<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Modules\MagentoApi\src\Jobs\ClearPricingInformationJob;
use App\Modules\MagentoApi\src\Jobs\ClearStockInformationJob;
use App\Modules\MagentoApi\src\Jobs\EnsureProductRecordsExistJob;

class EveryDayEventListener
{
    public function handle()
    {
        EnsureProductRecordsExistJob::dispatch();
        ClearPricingInformationJob::dispatch();
        ClearStockInformationJob::dispatch();
    }
}
