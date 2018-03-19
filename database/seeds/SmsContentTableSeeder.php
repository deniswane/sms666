<?php

use Illuminate\Database\Seeder;

class SmsContentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phone = ['1','2','3','4','5','6','7','8','9','10'];
        $faker = app(Faker\Generator::class);

        $sms_contents = factory(App\Models\SmsContent::class)->times(100)->make()->each(function ($status) use ($faker, $phone) {
            $status->phone_number_id = $faker->randomElement($phone);
        });

        App\Models\SmsContent::insert($sms_contents->toArray());
    }
}
