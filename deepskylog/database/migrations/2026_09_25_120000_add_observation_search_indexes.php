<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Indexes for the observation search table (/observations/search).
     *
     * - users.username: the table joins users on observations.observer_username.
     *   Without an index MariaDB compares every observation with every user
     *   (block nested loop join). Unique, because observations are linked to
     *   their observer by username; registration and social logins both pick a
     *   username no one else has, and this also guards against simultaneous
     *   sign-ups.
     * - observations.observed_on: the table's default sort. Without an index the
     *   whole joined result (188k rows) is sorted to show the first 25.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->index('observed_on');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['observed_on']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
        });
    }
};
