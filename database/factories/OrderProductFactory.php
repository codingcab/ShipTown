<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderProductFactory extends Factory
{
    protected $model = OrderProduct::class;

    public function definition(): array
    {
        // we will increase number of single line  orders
        $randomQuantityOrdered = Arr::random([1, 1, 1, 1, 2, 2, 3, 3]) * Arr::random([1, 1, 1, 1, 1, 1, 1, 1, 2, 3]);

        /** @var Product $product */
        $product = Product::factory()->create();

        return [
            'order_id'         => function () {
                return Order::factory()->create()->getKey();
            },
            'product_id'       => $product->getKey(),
            'sku_ordered'      => $product->sku,
            'name_ordered'     => $product->name,
            'quantity_ordered' => $randomQuantityOrdered,
            'price'            => $product->price,
        ];
    }
}
