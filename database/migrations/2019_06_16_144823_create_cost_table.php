<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->default('');
            $table->string('descript')->default('');
            $table->string('image')->default('')->comment('補充圖片');
            $table->tinyInteger('status')->default(0)->comment('狀態');
            $table->decimal('amount', 8, 2)->default(0)->comment('成本(人民幣)');
            $table->timestamp('date')->nullable()->comment('支出日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost');
    }
}
