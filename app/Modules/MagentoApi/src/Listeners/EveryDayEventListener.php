<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Modules\MagentoApi\src\Jobs\ClearPricingInformationJob;
use App\Modules\MagentoApi\src\Jobs\ClearStockInformationJob;

class EveryDayEventListener
{
    public function handle()
    {
        ClearPricingInformationJob::dispatch();
        ClearStockInformationJob::dispatch();
    }
}
