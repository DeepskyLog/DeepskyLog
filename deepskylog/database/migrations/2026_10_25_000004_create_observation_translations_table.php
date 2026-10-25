<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The descriptions of the observations translated into the languages of
     * the site (#310), shown to guests in the language of the site and to
     * observers who turned on "translate the observations". A null
     * description means there is nothing to show: the description turned out
     * to be in that language already, or could not be translated.
     */
    public function up(): void
    {
        Schema::create('observation_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('observation_id');
            $table->string('language', 8);
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->unique(['observation_id', 'language']);
            $table->foreign('observation_id')
                ->references('id')
                ->on('observations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_translations');
    }
};
