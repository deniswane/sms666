<?php

use Illuminate\Database\Seeder;

class PricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bal = factory(App\Models\Admin\Prices::class)->times(1)->make();
        App\Models\Admin\Prices::insert($bal->toArray());
    }
}
