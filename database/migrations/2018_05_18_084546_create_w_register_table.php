<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('w_register', function (Blueprint $table) {
            $table->increments('id');
            $table->string('imsi',20)->default('')->comment('imsi');
            $table->string('phone',11)->default('')->comment('手机号');
            $table->enum('operator',[0,1])->default(0)->comment('运营商 0移动 1联通');
            $table->string('province',20)->default('')->comment('省份');
            $table->string('nickname',50)->default('')->comment('昵称');
            $table->string('password',255)->default('')->comment('密码');
            $table->string('addtime',20)->default('')->comment('添加时间');
            $table->string('verify_code',20)->default('')->comment('验证码');
            $table->string('verify_code_time',20)->default('')->comment('收到验证码的时间');
            $table->string('send_data_time',20)->default('')->comment('数据发送时间');
            $table->integer('send')->default(0);
            $table->string('state',20)->default('')->comment('注册标识  成功 succ 1,失败 failure 2,已注册 registered \n3');
            $table->string('username',255)->default('')->comment('微信id');
            $table->smallInteger('status')->unsigned()->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('w_register');
    }
}
