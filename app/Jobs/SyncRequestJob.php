<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\SyncRequestedEvent;
use Illuminate\Support\Facades\Log;

class SyncRequestJob extends UniqueJob
{
    public function handle()
    {
        Log::debug('SyncRequestedEvent dispatching');

        SyncRequestedEvent::dispatch();

        Log::info('SyncRequestedEvent dispatched');
    }
}
