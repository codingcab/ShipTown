<?php

namespace Tests\Feature\Modules\StockControl;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\StockControl\src\StockControlServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncreaseQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_decreases_quantity_when_product_shipped()
    {
        /** @var Warehouse $warehouse */
        $warehouse = factory(Warehouse::class)->create();

        /** @var Product $product */
        $product = factory(Product::class)->create();

        /** @var Order $order */
        $order = factory(Order::class)->create();

        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create();

        StockControlServiceProvider::enableModule();

        $orderProductShipment = new OrderProductShipment();
        $orderProductShipment->order_id = $order->getKey();
        $orderProductShipment->quantity_shipped = $orderProduct->quantity_to_ship * (-1);
        $orderProductShipment->product()->associate($product);
        $orderProductShipment->warehouse()->associate($warehouse);
        $orderProductShipment->orderProduct()->associate($orderProduct);
        $orderProductShipment->save();

        $inventory = Inventory::whereWarehouseId($warehouse->getKey())->whereProductId($product->getKey())->first();

        $this->assertNotNull($inventory);
        $this->assertEquals($orderProduct->quantity_to_ship, $inventory->quantity);
    }
}
