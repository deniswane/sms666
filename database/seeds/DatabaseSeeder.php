<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * 定义好了用户模型工厂之后，便可以在生成的用户数据填充文件中使用 factory 这个辅助函数来生成一个使用假数据的用户对象。
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(PhoneNumberTableSeeder::class);
        $this->call(SmsContentTableSeeder::class);
        $this->call(BalsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(AdminsTableSeeder::class);
        Model::reguard();
    }
}
