<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $limit = (int) $request->get('limit', 20);

    // Filters: source (comma-separated source_table values) and object_type (comma-separated source_type)
    $sourceFilter = $request->get('source');
    $typeFilter = $request->get('object_type');

    // Allowed tokens to prevent typos and injection-like values. Update these lists if you add new source tables or types.
    $allowedSources = ['objects', 'cometobjects', 'planets', 'moons', 'lunar_features', 'asteroids'];
    $allowedTypes = ['object', 'comet', 'planet', 'moon', 'crater', 'mare', 'lunar_feature', 'asteroid'];

        // If any filter is provided, use query builder and apply WHERE IN clauses safely.
        if (!empty($sourceFilter) || !empty($typeFilter)) {
            // Parse tokens and validate them against allowed lists
            $sources = [];
            if (!empty($sourceFilter)) {
                $sources = array_values(array_filter(array_map('trim', explode(',', $sourceFilter))));
                $invalid = array_diff($sources, $allowedSources);
                if (!empty($invalid)) {
                    return response()->json(['error' => 'Invalid source tokens: ' . implode(',', $invalid)], 400);
                }
            }

            $types = [];
            if (!empty($typeFilter)) {
                $types = array_values(array_filter(array_map('trim', explode(',', $typeFilter))));
                $invalid = array_diff($types, $allowedTypes);
                if (!empty($invalid)) {
                    return response()->json(['error' => 'Invalid object_type tokens: ' . implode(',', $invalid)], 400);
                }
            }

            // If substring requested, use LIKE with filters (no ranking available)
            if ($request->boolean('substring')) {
                $query = DB::table('search_index');
                if (!empty($sources)) {
                    $query->whereIn('source_table', $sources);
                }
                if (!empty($types)) {
                    $query->whereIn('source_type', $types);
                }
                $query->where('name', 'like', '%' . $q . '%');
                $results = $query->orderBy('name')->limit($limit)->get();
            } else {
                // Attempt to use fulltext MATCH AGAINST with filters to preserve ranking
                try {
                    // Build WHERE IN SQL clauses with bindings
                    $whereParts = [];
                    $bindings = ['q' => $q . '*', 'limit' => $limit];

                    if (!empty($sources)) {
                        $placeholders = [];
                        foreach ($sources as $i => $s) {
                            $key = 'src_' . $i;
                            $placeholders[] = ':' . $key;
                            $bindings[$key] = $s;
                        }
                        $whereParts[] = 'source_table IN (' . implode(',', $placeholders) . ')';
                    }

                    if (!empty($types)) {
                        $placeholders = [];
                        foreach ($types as $i => $t) {
                            $key = 'typ_' . $i;
                            $placeholders[] = ':' . $key;
                            $bindings[$key] = $t;
                        }
                        $whereParts[] = 'source_type IN (' . implode(',', $placeholders) . ')';
                    }

                    $whereSql = '';
                    if (!empty($whereParts)) {
                        $whereSql = ' AND ' . implode(' AND ', $whereParts);
                    }

                    $sql = "SELECT id, name, source_table, source_pk, display_name, source_type, ra, decl, MATCH(name) AGAINST(:q IN BOOLEAN MODE) AS score FROM search_index WHERE MATCH(name) AGAINST(:q IN BOOLEAN MODE) " . $whereSql . " ORDER BY score DESC LIMIT :limit";
                    $results = DB::select(DB::raw($sql), $bindings);
                } catch (\Exception $e) {
                    // Fallback to prefix LIKE with filters
                    $query = DB::table('search_index');
                    if (!empty($sources)) {
                        $query->whereIn('source_table', $sources);
                    }
                    if (!empty($types)) {
                        $query->whereIn('source_type', $types);
                    }
                    $query->where('name', 'like', $q . '%');
                    $results = $query->orderBy('name')->limit($limit)->get();
                }
            }
        } else {
            // No filters: fallback to previous behavior (fulltext or LIKE)
            if ($request->boolean('substring')) {
                $like = '%' . $q . '%';
                $results = DB::table('search_index')
                    ->where('name', 'like', $like)
                    ->orderBy('name')
                    ->limit($limit)
                    ->get();
            } else {
                try {
                    $results = DB::select(DB::raw("SELECT id, name, source_table, source_pk, display_name, source_type, ra, decl, MATCH(name) AGAINST(:q IN BOOLEAN MODE) AS score FROM search_index WHERE MATCH(name) AGAINST(:q IN BOOLEAN MODE) ORDER BY score DESC LIMIT :limit"), ['q' => $q . '*', 'limit' => $limit]);
                } catch (\Exception $e) {
                    $like = $q . '%';
                    $results = DB::table('search_index')
                        ->where('name', 'like', $like)
                        ->orderBy('name')
                        ->limit($limit)
                        ->get();
                }
            }
        }

        return response()->json($results);
    }
}
