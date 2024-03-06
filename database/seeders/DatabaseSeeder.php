<?php

namespace Database\Seeders;

use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Modules\InventoryMovementsStatistics\src\InventoryMovementsStatisticsServiceProvider;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use App\Modules\ScurriAnpost\database\seeders\ScurriAnpostSeeder;
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
            Demo\NavigationSeeder::class,
            Demo\OrderStatusesSeeder::class,
            Demo\UsersSeeder::class,

            Demo\WarehousesSeeder::class,

            Demo\ProductsSeeder::class,
            Demo\ProductsTagsSeeder::class,
            ProductPriceSeeder::class,

            // Orders Seeders
            Demo\TestOrdersSeeder::class,
            Demo\PaidOrdersSeeder::class,
            Demo\CollectionOrdersSeeder::class,

            // Data Collector Seeders
            Demo\DataCollections\TransferToCorkBranchSeeder::class,
            Demo\DataCollections\TransfersFromWarehouseSeeder::class,
            Demo\DataCollections\ArchivedTransfersFromWarehouseSeeder::class,

            InventorySeeder::class,
            SalesSeeder::class,
            StocktakeSuggestionsSeeder::class,

            PrintNodeClientSeeder::class,

            DpdIrelandSeeder::class,
            DpdUKSeeder::class,
            ScurriAnpostSeeder::class,
            Modules\Slack\ConfigurationSeeder::class,
            Modules\Magento2MSI\ConnectionSeeder::class,


//            RestockingReportSeeder::class,
//            DataCollectionsSeeder::class,
//            RmsapiConnectionSeeder::class,
//            AutomationsSeeder::class,

//            ProductAliasSeeder::class,
//            ProductTagsSeeder::class,
//            SplitOrdersScenarioSeeder::class,
//            Orders_PackingWebDemoSeeder::class,
//            Orders_StorePickupDemoSeeder::class,
//            UnpaidOrdersSeeder::class,
//            ClosedOrdersSeeder::class,
//            PicksSeeder::class,
//            OrderShipmentsSeeder::class,

        ]);

        DispatchEveryMinuteEventJob::dispatch();
        DispatchEveryFiveMinutesEventJob::dispatch();
        DispatchEveryTenMinutesEventJob::dispatch();
        DispatchEveryHourEventJobs::dispatch();
        DispatchEveryDayEventJob::dispatch();
    }
}
