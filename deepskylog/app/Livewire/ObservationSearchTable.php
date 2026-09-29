<?php

namespace App\Livewire;

use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ObservationSearchTable extends PowerGridComponent
{
    use WithExport;

    private const IMPORT_EXPORT_HEADERS = [
        'Object name',
        'Observer',
        'Date',
        'UT Time',
        'Location',
        'Instrument',
        'Eyepiece',
        'Filter',
        'Lens / Barlow',
        'Seeing',
        'Lim. magnitude',
        'SQM',
        'Visibility',
        'Language',
        'Description',
    ];

    public string $tableName = 'observation-search-table';

    public string $sortField = 'observations.observed_on';
    public string $sortDirection = 'desc';

    /** Object name filter passed in from the page (bound via :objectName="$q"). */
    public string $objectName = '';
    public array $constellations = [];
    public array $objectTypes = [];
    public array $objectCategories = [];
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
    public array $observingLists = [];
    public string $observingListsMode = 'in';
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

    public function mount(string $objectName = '', array $filters = []): void
    {
        parent::mount();

        $this->objectName = $objectName;

        if (!empty($filters)) {
            $this->hydrateAdvancedFilters($filters);
        }
    }

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->persist(['columns', 'filters']);

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),

            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),

            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::detail()
                ->view('livewire.observation-search-row-detail')
                ->showCollapseIcon(),
        ];
    }

    public function datasource(): Builder
    {
        $query = Observation::query()
            ->leftJoin('objects', 'observations.target_name', '=', 'objects.name')
            ->leftJoin('constellations', 'objects.con', '=', 'constellations.id')
            ->leftJoin('instruments', 'observations.instrument_id', '=', 'instruments.id')
            ->leftJoin('locations', 'observations.location_id', '=', 'locations.id')
            ->leftJoin('deepsky_observation_details', 'observations.id', '=', 'deepsky_observation_details.observation_id')
            ->leftJoin('users', 'observations.observer_username', '=', 'users.username')
            ->select([
                'observations.id',
                'observations.target_name',
                'observations.observer_username',
                'observations.observed_on',
                'observations.legacy_date_int',
                'observations.has_drawing',
                'observations.drawing_filename',
                'observations.legacy_source',
                'observations.legacy_id',
                'observations.language',
                'observations.seeing',
                'observations.transparency',
                'observations.description',
                'observations.target_table',
                DB::raw('objects.con as constellation_abbr'),
                DB::raw('constellations.name as constellation'),
                DB::raw('COALESCE(users.name, observations.observer_username) as observer_display'),
                DB::raw('users.slug as observer_slug'),
                DB::raw('instruments.aperture_mm as aperture_mm'),
                DB::raw('locations.latitude as location_latitude'),
                DB::raw('deepsky_observation_details.visibility as visibility'),
            ]);

        if ($this->objectName !== '') {
            $query->where('observations.target_name', 'LIKE', '%' . $this->objectName . '%');
        }

        $this->applyAdvancedFilters($query);

        return $query;
    }

    private function hydrateAdvancedFilters(array $filters): void
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
        $this->observingStatus = (string) ($filters['observing_status'] ?? 'all');
        $this->observingLists = array_values(array_filter(array_map('strval', (array) ($filters['observing_lists'] ?? []))));
        $listsMode = (string) ($filters['observing_lists_mode'] ?? 'in');
        $this->observingListsMode = in_array($listsMode, ['in', 'not_in'], true) ? $listsMode : 'in';
        $this->atlasCode = (string) ($filters['atlas_code'] ?? '');
        $this->atlasPageSpec = (string) ($filters['atlas_page_spec'] ?? '');
        $this->atlasPageMin = (string) ($filters['atlas_page_min'] ?? '');
        $this->atlasPageMax = (string) ($filters['atlas_page_max'] ?? '');
        $this->observedFrom = (string) ($filters['observed_from'] ?? '');
        $this->observedTo = (string) ($filters['observed_to'] ?? '');
        $this->observers = array_values(array_filter(array_map('trim', (array) ($filters['observers'] ?? [])), fn($value) => $value !== ''));
        $this->apertureMin = (string) ($filters['aperture_min'] ?? '');
        $this->apertureMax = (string) ($filters['aperture_max'] ?? '');
        $this->seeingMax = (string) ($filters['seeing_max'] ?? '');
        $this->transparencyMax = (string) ($filters['transparency_max'] ?? '');
        $this->visibilityMin = (string) ($filters['visibility_min'] ?? '');
        $this->visibilityMax = (string) ($filters['visibility_max'] ?? '');
        $this->latitudeMin = (string) ($filters['latitude_min'] ?? '');
        $this->latitudeMax = (string) ($filters['latitude_max'] ?? '');
        $this->drawingState = (string) ($filters['drawing_state'] ?? 'all');
        $this->observationDescription = (string) ($filters['observation_description'] ?? '');
        $this->languages = collect((array) ($filters['languages'] ?? []))
            ->map(fn($language) => mb_strtolower(trim((string) $language)))
            ->filter(fn($language) => $language !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function applyAdvancedFilters(Builder $query): void
    {
        if (!empty($this->objectCategories)) {
            $query->whereIn('observations.target_table', $this->objectCategories);
        }

        if (!empty($this->constellations)) {
            $query->whereIn('objects.con', $this->constellations);
        }

        if (!empty($this->objectTypes)) {
            $query->whereIn('objects.type', $this->objectTypes);
        }

        if (!empty($this->catalogsInclude)) {
            $include = array_map('strval', $this->catalogsInclude);
            $query->whereIn('observations.target_name', function ($subQuery) use ($include) {
                $subQuery->from('objectnames')
                    ->select('objectname')
                    ->whereIn('catalog', $include);
            });
        }

        if (!empty($this->catalogsExclude)) {
            $exclude = array_map('strval', $this->catalogsExclude);
            $query->whereNotIn('observations.target_name', function ($subQuery) use ($exclude) {
                $subQuery->from('objectnames')
                    ->select('objectname')
                    ->whereIn('catalog', $exclude);
            });
        }

        if (!empty($this->nameSearchCatalogs) || $this->nameSearchNumber !== '') {
            $catalogs = array_values(array_filter(array_map('trim', $this->nameSearchCatalogs), fn($value) => $value !== ''));
            $number = trim($this->nameSearchNumber);
            $query->whereIn('observations.target_name', function ($subQuery) use ($catalogs, $number) {
                $subQuery->from('objectnames')->select('objectname');
                if (!empty($catalogs)) {
                    $subQuery->whereIn('catalog', $catalogs);
                }
                if ($number !== '') {
                    $subQuery->where('catindex', $number);
                }
            });
        }

        if ($this->nameText !== '') {
            $needle = mb_strtolower(trim($this->nameText));
            $query->where(function (Builder $subQuery) use ($needle) {
                $subQuery->whereRaw("LOWER(COALESCE(observations.target_name, '')) LIKE ?", ['%' . $needle . '%'])
                    ->orWhereIn('observations.target_name', function ($nameQuery) use ($needle) {
                        $nameQuery->from('objectnames')
                            ->select('objectname')
                            ->whereRaw("LOWER(COALESCE(altname, '')) LIKE ?", ['%' . $needle . '%']);
                    });
            });
        }

        if ($this->magMin !== '' && is_numeric($this->magMin)) {
            $query->where('objects.mag', '>=', (float) $this->magMin);
        }
        if ($this->magMax !== '' && is_numeric($this->magMax)) {
            $query->where('objects.mag', '<=', (float) $this->magMax);
        }
        if ($this->subrMin !== '' && is_numeric($this->subrMin)) {
            $query->where('objects.subr', '>=', (float) $this->subrMin);
        }
        if ($this->subrMax !== '' && is_numeric($this->subrMax)) {
            $query->where('objects.subr', '<=', (float) $this->subrMax);
        }
        if ($this->diam1Min !== '' && is_numeric($this->diam1Min)) {
            $query->where('objects.diam1', '>=', (float) $this->diam1Min);
        }
        if ($this->diam1Max !== '' && is_numeric($this->diam1Max)) {
            $query->where('objects.diam1', '<=', (float) $this->diam1Max);
        }
        if ($this->diam2Min !== '' && is_numeric($this->diam2Min)) {
            $query->where('objects.diam2', '>=', (float) $this->diam2Min);
        }
        if ($this->diam2Max !== '' && is_numeric($this->diam2Max)) {
            $query->where('objects.diam2', '<=', (float) $this->diam2Max);
        }
        if (($this->ratioMin !== '' && is_numeric($this->ratioMin)) || ($this->ratioMax !== '' && is_numeric($this->ratioMax))) {
            $query->whereNotNull('objects.diam1')
                ->where('objects.diam1', '>', 0)
                ->whereNotNull('objects.diam2')
                ->where('objects.diam2', '>', 0);
            if ($this->ratioMin !== '' && is_numeric($this->ratioMin)) {
                $query->whereRaw('(objects.diam1 / objects.diam2) >= ?', [(float) $this->ratioMin]);
            }
            if ($this->ratioMax !== '' && is_numeric($this->ratioMax)) {
                $query->whereRaw('(objects.diam1 / objects.diam2) <= ?', [(float) $this->ratioMax]);
            }
        }
        if ($this->raMin !== '' && is_numeric($this->raMin)) {
            $query->where('objects.ra', '>=', (float) $this->raMin);
        }
        if ($this->raMax !== '' && is_numeric($this->raMax)) {
            $query->where('objects.ra', '<=', (float) $this->raMax);
        }
        if ($this->declMin !== '' && is_numeric($this->declMin)) {
            $query->where('objects.decl', '>=', (float) $this->declMin);
        }
        if ($this->declMax !== '' && is_numeric($this->declMax)) {
            $query->where('objects.decl', '<=', (float) $this->declMax);
        }

        if (!empty($this->observingLists)) {
            $listIds = array_values(array_filter(array_map(fn($entry) => ($id = (int) $entry) > 0 ? $id : null, $this->observingLists)));

            if (empty($listIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $listNames = DB::table('observing_list_items')
                    ->whereIn('observing_list_id', $listIds)
                    ->distinct()
                    ->pluck('object_name')
                    ->toArray();

                if ($this->observingListsMode === 'not_in') {
                    if (!empty($listNames)) {
                        $query->whereNotIn('observations.target_name', $listNames);
                    }
                } elseif (empty($listNames)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('observations.target_name', $listNames);
                }
            }
        }

        $atlasCode = trim((string) $this->atlasCode);
        $atlasSpec = trim((string) $this->atlasPageSpec);
        $atlasMin = trim((string) $this->atlasPageMin);
        $atlasMax = trim((string) $this->atlasPageMax);
        if ($atlasCode !== '' || $atlasSpec !== '' || $atlasMin !== '' || $atlasMax !== '') {
            if ($atlasCode !== '' && preg_match('/^[A-Za-z0-9_]+$/', $atlasCode) && Schema::hasColumn('objects', $atlasCode)) {
                $query->where('observations.target_table', 'objects');

                $atlasExpr = "NULLIF(TRIM(objects.`{$atlasCode}`), '')";

                if ($atlasSpec !== '') {
                    $parsed = $this->parseAtlasPageSpec($atlasSpec);
                    $exactPages = $parsed['pages'];
                    $ranges = $parsed['ranges'];

                    if (empty($exactPages) && empty($ranges)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->where(function ($atlasQuery) use ($atlasExpr, $exactPages, $ranges) {
                            if (!empty($exactPages)) {
                                $atlasQuery->orWhereRaw("CAST({$atlasExpr} AS UNSIGNED) IN (" . implode(',', array_fill(0, count($exactPages), '?')) . ')', $exactPages);
                            }

                            foreach ($ranges as [$start, $end]) {
                                $atlasQuery->orWhereRaw("CAST({$atlasExpr} AS UNSIGNED) BETWEEN ? AND ?", [$start, $end]);
                            }
                        });
                    }
                } else {
                    if ($atlasMin !== '' && is_numeric($atlasMin)) {
                        $query->whereRaw("CAST({$atlasExpr} AS UNSIGNED) >= ?", [(int) $atlasMin]);
                    }
                    if ($atlasMax !== '' && is_numeric($atlasMax)) {
                        $query->whereRaw("CAST({$atlasExpr} AS UNSIGNED) <= ?", [(int) $atlasMax]);
                    }
                    if ($atlasMin === '' && $atlasMax === '') {
                        $query->whereRaw("{$atlasExpr} IS NOT NULL");
                    }
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->observedFrom !== '') {
            $query->whereDate('observations.observed_on', '>=', $this->observedFrom);
        }
        if ($this->observedTo !== '') {
            $query->whereDate('observations.observed_on', '<=', $this->observedTo);
        }

        if (!empty($this->observers)) {
            $observerTerms = $this->observers;
            $query->where(function (Builder $subQuery) use ($observerTerms) {
                foreach ($observerTerms as $term) {
                    $needle = mb_strtolower($term);
                    $subQuery->orWhereRaw("LOWER(COALESCE(observations.observer_username, '')) LIKE ?", ['%' . $needle . '%'])
                        ->orWhereRaw("LOWER(COALESCE(users.name, '')) LIKE ?", ['%' . $needle . '%']);
                }
            });
        }

        if ($this->apertureMin !== '' && is_numeric($this->apertureMin)) {
            $query->where('instruments.aperture_mm', '>=', (float) $this->apertureMin);
        }
        if ($this->apertureMax !== '' && is_numeric($this->apertureMax)) {
            $query->where('instruments.aperture_mm', '<=', (float) $this->apertureMax);
        }
        if ($this->seeingMax !== '' && is_numeric($this->seeingMax)) {
            $query->whereNotNull('observations.seeing')
                ->where('observations.seeing', '<=', (int) $this->seeingMax);
        }
        if ($this->transparencyMax !== '' && is_numeric($this->transparencyMax)) {
            $query->whereNotNull('observations.transparency')
                ->where('observations.transparency', '<=', (int) $this->transparencyMax);
        }
        if ($this->visibilityMin !== '' && is_numeric($this->visibilityMin)) {
            $query->whereNotNull('deepsky_observation_details.visibility')
                ->where('deepsky_observation_details.visibility', '>=', (int) $this->visibilityMin);
        }
        if ($this->visibilityMax !== '' && is_numeric($this->visibilityMax)) {
            $query->whereNotNull('deepsky_observation_details.visibility')
                ->where('deepsky_observation_details.visibility', '<=', (int) $this->visibilityMax);
        }
        if ($this->latitudeMin !== '' && is_numeric($this->latitudeMin)) {
            $query->where('locations.latitude', '>=', (float) $this->latitudeMin);
        }
        if ($this->latitudeMax !== '' && is_numeric($this->latitudeMax)) {
            $query->where('locations.latitude', '<=', (float) $this->latitudeMax);
        }
        if ($this->drawingState === 'with') {
            $query->where('observations.has_drawing', true);
        } elseif ($this->drawingState === 'without') {
            $query->where('observations.has_drawing', false);
        }
        if ($this->observationDescription !== '') {
            $query->whereRaw("LOWER(COALESCE(observations.description, '')) LIKE ?", ['%' . mb_strtolower($this->observationDescription) . '%']);
        }
        if (!empty($this->languages)) {
            $query->whereIn(DB::raw("LOWER(COALESCE(observations.language, ''))"), $this->languages);
        }

        $this->applyObservingStatusFilter($query);
        $this->applyContrastReserveFilter($query);
    }

    private function applyObservingStatusFilter(Builder $query): void
    {
        $status = trim((string) $this->observingStatus);
        if ($status === '' || $status === 'all' || $status === 'seen_any') {
            return;
        }

        $authUser = Auth::user();

        $applyExists = function (callable $constraint) use ($query): void {
            $query->whereExists(function ($subQuery) use ($constraint) {
                $subQuery->selectRaw('1')
                    ->from('observations as status_observations')
                    ->whereColumn('status_observations.target_table', 'observations.target_table')
                    ->whereRaw('COALESCE(status_observations.target_key, status_observations.target_name) = COALESCE(observations.target_key, observations.target_name)');

                $constraint($subQuery);
            });
        };

        $applyNotExists = function (callable $constraint) use ($query): void {
            $query->whereNotExists(function ($subQuery) use ($constraint) {
                $subQuery->selectRaw('1')
                    ->from('observations as status_observations')
                    ->whereColumn('status_observations.target_table', 'observations.target_table')
                    ->whereRaw('COALESCE(status_observations.target_key, status_observations.target_name) = COALESCE(observations.target_key, observations.target_name)');

                $constraint($subQuery);
            });
        };

        $userConstraint = function ($subQuery) use ($authUser): void {
            $subQuery->where(function ($userQuery) use ($authUser) {
                $userQuery->where('status_observations.observer_user_id', $authUser?->id ?? 0);
                if (!empty($authUser?->username)) {
                    $userQuery->orWhere('status_observations.observer_username', $authUser->username);
                }
            });
        };

        $otherUserConstraint = function ($subQuery) use ($authUser): void {
            $subQuery->where(function ($userQuery) use ($authUser) {
                if (!empty($authUser?->id)) {
                    $userQuery->where(function ($otherQuery) use ($authUser) {
                        $otherQuery->whereNotNull('status_observations.observer_user_id')
                            ->where('status_observations.observer_user_id', '!=', $authUser->id);
                    });
                }

                if (!empty($authUser?->username)) {
                    $method = !empty($authUser?->id) ? 'orWhere' : 'where';
                    $userQuery->{$method}(function ($otherQuery) use ($authUser) {
                        $otherQuery->whereNotNull('status_observations.observer_username')
                            ->where('status_observations.observer_username', '!=', $authUser->username);
                    });
                }
            });
        };

        switch ($status) {
            case 'drawn_any':
                $applyExists(fn($subQuery) => $subQuery->where('status_observations.has_drawing', true));
                break;
            case 'unseen_any':
                $query->whereRaw('1 = 0');
                break;
            case 'undrawn_any':
                $applyNotExists(fn($subQuery) => $subQuery->where('status_observations.has_drawing', true));
                break;
            case 'seen_by_me':
                if (!$authUser) {
                    $query->whereRaw('1 = 0');
                    break;
                }
                $applyExists($userConstraint);
                break;
            case 'drawn_by_me':
                if (!$authUser) {
                    $query->whereRaw('1 = 0');
                    break;
                }
                $applyExists(function ($subQuery) use ($userConstraint) {
                    $userConstraint($subQuery);
                    $subQuery->where('status_observations.has_drawing', true);
                });
                break;
            case 'unseen_by_me':
                if (!$authUser) {
                    $query->whereRaw('1 = 0');
                    break;
                }
                $applyNotExists($userConstraint);
                break;
            case 'undrawn_by_me':
                if (!$authUser) {
                    $query->whereRaw('1 = 0');
                    break;
                }
                $applyNotExists(function ($subQuery) use ($userConstraint) {
                    $userConstraint($subQuery);
                    $subQuery->where('status_observations.has_drawing', true);
                });
                break;
            case 'seen_by_others_not_me':
                if (!$authUser) {
                    $query->whereRaw('1 = 0');
                    break;
                }
                $applyExists($otherUserConstraint);
                $applyNotExists($userConstraint);
                break;
            case 'drawn_by_others_not_me':
                if (!$authUser) {
                    $query->whereRaw('1 = 0');
                    break;
                }
                $applyExists(function ($subQuery) use ($otherUserConstraint) {
                    $subQuery->where('status_observations.has_drawing', true)
                        ->where(function ($otherQuery) use ($otherUserConstraint) {
                            $otherUserConstraint($otherQuery);
                        });
                });
                $applyNotExists(function ($subQuery) use ($userConstraint) {
                    $userConstraint($subQuery);
                    $subQuery->where('status_observations.has_drawing', true);
                });
                break;
        }
    }

    private function applyContrastReserveFilter(Builder $query): void
    {
        $crMin = trim((string) $this->contrastReserveMin);
        $crMax = trim((string) $this->contrastReserveMax);
        if ($crMin === '' && $crMax === '') {
            return;
        }

        $authUser = Auth::user();
        $locationId = $authUser?->standardLocation?->id ?? null;
        $instrumentId = $authUser?->stdtelescope ?? null;
        $lensId = $authUser?->stdlens ?? null;

        if (!$authUser || !$locationId || !$instrumentId) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where('observations.target_table', 'objects');

        $contrastReserveExpression = null;
        if (!empty($lensId)) {
            $query->leftJoin('user_object_metrics as uom_lens', function ($join) use ($authUser, $instrumentId, $locationId, $lensId) {
                $join->on('objects.name', '=', 'uom_lens.object_name')
                    ->where('uom_lens.user_id', '=', $authUser->id)
                    ->where('uom_lens.instrument_id', '=', $instrumentId)
                    ->where('uom_lens.location_id', '=', $locationId)
                    ->where('uom_lens.lens_id', '=', $lensId);
            });
            $query->leftJoin('user_object_metrics as uom_default', function ($join) use ($authUser, $instrumentId, $locationId) {
                $join->on('objects.name', '=', 'uom_default.object_name')
                    ->where('uom_default.user_id', '=', $authUser->id)
                    ->where('uom_default.instrument_id', '=', $instrumentId)
                    ->where('uom_default.location_id', '=', $locationId)
                    ->whereNull('uom_default.lens_id');
            });
            $contrastReserveExpression = 'COALESCE(uom_lens.contrast_reserve, uom_default.contrast_reserve)';
        } else {
            $query->leftJoin('user_object_metrics as uom', function ($join) use ($authUser, $instrumentId, $locationId) {
                $join->on('objects.name', '=', 'uom.object_name')
                    ->where('uom.user_id', '=', $authUser->id)
                    ->where('uom.instrument_id', '=', $instrumentId)
                    ->where('uom.location_id', '=', $locationId)
                    ->whereNull('uom.lens_id');
            });
            $contrastReserveExpression = 'uom.contrast_reserve';
        }

        if ($crMin !== '' && is_numeric($crMin)) {
            $query->whereRaw("{$contrastReserveExpression} >= ?", [(float) $crMin]);
        }
        if ($crMax !== '' && is_numeric($crMax)) {
            $query->whereRaw("{$contrastReserveExpression} <= ?", [(float) $crMax]);
        }
    }

    /**
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

            if (preg_match('/^(\d+)$/', $token, $matches) === 1) {
                $page = (int) $matches[1];
                if ($page >= 0) {
                    $pages[] = $page;
                }
                continue;
            }

            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $token, $matches) === 1) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];
                if ($start >= 0 && $end >= 0) {
                    $ranges[] = [$start <= $end ? $start : $end, $start <= $end ? $end : $start];
                }
            }
        }

        return ['pages' => array_values(array_unique($pages)), 'ranges' => $ranges];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('target_name')
            ->add('target_name_link', function (object $row) {
                $name = $row->target_name ?? '';
                if (empty($name)) {
                    return '-';
                }
                // Build observation detail URL: /observation/{observer}/{date}/{object}
                $observer = $row->observer_slug ?: $row->observer_username;
                $date = null;
                if (!empty($row->observed_on)) {
                    $date = Carbon::parse($row->observed_on)->format('Y-m-d');
                } elseif (!empty($row->legacy_date_int)) {
                    try {
                        $date = Carbon::createFromFormat('Ymd', (string) $row->legacy_date_int)->format('Y-m-d');
                    } catch (\Throwable $_) {
                        $date = null;
                    }
                }
                if ($observer && $date) {
                    $objectSlug = Str::slug($name, '-');
                    $url = route('observations.detail', [$observer, $date, $objectSlug]);
                    return sprintf(
                        '<a href="%s" class="font-bold hover:underline text-blue-400">%s</a>',
                        e($url),
                        e($name)
                    );
                }
                return e($name);
            })
            ->add('constellation', fn(object $row) => $row->constellation ?? ($row->constellation_abbr ?? '-'))
            ->add('has_drawing')
            ->add('drawing_indicator', function (object $row) {
                if (empty($row->has_drawing)) {
                    return '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-600" aria-label="' . e(__('No drawing')) . '"></span>';
                }

                // Resolve drawing URL using the same logic as the detail view
                $filename = $row->drawing_filename ?? null;
                if (!empty($filename)) {
                    try {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
                        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

                        $drawingUrl = $publicDisk->exists($filename)
                            ? $publicDisk->url($filename)
                            : '/images/' . $filename;
                    } catch (\Throwable $_) {
                        $drawingUrl = '/images/' . $filename;
                    }
                } else {
                    $subfolder = ($row->legacy_source === 'comet') ? 'cometdrawings' : 'drawings';
                    $drawingUrl = '/images/' . $subfolder . '/' . $row->legacy_id . '.jpg';
                }

                $title = e(__('View drawing'));
                $drawingUrl = e($drawingUrl);
                return '<button type="button"'
                    . ' onclick="(function(){'
                    . 'var m=document.getElementById(\'obs-drawing-modal\');'
                    . 'var img=document.getElementById(\'obs-drawing-img\');'
                    . 'if(img){img.src=\'' . $drawingUrl . '\';}'
                    . 'if(m){m.classList.remove(\'hidden\');}'
                    . '})()"'
                    . ' title="' . $title . '"'
                    . ' class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 cursor-pointer hover:bg-amber-400 focus:outline-none" aria-label="' . $title . '">'
                    . '<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 20 20" fill="currentColor">'
                    . '<path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>'
                    . '</svg></button>';
            })
            ->add('observer_display')
            ->add('observer_name_link', function (object $row) {
                $display = $row->observer_display ?? $row->observer_username ?? '';
                $slug = $row->observer_slug ?? null;
                if ($slug) {
                    return sprintf(
                        '<a href="%s" class="hover:underline">%s</a>',
                        e(route('observer.show', $slug)),
                        e($display)
                    );
                }
                return e($display);
            })
            ->add('aperture_mm', fn(object $row) => $row->aperture_mm !== null ? (int) $row->aperture_mm : '-')
            ->add('observed_on')
            ->add('date_formatted', function (object $row) {
                if (!empty($row->observed_on)) {
                    return Carbon::parse($row->observed_on)->format('d M Y');
                }
                if (!empty($row->legacy_date_int)) {
                    try {
                        return Carbon::createFromFormat('Ymd', (string) $row->legacy_date_int)->format('d M Y');
                    } catch (\Throwable $_) {
                        return (string) $row->legacy_date_int;
                    }
                }
                return '-';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('Drawing'), 'drawing_indicator', 'observations.has_drawing')
                ->visibleInExport(false)
                ->sortable(),

            Column::make(__('Object'), 'target_name_link', 'observations.target_name')
                ->sortable()
                ->searchable(),

            Column::make(__('Constellation'), 'constellation', 'constellations.name')
                ->sortable()
                ->searchable(),

            Column::make(__('Observer'), 'observer_name_link', 'observations.observer_username')
                ->sortable()
                ->searchable(),

            Column::make(__('Instrument diameter (mm)'), 'aperture_mm', 'instruments.aperture_mm')
                ->sortable(),

            Column::make(__('Date'), 'date_formatted', 'observations.observed_on')
                ->sortable(),
        ];
    }

    public function exportToCsv(bool $selected = false): BinaryFileResponse|bool
    {
        if ($selected && count($this->checkboxValues) === 0) {
            return false;
        }

        $rows = $this->buildImportCompatibleRows($selected);
        $path = $this->makeExportPath('csv');

        $writer = new CsvWriter(new CsvOptions(';', '"'));
        $writer->openToFile($path);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()
            ->download($path, $this->downloadName('csv'))
            ->deleteFileAfterSend(true);
    }

    public function exportToXLS(bool $selected = false): BinaryFileResponse|bool
    {
        if ($selected && count($this->checkboxValues) === 0) {
            return false;
        }

        $rows = $this->buildImportCompatibleRows($selected);
        $path = $this->makeExportPath('xlsx');

        $writer = new XlsxWriter();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(self::IMPORT_EXPORT_HEADERS));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return response()
            ->download($path, $this->downloadName('xlsx'))
            ->deleteFileAfterSend(true);
    }

    private function buildImportCompatibleRows(bool $selected): array
    {
        $ids = collect($this->prepareToExport($selected))
            ->pluck('id')
            ->filter(fn(mixed $id) => is_numeric($id))
            ->map(fn(mixed $id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $observations = Observation::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $users = User::query()
            ->whereIn('username', $observations->pluck('observer_username')->filter()->unique())
            ->get(['username', 'name'])
            ->keyBy('username');

        $locations = Location::query()
            ->whereIn('id', $observations->pluck('location_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $instruments = Instrument::query()
            ->whereIn('id', $observations->pluck('instrument_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $eyepieces = \App\Models\Eyepiece::query()
            ->whereIn('id', $observations->pluck('eyepiece_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $filters = Filter::query()
            ->whereIn('id', $observations->pluck('filter_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $lenses = Lens::query()
            ->whereIn('id', $observations->pluck('lens_id')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $visibilities = DB::table('deepsky_observation_details')
            ->whereIn('observation_id', $ids)
            ->pluck('visibility', 'observation_id');

        return $ids
            ->map(function (int $id) use ($observations, $users, $locations, $instruments, $eyepieces, $filters, $lenses, $visibilities): ?array {
                /** @var Observation|null $observation */
                $observation = $observations->get($id);

                if ($observation === null) {
                    return null;
                }

                $observer = trim((string) ($users->get($observation->observer_username)?->name ?: $observation->observer_username ?: ''));

                return [
                    (string) ($observation->target_name ?? ''),
                    $observer,
                    $this->formatImportDate($observation),
                    $this->formatImportTime($observation),
                    (string) ($locations->get($observation->location_id)?->name ?? ''),
                    (string) ($instruments->get($observation->instrument_id)?->name ?? ''),
                    (string) ($eyepieces->get($observation->eyepiece_id)?->name ?? ''),
                    (string) ($filters->get($observation->filter_id)?->name ?? ''),
                    (string) ($lenses->get($observation->lens_id)?->name ?? ''),
                    $this->nullableInteger($observation->seeing),
                    $this->decimalOrZero($observation->limiting_magnitude),
                    $this->decimalOrZero($observation->sqm),
                    $this->nullableInteger($visibilities->get($observation->id)),
                    $this->exportLanguage($observation->language),
                    $this->exportDescription($observation->description),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function formatImportDate(Observation $observation): string
    {
        if (!empty($observation->observed_on)) {
            return Carbon::parse($observation->observed_on)->format('d-m-Y');
        }

        if (!empty($observation->legacy_date_int)) {
            try {
                return Carbon::createFromFormat('Ymd', (string) $observation->legacy_date_int)->format('d-m-Y');
            } catch (\Throwable $_) {
            }
        }

        return '';
    }

    /**
     * UT time as HH:MM, or empty when the time is unknown (migrated
     * observations without a time have observed_time 23:59:00 and
     * legacy_time_int -9999).
     */
    private function formatImportTime(Observation $observation): string
    {
        $time = $observation->time;

        return $time === null ? '' : sprintf('%02d:%02d', intdiv($time, 100), $time % 100);
    }

    private function nullableInteger(mixed $value): string
    {
        return is_numeric($value) ? (string) (int) $value : '';
    }

    private function decimalOrZero(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return rtrim(rtrim((string) $value, '0'), '.');
    }

    private function exportLanguage(mixed $language): string
    {
        $value = trim((string) $language);

        return $value !== '' ? strtolower(substr($value, 0, 10)) : 'en';
    }

    private function exportDescription(mixed $value): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<br\s*\/?>/iu', '<br />', $text) ?? $text;
        $text = str_replace(["\r\n", "\r", "\n"], '<br />', $text);
        $text = preg_replace('/\s*<br \/>\s*/u', '<br />', $text) ?? $text;
        $text = preg_replace('/[\t ]+/u', ' ', $text) ?? $text;

        return $text;
    }

    private function makeExportPath(string $extension): string
    {
        return storage_path('app/' . Str::uuid() . '.' . $extension);
    }

    private function downloadName(string $extension): string
    {
        $baseName = strval(data_get($this->setUp, 'exportable.fileName', 'export'));

        return Str::slug($baseName) !== '' ? Str::slug($baseName) . '.' . $extension : 'export.' . $extension;
    }
}
