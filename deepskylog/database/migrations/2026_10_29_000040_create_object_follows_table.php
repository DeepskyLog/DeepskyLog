<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The objects an observer follows, keyed like the observations
     * (target_type, target_table, target_key): their new observations go
     * into the observer's daily digest and personal RSS feed.
     */
    public function up(): void
    {
        Schema::create('object_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->string('target_table', 30);
            $table->string('target_key');
            $table->timestamps();
            $table->unique(['user_id', 'target_table', 'target_key']);
            $table->index(['target_table', 'target_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_follows');
    }
};
