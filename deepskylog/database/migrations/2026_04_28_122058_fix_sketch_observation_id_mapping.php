<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Fix sketch_of_the_week and sketch_of_the_month rows where the stored
     * observation_id is actually a legacy ID (not a unified observations.id).
     *
     * The BackfillSketchObservationIdsSeeder incorrectly skipped rows where
     * the legacy ID happened to coincidentally exist as a unified observations.id.
     * Those rows still point to observations without drawings.
     *
     * Fix: for any sketch row whose current observation has no drawing file,
     * look up the correct unified observation via legacy_id and update.
     */
    public function up(): void
    {
        foreach (['sketch_of_the_week', 'sketch_of_the_month'] as $table) {
            DB::table($table)->orderBy('id')->each(function ($row) use ($table) {
                $currentObs = DB::table('observations')->where('id', $row->observation_id)->first();
                if (!$currentObs) {
                    return;
                }

                // Check if drawing file exists for the current mapping
                $drawingFile = public_path('images/drawings/' . $currentObs->legacy_id . '.jpg');
                if (file_exists($drawingFile)) {
                    // Already correct
                    return;
                }

                // The stored observation_id may be a legacy ID — look up the correct unified observation
                $correctObs = DB::table('observations')
                    ->where('legacy_source', 'deepsky')
                    ->where('legacy_id', $row->observation_id)
                    ->first();

                if (!$correctObs) {
                    // Try comet
                    $correctObs = DB::table('observations')
                        ->where('legacy_source', 'comet')
                        ->where('legacy_id', $row->observation_id)
                        ->first();
                }

                if ($correctObs && $correctObs->id !== $row->observation_id) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['observation_id' => $correctObs->id]);
                }
            });
        }
    }

    public function down(): void
    {
        // Not reversible without a full re-seed
    }
};
