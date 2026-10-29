<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\User;
use App\Support\MemoryLimit;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "Observers who saw this also saw…": per deep-sky object the objects the
 * same observers saw (on different_object_key, "not seen" left out), stored
 * in object_similarities, PER_OBJECT per object.
 *
 * The score of two objects is the cosine of their observers, each observer
 * weighted 1/√(1 + the number of objects they saw): an observer who logged
 * 17,000 objects says little about which ones go together, and without the
 * weight they would decide every score. The cosine divides by the number of
 * observers of both objects, so the showpieces everybody saw do not top
 * every list: the neighbours of Hickson 44 are galaxies near it and Arp
 * galaxies, those of NGC 891 other edge-on galaxies. Objects of fewer than
 * MIN_OBSERVERS observers and pairs of fewer than MIN_COMMON common
 * observers are left out, as a single observer's list is no taste.
 *
 * rebuild() takes about 25 s and 130 MB for 120,000 observer/object pairs
 * (the work grows with the square of each observer's number of objects), so
 * it runs weekly on the long queue (ComputeObjectSimilarities) and after an
 * SQL import (objects:rebuild-also-seen). NextForYou sums the scores of the
 * objects an observer has seen into personal suggestions.
 */
class AlsoSeenObjects
{
    public const TABLE = 'object_similarities';

    /** The objects stored per object, the best first. */
    public const PER_OBJECT = 20;

    /** The objects shown on the object page. */
    public const SHOWN = 8;

    /** The observers an object needs to get or be a suggestion. */
    public const MIN_OBSERVERS = 3;

    /** The observers two objects need in common. */
    public const MIN_COMMON = 3;

    /** Catalog errors, never suggested. */
    private const EXCLUDED_TYPES = ['NONEX'];

    /** The cache key of the moment of the last rebuild(), part of NextForYou's key. */
    private const VERSION_KEY = 'object_similarities:version';

    private const INSERT_CHUNK = 1000;

    /**
     * Recalculates object_similarities from all observations.
     *
     * @return array{pairs: int, objects: int, rows: int}
     */
    public static function rebuild(): array
    {
        MemoryLimit::atLeast('1G');

        // the objects of each observer, as numbers
        $objects   = [];
        $byObject  = [];
        $byUser    = [];
        $observers = [];
        $pairs     = 0;
        foreach (self::seenPairs()->cursor() as $row) {
            $object = $objects[$row->name] ??= count($objects);
            $user   = $observers[$row->observer_username] ??= count($observers);
            $byObject[$object][] = $user;
            $byUser[$user][]     = $object;
            $pairs++;
        }
        unset($observers);

        $kept = [];
        foreach ($byObject as $object => $users) {
            if (count($users) >= self::MIN_OBSERVERS) {
                $kept[$object] = true;
            }
        }
        $weight = [];
        foreach ($byUser as $user => $seen) {
            $byUser[$user] = array_values(array_filter($seen, fn (int $object) => isset($kept[$object])));
            $weight[$user] = 1.0 / sqrt(1 + count($byUser[$user]));
        }
        // the length of each object's vector of observers (each the root of its weight)
        $length = [];
        foreach ($kept as $object => $_) {
            $sum = 0.0;
            foreach ($byObject[$object] as $user) {
                $sum += $weight[$user];
            }
            $length[$object] = sqrt($sum);
        }

        $names = array_flip($objects);
        $rows  = [];
        foreach ($kept as $a => $_) {
            $together = [];
            $common   = [];
            foreach ($byObject[$a] as $user) {
                $w = $weight[$user];
                foreach ($byUser[$user] as $b) {
                    $together[$b] = ($together[$b] ?? 0.0) + $w;
                    $common[$b]   = ($common[$b] ?? 0) + 1;
                }
            }
            unset($together[$a]);

            $scores = [];
            foreach ($together as $b => $sum) {
                if ($common[$b] >= self::MIN_COMMON) {
                    $scores[$b] = $sum / ($length[$a] * $length[$b]);
                }
            }
            arsort($scores);
            foreach (array_slice($scores, 0, self::PER_OBJECT, true) as $b => $score) {
                $rows[] = ['object_name' => $names[$a], 'similar_name' => $names[$b], 'score' => round($score, 4), 'observers' => $common[$b]];
            }
        }

        DB::transaction(function () use ($rows) {
            DB::table(self::TABLE)->delete();
            foreach (array_chunk($rows, self::INSERT_CHUNK) as $chunk) {
                DB::table(self::TABLE)->insert($chunk);
            }
        });
        Cache::forever(self::VERSION_KEY, now()->getTimestamp());

        return ['pairs' => $pairs, 'objects' => count(array_unique(array_column($rows, 'object_name'))), 'rows' => count($rows)];
    }

    /**
     * Changes with every rebuild(); 0 before the first.
     */
    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 0);
    }

    /**
     * The objects observers of $objectName saw too, the best first, with
     * their slug, type, constellation and magnitude.
     *
     * @return list<array{name: string, slug: string, type: string, constellation: ?string, magnitude: ?float, score: float, observers: int}>
     */
    public static function forObject(string $objectName, int $limit = self::SHOWN): array
    {
        return DB::table(self::TABLE.' as s')
            ->join('objects as o', 'o.name', '=', 's.similar_name')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->where('s.object_name', $objectName)
            ->whereNotIn('o.type', self::EXCLUDED_TYPES)
            ->orderByDesc('s.score')
            ->orderBy('o.name')
            ->limit($limit)
            ->get(['o.name', 'o.slug', 'o.type', 'o.mag', 't.name as type_name', 'c.name as constellation', 's.score', 's.observers'])
            ->map(fn ($row) => [
                'name'          => (string) $row->name,
                'slug'          => (string) $row->slug,
                'type'          => $row->type_name ?: (string) $row->type,
                'constellation' => $row->constellation,
                'magnitude'     => is_numeric($row->mag) && (float) $row->mag < 99 ? round((float) $row->mag, 1) : null,
                'score'         => (float) $row->score,
                'observers'     => (int) $row->observers,
            ])
            ->all();
    }

    /**
     * The deep-sky observations of an observer that were not "not seen".
     */
    public static function seenBy(User $user): Builder
    {
        return self::seen(DB::table('observations'))
            ->where('observations.observer_username', $user->username);
    }

    /**
     * Which of $names (objects.name) the observer has seen, keyed in lower case.
     *
     * @param  list<string>  $names
     * @return array<string, true>
     */
    public static function seenAmong(User $user, array $names): array
    {
        if ($names === []) {
            return [];
        }

        return self::seenBy($user)
            ->whereIn('observations.different_object_key', $names)
            ->distinct()
            ->pluck('observations.different_object_key')
            ->mapWithKeys(fn ($name) => [mb_strtolower((string) $name) => true])
            ->all();
    }

    /**
     * Every observer and deep-sky object they saw, once, by the object's
     * canonical name.
     */
    private static function seenPairs(): Builder
    {
        return self::seen(DB::table('observations'))
            ->join('objects', 'objects.name', '=', 'observations.different_object_key')
            ->whereNotIn('objects.type', self::EXCLUDED_TYPES)
            ->distinct()
            ->select(['observations.observer_username', 'objects.name']);
    }

    private static function seen(Builder $query): Builder
    {
        return $query
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->whereNotNull('observations.different_object_key')
            ->whereNotExists(function (Builder $details) {
                $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN);
            });
    }
}
