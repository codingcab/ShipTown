<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use Illuminate\Support\Facades\Log;

class JobProcessingListener
{
    public function handle($event)
    {
        Log::debug('Joh started', ['job' => $event->job->getName()]);
    }
}
