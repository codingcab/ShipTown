<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Events\Inventory\InventoryUpdatedEvent;
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
        $inventory = $event->inventory;

        $quantityDelta = $inventory->quantity - $inventory->getOriginal('quantity');
        $quantityReservedDelta = $inventory->quantity_reserved - $inventory->getOriginal('quantity_reserved');
        $quantityAvailableDelta = $inventory->quantity_available - $inventory->getOriginal('quantity_available');
        $quantityIncomingDelta = $inventory->quantity_incoming - $inventory->getOriginal('quantity_incoming');

        Product::query()
            ->where(['id' => $event->inventory->product_id])
            ->update([
                'quantity' => DB::raw('quantity + ' . $quantityDelta),
                'quantity_reserved' => DB::raw('quantity_reserved + ' . $quantityReservedDelta),
                'updated_at' => now(),
            ]);

        InventoryTotal::query()
            ->where('product_id', $inventory->product_id)
            ->update([
                'quantity' => DB::raw('quantity + ' . $quantityDelta),
                'quantity_reserved' => DB::raw('quantity_reserved + ' . $quantityReservedDelta),
                'quantity_available' => DB::raw('quantity_available + ' . $quantityAvailableDelta),
                'quantity_incoming' => DB::raw('quantity_incoming + ' . $quantityIncomingDelta),
                'max_inventory_updated_at' => $inventory->updated_at,
                'updated_at' => now(),
            ]);

        $tags = Taggable::query()
            ->where([
                'taggable_type' => Warehouse::class,
                'taggable_id' => $inventory->warehouse_id,
            ])
            ->get();

        InventoryTotalByWarehouseTag::query()
            ->where('product_id', $inventory->product_id)
            ->whereIn('tag_id', $tags->pluck('tag_id'))
            ->update([
                'quantity' => DB::raw('quantity + ' . $quantityDelta),
                'quantity_reserved' => DB::raw('quantity_reserved + ' . $quantityReservedDelta),
                'quantity_available' => DB::raw('quantity_available + ' . $quantityAvailableDelta),
                'quantity_incoming' => DB::raw('quantity_incoming + ' . $quantityIncomingDelta),
                'max_inventory_updated_at' => $inventory->updated_at,
                'updated_at' => now(),
            ]);
    }
}
