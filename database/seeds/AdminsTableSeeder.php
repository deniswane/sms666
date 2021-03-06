<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = factory(App\Models\Admin::class)->times(5)->make();
        App\Models\Admin::insert($admin->makeVisible(['password', 'remember_token'])->toArray());
    }
}
