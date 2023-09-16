<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryHourEvent;
use App\Models\Heartbeat;

/**
 * Class RunHourlyListener.
 */
class DispatchEveryHourEventJobs extends UniqueJob
{
    public function handle()
    {
        EveryHourEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Every Hour Event heartbeat missed',
            'expires_at' => now()->addHours(2)
        ]);
    }
}
