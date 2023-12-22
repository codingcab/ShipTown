<?php

namespace App\Modules\Integrations\Magento2MSI\src;

use App\Events\EveryTenMinutesEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\BaseModuleServiceProvider;

class Magento2MsiServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Integration - Magento 2 MSI API';

    public static string $module_description = 'Module provides connectivity to Magento 2 API - Multi Source Inventory';

    public static string $settings_link = '/settings/modules/magento2msi';

    public static bool $autoEnable = false;

    protected $listen = [
        SyncRequestedEvent::class => [
            Listeners\SyncRequestedEventListener::class,
        ],

        EveryTenMinutesEvent::class => [
            Listeners\EveryTenMinutesEventListener::class
        ],

        ProductTagAttachedEvent::class => [
            Listeners\ProductTagAttachedEventListener::class,
        ],

        ProductTagDetachedEvent::class => [
            Listeners\ProductTagDetachedEventListener::class,
        ],
    ];
}
