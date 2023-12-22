<?php

namespace App\Modules\Integrations\Magento2MSI\src\Listeners;

use App\Events\SyncRequestedEvent;
use App\Modules\Integrations\Magento2MSI\src\Jobs\EnsureProductRecordsExistJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\FetchBasePricesJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\FetchSpecialPricesJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\FetchStockItemsJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\SyncProductBasePricesJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\SyncProductInventoryJob;
use App\Modules\Integrations\Magento2MSI\src\Jobs\SyncProductSalePricesJob;

class SyncRequestedEventListener
{
    /**
     * Handle the event.
     *
     * @param SyncRequestedEvent $event
     *
     * @return void
     */
    public function handle(SyncRequestedEvent $event)
    {
        EnsureProductRecordsExistJob::dispatch();

        FetchStockItemsJob::dispatch();
        FetchBasePricesJob::dispatch();
        FetchSpecialPricesJob::dispatch();

        SyncProductInventoryJob::dispatch();
        SyncProductBasePricesJob::dispatch();
        SyncProductSalePricesJob::dispatch();
    }
}
