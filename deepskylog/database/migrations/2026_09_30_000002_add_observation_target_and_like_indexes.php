<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Indexes for queries that scanned all observations or likes.
     *
     * - observations(target_type, target_key): the ofDeepskyObject and ofComet
     *   scopes (the observations of one object, counted several times per
     *   object page and twice per star chart).
     * - observations(created_at): the RSS feeds, which show the 50 newest
     *   observations.
     * - observation_likes(observation_type, observation_id): the likes of one
     *   observation or session, and the popular observations and sessions,
     *   which group on these columns. The unique index starts with user_id, so
     *   it only serves the likes of one user.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->index(['target_type', 'target_key']);
            $table->index('created_at');
        });

        Schema::table('observation_likes', function (Blueprint $table) {
            $table->index(['observation_type', 'observation_id']);
        });
    }

    public function down(): void
    {
        Schema::table('observation_likes', function (Blueprint $table) {
            $table->dropIndex(['observation_type', 'observation_id']);
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['target_type', 'target_key']);
            $table->dropIndex(['created_at']);
        });
    }
};
