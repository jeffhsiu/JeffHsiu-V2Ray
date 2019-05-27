<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('provider')->default(1)->comment('VPS提供商');
            $table->string('ip', 20)->default('');
            $table->integer('account_id')->default(1)->comment('VPS商家帳號');
            $table->integer('ssh_port')->default(22)->comment('SSH連線端口');
            $table->string('ssh_pwd')->default('')->comment('SSH連線密碼');
            $table->timestamp('end_date')->nullable()->comment('服務器租用到期日');
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
        Schema::dropIfExists('server');
    }
}
