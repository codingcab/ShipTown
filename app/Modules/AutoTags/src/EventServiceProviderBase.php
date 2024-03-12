<?php

namespace App\Modules\AutoTags\src;

use App\Events\EveryDayEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class EventServiceProviderBase extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'Automation - AutoTags';

    /**
     * @var string
     */
    public static string $module_description = 'Automatically manages Out Of Stock & Oversold tags';

    /**
     * @var bool
     */
    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EveryDayEvent::class => [
            Listeners\DailyEvent\RunDailyMaintenanceJobsListener::class,
        ],

        InventoryUpdatedEvent::class => [
            Listeners\InventoryUpdatedEvent\ToggleProductOutOfStockTagListener::class,
            Listeners\InventoryUpdatedEvent\ToggleProductOversoldTagListener::class,
        ],

        OrderCreatedEvent::class => [
            Listeners\OrderCreatedEvent\ToggleOrderOutOfStockTagListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEvent\ToggleOrderOutOfStockTagListener::class,
        ],
    ];
}
