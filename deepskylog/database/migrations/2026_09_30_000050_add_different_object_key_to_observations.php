<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The target an observation counts for when counting different objects
     * (Observation::DIFFERENT_OBJECT_SQL), as a stored generated column with
     * indexes: COUNT(DISTINCT) on the expression read and sorted all
     * observations, a second per count on /observers and /statistics.
     *
     * - (observer_username, different_object_key): the different objects per
     *   observer, read from the index alone.
     * - (target_type, target_name_normalized, different_object_key): the
     *   different objects per kind, with the double stars (found on
     *   target_name_normalized) apart, read from the index alone.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->string('different_object_key', 255)->nullable()
                ->storedAs("COALESCE(NULLIF(TRIM(target_key), ''), LOWER(TRIM(target_name)))");
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->index(['observer_username', 'different_object_key'], 'idx_obs_observer_different_object');
            $table->index(['target_type', 'target_name_normalized', 'different_object_key'], 'idx_obs_type_name_different_object');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex('idx_obs_observer_different_object');
            $table->dropIndex('idx_obs_type_name_different_object');
            $table->dropColumn('different_object_key');
        });
    }
};
