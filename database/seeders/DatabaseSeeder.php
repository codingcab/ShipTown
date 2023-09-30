<?php

namespace Database\Seeders;

use App\Events\SyncRequestedEvent;
use App\Modules\InventoryMovementsStatistics\src\InventoryMovementsStatisticsServiceProvider;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\MagentoApi\database\seeders\MagentoSeeder;
use App\Modules\Maintenance\src\Jobs\CopyInventoryMovementsToNewTableJob;
use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        QueueMonitorServiceProvider::enableModule();
        InventoryMovementsStatisticsServiceProvider::enableModule();
        InventoryTotalsServiceProvider::enableModule();

        $this->call([
            Demo\ConfigurationSeeder::class,
            Demo\OrderStatusesSeeder::class,
            Demo\UsersSeeder::class,

            Demo\WarehousesSeeder::class,
            Demo\ProductsSeeder::class,

            Demo\PaidOrdersSeeder::class,
            Demo\TestOrdersSeeder::class,
            Demo\ProductsTagsSeeder::class,
            ProductPriceSeeder::class,

            InventorySeeder::class,
            SalesSeeder::class,
            StocktakeSuggestionsSeeder::class,

//            RestockingReportSeeder::class,
//            DataCollectionsSeeder::class,
            DataCollections\TransfersFromWarehouseSeeder::class,
            DataCollections\ArchivedTransfersFromWarehouseSeeder::class,
//            RmsapiConnectionSeeder::class,
//            AutomationsSeeder::class,

            PrintNodeClientSeeder::class,
            Modules\Slack\ConfigurationSeeder::class,
            MagentoSeeder::class,
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
        CopyInventoryMovementsToNewTableJob::dispatch();

        SyncRequestedEvent::dispatch();
    }
}
