<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Backfill sessionObservations to use new observations.id values.
 *
 * Background
 * ----------
 * The legacy sessionObservations pivot stored the original deep-sky
 * observation integer IDs from the old `observations` table. Two problems:
 *
 *  1. Comet observations were never linked to sessions in the old system.
 *  2. Deep-sky and comet legacy IDs overlap (same integer can exist in both
 *     old tables), so the pivot is ambiguous.
 *
 * After running LegacyObservationsSeeder, the new `observations` table gives
 * every observation a unique bigint `id` and records the original source table
 * via `legacy_source` ('deepsky' | 'comet') + `legacy_id`.
 *
 * This seeder:
 *  1. Replaces every existing deep-sky legacy ID in sessionObservations with
 *     the corresponding new observations.id.
 *  2. Adds missing comet observation links by matching each session's observer
 *     and date range against the new observations table.
 *
 * Run this seeder BEFORE applying the migration that changes observationid to
 * unsignedBigInteger, so the values are correct before the type is enforced.
 *
 * Safe to re-run: uses insertOrIgnore and updates are idempotent.
 */
class BackfillSessionObservationIdsSeeder extends Seeder
{
    public function run(): void
    {
        $app = DB::connection();

        // Build a lookup: legacy_id (deepsky) → new observations.id
        // Load in chunks to avoid memory issues with large datasets.
        $this->command?->info('Building deepsky legacy_id → new observations.id map...');

        $deepLegacyToNew = [];
        $app->table('observations')
            ->where('legacy_source', 'deepsky')
            ->whereNotNull('legacy_id')
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$deepLegacyToNew) {
                foreach ($rows as $row) {
                    $deepLegacyToNew[(int) $row->legacy_id] = (int) $row->id;
                }
            });

        $this->command?->info(sprintf('  → %d deepsky observations indexed.', count($deepLegacyToNew)));

        // Step 1: Convert existing deepsky legacy IDs → new observations.id.
        // Process in chunks to avoid locking the table for too long.
        $this->command?->info('Step 1: Converting existing sessionObservations rows to new IDs...');
        $converted = 0;
        $skipped = 0;

        $app->table('sessionObservations')
            ->orderBy('sessionid')
            ->orderBy('observationid')
            ->chunk(1000, function ($rows) use ($app, $deepLegacyToNew, &$converted, &$skipped) {
                foreach ($rows as $row) {
                    $legacyId = (int) $row->observationid;
                    if (!isset($deepLegacyToNew[$legacyId])) {
                        // Already a new ID, or an ID that has no match in observations (stale data).
                        $skipped++;
                        continue;
                    }
                    $newId = $deepLegacyToNew[$legacyId];
                    // Only update if the value actually changed (avoids re-running changes).
                    if ((int) $row->observationid === $newId) {
                        $skipped++;
                        continue;
                    }
                    // The PK is (sessionid, observationid) – we must delete + reinsert.
                    $app->table('sessionObservations')
                        ->where('sessionid', $row->sessionid)
                        ->where('observationid', $row->observationid)
                        ->delete();
                    $app->table('sessionObservations')->insertOrIgnore([
                        'sessionid' => (int) $row->sessionid,
                        'observationid' => (string) $newId,
                    ]);
                    $converted++;
                }
            });

        $this->command?->info(sprintf('  → %d rows converted, %d skipped.', $converted, $skipped));

        // Step 2: Add comet observations to sessions.
        // For each session, find all comet observations in the new table that
        // match the session's observer and fall within the session's date range.
        $this->command?->info('Step 2: Adding comet observation links to sessions...');
        $insertedComet = 0;
        $sessionsProcessed = 0;

        $app->table('observation_sessions')
            ->orderBy('id')
            ->chunk(200, function ($sessions) use ($app, &$insertedComet, &$sessionsProcessed) {
                foreach ($sessions as $session) {
                    $sessionsProcessed++;

                    $beginDate = $session->begindate ? substr((string) $session->begindate, 0, 10) : null;
                    $endDate = $session->enddate ? substr((string) $session->enddate, 0, 10) : null;

                    if (!$beginDate || !$endDate) {
                        continue;
                    }

                    $observerUsername = html_entity_decode(
                        (string) $session->observerid,
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );

                    // Find all comet observations for this observer within the session's date range.
                    $cometNewIds = $app->table('observations')
                        ->where('target_type', 'comet')
                        ->where('observer_username', $observerUsername)
                        ->whereBetween('observed_on', [$beginDate, $endDate])
                        ->pluck('id');

                    if ($cometNewIds->isEmpty()) {
                        continue;
                    }

                    // Only insert IDs not already in the pivot.
                    $existingIds = $app->table('sessionObservations')
                        ->where('sessionid', (int) $session->id)
                        ->pluck('observationid')
                        ->map(fn($v) => (string) $v)
                        ->flip()
                        ->all();

                    $inserts = [];
                    foreach ($cometNewIds as $newId) {
                        $key = (string) $newId;
                        if (isset($existingIds[$key])) {
                            continue;
                        }
                        $inserts[] = [
                            'sessionid' => (int) $session->id,
                            'observationid' => $key,
                        ];
                    }

                    if (!empty($inserts)) {
                        $app->table('sessionObservations')->insertOrIgnore($inserts);
                        $insertedComet += count($inserts);
                    }
                }
            });

        $this->command?->info(sprintf(
            '  → Processed %d sessions, inserted %d comet observation links.',
            $sessionsProcessed,
            $insertedComet
        ));

        $this->command?->info('BackfillSessionObservationIdsSeeder complete.');
    }
}
