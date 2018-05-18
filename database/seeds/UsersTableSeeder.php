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
        $users = factory(User::class)->times(10)->make();
        User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(5);
        $user->name = 'Guo';
        $user->email = '641268939@qq.com';
        $user->password = bcrypt('123456');
        $user->token ='123456';
        $user->verified =1;
        $user->balance = 5120;
        $user->save();
    }
}
