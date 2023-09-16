<?php

namespace App\Modules\QueueMonitor\src\Dispatcher;

use Exception;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;

class DispatchWatcher extends Dispatcher
{
    public function __construct($app, $dispatcher)
    {
        parent::__construct($app, $dispatcher->queueResolver);
    }

    public function dispatchToQueue($command)
    {
        try {
            Log::debug('Job dispatched', ['job' => get_class($command)]);
        } catch (Exception $e) {
            report($e);
        }

        return parent::dispatchToQueue($command);
    }
}
