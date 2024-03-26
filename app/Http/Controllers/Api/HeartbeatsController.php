<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeartbeatResources;
use App\Models\Heartbeat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HeartbeatsController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $expiredHeartbeats = Heartbeat::expired()->get();
        $heartbeats = $expiredHeartbeats->take(2);

        foreach ($expiredHeartbeats as $expiredHeartbeat) {
            if (is_null($expiredHeartbeat->auto_heal_job_class)) {
                continue;
            }
            $job = new $expiredHeartbeat->auto_heal_job_class();
            $job->handle();
        }

        return HeartbeatResources::collection($heartbeats);
    }
}
