<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCmsOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_order', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('order_name',50)->default('')->comment('订单号');
            $table->integer('order_tnum')->default(0)->comment('订单总数');
            $table->integer('order_num')->default(0)->comment('订单发送数');
            $table->integer('state')->default(0)->comment('删除-1,开启1,暂停0,结束2');
            $table->string('addtime',20)->default('')->comment('添加时间');
            $table->text('note')->comment('备注');
            $table->integer('type')->default(0)->comment('1导入指令，2添加指令  5短信群发订单');
            $table->string('spnumber',3)->comment('业务号码')->nullable();
            $table->integer('order_rnum')->default(0)->comment('返回条数(同步条数)');
            $table->integer('order_times')->default(0)->comment('返回用户时长总和');
            $table->string('coreframe',10)->default('')->comment('核心框架:展讯s 互芯c');
            $table->text('softarr')->comment('软件名集合')->nullable();
            $table->string('LateSendTime')->nullable();
            $table->string('LateReturnTime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_order');
    }
}
