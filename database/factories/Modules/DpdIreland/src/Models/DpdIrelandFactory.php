<?php

namespace Database\Factories\Modules\DpdIreland\src\Models;

use App\Modules\DpdIreland\src\Models\DpdIreland;
use Illuminate\Database\Eloquent\Factories\Factory;

class DpdIrelandFactory extends Factory
{
    protected $model = DpdIreland::class;

    public function definition(): array
    {
        return [
            'live'              => false,
            'token'             => $this->faker->randomNumber(9),
            'user'              => $this->faker->randomNumber(6),
            'password'          => $this->faker->randomNumber(6),
            'contact'           => 'John Smith',
            'contact_telephone' => '12345678901',
            'contact_email'     => 'john.smith@dpd.ie',
            'business_name'     => 'JS Business',
            'address_line_1'    => 'DPD Ireland, Westmeath',
            'address_line_2'    => 'Unit 2B Midland Gateway Bus',
            'address_line_3'    => 'Kilbeggan',
            'address_line_4'    => 'Westmeath',
            'country_code'      => 'IE',
        ];
    }
}
