<?php

use Faker\Generator as Faker;

$factory->define(App\Models\SmsContent::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;
    return [
        'from'      =>$faker->phoneNumber,
        'content'    => $faker->text(),
        'created_at' => $date_time,
        'updated_at' => $date_time,
    ];
});
