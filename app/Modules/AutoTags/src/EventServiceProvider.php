<?php

namespace App\Modules\AutoTags\src;

use App\Events\DailyEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Modules\ModuleServiceProvider;

/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class EventServiceProvider extends ModuleServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        DailyEvent::class => [
            Listeners\DailyEvent\RunDailyMaintenanceJobsListener::class,
        ],

        InventoryUpdatedEvent::class => [
            Listeners\InventoryUpdatedEvent\ToggleProductOutOfStockTagListener::class,
            Listeners\InventoryUpdatedEvent\ToggleProductOversoldTagListener::class,
        ],

        OrderCreatedEvent::class => [
            Listeners\OrderCreatedEvent\ToggleOrderOutOfStockTagListener::class
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEvent\ToggleOrderOutOfStockTagListener::class
        ],
    ];
}
