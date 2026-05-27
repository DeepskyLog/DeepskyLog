<?php

namespace App\Livewire;

use App\Models\SavedObservationSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class AdvancedObservationSearch extends Component
{
    public array $activeFilters = [];

    public array $constellations = [];
    public array $objectTypes = [];
    public array $objectCategories = [];
    public array $allCategories = [
        'objects' => 'Deep-sky objects',
        'cometobjects' => 'Comets',
        'planets' => 'Planets',
        'moons' => 'Moons',
        'asteroids' => 'Asteroids',
        'lunar_features' => 'Lunar features',
    ];
    public array $catalogsInclude = [];
    public array $catalogsExclude = [];
    public array $nameSearchCatalogs = [];
    public string $nameSearchNumber = '';
    public string $nameText = '';
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
    private array $allObservingStatusOptions = [
        'all' => 'All observations',
        'seen_any' => 'Only observations of objects that have already been seen',
        'drawn_any' => 'Only observations of objects that have been drawn',
        'unseen_any' => 'Only observations of objects that have not been seen',
        'undrawn_any' => 'Only observations of objects that have not been drawn',
        'seen_by_me' => 'Only observations of objects that have been seen by me',
        'drawn_by_me' => 'Only observations of objects that have been drawn by me',
        'unseen_by_me' => 'Only observations of objects that have not been seen by me',
        'undrawn_by_me' => 'Only observations of objects that have not been drawn by me',
        'seen_by_others_not_me' => 'Observed by someone else but not by me',
        'drawn_by_others_not_me' => 'Drawn by someone else but not by me',
    ];
    public array $observingLists = [];
    public string $observingListsMode = 'in';
    public array $observingListsModeOptions = [
        'in' => 'In selected observing list(s)',
        'not_in' => 'Not in selected observing list(s)',
    ];
    public string $atlasCode = '';
    public string $atlasPageSpec = '';
    public string $atlasPageMin = '';
    public string $atlasPageMax = '';

    public string $observedFrom = '';
    public string $observedTo = '';
    public array $observers = [];
    public string $apertureMin = '';
    public string $apertureMax = '';
    public string $seeingMax = '';
    public string $transparencyMax = '';
    public string $visibilityMin = '';
    public string $visibilityMax = '';
    public string $latitudeMin = '';
    public string $latitudeMax = '';
    public string $drawingState = 'all';
    public string $observationDescription = '';
    public array $languages = [];

    public string $saveName = '';
    public ?int $loadedId = null;
    public string $errorMessage = '';
    public bool $isContrastReserveConfigured = false;
    public array $availableFilterTypes = [];

    public array $allConstellations = [];
    public array $allObjectTypes = [];
    public array $allCatalogs = [];
    public array $allLanguages = [];
    public array $allAtlases = [];
    public array $allObservingLists = [];

    public array $drawingStateOptions = [
        'all' => 'All observations',
        'with' => 'Only observations with drawing',
        'without' => 'Only observations without drawing',
    ];

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
        $this->allCatalogs = !empty($catalogList) ? array_combine($catalogList, $catalogList) : [];

        $atlasOptions = DB::table('atlases')
            ->orderBy('name')
            ->get(['code', 'name']);
        $this->allAtlases = $atlasOptions
            ->filter(fn($atlas) => preg_match('/^[A-Za-z0-9_]+$/', (string) $atlas->code) && Schema::hasColumn('objects', (string) $atlas->code))
            ->mapWithKeys(fn($atlas) => [(string) $atlas->code => (string) $atlas->name])
            ->toArray();

        try {
            $authUser = Auth::user();
            $userId = $authUser?->id;
            $ownedLists = [];
            $subscribedLists = [];
            $publicLists = [];

            if ($userId) {
                $ownedLists = DB::table('observing_lists')
                    ->where('owner_user_id', $userId)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->mapWithKeys(function ($list) use ($authUser) {
                        $displayName = html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        return [$list->id => $displayName . ' (' . $authUser->username . ')'];
                    })
                    ->toArray();

                $subscribedLists = DB::table('observing_list_subscriptions')
                    ->join('observing_lists', 'observing_list_subscriptions.observing_list_id', '=', 'observing_lists.id')
                    ->join('users', 'observing_lists.owner_user_id', '=', 'users.id')
                    ->where('observing_list_subscriptions.user_id', $userId)
                    ->orderBy('observing_lists.name')
                    ->get(['observing_lists.id', 'observing_lists.name', 'users.username'])
                    ->mapWithKeys(function ($list) {
                        $displayName = html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        return [$list->id => $displayName . ' (' . $list->username . ')'];
                    })
                    ->toArray();
            }

            $publicLists = DB::table('observing_lists')
                ->join('users', 'observing_lists.owner_user_id', '=', 'users.id')
                ->where('observing_lists.public', 1)
                ->where(function ($query) use ($userId) {
                    if ($userId) {
                        $query->where('observing_lists.owner_user_id', '<>', $userId);
                    }
                })
                ->orderBy('observing_lists.name')
                ->get(['observing_lists.id', 'observing_lists.name', 'users.username'])
                ->mapWithKeys(function ($list) {
                    $displayName = html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    return [$list->id => $displayName . ' (' . $list->username . ') - ' . __('public')];
                })
                ->toArray();

            $this->allObservingLists = array_merge($ownedLists, $subscribedLists, $publicLists);
        } catch (\Throwable $_) {
            $this->allObservingLists = [];
        }

        try {
            $authUser = Auth::user();
            $this->isContrastReserveConfigured = (bool) ($authUser && $authUser->standardLocation && $authUser->stdtelescope);
        } catch (\Throwable $_) {
            $this->isContrastReserveConfigured = false;
        }

        $availableLanguageOptions = collect(config('app.available_locales', []))
            ->mapWithKeys(fn($code, $label) => [mb_strtolower(trim((string) $code)) => (string) $label]);

        $languageCodes = DB::table('observations')
            ->select('language')
            ->whereNotNull('language')
            ->where('language', '<>', '')
            ->distinct()
            ->pluck('language')
            ->map(fn($language) => mb_strtolower(trim((string) $language)))
            ->filter(fn(string $language) => $language !== '' && $availableLanguageOptions->has($language))
            ->unique()
            ->sortBy(fn(string $language) => $availableLanguageOptions->get($language))
            ->values();

        $this->allLanguages = $languageCodes
            ->mapWithKeys(fn(string $language) => [$language => $availableLanguageOptions->get($language)])
            ->toArray();

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

        $rawNameCatalogs = $filters['name_search_catalogs'] ?? [];
        if (!is_array($rawNameCatalogs)) {
            $rawNameCatalogs = [$rawNameCatalogs];
        }
        $this->nameSearchCatalogs = array_values(array_filter(array_map('strval', $rawNameCatalogs), fn($value) => $value !== ''));
        $this->nameSearchNumber = (string) ($filters['name_search_number'] ?? '');
        $this->nameText = (string) ($filters['name_text'] ?? '');
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
        $availableOptions = $this->observingStatusOptions;
        $this->observingStatus = array_key_exists($status, $availableOptions) ? $status : 'all';
        $this->observingLists = array_values(array_filter(array_map('strval', (array) ($filters['observing_lists'] ?? []))));
        $listMode = (string) ($filters['observing_lists_mode'] ?? 'in');
        $this->observingListsMode = array_key_exists($listMode, $this->observingListsModeOptions) ? $listMode : 'in';
        $this->atlasCode = (string) ($filters['atlas_code'] ?? '');
        $this->atlasPageSpec = (string) ($filters['atlas_page_spec'] ?? '');
        $this->atlasPageMin = (string) ($filters['atlas_page_min'] ?? '');
        $this->atlasPageMax = (string) ($filters['atlas_page_max'] ?? '');

        $this->observedFrom = (string) ($filters['observed_from'] ?? '');
        $this->observedTo = (string) ($filters['observed_to'] ?? '');
        $this->observers = $this->normalizeObserverTerms((array) ($filters['observers'] ?? []));
        $this->apertureMin = (string) ($filters['aperture_min'] ?? '');
        $this->apertureMax = (string) ($filters['aperture_max'] ?? '');
        $this->seeingMax = (string) ($filters['seeing_max'] ?? '');
        $this->transparencyMax = (string) ($filters['transparency_max'] ?? '');
        $this->visibilityMin = (string) ($filters['visibility_min'] ?? '');
        $this->visibilityMax = (string) ($filters['visibility_max'] ?? '');
        $this->latitudeMin = $this->normalizeLatitudeValue($filters['latitude_min'] ?? '');
        $this->latitudeMax = $this->normalizeLatitudeValue($filters['latitude_max'] ?? '');
        $this->drawingState = (string) ($filters['drawing_state'] ?? 'all');
        if (!array_key_exists($this->drawingState, $this->drawingStateOptions)) {
            $this->drawingState = 'all';
        }
        $this->observationDescription = (string) ($filters['observation_description'] ?? '');
        $this->languages = $this->normalizeLanguageCodes((array) ($filters['languages'] ?? []));

        $active = array_values(array_filter(array_map('strval', (array) ($filters['activeFilters'] ?? []))));
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
            if (!empty($this->nameSearchCatalogs) || $this->nameSearchNumber !== '')
                $active[] = 'nameSearch';
            if ($this->nameText !== '')
                $active[] = 'nameText';
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
            if ($this->atlasCode !== '' || $this->atlasPageSpec !== '' || $this->atlasPageMin !== '' || $this->atlasPageMax !== '')
                $active[] = 'atlasPage';
            if ($this->observedFrom !== '' || $this->observedTo !== '')
                $active[] = 'observedBetween';
            if (!empty(array_filter($this->observers, fn($value) => trim((string) $value) !== '')))
                $active[] = 'observers';
            if ($this->apertureMin !== '' || $this->apertureMax !== '')
                $active[] = 'aperture';
            if ($this->seeingMax !== '')
                $active[] = 'seeing';
            if ($this->transparencyMax !== '')
                $active[] = 'transparency';
            if ($this->visibilityMin !== '' || $this->visibilityMax !== '')
                $active[] = 'visibility';
            if ($this->latitudeMin !== '' || $this->latitudeMax !== '')
                $active[] = 'latitude';
            if ($this->drawingState !== 'all')
                $active[] = 'drawing';
            if ($this->observationDescription !== '')
                $active[] = 'observationDescription';
            if (!empty($this->languages))
                $active[] = 'languages';
        }

        $this->activeFilters = array_values(array_intersect($this->validFilterTypes(), array_unique($active)));
    }

    private function normalizeObserverTerms(array $terms): array
    {
        return array_values(array_filter(
            array_map(fn($value) => trim((string) $value), $terms),
            fn($value) => $value !== ''
        ));
    }

    private function normalizeLanguageCodes(array $languages): array
    {
        $availableCodes = collect(config('app.available_locales', []))
            ->map(fn($code) => mb_strtolower(trim((string) $code)))
            ->filter()
            ->unique();

        return collect($languages)
            ->map(fn($language) => mb_strtolower(trim((string) $language)))
            ->filter(fn($language) => $language !== '' && $availableCodes->contains($language))
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeLatitudeValue(mixed $value): string
    {
        $text = trim((string) $value);
        if ($text === '' || !is_numeric($text)) {
            return $text === '' ? '' : $text;
        }

        $latitude = max(-90.0, min(90.0, (float) $text));

        return rtrim(rtrim(sprintf('%.10F', $latitude), '0'), '.');
    }

    public function updatedLatitudeMin(mixed $value): void
    {
        $this->latitudeMin = $this->normalizeLatitudeValue($value);
    }

    public function updatedLatitudeMax(mixed $value): void
    {
        $this->latitudeMax = $this->normalizeLatitudeValue($value);
    }

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
        $this->activeFilters = array_values(array_filter($this->activeFilters, fn($filter) => $filter !== $type));
        $this->refreshAvailableFilterTypes();
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
            'nameSearch' => [$this->nameSearchCatalogs, $this->nameSearchNumber] = [[], ''],
            'nameText' => $this->nameText = '',
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
            'atlasPage' => [$this->atlasCode, $this->atlasPageSpec, $this->atlasPageMin, $this->atlasPageMax] = ['', '', '', ''],
            'observedBetween' => [$this->observedFrom, $this->observedTo] = ['', ''],
            'observers' => $this->observers = [],
            'aperture' => [$this->apertureMin, $this->apertureMax] = ['', ''],
            'seeing' => $this->seeingMax = '',
            'transparency' => $this->transparencyMax = '',
            'visibility' => [$this->visibilityMin, $this->visibilityMax] = ['', ''],
            'latitude' => [$this->latitudeMin, $this->latitudeMax] = ['', ''],
            'drawing' => $this->drawingState = 'all',
            'observationDescription' => $this->observationDescription = '',
            'languages' => $this->languages = [],
            default => null,
        };
    }

    private function validFilterTypes(): array
    {
        return [
            'nameText',
            'nameSearch',
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
            'atlasPage',
            'observedBetween',
            'observers',
            'aperture',
            'seeing',
            'transparency',
            'visibility',
            'latitude',
            'drawing',
            'observationDescription',
            'languages',
        ];
    }

    private function filterTypeLabels(): array
    {
        return [
            'nameText' => __('Object name contains text'),
            'nameSearch' => __('Object name (catalog + number)'),
            'constellations' => __('Constellations'),
            'objectTypes' => __('Object type'),
            'objectCategories' => __('Object categories'),
            'catalogsInclude' => __('Include catalogs'),
            'catalogsExclude' => __('Exclude catalogs'),
            'magnitude' => __('Object magnitude'),
            'surfaceBrightness' => __('Surface brightness'),
            'diam1' => __('Diameter 1 arcmin'),
            'diam2' => __('Diameter 2 arcmin'),
            'ratio' => __('Diam1 / Diam2 ratio'),
            'contrastReserve' => __('Contrast reserve'),
            'ra' => __('Right ascension'),
            'decl' => __('Declination'),
            'observingStatus' => __('Observing status'),
            'observingLists' => __('Observing lists'),
            'atlasPage' => __('Atlas page'),
            'observedBetween' => __('Observation date'),
            'observers' => __('Observers'),
            'aperture' => __('Instrument diameter'),
            'seeing' => __('Worst seeing'),
            'transparency' => __('Worst transparency'),
            'visibility' => __('Best / worst visibility'),
            'latitude' => __('Observation latitude'),
            'drawing' => __('Drawing'),
            'observationDescription' => __('Observation description contains'),
            'languages' => __('Observation languages'),
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

    public function search(): void
    {
        $this->errorMessage = '';

        $params = $this->buildParams();
        if (empty($params)) {
            $this->errorMessage = __('Please add at least one filter before searching.');
            return;
        }

        $this->redirect(route('search.observations.advanced.results', $params));
    }

    private function buildParams(): array
    {
        $params = [];

        if (in_array('nameText', $this->activeFilters, true) && $this->nameText !== '')
            $params['name_text'] = $this->nameText;
        if (in_array('nameSearch', $this->activeFilters, true)) {
            if (!empty($this->nameSearchCatalogs))
                $params['name_search_catalogs'] = $this->nameSearchCatalogs;
            if ($this->nameSearchNumber !== '')
                $params['name_search_number'] = $this->nameSearchNumber;
        }
        if (in_array('constellations', $this->activeFilters, true) && !empty($this->constellations))
            $params['constellations'] = $this->constellations;
        if (in_array('objectTypes', $this->activeFilters, true) && !empty($this->objectTypes))
            $params['object_types'] = $this->objectTypes;
        if (in_array('objectCategories', $this->activeFilters, true) && !empty($this->objectCategories))
            $params['object_categories'] = $this->objectCategories;
        if (in_array('catalogsInclude', $this->activeFilters, true) && !empty($this->catalogsInclude))
            $params['catalogs_include'] = $this->catalogsInclude;
        if (in_array('catalogsExclude', $this->activeFilters, true) && !empty($this->catalogsExclude))
            $params['catalogs_exclude'] = $this->catalogsExclude;
        if (in_array('magnitude', $this->activeFilters, true)) {
            if ($this->magMin !== '')
                $params['mag_min'] = $this->magMin;
            if ($this->magMax !== '')
                $params['mag_max'] = $this->magMax;
        }
        if (in_array('surfaceBrightness', $this->activeFilters, true)) {
            if ($this->subrMin !== '')
                $params['subr_min'] = $this->subrMin;
            if ($this->subrMax !== '')
                $params['subr_max'] = $this->subrMax;
        }
        if (in_array('diam1', $this->activeFilters, true)) {
            if ($this->diam1Min !== '')
                $params['diam1_min'] = $this->diam1Min;
            if ($this->diam1Max !== '')
                $params['diam1_max'] = $this->diam1Max;
        }
        if (in_array('diam2', $this->activeFilters, true)) {
            if ($this->diam2Min !== '')
                $params['diam2_min'] = $this->diam2Min;
            if ($this->diam2Max !== '')
                $params['diam2_max'] = $this->diam2Max;
        }
        if (in_array('ratio', $this->activeFilters, true)) {
            if ($this->ratioMin !== '')
                $params['ratio_min'] = $this->ratioMin;
            if ($this->ratioMax !== '')
                $params['ratio_max'] = $this->ratioMax;
        }
        if (in_array('contrastReserve', $this->activeFilters, true)) {
            if ($this->contrastReserveMin !== '')
                $params['cr_min'] = $this->contrastReserveMin;
            if ($this->contrastReserveMax !== '')
                $params['cr_max'] = $this->contrastReserveMax;
        }
        if (in_array('ra', $this->activeFilters, true)) {
            if ($this->raMin !== '')
                $params['ra_min'] = $this->raMin;
            if ($this->raMax !== '')
                $params['ra_max'] = $this->raMax;
        }
        if (in_array('decl', $this->activeFilters, true)) {
            if ($this->declMin !== '')
                $params['decl_min'] = $this->declMin;
            if ($this->declMax !== '')
                $params['decl_max'] = $this->declMax;
        }
        if (in_array('observingStatus', $this->activeFilters, true) && $this->observingStatus !== 'all') {
            $params['observing_status'] = $this->observingStatus;
        }
        if (in_array('observingLists', $this->activeFilters, true) && !empty($this->observingLists)) {
            $params['observing_lists'] = $this->observingLists;
            if ($this->observingListsMode !== 'in') {
                $params['observing_lists_mode'] = $this->observingListsMode;
            }
        }
        if (in_array('atlasPage', $this->activeFilters, true)) {
            if ($this->atlasCode !== '')
                $params['atlas_code'] = $this->atlasCode;
            if ($this->atlasPageSpec !== '')
                $params['atlas_page_spec'] = $this->atlasPageSpec;
            if ($this->atlasPageMin !== '')
                $params['atlas_page_min'] = $this->atlasPageMin;
            if ($this->atlasPageMax !== '')
                $params['atlas_page_max'] = $this->atlasPageMax;
        }
        if (in_array('observedBetween', $this->activeFilters, true)) {
            if ($this->observedFrom !== '')
                $params['observed_from'] = $this->observedFrom;
            if ($this->observedTo !== '')
                $params['observed_to'] = $this->observedTo;
        }
        if (in_array('observers', $this->activeFilters, true)) {
            $observers = array_values(array_filter(array_map('trim', $this->observers), fn($value) => $value !== ''));
            if (!empty($observers))
                $params['observers'] = $observers;
        }
        if (in_array('aperture', $this->activeFilters, true)) {
            if ($this->apertureMin !== '')
                $params['aperture_min'] = $this->apertureMin;
            if ($this->apertureMax !== '')
                $params['aperture_max'] = $this->apertureMax;
        }
        if (in_array('seeing', $this->activeFilters, true) && $this->seeingMax !== '')
            $params['seeing_max'] = $this->seeingMax;
        if (in_array('transparency', $this->activeFilters, true) && $this->transparencyMax !== '')
            $params['transparency_max'] = $this->transparencyMax;
        if (in_array('visibility', $this->activeFilters, true)) {
            if ($this->visibilityMin !== '')
                $params['visibility_min'] = $this->visibilityMin;
            if ($this->visibilityMax !== '')
                $params['visibility_max'] = $this->visibilityMax;
        }
        if (in_array('latitude', $this->activeFilters, true)) {
            $this->latitudeMin = $this->normalizeLatitudeValue($this->latitudeMin);
            $this->latitudeMax = $this->normalizeLatitudeValue($this->latitudeMax);

            if ($this->latitudeMin !== '' && is_numeric($this->latitudeMin))
                $params['latitude_min'] = $this->latitudeMin;
            if ($this->latitudeMax !== '' && is_numeric($this->latitudeMax))
                $params['latitude_max'] = $this->latitudeMax;
        }
        if (in_array('drawing', $this->activeFilters, true) && $this->drawingState !== 'all')
            $params['drawing_state'] = $this->drawingState;
        if (in_array('observationDescription', $this->activeFilters, true) && $this->observationDescription !== '') {
            $params['observation_description'] = $this->observationDescription;
        }
        if (in_array('languages', $this->activeFilters, true) && !empty($this->languages))
            $params['languages'] = $this->languages;

        if (!empty($params)) {
            $params['activeFilters'] = $this->activeFilters;
        }

        return $params;
    }

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

        $saved = SavedObservationSearch::create([
            'user_id' => $user->id,
            'name' => $name,
            'filters' => $params,
        ]);

        $this->loadedId = $saved->id;
        $this->saveName = '';
        $this->errorMessage = '';
    }

    public function loadSearch(int $id): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $saved = SavedObservationSearch::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        if (!$saved) {
            return;
        }

        $this->hydrateFromFilters($saved->filters ?? []);
        $this->loadedId = $id;
        $this->errorMessage = '';
        $this->refreshAvailableFilterTypes();
    }

    public function deleteSearch(int $id): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        SavedObservationSearch::where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        if ($this->loadedId === $id) {
            $this->loadedId = null;
        }
    }

    public function getSavedSearchesProperty(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        return SavedObservationSearch::where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function getObservingStatusOptionsProperty(): array
    {
        if (!Auth::check()) {
            return array_filter(
                $this->allObservingStatusOptions,
                fn($key) => !in_array($key, [
                    'seen_by_me',
                    'drawn_by_me',
                    'unseen_by_me',
                    'undrawn_by_me',
                    'seen_by_others_not_me',
                    'drawn_by_others_not_me',
                ]),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $this->allObservingStatusOptions;
    }

    public function render()
    {
        return view('livewire.advanced-observation-search', [
            'savedSearches' => $this->savedSearches,
            'observingStatusOptions' => $this->observingStatusOptions,
        ]);
    }
}