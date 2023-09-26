<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Modules\MagentoApi\src\Jobs\Fetch\FetchBasePricesJob;
use App\Modules\MagentoApi\src\Jobs\Fetch\FetchSpecialPricesJob;
use App\Modules\MagentoApi\src\Jobs\Fetch\FetchStockItemsJob;
use App\Modules\MagentoApi\src\Jobs\Sync\SyncProductBasePricesJob;
use App\Modules\MagentoApi\src\Jobs\Sync\SyncProductInventoryJob;
use App\Modules\MagentoApi\src\Jobs\Sync\SyncProductSalePricesJob;

class EveryTenMinutesEventListener
{
    public function handle()
    {
        FetchStockItemsJob::dispatch();
        FetchBasePricesJob::dispatch();
        FetchSpecialPricesJob::dispatch();

        SyncProductInventoryJob::dispatch();
        SyncProductBasePricesJob::dispatch();
        SyncProductSalePricesJob::dispatch();
    }
}
