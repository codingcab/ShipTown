<?php

namespace App\Modules\ScurriAnpost\database\seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderComment;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Modules\ScurriAnpost\src\ScurriServiceProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScurriAnpostSeeder extends Seeder
{
    public function run()
    {
        if (empty(env('SCURRI_BASE_URI')) or empty(env('SCURRI_USERNAME')) or empty(env('SCURRI_PASSWORD'))) {
            return;
        }

        ScurriServiceProvider::enableModule();

        OrderStatus::factory()->create([
            'name' => 'test_orders_courier_anpost_ireland',
            'code' => 'test_orders_courier_anpost_ireland',
            'order_active' => true,
            'order_on_hold' => true,
        ]);

        $this->createTestOrders();
        $this->create_order_with_too_long_address();
    }

    protected function createTestOrders(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'status_code' => 'test_orders_courier_anpost_ireland',
            'label_template' => 'anpost_3day'
        ]);

        /** @var Product $product */
        $product = Product::findBySku('45');

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'quantity_ordered' => 1,
            'price' => $product->price,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
        ]);

        $order->refresh();

        $order->update(['total_paid' => $order->total_order]);
    }

    private function create_order_with_too_long_address()
    {
        $orderAddress = OrderAddress::factory()->create([
            'address1' => 'This address is too long, over 50 characters, and some couriers might not accept it',
            'address2' => 'Test address',
            'city' => 'Dublin',
            'postcode' => 'D02EY47',
            'country_code' => 'IE',
            'country_name' => 'Ireland',
        ]);

        $order = Order::factory()->create([
            'status_code' => 'packing',
            'label_template' => 'anpost_3day',
            'shipping_address_id' => $orderAddress->getKey(),
            'order_placed_at' => now()->subDays(3)
        ]);

        OrderComment::create([
            'order_id' => $order->getKey(),
            'comment' => 'Test with incorrect address (too long)'
        ]);

        /** @var Product $product */
        $product = Product::findBySku('45');

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'quantity_ordered' => 1,
            'price' => $product->price,
            'name_ordered' => $product->name,
            'sku_ordered' => $product->sku,
        ]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => DB::raw('total_order')]);
    }
}
