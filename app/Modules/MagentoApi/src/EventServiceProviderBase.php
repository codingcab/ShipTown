<?php

namespace App\Modules\MagentoApi\src;

use App\Events\EveryDayEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\Modules\MagentoApi\src\Observers\MagentoConnectionObserver;

/**
 * Class EventServiceProviderBase.
 */
class EventServiceProviderBase extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'eCommerce - Magento 2.0 API';

    /**
     * @var string
     */
    public static string $module_description = 'Module provides connectivity to Magento 2.0 API';

    /**
     * @var string
     */
    public static string $settings_link = '/admin/settings/modules/magento-api';

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
        SyncRequestedEvent::class => [
            Listeners\SyncRequestedEventListener::class,
        ],

        EveryMinuteEvent::class => [
            Listeners\EveryTenMinutesEventListener::class
        ],

        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class
        ],

        ProductTagAttachedEvent::class => [
            Listeners\ProductTagAttachedEventListener::class,
        ],

        ProductTagDetachedEvent::class => [
            Listeners\ProductTagDetachedEventListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        MagentoConnection::observe(MagentoConnectionObserver::class);
    }
}
