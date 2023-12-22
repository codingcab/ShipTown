<?php

namespace App\Modules\Integrations\Magento2MSI\src\Listeners;

use App\Modules\Integrations\Magento2MSI\src\Jobs\SyncProductInventoryJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\EnsureProductRecordsExistJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\FetchStockItemsJob;

class EveryTenMinutesEventListener
{
    public function handle()
    {
        EnsureProductRecordsExistJob::dispatch();

//        FetchOrdersJob::dispatch();
        FetchStockItemsJob::dispatch();
//        FetchBasePricesJob::dispatch();
//        FetchSpecialPricesJob::dispatch();

        SyncProductInventoryJob::dispatch();
//        SyncProductBasePricesJob::dispatch();
//        SyncProductSalePricesJob::dispatch();
    }
}
