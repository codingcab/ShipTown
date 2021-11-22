<?php

namespace App\Jobs;

use App\Models\Heartbeat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class HeartbeatJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Heartbeat::query()->updateOrCreate([
            'code' => 'heartbeat_job',
            'Error message' => 'Job heartbeat missed, please contact support'
        ], [
            'expired_at' => now()->addHour()
        ]);

        Log::info('heartbeat');
    }
}
