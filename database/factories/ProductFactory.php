<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $productNameBricks = [
            'adjective' => ['Small', 'Ergonomic', 'Rustic', 'Intelligent', 'Gorgeous', 'Incredible', 'Fantastic',
                'Practical', 'Sleek', 'Awesome', 'Enormous', 'Mediocre', 'Synergistic', 'Heavy Duty', 'Lightweight'
            ],
            'material'  => ['Steel', 'Wooden', 'Concrete', 'Plastic', 'Cotton', 'Granite', 'Rubber', 'Leather', 'Silk',
                'Wool', 'Linen', 'Marble', 'Iron', 'Bronze', 'Copper', 'Aluminum', 'Paper'
            ],
            'product'   => ['Chair', 'Car', 'Computer', 'Gloves', 'Pants', 'Shirt', 'Table', 'Shoes', 'Hat', 'Plate',
                'Knife', 'Bottle', 'Coat', 'Lamp', 'Keyboard', 'Bag', 'Bench', 'Clock', 'Watch', 'Wallet'
            ],
        ];

        $randomProductName = $this->faker->randomElement($productNameBricks['adjective'])
        .' '.$this->faker->randomElement($productNameBricks['material'])
        .' '.$this->faker->randomElement($productNameBricks['product']);

        return [
            'sku'                   => (string) $this->faker->unique()->randomNumber(5),
            'name'                  => $randomProductName,
            'department'            => $this->faker->randomElement(['Electronics', 'Books', 'Movies', 'Music', 'Games', 'Home', 'Toys', 'Health', 'Beauty', 'Clothing', 'Food', 'Jewelry', 'Sports', 'Automotive', 'Industrial']),
            'category'              => $this->faker->randomElement(['TV', 'Movies', 'Music', 'Games', 'Home', 'Toys', 'Health', 'Beauty', 'Clothing', 'Food', 'Jewelry', 'Sports', 'Automotive', 'Industrial']),
            'price'                 => $this->faker->randomFloat(2, 0.01, 110),
            'sale_price'            => $this->faker->randomFloat(2, 0.01, 110),
            'sale_price_start_date' => $this->faker->dateTimeBetween('-1 year', '+5 months'),
            'sale_price_end_date'   => $this->faker->dateTimeBetween('-1 month', '+1 year'),
            'commodity_code'        => $this->faker->randomElement(['6109100010', '6110309100', '6115210000']),
        ];
    }
}

