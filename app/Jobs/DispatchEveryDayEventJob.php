<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryDayEvent;
use App\Models\Heartbeat;

class DispatchEveryDayEventJob extends UniqueJob
{
    public function handle()
    {
        EveryDayEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Daily jobs heartbeat missed',
            'expires_at' => now()->addDay()
        ]);
    }
}
