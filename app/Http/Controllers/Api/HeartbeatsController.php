<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeartbeatResources;
use App\Models\Heartbeat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HeartbeatsController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $expiredHeartbeats = Heartbeat::expired()->limit(2)->get();

        if ($expiredHeartbeats->isNotEmpty()) {
            Heartbeat::expired()
                ->whereNotNull('auto_heal_job_class')
                ->each(function (Heartbeat $heartbeat) {
                    (new $heartbeat->auto_heal_job_class)->dispatch($heartbeat);
                });
        }

        return HeartbeatResources::collection($expiredHeartbeats);
    }
}
