<?php

namespace App\Services;

use App\Jobs\ComputeContrastReserveForObjectsBatch;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Queues the contrast reserve and best magnification (user_object_metrics) of
 * the objects of a table that have none yet for the observer's equipment, as
 * a few ComputeContrastReserveForObjectsBatch jobs, and follows their progress
 * in the cache, so a table can tell whether values are still coming without
 * querying.
 */
class ObjectMetricsQueue
{
    public const NAMES_PER_JOB = 1000;

    /**
     * How long queued objects are not queued again while their jobs have not
     * finished (a job that was killed never reports back).
     */
    private const PENDING_SECONDS = 3600;

    /**
     * Queue the metrics missing for these objects (objects.name) and return
     * the token to pass to pending(), or null when none are missing. The same
     * missing objects are queued once until their jobs are done.
     *
     * @param  iterable<string>  $objectNames
     */
    public static function queueMissing(int $userId, int $instrumentId, int $locationId, ?int $lensId, iterable $objectNames): ?string
    {
        $missing = self::missing($userId, $instrumentId, $locationId, $lensId, $objectNames);
        if ($missing === []) {
            return null;
        }

        $token = md5(json_encode([$userId, $instrumentId, $locationId, $lensId, $missing]));
        $chunks = array_chunk($missing, self::NAMES_PER_JOB);
        // The counter is set before dispatching: on the sync queue the jobs
        // run (and count down) during dispatch.
        if (Cache::add(self::key($token), count($chunks), self::PENDING_SECONDS)) {
            foreach ($chunks as $chunk) {
                ComputeContrastReserveForObjectsBatch::dispatch($userId, $instrumentId, $locationId, $chunk, $lensId, $token);
            }
        }

        return self::pending($token) ? $token : null;
    }

    /**
     * Queue the metrics of all objects for this equipment, replacing the
     * stored ones, as ComputeContrastReserveForObjectsBatch jobs of
     * NAMES_PER_JOB objects. Returns the number of jobs queued.
     */
    public static function queueRecompute(int $userId, int $instrumentId, int $locationId, ?int $lensId): int
    {
        $chunks = self::allObjectNameChunks();
        foreach ($chunks as $chunk) {
            ComputeContrastReserveForObjectsBatch::dispatch($userId, $instrumentId, $locationId, $chunk, $lensId, null, true);
        }

        return count($chunks);
    }

    /**
     * The names of all objects, sorted, in chunks of NAMES_PER_JOB.
     *
     * @return list<list<string>>
     */
    public static function allObjectNameChunks(): array
    {
        $names = DB::table('objects')->orderBy('name')->distinct()->pluck('name')
            ->map(fn ($name) => (string) $name)
            ->filter(fn ($name) => trim($name) !== '')
            ->values()
            ->all();

        return array_chunk($names, self::NAMES_PER_JOB);
    }

    /**
     * Whether jobs queued with this token are still to finish.
     */
    public static function pending(?string $token): bool
    {
        return $token !== null && (int) Cache::get(self::key($token), 0) > 0;
    }

    /**
     * Called by each job when it is done.
     */
    public static function jobDone(string $token): void
    {
        if ((int) Cache::decrement(self::key($token)) <= 0) {
            Cache::forget(self::key($token));
        }
    }

    /**
     * The objects without a stored metric for this equipment, sorted.
     *
     * @param  iterable<string>  $objectNames
     * @return list<string>
     */
    public static function missing(int $userId, int $instrumentId, int $locationId, ?int $lensId, iterable $objectNames): array
    {
        $names = [];
        foreach ($objectNames as $name) {
            $name = trim((string) $name);
            if ($name !== '') {
                $names[mb_strtolower($name)] = $name;
            }
        }

        foreach (array_chunk(array_keys($names), self::NAMES_PER_JOB) as $chunk) {
            $existing = DB::table('user_object_metrics')
                ->where('user_id', $userId)
                ->where('instrument_id', $instrumentId)
                ->where('location_id', $locationId)
                ->when($lensId === null, fn ($query) => $query->whereNull('lens_id'), fn ($query) => $query->where('lens_id', $lensId))
                ->whereIn('object_name', array_map(fn ($key) => $names[$key], $chunk))
                ->distinct()
                ->pluck('object_name');
            foreach ($existing as $name) {
                unset($names[mb_strtolower((string) $name)]);
            }
        }

        $missing = array_values($names);
        sort($missing);

        return $missing;
    }

    private static function key(string $token): string
    {
        return 'object_metrics_pending:'.$token;
    }
}
