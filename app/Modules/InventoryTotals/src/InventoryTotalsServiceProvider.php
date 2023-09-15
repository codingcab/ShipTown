<?php

namespace App\Modules\InventoryTotals\src;

use App\Events\EveryDayEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\Product\ProductCreatedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\InventoryTotals\src\Jobs\EnsureTotalsByWarehouseTagRecordsExistJob;
use App\Modules\InventoryTotals\src\Jobs\LastCountedAtJob;
use App\Modules\InventoryTotals\src\Jobs\UpdateTotalsByWarehouseTagTableJob;

/**
 * Class EventServiceProviderBase.
 */
class InventoryTotalsServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = '.CORE - Inventory Totals';

    /**
     * @var string
     */
    public static string $module_description = 'Tracks inventory totals for each product';

    /**
     * @var bool
     */
    public static bool $autoEnable = true;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SyncRequestedEvent::class => [
            Listeners\SyncRequestedEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\DailyEventListener::class,
        ],

        ProductCreatedEvent::class => [
            Listeners\ProductCreatedEventListener::class,
        ],

        InventoryUpdatedEvent::class => [
            Listeners\InventoryUpdatedEventListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
