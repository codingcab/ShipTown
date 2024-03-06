<?php

namespace Database\Seeders\Demo;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::factory(4)->create([
            'status_code' => 'store_collection',
            'shipping_method_code' => 'store_collection',
            'shipping_method_name' => 'store_collection',
            'label_template' => 'address_label',
        ]);

        $orders->each(function (Order $order) {
            Product::query()
                ->inRandomOrder()
                ->limit(2)
                ->get()
                ->each(function (Product $product) use ($order) {
                    return OrderProduct::create([
                        'order_id' => $order->getKey(),
                        'product_id' => $product->getKey(),
                        'quantity_ordered' => rand(2, 6),
                        'price' => $product->price,
                        'name_ordered' => $product->name,
                        'sku_ordered' => $product->sku,
                    ]);
                });

            $order = $order->refresh();
            $order->update(['total_paid' => $order->total_order]);
        });
    }
}
