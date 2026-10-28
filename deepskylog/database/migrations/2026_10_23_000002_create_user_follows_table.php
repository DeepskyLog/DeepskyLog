<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The observers a user follows (both sides go with their account), the
     * daily digest of their new observations, and the secret token of the
     * personal RSS feed of their observations.
     */
    public function up(): void
    {
        Schema::create('user_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('followed_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['follower_id', 'followed_id']);
            $table->index('followed_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('follow_digest')->default(false);
            $table->timestamp('follow_digest_sent_at')->nullable();
            $table->string('follow_feed_token', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['follow_feed_token']);
            $table->dropColumn(['follow_digest', 'follow_digest_sent_at', 'follow_feed_token']);
        });

        Schema::dropIfExists('user_follows');
    }
};
