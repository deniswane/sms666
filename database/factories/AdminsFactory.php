<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Admin::class, function (Faker $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'password' => $password ?: $password = bcrypt('123456'),
        'email' =>  $faker->email,
        'remember_token' => str_random(10),
    ];
});
