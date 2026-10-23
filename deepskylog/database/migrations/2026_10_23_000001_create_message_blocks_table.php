<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The observers a user blocked: they can't send the user messages. Both
     * sides go with their account.
     */
    public function up(): void
    {
        Schema::create('message_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'blocked_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_blocks');
    }
};
