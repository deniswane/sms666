<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('zh_name')->comment('中文名称');
            $table->string('en_name')->comment('英文名称');
            $table->string('src')->comment('国旗相对地址');
            $table->string('abbreviation')->comment('简写');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flages');
    }
}
