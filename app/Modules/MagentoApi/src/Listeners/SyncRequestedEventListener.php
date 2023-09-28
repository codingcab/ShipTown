<?php

namespace App\Modules\MagentoApi\src\Listeners;

use App\Modules\MagentoApi\src\Jobs\Fetch\FetchBasePricesJob;
use App\Modules\MagentoApi\src\Jobs\Fetch\FetchRemoteIdJob;
use App\Modules\MagentoApi\src\Jobs\Fetch\FetchSpecialPricesJob;
use App\Modules\MagentoApi\src\Jobs\Fetch\FetchStockItemsJob;
use App\Modules\MagentoApi\src\Jobs\Maintenance\EnsureProductRecordsExistJob;
use App\Modules\MagentoApi\src\Jobs\Maintenance\FillForeignIndexesJob;
use App\Modules\MagentoApi\src\Jobs\Maintenance\InvalidatePricingSyncedAtJob;
use App\Modules\MagentoApi\src\Jobs\Sync\SyncProductInventoryJob;

class SyncRequestedEventListener
{
    public function handle()
    {
        EnsureProductRecordsExistJob::dispatch();
        FillForeignIndexesJob::dispatch();

        InvalidatePricingSyncedAtJob::dispatch();
        InvalidatePricingSyncedAtJob::dispatch();

        FetchRemoteIdJob::dispatch();
        FetchStockItemsJob::dispatch();
        FetchBasePricesJob::dispatch();
        FetchSpecialPricesJob::dispatch();

        SyncProductInventoryJob::dispatch();
    }
}
