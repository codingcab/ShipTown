<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryWeekEvent;
use App\Models\Heartbeat;

class DispatchEveryWeekEventJob extends UniqueJob
{
    public function handle()
    {
        EveryWeekEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Weekly jobs heartbeat missed',
            'expires_at' => now()->addWeek(),
            'auto_heal_job_class' => self::class,
        ]);
    }
}
