<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSetInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_infos', function (Blueprint $table) {
            $table->unsignedInteger('set_id');
            $table->unsignedInteger('set_info_id');
            $table->string('set_info_type');
            $table->unique(['set_id', 'set_info_id', 'set_info_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('set_info');
    }
}
