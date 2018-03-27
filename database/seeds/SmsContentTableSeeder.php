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

        $sms_contents = factory(App\Models\SmsContent::class)->times(100)->make()->each(function ($status) {
            $status->phone_number_id = 2;
        });

        App\Models\SmsContent::insert($sms_contents->toArray());
    }
}
