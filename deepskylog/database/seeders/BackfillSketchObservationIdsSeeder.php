<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackfillSketchObservationIdsSeeder extends Seeder
{
    public function run(): void
    {
        $app = DB::connection();

        $legacyToNew = [];
        $app->table('observations')
            ->whereNotNull('legacy_source')
            ->whereNotNull('legacy_id')
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$legacyToNew) {
                foreach ($rows as $row) {
                    $source = (string) $row->legacy_source;
                    $legacyId = (int) $row->legacy_id;
                    $legacyToNew[$source . ':' . $legacyId] = (int) $row->id;
                }
            });

        $this->backfillTable($app, 'sketch_of_the_week', $legacyToNew);
        $this->backfillTable($app, 'sketch_of_the_month', $legacyToNew);
    }

    /**
     * Convert legacy sketch observation ids to observations.id.
     *
     * Legacy semantics:
     * - observation_id > 0 => deepsky legacy id
     * - observation_id < 0 => comet legacy id stored as negative value
     *
     * Also keeps rows unchanged when they already contain new observations.id.
     */
    private function backfillTable($app, string $table, array $legacyToNew): void
    {
        $updated = 0;
        $skipped = 0;

        $app->table($table)
            ->orderBy('id')
            ->chunk(1000, function ($rows) use ($app, $table, $legacyToNew, &$updated, &$skipped) {
                foreach ($rows as $row) {
                    $currentId = (int) $row->observation_id;

                    // Already mapped to observations.id.
                    if ($app->table('observations')->where('id', $currentId)->exists()) {
                        $skipped++;

                        continue;
                    }

                    $legacySource = $currentId < 0 ? 'comet' : 'deepsky';
                    $legacyId = abs($currentId);
                    $lookupKey = $legacySource . ':' . $legacyId;

                    if (!isset($legacyToNew[$lookupKey])) {
                        $skipped++;

                        continue;
                    }

                    $app->table($table)
                        ->where('id', (int) $row->id)
                        ->update(['observation_id' => $legacyToNew[$lookupKey]]);

                    $updated++;
                }
            });

        $this->command?->info(sprintf('%s: %d rows updated, %d rows skipped.', $table, $updated, $skipped));
    }
}
