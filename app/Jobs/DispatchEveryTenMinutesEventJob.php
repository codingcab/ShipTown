<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryTenMinutesEvent;
use App\Models\Heartbeat;

/**
 * Class RunHourlyListener.
 */
class DispatchEveryTenMinutesEventJob extends UniqueJob
{
    public function handle()
    {
        EveryTenMinutesEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Every Ten Minutes Event heartbeat missed',
            'expires_at' => now()->addHour()
        ]);
    }
}
