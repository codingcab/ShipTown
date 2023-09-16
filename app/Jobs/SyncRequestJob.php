<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\SyncRequestedEvent;

class SyncRequestJob extends UniqueJob
{
    public function handle()
    {
        SyncRequestedEvent::dispatch();
    }
}
