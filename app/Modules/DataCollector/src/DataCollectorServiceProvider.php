<?php

namespace App\Modules\DataCollector\src;

use App\Events\EveryTenMinutesEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\DataCollector\src\Jobs\DispatchCollectionsTasksJob;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class DataCollectorServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'Data Collector Services';

    /**
     * @var string
     */
    public static string $module_description = 'Provides bulk actions for data collections';

    /**
     * @var string
     */
    public static string $settings_link = '/modules/data-collector';

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
        EveryTenMinutesEvent::class => [
            Listeners\EveryTenMinutesEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        DispatchCollectionsTasksJob::dispatch();

        return parent::enabling();
    }
}
