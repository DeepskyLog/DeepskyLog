<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetNamesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('target_names', function (Blueprint $table) {
            $table->foreignId('target_id');
            $table->string('catalog', 128)->nullable();
            $table->string('catindex', 128)->nullable();
            $table->string('altname', 128);
            $table->index(['catalog', 'catindex'], 'Index_catalog');
            $table->index('altname', 'Index_altname');
            $table->timestamps();

            $table->foreign('target_id')
                ->references('target_id')->on('targets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('target_names');
    }
}
