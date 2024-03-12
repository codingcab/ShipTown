<?php

namespace App\Modules\InventoryQuantityIncoming\src;

use App\Events\DataCollection\DataCollectionDeletedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Modules\Api2cart\src\Listeners\DailyEventListener;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\InventoryQuantityIncoming\src\Jobs\FixIncorrectQuantityIncomingJob;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class InventoryQuantityIncomingServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'Inventory Quantity Incoming';

    /**
     * @var string
     */
    public static string $module_description = 'Reserves quantity incoming for open Inventory Transfers In';

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
        DataCollectionRecordCreatedEvent::class => [
            Listeners\DataCollectionRecordCreatedEventListener::class,
        ],

        DataCollectionRecordUpdatedEvent::class => [
            Listeners\DataCollectionRecordUpdatedEventListener::class,
        ],

        DataCollectionRecordDeletedEvent::class => [
            Listeners\DataCollectionRecordDeletedEventListener::class,
        ],

        DataCollectionDeletedEvent::class => [
            Listeners\DataCollectionDeletedEventListener::class,
        ],

        DailyEventListener::class => [
            Listeners\DailyEventListener::class,
        ],
    ];

    public static function enableModule(): bool
    {
        if (! parent::enableModule()) {
            return false;
        }

        FixIncorrectQuantityIncomingJob::dispatch();

        return true;
    }

    public static function disabling(): bool
    {
        return false;
    }
}
