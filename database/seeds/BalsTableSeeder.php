<?php

use Illuminate\Database\Seeder;

class BalsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bal = factory(App\Models\Admin\Bal::class)->times(100)->make();
        App\Models\Admin\Bal::insert($bal->toArray());
    }
}
