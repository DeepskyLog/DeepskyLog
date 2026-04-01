<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuickSearch extends Component
{
    public $query = '';
    public $suggestions = [];
    public $showSuggestions = false;
    // Index of the currently highlighted suggestion (-1 = none)
    public $selectedIndex = -1;
    // Cache resolved type names for short codes (avoid repeated DB lookups)
    protected $cachedTypeNames = [];

    protected $rules = [
        'query' => 'string|max:255',
    ];

    public function updatedQuery()
    {
        $this->fetchSuggestions();
    }

    protected function normalizeWildcard(string $q): string
    {
        $q = trim($q);
        if ($q === '') {
            return '';
        }
        // Convert * to % for SQL LIKE
        $q = str_replace('*', '%', $q);
        return $q;
    }

    public function fetchSuggestions()
    {
        $original = trim($this->query);
        $q = $this->normalizeWildcard($this->query);
        // Determine wildcard positioning. We support three modes:
        // - prefix (default): matches at start (e.g. `M *` -> `M %`)
        // - suffix (leading wildcard only): matches at end (e.g. `* 1` -> `% 1`)
        // - contains (leading+trailing or internal wildcard): matches anywhere
        $hasPercent = strpos($q, '%') !== false;
        $hasLeading = $hasPercent && substr($q, 0, 1) === '%';
        $hasTrailing = $hasPercent && substr($q, -1) === '%';
        $isContains = false;
        $isSuffix = false;
        if ($hasPercent) {
            if ($hasLeading && !$hasTrailing) {
                // leading wildcard only -> suffix (ends-with)
                $isSuffix = true;
            } elseif ($hasLeading && $hasTrailing) {
                $isContains = true;
            } elseif (!$hasLeading && $hasTrailing) {
                // trailing wildcard only -> prefix
                $isContains = false;
            } else {
                // internal percent(s) -> contains
                $isContains = true;
            }
        }
        if ($q === '') {
            $this->suggestions = [];
            $this->showSuggestions = false;
            return;
        }

        // Prefer startswith for performance unless user provided leading %
        if ($isSuffix) {
            // leading wildcard only: ends-with
            $likeStarts = $q; // e.g. '% 1' -> matches names ending with ' 1'
            $likeContains = null;
        } elseif ($isContains) {
            // contains mode
            $likeStarts = '%' . ltrim($q, '%');
            $likeContains = '%' . trim($q, '%') . '%';
        } else {
            // prefix mode (default or trailing wildcard)
            $likeStarts = (substr($q, -1) === '%') ? $q : ($q . '%');
            $likeContains = null;
        }

        // Normalizations for exact-match variations (e.g. "M 1" vs "M1")
        $qNoSpace = preg_replace('/\s+/', '', $original);
        $qLower = Str::lower($original);
        $qNoSpaceLower = Str::lower($qNoSpace);

        $results = [];

        // Build candidate list using search_index when available (includes planets, comets, etc.)
        $maxCandidates = 50;
        $added = [];

        $addRows = function ($rows, $rank) use (&$results, &$added) {
            foreach ($rows as $r) {
                // Build a stable deduplication key using source_table+source_pk when available
                $key = null;
                if (!empty($r->source_table) && !empty($r->source_pk)) {
                    $key = $r->source_table . ':' . $r->source_pk;
                } elseif (!empty($r->slug)) {
                    $key = 'slug:' . $r->slug;
                } elseif (!empty($r->name)) {
                    $key = 'name:' . $r->name;
                }
                if ($key && !isset($added[$key])) {
                    $added[$key] = true;
                    // annotate match rank to prefer exact/prefix over contains
                    $r->match_rank = $rank;
                    $results[] = $r;
                }
            }
        };

        try {
            if (DB::getSchemaBuilder()->hasTable('search_index')) {
                // Detect simple catalog queries like "M 1" or just "M " so we
                // can bias SQL ordering to include low-numbered catalog entries
                // in the candidate set instead of arbitrary DB order.
                $isCatalogQuery = preg_match('/^[A-Za-z]\s*(\d*)?$/u', trim($original));

                // Detect whether the DB supports REGEXP_SUBSTR (MySQL 8+). If not,
                // we'll fall back to a LENGTH-based ordering to remain portable.
                $useRegexpSubstr = false;
                try {
                    $res = DB::select(DB::raw("SELECT REGEXP_SUBSTR('M 10', '[0-9]+') as r"));
                    if (is_array($res) && count($res) && isset($res[0]->r)) {
                        $useRegexpSubstr = true;
                    }
                } catch (\Throwable $_) {
                    $useRegexpSubstr = false;
                }
                // exact matches (use name/display_name from search_index)
                $exacts = DB::table('search_index')
                    ->select(['name', 'display_name', 'source_table', 'source_pk', 'source_type'])
                    ->whereRaw('LOWER(name) = ?', [$qLower])
                    ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$qNoSpaceLower])
                    ->limit(10)
                    ->get();
                $addRows($exacts, 0);

                if (count($results) < $maxCandidates) {
                    $remaining = $maxCandidates - count($results);
                    $prefixQuery = DB::table('search_index')
                        ->select(['name', 'display_name', 'source_table', 'source_pk', 'source_type'])
                        ->whereRaw('LOWER(name) LIKE ?', [Str::lower($likeStarts)]);
                    if (!empty($isCatalogQuery)) {
                        if ($useRegexpSubstr) {
                            $prefixQuery->orderByRaw("IFNULL(CAST(REGEXP_SUBSTR(name, '[0-9]+') AS UNSIGNED), 999999) ASC, LENGTH(name) ASC, name ASC");
                        } else {
                            // Fallback: prefer shorter names which tends to push
                            // single/two-digit catalog entries earlier.
                            $prefixQuery->orderByRaw('LENGTH(name) ASC, name ASC');
                        }
                    }
                    $prefixRows = $prefixQuery->limit($remaining)->get();
                    $addRows($prefixRows, 1);
                }

                // Debug logging removed for QuickSearch raw samples

                if ($isContains && count($results) < $maxCandidates) {
                    $remaining = $maxCandidates - count($results);
                    $containsQuery = DB::table('search_index')
                        ->select(['name', 'display_name', 'source_table', 'source_pk', 'source_type'])
                        ->whereRaw('LOWER(name) LIKE ?', [Str::lower($likeContains)]);
                    if (!empty($isCatalogQuery)) {
                        if ($useRegexpSubstr) {
                            $containsQuery->orderByRaw("IFNULL(CAST(REGEXP_SUBSTR(name, '[0-9]+') AS UNSIGNED), 999999) ASC, LENGTH(name) ASC, name ASC");
                        } else {
                            $containsQuery->orderByRaw('LENGTH(name) ASC, name ASC');
                        }
                    }
                    $containsRows = $containsQuery->limit($remaining)->get();
                    $addRows($containsRows, 2);
                }

                // Debug logging removed for QuickSearch gathered candidates
            }
        } catch (\Throwable $_) {
            // ignore schema/availability errors and fallback below
        }

        // Fallback to objects + aliases if search_index not present or insufficient
        if (count($results) < $maxCandidates) {
            // objects exact
            $exacts = DB::table('objects')
                ->select(['name as title', 'slug', DB::raw("'object' as type")])
                ->whereRaw('LOWER(name) = ?', [$qLower])
                ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$qNoSpaceLower])
                ->limit(10)
                ->get();
            $addRows($exacts, 0);

            if (count($results) < $maxCandidates) {
                $remaining = $maxCandidates - count($results);
                $prefixQueryObj = DB::table('objects')
                    ->select(['name as title', 'slug', DB::raw("'object' as type")])
                    ->whereRaw('LOWER(name) LIKE ?', [Str::lower($likeStarts)]);
                if (!empty($isCatalogQuery)) {
                    if ($useRegexpSubstr) {
                        $prefixQueryObj->orderByRaw("IFNULL(CAST(REGEXP_SUBSTR(name, '[0-9]+') AS UNSIGNED), 999999) ASC, LENGTH(name) ASC, name ASC");
                    } else {
                        $prefixQueryObj->orderByRaw('LENGTH(name) ASC, name ASC');
                    }
                }
                $prefix = $prefixQueryObj->limit($remaining)->get();
                $addRows($prefix, 1);
            }

            if ($isContains && count($results) < $maxCandidates) {
                $remaining = $maxCandidates - count($results);
                $containsQueryObj = DB::table('objects')
                    ->select(['name as title', 'slug', DB::raw("'object' as type")])
                    ->whereRaw('LOWER(name) LIKE ?', [Str::lower($likeContains)]);
                if (!empty($isCatalogQuery)) {
                    if ($useRegexpSubstr) {
                        $containsQueryObj->orderByRaw("IFNULL(CAST(REGEXP_SUBSTR(name, '[0-9]+') AS UNSIGNED), 999999) ASC, LENGTH(name) ASC, name ASC");
                    } else {
                        $containsQueryObj->orderByRaw('LENGTH(name) ASC, name ASC');
                    }
                }
                $contains = $containsQueryObj->limit($remaining)->get();
                $addRows($contains, 2);
            }

            // aliases
            try {
                if (DB::getSchemaBuilder()->hasTable('objectnames')) {
                    $remaining = max(0, $maxCandidates - count($results));
                    if ($remaining > 0) {
                        $aliasQuery = DB::table('objectnames')
                            ->join('objects', 'objectnames.object_id', '=', 'objects.id')
                            ->select(['objects.name as title', 'objects.slug', DB::raw("'object' as type")])
                            ->whereRaw('LOWER(objectnames.alias) = ?', [Str::lower($original)])
                            ->orWhereRaw("LOWER(REPLACE(objectnames.alias, ' ', '')) = ?", [Str::lower($qNoSpace)]);

                        $aliasQuery->orWhereRaw('LOWER(objectnames.alias) LIKE ?', [Str::lower($likeStarts)]);
                        if ($isContains) {
                            $aliasQuery->orWhereRaw('LOWER(objectnames.alias) LIKE ?', [Str::lower($likeContains)]);
                        }

                        $aliases = $aliasQuery->groupBy('objects.id', 'objects.name', 'objects.slug')
                            ->limit($remaining)
                            ->get();
                        $addRows($aliases, 1);
                    }
                }
            } catch (\Throwable $_) {
                // ignore
            }
        }

        // Now we have up to $maxCandidates candidates with match_rank annotated
        // Resolve canonical keys and deduplicate candidates BEFORE sorting so aliases
        // and duplicate index rows collapse into a single candidate (e.g., Copernicus)
        $seenKeys = [];
        $dedupCandidates = [];

        // Helper to compute a canonical key for a candidate row. Ensures we
        // consistently use resolved table:id keys (e.g., objects:123) when
        // available so later reinsertion checks match the same identity.
        $canonicalKey = function ($r) {
            // Attempt to resolve table:id when source_table/source_pk provided
            try {
                if (!empty($r->source_table) && !empty($r->source_pk)) {
                    $st = $r->source_table;
                    $sp = $r->source_pk;
                    switch ($st) {
                        case 'objects':
                            // The `objects` table in this schema uses `name`/`slug` as keys
                            // (no numeric `id` column). Prefer slug and exact name
                            // lookups, then fall back to normalized variants.
                            $obj = DB::table('objects')
                                ->where('slug', $sp)
                                ->orWhere('name', $sp)
                                ->first();
                            if (!$obj) {
                                $norm = Str::lower(preg_replace('/\s+/', '', (string) $sp));
                                $obj = DB::table('objects')
                                    ->whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [$norm])
                                    ->orWhereRaw('LOWER(REPLACE(slug, "-", "")) = ?', [str_replace('-', '', $norm)])
                                    ->first();
                            }
                            if ($obj)
                                return 'objects:' . ($obj->slug ?? $obj->name);
                            break;
                        case 'planets':
                            $p = DB::table('planets')
                                ->where('id', $sp)
                                ->orWhere('name', $sp)
                                ->orWhere('slug', $sp)
                                ->first();
                            if ($p)
                                return 'planets:' . $p->id;
                        case 'moons':
                            $m = DB::table('moons')
                                ->where('id', $sp)
                                ->orWhere('name', $sp)
                                ->orWhere('slug', $sp)
                                ->first();
                            if ($m)
                                return 'moons:' . $m->id;
                            break;
                        case 'cometobjects':
                            $c = DB::table('cometobjects')
                                ->where('id', $sp)
                                ->orWhere('name', $sp)
                                ->orWhere('slug', $sp)
                                ->first();
                            if ($c)
                                return 'cometobjects:' . $c->id;
                            break;
                        case 'lunar_features':
                            $lf = DB::table('lunar_features')
                                ->where('id', $sp)
                                ->orWhere('name', $sp)
                                ->orWhere('slug', $sp)
                                ->first();
                            if ($lf)
                                return 'lunar_features:' . $lf->id;
                            break;
                        case 'asteroids':
                            $a = DB::table('asteroids')
                                ->where('id', $sp)
                                ->orWhere('name', $sp)
                                ->orWhere('slug', $sp)
                                ->first();
                            if ($a)
                                return 'asteroids:' . $a->id;
                            break;
                    }
                }
            } catch (\Throwable $_) {
                // ignore resolution errors
            }

            // Try by display_name / name across known tables
            $nameTry = $r->display_name ?? ($r->name ?? null);
            if (!empty($nameTry)) {
                $nameLower = Str::lower(trim($nameTry));
                try {
                    $obj = DB::table('objects')->whereRaw('LOWER(name) = ?', [$nameLower])->first();
                    if ($obj)
                        return 'objects:' . $obj->id;
                    $lf = DB::table('lunar_features')->whereRaw('LOWER(name) = ?', [$nameLower])->first();
                    if ($lf)
                        return 'lunar_features:' . $lf->id;
                    $p = DB::table('planets')->whereRaw('LOWER(name) = ?', [$nameLower])->first();
                    if ($p)
                        return 'planets:' . $p->id;
                    $m = DB::table('moons')->whereRaw('LOWER(name) = ?', [$nameLower])->first();
                    if ($m)
                        return 'moons:' . $m->id;
                    $c = DB::table('cometobjects')->whereRaw('LOWER(name) = ?', [$nameLower])->first();
                    if ($c)
                        return 'cometobjects:' . $c->id;
                    $a = DB::table('asteroids')->whereRaw('LOWER(name) = ?', [$nameLower])->first();
                    if ($a)
                        return 'asteroids:' . $a->id;

                    if (DB::getSchemaBuilder()->hasTable('objectnames')) {
                        $alias = DB::table('objectnames')
                            ->whereRaw('LOWER(altname) = ?', [$nameLower])
                            ->orWhereRaw('LOWER(objectname) = ?', [$nameLower])
                            ->orWhereRaw('LOWER(alias) = ?', [$nameLower])
                            ->first();
                        if ($alias) {
                            $objA = DB::table('objects')->where('id', $alias->object_id)->first();
                            if ($objA)
                                return 'objects:' . $objA->id;
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
            }

            // Fallback to slug or normalized name
            if (!empty($r->slug)) {
                return 'slug:' . $r->slug;
            }
            $n = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', trim($r->display_name ?? ($r->name ?? ($r->title ?? '')))));
            return 'name:' . $n;
        };

        foreach ($results as $r) {
            $key = $canonicalKey($r);
            if (!isset($seenKeys[$key])) {
                $seenKeys[$key] = true;
                $dedupCandidates[] = $r;
            }
        }

        $results = $dedupCandidates;

        // Ensure we don't lose obvious matches from `search_index` (exact or prefix rows)
        // — if any search_index row exactly matches the normalized query but was
        // deduplicated away, re-insert it at the front so users see it.
        $mustInsert = [];
        try {
            $candidatesToCheck = [];
            if (isset($exacts) && is_iterable($exacts)) {
                foreach ($exacts as $r)
                    $candidatesToCheck[] = $r;
            }
            if (isset($prefixRows) && is_iterable($prefixRows)) {
                foreach ($prefixRows as $r)
                    $candidatesToCheck[] = $r;
            }

            foreach ($candidatesToCheck as $r) {
                $d = strtolower(trim((string) ($r->display_name ?? $r->name ?? '')));
                $nosp = strtolower(trim(preg_replace('/\s+/', '', (string) ($r->display_name ?? $r->name ?? ''))));
                if ($d === $qLower || $nosp === $qNoSpaceLower) {
                    // compute canonical key using the same helper as dedup
                    try {
                        $key = $canonicalKey($r);
                    } catch (\Throwable $_) {
                        $key = null;
                    }

                    // if not present in current results, prepend the row
                    $present = false;
                    foreach ($results as $ex) {
                        try {
                            $exKey = $canonicalKey($ex);
                        } catch (\Throwable $_) {
                            $exKey = null;
                        }
                        if ($exKey === $key) {
                            $present = true;
                            break;
                        }
                    }
                    if (!$present) {
                        array_unshift($results, $r);
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }

        // Prepare logging arrays if debug enabled
        $titlesBefore = array_map(function ($r) {
            return (string) ($r->display_name ?? ($r->name ?? ($r->title ?? ''))); }, $results);

        // Tokenize helper: split into sequence of numeric and non-numeric tokens
        $tokenize = function ($s) {
            $s = (string) $s;
            $s = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $s));
            $parts = preg_split('/(\d+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $tokens = [];
            foreach ($parts as $p) {
                $p = trim((string) $p);
                if ($p === '') {
                    continue;
                }
                if (preg_match('/^\d+$/', $p)) {
                    $tokens[] = (int) $p;
                } else {
                    $tokens[] = $p;
                }
            }
            return $tokens;
        };

        $compareTokens = function ($ta, $tb) use ($tokenize) {
            $na = $ta;
            $nb = $tb;
            $la = count($na);
            $lb = count($nb);
            $min = min($la, $lb);
            for ($i = 0; $i < $min; $i++) {
                $a = $na[$i];
                $b = $nb[$i];
                if (is_int($a) && is_int($b)) {
                    if ($a !== $b)
                        return $a < $b ? -1 : 1;
                } elseif (is_int($a) && !is_int($b)) {
                    return -1; // numbers before text
                } elseif (!is_int($a) && is_int($b)) {
                    return 1;
                } else {
                    $cmp = strnatcasecmp($a, $b);
                    if ($cmp !== 0)
                        return $cmp < 0 ? -1 : 1;
                }
            }
            // shorter token list first
            if ($la !== $lb)
                return $la < $lb ? -1 : 1;
            return 0;
        };

        usort($results, function ($a, $b) use ($tokenize, $compareTokens) {
            // first by match rank (exact < prefix < contains)
            $ra = $a->match_rank ?? 1;
            $rb = $b->match_rank ?? 1;
            if ($ra !== $rb)
                return $ra < $rb ? -1 : 1;

            $ta = $tokenize($a->display_name ?? ($a->name ?? ''));
            $tb = $tokenize($b->display_name ?? ($b->name ?? ''));
            $tcmp = $compareTokens($ta, $tb);
            if ($tcmp !== 0)
                return $tcmp < 0 ? -1 : 1;

            // final fallback
            return strnatcasecmp($a->title ?? '', $b->title ?? '');
        });

        $titlesAfter = array_map(function ($r) {
            return (string) ($r->display_name ?? ($r->name ?? ($r->title ?? ''))); }, $results);

        // Debug logging removed for QuickSearch suggestions before/after

        // Limit to top 10 suggestions for UI
        $top = array_slice($results, 0, 10);

        $this->suggestions = array_map(function ($row) {
            $type = $row->source_type ?? ($row->type ?? 'object');
            if (strtolower(trim($type)) === 'object') {
                $typeLabel = 'Deep-Sky Object';
            } else {
                $typeLabel = ucfirst(str_replace('_', ' ', $type));
            }

            // Resolve slug and real type for routing/labels where possible
            // initialize slug from any available field (search_index or fallback)
            $slug = $row->slug ?? ($row->source_pk ?? null);
            $resolvedType = $row->source_type ?? ($row->type ?? 'object');
            try {
                if (!empty($row->source_table) && !empty($row->source_pk)) {
                    switch ($row->source_table) {
                        case 'objects':
                            // Try lookup by exact name or slug
                            $obj = DB::table('objects')->where('name', $row->source_pk)->orWhere('slug', $row->source_pk)->first();
                            if (!$obj) {
                                // Try normalized lookups: remove spaces from name, and strip dashes from slug
                                $spNorm = Str::lower(preg_replace('/\s+/', '', (string) ($row->source_pk ?? '')));
                                if ($spNorm !== '') {
                                    $obj = DB::table('objects')
                                        ->whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [$spNorm])
                                        ->orWhereRaw('LOWER(REPLACE(slug, "-", "")) = ?', [str_replace('-', '', $spNorm)])
                                        ->first();
                                }
                            }
                            // If this row is an alias and we still couldn't find the object
                            // by the alias slug/name, try resolving via `objectnames` to
                            // the canonical object name and then load that object.
                            if (!$obj && isset($row->source_type) && strtolower($row->source_type) === 'alias') {
                                try {
                                    $on = DB::table('objectnames')
                                        ->where('slug', $row->source_pk)
                                        ->orWhere('altname', $row->display_name)
                                        ->orWhere('objectname', $row->display_name)
                                        ->first();
                                    if ($on && !empty($on->objectname)) {
                                        $obj = DB::table('objects')
                                            ->whereRaw('LOWER(name) = ?', [Str::lower($on->objectname)])
                                            ->orWhere('slug', $on->slug)
                                            ->first();
                                    }
                                } catch (\Throwable $_) {
                                    // ignore
                                }
                            }

                            if ($obj) {
                                $slug = $obj->slug ?? $obj->name;
                                $resolvedType = $obj->type ?? $resolvedType;
                            } else {
                                // try by display_name or name fields if available
                                $tryName = $row->name ?? $row->display_name ?? null;
                                if (!empty($tryName)) {
                                    $obj2 = DB::table('objects')
                                        ->whereRaw('LOWER(name) = ?', [Str::lower($tryName)])
                                        ->orWhereRaw('LOWER(REPLACE(name, " ", "")) = ?', [Str::lower(preg_replace('/\s+/', '', $tryName))])
                                        ->first();
                                    if ($obj2) {
                                        $slug = $obj2->slug ?? $obj2->name;
                                        $resolvedType = $obj2->type ?? $resolvedType;
                                    }
                                }
                            }
                            break;
                        case 'planets':
                            $p = DB::table('planets')->where('id', $row->source_pk)->first();
                            if ($p) {
                                $slug = $p->slug ?? $p->name;
                                $resolvedType = $p->body_type ?? $resolvedType;
                            }
                            break;
                        case 'moons':
                            $m = DB::table('moons')->where('id', $row->source_pk)->first();
                            if ($m) {
                                $slug = $m->slug ?? $m->name;
                                $resolvedType = $m->body_type ?? $resolvedType;
                            }
                            break;
                        case 'cometobjects':
                            $c = DB::table('cometobjects')->where('id', $row->source_pk)->first();
                            if ($c) {
                                $slug = $c->slug ?? $c->name;
                                $resolvedType = 'comet';
                            }
                            break;
                        case 'lunar_features':
                            $lf = DB::table('lunar_features')->where('id', $row->source_pk)->first();
                            if ($lf) {
                                $slug = $lf->slug ?? $lf->name;
                                $resolvedType = $lf->feature_type ?? $resolvedType;
                            }
                            break;
                        case 'asteroids':
                            $a = DB::table('asteroids')->where('id', $row->source_pk)->first();
                            if ($a) {
                                $slug = $a->slug ?? $a->name;
                                $resolvedType = $a->body_type ?? $resolvedType;
                            }
                            break;
                    }
                }
            } catch (\Throwable $_) {
                // ignore resolution errors
            }

            // If we still have a generic 'object' resolved type but a slug
            // is available (from fallback queries), try to load the object by
            // slug to obtain the actual `type` code stored on the object.
            if ((!$resolvedType || strtolower(trim($resolvedType)) === 'object') && !empty($slug)) {
                try {
                    $objBySlug = DB::table('objects')->where('slug', $slug)->first();
                    if ($objBySlug && !empty($objBySlug->type)) {
                        $resolvedType = $objBySlug->type;
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
            }

            // If resolvedType looks like an uppercase code (<=6 chars), try to resolve via DeepskyType
            $typeLabel = $resolvedType;
            if (!empty($typeLabel) && strlen($typeLabel) <= 6 && $typeLabel === strtoupper($typeLabel)) {
                $code = strtoupper($typeLabel);
                if (!isset($this->cachedTypeNames[$code])) {
                    try {
                        $typeModel = \App\Models\DeepskyType::find($code);
                        if ($typeModel && !empty($typeModel->name) && $typeModel->name !== $code) {
                            $this->cachedTypeNames[$code] = $typeModel->name;
                        } else {
                            $this->cachedTypeNames[$code] = $code;
                        }
                    } catch (\Throwable $_) {
                        $this->cachedTypeNames[$code] = $code;
                    }
                }
                $typeLabel = $this->cachedTypeNames[$code];
            }

            if (strtolower(trim($typeLabel)) === 'object') {
                $typeLabel = 'Deep-Sky Object';
            } else {
                $typeLabel = ucfirst(str_replace('_', ' ', $typeLabel));
            }

            return [
                'title' => $row->display_name ?? ($row->name ?? ($row->title ?? '')),
                'slug' => $slug,
                'type' => $typeLabel,
            ];
        }, $top);

        // Reset selection when suggestions update
        $this->selectedIndex = -1;

        // Deduplicate final suggestions: prefer slug identity first, then collapse
        // identical normalized titles so users don't see the same name multiple times.
        $seen = [];
        $titleIndex = []; // map titleKey -> index in deduped
        $deduped = [];
        foreach ($this->suggestions as $s) {
            $slugKey = $s['slug'] ? ('slug:' . $s['slug']) : null;
            $titleKey = 'title:' . strtolower(trim(preg_replace('/\s+/', ' ', $s['title'])));

            // Skip if we've already seen this exact slug
            if ($slugKey && isset($seen[$slugKey])) {
                continue;
            }

            // If we've already emitted a suggestion with the same normalized title,
            // prefer the one that has a non-generic type label (not 'Deep-Sky Object')
            if (isset($titleIndex[$titleKey])) {
                $idx = $titleIndex[$titleKey];
                $existing = $deduped[$idx];
                $existingIsGeneric = (strtolower(trim($existing['type'])) === 'deep-sky object' || strtolower(trim($existing['type'])) === 'object');
                $newIsGeneric = (strtolower(trim($s['type'])) === 'deep-sky object' || strtolower(trim($s['type'])) === 'object');
                // If existing is generic and new is specific, replace it.
                if ($existingIsGeneric && !$newIsGeneric) {
                    // replace and update slug seen map
                    if (!empty($existing['slug']))
                        unset($seen['slug:' . $existing['slug']]);
                    $deduped[$idx] = $s;
                    if ($slugKey)
                        $seen[$slugKey] = true;
                }
                // otherwise skip the new one
                continue;
            }

            if ($slugKey) {
                $seen[$slugKey] = true;
            }
            $titleIndex[$titleKey] = count($deduped);
            $deduped[] = $s;
        }
        $this->suggestions = $deduped;
        $this->showSuggestions = count($this->suggestions) > 0;

        // Debug logging removed for QuickSearch final suggestion types
    }

    public function closeSuggestions()
    {
        $this->showSuggestions = false;
        $this->selectedIndex = -1;
    }

    public function moveSelection($dir = 1)
    {
        $count = count($this->suggestions);
        if ($count === 0) {
            $this->selectedIndex = -1;
            return;
        }

        $i = $this->selectedIndex ?? -1;
        if ($dir > 0) {
            // move down, wrap to 0 if at end
            if ($i < 0) {
                $i = 0;
            } elseif ($i < $count - 1) {
                $i++;
            } else {
                $i = 0;
            }
        } else {
            // move up, wrap to last if at top or none
            if ($i < 0) {
                $i = $count - 1;
            } elseif ($i > 0) {
                $i--;
            } else {
                $i = $count - 1;
            }
        }
        $this->selectedIndex = $i;
    }

    public function enterPressed()
    {
        if (($this->selectedIndex ?? -1) >= 0 && isset($this->suggestions[$this->selectedIndex])) {
            $slug = $this->suggestions[$this->selectedIndex]['slug'] ?? null;
            if ($slug) {
                return redirect()->route('object.show', ['slug' => $slug]);
            }
        }

        return $this->submit();
    }

    public function selectSuggestion($slug)
    {
        return redirect()->route('object.show', ['slug' => $slug]);
    }

    public function submit()
    {
        $original = trim($this->query ?? '');
        if ($original === '') {
            return redirect()->route('search.results', ['q' => $this->query]);
        }

        $qLower = Str::lower($original);
        $qNoSpaceLower = Str::lower(preg_replace('/\s+/', '', $original));

        // Try to resolve an exact object (by name, slug, or alias) before
        // falling back to the suggestion list.
        try {
            $obj = DB::table('objects')
                ->whereRaw('LOWER(name) = ?', [$qLower])
                ->orWhereRaw('LOWER(REPLACE(name, " ", "")) = ?', [$qNoSpaceLower])
                ->orWhere('slug', $original)
                ->first();

            if (!$obj && DB::getSchemaBuilder()->hasTable('objectnames')) {
                $alias = DB::table('objectnames')
                    ->whereRaw('LOWER(altname) = ?', [$qLower])
                    ->orWhereRaw('LOWER(objectname) = ?', [$qLower])
                    ->orWhereRaw('LOWER(alias) = ?', [$qLower])
                    ->orWhereRaw('LOWER(REPLACE(alias, " ", "")) = ?', [$qNoSpaceLower])
                    ->first();
                if ($alias && !empty($alias->object_id)) {
                    $obj = DB::table('objects')->where('id', $alias->object_id)->first();
                }
            }
        } catch (\Throwable $_) {
            $obj = null;
        }

        if (!empty($obj)) {
            $slug = $obj->slug ?? $obj->name;
            return redirect()->route('object.show', ['slug' => $slug]);
        }

        // No exact match found — fall back to suggestions / results page
        $this->fetchSuggestions();
        $count = count($this->suggestions);
        if ($count === 1) {
            return redirect()->route('object.show', ['slug' => $this->suggestions[0]['slug']]);
        }

        return redirect()->route('search.results', ['q' => $this->query]);
    }

    public function render()
    {
        return view('livewire.quick-search');
    }
}
