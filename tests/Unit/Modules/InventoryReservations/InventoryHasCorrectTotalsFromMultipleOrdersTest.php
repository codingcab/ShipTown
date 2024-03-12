<?php

namespace Tests\Unit\Modules\InventoryReservations;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use Tests\TestCase;

/** @test */
class InventoryHasCorrectTotalsFromMultipleOrdersTest extends TestCase
{
    // Checks that the quantity_reserved field in the inventory table is updated to reflect the sum of
    // the quantity_to_ship for all the entries in the order_reservations table that have the same inventory_id

    /** @test */
    public function inventoryHasCorrectTotalsFromMultipleOrdersTest()
    {
        // prepare database
        $warehouse = Warehouse::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $product = Product::factory()->create();

        $inventory = Inventory::where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])->first();

        $order1 = Order::factory()->create();
        OrderProduct::factory()->create([
            'order_id' => $order1->getKey(),
            'product_id' => $product->getKey(),
            'quantity_ordered' => 10,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
        ]);

        $order2 = Order::factory()->create();
        OrderProduct::factory()->create([
            'order_id' => $order2->getKey(),
            'product_id' => $product->getKey(),
            'quantity_ordered' => 10,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
        ]);

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => 20,
        ]);
    }
}
