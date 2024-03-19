<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        return [
            'recount_required' => true,
            'product_id' => function () {
                return Product::factory()->create();
            },
            'warehouse_id' => $warehouse->getKey(),
            'warehouse_code' => $warehouse->code,
            'quantity' => rand(1, 100),
        ];
    }
}
