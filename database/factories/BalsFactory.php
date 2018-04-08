<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Admin\Bal::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;
    return [
        'phone_id' => $faker->randomNumber(),
        'user_id' => $faker->randomNumber(),
        'num' => $faker->randomNumber(), // secret
        'created_at' => $date_time,
        'updated_at' => $date_time,
    ];
});
