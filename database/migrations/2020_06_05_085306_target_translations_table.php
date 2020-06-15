<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TargetTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        Schema::create('target_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('locale')->index();

            // Foreign key to the main model
            $table->foreignId('target_id');
            $table->unique(['target_id', 'locale']);
            $table->unique(['target_name', 'locale']);
            $table->foreign('target_id')->references('target_id')->on('targets')
                ->onDelete('cascade');

            // Actual fields you want to translate
            $table->string('target_name', 128);
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        Schema::dropIfExists('target_translations');
    }
}
