<?php

namespace App\Modules\Api2cart\src;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryMinuteEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Events\Product\ProductPriceUpdatedEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\BaseModuleServiceProvider;
use Exception;

/**
 * Class Api2cartServiceProvider.
 */
class Api2cartServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'eCommerce - Api2cart Integration';

    /**
     * @var string
     */
    public static string $module_description = 'Api2cart.com platform integration';

    /**
     * @var string
     */
    public static string $settings_link = '/admin/settings/api2cart';

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
        SyncRequestedEvent::class => [
            Listeners\SyncRequestedEventListener::class,
        ],

        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class
        ],

        EveryFiveMinutesEvent::class => [
            Listeners\EveryFiveMinutesEventListener::class
        ],

        EveryDayEvent::class => [
            Listeners\DailyEventListener::class],

        ProductPriceUpdatedEvent::class => [
            Listeners\ProductPriceUpdatedEventListener::class
        ],

        ProductTagAttachedEvent::class => [
            Listeners\ProductTagAttachedEventListener::class
        ],

        ProductTagDetachedEvent::class => [
            Listeners\ProductTagDetachedEventListener::class
        ],

        InventoryUpdatedEvent::class => [
            Listeners\InventoryUpdatedEventListener::class
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEventListener::class
        ]
    ];

    public static function enabling(): bool
    {
        DispatchImportOrdersJobs::dispatch();
        ProcessImportedOrdersJob::dispatch();

        return parent::enabling();
    }

    /**
     * @throws Exception
     */
    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }
}
