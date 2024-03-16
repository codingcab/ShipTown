<?php

namespace App\Services;

use App\Models\Module;
use App\Modules\BaseModuleServiceProvider;

class ModulesService
{
    private static array $modules = [
        // misc modules
        \App\Modules\Maintenance\src\EventServiceProviderBase::class,
        \App\Modules\SystemHeartbeats\src\SystemHeartbeatsServiceProvider::class,
        \App\Modules\StockControl\src\StockControlServiceProvider::class,
        \App\Modules\OrderTotals\src\OrderTotalsServiceProvider::class,
        \App\Modules\OrderStatus\src\OrderStatusServiceProvider::class,
        \App\Modules\FireActiveOrderCheckEvent\src\ActiveOrderCheckEventServiceProvider::class,

        \App\Modules\InventoryReservations\src\EventServiceProviderBase::class,
        \App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider::class,
        \App\Modules\Automations\src\AutomationsServiceProvider::class,
        \App\Modules\Reports\src\ReportsServiceProvider::class,

        // Automations modules
        // order MIGHT be important!
        \App\Modules\AutoPilot\src\AutoPilotServiceProvider::class,
        \App\Modules\AutoTags\src\EventServiceProviderBase::class,
        \App\Modules\OversoldProductNotification\src\OversoldProductNotificationServiceProvider::class,

        // AutoStatus modules
        // order is important!
        \App\Modules\AutoStatusPicking\src\AutoStatusPickingServiceProvider::class,

        // 3rd party integrations
        // order SHOULD not be important
        \App\Modules\Webhooks\src\WebhooksServiceProviderBase::class,
        \App\Modules\Api2cart\src\Api2cartServiceProvider::class,
        \App\Modules\Rmsapi\src\RmsapiModuleServiceProvider::class,
        \App\Modules\MagentoApi\src\EventServiceProviderBase::class,
        \App\Modules\ScurriAnpost\src\ScurriServiceProvider::class,
        \App\Modules\DpdUk\src\DpdUkServiceProvider::class,
        \App\Modules\AddressLabel\src\AddressLabelServiceProvider::class,
        \App\Modules\DpdIreland\src\DpdIrelandServiceProvider::class,
        \App\Modules\PrintNode\src\PrintNodeServiceProvider::class,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public static function updateModulesTable(): void
    {
        Module::query()->whereNotIn('service_provider_class', self::$modules)->forceDelete();

        collect(self::$modules)->each(function (BaseModuleServiceProvider|string $module_class) {
            Module::firstOrCreate([
                'service_provider_class' => $module_class
            ], [
                'enabled' => $module_class::$autoEnable,
            ]);

            if ($module_class::$autoEnable) {
                $module_class::enableModule();
            }
        });
    }
}
