<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Events\EveryMonthEvent;
use App\Models\Heartbeat;

class DispatchEveryMonthEventJob extends UniqueJob
{
    public function handle()
    {
        EveryMonthEvent::dispatch();

        Heartbeat::query()->updateOrCreate([
            'code' => self::class,
        ], [
            'error_message' => 'Monthly event heartbeat missed',
            'expires_at' => now()->addMonth()->addDay(),
            'auto_heal_job_class' => self::class,
        ]);
    }
}
