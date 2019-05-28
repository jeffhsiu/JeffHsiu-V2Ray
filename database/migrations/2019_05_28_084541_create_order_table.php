<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('customer_id')->default(0)->comment('用戶ID');
            $table->integer('distributor_id')->default(0)->comment('經銷商ID');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('type')->default(0);
            $table->timestamp('start_date')->nullable(true)->comment('開始日期');
            $table->timestamp('end_date')->nullable(true)->comment('到期日期');
            $table->integer('server_id')->default(0)->comment('伺服器ID');
            $table->string('docker_name', 20)->default('');
            $table->decimal('price', 8, 2)->default(0)->comment('售價 (人民幣)');
            $table->decimal('commission', 8, 2)->default(0)->comment('經銷商抽成 (人民幣)');
            $table->decimal('profit', 8, 2)->default(0)->comment('利潤 (人民幣)');
            $table->string('remark')->default('')->comment('備註');
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
        Schema::dropIfExists('order');
    }
}
