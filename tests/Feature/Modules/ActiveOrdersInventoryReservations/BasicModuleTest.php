<?php

namespace Tests\Feature\Modules\ActiveOrdersInventoryReservations;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ActiveOrdersInventoryReservationsServiceProvider::enableModule();
    }

    /** @test */
    public function testIfReservesInventoryWhenOrderProductsAddedToActiveOrder()
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
        $order = Order::factory()->create(['is_active' => true]);

        $orderProduct1 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct2 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

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
    }

    /** @test */
    public function testIfNoErrorsDuringEvents()
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}
