<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PopularObjectController extends Controller
{
    private const PER_PAGE = 50;

    /**
     * The ranking groups all observations, so the number of objects and each
     * page (with the slugs of its objects) are fresh for an hour; for a day
     * after that the old ranking is shown while it is computed again after the
     * response is sent (Cache::flexible()).
     */
    private const CACHE_SECONDS = [3600, 86400];

    public function index(Request $request)
    {
        $mode = $request->query('mode', 'observations');
        if (!in_array($mode, ['observations', 'drawings'], true)) {
            $mode = 'observations';
        }

        return view('observations.popular-objects', [
            'mode' => $mode,
            'popularObjects' => $this->popularObjects($mode, $request),
        ]);
    }

    private function popularObjects(string $mode, Request $request): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();

        $total = Cache::flexible("popular_objects:{$mode}:total", self::CACHE_SECONDS, fn() => DB::query()
            ->fromSub($this->observationsQuery($mode)->selectRaw('1')->groupBy('target_type', 'target_name'), 'popular_objects')
            ->count());

        // Pages past the last one are empty; not cached, so that requests for
        // arbitrary page numbers cannot fill the cache.
        $rows = $page > (int) ceil($total / self::PER_PAGE)
            ? []
            : Cache::flexible("popular_objects:{$mode}:page:{$page}", self::CACHE_SECONDS, fn() => $this->pageRows($mode, $page));

        $items = array_map(fn(array $row) => (object) [
            'name' => $row['name'],
            'count' => $row['count'],
            'object_url' => route('object.show', ['slug' => $row['slug']]),
            'count_url' => $this->buildCountUrl($row['kind'], $mode, $row['slug']),
        ], $rows);

        return new LengthAwarePaginator($items, $total, self::PER_PAGE, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);
    }

    /**
     * The objects of one page, most observed first, with the slug of each.
     *
     * @return list<array{name: string, kind: string, count: int, slug: string}>
     */
    private function pageRows(string $mode, int $page): array
    {
        return $this->groupedQuery($mode)
            ->orderByDesc('total_count')
            ->orderBy('object_name')
            ->forPage($page, self::PER_PAGE)
            ->get()
            ->map(fn($row) => [
                'name' => (string) $row->object_name,
                'kind' => (string) $row->object_kind,
                'count' => (int) ($row->total_count ?? 0),
                'slug' => $this->resolveObjectSlug(
                    (string) $row->object_kind,
                    $row->sample_target_key,
                    (string) $row->object_name
                ),
            ])
            ->all();
    }

    /**
     * The observations (or drawings) grouped per object. The kind of object
     * follows from target_type, so grouping on target_type and target_name
     * uses their index instead of a temporary table of all observations.
     */
    private function groupedQuery(string $mode): Builder
    {
        return $this->observationsQuery($mode)
            ->selectRaw('target_name as object_name')
            ->selectRaw("CASE WHEN target_type = 'comet' THEN 'comet' WHEN target_type = 'planet' THEN 'planet' ELSE 'deepsky' END as object_kind")
            ->selectRaw('MIN(target_key) as sample_target_key')
            ->selectRaw('COUNT(*) as total_count')
            ->groupBy('target_type', 'target_name');
    }

    private function observationsQuery(string $mode): Builder
    {
        return DB::table('observations')
            ->whereNotNull('target_name')
            ->where('target_name', '!=', '')
            ->whereIn('target_type', ['deepsky', 'comet', 'planet'])
            ->when($mode === 'drawings', fn(Builder $query) => $query->where('has_drawing', 1));
    }

    private function buildCountUrl(string $objectKind, string $mode, string $objectSlug): string
    {
        if ($objectKind === 'comet') {
            return $mode === 'drawings'
                ? route('cometdrawings.show', ['observer' => $objectSlug])
                : route('observations.comet.object', ['object' => $objectSlug]);
        }

        return $mode === 'drawings'
            ? route('observations.drawings.show', ['slug' => $objectSlug])
            : route('observations.object', ['slug' => $objectSlug]);
    }

    private function resolveObjectSlug(string $objectKind, mixed $sampleTargetKey, string $objectName): string
    {
        $sampleKey = trim((string) ($sampleTargetKey ?? ''));

        return match ($objectKind) {
            'comet' => $this->normalizeObjectSlug($this->resolveCometSlug($sampleKey, $objectName), $objectName),
            'planet' => $this->normalizeObjectSlug($this->resolvePlanetSlug($sampleKey, $objectName), $objectName),
            default => $this->normalizeObjectSlug($this->resolveDeepskySlug($sampleKey, $objectName), $objectName),
        };
    }

    private function resolveCometSlug(string $sampleKey, string $objectName): ?string
    {
        try {
            return DB::table('cometobjects')
                ->where('name', $objectName)
                ->orWhere('slug', $sampleKey)
                ->when(is_numeric($sampleKey), fn($query) => $query->orWhere('id', (int) $sampleKey))
                ->value('slug');
        } catch (\Throwable $_) {
            return $sampleKey !== '' ? $sampleKey : null;
        }
    }

    private function resolvePlanetSlug(string $sampleKey, string $objectName): ?string
    {
        try {
            return DB::table('planets')
                ->where('name', $objectName)
                ->orWhere('slug', $sampleKey)
                ->when(is_numeric($sampleKey), fn($query) => $query->orWhere('id', (int) $sampleKey))
                ->value('slug');
        } catch (\Throwable $_) {
            return $sampleKey !== '' ? $sampleKey : null;
        }
    }

    private function resolveDeepskySlug(string $sampleKey, string $objectName): ?string
    {
        $objectnameSlug = null;
        try {
            $objectnameSlug = DB::table('objectnames')
                ->where('objectname', $objectName)
                ->orderBy('id')
                ->value('slug');
        } catch (\Throwable $_) {
            $objectnameSlug = null;
        }

        if ($objectnameSlug) {
            return $objectnameSlug;
        }

        try {
            return DB::table('objects')
                ->where('name', $objectName)
                ->orWhere('slug', $sampleKey)
                ->value('slug');
        } catch (\Throwable $_) {
            return $sampleKey !== '' ? $sampleKey : null;
        }
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