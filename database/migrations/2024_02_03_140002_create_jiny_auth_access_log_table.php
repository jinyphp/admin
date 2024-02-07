<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJinyAuthAccessLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jiny_auth_access_log', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('email')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('uri')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jiny_auth_access_log');
    }
}
