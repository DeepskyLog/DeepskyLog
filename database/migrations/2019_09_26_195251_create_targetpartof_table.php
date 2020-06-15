<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetpartofTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create(
            'target_partof',
            function (Blueprint $table) {
                $table->foreignId('target_id', 128);
                $table->foreignId('partof_id', 128);
                $table->timestamps();
                $table->unique(['target_id', 'partof_id']);

                $table->foreign('target_id')->references('target_id')
                    ->on('target_names');
                $table->foreign('partof_id')->references('target_id')
                    ->on('target_names');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('target_partof');
    }
}
