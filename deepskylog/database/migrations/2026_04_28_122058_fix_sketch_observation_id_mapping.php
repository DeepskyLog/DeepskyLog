<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * This migration used to repair the sketches of the week and month that
     * still held an old observation id, but it kept wrong links whose
     * observation also had a drawing and replaced correct comet ids (whose
     * drawings are not in images/drawings) by the observation with that old
     * deep-sky id. BackfillSketchObservationIdsSeeder now checks each sketch
     * against the observer of its drawing, so this does nothing.
     */
    public function up(): void
    {
    }

    public function down(): void
    {
    }
};
