<?php

namespace Database\Seeders\Demo;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderComment;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->create_test_paid_order();

        $this->create_test_unpaid_order();

        $this->create_order_with_sku_not_in_our_database();

        $this->create_test_order_for_packing();

        $this->create_order_with_incorrect_address();
    }

    protected function create_order_with_sku_not_in_our_database(): void
    {
        $order = Order::query()->create(['order_number' => 'T100001', 'order_placed_at' => now()->subDays(3)]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Product with SKU ordered does not exist in the system. This simulates scenario when product exists in remote system (Magento, Shopify etc) but no in our system.'
        ]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Test order'
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'sku_ordered' => '123123123123123',
            'quantity_ordered' => 1,
            'product_id' => null,
        ]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('total_order')]);
    }

    protected function create_test_order_for_packing(): void
    {
        $product1 = Product::query()->firstOrCreate(['sku' => '45'], ['name' => 'Test Product - 45']);
        $product2 = Product::query()->firstOrCreate(['sku' => '44'], ['name' => 'Test Product - 44']);

        $order = Order::query()->create(['order_number' => 'T100002 - Packsheet', 'status_code' => 'paid', 'order_placed_at' => now()->subDays(3)]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'quantity_ordered' => 1,
            'product_id' => $product1->getKey(),
            'sku_ordered' => $product1->sku,
            'name_ordered' => $product1->name,
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'quantity_ordered' => 2,
            'product_id' => $product2->getKey(),
            'sku_ordered' => $product2->sku,
            'name_ordered' => $product2->name,
        ]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('total_order')]);
    }

    protected function create_test_unpaid_order(): void
    {
        $product1 = Product::query()->firstOrCreate(['sku' => '45'], ['name' => 'Test Product - 45']);
        $product2 = Product::query()->firstOrCreate(['sku' => '44'], ['name' => 'Test Product - 44']);

        $order = Order::factory()->create(['order_number' => 'T100002 - Unpaid order', 'order_placed_at' => now()->subDays(3), 'total_paid' => 0]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'quantity_ordered' => 1,
            'product_id' => $product1->getKey(),
            'sku_ordered' => $product1->sku,
            'name_ordered' => $product1->name,
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'quantity_ordered' => 2,
            'product_id' => $product2->getKey(),
            'sku_ordered' => $product2->sku,
            'name_ordered' => $product2->name,
        ]);
    }

    private function create_order_with_incorrect_address()
    {
        $orderAddress = OrderAddress::factory()->create([
            'address1' => 'This address is too long, over 50 characters, and some couriers might not accept it',
            'address2' => 'Test address',
            'city' => 'Dublin',
            'postcode' => 'D02EY47',
            'country_code' => 'IE',
            'country_name' => 'Ireland',
        ]);

        $order = Order::query()->create([
            'shipping_address_id' => $orderAddress->getKey(),
            'order_number' => 'T100003 - Incorrect address',
            'order_placed_at' => now()->subDays(3)
        ]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Test with incorrect address (too long)'
        ]);

        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('total_order')]);
    }

    private function create_test_paid_order(): void
    {
        $product1 = Product::query()->firstOrCreate(['sku' => '45'], ['name' => 'Test Product - 45']);
        $product2 = Product::query()->firstOrCreate(['sku' => '44'], ['name' => 'Test Product - 44']);

        $order = Order::factory()->create(['status_code' => 'paid', 'order_placed_at' => now()->subDays(3)]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'quantity_ordered' => 12,
            'product_id' => $product1->getKey(),
            'sku_ordered' => $product1->sku,
            'name_ordered' => $product1->name,
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'quantity_ordered' => 2,
            'product_id' => $product2->getKey(),
            'sku_ordered' => $product2->sku,
            'name_ordered' => $product2->name,
        ]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('total_order')]);
    }
}
