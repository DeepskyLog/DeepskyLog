<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Observers who saw this also saw…": per deep-sky object the objects
     * seen most often by the same observers (App\Services\AlsoSeenObjects),
     * rebuilt weekly on the queue.
     */
    public function up(): void
    {
        Schema::create('object_similarities', function (Blueprint $table) {
            $table->id();
            $table->string('object_name');
            $table->string('similar_name');
            $table->float('score');
            // the observers who saw both
            $table->unsignedInteger('observers');

            $table->index(['object_name', 'score']);
            $table->index('similar_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_similarities');
    }
};
