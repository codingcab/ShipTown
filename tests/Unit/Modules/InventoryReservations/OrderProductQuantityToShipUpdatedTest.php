<?php

namespace Tests\Unit\Modules\InventoryReservations;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\Models\Configuration;
use Tests\TestCase;

/** @test */
class OrderProductQuantityToShipUpdatedTest extends TestCase
{

    // this test checks that the quantity_reserved field in the inventory_reservations table is updated
    // when the quantity_to_ship field in the order_products table is updated
    /** @test */
    public function orderProductQuantityToShipUpdatedTest()
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

        $order = Order::factory()->create();

        // quantity_ordered - quantity_split - quantity_shipped = quantity_to_ship
        $orderProduct = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'quantity_ordered' => 10,
            'quantity_split' => 0,
            'quantity_shipped' => 0,
        ]);

        // quantity_to_ship is a generated field, so we update the quantity_shipped field
        $orderProduct->quantity_shipped =  1;
        $orderProduct->save();

        // assert something
        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct->sku_ordered,
            'quantity_reserved' => 9, // 10 - 1 = 9
            'comment' => 'Order #' . $order->order_number, // Order #1
        ]);

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => 9,
        ]);
    }
}
