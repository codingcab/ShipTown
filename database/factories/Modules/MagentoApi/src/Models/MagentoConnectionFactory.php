<?php

namespace Database\Factories\Modules\MagentoApi\src\Models;

use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class MagentoConnectionFactory extends Factory
{
    protected $model = MagentoConnection::class;

    public function definition(): array
    {
        return [
            'base_url'  => $this->faker->url
        ];
    }
}
