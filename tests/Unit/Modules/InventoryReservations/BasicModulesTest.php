<?php

namespace Tests\Unit\Modules\InventoryReservations;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\Models\Configuration;
use Tests\TestCase;

class BasicModulesTest extends TestCase
{
    /** @test */
    public function basicTest()
    {
        // prepare database
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $inventory = Inventory::where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])->first();

        // doing something
        $order = Order::factory()->create();

        $orderProduct1 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct2 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct2->quantity_shipped = 1;
        $orderProduct2->save();


        // assert something
        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct2->sku_ordered,
            'quantity_reserved' => $orderProduct1->quantity_to_ship,
            'comment' => 'Order #' . $order->order_number, // Order #1
        ]);

        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct2->sku_ordered,
            'quantity_reserved' => $orderProduct2->quantity_to_ship,
            'comment' => 'Order #' . $order->order_number, // Order #1
        ]);

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => $orderProduct1->quantity_to_ship + $orderProduct2->quantity_to_ship,
        ]);
    }
}
