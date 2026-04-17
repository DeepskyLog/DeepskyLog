<?php

namespace App\Livewire;

use App\Models\SavedObjectSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class AdvancedObjectSearch extends Component
{
    // Which filter rows are currently active (keyed by filter type)
    public array $activeFilters = [];

    // ── Constellation filter ──────────────────────────────────────────────
    public array $constellations = [];          // selected IAU abbreviations

    // ── Object type filter ────────────────────────────────────────────────
    public array $objectTypes = [];             // selected deepskytypes.code values

    // ── Object categories (source_table) filter ───────────────────────────
    public array $objectCategories = [];        // selected source_table values
    // Available category keys (never changed at runtime)
    public array $allCategories = [
        'objects' => 'Deep-sky objects',
        'cometobjects' => 'Comets',
        'planets' => 'Planets',
        'moons' => 'Moons',
        'asteroids' => 'Asteroids',
        'lunar_features' => 'Lunar features',
    ];

    // ── Catalog include / exclude ─────────────────────────────────────────
    public array $catalogsInclude = [];
    public array $catalogsExclude = [];

    // ── Magnitude ─────────────────────────────────────────────────────────
    public string $magMin = '';
    public string $magMax = '';

    // ── Surface brightness ────────────────────────────────────────────────
    public string $subrMin = '';
    public string $subrMax = '';

    // ── Diameter 1 (arcmin) ───────────────────────────────────────────────
    public string $diam1Min = '';
    public string $diam1Max = '';

    // ── Diameter 2 (arcmin) ───────────────────────────────────────────────
    public string $diam2Min = '';
    public string $diam2Max = '';

    // ── Diam1/Diam2 ratio ────────────────────────────────────────────────
    public string $ratioMin = '';
    public string $ratioMax = '';

    // ── Contrast reserve ──────────────────────────────────────────────────
    public string $contrastReserveMin = '';
    public string $contrastReserveMax = '';

    // ── RA (hours, 0-24) ─────────────────────────────────────────────────
    public string $raMin = '';
    public string $raMax = '';

    // ── Dec (degrees, -90 to +90) ─────────────────────────────────────────
    public string $declMin = '';
    public string $declMax = '';

    // ── Observing status ───────────────────────────────────────────────────
    public string $observingStatus = 'all';
    public array $observingStatusOptions = [
        'all' => 'All objects, seen or not',
        'seen_any' => 'Only objects that have already been seen',
        'drawn_any' => 'Only objects that have been drawn',
        'unseen_any' => 'Only objects that haven\'t been seen',
        'undrawn_any' => 'Only objects that haven\'t been drawn',
        'seen_by_me' => 'Only objects that have been seen by me',
        'drawn_by_me' => 'Only objects that have been drawn by me',
        'unseen_by_me' => 'Only objects that haven\'t been seen by me',
        'undrawn_by_me' => 'Only objects that haven\'t been drawn by me',
        'seen_by_others_not_me' => 'Already seen by someone else but not by me',
        'drawn_by_others_not_me' => 'Already drawn by someone else but not by me',
    ];

    // ── Observing lists (legacy DB) ───────────────────────────────────────
    public array $observingLists = [];
    public string $observingListsMode = 'in';
    public array $observingListsModeOptions = [
        'in' => 'In selected observing list(s)',
        'not_in' => 'Not in selected observing list(s)',
    ];

    // ── Saved searches ────────────────────────────────────────────────────
    public string $saveName = '';
    public array $nameSearchCatalogs = [];
    public string $nameSearchNumber = '';
    public string $nameText = '';
    public string $descriptionText = '';
    public string $descriptionMode = 'and';
    public array $descriptionModeOptions = [
        'and' => 'Match all words (AND)',
        'or' => 'Match any word (OR)',
    ];
    public string $atlasCode = '';
    public string $atlasPageSpec = '';
    public string $atlasPageMin = '';
    public string $atlasPageMax = '';
    public ?int $loadedId = null;

    // ── Validation message ────────────────────────────────────────────────
    public string $errorMessage = '';
    public bool $isContrastReserveConfigured = false;

    // ── Available filter types (not yet added) ────────────────────────────
    public array $availableFilterTypes = [];

    // ── Prefetched option lists (loaded in mount) ─────────────────────────
    public array $allConstellations = [];
    public array $allObjectTypes = [];
    public array $allCatalogs = [];
    public array $allAtlases = [];
    public array $allObservingLists = [];

    public function mount(array $filters = []): void
    {
        $this->allConstellations = DB::table('constellations')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->allObjectTypes = DB::table('deepskytypes')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->toArray();

        $catalogList = DB::table('objectnames')
            ->select('catalog')
            ->whereNotNull('catalog')
            ->where('catalog', '<>', '')
            ->distinct()
            ->orderBy('catalog')
            ->pluck('catalog')
            ->toArray();
        // Associative format (value => label) required by the searchable-multiselect component
        $this->allCatalogs = array_combine($catalogList, $catalogList);

        // Only expose atlases that have a corresponding column on the objects table.
        $atlasOptions = DB::table('atlases')
            ->orderBy('name')
            ->get(['code', 'name']);
        $this->allAtlases = $atlasOptions
            ->filter(fn($a) => preg_match('/^[A-Za-z0-9_]+$/', (string) $a->code) && Schema::hasColumn('objects', (string) $a->code))
            ->mapWithKeys(fn($a) => [(string) $a->code => (string) $a->name])
            ->toArray();

        // Observing lists from legacy DB: current user's lists + all public lists.
        try {
            $authUser = Auth::user();
            $legacyUser = (string) ($authUser?->username ?? '');
            $listRows = DB::connection('mysqlOld')
                ->table('observerobjectlist')
                ->select('observerid', 'listname', 'public')
                ->where('listname', '<>', '')
                ->where(function ($q) use ($legacyUser) {
                    if ($legacyUser !== '') {
                        $q->where('observerid', $legacyUser);
                    }
                    $q->orWhere('public', 1);
                })
                ->distinct()
                ->orderBy('listname')
                ->orderBy('observerid')
                ->get();

            $this->allObservingLists = $listRows
                ->mapWithKeys(function ($row) use ($legacyUser) {
                    $owner = (string) $row->observerid;
                    $name = (string) $row->listname;
                    $displayName = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $isPublic = intval($row->public) === 1;
                    $key = $owner . '::' . $name;

                    $label = $displayName;
                    if ($owner !== '') {
                        $label .= ' (' . $owner . ')';
                    }
                    if ($isPublic && $owner !== $legacyUser) {
                        $label .= ' - ' . __('public');
                    }

                    return [$key => $label];
                })
                ->toArray();
        } catch (\Throwable $_) {
            $this->allObservingLists = [];
        }

        try {
            $authUser = Auth::user();
            $this->isContrastReserveConfigured = (bool) ($authUser && $authUser->standardLocation && $authUser->stdtelescope);
        } catch (\Throwable $_) {
            $this->isContrastReserveConfigured = false;
        }

        if (!empty($filters)) {
            $this->hydrateFromFilters($filters);
        }

        $this->refreshAvailableFilterTypes();
    }

    private function hydrateFromFilters(array $filters): void
    {
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

        $status = (string) ($filters['observing_status'] ?? 'all');
        $this->observingStatus = array_key_exists($status, $this->observingStatusOptions) ? $status : 'all';

        $this->observingLists = array_values(array_filter(array_map('strval', (array) ($filters['observing_lists'] ?? []))));
        $listMode = (string) ($filters['observing_lists_mode'] ?? 'in');
        $this->observingListsMode = array_key_exists($listMode, $this->observingListsModeOptions) ? $listMode : 'in';

        $active = array_values(array_filter(array_map('strval', (array) ($filters['activeFilters'] ?? []))));

        $rawNameCatalogs = $filters['name_search_catalogs'] ?? ($filters['name_search_catalog'] ?? []);
        if (!is_array($rawNameCatalogs)) {
            $rawNameCatalogs = [$rawNameCatalogs];
        }
        $this->nameSearchCatalogs = array_values(array_filter(array_map('strval', $rawNameCatalogs), fn($v) => $v !== ''));
        $this->nameSearchNumber = (string) ($filters['name_search_number'] ?? '');
        $this->nameText = (string) ($filters['name_text'] ?? '');
        $this->descriptionText = (string) ($filters['description_text'] ?? '');
        $mode = (string) ($filters['description_mode'] ?? 'and');
        $this->descriptionMode = array_key_exists($mode, $this->descriptionModeOptions) ? $mode : 'and';
        $this->atlasCode = (string) ($filters['atlas_code'] ?? '');
        $this->atlasPageSpec = (string) ($filters['atlas_page_spec'] ?? '');
        $this->atlasPageMin = (string) ($filters['atlas_page_min'] ?? '');
        $this->atlasPageMax = (string) ($filters['atlas_page_max'] ?? '');

        if (empty($active)) {
            if (!empty($this->constellations))
                $active[] = 'constellations';
            if (!empty($this->objectTypes))
                $active[] = 'objectTypes';
            if (!empty($this->objectCategories))
                $active[] = 'objectCategories';
            if (!empty($this->catalogsInclude))
                $active[] = 'catalogsInclude';
            if (!empty($this->catalogsExclude))
                $active[] = 'catalogsExclude';
            if ($this->magMin !== '' || $this->magMax !== '')
                $active[] = 'magnitude';
            if ($this->subrMin !== '' || $this->subrMax !== '')
                $active[] = 'surfaceBrightness';
            if ($this->diam1Min !== '' || $this->diam1Max !== '')
                $active[] = 'diam1';
            if ($this->diam2Min !== '' || $this->diam2Max !== '')
                $active[] = 'diam2';
            if ($this->ratioMin !== '' || $this->ratioMax !== '')
                $active[] = 'ratio';
            if ($this->contrastReserveMin !== '' || $this->contrastReserveMax !== '')
                $active[] = 'contrastReserve';
            if ($this->raMin !== '' || $this->raMax !== '')
                $active[] = 'ra';
            if ($this->declMin !== '' || $this->declMax !== '')
                $active[] = 'decl';
            if ($this->observingStatus !== 'all')
                $active[] = 'observingStatus';
            if (!empty($this->observingLists))
                $active[] = 'observingLists';
            if (!empty($this->nameSearchCatalogs) || $this->nameSearchNumber !== '')
                $active[] = 'nameSearch';
            if ($this->nameText !== '')
                $active[] = 'nameText';
            if ($this->descriptionText !== '')
                $active[] = 'descriptionText';
            if ($this->atlasCode !== '' || $this->atlasPageSpec !== '' || $this->atlasPageMin !== '' || $this->atlasPageMax !== '')
                $active[] = 'atlasPage';
        }

        $this->activeFilters = array_values(array_intersect($this->validFilterTypes(), array_unique($active)));
    }

    // ── Filter row management ─────────────────────────────────────────────

    public function addFilter(string $type): void
    {
        if (!in_array($type, $this->validFilterTypes(), true)) {
            return;
        }
        if (in_array($type, $this->activeFilters, true)) {
            return;
        }
        $this->activeFilters[] = $type;
        $this->refreshAvailableFilterTypes();
    }

    public function removeFilter(string $type): void
    {
        $this->activeFilters = array_values(array_filter($this->activeFilters, fn($f) => $f !== $type));
        $this->refreshAvailableFilterTypes();
        // Reset values for this filter type so they don't ghost into searches
        $this->resetFilterValues($type);
    }

    private function resetFilterValues(string $type): void
    {
        match ($type) {
            'constellations' => $this->constellations = [],
            'objectTypes' => $this->objectTypes = [],
            'objectCategories' => $this->objectCategories = [],
            'catalogsInclude' => $this->catalogsInclude = [],
            'catalogsExclude' => $this->catalogsExclude = [],
            'magnitude' => [$this->magMin, $this->magMax] = ['', ''],
            'surfaceBrightness' => [$this->subrMin, $this->subrMax] = ['', ''],
            'diam1' => [$this->diam1Min, $this->diam1Max] = ['', ''],
            'diam2' => [$this->diam2Min, $this->diam2Max] = ['', ''],
            'ratio' => [$this->ratioMin, $this->ratioMax] = ['', ''],
            'contrastReserve' => [$this->contrastReserveMin, $this->contrastReserveMax] = ['', ''],
            'ra' => [$this->raMin, $this->raMax] = ['', ''],
            'decl' => [$this->declMin, $this->declMax] = ['', ''],
            'observingStatus' => $this->observingStatus = 'all',
            'observingLists' => [$this->observingLists, $this->observingListsMode] = [[], 'in'],
            'nameSearch' => [$this->nameSearchCatalogs, $this->nameSearchNumber] = [[], ''],
            'nameText' => $this->nameText = '',
            'descriptionText' => [$this->descriptionText, $this->descriptionMode] = ['', 'and'],
            'atlasPage' => [$this->atlasCode, $this->atlasPageSpec, $this->atlasPageMin, $this->atlasPageMax] = ['', '', '', ''],
            default => null,
        };
    }

    private function validFilterTypes(): array
    {
        return [
            'constellations',
            'objectTypes',
            'objectCategories',
            'catalogsInclude',
            'catalogsExclude',
            'magnitude',
            'surfaceBrightness',
            'diam1',
            'diam2',
            'ratio',
            'contrastReserve',
            'ra',
            'decl',
            'observingStatus',
            'observingLists',
            'nameSearch',
            'nameText',
            'descriptionText',
            'atlasPage',
        ];
    }

    private function filterTypeLabels(): array
    {
        return [
            'constellations' => __('Constellations'),
            'objectTypes' => __('Object type'),
            'objectCategories' => __('Object categories'),
            'catalogsInclude' => __('Include catalogs'),
            'catalogsExclude' => __('Exclude catalogs'),
            'magnitude' => __('Magnitude (min/max)'),
            'surfaceBrightness' => __('Surface brightness (min/max)'),
            'diam1' => __('Diameter 1 arcmin (min/max)'),
            'diam2' => __('Diameter 2 arcmin (min/max)'),
            'ratio' => __('Diam1/Diam2 ratio (min/max)'),
            'contrastReserve' => __('Contrast reserve (min/max)'),
            'ra' => __('Right ascension hours (min/max)'),
            'decl' => __('Declination degrees (min/max)'),
            'observingStatus' => __('Observing status'),
            'observingLists' => __('Observing lists'),
            'nameSearch' => __('Object name'),
            'nameText' => __('Name contains text'),
            'descriptionText' => __('Description contains text'),
            'atlasPage' => __('Atlas page'),
        ];
    }

    private function refreshAvailableFilterTypes(): void
    {
        $labels = $this->filterTypeLabels();
        $this->availableFilterTypes = [];
        foreach ($this->validFilterTypes() as $type) {
            if (!in_array($type, $this->activeFilters, true)) {
                $this->availableFilterTypes[$type] = $labels[$type];
            }
        }
    }

    // ── Search ────────────────────────────────────────────────────────────

    public function search(): void
    {
        $this->errorMessage = '';

        $params = $this->buildParams();

        if (empty($params)) {
            $this->errorMessage = __('Please add at least one filter before searching.');
            return;
        }

        $this->redirect(route('search.advanced.results', $params));
    }

    private function buildParams(): array
    {
        $params = [];

        if (in_array('constellations', $this->activeFilters) && !empty($this->constellations)) {
            $params['constellations'] = $this->constellations;
        }
        if (in_array('objectTypes', $this->activeFilters) && !empty($this->objectTypes)) {
            $params['object_types'] = $this->objectTypes;
        }
        if (in_array('objectCategories', $this->activeFilters) && !empty($this->objectCategories)) {
            $params['object_categories'] = $this->objectCategories;
        }
        if (in_array('catalogsInclude', $this->activeFilters) && !empty($this->catalogsInclude)) {
            $params['catalogs_include'] = $this->catalogsInclude;
        }
        if (in_array('catalogsExclude', $this->activeFilters) && !empty($this->catalogsExclude)) {
            $params['catalogs_exclude'] = $this->catalogsExclude;
        }
        if (in_array('magnitude', $this->activeFilters)) {
            if ($this->magMin !== '')
                $params['mag_min'] = $this->magMin;
            if ($this->magMax !== '')
                $params['mag_max'] = $this->magMax;
        }
        if (in_array('surfaceBrightness', $this->activeFilters)) {
            if ($this->subrMin !== '')
                $params['subr_min'] = $this->subrMin;
            if ($this->subrMax !== '')
                $params['subr_max'] = $this->subrMax;
        }
        if (in_array('diam1', $this->activeFilters)) {
            if ($this->diam1Min !== '')
                $params['diam1_min'] = $this->diam1Min;
            if ($this->diam1Max !== '')
                $params['diam1_max'] = $this->diam1Max;
        }
        if (in_array('diam2', $this->activeFilters)) {
            if ($this->diam2Min !== '')
                $params['diam2_min'] = $this->diam2Min;
            if ($this->diam2Max !== '')
                $params['diam2_max'] = $this->diam2Max;
        }
        if (in_array('ratio', $this->activeFilters)) {
            if ($this->ratioMin !== '')
                $params['ratio_min'] = $this->ratioMin;
            if ($this->ratioMax !== '')
                $params['ratio_max'] = $this->ratioMax;
        }
        if (in_array('contrastReserve', $this->activeFilters)) {
            if ($this->contrastReserveMin !== '')
                $params['cr_min'] = $this->contrastReserveMin;
            if ($this->contrastReserveMax !== '')
                $params['cr_max'] = $this->contrastReserveMax;
        }
        if (in_array('ra', $this->activeFilters)) {
            if ($this->raMin !== '')
                $params['ra_min'] = $this->raMin;
            if ($this->raMax !== '')
                $params['ra_max'] = $this->raMax;
        }
        if (in_array('decl', $this->activeFilters)) {
            if ($this->declMin !== '')
                $params['decl_min'] = $this->declMin;
            if ($this->declMax !== '')
                $params['decl_max'] = $this->declMax;
        }
        if (in_array('observingStatus', $this->activeFilters) && $this->observingStatus !== 'all') {
            $params['observing_status'] = $this->observingStatus;
        }
        if (in_array('observingLists', $this->activeFilters) && !empty($this->observingLists)) {
            $params['observing_lists'] = $this->observingLists;
            if ($this->observingListsMode !== 'in') {
                $params['observing_lists_mode'] = $this->observingListsMode;
            }
        }

        if (in_array('nameSearch', $this->activeFilters)) {
            if (!empty($this->nameSearchCatalogs))
                $params['name_search_catalogs'] = $this->nameSearchCatalogs;
            if ($this->nameSearchNumber !== '')
                $params['name_search_number'] = $this->nameSearchNumber;
        }
        if (in_array('nameText', $this->activeFilters) && $this->nameText !== '') {
            $params['name_text'] = $this->nameText;
        }
        if (in_array('descriptionText', $this->activeFilters) && $this->descriptionText !== '') {
            $params['description_text'] = $this->descriptionText;
            if ($this->descriptionMode !== 'and') {
                $params['description_mode'] = $this->descriptionMode;
            }
        }

        if (in_array('atlasPage', $this->activeFilters)) {
            if ($this->atlasCode !== '')
                $params['atlas_code'] = $this->atlasCode;
            if ($this->atlasPageSpec !== '') {
                $params['atlas_page_spec'] = $this->atlasPageSpec;
            }
            if ($this->atlasPageMin !== '')
                $params['atlas_page_min'] = $this->atlasPageMin;
            if ($this->atlasPageMax !== '')
                $params['atlas_page_max'] = $this->atlasPageMax;
        }

        return $params;
    }

    // ── Saved searches ────────────────────────────────────────────────────

    public function saveSearch(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->errorMessage = __('You must be logged in to save searches.');
            return;
        }
        $name = trim($this->saveName);
        if ($name === '') {
            $this->errorMessage = __('Please enter a name for the saved search.');
            return;
        }
        $params = $this->buildParams();
        if (empty($params)) {
            $this->errorMessage = __('No filters to save.');
            return;
        }

        $saved = SavedObjectSearch::create([
            'user_id' => $user->id,
            'name' => $name,
            'filters' => array_merge(['activeFilters' => $this->activeFilters], $params),
        ]);

        $this->loadedId = $saved->id;
        $this->saveName = '';
        $this->errorMessage = '';
    }

    public function loadSearch(int $id): void
    {
        $user = Auth::user();
        if (!$user)
            return;

        $saved = SavedObjectSearch::where('id', $id)->where('user_id', $user->id)->first();
        if (!$saved)
            return;

        $filters = $saved->filters;
        $this->hydrateFromFilters($filters);
        $this->loadedId = $id;
        $this->errorMessage = '';
        $this->refreshAvailableFilterTypes();
    }

    public function deleteSearch(int $id): void
    {
        $user = Auth::user();
        if (!$user)
            return;
        SavedObjectSearch::where('id', $id)->where('user_id', $user->id)->delete();
        if ($this->loadedId === $id) {
            $this->loadedId = null;
        }
    }

    public function getSavedSearchesProperty(): array
    {
        $user = Auth::user();
        if (!$user)
            return [];
        return SavedObjectSearch::where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.advanced-object-search', [
            'savedSearches' => $this->savedSearches,
        ]);
    }
}
