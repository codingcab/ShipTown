<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src;

use App\Events\Order\OrderUpdatedEvent;
use App\Events\OrderProduct\OrderProductCreatedEvent;
use App\Events\OrderProduct\OrderProductDeletedEvent;
use App\Events\OrderProduct\OrderProductUpdatedEvent;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\Events\ConfigurationUpdatedEvent;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\BaseModuleServiceProvider;

class ActiveOrdersInventoryReservationsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Active Orders Inventory Reservations';

    public static string $module_description = 'Reserves inventory for active orders';

    public static string $settings_link = '/settings/modules/active-orders-inventory-reservations';

    public static bool $autoEnable = false;

    protected $listen = [
        OrderProductUpdatedEvent::class => [
            Listeners\OrderProductUpdatedEventListener::class,
        ],

        OrderProductCreatedEvent::class => [
            Listeners\OrderProductCreatedEventListener::class,
        ],

        OrderProductDeletedEvent::class => [
            Listeners\OrderProductDeletedEventListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEventListener::class,
        ],

        ConfigurationUpdatedEvent::class => [
            Listeners\ConfigurationUpdatedEventListener::class
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        Configuration::observe(Observers\ConfigurationObserver::class);
    }

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
