<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners;

use App\Modules\StocktakeSuggestions\src\Jobs\NegativeInventoryJob;

class EveryDayEventListener
{
    public function handle()
    {
        NegativeInventoryJob::dispatch();
    }
}
