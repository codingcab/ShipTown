<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryFiveMinutesEvent;
use App\Models\Heartbeat;

/**
 * Class RunHourlyListener.
 */
class DispatchEveryFiveMinutesEventJob extends UniqueJob
{
    public function handle()
    {
        EveryFiveMinutesEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Every Five Minutes Event heartbeat missed',
            'expires_at' => now()->addHour()
        ]);
    }
}
