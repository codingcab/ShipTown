<?php

namespace App\Modules\Maintenance\src;

use App\Events\EveryDayEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryTenMinutesEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class EventServiceProviderBase extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = '.CORE - Maintenance';

    /**
     * @var string
     */
    public static string $module_description = 'Daily Maintenance Jobs';

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

        EveryDayEvent::class => [
            Listeners\DailyEvent\RunDailyMaintenanceJobsListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
