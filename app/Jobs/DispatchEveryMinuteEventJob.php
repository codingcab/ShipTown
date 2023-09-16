<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryMinuteEvent;
use App\Models\Heartbeat;

class DispatchEveryMinuteEventJob extends UniqueJob
{
    public function handle()
    {
        EveryMinuteEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Every Minute heartbeat missed',
            'expires_at' => now()->addHour(),
        ]);
    }
}
