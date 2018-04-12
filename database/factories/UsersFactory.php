<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;
    # 用户token使用email+随机数 md5生成
    $email = $faker->safeEmail;
    $salt = $faker->numberBetween();
    static $password;

    return [
        'name' => $faker->name,
        'email' => $email,
        'is_admin' => false,
        'password' => $password ?: $password = bcrypt('321321'),
        'remember_token' => str_random(10),
        'created_at' => $date_time,
        'updated_at' => $date_time,
        'balance' => $faker->numberBetween($min = 10, $max = 341),
        'token' => md5($email.$salt),
    ];
});

