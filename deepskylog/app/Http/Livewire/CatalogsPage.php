<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CatalogsPage extends Component
{
    use WithPagination;

    public $search = '';
    public $selected = '';
    public $perPage = 50;
    public $open = false;

    protected $queryString = ['selected'];

    protected $listeners = [
        'selectCatalog' => 'selectCatalog'
    ];

    public function mount()
    {
        $this->selected = request()->query('catalog', $this->selected);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        // Open the suggestion list when the user types
        $this->open = true;
        $this->resetPage();
    }

    public function selectCatalog($catalog)
    {
        $this->selected = $catalog;
        $this->resetPage();
        $this->open = false;
    }

    public function clearSelection()
    {
        $this->selected = '';
        $this->search = '';
        $this->resetPage();
        $this->open = false;
    }

    public function render()
    {
        // All distinct catalogs for suggestion list (filtered by search)
        $catalogQuery = DB::table('objectnames')
            ->select('catalog')
            ->whereNotNull('catalog')
            ->where('catalog', '!=', '');

        if (trim($this->search) !== '') {
            $catalogQuery->where('catalog', 'like', '%' . $this->search . '%');
        }

        $catalogs = $catalogQuery->distinct()->orderBy('catalog')->limit(200)->pluck('catalog')->toArray();

        // Also include catalogs from the legacy DB if available (some catalogs still live there)
        try {
            $old = DB::connection('mysqlOld')->table('objectnames')
                ->select('catalog')
                ->whereNotNull('catalog')
                ->where('catalog', '!=', '')
                ->distinct()
                ->orderBy('catalog')
                ->limit(200)
                ->pluck('catalog')
                ->toArray();
            $catalogs = array_unique(array_merge($catalogs, $old));
        } catch (\Throwable $_) {
            // ignore if legacy connection not configured
        }

        $catalogs = collect($catalogs)->sort()->values();

        $constellationCounts = collect();
        $typeCounts = collect();
        $objects = null;

        if (!empty($this->selected)) {
            // Resolve catalog variants conservatively: prefer exact (case-insensitive) matches only.
            $catalogVariants = DB::table('objectnames')
                ->whereRaw('LOWER(catalog) = ?', [strtolower($this->selected)])
                ->distinct()
                ->pluck('catalog')
                ->toArray();

            try {
                $oldCats = DB::connection('mysqlOld')->table('objectnames')
                    ->whereRaw('LOWER(catalog) = ?', [strtolower($this->selected)])
                    ->distinct()
                    ->pluck('catalog')
                    ->toArray();
                $catalogVariants = array_unique(array_merge($catalogVariants, $oldCats));
            } catch (\Throwable $_) {
                // ignore
            }

            // Always include the selected literal value as a fallback (covers exact matches).
            if (!in_array($this->selected, $catalogVariants, true)) {
                $catalogVariants[] = $this->selected;
            }
            // Gather slugs from primary objectnames table
            $slugs = DB::table('objectnames')
                ->whereIn('catalog', $catalogVariants)
                ->whereNotNull('slug')
                ->distinct()
                ->pluck('slug')
                ->toArray();

            // Also gather slugs from legacy objectnames if present
            try {
                $oldSlugs = DB::connection('mysqlOld')->table('objectnames')
                    ->whereIn('catalog', $catalogVariants)
                    ->whereNotNull('slug')
                    ->distinct()
                    ->pluck('slug')
                    ->toArray();
                $slugs = array_unique(array_merge($slugs, $oldSlugs));
            } catch (\Throwable $_) {
                // ignore
            }

            // Ensure non-empty and remove null/empty values
            $slugs = array_values(array_filter($slugs, function ($s) {
                return !empty($s);
            }));

            // If slugs are missing for many entries, try a fallback: match objects.name
            // against objectnames.objectname and objectnames.altname from primary and legacy tables.
            try {
                // Collect canonical and alt names from primary
                $names = DB::table('objectnames')
                    ->whereIn('catalog', $catalogVariants)
                    ->pluck('objectname')
                    ->toArray();
                $alt = DB::table('objectnames')
                    ->whereIn('catalog', $catalogVariants)
                    ->pluck('altname')
                    ->toArray();
                $names = array_filter(array_unique(array_merge($names, $alt)));

                // Merge legacy names if available
                try {
                    $oldNames = DB::connection('mysqlOld')->table('objectnames')
                        ->whereIn('catalog', $catalogVariants)
                        ->pluck('objectname')
                        ->toArray();
                    $oldAlt = DB::connection('mysqlOld')->table('objectnames')
                        ->whereIn('catalog', $catalogVariants)
                        ->pluck('altname')
                        ->toArray();
                    $names = array_filter(array_unique(array_merge($names, $oldNames, $oldAlt)));
                } catch (\Throwable $_) {
                    // ignore
                }

                if (!empty($names)) {
                    $foundSlugs = DB::table('objects')
                        ->whereIn('name', $names)
                        ->distinct()
                        ->pluck('slug')
                        ->toArray();

                    if (!empty($foundSlugs)) {
                        $slugs = array_unique(array_merge($slugs, $foundSlugs));
                    }
                }
            } catch (\Throwable $_) {
                // ignore fallback failures
            }

            // If no slugs found, return empty results to avoid invalid SQL
            if (empty($slugs)) {
                $objects = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
                $constellationCounts = collect();
                $typeCounts = collect();
            } else {
                // Constellation counts derived from objects that have any matching objectname (slug)
                $constellationCounts = DB::table('objects')
                    ->leftJoin('constellations', 'objects.con', '=', 'constellations.id')
                    ->whereIn('objects.slug', $slugs)
                    ->select('constellations.id as con_id', 'constellations.name as constellation', DB::raw('COUNT(DISTINCT objects.slug) as total'))
                    ->groupBy('constellations.id', 'constellations.name')
                    ->whereNotNull('constellations.name')
                    ->orderByDesc('total')
                    ->get();

                $typeCounts = DB::table('objects')
                    ->leftJoin('target_types', 'objects.type', '=', 'target_types.id')
                    ->whereIn('objects.slug', $slugs)
                    ->select('target_types.type as type_name', DB::raw('COUNT(DISTINCT objects.slug) as total'))
                    ->groupBy('target_types.type')
                    ->orderByDesc('total')
                    ->get();

                $prefixExpr = "LOWER(REGEXP_SUBSTR(objects.name, '^[^0-9]+'))";
                $numExprAsc = "COALESCE(CAST(REGEXP_SUBSTR(objects.name, '[0-9]+') AS UNSIGNED), 4294967295)";
                // Prefer numeric `oname.catindex` when it starts with digits; otherwise fall back to numeric in objects.name
                $catIndexNumExpr = "CASE WHEN oname.catindex REGEXP '^[0-9]+' THEN CAST(REGEXP_SUBSTR(oname.catindex, '[0-9]+') AS UNSIGNED) WHEN REGEXP_SUBSTR(objects.name, '[0-9]+') IS NOT NULL THEN CAST(REGEXP_SUBSTR(objects.name, '[0-9]+') AS UNSIGNED) ELSE 4294967295 END";

                // Join objectnames on objects.name = oname.objectname (not on slug, which differs between tables)
                $objects = DB::table('objects')
                    ->whereIn('objects.slug', $slugs)
                    ->leftJoin('objectnames as oname', function ($join) use ($catalogVariants) {
                        $join->on('objects.name', '=', 'oname.objectname')
                            ->whereIn('oname.catalog', $catalogVariants);
                    })
                    ->leftJoin('constellations', 'objects.con', '=', 'constellations.id')
                    ->leftJoin('target_types', 'objects.type', '=', 'target_types.id')
                    ->select('objects.slug', 'objects.name', 'constellations.name as constellation', 'target_types.type as type_name', DB::raw('MIN(oname.catindex) as catindex'))
                    ->groupBy('objects.slug', 'objects.name', 'constellations.name', 'target_types.type')
                    // Order by numeric catindex when present, otherwise fall back to natural ordering on objects.name
                    ->orderByRaw("{$catIndexNumExpr} ASC, {$prefixExpr} ASC, {$numExprAsc} ASC, objects.name ASC")
                    ->paginate($this->perPage);
            }
        }

        // Build display names for objects on the current page: prefer catalog-specific
        // objectnames (primary, then legacy). Format: <catalog-specific name> (<main name>).
        $displayNames = [];
        if ($objects && $objects instanceof \Illuminate\Pagination\LengthAwarePaginator && $objects->total() > 0) {
            $pageSlugs = collect($objects->items())->pluck('slug')->toArray();

            if (!empty($pageSlugs)) {
                // Primary objectnames by slug; prefer catalog index for display when available
                $rows = DB::table('objectnames')
                    ->whereIn('catalog', $catalogVariants)
                    ->whereIn('slug', $pageSlugs)
                    ->select('slug', 'objectname', 'altname', 'catindex')
                    ->get();

                foreach ($rows as $r) {
                    $main = $r->objectname ?: $r->altname;
                    if ($r->catindex && strlen(trim($r->catindex)) > 0) {
                        $display = $this->selected . ' ' . trim($r->catindex);
                    } elseif ($main) {
                        // Fall back to using the catalog label + name when no index
                        $cat = $this->selected;
                        if (stripos($main, $cat) === 0) {
                            $display = $main;
                        } else {
                            $display = $cat . ' ' . $main;
                        }
                    } else {
                        continue;
                    }

                    $displayNames[$r->slug] = $display;
                }

                // Legacy objectnames (do not override primary)
                try {
                    $oldRows = DB::connection('mysqlOld')->table('objectnames')
                        ->whereIn('catalog', $catalogVariants)
                        ->whereIn('slug', $pageSlugs)
                        ->select('slug', 'objectname', 'altname', 'catindex')
                        ->get();

                    foreach ($oldRows as $r) {
                        if (isset($displayNames[$r->slug]))
                            continue;
                        $main = $r->objectname ?: $r->altname;
                        if ($r->catindex && strlen(trim($r->catindex)) > 0) {
                            $display = $this->selected . ' ' . trim($r->catindex);
                        } elseif ($main) {
                            $cat = $this->selected;
                            if (stripos($main, $cat) === 0) {
                                $display = $main;
                            } else {
                                $display = $cat . ' ' . $main;
                            }
                        } else {
                            continue;
                        }
                        $displayNames[$r->slug] = $display;
                    }
                } catch (\Throwable $_) {
                    // ignore
                }

                // For any remaining slugs try joining on objects.name -> objectnames.objectname|altname
                $missing = array_values(array_filter($pageSlugs, function ($s) use ($displayNames) {
                    return !isset($displayNames[$s]);
                }));
                if (!empty($missing)) {
                    $joined = DB::table('objects as o')
                        ->join('objectnames as n', function ($join) {
                            $join->on('o.name', '=', 'n.objectname')->orOn('o.name', '=', 'n.altname');
                        })
                        ->whereIn('o.slug', $missing)
                        ->whereIn('n.catalog', $catalogVariants)
                        ->select('o.slug', 'n.objectname', 'n.altname', 'n.catindex')
                        ->get();

                    foreach ($joined as $r) {
                        if (isset($displayNames[$r->slug]))
                            continue;
                        $main = $r->objectname ?: $r->altname;
                        // joined rows may include catindex
                        $catindex = property_exists($r, 'catindex') ? $r->catindex : null;
                        if ($catindex && strlen(trim($catindex)) > 0) {
                            $display = $this->selected . ' ' . trim($catindex);
                        } elseif ($main) {
                            $cat = $this->selected;
                            if (stripos($main, $cat) === 0) {
                                $display = $main;
                            } else {
                                $display = $cat . ' ' . $main;
                            }
                        } else {
                            continue;
                        }
                        $displayNames[$r->slug] = $display;
                    }

                    // legacy join fallback
                    try {
                        $joinedOld = DB::connection('mysqlOld')->table('objects as o')
                            ->join('objectnames as n', function ($join) {
                                $join->on('o.name', '=', 'n.objectname')->orOn('o.name', '=', 'n.altname');
                            })
                            ->whereIn('o.slug', $missing)
                            ->whereIn('n.catalog', $catalogVariants)
                            ->select('o.slug', 'n.objectname', 'n.altname', 'n.catindex')
                            ->get();

                        foreach ($joinedOld as $r) {
                            if (isset($displayNames[$r->slug]))
                                continue;
                            $main = $r->objectname ?: $r->altname;
                            $catindex = property_exists($r, 'catindex') ? $r->catindex : null;
                            if ($catindex && strlen(trim($catindex)) > 0) {
                                $display = $this->selected . ' ' . trim($catindex);
                            } elseif ($main) {
                                $cat = $this->selected;
                                if (stripos($main, $cat) === 0) {
                                    $display = $main;
                                } else {
                                    $display = $cat . ' ' . $main;
                                }
                            } else {
                                continue;
                            }
                            $displayNames[$r->slug] = $display;
                        }
                    } catch (\Throwable $_) {
                        // ignore
                    }
                }

                // Finally, ensure every slug has at least the main object name
                $objsMap = collect($objects->items())->mapWithKeys(function ($o) {
                    return [$o->slug => $o->name];
                })->toArray();
                foreach ($objsMap as $slug => $mainName) {
                    if (!isset($displayNames[$slug]) || empty($displayNames[$slug])) {
                        $displayNames[$slug] = $mainName;
                    } else {
                        $display = $displayNames[$slug];
                        // Do not duplicate when display already contains the main name (case-insensitive)
                        if (stripos($display, $mainName) === false) {
                            $displayNames[$slug] = $display . ' (' . $mainName . ')';
                        } else {
                            $displayNames[$slug] = $display;
                        }
                    }
                }
            }
        }

        return view('livewire.catalogs-page', [
            'catalogs' => $catalogs,
            'constellationCounts' => $constellationCounts,
            'typeCounts' => $typeCounts,
            'objects' => $objects,
            'displayNames' => isset($displayNames) ? $displayNames : [],
        ]);
    }
}
