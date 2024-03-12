<?php

namespace App\Modules\InventoryQuantityReserved\src;

use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Events\InventoryReservation\InventoryReservationCreatedEvent;
use App\Modules\BaseModuleServiceProvider;

class InventoryQuantityReservedServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Inventory Reservations Quantity Reserved';

    public static string $module_description = 'Keeps track of total quantity reserved';

    public static string $settings_link = '';

    public static bool $autoEnable = true;

    protected $listen = [
        InventoryReservationCreatedEvent::class => [
            Listeners\InventoryReservationCreatedEventListener::class,
        ],

        RecalculateInventoryRequestEvent::class => [
            Listeners\RecalculateInventoryRequestEventListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    public static function disabling(): bool
    {
        return false;
    }
}
