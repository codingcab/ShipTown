<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\Inventory;
use App\Models\StocktakeSuggestion;
use App\Modules\StocktakeSuggestions\src\Models\StocktakeSuggestionsConfiguration;

class InventoryUpdatedEventListener
{
    public function handle(InventoryUpdatedEvent $event): void
    {
        $inventory = $event->inventory;

        StocktakeSuggestion::query()->where(['inventory_id' => $inventory->id])->delete();

        $this->outdatedCount($inventory);
    }

    private function outdatedCount(Inventory $inventory): void
    {
        if ($inventory->quantity === 0.00) {
            return;
        }

        if ($inventory->first_movement_at === null) {
            return;
        }

        $configuration = StocktakeSuggestionsConfiguration::query()->first();

        if ($configuration === null) {
            return;
        }

        if ($inventory->first_movement_at->isAfter($configuration->min_count_date)) {
            return;
        }

        if ($inventory->last_counted_at === null || $inventory->last_counted_at->isBefore($configuration->min_count_date)) {
            StocktakeSuggestion::upsert([
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
                'points' => 5,
                'reason' => 'outdated count',
            ], ['inventory_id', 'reason']);
        }
    }
}
