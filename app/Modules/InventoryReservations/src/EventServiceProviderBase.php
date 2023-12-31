<?php

namespace App\Modules\InventoryReservations\src;

use App\Events\EveryDayEvent;
use App\Events\EveryHourEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\Warehouse;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\InventoryReservations\src\Jobs\RecalculateQuantityReservedJob;
use App\Modules\InventoryReservations\src\Models\Configuration;

/**
 * Class EventServiceProviderBase.
 */
class EventServiceProviderBase extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = '.CORE - Inventory Reservations';

    /**
     * @var string
     */
    public static string $module_description = 'Reserves stock for active orders.';

    /**
     * @var string
     */
    public static string $settings_link = '/admin/settings/modules/inventory-reservations';

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
        OrderProductUpdatedEvent::class => [
            Listeners\OrderProductUpdatedEventListener::class,
        ],

        OrderProductCreatedEvent::class => [
            Listeners\OrderProductCreatedEventListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEventListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],
    ];

    public static function enableModule(): bool
    {
        if (Configuration::query()->doesntExist()) {
            $warehouse = Warehouse::query()->firstOrCreate(['code' => '999'], ['name' => '999']);

            Configuration::updateOrCreate([], [
                'warehouse_id' => $warehouse->id,
            ]);
        }

        RecalculateQuantityReservedJob::dispatch();

        return parent::enableModule();
    }

    public static function disabling(): bool
    {
        return false;
    }
}
