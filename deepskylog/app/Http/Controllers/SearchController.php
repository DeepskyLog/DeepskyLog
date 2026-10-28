<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        // Sigma 1, Struve 1: the Struve double star STF 1
        $q = \App\Services\StruveSigmaNames::designation($q) ?? $q;
        if ($q === '') {
            return response()->json([]);
        }

        $limit = max(1, min(100, (int) $request->get('limit', 20)));

        // Filters: source (comma-separated source_table values) and object_type (comma-separated source_type)
        $allowedSources = ['objects', 'cometobjects', 'planets', 'moons', 'lunar_features', 'asteroids'];

        $sources = $this->parseTokens($request->get('source'));
        $invalid = array_diff($sources, $allowedSources);
        if (!empty($invalid)) {
            return response()->json(['error' => 'Invalid source tokens: ' . implode(',', $invalid)], 400);
        }

        // source_type holds the object type code (GALXY, OPNCL, ...), 'alias',
        // 'comet', the body type of planets, moons and asteroids and the
        // feature type of lunar features, so the allowed tokens are the values
        // that are in the index.
        $types = $this->parseTokens($request->get('object_type'));
        if (!empty($types)) {
            $allowedTypes = $this->indexedSourceTypes();
            $invalid = array_udiff($types, $allowedTypes, 'strcasecmp');
            if (!empty($invalid)) {
                return response()->json(['error' => 'Invalid object_type tokens: ' . implode(',', $invalid)], 400);
            }
        }

        $query = function () use ($sources, $types) {
            $query = DB::table('search_index')
                ->select(['id', 'name', 'source_table', 'source_pk', 'display_name', 'source_type', 'ra', 'decl']);
            if (!empty($sources)) {
                $query->whereIn('source_table', $sources);
            }
            if (!empty($types)) {
                $query->whereIn('source_type', $types);
            }

            return $query;
        };

        if ($request->boolean('substring')) {
            $results = $query()->where('name', 'like', '%' . $q . '%')->orderBy('name')->limit($limit)->get();
        } else {
            try {
                // Fulltext (MySQL only) keeps the ranking.
                $results = $query()
                    ->selectRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE) AS score', [$q . '*'])
                    ->whereRaw('MATCH(name) AGAINST(? IN BOOLEAN MODE)', [$q . '*'])
                    ->orderByDesc('score')
                    ->limit($limit)
                    ->get();
            } catch (\Exception $e) {
                $results = $query()->where('name', 'like', $q . '%')->orderBy('name')->limit($limit)->get();
            }
        }

        return response()->json($results);
    }

    /**
     * Split a comma-separated filter value into trimmed, non-empty tokens.
     */
    private function parseTokens(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($t) => $t !== ''));
    }

    /**
     * The distinct source_type values of the search index.
     */
    private function indexedSourceTypes(): array
    {
        return Cache::remember('search_index:source_types', now()->addDay(), function () {
            return DB::table('search_index')
                ->whereNotNull('source_type')
                ->where('source_type', '!=', '')
                ->distinct()
                ->pluck('source_type')
                ->all();
        });
    }

    /**
     * Render a simple search results page that mounts a Livewire table.
     */
    public function results(Request $request)
    {
        $q = trim($request->get('q', ''));
        return view('search.results', ['q' => $q]);
    }

    /**
     * Render the advanced object search filter builder page.
     */
    public function advanced(Request $request)
    {
        $filters = $request->except('_token', 'page');

        return view('search.advanced', ['filters' => $filters]);
    }

    /**
     * Render the advanced search results page.
     */
    public function advancedResults(Request $request)
    {
        $filters = $request->except('_token');
        return view('search.advanced-results', ['filters' => $filters]);
    }

    /**
     * Render the advanced observation search filter builder page.
     */
    public function advancedObservations(Request $request)
    {
        $filters = $request->except('_token', 'page');

        return view('search.advanced-observations', ['filters' => $filters]);
    }

    /**
     * Render the advanced observation search results page.
     */
    public function advancedObservationResults(Request $request)
    {
        $filters = $request->except('_token');

        return view('search.advanced-observation-results', ['filters' => $filters]);
    }
}
