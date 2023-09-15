<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Events\Product\ProductCreatedEvent;
use App\Models\InventoryTotal;
use App\Models\Taggable;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;

class ProductCreatedEventListener
{
    public function handle(ProductCreatedEvent $event)
    {
        InventoryTotal::query()->create([
            'product_id' => $event->product->getKey(),
            'quantity' => 0,
            'quantity_reserved' => 0,
            'quantity_incoming' => 0,
        ]);

        $records = Taggable::query()
            ->where(['taggable_type' => 'App\Models\Warehouse'])
            ->get()
            ->map(function (Taggable $tag) use ($event) {
                return [
                    'tag_id' => $tag->tag_id,
                    'product_id' => $event->product->getKey(),
                    'created_at' => now(),
                ];
            })
            ->toArray();

        InventoryTotalByWarehouseTag::query()->insertOrIgnore($records);
    }
}
