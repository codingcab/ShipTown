<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;

class JobProcessedListener
{
    public function handle(JobProcessed $event)
    {
        Log::debug('Job processed', ['job' => data_get($event->job->payload(), 'displayName')]);
    }
}
