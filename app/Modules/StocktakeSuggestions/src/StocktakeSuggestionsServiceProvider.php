<?php

namespace App\Modules\StocktakeSuggestions\src;

use App\Events\EveryDayEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\InventoryMovement\InventoryMovementCreatedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class StocktakeSuggestionsServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'Stocktake Suggestions AI';

    /**
     * @var string
     */
    public static string $module_description = 'This AI prevents stock issues and suggests stocktakes' .
    ' based various product factors';

    public static string $settings_link = '/settings/modules/stocktake-suggestions';

    /**
     * @var bool
     */
    public static bool $autoEnable = true;

    /**
     * @var array
     */
    protected $listen = [
        EveryTenMinutesEvent::class => [
            Listeners\EveryTenMinutesEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],

        InventoryUpdatedEvent::class => [
            Listeners\OutdatedCounts\InventoryUpdatedEvent\AddOutdatedCountSuggestionListener::class,
        ],

        InventoryMovementCreatedEvent::class => [
            Listeners\InventoryMovementCreatedEventListener::class,
        ],
    ];
}
