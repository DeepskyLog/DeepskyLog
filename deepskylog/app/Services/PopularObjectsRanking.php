<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The objects ranked by their number of observations (or drawings), as shown
 * on /popular-objects: of all observers, or of the observers living in one
 * country (ObserverCountries::options()).
 */
class PopularObjectsRanking
{
    public const MODES = ['observations', 'drawings'];

    /**
     * The ranking groups all observations, so it is fresh for an hour; for a
     * day after that the old ranking is shown while it is computed again after
     * the response is sent (Cache::flexible()).
     */
    private const CACHE_SECONDS = [3600, 86400];

    /** Most names or keys in one whereIn() while resolving the slugs. */
    private const CHUNK = 1000;

    /**
     * All objects, most observed first, with their rank, kind and slug.
     *
     * @return list<array{id: int, rank: int, name: string, kind: string, count: int, slug: string}>
     */
    public function rows(string $mode, ?string $country = null): array
    {
        if (!in_array($mode, self::MODES, true)) {
            $mode = 'observations';
        }
        $country = ObserverCountries::isOption($country) ? strtoupper($country) : null;

        return Cache::flexible(
            "popular_objects:{$mode}:ranking" . ($country ? ":{$country}" : ''),
            self::CACHE_SECONDS,
            fn() => $this->compute($mode, $country)
        );
    }

    /**
     * @return list<array{id: int, rank: int, name: string, kind: string, count: int, slug: string}>
     */
    private function compute(string $mode, ?string $country): array
    {
        $groups = $this->groupedQuery($mode, $country)
            ->orderByDesc('total_count')
            ->orderBy('object_name')
            ->get();

        $slugs = [];
        foreach (['deepsky', 'comet', 'planet', 'asteroid'] as $kind) {
            $ofKind = $groups->where('object_kind', $kind);
            if ($ofKind->isNotEmpty()) {
                $slugs[$kind] = $this->resolveSlugs(
                    $kind,
                    $ofKind->pluck('object_name')->map(fn($name) => (string) $name)->all(),
                    $ofKind->pluck('sample_target_key')->map(fn($key) => trim((string) $key))->filter()->all()
                );
            }
        }

        $rows = [];
        foreach ($groups->values() as $index => $group) {
            $name = (string) $group->object_name;
            $kind = (string) $group->object_kind;
            $sampleKey = trim((string) ($group->sample_target_key ?? ''));
            $map = $slugs[$kind] ?? [];

            $slug = $map['name'][self::key($name)]
                ?? ($sampleKey !== '' ? ($map['key'][self::key($sampleKey)] ?? null) : null)
                ?? ($sampleKey !== '' && $kind !== 'deepsky' ? $sampleKey : null);

            $rows[] = [
                'id' => $index + 1,
                'rank' => $index + 1,
                'name' => $name,
                'kind' => $kind,
                'count' => (int) ($group->total_count ?? 0),
                'slug' => $this->normalizeObjectSlug($slug, $name),
            ];
        }

        return $rows;
    }

    /**
     * The observations (or drawings) grouped per object. The kind of object
     * follows from target_type, so grouping on target_type and target_name
     * uses their index instead of a temporary table of all observations.
     */
    private function groupedQuery(string $mode, ?string $country): Builder
    {
        return DB::table('observations')
            ->when($country, fn(Builder $query) => $query->whereIn(
                'observer_username',
                DB::table('users')->where('country', $country)->select('username')
            ))
            ->whereNotNull('target_name')
            ->where('target_name', '!=', '')
            // Asteroids are 'solarsystem' (see SavesImportedObservations)
            ->whereIn('target_type', ['deepsky', 'comet', 'planet', 'solarsystem'])
            ->where(fn(Builder $query) => $query->where('target_type', '<>', 'solarsystem')->orWhere('target_table', 'asteroids'))
            ->when($mode === 'drawings', fn(Builder $query) => $query->where('has_drawing', 1))
            ->selectRaw('target_name as object_name')
            ->selectRaw("CASE WHEN target_type = 'comet' THEN 'comet' WHEN target_type = 'planet' THEN 'planet' WHEN target_type = 'solarsystem' THEN 'asteroid' ELSE 'deepsky' END as object_kind")
            ->selectRaw('MIN(target_key) as sample_target_key')
            ->selectRaw('COUNT(*) as total_count')
            ->groupBy('target_type', 'target_name');
    }

