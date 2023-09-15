<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
use App\Models\Inventory;
use App\Models\InventoryTotal;
use App\Models\Product;
use App\Models\Taggable;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use Illuminate\Support\Facades\DB;

class InventoryUpdatedEventListener
{
    public function handle(InventoryUpdatedEvent $event)
    {
        $this->updateInventoryTotals($event);
        $this->updateInventoryTotalsByWarehouseTag($event);
    }

    private function updateInventoryTotals(InventoryUpdatedEvent $event): void
    {
        $totals = Inventory::query()
            ->selectRaw('
                SUM(quantity) as quantity,
                SUM(quantity_reserved) as quantity_reserved,
                SUM(quantity_incoming) as quantity_incoming
            ')
            ->where(['product_id' => $event->inventory->product_id])
            ->first();

        InventoryTotal::query()
            ->updateOrCreate([
                'product_id' => $event->inventory->product_id
            ], [
                'quantity' => $totals->quantity,
                'quantity_reserved' => $totals->quantity_reserved,
                'quantity_incoming' => $totals->quantity_incoming,
                'updated_at' => now(),
            ]);

        Product::query()
            ->where([
                'id' => $event->inventory->product_id
            ])->update([
                'quantity' => $totals->quantity,
                'quantity_reserved' => $totals->quantity_reserved,
                'updated_at' => now(),
            ]);
    }

    private function updateInventoryTotalsByWarehouseTag(InventoryUpdatedEvent $event)
    {
        $inventory = $event->inventory;

        $tags = Taggable::query()
            ->where([
                'taggable_type' => Warehouse::class,
                'taggable_id' => $inventory->warehouse_id,
            ])
            ->get();

        $quantityDelta = $inventory->quantity - $inventory->getOriginal('quantity');
        $quantityReservedDelta = $inventory->quantity_reserved - $inventory->getOriginal('quantity_reserved');
        $quantityAvailableDelta = $inventory->quantity_available - $inventory->getOriginal('quantity_available');
        $quantityIncomingDelta = $inventory->quantity_incoming - $inventory->getOriginal('quantity_incoming');

        InventoryTotalByWarehouseTag::query()
            ->where('product_id', $inventory->product_id)
            ->whereIn('tag_id', $tags->pluck('tag_id'))
            ->update([
                'quantity' => DB::raw('quantity + ' . $quantityDelta),
                'quantity_reserved' => DB::raw('quantity_reserved + ' . $quantityReservedDelta),
                'quantity_available' => DB::raw('quantity_available + ' . $quantityAvailableDelta),
                'quantity_incoming' => DB::raw('quantity_incoming + ' . $quantityIncomingDelta),
                'updated_at' => now(),
            ]);
    }
}
