<?php

namespace App\Modules\QueueMonitor\src\Listeners;

use Illuminate\Support\Facades\Log;

class JobProcessedListener
{
    public function handle($event)
    {
        Log::debug('JobProcessedListener', ['event' => $event]);
    }
}
