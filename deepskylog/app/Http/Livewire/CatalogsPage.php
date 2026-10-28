<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

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

    /**
     * The catalog list and the counts of a catalog group the whole
     * objectnames table, so they are cached for an hour.
     */
    private const CACHE_SECONDS = 3600;

    public function render()
    {
        // All distinct catalogs for the suggestion list (filtered by search)
        $search = trim((string) $this->search);
        $catalogs = collect(Cache::remember('catalogs:names', self::CACHE_SECONDS, fn() => DB::table('objectnames')
            ->whereNotNull('catalog')
            ->where('catalog', '!=', '')
            ->distinct()
            ->orderBy('catalog')
            ->pluck('catalog')
            ->all()))
            ->when($search !== '', fn($catalogs) => $catalogs->filter(fn($catalog) => Str::contains((string) $catalog, $search, ignoreCase: true)))
            ->take(200)
            ->sort()
            ->values();

        $constellationCounts = collect();
        $typeCounts = collect();
        $objects = null;
        $catalogVariants = [];

        if (!empty($this->selected)) {
            $catalog = Cache::remember('catalogs:catalog:' . md5(Str::lower((string) $this->selected)), self::CACHE_SECONDS, fn() => $this->catalogData((string) $this->selected));
            $catalogVariants = $catalog['variants'];
            $slugs = $catalog['slugs'];
            $constellationCounts = collect($catalog['constellation_counts']);
            $typeCounts = collect($catalog['type_counts']);

            // If no slugs found, return empty results to avoid invalid SQL
            if (empty($slugs)) {
                $objects = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            } else {
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
        // objectnames. Format: <catalog-specific name> (<main name>).
        $displayNames = [];
        if ($objects && $objects instanceof \Illuminate\Pagination\LengthAwarePaginator && $objects->total() > 0) {
            $pageSlugs = collect($objects->items())->pluck('slug')->toArray();

            if (!empty($pageSlugs)) {
                // Objectnames by slug; prefer catalog index for display when available
                $rows = DB::table('objectnames')
                    ->whereIn('catalog', $catalogVariants)
                    ->whereIn('slug', $pageSlugs)
                    ->select('slug', 'objectname', 'altname', 'catindex')
                    ->get();

                foreach ($rows as $r) {
                    $display = $this->catalogDisplayName($r->objectname ?: $r->altname, $r->catindex);
                    if ($display !== null) {
                        $displayNames[$r->slug] = $display;
                    }
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
                        if (isset($displayNames[$r->slug])) {
                            continue;
                        }
                        $display = $this->catalogDisplayName($r->objectname ?: $r->altname, $r->catindex);
                        if ($display !== null) {
                            $displayNames[$r->slug] = $display;
                        }
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
            'displayNames' => $displayNames,
        ]);
    }

    /**
     * The spellings of the catalog, the slugs of its objects and their counts
     * per constellation and per type.
     *
     * @return array{variants: list<string>, slugs: list<string>, constellation_counts: list<object>, type_counts: list<object>}
     */
    private function catalogData(string $selected): array
    {
        // Resolve catalog variants conservatively: prefer exact (case-insensitive) matches only.
        $catalogVariants = DB::table('objectnames')
            ->where('catalog', $selected)
            ->distinct()
            ->pluck('catalog')
            ->toArray();

        // Always include the selected literal value as a fallback (covers exact matches).
        if (!in_array($selected, $catalogVariants, true)) {
            $catalogVariants[] = $selected;
        }
        // Gather slugs from primary objectnames table
        $slugs = DB::table('objectnames')
            ->whereIn('catalog', $catalogVariants)
            ->whereNotNull('slug')
            ->distinct()
            ->pluck('slug')
            ->toArray();

        // Ensure non-empty and remove null/empty values
        $slugs = array_values(array_filter($slugs, function ($s) {
            return !empty($s);
        }));

        // If slugs are missing for many entries, try a fallback: match objects.name
        // against objectnames.objectname and objectnames.altname.
        try {
            $names = DB::table('objectnames')
                ->whereIn('catalog', $catalogVariants)
                ->pluck('objectname')
                ->toArray();
            $alt = DB::table('objectnames')
                ->whereIn('catalog', $catalogVariants)
                ->pluck('altname')
                ->toArray();
            $names = array_filter(array_unique(array_merge($names, $alt)));

            if (!empty($names)) {
                $foundSlugs = DB::table('objects')
                    ->whereIn('name', $names)
                    ->distinct()
                    ->pluck('slug')
                    ->toArray();

                if (!empty($foundSlugs)) {
                    $slugs = array_values(array_unique(array_merge($slugs, $foundSlugs)));
                }
            }
        } catch (\Throwable $_) {
            // ignore fallback failures
        }

        if (empty($slugs)) {
            return ['variants' => $catalogVariants, 'slugs' => [], 'constellation_counts' => [], 'type_counts' => []];
        }

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

        return [
            'variants' => $catalogVariants,
            'slugs' => $slugs,
            'constellation_counts' => $constellationCounts->all(),
            'type_counts' => $typeCounts->all(),
        ];
    }

    /**
     * The name of an object in the selected catalog: the catalog with the index when there is one,
     * else the name, prefixed with the catalog when it doesn't start with it.
     */
    private function catalogDisplayName(?string $main, ?string $catindex): ?string
    {
        if ($catindex !== null && trim($catindex) !== '') {
            return $this->selected . ' ' . trim($catindex);
        }

        if (!$main) {
            return null;
        }

        return stripos($main, $this->selected) === 0 ? $main : $this->selected . ' ' . $main;
    }
}
