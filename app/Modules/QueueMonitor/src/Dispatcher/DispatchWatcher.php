<?php

namespace App\Modules\QueueMonitor\src\Dispatcher;

use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Exception;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;

class DispatchWatcher extends Dispatcher
{
    public function __construct($app, $dispatcher)
    {
        parent::__construct($app, $dispatcher->queueResolver);
    }

    public function dispatchToQueue($command)
    {
        try {
            $jobName = get_class($command);

            if (! in_array($jobName, QueueMonitorServiceProvider::$ignoredJobList)) {
                Log::debug('Job dispatched', ['job' => $jobName]);
            }
        } catch (Exception $e) {
            report($e);
        }

        return parent::dispatchToQueue($command);
    }
}
