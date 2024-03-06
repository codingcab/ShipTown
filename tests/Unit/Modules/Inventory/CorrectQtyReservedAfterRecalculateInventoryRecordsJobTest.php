<?php

namespace Tests\Unit\Modules\Inventory;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\Models\Configuration;
use Tests\TestCase;

class CorrectQtyReservedAfterRecalculateInventoryRecordsJobTest extends TestCase
{

    /** @test */
    public function correctQtyReservedAfterRecalculateInventoryRecordsJobTest()
    {
        $warehouse = Warehouse::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $product = Product::factory()->create();

        $inventory = Inventory::where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])->first();

        $order = Order::factory()->create();

        // quantity_ordered - quantity_split - quantity_shipped = quantity_to_ship
         OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'quantity_ordered' => 10,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
         ]);

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => 10,
        ]);

        // update the quantity_reserved field in the inventory table so that
        // we know that the total is wrong and set recount_required to 1
        $inventory->quantity_reserved = 5;
        $inventory->recount_required = 1;
        $inventory->save();

        // run the job to recalculate the inventory record back to the correct total
        $job = new \App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryRecordsJob();
        $job->handle();

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => 10,
        ]);
    }
}
