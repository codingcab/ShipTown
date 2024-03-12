<?php

namespace App\Modules\OrderStatus\src;

use App\Events\EveryHourEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Events\OrderStatus\OrderStatusUpdatedEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\OrderStatus\src\Jobs\EnsureCorrectIsActiveAndIsOnHoldJob;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class OrderStatusServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = '.CORE - Order Status';

    /**
     * @var string
     */
    public static string $module_description = 'Manages orders Active & On Hold when status code changed';

    /**
     * @var bool
     */
    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryHourEvent::class => [
            Listeners\HourlyEventListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEventListener::class,
        ],

        OrderStatusUpdatedEvent::class => [
            Listeners\OrderStatusUpdatedEventListener::class,
        ]
    ];

    public static function enabling(): bool
    {
        EnsureCorrectIsActiveAndIsOnHoldJob::dispatch();

        return parent::enabling();
    }

    public static function disabling(): bool
    {
        return false;
    }
}
