<?php

use Illuminate\Database\Seeder;

class PhoneNumberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phonen = factory(App\Models\PhoneNumber::class)->times(40)->make();
        App\Models\PhoneNumber::insert($phonen->toArray());
    }
}
