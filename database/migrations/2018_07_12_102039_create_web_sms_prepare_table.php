<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebSmsPrepareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_sms_prepare', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('projectid');
            $table->string('imsi');
            $table->string('phone',15);
            $table->tinyInteger('operator')->default*=(0);
            $table->string('province');
            $table->string('nickname');
            $table->string('password');
            $table->string('addtime');
            $table->string('verify_code_time');
            $table->string('send_data_time');
            $table->tinyInteger('send');
            $table->string('state');
            $table->string('outip');
            $table->string('username');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_sms_prepare');
    }
}
