<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJinyActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jiny_actions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('enable')->default(1);

            ## uri
            $table->string('uri');

            $table->string('controller')->nullable();

            ## db table
            $table->string('table')->nullable();
            $table->integer('paging')->default(10);
            $table->string('where')->nullable();

            ## view resource
            $table->string('view_main')->nullable();
            $table->string('view_title')->nullable();
            $table->string('view_filter')->nullable();
            $table->string('view_list')->nullable();
            $table->string('view_edit')->nullable();
            $table->string('view_form')->nullable();

            $table->string('description')->nullable();
            // 작업자ID
            $table->unsignedBigInteger('user_id')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jiny_actions');
    }
}
