<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->default(0)->comment('操作用戶ID');
            $table->decimal('revenue', 8, 2)->default(0)->comment('營業額');
            $table->decimal('cost', 8, 2)->default(0)->comment('總成本');
            $table->decimal('net_profit', 8, 2)->default(0)->comment('淨利潤');
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
        Schema::dropIfExists('settlement');
    }
}
