<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjectpartofTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objectpartof', function (Blueprint $table) {
            $table->engine = 'MyISAM';
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
        Schema::dropIfExists('objectpartof');
    }
}
