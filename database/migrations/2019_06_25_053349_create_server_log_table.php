<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->default(0)->comment('操作用戶ID');
            $table->bigInteger('server_id')->default(0);
            $table->bigInteger('order_id')->default(0);
            $table->string('ip', 20)->default('');
            $table->string('docker_name', 20)->default('');
            $table->tinyInteger('action')->default(0)->comment('操作類型');
            $table->string('reason')->default('')->comment('操作原因');
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
        Schema::dropIfExists('server_log');
    }
}
