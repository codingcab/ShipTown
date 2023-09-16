<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Heartbeat;

class HeartbeatJob extends UniqueJob
{
    public function handle()
    {
        Heartbeat::query()->updateOrCreate([
            'code' => 'heartbeat_job',
        ], [
            'error_message' => 'Job heartbeat missed, please contact support',
            'expired_at' => now()->addHour()
        ]);
    }
}
