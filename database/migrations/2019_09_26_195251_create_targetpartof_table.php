<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetpartofTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('target_partof', function (Blueprint $table) {
            $table->string('objectname', 128);
            $table->string('partofname', 128);
            $table->index('objectname', 'Index_object');
            $table->index('partofname', 'Index_partof');
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
        Schema::dropIfExists('target_partof');
    }
}
