<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PopularObjectController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'observations');
        if (!in_array($mode, ['observations', 'drawings'], true)) {
            $mode = 'observations';
        }

        return view('observations.popular-objects', [
            'mode' => $mode,
            'popularObjects' => $this->popularObjects($mode),
        ]);
    }

    private function popularObjects(string $mode): LengthAwarePaginator
    {
        $kindExpression = "CASE WHEN target_table = 'cometobjects' OR target_type = 'comet' OR legacy_source = 'comet' THEN 'comet' WHEN target_table = 'planets' THEN 'planet' ELSE 'deepsky' END";

        $baseQuery = DB::table('observations')
            ->whereNotNull('target_name')
            ->where('target_name', '!=', '')
            ->where(function ($builder) {
                $builder->where('target_type', 'deepsky')
                    ->orWhere('target_table', 'objects')
                    ->orWhere('target_table', 'planets')
                    ->orWhere('target_table', 'cometobjects')
                    ->orWhere('target_type', 'comet')
                    ->orWhere('legacy_source', 'comet')
                    ->orWhere(function ($legacyBuilder) {
                        $legacyBuilder->whereNull('target_table')
                            ->where(function ($objectBuilder) {
                                $objectBuilder->whereExists(function ($subQuery) {
                                    $subQuery->selectRaw('1')
                                        ->from('objectnames')
                                        ->whereColumn('objectnames.objectname', 'observations.target_name');
                                })->orWhereExists(function ($subQuery) {
                                    $subQuery->selectRaw('1')
                                        ->from('objects')
                                        ->whereColumn('objects.name', 'observations.target_name');
                                });
                            });
                    });
            })
            ->selectRaw('target_name as object_name')
            ->selectRaw('target_key')
            ->selectRaw($kindExpression . ' as object_kind');

        if ($mode === 'drawings') {
            $baseQuery->where('has_drawing', 1);
        }

        return DB::query()
            ->fromSub($baseQuery, 'object_observations')
            ->select('object_name', 'object_kind')
            ->selectRaw('MIN(target_key) as sample_target_key')
            ->selectRaw('COUNT(*) as total_count')
            ->groupBy('object_name', 'object_kind')
            ->orderByDesc('total_count')
            ->orderBy('object_name')
            ->paginate(50)
            ->withQueryString()
            ->through(function ($row) use ($mode) {
                $objectSlug = $this->resolveObjectSlug(
                    (string) $row->object_kind,
                    $row->sample_target_key,
                    (string) $row->object_name
                );

                return (object) [
                    'name' => (string) $row->object_name,
                    'count' => (int) ($row->total_count ?? 0),
                    'object_url' => route('object.show', ['slug' => $objectSlug]),
                    'count_url' => $this->buildCountUrl((string) $row->object_kind, $mode, $objectSlug),
                ];
            });
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