<?php

namespace Database\Seeders\Demo;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class PaidOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::factory()
            ->count(1)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                Product::query()
                    ->whereIn('sku', ['45', '46'])
                    ->inRandomOrder()
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
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(1)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                Product::query()
                    ->inRandomOrder()
                    ->limit(1)
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
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(10)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
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
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(3)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                Product::query()
                    ->inRandomOrder()
                    ->limit(3)
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
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(2)
            ->create(['status_code' => 'paid'])
            ->each(function (Order $order) {
                Product::query()
                    ->inRandomOrder()
                    ->limit(4)
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
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(1)
            ->create(['status_code' => 'packing', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                Product::query()
                    ->whereIn('sku', ['45'])
                    ->inRandomOrder()
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
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });
    }
}
