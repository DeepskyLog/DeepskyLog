<?php

use App\Models\ObservationLike;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Store likes and comments of observations as observation_type 'observation'
     * with the id of the observation in the `observations` table.
     *
     * They used to be stored under the type of the target ('deepsky', 'comet',
     * and from the list cards also 'planet', 'lunar', ...) and, depending on the
     * page they were made on, under the id of the old DeepskyLog (legacy_id) or
     * under the new id. For 'deepsky' and 'comet' the old id is tried first, as
     * the like controller did when it gave the observer a point; the new id
     * only when no migrated observation has that old id. Other types were only
     * ever sent with the new id.
     *
     * Rows whose observation no longer exists are deleted, as are likes that
     * turn out to be a second like of the same observation by the same user.
     * Session likes are left as they are.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $this->convert('observation_likes', true);
            $this->convert('observation_comments', false);
        });
    }

    private function convert(string $table, bool $uniquePerUser): void
    {
        DB::table($table)
            ->whereNotIn('observation_type', [ObservationLike::TYPE_OBSERVATION, ObservationLike::TYPE_SESSION])
            ->orderBy('id')
            ->get(['id', 'user_id', 'observation_type', 'observation_id'])
            ->each(function ($row) use ($table, $uniquePerUser) {
                $observationId = $this->resolveObservationId($row->observation_type, (int) $row->observation_id);

                $duplicate = $observationId !== null && $uniquePerUser && DB::table($table)
                    ->where('user_id', $row->user_id)
                    ->where('observation_type', ObservationLike::TYPE_OBSERVATION)
                    ->where('observation_id', $observationId)
                    ->exists();

                if ($observationId === null || $duplicate) {
                    DB::table($table)->where('id', $row->id)->delete();

                    return;
                }

                DB::table($table)->where('id', $row->id)->update([
                    'observation_type' => ObservationLike::TYPE_OBSERVATION,
                    'observation_id' => $observationId,
                ]);
            });
    }

    private function resolveObservationId(string $type, int $id): ?int
    {
        if (in_array($type, ['deepsky', 'comet'], true)) {
            $migrated = DB::table('observations')
                ->where('legacy_source', $type)
                ->where('legacy_id', $id)
                ->value('id');
            if ($migrated !== null) {
                return (int) $migrated;
            }
        }

        $current = DB::table('observations')->where('id', $id)->value('id');

        return $current !== null ? (int) $current : null;
    }

    /**
     * Back to the type of the target and, for migrated observations, the id of
     * the old DeepskyLog. Deleted rows are not restored.
     */
    public function down(): void
    {
        foreach (['observation_likes', 'observation_comments'] as $table) {
            DB::table($table)
                ->where('observation_type', ObservationLike::TYPE_OBSERVATION)
                ->orderBy('id')
                ->get(['id', 'observation_id'])
                ->each(function ($row) use ($table) {
                    $observation = DB::table('observations')
                        ->where('id', $row->observation_id)
                        ->first(['id', 'legacy_source', 'legacy_id', 'target_type']);
                    if (! $observation) {
                        return;
                    }

                    $legacy = $observation->legacy_source !== null && $observation->legacy_id !== null;
                    DB::table($table)->where('id', $row->id)->update([
                        'observation_type' => $legacy ? $observation->legacy_source : ($observation->target_type ?? 'deepsky'),
                        'observation_id' => $legacy ? $observation->legacy_id : $observation->id,
                    ]);
                });
        }
    }
};
