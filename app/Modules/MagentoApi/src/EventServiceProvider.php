<?php

namespace App\Modules\MagentoApi\src;

use App\Events\HourlyEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Modules\ModuleServiceProvider;

/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class EventServiceProvider extends ModuleServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        HourlyEvent::class => [
            Listeners\HourlyEvent\SyncProductsListener::class
        ],

        ProductTagAttachedEvent::class => [
            Listeners\ProductTagAttachedEvent\SyncWhenOutOfStockAttachedListener::class,
        ],

        ProductTagDetachedEvent::class => [
            Listeners\ProductTagDetachedEvent\SyncWhenOutOfStockDetachedListener::class,
        ],
    ];
}
