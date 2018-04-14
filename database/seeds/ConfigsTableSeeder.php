<?php

use Illuminate\Database\Seeder;

class ConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $con = factory(App\Models\Admin\Config::class)->times(1)->make();
        App\Models\Admin\Config::insert($con->toArray());
    }
}
