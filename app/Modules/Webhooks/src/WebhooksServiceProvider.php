<?php

namespace App\Modules\Webhooks\src;

use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderUpdatedEvent;
use App\Events\Product\ProductCreatedEvent;
use App\Events\Product\ProductUpdatedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\Webhooks\src\Jobs\PublishOrdersWebhooksJob;
use App\Modules\Webhooks\src\Listeners\SyncRequestedEvent\PublishProductsWebhooksListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class WebhooksServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SyncRequestedEvent::class => [
            PublishOrdersWebhooksJob::class,
            PublishProductsWebhooksListener::class,
        ],

        ProductCreatedEvent::class => [
            Listeners\ProductCreatedEvent\AttachAwaitingPublishTagListener::class,
        ],

        ProductUpdatedEvent::class => [
            Listeners\ProductUpdatedEvent\AttachAwaitingPublishTagListener::class,
        ],

        OrderCreatedEvent::class => [
            Listeners\OrderCreatedEvent\AttachAwaitingPublishTagListener::class,
        ],

        OrderUpdatedEvent::class => [
            Listeners\OrderUpdatedEvent\AttachAwaitingPublishTagListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/webhooks.php' => config_path('webhooks.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/webhooks.php', 'webhooks');
    }
}
