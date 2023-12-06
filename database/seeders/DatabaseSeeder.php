<?php

namespace Database\Seeders;

use App\Events\SyncRequestedEvent;
use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Modules\InventoryMovementsStatistics\src\InventoryMovementsStatisticsServiceProvider;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\Maintenance\src\Jobs\CopyInventoryMovementsToNewTableJob;
use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('up');

        QueueMonitorServiceProvider::enableModule();
        InventoryMovementsStatisticsServiceProvider::enableModule();
        InventoryTotalsServiceProvider::enableModule();

        $this->call([
            Demo\ConfigurationSeeder::class,
            Demo\OrderStatusesSeeder::class,
            Demo\UsersSeeder::class,

            Demo\WarehousesSeeder::class,
            Demo\ProductsSeeder::class,

            Demo\TestOrdersSeeder::class,

            Demo\PaidOrdersSeeder::class,

            Demo\ProductsTagsSeeder::class,

            Demo\DataCollections\TransferToCorkBranchSeeder::class,
            Demo\DataCollections\TransfersFromWarehouseSeeder::class,
            Demo\DataCollections\ArchivedTransfersFromWarehouseSeeder::class,

            ProductPriceSeeder::class,

            InventorySeeder::class,
            SalesSeeder::class,
            StocktakeSuggestionsSeeder::class,

//            RestockingReportSeeder::class,
//            DataCollectionsSeeder::class,
//            RmsapiConnectionSeeder::class,
//            AutomationsSeeder::class,

            PrintNodeClientSeeder::class,
            Modules\Slack\ConfigurationSeeder::class,

//            DpdIrelandSeeder::class,
//            ProductAliasSeeder::class,
//            ProductTagsSeeder::class,
//            SplitOrdersScenarioSeeder::class,
//            Orders_PackingWebDemoSeeder::class,
//            Orders_StorePickupDemoSeeder::class,
//            UnpaidOrdersSeeder::class,
//            ClosedOrdersSeeder::class,
//            PicksSeeder::class,
//            OrderShipmentsSeeder::class,

//        Modules Seeders
//            WebhooksTestSeeder::class,
//            DpdUk\DpdUkTestOrdersSeeder::class,
        ]);

//        RunHourlyJobs::dispatchSync();
//        CopyInventoryMovementsToNewTableJob::dispatch();

//        SyncRequestedEvent::dispatch();

        DispatchEveryMinuteEventJob::dispatch();
        DispatchEveryFiveMinutesEventJob::dispatch();
        DispatchEveryTenMinutesEventJob::dispatch();
        DispatchEveryHourEventJobs::dispatch();
        DispatchEveryDayEventJob::dispatch();
    }
}
