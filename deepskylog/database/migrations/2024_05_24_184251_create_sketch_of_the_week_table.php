<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sketch_of_the_week', function (Blueprint $table) {
            $table->id();
            $table->Integer('observation_id');
            // Foreign key observation_id references observation.id
            //            $table->foreign('observation_id')->references('id')->on('observations')->onDelete('cascade');

            $table->date('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sketch_of_the_week');
    }
};
