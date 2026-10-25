<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The contrast reserves now dim the object by the atmosphere at the
     * highest altitude it reaches at the location (ContrastReserveCalculator),
     * so the stored ones are too high. They are computed again on the queue
     * (ObjectMetricsQueue) for the objects the tables and Tonight's best show.
     */
    public function up(): void
    {
        DB::table('user_object_metrics')->truncate();
    }

    public function down(): void
    {
        // the metrics are computed again when they are missing
    }
};
