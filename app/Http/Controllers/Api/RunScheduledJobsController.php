<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunScheduledJobsRequest;
use App\Jobs\SyncRequestJob;

class RunScheduledJobsController extends Controller
{
    public function store(RunScheduledJobsRequest $request)
    {
        SyncRequestJob::dispatchAfterResponse();

        $this->respondOK200('Sync requested successfully');
    }
}
