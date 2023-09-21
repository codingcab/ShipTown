<?php

namespace App\Modules\QueueMonitor\src;

use App\Events\EveryHourEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\QueueMonitor\src\Dispatcher\DispatchWatcher;
use Illuminate\Bus\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

/**
 * Class Api2cartServiceProvider.
 */
class QueueMonitorServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var string
     */
    public static string $module_name = 'Queue Monitor';

    /**
     * @var string
     */
    public static string $module_description = 'Logs jobs dispatched to the queue.';

    /**
     * @var string
     */
    public static string $settings_link = '';

    /**
     * @var bool
     */
    public static bool $autoEnable = false;

    public static array $ignoredJobList = [
        'Laravel\Telescope\Jobs\ProcessPendingUpdates',
    ];

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        JobProcessing::class => [
            Listeners\JobProcessingListener::class,
        ],

        JobProcessed::class => [
            Listeners\JobProcessedListener::class,
        ],

        JobFailed::class => [
            Listeners\JobFailedListener::class,
        ],
    ];


    public static function loaded(): bool
    {
        app()->extend(Dispatcher::class, function ($dispatcher, $app) {
            return new DispatchWatcher($app, $dispatcher);
        });

        return parent::loaded();
    }
}
