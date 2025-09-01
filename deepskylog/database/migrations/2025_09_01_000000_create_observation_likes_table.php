<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // observation can be from old observations or comet observations, store type and id
            $table->string('observation_type');
            $table->unsignedBigInteger('observation_id');
            $table->timestamps();

            $table->unique(['user_id', 'observation_type', 'observation_id'], 'unique_user_observation_like');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_likes');
    }
};
