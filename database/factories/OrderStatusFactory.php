<?php

/** @var Factory $factory */

use App\Models\OrderStatus;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(OrderStatus::class, function (Faker $faker) {
    $availableStatuses = [
        'open',
        'closed',
        'pending',
        'ready',
        'on_hold',
        'authorized',
        'paid',
        'fulfilled',
        'overdue',
        'refunded',
        'unpaid',
        'voided',
        'in_transit',
        'canceled',
        'completed',
        'awaiting',
        'cannot_fulfill',
        'packing_dublin',
        'packing_galway',
        'packing_warehouse',
        'ready_for_collection',
        'collection_dublin',
        'collection_galway',
    ];

    $status = $faker->randomElement($availableStatuses);

    while (OrderStatus::query()->where(['code' => $status])->exists()) {
        $status = $faker->randomElement($availableStatuses);
    }

    return [
        'code'           => $status,
        'name'           => $status,
        'order_active'   => $faker->boolean,
        'order_on_hold'  => $faker->boolean,
    ];
});
