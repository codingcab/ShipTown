<?php

namespace App\Modules\StocktakeSuggestions\src\Jobs;

use App\Models\Inventory;
use App\Models\StocktakeSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NegativeInventoryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public function handle(): bool
    {
        $reason = 'stock below 0';
        $points = 20;

        StocktakeSuggestion::query()->where(['reason' => $reason])->delete();

        DB::statement('
            INSERT INTO stocktake_suggestions (inventory_id, points, reason, created_at, updated_at)
            SELECT id, ?, ?, NOW(), NOW()
            FROM inventory
            WHERE quantity < 0
            ORDER BY quantity ASC
            LIMIT 100
        ', [$points, $reason]);

        return true;
    }
}
