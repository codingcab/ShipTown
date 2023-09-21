<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Modules\MagentoApi\src\Jobs\FetchBasePricesJob;
use App\Modules\MagentoApi\src\Jobs\FetchSpecialPricesJob;
use App\Modules\MagentoApi\src\Jobs\FetchStockItemsJob;
use App\Modules\MagentoApi\src\Jobs\SyncProductBasePricesJob;
use App\Modules\MagentoApi\src\Jobs\SyncProductInventoryJob;
use App\Modules\MagentoApi\src\Jobs\SyncProductSalePricesJob;

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
