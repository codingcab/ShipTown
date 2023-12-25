<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Abstracts\UniqueJob;

class RunAllJobsJob extends UniqueJob
{
    public function handle(): bool
    {
        NoMovementJob::dispatch();
        BarcodeScannedToQuantityFieldJob::dispatch();

        return true;
    }
}
