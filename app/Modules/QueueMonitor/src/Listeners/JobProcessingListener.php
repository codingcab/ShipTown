<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use Illuminate\Support\Facades\Log;

class JobProcessingListener
{
    public function handle($event)
    {
        $jobName = data_get($event->job->payload(), 'displayName');

        if (in_array($jobName, QueueMonitorServiceProvider::$ignoredJobList)) {
            return;
        }

        Log::debug('Joh processing', ['job' => $jobName]);
    }
}
