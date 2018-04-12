<?php

use Illuminate\Database\Seeder;
use App\Models\User;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class)->times(50)->make();
        User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(5);
        $user->name = 'Guo';
        $user->email = 'guo@example.com';
        $user->password = bcrypt('321321');
        $user->balance = 5120;
        $user->save();

    }
}
