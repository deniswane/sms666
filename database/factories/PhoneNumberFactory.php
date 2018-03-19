<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\PhoneNumber::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;
    return [
        'phone' => $faker->phoneNumber,
        'country' => $faker->country,
        'amount' => $faker->numberBetween($min = 0, $max = 10000), // secret
        'created_at' => $date_time,
        'updated_at' => $date_time,
    ];
});
