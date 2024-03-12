<?php

namespace App\Modules\InventoryQuantityReserved\src;

use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Modules\BaseModuleServiceProvider;

class InventoryQuantityReservedServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = '.CORE - Inventory Reservations Quantity Reserved';

    public static string $module_description = 'Keeps track of total quantity reserved';

    public static string $settings_link = '';

    public static bool $autoEnable = true;

    protected $listen = [
        RecalculateInventoryRequestEvent::class => [
            Listeners\RecalculateInventoryRequestEventListener::class,
        ],
    ];

    public static function disabling(): bool
    {
        return false;
    }
}
