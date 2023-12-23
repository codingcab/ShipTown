<?php

namespace App\Modules\Magento2MSI\src;

use App\Events\EveryTenMinutesEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\BaseModuleServiceProvider;

class Magento2MsiServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'eCommerce - Magento 2 MSI';

    public static string $module_description = 'Module provides connectivity to Magento 2 API - Multi Source Inventory';

    public static string $settings_link = '/settings/modules/magento2msi';

    public static bool $autoEnable = false;

    protected $listen = [
        SyncRequestedEvent::class => [
            \App\Modules\Magento2MSI\src\Listeners\SyncRequestedEventListener::class,
        ],

        EveryTenMinutesEvent::class => [
            \App\Modules\Magento2MSI\src\Listeners\EveryTenMinutesEventListener::class
        ],

        ProductTagAttachedEvent::class => [
            \App\Modules\Magento2MSI\src\Listeners\ProductTagAttachedEventListener::class,
        ],

        ProductTagDetachedEvent::class => [
            \App\Modules\Magento2MSI\src\Listeners\ProductTagDetachedEventListener::class,
        ],
    ];
}
