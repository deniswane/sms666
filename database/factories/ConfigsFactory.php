<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Admin\Config::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;
    return [
        'price' => 1,
        'created_at' => $date_time,
        'updated_at' => $date_time,
    ];
});
