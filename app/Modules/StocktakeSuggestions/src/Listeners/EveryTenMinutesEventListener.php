<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners;

use App\Modules\StocktakeSuggestions\src\Jobs\RunAllJobsJob;

class EveryTenMinutesEventListener
{
    public function handle(): void
    {
        RunAllJobsJob::dispatch();
    }
}
