<?php

namespace App\Modules\Webhooks\src;

use App\Events\EveryDayEvent;
use App\Events\EveryMinuteEvent;
use App\Events\Inventory\InventoryUpdatedEvent;
use App\Events\InventoryMovement\InventoryMovementCreatedEvent;
use App\Events\OrderProduct\OrderProductShipmentCreatedEvent;
use App\Events\SyncRequestedEvent;
use App\Modules\BaseModuleServiceProvider;
use Exception;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Guid\Guid;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class WebhooksServiceProviderBase extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = '.CORE - Webhooks';

    /**
     * @var string
     */
    public static string $module_description = 'Amazon SNS integration to provide webhooks';

    /**
     * @var string
     */
    public static string $settings_link = '/admin/settings/modules/webhooks/subscriptions';

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
        EveryMinuteEvent::class => [
            Listeners\DispatchEveryMinuteJobsListener::class,
        ],

        EveryDayEvent::class => [
            Listeners\RepublishLast24hWebhooksListener::class,
            Listeners\ClearOldWebhooksListener::class,
        ],

        OrderProductShipmentCreatedEvent::class => [
            Listeners\OrderProductShipmentCreatedListener::class,
        ],

        InventoryMovementCreatedEvent::class => [
            Listeners\InventoryMovementCreatedEventListener::class,
        ],

        InventoryUpdatedEvent::class => [
            Listeners\InventoryUpdatedEventListener::class,
        ]
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([__DIR__ . '/../config/webhooks.php' => config_path('webhooks.php')], 'config');
        $this->mergeConfigFrom(__DIR__ . '/../config/webhooks.php', 'webhooks');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'webhooks');
    }

    public static function enabling(): bool
    {
        if (empty(config('aws.credentials.secret'))) {
            Log::warning('AWS SNS not configured. Please set the SNS_TOPIC_PREFIX environment variable.');
            return false;
        }

        $configuration = Services\SnsService::getConfiguration();

        if ($configuration->topic_arn === null) {
            $response = Services\SnsService::client()->createTopic(['Name' => Guid::uuid4()]);

            $topicArn = $response->toArray()['TopicArn'];

            $configuration->update(['topic_arn' => $topicArn]);
        }

        try {
            Services\SnsService::client()->listSubscriptionsByTopic(['TopicArn' => $configuration->topic_arn]);
        } catch (Exception $exception) {
            ray($exception);
            report($exception);
            return false;
        }


        return true;
    }
}
