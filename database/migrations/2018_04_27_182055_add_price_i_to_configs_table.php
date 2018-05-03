<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceIToConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->float('price_i')->comment('低价格');
            $table->float('price_a')->comment('高价格');
            $table->integer('num_i')->comment('低位的数量');
            $table->integer('num_a')->comment('高位的数量');
            $table->timestamp('num_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->dropColumn('price_i');
            $table->dropColumn('price_a');
            $table->dropColumn('num_a');
            $table->dropColumn('num_i');
            $table->dropColumn('num_updated_at');

        });
    }
}
