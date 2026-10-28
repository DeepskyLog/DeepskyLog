<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Indexes for the observation lists (/observations, /observations/{observer}),
     * which sort on observed_on, observed_time and id. Without them MariaDB
     * sorts every matching row (188k for all observations, 470 ms) to show 20.
     * The id does not have to be in the indexes: InnoDB adds the primary key to
     * every secondary index, and MariaDB uses it for the order.
     *
     * - (observed_on, observed_time) replaces the index on observed_on (the
     *   default sort of the observation search table, which it still serves):
     *   the list of all observation types.
     * - (target_type, observed_on, observed_time): the list of one type.
     * - (observer_username, observed_on, observed_time) replaces
     *   (observer_username, observed_on), which queries on an observer's day
     *   still use: the list of an observer.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->index(['observed_on', 'observed_time']);
            $table->index(['target_type', 'observed_on', 'observed_time']);
            $table->index(['observer_username', 'observed_on', 'observed_time']);
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['observed_on']);
            $table->dropIndex(['observer_username', 'observed_on']);
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->index('observed_on');
            $table->index(['observer_username', 'observed_on']);
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['observed_on', 'observed_time']);
            $table->dropIndex(['target_type', 'observed_on', 'observed_time']);
            $table->dropIndex(['observer_username', 'observed_on', 'observed_time']);
        });
    }
};
