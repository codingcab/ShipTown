<?php

namespace App\Modules\InventoryReservations\src;

use App\Events\EveryDayEvent;
use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\Warehouse;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\InventoryReservations\src\Models\Configuration;

class EventServiceProviderBase extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Inventory Reservations';

    public static string $module_description = 'Reserves stock for active orders.';

    public static string $settings_link = '/admin/settings/modules/inventory-reservations';

    public static bool $autoEnable = false;

    protected $listen = [
        RecalculateInventoryRequestEvent::class => [
            Listeners\RecalculateInventoryRequestEventListener::class,
        ],

        OrderProductUpdatedEvent::class => [
            Listeners\OrderProductUpdatedEventListener::class,
        ],

        OrderProductCreatedEvent::class => [
            Listeners\OrderProductCreatedEventListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEventListener::class,
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

        return parent::enableModule();
    }

    public static function disabling(): bool
    {
        return false;
    }
}
