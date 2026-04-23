<?php

namespace App\Livewire;

use App\Models\SearchIndex;
use App\Models\Atlas;
use App\Services\ActiveObservingListService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use App\Jobs\ComputeContrastReserveForObject;

#[Lazy]
class AdvancedObjectSearchTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'advanced-search-results-table';
    public string $primaryKey = 'search_index_id';
    public string $sortField = 'display_name';
    public bool $showAddColumn = true;

    // ── Filter parameters passed from the results page ────────────────────
    public array $constellations = [];
    public array $objectTypes = [];
    public array $objectCategories = [];
    public array $catalogsInclude = [];
    public array $catalogsExclude = [];
    public string $magMin = '';
    public string $magMax = '';
    public string $subrMin = '';
    public string $subrMax = '';
    public string $diam1Min = '';
    public string $diam1Max = '';
    public string $diam2Min = '';
    public string $diam2Max = '';
    public string $ratioMin = '';
    public string $ratioMax = '';
    public string $contrastReserveMin = '';
    public string $contrastReserveMax = '';
    public string $raMin = '';
    public string $raMax = '';
    public string $declMin = '';
    public string $declMax = '';
    public string $observingStatus = 'all';
    public array $observingLists = [];
    public string $observingListsMode = 'in';
    public array $nameSearchCatalogs = [];
    public string $nameSearchNumber = '';
    public string $nameText = '';
    public string $descriptionText = '';
    public string $descriptionMode = 'and';
    public string $atlasCode = '';
    public string $atlasPageSpec = '';
    public string $atlasPageMin = '';
    public string $atlasPageMax = '';

    // ── CR / best-mag dedup flags ─────────────────────────────────────────
    public bool $bestMagUpsertedThisLoad = false;
    public bool $crUpsertedThisLoad = false;
    public bool $hasPendingCalculations = false;
    private ?bool $canModifyActiveListCached = null;

    private ?array $cachedEyepieces = null;
    public int $refreshTick = 0;

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function mount(array $filters = [], ?bool $showAddColumn = null): void
    {
        parent::mount();

        if ($showAddColumn !== null) {
            $this->showAddColumn = $showAddColumn;
        }

        $this->constellations = array_map('strval', (array) ($filters['constellations'] ?? []));
        $this->objectTypes = array_map('strval', (array) ($filters['object_types'] ?? []));
        $this->objectCategories = array_map('strval', (array) ($filters['object_categories'] ?? []));
        $this->catalogsInclude = array_map('strval', (array) ($filters['catalogs_include'] ?? []));
        $this->catalogsExclude = array_map('strval', (array) ($filters['catalogs_exclude'] ?? []));
        $this->magMin = (string) ($filters['mag_min'] ?? '');
        $this->magMax = (string) ($filters['mag_max'] ?? '');
        $this->subrMin = (string) ($filters['subr_min'] ?? '');
        $this->subrMax = (string) ($filters['subr_max'] ?? '');
        $this->diam1Min = (string) ($filters['diam1_min'] ?? '');
        $this->diam1Max = (string) ($filters['diam1_max'] ?? '');
        $this->diam2Min = (string) ($filters['diam2_min'] ?? '');
        $this->diam2Max = (string) ($filters['diam2_max'] ?? '');
        $this->ratioMin = (string) ($filters['ratio_min'] ?? '');
        $this->ratioMax = (string) ($filters['ratio_max'] ?? '');
        $this->contrastReserveMin = (string) ($filters['cr_min'] ?? '');
        $this->contrastReserveMax = (string) ($filters['cr_max'] ?? '');
        $this->raMin = (string) ($filters['ra_min'] ?? '');
        $this->raMax = (string) ($filters['ra_max'] ?? '');
        $this->declMin = (string) ($filters['decl_min'] ?? '');
        $this->declMax = (string) ($filters['decl_max'] ?? '');
        $this->observingStatus = (string) ($filters['observing_status'] ?? 'all');
        $this->observingLists = array_values(array_filter(array_map('strval', (array) ($filters['observing_lists'] ?? []))));
        $listsMode = (string) ($filters['observing_lists_mode'] ?? 'in');
        $this->observingListsMode = in_array($listsMode, ['in', 'not_in'], true) ? $listsMode : 'in';

        $rawNameCatalogs = $filters['name_search_catalogs'] ?? ($filters['name_search_catalog'] ?? []);
        if (!is_array($rawNameCatalogs)) {
            $rawNameCatalogs = [$rawNameCatalogs];
        }
        $this->nameSearchCatalogs = array_values(array_filter(array_map('strval', $rawNameCatalogs), fn($v) => $v !== ''));
        $this->nameSearchNumber = (string) ($filters['name_search_number'] ?? '');
        $this->nameText = (string) ($filters['name_text'] ?? '');
        $this->descriptionText = (string) ($filters['description_text'] ?? '');
        $mode = (string) ($filters['description_mode'] ?? 'and');
        $this->descriptionMode = in_array($mode, ['and', 'or'], true) ? $mode : 'and';
        $this->atlasCode = (string) ($filters['atlas_code'] ?? '');
        $this->atlasPageSpec = (string) ($filters['atlas_page_spec'] ?? '');
        $this->atlasPageMin = (string) ($filters['atlas_page_min'] ?? '');
        $this->atlasPageMax = (string) ($filters['atlas_page_max'] ?? '');
    }

    #[On('advanced-add-all-to-active-list')]
    public function addAllToActiveList(): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->with('error', __('Please login first.'));
        }

        /** @var ActiveObservingListService $svc */
        $svc = app(ActiveObservingListService::class);
        $activeList = $svc->getActiveList($user);

        if (!$activeList) {
            return redirect()->back()->with('error', __('No active observing list set. Please set one first.'));
        }

        if (!$user->can('addItem', $activeList)) {
            return redirect()->back()->with('error', __('You cannot modify this list.'));
        }

        $rows = $this->datasource()?->get() ?? collect();
        $objectNames = $rows
            ->map(function ($row) {
                return trim((string) ($row->obj_name ?? $row->name ?? $row->display_name ?? ''));
            })
            ->filter(fn($name) => $name !== '')
            ->unique()
            ->values();

        $added = 0;
        $addedNames = [];
        foreach ($objectNames as $name) {
            $item = $activeList->items()->firstOrCreate(
                ['object_name' => $name],
                [
                    'source_mode' => 'manual',
                    'added_by_user_id' => $user->id,
                ]
            );
            if ($item->wasRecentlyCreated) {
                $added++;
                $addedNames[] = $name;
            }
        }

        if (count($addedNames) === 1) {
            return redirect()->back()->with('success', __(':object added to :list.', [
                'object' => $addedNames[0],
                'list' => $activeList->name,
            ]));
        }

        return redirect()->back()->with('success', __(':count object(s) added to :list.', [
            'count' => $added,
            'list' => $activeList->name,
        ]));
    }

    public function setUp(): array
    {
        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::header()->showSearchInput()->showToggleColumns(),
            PowerGrid::footer()->showPerPage(25)->showRecordCount(),
            PowerGrid::responsive()->fixedColumns('display_name'),
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
        ];
    }

    public function rendered(): void
    {
        $listId = $this->currentObservingListId();
        if ($listId === null) {
            return;
        }

        $this->dispatch(
            'observing-list-table-visible-objects-updated',
            listId: $listId,
            objectNames: $this->currentVisibleObjectNames()
        );
    }

    public function datasource(): ?Builder
    {
        // ── Allowed source_table values ──────────────────────────────────────
        $allowedCategories = ['objects', 'cometobjects', 'planets', 'moons', 'asteroids', 'lunar_features'];

        // Determine which categories to include
        $categories = array_values(array_intersect($this->objectCategories, $allowedCategories));

        // ── Pre-fetch observing list object names (before building $sub) ─────────────
        // Fetching object names from new observing_list_items table.
        // null = no filter; 'zero' = match nothing; [$mode, $names] = filter.
        $preloadedListFilter = null;
        if (!empty($this->observingLists)) {
            $listIds = array_filter(array_map(function ($entry) {
                $id = (int) $entry;
                return $id > 0 ? $id : null;
            }, $this->observingLists));

            if (empty($listIds)) {
                $preloadedListFilter = 'zero';
            } else {
                // Query new observing_list_items table for object names in selected lists
                $listNames = DB::table('observing_list_items')
                    ->whereIn('observing_list_id', $listIds)
                    ->distinct()
                    ->pluck('object_name')
                    ->toArray();

                if (empty($listNames)) {
                    $preloadedListFilter = 'zero';
                } else {
                    $preloadedListFilter = [$this->observingListsMode, $listNames];
                }
            }
        }

        // ── Pre-fetch object slugs for objects-table filters ──────────────────
        // Filters on objects columns (type, constellation, mag, subr, diam, ratio)
        // are applied on the outer $query after a full-scan derived table. By pre-
        // fetching matching slugs and adding whereIn('source_pk', $slugs) on $sub
        // BEFORE the GROUP BY, MySQL can use search_index_source_pk_index instead
        // of scanning all 83K rows.
        $hasObjectsFilter = (
            !empty($this->objectTypes) ||
            !empty($this->constellations) ||
            ($this->magMin !== '' && is_numeric($this->magMin)) ||
            ($this->magMax !== '' && is_numeric($this->magMax)) ||
            ($this->subrMin !== '' && is_numeric($this->subrMin)) ||
            ($this->subrMax !== '' && is_numeric($this->subrMax)) ||
            ($this->diam1Min !== '' && is_numeric($this->diam1Min)) ||
            ($this->diam1Max !== '' && is_numeric($this->diam1Max)) ||
            ($this->diam2Min !== '' && is_numeric($this->diam2Min)) ||
            ($this->diam2Max !== '' && is_numeric($this->diam2Max)) ||
            ($this->ratioMin !== '' && is_numeric($this->ratioMin)) ||
            ($this->ratioMax !== '' && is_numeric($this->ratioMax))
        );

        $preloadedObjectSlugs = null; // null = no filter; [] = match nothing; array = restrict to these slugs
        if ($hasObjectsFilter) {
            $objQ = DB::table('objects');

            if (!empty($this->objectTypes)) {
                $objQ->whereIn('type', array_map('strval', $this->objectTypes));
            }
            if (!empty($this->constellations)) {
                $objQ->whereIn('con', array_map('strval', $this->constellations));
            }
            if ($this->magMin !== '' && is_numeric($this->magMin)) {
                $objQ->where('mag', '>=', floatval($this->magMin));
            }
            if ($this->magMax !== '' && is_numeric($this->magMax)) {
                $objQ->where('mag', '<=', floatval($this->magMax));
            }
            if ($this->subrMin !== '' && is_numeric($this->subrMin)) {
                $objQ->where('subr', '>=', floatval($this->subrMin));
            }
            if ($this->subrMax !== '' && is_numeric($this->subrMax)) {
                $objQ->where('subr', '<=', floatval($this->subrMax));
            }
            if ($this->diam1Min !== '' && is_numeric($this->diam1Min)) {
                $objQ->where('diam1', '>=', floatval($this->diam1Min));
            }
            if ($this->diam1Max !== '' && is_numeric($this->diam1Max)) {
                $objQ->where('diam1', '<=', floatval($this->diam1Max));
            }
            if ($this->diam2Min !== '' && is_numeric($this->diam2Min)) {
                $objQ->where('diam2', '>=', floatval($this->diam2Min));
            }
            if ($this->diam2Max !== '' && is_numeric($this->diam2Max)) {
                $objQ->where('diam2', '<=', floatval($this->diam2Max));
            }
            if (($this->ratioMin !== '' && is_numeric($this->ratioMin)) || ($this->ratioMax !== '' && is_numeric($this->ratioMax))) {
                $objQ->whereNotNull('diam1')->where('diam1', '>', 0)
                    ->whereNotNull('diam2')->where('diam2', '>', 0);
                if ($this->ratioMin !== '' && is_numeric($this->ratioMin)) {
                    $objQ->whereRaw('(diam1 / diam2) >= ?', [floatval($this->ratioMin)]);
                }
                if ($this->ratioMax !== '' && is_numeric($this->ratioMax)) {
                    $objQ->whereRaw('(diam1 / diam2) <= ?', [floatval($this->ratioMax)]);
                }
            }

            $preloadedObjectSlugs = $objQ->pluck('slug')->toArray();
        }

        // Build a subquery that groups search_index rows by canonical name.
        // When categories are specified we restrict to those source_tables;
        // otherwise we include all source_tables.
        $sub = DB::table('search_index')
            ->where('source_type', '<>', 'alias')
            ->selectRaw(
                'MIN(id) as search_index_id, '
                . "LOWER(COALESCE(name, display_name)) as canonical_name_norm, "
                . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN source_table END), MIN(source_table)) as source_table, "
                . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN source_pk END), MIN(source_pk)) as source_pk, "
                . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN display_name END), COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN display_name END), MIN(display_name))) as display_name, "
                . "COALESCE(MIN(CASE WHEN source_table = 'objects' THEN name END), COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN name END), MIN(name))) as name, "
                . 'MAX(ra) as ra, MAX(decl) as decl, '
                . "COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN source_type END), MIN(source_type)) as source_type, "
                . "COALESCE(MIN(CASE WHEN source_type <> 'alias' THEN metadata END), MIN(metadata)) as metadata"
            );

        if (!empty($categories)) {
            $sub->whereIn('source_table', $categories);
        }

        // ── Catalog include/exclude via objectnames ───────────────────────────
        if (!empty($this->catalogsInclude)) {
            $include = array_map('strval', $this->catalogsInclude);
            $sub->whereIn('name', function ($q) use ($include) {
                $q->from('objectnames')->whereIn('catalog', $include)->select('objectname');
            });
        }
        if (!empty($this->catalogsExclude)) {
            $exclude = array_map('strval', $this->catalogsExclude);
            $sub->whereNotIn('name', function ($q) use ($exclude) {
                $q->from('objectnames')->whereIn('catalog', $exclude)->select('objectname');
            });
        }

        // ── Name search (catalog + number) ────────────────────────────────
        if (!empty($this->nameSearchCatalogs) || $this->nameSearchNumber !== '') {
            $catalogs = array_values(array_filter(array_map('trim', $this->nameSearchCatalogs), fn($v) => $v !== ''));
            $number = trim((string) $this->nameSearchNumber);

            $sub->whereIn('name', function ($q) use ($catalogs, $number) {
                $q->from('objectnames')->select('objectname');

                if (!empty($catalogs)) {
                    $q->whereIn('catalog', $catalogs);
                }
                if ($number !== '') {
                    $q->where('catindex', $number);
                }
            });
        }

        // ── Apply observing list pre-filter to $sub (before GROUP BY) ────────
        // This lets MySQL use search_index_name_index instead of a full scan.
        if ($preloadedListFilter !== null) {
            if ($preloadedListFilter === 'zero') {
                $sub->whereRaw('1 = 0');
            } else {
                [$listMode, $listNames] = $preloadedListFilter;
                if ($listMode === 'not_in') {
                    if (!empty($listNames)) {
                        $sub->whereNotIn('name', $listNames);
                    }
                    // Empty list in not_in mode = no restriction (keep all objects).
                } else {
                    // 'in' mode
                    if (empty($listNames)) {
                        $sub->whereRaw('1 = 0');
                    } else {
                        $sub->whereIn('name', $listNames);
                    }
                }
            }
        }

        // ── Apply objects-table pre-filter to $sub (before GROUP BY) ──────────
        // Restricts $sub to matching source_pks so MySQL uses search_index_source_pk_index
        // instead of scanning all 83K rows + filesort.
        if ($preloadedObjectSlugs !== null) {
            // These filters only apply to deep-sky objects.
            $sub->where('source_table', 'objects');

            if (empty($preloadedObjectSlugs)) {
                $sub->whereRaw('1 = 0');
            } else {
                $sub->whereIn('source_pk', $preloadedObjectSlugs);
            }
        }

        $sub->groupBy(DB::raw("LOWER(COALESCE(name, display_name))"));

        $query = SearchIndex::query()->fromSub($sub, 'search_index')
            ->leftJoin('objects', function ($join) {
                $join->on('search_index.source_pk', '=', 'objects.slug')
                    ->where('search_index.source_table', '=', 'objects');
            })
            ->leftJoin('deepskytypes', 'deepskytypes.code', '=', 'objects.type')
            ->leftJoin('constellations', 'constellations.id', '=', 'objects.con');

        // ── Object type filter ────────────────────────────────────────────────
        if (!empty($this->objectTypes)) {
            $types = array_map('strval', $this->objectTypes);
            $query->whereIn('objects.type', $types);
        }

        // ── Constellation filter ──────────────────────────────────────────────
        if (!empty($this->constellations)) {
            // constellations values are string abbreviation IDs stored in objects.con (e.g. 'LEO', 'VIR')
            $conIds = array_map('strval', $this->constellations);
            $query->whereIn('objects.con', $conIds);
        }

        // ── Name text filter (full/partial name) ────────────────────────────
        $nameNeedle = trim((string) $this->nameText);
        if ($nameNeedle !== '') {
            $needle = mb_strtolower($nameNeedle);
            $query->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(COALESCE(search_index.display_name, \'\')) LIKE ?', ['%' . $needle . '%'])
                    ->orWhereRaw('LOWER(COALESCE(search_index.name, \'\')) LIKE ?', ['%' . $needle . '%'])
                    ->orWhereRaw('LOWER(COALESCE(objects.name, \'\')) LIKE ?', ['%' . $needle . '%'])
                    ->orWhereExists(function ($sq) use ($needle) {
                        // Match aliases stored in search_index rows for the same canonical source item.
                        $sq->selectRaw('1')
                            ->from('search_index as si_alias')
                            ->whereRaw('si_alias.source_table = search_index.source_table')
                            ->whereRaw('si_alias.source_pk = search_index.source_pk')
                            ->whereRaw('LOWER(COALESCE(si_alias.display_name, \'\')) LIKE ?', ['%' . $needle . '%']);
                    })
                    ->orWhereIn('objects.name', function ($sq) use ($needle) {
                        // Match alternative names from objectnames (e.g. "Jupiter\'s Ghost").
                        $sq->from('objectnames')
                            ->select('objectname')
                            ->whereRaw('LOWER(COALESCE(altname, \'\')) LIKE ?', ['%' . $needle . '%']);
                    });
            });
        }

        // ── Observing lists filter is now handled in $sub above (pre-fetched) ─

        // ── Description text filter (deep-sky objects only) ──────────────────
        $descriptionNeedle = trim((string) $this->descriptionText);
        if ($descriptionNeedle !== '') {
            $terms = preg_split('/\s+/', $descriptionNeedle) ?: [];
            $terms = array_values(array_unique(array_filter(array_map('trim', $terms), fn($t) => $t !== '')));

            $query->where('search_index.source_table', 'objects');

            if (!empty($terms)) {
                if ($this->descriptionMode === 'or') {
                    $query->where(function ($q) use ($terms) {
                        foreach ($terms as $term) {
                            $q->orWhereRaw('COALESCE(objects.description, \'\') LIKE ?', ['%' . $term . '%']);
                        }
                    });
                } else {
                    foreach ($terms as $term) {
                        $query->whereRaw('COALESCE(objects.description, \'\') LIKE ?', ['%' . $term . '%']);
                    }
                }
            }
        }

        // ── Magnitude filter ──────────────────────────────────────────────────
        if ($this->magMin !== '' && is_numeric($this->magMin)) {
            $query->where('objects.mag', '>=', floatval($this->magMin));
        }
        if ($this->magMax !== '' && is_numeric($this->magMax)) {
            $query->where('objects.mag', '<=', floatval($this->magMax));
        }

        // ── Surface brightness filter ─────────────────────────────────────────
        if ($this->subrMin !== '' && is_numeric($this->subrMin)) {
            $query->where('objects.subr', '>=', floatval($this->subrMin));
        }
        if ($this->subrMax !== '' && is_numeric($this->subrMax)) {
            $query->where('objects.subr', '<=', floatval($this->subrMax));
        }

        // ── Diameter 1 filter ─────────────────────────────────────────────────
        if ($this->diam1Min !== '' && is_numeric($this->diam1Min)) {
            $query->where('objects.diam1', '>=', floatval($this->diam1Min));
        }
        if ($this->diam1Max !== '' && is_numeric($this->diam1Max)) {
            $query->where('objects.diam1', '<=', floatval($this->diam1Max));
        }

        // ── Diameter 2 filter ─────────────────────────────────────────────────
        if ($this->diam2Min !== '' && is_numeric($this->diam2Min)) {
            $query->where('objects.diam2', '>=', floatval($this->diam2Min));
        }
        if ($this->diam2Max !== '' && is_numeric($this->diam2Max)) {
            $query->where('objects.diam2', '<=', floatval($this->diam2Max));
        }

        // ── Diam1/Diam2 ratio filter ──────────────────────────────────────────
        if (
            ($this->ratioMin !== '' && is_numeric($this->ratioMin)) ||
            ($this->ratioMax !== '' && is_numeric($this->ratioMax))
        ) {
            // Ratio only makes sense when both diameters are present and non-zero
            $query->whereNotNull('objects.diam1')
                ->where('objects.diam1', '>', 0)
                ->whereNotNull('objects.diam2')
                ->where('objects.diam2', '>', 0);
            if ($this->ratioMin !== '' && is_numeric($this->ratioMin)) {
                $query->whereRaw('(objects.diam1 / objects.diam2) >= ?', [floatval($this->ratioMin)]);
            }
            if ($this->ratioMax !== '' && is_numeric($this->ratioMax)) {
                $query->whereRaw('(objects.diam1 / objects.diam2) <= ?', [floatval($this->ratioMax)]);
            }
        }

        // ── RA filter (hours, 0-24) ───────────────────────────────────────────
        if ($this->raMin !== '' && is_numeric($this->raMin)) {
            $query->whereRaw('COALESCE(objects.ra, search_index.ra) >= ?', [floatval($this->raMin)]);
        }
        if ($this->raMax !== '' && is_numeric($this->raMax)) {
            $query->whereRaw('COALESCE(objects.ra, search_index.ra) <= ?', [floatval($this->raMax)]);
        }

        // ── Declination filter (degrees) ──────────────────────────────────────
        if ($this->declMin !== '' && is_numeric($this->declMin)) {
            $query->whereRaw('COALESCE(objects.decl, search_index.decl) >= ?', [floatval($this->declMin)]);
        }
        if ($this->declMax !== '' && is_numeric($this->declMax)) {
            $query->whereRaw('COALESCE(objects.decl, search_index.decl) <= ?', [floatval($this->declMax)]);
        }

        // ── Atlas page filter (deep-sky objects only) ──────────────────────
        $atlasCode = trim((string) $this->atlasCode);
        $atlasSpec = trim((string) $this->atlasPageSpec);
        $atlasMin = trim((string) $this->atlasPageMin);
        $atlasMax = trim((string) $this->atlasPageMax);
        if ($atlasCode !== '' || $atlasSpec !== '' || $atlasMin !== '' || $atlasMax !== '') {
            if ($atlasCode !== '' && preg_match('/^[A-Za-z0-9_]+$/', $atlasCode) && Schema::hasColumn('objects', $atlasCode)) {
                $query->where('search_index.source_table', 'objects');

                $atlasExpr = "NULLIF(TRIM(objects.`{$atlasCode}`), '')";

                if ($atlasSpec !== '') {
                    $parsed = $this->parseAtlasPageSpec($atlasSpec);
                    $exactPages = $parsed['pages'];
                    $ranges = $parsed['ranges'];

                    if (empty($exactPages) && empty($ranges)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->where(function ($q) use ($atlasExpr, $exactPages, $ranges) {
                            if (!empty($exactPages)) {
                                $q->orWhereRaw("CAST({$atlasExpr} AS UNSIGNED) IN (" . implode(',', array_fill(0, count($exactPages), '?')) . ")", $exactPages);
                            }
                            foreach ($ranges as [$start, $end]) {
                                $q->orWhereRaw("CAST({$atlasExpr} AS UNSIGNED) BETWEEN ? AND ?", [$start, $end]);
                            }
                        });
                    }
                } else {
                    // Backward compatibility with old min/max query params.
                    if ($atlasMin !== '' && is_numeric($atlasMin)) {
                        $query->whereRaw("CAST({$atlasExpr} AS UNSIGNED) >= ?", [intval($atlasMin)]);
                    }
                    if ($atlasMax !== '' && is_numeric($atlasMax)) {
                        $query->whereRaw("CAST({$atlasExpr} AS UNSIGNED) <= ?", [intval($atlasMax)]);
                    }
                    if ($atlasMin === '' && $atlasMax === '') {
                        $query->whereRaw("{$atlasExpr} IS NOT NULL");
                    }
                }
            } else {
                // Invalid/missing atlas code with atlas-page filtering requested.
                $query->whereRaw('1 = 0');
            }
        }

        $select = 'search_index.*, objects.name as obj_name, objects.type as obj_type, objects.ra as obj_ra, objects.decl as obj_decl, deepskytypes.name as type_name, constellations.name as constellation, objects.mag, objects.subr, objects.diam1, objects.diam2, objects.pa';

        $inActiveListSelect = '0 as in_active_list';
        try {
            $authUser = Auth::user();
            if ($authUser) {
                /** @var ActiveObservingListService $svc */
                $svc = app(ActiveObservingListService::class);
                $activeList = $svc->getActiveList($authUser);
                if ($activeList) {
                    $activeListId = (int) $activeList->id;
                    $inActiveListSelect = "EXISTS(SELECT 1 FROM observing_list_items oli WHERE oli.observing_list_id = {$activeListId} AND oli.object_name = COALESCE(objects.name, search_index.name, search_index.display_name)) as in_active_list";
                }
            }
        } catch (\Throwable $_) {
            $inActiveListSelect = '0 as in_active_list';
        }
        $select .= ', ' . $inActiveListSelect;

        // Atlas column
        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', (string) $authUser->standardAtlasCode)) {
                $acol = (string) $authUser->standardAtlasCode;
                if (Schema::hasColumn('objects', $acol)) {
                    $select .= ', objects.`' . $acol . '` as atlas_page';
                }
            }
        } catch (\Throwable $_) {
        }

        $status = trim((string) $this->observingStatus);
        $needsObservingStatusFilter = $status !== '' && $status !== 'all';
        $obsJoinApplied = false;

        // Legacy observations aggregate
        try {
            $oldDbName = config('database.connections.mysqlOld.database') ?? env('DB_DATABASE_OLD');
            $authUser = Auth::user();
            $legacyUser = $authUser?->username ?? '';
            if (!empty($oldDbName)) {
                try {
                    $quotedUser = DB::getPdo()->quote($legacyUser);
                } catch (\Throwable $_) {
                    $quotedUser = "''";
                }

                $globalSearch = '';
                try {
                    $globalSearch = trim((string) ($this->search ?? ''));
                } catch (\Throwable $_) {
                    $globalSearch = '';
                }

                // Build aggregate only for names that survive the current filters.
                // This keeps PowerGrid pagination/search responsive.
                $filteredObjectNames = [];
                try {
                    $filteredObjectNames = (clone $query)
                        ->whereNotNull('objects.name')
                        ->distinct()
                        ->pluck('objects.name')
                        ->map(fn($n) => (string) $n)
                        ->values()
                        ->toArray();
                } catch (\Throwable $_) {
                    $filteredObjectNames = [];
                }

                // For broad datasets or active text search, skip legacy aggregation and
                // return neutral values to keep PowerGrid interactions responsive.
                if ((!empty($globalSearch) || empty($filteredObjectNames) || count($filteredObjectNames) > 1500) && !$needsObservingStatusFilter) {
                    $select .= ", 0 as total_observations, 0 as total_drawings, 0 as your_observations, 0 as your_drawings, NULL as last_seen_date, NULL as your_last_seen_date, NULL as last_drawing_date, NULL as your_last_drawing_date, 0 as seen, NULL as last_seen";
                } else {
                    try {
                        $quotedNames = implode(', ', array_map(function ($n) {
                            try {
                                return DB::getPdo()->quote($n);
                            } catch (\Throwable $_) {
                                return "''";
                            }
                        }, $filteredObjectNames));
                    } catch (\Throwable $_) {
                        $quotedNames = "''";
                    }

                    $obsAggSql = "SELECT o.objectname AS object_name,
                    COUNT(*) AS total_observations,
                    SUM(o.hasDrawing = 1) AS total_drawings,
                    SUM(o.observerid = {$quotedUser}) AS your_observations,
                    SUM(o.observerid = {$quotedUser} AND o.hasDrawing = 1) AS your_drawings,
                    MAX(o.date) AS last_seen_date,
                    MAX(CASE WHEN o.observerid = {$quotedUser} THEN o.date END) AS your_last_seen_date,
                    MAX(CASE WHEN o.hasDrawing = 1 THEN o.date END) AS last_drawing_date,
                    MAX(CASE WHEN o.observerid = {$quotedUser} AND o.hasDrawing = 1 THEN o.date END) AS your_last_drawing_date
                FROM `" . $oldDbName . "`.`observations` o
                WHERE o.objectname IN ({$quotedNames})
                GROUP BY o.objectname";

                    $select .= ", obs.total_observations as total_observations, obs.total_drawings as total_drawings, obs.your_observations as your_observations, obs.your_drawings as your_drawings, obs.last_seen_date as last_seen_date, obs.your_last_seen_date as your_last_seen_date, obs.last_drawing_date as last_drawing_date, obs.your_last_drawing_date as your_last_drawing_date, obs.total_observations as seen, obs.your_last_seen_date as last_seen";

                    try {
                        $query->leftJoin(DB::raw('(' . $obsAggSql . ') as obs'), function ($join) {
                            $join->on('obs.object_name', '=', 'objects.name');
                        });
                        $obsJoinApplied = true;
                    } catch (\Throwable $_) {
                    }
                }
            }
        } catch (\Throwable $_) {
        }

        if ($needsObservingStatusFilter) {
            // If aggregation cannot be joined, avoid returning misleading results.
            if (!$obsJoinApplied) {
                $query->whereRaw('1 = 0');
            } else {
                switch ($status) {
                    case 'seen_any':
                        $query->whereRaw('COALESCE(obs.total_observations, 0) > 0');
                        break;
                    case 'drawn_any':
                        $query->whereRaw('COALESCE(obs.total_drawings, 0) > 0');
                        break;
                    case 'unseen_any':
                        $query->whereRaw('COALESCE(obs.total_observations, 0) = 0');
                        break;
                    case 'undrawn_any':
                        $query->whereRaw('COALESCE(obs.total_drawings, 0) = 0');
                        break;
                    case 'seen_by_me':
                        $query->whereRaw('COALESCE(obs.your_observations, 0) > 0');
                        break;
                    case 'drawn_by_me':
                        $query->whereRaw('COALESCE(obs.your_drawings, 0) > 0');
                        break;
                    case 'unseen_by_me':
                        $query->whereRaw('COALESCE(obs.your_observations, 0) = 0');
                        break;
                    case 'undrawn_by_me':
                        $query->whereRaw('COALESCE(obs.your_drawings, 0) = 0');
                        break;
                    case 'seen_by_others_not_me':
                        $query->whereRaw('COALESCE(obs.total_observations, 0) > 0')
                            ->whereRaw('COALESCE(obs.your_observations, 0) = 0');
                        break;
                    case 'drawn_by_others_not_me':
                        $query->whereRaw('COALESCE(obs.total_drawings, 0) > 0')
                            ->whereRaw('COALESCE(obs.your_drawings, 0) = 0');
                        break;
                }
            }
        }

        // Per-user cached metrics (contrast reserve / best mag)
        $crExpr = null;
        try {
            $authUser = Auth::user();
            if ($authUser && $authUser->standardLocation) {
                $locId = $authUser->standardLocation->id ?? null;
                $instrId = $authUser->stdtelescope ?? null;
                $stdLens = $authUser->stdlens ?? null;

                if ($instrId && $locId) {
                    if (!empty($stdLens)) {
                        $query->leftJoin('user_object_metrics as uom_lens', function ($join) use ($instrId, $locId, $authUser, $stdLens) {
                            $join->on('objects.name', '=', 'uom_lens.object_name')
                                ->where('uom_lens.user_id', '=', $authUser->id)
                                ->where('uom_lens.instrument_id', '=', $instrId)
                                ->where('uom_lens.location_id', '=', $locId)
                                ->where('uom_lens.lens_id', '=', $stdLens);
                        });
                        $query->leftJoin('user_object_metrics as uom_default', function ($join) use ($instrId, $locId, $authUser) {
                            $join->on('objects.name', '=', 'uom_default.object_name')
                                ->where('uom_default.user_id', '=', $authUser->id)
                                ->where('uom_default.instrument_id', '=', $instrId)
                                ->where('uom_default.location_id', '=', $locId)
                                ->whereNull('uom_default.lens_id');
                        });
                        $crExpr = 'COALESCE(uom_lens.contrast_reserve, uom_default.contrast_reserve)';
                        $query->addSelect([
                            DB::raw('COALESCE(uom_lens.contrast_reserve, uom_default.contrast_reserve) as computed_contrast_reserve'),
                            DB::raw('COALESCE(uom_lens.contrast_reserve_category, uom_default.contrast_reserve_category) as computed_contrast_reserve_category'),
                            DB::raw('COALESCE(uom_lens.optimum_detection_magnification, uom_default.optimum_detection_magnification) as computed_best_mag'),
                            DB::raw('COALESCE(uom_lens.id, uom_default.id) as metric_exists'),
                        ]);
                    } else {
                        $query->leftJoin('user_object_metrics as uom', function ($join) use ($instrId, $locId, $authUser) {
                            $join->on('objects.name', '=', 'uom.object_name')
                                ->where('uom.user_id', '=', $authUser->id)
                                ->where('uom.instrument_id', '=', $instrId)
                                ->where('uom.location_id', '=', $locId)
                                ->whereNull('uom.lens_id');
                        });
                        $crExpr = 'uom.contrast_reserve';
                        $query->addSelect([
                            'uom.contrast_reserve as computed_contrast_reserve',
                            'uom.contrast_reserve_category as computed_contrast_reserve_category',
                            'uom.optimum_detection_magnification as computed_best_mag',
                            'uom.id as metric_exists',
                        ]);
                    }
                }
            }
        } catch (\Throwable $_) {
        }

        // ── Contrast reserve filter (user-specific metrics) ───────────────
        $crMin = trim((string) $this->contrastReserveMin);
        $crMax = trim((string) $this->contrastReserveMax);
        if ($crMin !== '' || $crMax !== '') {
            if ($crExpr === null) {
                // CR is unavailable without user/location/telescope context.
                $query->whereRaw('1 = 0');
            } else {
                if ($crMin !== '' && is_numeric($crMin)) {
                    $query->whereRaw("{$crExpr} >= ?", [floatval($crMin)]);
                }
                if ($crMax !== '' && is_numeric($crMax)) {
                    $query->whereRaw("{$crExpr} <= ?", [floatval($crMax)]);
                }
            }
        }

        return $query->selectRaw($select);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('search_index_id')
            ->add('name_link', function ($row) {
                $slug = '';
                if (!empty($row->source_table) && $row->source_table === 'objects' && !empty($row->source_pk)) {
                    $slug = $row->source_pk;
                } else {
                    $slug = Str::slug($row->name ?? $row->display_name ?? '');
                }
                $display = $row->display_name ?? $row->name ?? '';
                $canonical = $row->obj_name ?? null;
                $html = '<a class="font-medium" href="' . route('object.show', ['slug' => $slug]) . '">' . e($display);
                try {
                    if (!empty($canonical) && mb_strtolower(trim($canonical)) !== mb_strtolower(trim($display))) {
                        $html .= ' <span class="text-gray-400">(' . e($canonical) . ')</span>';
                    }
                } catch (\Throwable $_) {
                }
                $html .= '</a>';
                return $html;
            })
            ->add('name_plain', function ($row) {
                return html_entity_decode((string) ($row->display_name ?? $row->name ?? ''));
            })
            ->add('display_name')
            ->add('ra', function ($row) {
                $ra = $row->obj_ra ?? $row->ra ?? null;
                return \App\Models\DeepskyObject::formatRa($ra ?? null) ?? '';
            })
            ->add('decl', function ($row) {
                $decl = $row->obj_decl ?? $row->decl ?? null;
                return \App\Models\DeepskyObject::formatDec($decl ?? null) ?? '';
            })
            ->add('type_name', function ($row) {
                return $row->type_name ?? $row->source_type ?? '';
            })
            ->add('constellation')
            ->add('add_to_list_action', function ($row) {
                if (!$this->showAddColumn || !Auth::check() || !$this->canModifyActiveList()) {
                    return '';
                }

                $name = trim((string) ($row->obj_name ?? $row->name ?? $row->display_name ?? ''));
                if ($name === '') {
                    return '';
                }

                $inList = intval($row->in_active_list ?? 0) === 1;
                $token = csrf_token();
                $action = route('observing-list.active.toggle-item');
                $title = $inList ? e(__('Remove from active observing list')) : e(__('Add to active observing list'));
                $colorClass = $inList ? 'text-red-400 hover:text-red-300' : 'text-green-400 hover:text-green-300';
                $iconPath = $inList
                    ? '<path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
                    : '<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
                $addedTitle = e(__('Add to active observing list'));
                $removedTitle = e(__('Remove from active observing list'));

                return '<form method="POST" action="' . e($action) . '" class="inline-flex toggle-list-form">'
                    . '<input type="hidden" name="_token" value="' . e($token) . '">'
                    . '<input type="hidden" name="object_name" value="' . e($name) . '">'
                    . '<button type="submit" class="' . $colorClass . '" title="' . $title . '" data-added-title="' . $addedTitle . '" data-removed-title="' . $removedTitle . '">'
                    . '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
                    . $iconPath
                    . '</svg>'
                    . '</button>'
                    . '</form>';
            })
            ->add('mag', function ($row) {
                $mag = $row->mag ?? null;
                if (is_numeric($mag) && floatval($mag) != 99.9 && floatval($mag) != 0.0) {
                    return $mag;
                }
                return '-';
            })
            ->add('subr', function ($row) {
                $sb = $row->subr ?? null;
                if (is_numeric($sb) && floatval($sb) != 99.9 && floatval($sb) != 0.0) {
                    return $sb;
                }
                return '-';
            })
            ->add('total_observations', function ($row) {
                try {
                    return isset($row->total_observations) ? intval($row->total_observations) : 0;
                } catch (\Throwable $_) {
                    return 0;
                }
            })
            ->add('your_last_seen_date', function ($row) {
                try {
                    $d = $row->your_last_seen_date ?? null;
                    if (!$d)
                        return '';
                    try {
                        $c = \Carbon\Carbon::createFromFormat('Ymd', (string) $d);
                        return $c->translatedFormat('j M Y');
                    } catch (\Throwable $_) {
                        return (string) $d;
                    }
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('size', function ($row) {
                $d1 = $row->diam1 ?? null;
                $d2 = $row->diam2 ?? null;
                $pa = $row->pa ?? null;
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '-';
                $d1f = $hasD1 ? floatval($d1) : 0.0;
                $d2f = $hasD2 ? floatval($d2) : 0.0;
                $fmt = function ($v) {
                    return (floor($v) == $v) ? sprintf('%d', $v) : sprintf('%.1f', $v);
                };
                if ($hasD1 && $hasD2) {
                    if (max($d1f, $d2f) > 60.0) {
                        $d1m = $d1f / 60.0;
                        $d2m = $d2f / 60.0;
                        $d1m_fmt = $fmt($d1m);
                        $d2m_fmt = $fmt($d2m);
                        if ($d1m_fmt === '0' || $d1m_fmt === '0.0' || $d2m_fmt === '0' || $d2m_fmt === '0.0') {
                            $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                        } else {
                            $size = $d1m_fmt . "'x" . $d2m_fmt . "'";
                        }
                    } else {
                        $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                    }
                } else {
                    $single = $hasD1 ? $d1f : $d2f;
                    $size = ($single > 60.0) ? $fmt($single / 60.0) . "'" : $fmt($single) . "''";
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            })
            ->add('computed_contrast_reserve', function ($row) {
                try {
                    $authUser = Auth::user();
                    if (!$authUser) {
                        return '<span title="' . e(__('Login required to compute contrast reserve')) . '">-</span>';
                    }
                    $cached = $row->computed_contrast_reserve ?? null;
                    $metricExists = $row->metric_exists ?? null;
                    if ($metricExists !== null && $cached === null) {
                        return '<span title="' . e(__('Contrast reserve unavailable for this object')) . '">-</span>';
                    }
                    if (is_numeric($cached)) {
                        $display = number_format(round(floatval($cached), 2), 2);
                        $contrastVal = floatval($cached);
                        if ($contrastVal > 1.0)
                            $crClass = 'text-green-400';
                        elseif ($contrastVal > 0.5)
                            $crClass = 'text-green-600';
                        elseif ($contrastVal > 0.35)
                            $crClass = 'text-yellow-400';
                        elseif ($contrastVal > 0.1)
                            $crClass = 'text-orange-400';
                        elseif ($contrastVal > -0.2)
                            $crClass = 'text-gray-300';
                        else
                            $crClass = 'text-gray-600';
                        return '<span class="font-medium ' . $crClass . '">' . e($display) . '</span>';
                    }
                    return '<span>-</span>';
                } catch (\Throwable $_) {
                    return '-';
                }
            })
            ->add('atlas_page');
    }

    public function columns(): array
    {
        $atlasTitle = __('Atlas');
        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', $authUser->standardAtlasCode)) {
                $m = Atlas::where('code', $authUser->standardAtlasCode)->first();
                if ($m?->name)
                    $atlasTitle = $m->name;
            }
        } catch (\Throwable $_) {
        }

        $showContrast = false;
        $authUser = null;
        try {
            $authUser = Auth::user();
            if ($authUser && $authUser?->standardLocation && $authUser?->stdtelescope) {
                $showContrast = true;
            }
        } catch (\Throwable $_) {
        }

        $cols = [
            Column::make(__('Name'), 'name_link', 'display_name')
                ->searchable()
                ->sortUsing(function ($query, $direction) {
                    $prefix = "LOWER(REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[0-9]', ''))";
                    $num = "(CASE WHEN REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[^0-9]', '') = '' THEN 0 ELSE CAST(REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[^0-9]', '') AS UNSIGNED) END)";
                    $query->orderByRaw("{$prefix} {$direction}, {$num} {$direction}, search_index.display_name {$direction}");
                })
                ->bodyAttribute('class', 'font-medium whitespace-normal')
                ->bodyAttribute('style', 'white-space:normal; overflow:visible;')
                ->headerAttribute('style', 'white-space:normal;'),
            Column::make(__('Name'), 'name_plain')->hidden()->visibleInExport(true),
            Column::make(__('RA'), 'ra')
                ->sortUsing(function ($query, $direction) {
                    $query->orderByRaw("(CASE WHEN COALESCE(objects.ra, search_index.ra) > 24 THEN COALESCE(objects.ra, search_index.ra) / 15.0 ELSE COALESCE(objects.ra, search_index.ra) END) $direction");
                })
                ->bodyAttribute('style', 'white-space:nowrap; overflow:visible;'),
            Column::make(__('Dec'), 'decl')
                ->sortUsing(function ($query, $direction) {
                    $query->orderByRaw("COALESCE(objects.decl, search_index.decl) $direction");
                })
                ->bodyAttribute('style', 'white-space:nowrap; overflow:visible;'),
            Column::make(__('Type'), 'type_name')
                ->sortUsing(function ($query, $direction) {
                    $query->orderByRaw("COALESCE(deepskytypes.name, search_index.source_type) $direction");
                })
                ->bodyAttribute('style', 'white-space:normal; overflow:visible;'),
            Column::make(__('Constellation'), 'constellation')
                ->sortUsing(function ($query, $direction) {
                    $query->orderBy('constellations.name', $direction);
                })
                ->bodyAttribute('style', 'white-space:normal; overflow:visible;'),
            Column::make(__('Mag'), 'mag')
                ->sortUsing(function ($query, $direction) {
                    $query->orderBy('objects.mag', $direction);
                }),
            Column::make(__('SB'), 'subr')
                ->sortUsing(function ($query, $direction) {
                    $query->orderBy('objects.subr', $direction);
                }),
            Column::make(__('Seen'), 'total_observations')->sortable()->bodyAttribute('class', 'text-center'),
        ];

        if ($authUser) {
            $cols[] = Column::make(__('Last seen'), 'your_last_seen_date')->sortable()->bodyAttribute('class', 'text-center');
        }

        if ($showContrast) {
            $cols[] = Column::make(__('CR'), 'computed_contrast_reserve')->sortable()->bodyAttribute('class', 'text-center');
        }

        $cols[] = Column::make(__('Size'), 'size')->sortable()->bodyAttribute('class', 'text-center');

        if ($authUser && $this->showAddColumn && $this->canModifyActiveList()) {
            $cols[] = Column::make(__('Add'), 'add_to_list_action')
                ->bodyAttribute('class', 'text-center')
                ->headerAttribute('class', 'text-center');
        }

        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', $authUser->standardAtlasCode)) {
                $cols[] = Column::make($atlasTitle, 'atlas_page')->sortable()->bodyAttribute('class', 'text-center');
            }
        } catch (\Throwable $_) {
        }

        return $cols;
    }

    /**
     * Parse atlas page spec like: 1,3,4-7 into exact pages + ranges.
     * Invalid tokens are ignored.
     *
     * @return array{pages: array<int>, ranges: array<array{0:int,1:int}>}
     */
    private function parseAtlasPageSpec(string $spec): array
    {
        $pages = [];
        $ranges = [];

        $tokens = preg_split('/\s*,\s*/', trim($spec)) ?: [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            if (preg_match('/^(\d+)$/', $token, $m) === 1) {
                $p = intval($m[1]);
                if ($p >= 0) {
                    $pages[] = $p;
                }
                continue;
            }

            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $token, $m) === 1) {
                $a = intval($m[1]);
                $b = intval($m[2]);
                if ($a >= 0 && $b >= 0) {
                    $ranges[] = [$a <= $b ? $a : $b, $a <= $b ? $b : $a];
                }
            }
        }

        $pages = array_values(array_unique($pages));
        return ['pages' => $pages, 'ranges' => $ranges];
    }

    private function currentObservingListId(): ?int
    {
        if (empty($this->observingLists)) {
            return null;
        }

        $firstListId = (int) ($this->observingLists[0] ?? 0);
        return $firstListId > 0 ? $firstListId : null;
    }

    private function currentVisibleObjectNames(): array
    {
        try {
            $records = $this->records;
        } catch (\Throwable $_) {
            return [];
        }

        if ($records instanceof LengthAwarePaginator || $records instanceof Paginator) {
            $rows = collect($records->items());
        } elseif ($records instanceof Collection) {
            $rows = $records;
        } else {
            $rows = collect();
        }

        return $rows
            ->map(function ($row) {
                return trim((string) ($row->obj_name ?? $row->name ?? $row->display_name ?? ''));
            })
            ->filter(fn($name) => $name !== '')
            ->values()
            ->all();
    }

    private function canModifyActiveList(): bool
    {
        if ($this->canModifyActiveListCached !== null) {
            return $this->canModifyActiveListCached;
        }

        $user = Auth::user();
        if (!$user) {
            $this->canModifyActiveListCached = false;
            return false;
        }

        /** @var ActiveObservingListService $svc */
        $svc = app(ActiveObservingListService::class);
        $activeList = $svc->getActiveList($user);

        if (!$activeList) {
            $this->canModifyActiveListCached = true;
            return true;
        }

        $this->canModifyActiveListCached = $user->can('addItem', $activeList);
        return $this->canModifyActiveListCached;
    }

    public function actionRules(mixed $row): array
    {
        return [
            [
                'forAction' => 'stripe_odd',
                'rule' => [
                    'loop' => function ($loop) {
                        return (isset($loop->index) && intval($loop->index) % 2 === 0);
                    },
                    'setAttribute' => ['attribute' => 'class', 'value' => 'dsl-row-odd']
                ]
            ],
            [
                'forAction' => 'stripe_even',
                'rule' => [
                    'loop' => function ($loop) {
                        return (isset($loop->index) && intval($loop->index) % 2 === 1);
                    },
                    'setAttribute' => ['attribute' => 'class', 'value' => 'dsl-row-even']
                ]
            ],
        ];
    }

    private function getCachedEyepieces($user): array
    {
        if ($this->cachedEyepieces !== null)
            return $this->cachedEyepieces;
        $this->cachedEyepieces = [];
        try {
            if (!$user || !isset($user->id))
                return $this->cachedEyepieces;
            $eps = \App\Models\Eyepiece::where('user_id', $user->id)
                ->where('active', 1)
                ->orderBy('focal_length_mm', 'asc')
                ->limit(200)
                ->get();
            foreach ($eps as $ep) {
                $this->cachedEyepieces[] = $ep;
            }
        } catch (\Throwable $_) {
        }
        return $this->cachedEyepieces;
    }
}

