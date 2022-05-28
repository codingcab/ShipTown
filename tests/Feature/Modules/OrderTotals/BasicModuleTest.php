<?php

namespace Tests\Feature\Modules\OrderTotals;

use App\Models\OrderProduct;
use App\Modules\OrderTotals\src\OrderTotalsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_if_updates_totals()
    {
        /** @var OrderProduct $orderProduct */
        $orderProduct = factory(OrderProduct::class)->create();

        $orderProduct = $orderProduct->refresh();

        $this->assertDatabaseHas('orders_products_totals', ['order_id' => $orderProduct->order_id]);
        $this->assertDatabaseHas('orders_products_totals', ['count' => 1]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_ordered' => $orderProduct->quantity_ordered]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_split' => $orderProduct->quantity_split]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_picked' => $orderProduct->quantity_picked]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_skipped_picking' => $orderProduct->quantity_skipped_picking]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_not_picked' => $orderProduct->quantity_not_picked]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_shipped' => $orderProduct->quantity_shipped]);
    }

    /** @test */
    public function test_module_basic_functionality()
    {
        OrderTotalsServiceProvider::enableModule();

        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}
