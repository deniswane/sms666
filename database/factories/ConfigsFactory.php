<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Admin\Config::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;
    return [
        'price' => 1,
        'price_i'=>100,
        'price_a'=>200,
        'num_a'=>200,
        'num_i'=>100,
        'created_at' => $date_time,
        'updated_at' => $date_time,
        'num_updated_at' => $date_time,
    ];
});