    /**
     * The slugs of the objects of one kind, by name and by target key: deep-sky
     * objects through their (alternative) names in objectnames, else objects;
     * comets, planets and asteroids through their own table, by name, slug or id.
     *
     * @param  list<string>  $names
     * @param  list<string>  $keys
     * @return array{name: array<string, string>, key: array<string, string>}
     */
    private function resolveSlugs(string $kind, array $names, array $keys): array
    {
        $byName = [];
        $byKey = [];

        try {
            if ($kind === 'deepsky') {
                foreach (array_chunk(array_values(array_unique($names)), self::CHUNK) as $chunk) {
                    // The first objectname row of a name wins, as before.
                    DB::table('objectnames')->whereIn('objectname', $chunk)->orderByDesc('id')
                        ->get(['objectname', 'slug'])
                        ->each(function ($row) use (&$byName) {
                            if (filled($row->slug)) {
                                $byName[self::key((string) $row->objectname)] = (string) $row->slug;
                            }
                        });
                }

                $missing = array_values(array_filter(array_unique($names), fn($name) => !isset($byName[self::key($name)])));
                foreach (array_chunk($missing, self::CHUNK) as $chunk) {
                    DB::table('objects')->whereIn('name', $chunk)->get(['name', 'slug'])
                        ->each(function ($row) use (&$byName) {
                            $byName[self::key((string) $row->name)] ??= (string) $row->slug;
                        });
                }

                foreach (array_chunk(array_values(array_unique($keys)), self::CHUNK) as $chunk) {
                    DB::table('objects')->whereIn('slug', $chunk)->pluck('slug')
                        ->each(function ($slug) use (&$byKey) {
                            $byKey[self::key((string) $slug)] = (string) $slug;
                        });
                }

                return ['name' => $byName, 'key' => $byKey];
            }

            $table = match ($kind) {
                'comet' => 'cometobjects',
                'asteroid' => 'asteroids',
                default => 'planets',
            };

            foreach (array_chunk(array_values(array_unique($names)), self::CHUNK) as $chunk) {
                DB::table($table)->whereIn('name', $chunk)->get(['name', 'slug'])
                    ->each(function ($row) use (&$byName) {
                        $byName[self::key((string) $row->name)] ??= (string) $row->slug;
                    });
            }

            foreach (array_chunk(array_values(array_unique($keys)), self::CHUNK) as $chunk) {
                $ids = array_values(array_map('intval', array_filter($chunk, 'is_numeric')));
                DB::table($table)
                    ->where(fn(Builder $query) => $query->whereIn('slug', $chunk)->when($ids, fn($q) => $q->orWhereIn('id', $ids)))
                    ->get(['id', 'slug'])
                    ->each(function ($row) use (&$byKey) {
                        $byKey[self::key((string) $row->slug)] = (string) $row->slug;
                        $byKey[(string) $row->id] ??= (string) $row->slug;
                    });
            }
        } catch (\Throwable $_) {
            // A missing table leaves the slugs to the names and keys.
        }

        return ['name' => $byName, 'key' => $byKey];
    }

    /** Names and slugs compare case-insensitively, as the database does. */
    private static function key(string $value): string
    {
        return mb_strtolower($value);
    }

    private function normalizeObjectSlug(?string $slug, string $objectName): string
    {
        $candidate = trim((string) ($slug ?? ''));
        if ($candidate === '') {
            $candidate = $objectName;
        }

        return Str::slug($candidate, '-') ?: Str::slug($objectName, '-');
    }
}
