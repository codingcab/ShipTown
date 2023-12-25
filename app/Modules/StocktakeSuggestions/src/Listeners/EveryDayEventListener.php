<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners;

use App\Modules\StocktakeSuggestions\src\Jobs\NegativeInventoryJob;
use App\Modules\StocktakeSuggestions\src\Jobs\OutdatedCountsJob;

class EveryDayEventListener
{
    public function handle()
    {
        NegativeInventoryJob::dispatch();
        OutdatedCountsJob::dispatch();
    }
}
