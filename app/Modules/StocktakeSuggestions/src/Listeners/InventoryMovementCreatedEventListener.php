<?php

namespace App\Modules\StocktakeSuggestions\src\Listeners;

use App\Events\InventoryMovement\InventoryMovementCreatedEvent;
use App\Models\StocktakeSuggestion;

class InventoryMovementCreatedEventListener
{
    public function handle(InventoryMovementCreatedEvent $event): void
    {
        $reason = 'negative stock - have you received in the stock correctly?';
        $points = 5;
        $inventoryMovement = $event->inventoryMovement;

        if ($inventoryMovement->quantity_after < 0 and $inventoryMovement->quantity_before >= 0) {
            StocktakeSuggestion::create([
                'inventory_id' => $inventoryMovement->inventory_id,
                'product_id' => $inventoryMovement->product_id,
                'warehouse_id' => $inventoryMovement->warehouse_id,
                'points' => $points,
                'reason' => $reason,
            ]);

            return;
        }

        if ($inventoryMovement->quantity_after >= 0 and $inventoryMovement->quantity_before < 0) {
            StocktakeSuggestion::query()->where([
                'inventory_id' => $inventoryMovement->inventory_id,
                'reason' => $reason
            ])->delete();

            return;
        }
    }
}
