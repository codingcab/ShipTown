<?php

namespace Database\Seeders\Demo;

use App\Models\NavigationMenu;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\IsFullyPackedCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Automation;
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
                OrderProduct::factory()->count(1)->create([
                    'sku_ordered' => '45',
                    'order_id' => $order->getKey(),
                    'quantity_ordered' => 6,
                ]);
                OrderProduct::factory()->count(1)->create([
                    'sku_ordered' => '46',
                    'order_id' => $order->getKey(),
                    'quantity_ordered' => 6,
                ]);
                $order = $order->refresh();
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(1)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                OrderProduct::factory()->count(1)->create(['order_id' => $order->getKey()]);
                $order = $order->refresh();
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(10)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                OrderProduct::factory()->count(2)->create(['order_id' => $order->getKey()]);
                $order = $order->refresh();
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(3)
            ->create(['status_code' => 'paid', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
                OrderProduct::factory()->count(3)->create(['order_id' => $order->getKey()]);
                $order = $order->refresh();
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(2)
            ->create(['status_code' => 'paid'])
            ->each(function (Order $order) {
                OrderProduct::factory()->count(4)->create(['order_id' => $order->getKey()]);
                $order = $order->refresh();
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });

        Order::factory()
            ->count(1)
            ->create(['status_code' => 'autopilot_packlist_test', 'label_template' => 'address_label'])
            ->each(function (Order $order) {
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

                $order = $order->refresh();
                Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order]);
            });
    }
}
