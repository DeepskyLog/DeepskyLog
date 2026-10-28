<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The observation statistics of the deep-sky objects, kept up to date by
     * App\Services\ObjectObservationStatistics, so that the object tables join
     * one row per object instead of aggregating the observations of every
     * object they sort or filter on.
     *
     * - object_observation_stats: all observers together, one row per object.
     * - object_observation_stats_by_observer: one row per observer and object,
     *   for the "your ..." columns.
     *
     * Only objects with at least one observation have a row. Fill them with
     * `php artisan observations:rebuild-object-stats`.
     */
    public function up(): void
    {
        Schema::create('object_observation_stats', function (Blueprint $table) {
            $table->string('object_name', 255)->primary();
            $table->integer('observations')->default(0);
            $table->integer('drawings')->default(0);
            $table->date('last_observed_on')->nullable();
            $table->date('last_drawing_on')->nullable();
        });

        Schema::create('object_observation_stats_by_observer', function (Blueprint $table) {
            $table->string('object_name', 255);
            $table->string('observer_username', 255);
            $table->integer('observations')->default(0);
            $table->integer('drawings')->default(0);
            $table->date('last_observed_on')->nullable();
            $table->date('last_drawing_on')->nullable();

            $table->primary(['object_name', 'observer_username']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_observation_stats_by_observer');
        Schema::dropIfExists('object_observation_stats');
    }
};
