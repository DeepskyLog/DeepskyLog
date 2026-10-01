<?php

namespace App\Livewire;

use App\Support\SchemaColumns;
use App\Models\DeepskyObject;
use App\Models\Atlas;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ActiveObservingListService;
use App\Services\ObjectMetricsQueue;
use App\Services\ObjectObservationStatistics;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Models\UserTableSetting;

/**
 * Displays nearby objects in a PowerGrid table.
 */
class NearbyObjectsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'nearby-objects-table';
    public string $primaryKey = 'id';

    public int $objectId;
    public ?string $objectName = null;
    public ?float $ra = null;
    public ?float $decl = null;

    /** Largest radius the radius selector offers (2°), in arcminutes. */
    public const MAX_RADIUS_ARCMIN = 120;

    public int $radiusArcMin = 30;
    public int $perPage = 25;
    // Incrementing tick to force component re-render when external Aladin preview
    // selections change (instrument / eyepiece / lens). Bumped by the event
    // listener so datasource() / fields() are re-evaluated.
    public int $refreshTick = 0;
    // Preview selections forwarded from the Aladin preview (instrument/eyepiece/lens)
    // When set, these override the authenticated user's standard instrument/eyepiece
    // for the purpose of on-page computations like Best Mag and (optionally)
    // inline contrast reserve calculation.
    public ?int $previewInstrumentId = null;
    public ?int $previewEyepieceId = null;
    public ?int $previewLensId = null;

    /**
     * Indicates whether there are any objects with pending CR calculations
     * (across all pages, not just current page). Used by JavaScript to 
     * automatically poll for updates and stop when all calculations complete.
     * @var bool
     */
    public bool $hasPendingCalculations = false;

    /** The queued metric jobs of the nearby objects (ObjectMetricsQueue). */
    #[Locked]
    public ?string $metricsToken = null;

    /**
     * Cached eyepieces for the authenticated user during a single component render.
     * This prevents re-querying the DB for each table row which can cause high memory
     * and DB load when rendering many rows.
     *
     * @var array|null
     */
    private ?array $cachedEyepieces = null;

    /**
     * Ephemeris date forwarded from the Ephemerides aside (ISO date string YYYY-MM-DD).
     * When set, nearby rows will compute per-object ephemerides for this date.
     * @var string|null
     */
    public ?string $ephemerisDate = null;

    /**
     * In-memory cache of computed ephemerides for rows during a single render.
     * Keyed by object name + date to avoid recomputation in the same request.
     * @var array
     */
    private array $cachedEphemerides = [];

    /**
     * Per-render cached preview models to avoid N+1 lookups in row closures.
     * These are populated at the start of datasource() and reused by fields().
     * @var \App\Models\Instrument|null
     */
    private ?\App\Models\Instrument $previewInstrumentModel = null;

    /** @var \App\Models\Lens|null */
    private ?\App\Models\Lens $previewLensModel = null;

    /** @var \App\Models\Eyepiece|null */
    private ?\App\Models\Eyepiece $previewEyepieceModel = null;
    private ?bool $canModifyActiveListCached = null;

    /**
     * Cached type names to avoid N+1 queries when resolving type codes.
     * Keyed by type code (e.g., 'PLNNB') with values being the full names.
     * @var array<string, string>
     */
    private array $cachedTypeNames = [];

    /**
     * Persisted list of visible column fields (PowerGrid uses this in the toggle UI).
     * We persist it so toggling via the UI is remembered in the session.
     *
     * @var array
     */

    public ?string $atlasCode = null;
    public ?string $atlasName = null;
    public bool $includeAtlas = false;

    /**
     * Keep a radius coming from the client, the query string or the stored
     * settings within what the radius selector offers.
     */
    public static function clampRadius(mixed $arcmin): int
    {
        return max(1, min(self::MAX_RADIUS_ARCMIN, (int) $arcmin));
    }

    public function updatedRadiusArcMin(): void
    {
        $this->radiusArcMin = self::clampRadius($this->radiusArcMin);
        $this->triggerBulkPrecompute();
    }

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);

        // Attempt to load persisted user table settings early in the component
        // lifecycle so setUp() can pick up values like perPage when it runs.
        try {
            $authUser = Auth::user();
            if ($authUser) {
                $row = UserTableSetting::where('user_id', $authUser->id)
                    ->where('table_name', $this->tableName)
                    ->first();
                if ($row && is_array($row->settings)) {
                    $s = $row->settings;
                    if (isset($s['perPage'])) {
                        $this->perPage = intval($s['perPage']);
                    }
                    if (isset($s['radiusArcMin'])) {
                        $this->radiusArcMin = self::clampRadius($s['radiusArcMin']);
                    }
                    if (isset($s['sortField'])) {
                        $this->sortField = $s['sortField'];
                    }
                    if (isset($s['sortDirection'])) {
                        $this->sortDirection = $s['sortDirection'];
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore early-load failures; mount() will try again
        }
    }

    public function setUp(): array
    {
        // Persist filters and column visibility so the user's choices survive reloads.
        // PowerGrid expects the key name 'columns' for persistence/restore, not 'visibleColumns'.
        $this->persist(['filters', 'columns']);
        return [
            PowerGrid::header()->showSearchInput()->showToggleColumns(),
            PowerGrid::footer()->showPerPage($this->perPage)->showRecordCount(),
            PowerGrid::responsive()->fixedColumns('name'),
            // Include custom 'argo' and 'skylist' export types so the export dropdown can show them.
            PowerGrid::exportable('export')->striped()->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV, 'argo', 'skylist', 'stxt', 'apd'),
        ];
    }

    public function mount(): void
    {
        if (method_exists(get_parent_class($this), 'mount')) {
            parent::mount();
        }

        $this->sortField = 'distance_deg';
        $this->sortDirection = 'asc';

        // Load persisted settings from DB for authenticated user
        try {
            $authUser = Auth::user();
            if ($authUser) {
                $row = UserTableSetting::where('user_id', $authUser->id)
                    ->where('table_name', $this->tableName)
                    ->first();
                if ($row && is_array($row->settings)) {
                    $s = $row->settings;
                    if (isset($s['sortField'])) {
                        $this->sortField = $s['sortField'];
                        // Remap old field names to new computed column names
                        if ($this->sortField === 'contrast_reserve') {
                            $this->sortField = 'computed_contrast_reserve';
                        }
                        if ($this->sortField === 'best_mag') {
                            $this->sortField = 'computed_best_mag';
                        }
                    }
                    if (isset($s['sortDirection'])) {
                        $this->sortDirection = $s['sortDirection'];
                    }
                    if (isset($s['radiusArcMin'])) {
                        $this->radiusArcMin = self::clampRadius($s['radiusArcMin']);
                    }
                    if (isset($s['perPage'])) {
                        $this->perPage = intval($s['perPage']);
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore DB failures and continue with defaults
        }

        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', $authUser->standardAtlasCode)) {
                $this->includeAtlas = true;
                $this->atlasCode = (string) $authUser->standardAtlasCode;
                try {
                    $m = Atlas::where('code', (string) $this->atlasCode)->first();
                    if (!$m) {
                        $m = Atlas::where('code', (string) $this->atlasCode)->first();
                    }
                    $this->atlasName = $m?->name ?? null;
                } catch (\Throwable $_) {
                    $this->atlasName = null;
                }
            } else {
                $this->includeAtlas = false;
                $this->atlasCode = null;
                $this->atlasName = null;
            }
        } catch (\Throwable $_) {
            $this->includeAtlas = false;
            $this->atlasCode = null;
            $this->atlasName = null;
        }

        // Trigger bulk precompute on initial page load
        $this->triggerBulkPrecompute();

        // Check and dispatch initial pending calculations state
        $this->updateHasPendingCalculations();
    }

    /**
     * Called before each render - check and update pending calculations state.
     */
    public function rendering(): void
    {
        $this->updateHasPendingCalculations();
    }

    /**
     * Update the hasPendingCalculations property from the cache (no query)
     * and tell the JavaScript polling whether to go on.
     */
    protected function updateHasPendingCalculations(): void
    {
        $this->hasPendingCalculations = ObjectMetricsQueue::pending($this->metricsToken);
        if (!$this->hasPendingCalculations) {
            $this->metricsToken = null;
        }

        try {
            $this->dispatch('hasPendingCalculationsUpdated', hasPending: $this->hasPendingCalculations);
        } catch (\Throwable $_) {
        }
    }

    /**
     * Queue the metrics (contrast reserve, best magnification) missing for
     * the nearby objects with the selected instrument, location and lens, as
     * one job, instead of computing them in the request.
     */
    protected function triggerBulkPrecompute(): void
    {
        try {
            $authUser = Auth::user();
            $instrId = $this->previewInstrumentId ?? ($authUser?->standardInstrument?->id ?? null);
            $locId = $authUser?->standardLocation?->id ?? null;
            if ($authUser && $instrId && $locId && is_numeric($this->ra) && is_numeric($this->decl)) {
                $this->metricsToken = ObjectMetricsQueue::queueMissing(
                    $authUser->id,
                    (int) $instrId,
                    (int) $locId,
                    $this->previewLensId,
                    $this->nearbyObjectNames()
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * The names of the objects within the radius, in one light query: the
     * declination band first (indexed), then the exact distance.
     *
     * @return list<string>
     */
    private function nearbyObjectNames(): array
    {
        $radiusDeg = self::clampRadius($this->radiusArcMin) / 60.0;
        $centerRaDeg = floatval($this->ra);
        if ($centerRaDeg <= 24.0) {
            $centerRaDeg = $centerRaDeg * 15.0;
        }
        $expr = "DEGREES(ACOS(LEAST(1, GREATEST(-1, SIN(RADIANS(?))*SIN(RADIANS(`decl`)) + COS(RADIANS(?))*COS(RADIANS(`decl`))*COS(RADIANS((?)-(CASE WHEN `ra` <= 24 THEN `ra`*15 ELSE `ra` END))) ))))";

        return DB::table('objects')
            ->whereBetween('decl', [floatval($this->decl) - $radiusDeg, floatval($this->decl) + $radiusDeg])
            ->whereRaw("{$expr} <= ?", [$this->decl, $this->decl, $centerRaDeg, $radiusDeg])
            ->when(!empty($this->objectName), fn($query) => $query->where('name', '<>', $this->objectName))
            ->when(empty($this->objectName) && !empty($this->objectId), fn($query) => $query->where('name', '<>', (string) $this->objectId))
            ->pluck('name')
            ->all();
    }

    public function datasource(): ?Builder
    {
        $dsStart = microtime(true);
        if ($this->ra === null || $this->decl === null) {
            return DeepskyObject::query()->whereRaw('0 = 1');
        }

        // Populate per-render cached preview models to avoid doing DB lookups
        // inside per-row closures (which causes N+1 queries).
        try {
            if (!empty($this->previewInstrumentId)) {
                $this->previewInstrumentModel = \App\Models\Instrument::find($this->previewInstrumentId);
            } else {
                $this->previewInstrumentModel = null;
            }

            if (!empty($this->previewLensId)) {
                $this->previewLensModel = \App\Models\Lens::find($this->previewLensId);
            } else {
                $this->previewLensModel = null;
            }

            if (!empty($this->previewEyepieceId)) {
                $this->previewEyepieceModel = \App\Models\Eyepiece::find($this->previewEyepieceId);
            } else {
                $this->previewEyepieceModel = null;
            }
        } catch (\Throwable $_) {
            $this->previewInstrumentModel = null;
            $this->previewLensModel = null;
            $this->previewEyepieceModel = null;
        }

        $radiusDeg = self::clampRadius($this->radiusArcMin) / 60.0;

        $centerRaDeg = floatval($this->ra);
        if ($centerRaDeg <= 24.0) {
            $centerRaDeg = $centerRaDeg * 15.0;
        }

        $expr = "DEGREES(ACOS(LEAST(1, GREATEST(-1, SIN(RADIANS(?))*SIN(RADIANS(`decl`)) + COS(RADIANS(?))*COS(RADIANS(`decl`))*COS(RADIANS((?)-(CASE WHEN `ra` <= 24 THEN `ra`*15 ELSE `ra` END))) ))))";

        $atlasSelect = '';
        if ($this->includeAtlas && $this->atlasCode && preg_match('/^[A-Za-z0-9_]+$/', (string) $this->atlasCode)) {
            $acol = (string) $this->atlasCode;
            $atlasSelect = ", objects.`{$acol}` as atlas_page";
        }

        // Don't include placeholder columns in the inner query to avoid duplicate column names
        // when the outer query uses addSelect() to add the actual computed values.
        $selectRaw = "objects.* , constellations.name as constellation, deepskytypes.name as type_name, GREATEST(COALESCE(objects.diam1,0), COALESCE(objects.diam2,0)) as size, {$expr} as distance_deg, objects.name as id" . $atlasSelect;

        $inActiveListSelect = ', 0 as in_active_list';
        try {
            $authUser = Auth::user();
            if ($authUser) {
                /** @var ActiveObservingListService $svc */
                $svc = app(ActiveObservingListService::class);
                $activeList = $svc->getActiveList($authUser);
                if ($activeList) {
                    $activeListId = (int) $activeList->id;
                    $inActiveListSelect = ", EXISTS(SELECT 1 FROM observing_list_items oli WHERE oli.observing_list_id = {$activeListId} AND oli.object_name = objects.name) as in_active_list";
                }
            }
        } catch (\Throwable $_) {
            $inActiveListSelect = ', 0 as in_active_list';
        }

        $selectRaw .= $inActiveListSelect;

        // Bindings for the distance expression (used in both the SELECT and WHERE).
        $exprBindings = [$this->decl, $this->decl, $centerRaDeg];
        // Separate bindings for the full SELECT. Keep them distinct so we can
        // pass only the required values to each whereRaw/selectRaw call.
        $selectBindings = $exprBindings;

        // Observation statistics (total_observations, your_last_seen_date, ...),
        // exposed by the inner select so the outer wrapper can sort on them.
        $selectRaw .= ', ' . ObjectObservationStatistics::selectList();

        $baseQuery = DeepskyObject::query()
            ->leftJoin('constellations', 'constellations.id', '=', 'objects.con')
            ->leftJoin('deepskytypes', 'deepskytypes.code', '=', 'objects.type')
            ->selectRaw($selectRaw, $selectBindings);
        ObjectObservationStatistics::join($baseQuery, Auth::user()?->username);

        // Apply a cheap bounding-box prefilter on decl using numeric literals
        // to avoid modifying the prepared statement binding order. This lets
        // the DB use a decl index to narrow the candidate rows before the
        // more expensive trig distance calculation. We avoid adding bound
        // parameters here so the existing selectRaw/whereRaw binding order
        // (used for the distance expression) remains stable.
        $minDecl = floatval($this->decl) - $radiusDeg;
        $maxDecl = floatval($this->decl) + $radiusDeg;
        $baseQuery->whereRaw("`decl` BETWEEN {$minDecl} AND {$maxDecl}");

        // Apply the distance filter (exact spherical distance) after the
        // decl bounding box so the expensive calculation runs on far fewer
        // rows. Keep parameter bindings for the distance expression as-is.
        $baseQuery->whereRaw("{$expr} <= ?", array_merge($exprBindings, [$radiusDeg]));

        if (!empty($this->objectName)) {
            $baseQuery->where('objects.name', '<>', $this->objectName);
        } elseif (!empty($this->objectId)) {
            $baseQuery->where('objects.name', '<>', (string) $this->objectId);
        }

        $sub = $baseQuery->toBase();

        $outer = DeepskyObject::query();
        $outer->getQuery()->fromSub($sub, 'objects');
        $outer->select('objects.*');

        try {
            $authUser = Auth::user();
            if ($authUser && $authUser->standardLocation && ($authUser->standardInstrument || $this->previewInstrumentId)) {
                // Prefer the preview instrument forwarded from the Aladin preview
                // when present; otherwise fall back to the user's standard instrument.
                $instrId = $this->previewInstrumentId ?? ($authUser->standardInstrument->id ?? null);
                $locId = $authUser->standardLocation->id ?? null;
                $previewLens = $this->previewLensId ?? null;
                if ($instrId && $locId) {
                    // If a preview lens is active, prefer lens-specific cached metrics
                    // but fall back to the lens-less (legacy) metrics when available.
                    if (!empty($previewLens)) {
                        $outer->leftJoin('user_object_metrics as uom_lens', function ($join) use ($instrId, $locId, $authUser, $previewLens) {
                            $join->on('objects.name', '=', 'uom_lens.object_name')
                                ->where('uom_lens.user_id', '=', $authUser->id)
                                ->where('uom_lens.instrument_id', '=', $instrId)
                                ->where('uom_lens.location_id', '=', $locId)
                                ->where('uom_lens.lens_id', '=', $previewLens);
                        });

                        // Also join the legacy lens-less metrics so we can prefer lens-specific values
                        // but fall back to them when specific rows are missing.
                        $outer->leftJoin('user_object_metrics as uom_default', function ($join) use ($instrId, $locId, $authUser) {
                            $join->on('objects.name', '=', 'uom_default.object_name')
                                ->where('uom_default.user_id', '=', $authUser->id)
                                ->where('uom_default.instrument_id', '=', $instrId)
                                ->where('uom_default.location_id', '=', $locId)
                                ->whereNull('uom_default.lens_id');
                        });

                        $outer->addSelect([
                            DB::raw('COALESCE(uom_lens.contrast_reserve, uom_default.contrast_reserve) as computed_contrast_reserve'),
                            DB::raw('COALESCE(uom_lens.contrast_reserve_category, uom_default.contrast_reserve_category) as computed_contrast_reserve_category'),
                            // Prefetch stored optimum detection magnification so the column can be sorted server-side
                            DB::raw('COALESCE(uom_lens.optimum_detection_magnification, uom_default.optimum_detection_magnification) as computed_best_mag'),
                            // Flag to indicate whether a metric exists (for distinguishing NULL from no-record)
                            DB::raw('COALESCE(uom_lens.id, uom_default.id) as metric_exists'),
                        ]);
                    } else {
                        // No preview lens: join legacy lens-less metrics (preserves previous behavior)
                        $outer->leftJoin('user_object_metrics as uom', function ($join) use ($instrId, $locId, $authUser) {
                            $join->on('objects.name', '=', 'uom.object_name')
                                ->where('uom.user_id', '=', $authUser->id)
                                ->where('uom.instrument_id', '=', $instrId)
                                ->where('uom.location_id', '=', $locId)
                                ->whereNull('uom.lens_id');
                        });
                        $outer->addSelect([
                            'uom.contrast_reserve as computed_contrast_reserve',
                            'uom.contrast_reserve_category as computed_contrast_reserve_category',
                            // Prefetch stored optimum detection magnification so the column can be sorted server-side
                            'uom.optimum_detection_magnification as computed_best_mag',
                            // Flag to indicate whether a metric exists (for distinguishing NULL from no-record)
                            'uom.id as metric_exists',
                        ]);
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }

        $sortField = $this->sortField ?? 'distance_deg';
        $sortDirection = $this->sortDirection ?? 'asc';
        // Validate sort direction to accept only 'asc' or 'desc' (case-insensitive).
        $sortDirection = is_string($sortDirection) ? strtolower($sortDirection) : 'asc';
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        // Persist sort changes proactively here. PowerGrid sometimes invokes a
        // server-side method (eg. sortBy) rather than a simple property update
        // that our generic `updated()` hook sees. datasource() is called after
        // those operations, so use it to persist the current sort state when it
        // differs from what we have stored for the user.
        try {
            $authUser = Auth::user();
            if ($authUser) {
                $row = UserTableSetting::where('user_id', $authUser->id)
                    ->where('table_name', $this->tableName)
                    ->first();
                $existing = [];
                if ($row && is_array($row->settings)) {
                    $existing = $row->settings;
                } elseif ($row && is_string($row->settings)) {
                    $existing = json_decode($row->settings, true) ?? [];
                }

                $need = [];
                if (($existing['sortField'] ?? null) !== $sortField) {
                    $need['sortField'] = $sortField;
                }
                if (($existing['sortDirection'] ?? null) !== $sortDirection) {
                    $need['sortDirection'] = $sortDirection;
                }
                if (!empty($need)) {
                    $this->saveUserTableSettings($need);
                }
            }
        } catch (\Throwable $_) {
            // ignore persistence failures
        }
        $sortField = preg_replace('/.*\./', '', $sortField);

        // Remap old column names to computed column names to avoid ambiguity
        // when the JOIN has columns with the same name as our computed aliases.
        // Do this BEFORE the allowedSorts check so the remapped names are validated.
        if ($sortField === 'contrast_reserve') {
            $this->sortField = 'computed_contrast_reserve';
            $sortField = 'computed_contrast_reserve';
        }
        if ($sortField === 'best_mag') {
            $this->sortField = 'computed_best_mag';
            $sortField = 'computed_best_mag';
        }
        // Remap virtual field names to real column names
        if ($sortField === 'seen') {
            $this->sortField = 'total_observations';
            $sortField = 'total_observations';
        }
        if ($sortField === 'last_seen') {
            $this->sortField = 'your_last_seen_date';
            $sortField = 'your_last_seen_date';
        }

        $allowedSorts = ['distance_deg', 'name', 'type_name', 'constellation', 'mag', 'subr', 'ra', 'decl', 'size', 'atlas_page', 'contrast_reserve', 'computed_contrast_reserve', 'best_mag', 'computed_best_mag', 'seen', 'last_seen', 'total_observations', 'your_last_seen_date'];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'distance_deg';
        }

        // Centralize ordering through helper to avoid duplicated logic
        $this->applySortingToQuery($outer, $sortField, $sortDirection);

        // Do not mutate `$this->sortField` here; keep the component's sort
        // property stable (unqualified) so repeated header clicks toggle the
        // direction as PowerGrid expects. Ordering is enforced on the SQL
        // using fully-qualified expressions inside `applySortingToQuery()`.

        return $outer;
    }

    /**
     * Apply the given sort field and pre-validated direction to the outer query.
     *
     * This method centralises all ordering logic for the table's outer query
     * (the wrapper around the inner subquery aliased as `objects`). It:
     *
     * - Clears any existing ORDER clauses on the provided query so a
     *   deterministic, fully-qualified ORDER BY can be applied.
     * - Implements special-case ordering for virtual/derived fields and
     *   application-specific tie-break rules (see list below).
     * - Validates the fallback column name before applying a default ORDER BY
     *   to avoid SQL injection and ambiguous column errors.
     *
     * Supported special cases and behaviour:
     * - `size`: orders by the greatest of `objects.diam1`/`objects.diam2`;
     *   rows with size 0 are treated as missing and ordered after real values.
     * - `name`: performs a natural-like sort by extracting a non-numeric
     *   prefix and a numeric suffix (via `REGEXP_SUBSTR`) so entries like
     *   `NGC1`, `NGC2`, `NGC10` sort in an expected human order.
     * - `best_mag`: prefers lens-specific metrics (`uom_lens`), then default
     *   metrics (`uom_default`/`uom`), then the inner placeholder
     *   `objects.best_mag`. NULL values are pushed after numeric values.
     * - `contrast_reserve`: similar preference ordering as `best_mag` and
     *   treats NULL/empty as missing (ordered last).
     * - `mag` / `subr`: sentinel values (`99.9`) and explicit zero are
     *   considered missing and ordered after real numeric values.
     * - `seen` / `total_observations`: orders by aggregated totals with
     *   missing/zero totals last; ties broken by the current user's own counts
     *   (`your_observations`) so rows where the user has contributed appear
     *   earlier/later depending on direction.
     * - `last_seen` / `your_last_seen_date`: orders NULLs last then by date.
     * - `atlas_page`: when atlas columns are included, empty/NULL atlas pages
     *   are ordered after non-empty values.
     *
     * Fallback behaviour:
     * - For any other field the method will attempt a fully-qualified
     *   `ORDER BY objects.<field> <ASC|DESC>` where `<field>` is validated
     *   with a conservative regex and `SchemaColumns::has('objects', $field)`.
     *
     * Error handling and guarantees:
     * - The method catches and swallows exceptions to avoid breaking the
     *   Livewire render cycle; if ordering fails the query may be returned
     *   without these deterministic ORDER clauses.
     * - All raw SQL fragments are fully qualified (prefix `objects.` or
     *   explicit table aliases) to prevent ambiguous column errors when joins
     *   are present.
     *
     * @param \Illuminate\Database\Eloquent\Builder $outer         Outer query builder (expects inner subquery aliased as `objects`)
     * @param string                                   $sortField     Unqualified field/alias name (pre-processed by caller)
     * @param string                                   $sortDirection Pre-validated direction: 'asc' or 'desc'
     *
     * @return void
     */
    private function applySortingToQuery(\Illuminate\Database\Eloquent\Builder $outer, string $sortField, string $sortDirection): void
    {
        try {
            $outer->getQuery()->orders = null;
            $sf = $sortField;
            $sd = ($sortDirection === 'asc') ? 'ASC' : 'DESC';

            if ($sf === 'size') {
                $sizeExpr = 'GREATEST(COALESCE(objects.diam1,0), COALESCE(objects.diam2,0))';
                if ($sd === 'ASC') {
                    $outer->orderByRaw("({$sizeExpr} = 0) ASC, {$sizeExpr} ASC");
                } else {
                    $outer->orderByRaw("({$sizeExpr} = 0) ASC, {$sizeExpr} DESC");
                }
                return;
            }

            if ($sf === 'name') {
                $prefixExpr = "LOWER(REGEXP_SUBSTR(objects.name, '^[^0-9]+'))";
                $numExprAsc = "COALESCE(CAST(REGEXP_SUBSTR(objects.name, '[0-9]+') AS UNSIGNED), 4294967295)";
                $numExprDesc = "COALESCE(CAST(REGEXP_SUBSTR(objects.name, '[0-9]+') AS SIGNED), -1)";
                if ($sd === 'ASC') {
                    $outer->orderByRaw("{$prefixExpr} ASC, {$numExprAsc} ASC, objects.name ASC");
                } else {
                    $outer->orderByRaw("{$prefixExpr} DESC, {$numExprDesc} DESC, objects.name DESC");
                }
                return;
            }

            if ($sf === 'best_mag' || $sf === 'computed_best_mag') {
                // Check if user has required instrument/location for best_mag sorting
                $authUser = Auth::user();
                $hasUserMetrics = $authUser && $authUser->standardLocation && ($authUser->standardInstrument || $this->previewInstrumentId);

                if ($hasUserMetrics) {
                    // Reference the computed alias we created
                    if ($sd === 'ASC') {
                        $outer->orderByRaw("(computed_best_mag IS NULL) ASC, computed_best_mag ASC");
                    } else {
                        $outer->orderByRaw("(computed_best_mag IS NULL) ASC, computed_best_mag DESC");
                    }
                } else {
                    // No user metrics available, fall back to sorting by distance
                    $outer->orderBy('objects.distance_deg', 'ASC');
                }
                return;
            }

            if ($sf === 'contrast_reserve' || $sf === 'computed_contrast_reserve') {
                // Check if user has required instrument/location for CR sorting
                $authUser = Auth::user();
                $hasUserMetrics = $authUser && $authUser->standardLocation && ($authUser->standardInstrument || $this->previewInstrumentId);

                if ($hasUserMetrics) {
                    // Reference the computed alias we created
                    if ($sd === 'ASC') {
                        $outer->orderByRaw("(computed_contrast_reserve IS NULL) ASC, computed_contrast_reserve ASC");
                    } else {
                        $outer->orderByRaw("(computed_contrast_reserve IS NULL) ASC, computed_contrast_reserve DESC");
                    }
                } else {
                    // No user metrics available, sort by placeholder only (always NULL)
                    // Fall back to sorting by distance instead of showing ambiguous column error
                    $outer->orderBy('objects.distance_deg', 'ASC');
                }
                return;
            }

            if ($sf === 'mag' || $sf === 'subr') {
                $col = "objects.{$sf}";
                if ($sd === 'ASC') {
                    $outer->orderByRaw("({$col} = 0 OR {$col} = 99.9 OR {$col} IS NULL) ASC, {$col} ASC");
                } else {
                    $outer->orderByRaw("({$col} = 0 OR {$col} = 99.9 OR {$col} IS NULL) ASC, {$col} DESC");
                }
                return;
            }

            if ($sf === 'seen' || $sf === 'total_observations') {
                $col = 'objects.total_observations';
                $userCol = 'objects.your_observations';
                if ($sd === 'ASC') {
                    $outer->orderByRaw("(COALESCE({$col},0) = 0) ASC, COALESCE({$col},0) ASC, COALESCE({$userCol},0) ASC");
                } else {
                    $outer->orderByRaw("(COALESCE({$col},0) = 0) ASC, COALESCE({$col},0) DESC, COALESCE({$userCol},0) DESC");
                }
                return;
            }

            if ($sf === 'last_seen' || $sf === 'your_last_seen_date') {
                $col = 'objects.your_last_seen_date';
                if ($sd === 'ASC') {
                    $outer->orderByRaw("({$col} IS NULL) ASC, {$col} ASC");
                } else {
                    $outer->orderByRaw("({$col} IS NULL) ASC, {$col} DESC");
                }
                return;
            }

            if ($sf === 'atlas_page' && $this->includeAtlas) {
                $atlasExpr = 'objects.atlas_page';
                if ($sd === 'ASC') {
                    $outer->orderByRaw("({$atlasExpr} = '' OR {$atlasExpr} IS NULL) ASC, {$atlasExpr} ASC");
                } else {
                    $outer->orderByRaw("({$atlasExpr} = '' OR {$atlasExpr} IS NULL) ASC, {$atlasExpr} DESC");
                }
                return;
            }

            // Normalise sort direction to a safe value.
            $sd = strtoupper($sd) === 'DESC' ? 'DESC' : 'ASC';

            // Default fallback (fully-qualified), with validation to avoid SQL injection.
            if (is_string($sf) && preg_match('/^[A-Za-z0-9_]+$/', $sf) && SchemaColumns::has('objects', $sf)) {
                $outer->orderBy("objects.{$sf}", $sd);
            }
        } catch (\Throwable $_) {
            // keep rendering even if ordering fails
        }

    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name', function ($row) {
                $slug = \Illuminate\Support\Str::slug($row->name ?? '');
                return '<a class="font-medium" href="' . route('object.show', ['slug' => $slug]) . '">' . e($row->name) . '</a>';
            })
            // Plain text name for CSV/XLSX exports (no HTML)
            ->add('name_plain', function ($row) {
                return html_entity_decode((string) ($row->name ?? ''));
            })
            ->add('add_to_list_action', function ($row) {
                if (!Auth::check() || !$this->canModifyActiveList()) {
                    return '';
                }

                $name = trim((string) ($row->name ?? ''));
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
            ->add('type_name', function ($row) {
                $typeName = $row->type_name ?? $row->type ?? '';

                // If type_name looks like a code (all uppercase, <= 6 chars), try to resolve
                // the full name from the DeepskyType model. This handles cases where the
                // production database hasn't been properly seeded with full type names.
                if (!empty($typeName) && strlen($typeName) <= 6 && $typeName === strtoupper($typeName)) {
                    $typeCode = strtoupper($typeName);

                    // Check cache first to avoid repeated DB queries
                    if (!isset($this->cachedTypeNames[$typeCode])) {
                        try {
                            $typeModel = \App\Models\DeepskyType::find($typeCode);
                            if ($typeModel && !empty($typeModel->name) && $typeModel->name !== $typeCode) {
                                // Found a different (non-code) name in the model
                                $this->cachedTypeNames[$typeCode] = $typeModel->name;
                            } else {
                                // Store the code itself to avoid re-querying
                                $this->cachedTypeNames[$typeCode] = $typeName;
                            }
                        } catch (\Throwable $_) {
                            // Fall back to the original value if lookup fails
                            $this->cachedTypeNames[$typeCode] = $typeName;
                        }
                    }

                    $typeName = $this->cachedTypeNames[$typeCode];
                }

                return e($typeName);
            })
            ->add('constellation')
            ->add('mag', function ($row) {
                $mag = $row->mag;
                // Treat sentinel values (99.9) and explicit zero as missing
                if (is_numeric($mag) && (floatval($mag) == 99.9 || floatval($mag) == 0.0)) {
                    return '-';
                }
                return e($mag);
            })
            ->add('subr', function ($row) {
                $sb = $row->subr ?? null;
                // Treat sentinel values (99.9) and explicit zero as missing
                if (is_numeric($sb) && (floatval($sb) == 99.9 || floatval($sb) == 0.0)) {
                    return '-';
                }
                return e($sb);
            })
            ->add('seen', function ($row) {
                try {
                    $totalObs = isset($row->total_observations) ? intval($row->total_observations) : 0;
                    $totalDraw = isset($row->total_drawings) ? intval($row->total_drawings) : 0;
                    $yourObs = isset($row->your_observations) ? intval($row->your_observations) : 0;
                    $yourDraw = isset($row->your_drawings) ? intval($row->your_drawings) : 0;
                    $parts = [];
                    $parts[] = e($totalObs . ' obs');
                    $parts[] = e($totalDraw . ' drw');
                    // show per-user counts in parens when non-zero or when user is present
                    $parts[] = '<span class="text-gray-400">(' . e($yourObs . ' / ' . $yourDraw . ' you') . ')</span>';
                    return implode(' ', $parts);
                } catch (\Throwable $_) {
                    return '';
                }
            })
            // Expose DB-backed fields for server-side sorting. These provide
            // simple raw values that PowerGrid can request as sort keys.
            ->add('total_observations', function ($row) {
                try {
                    return isset($row->total_observations) ? intval($row->total_observations) : 0;
                } catch (\Throwable $_) {
                    return 0;
                }
            })
            // The Seen column: the number of observations, linking to them when there are any.
            ->add('seen_link', function ($row) {
                $total = intval($row->total_observations ?? 0);
                $slug = \Illuminate\Support\Str::slug($row->name ?? '');
                if ($total <= 0 || $slug === '') {
                    return e($total);
                }

                return '<a class="hover:underline" href="' . e(route('observations.object', ['slug' => $slug])) . '">' . e($total) . '</a>';
            })
            ->add('your_last_seen_date', function ($row) {
                try {
                    $d = $row->your_last_seen_date ?? null;
                    if (!$d)
                        return '';
                    $s = (string) $d;
                    try {
                        $c = \Carbon\Carbon::createFromFormat('Ymd', $s);
                        return $c->translatedFormat('j M Y');
                    } catch (\Throwable $_) {
                        return (string) $d;
                    }
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('last_seen', function ($row) {
                try {
                    $fmt = function ($d) {
                        if (!$d)
                            return '';
                        try {
                            // legacy date stored as Ymd integer/string
                            $s = (string) $d;
                            $c = \Carbon\Carbon::createFromFormat('Ymd', $s);
                            return $c->translatedFormat('j M Y');
                        } catch (\Throwable $_) {
                            return '';
                        }
                    };

                    $yourLast = $fmt($row->your_last_seen_date ?? null);
                    $yourLastDraw = $fmt($row->your_last_drawing_date ?? null);

                    $parts = [];
                    $parts[] = $yourLast ? e($yourLast) : e('-');
                    if ($yourLastDraw) {
                        $parts[] = '<span class="text-gray-400">(' . e($yourLastDraw) . ' drw)</span>';
                    }
                    return implode(' ', $parts);
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('computed_best_mag', function ($row) {
                try {
                    $authUser = Auth::user();
                    if (!$authUser) {
                        return '<span title="' . e('Login required to compute best magnification') . '">-</span>';
                    }

                    $userLocation = $authUser?->standardLocation ?? null;
                    // Prefer a preview instrument when available (from Aladin preview);
                    // otherwise fall back to the user's standard instrument.
                    $userInstrument = null;
                    try {
                        if (!empty($this->previewInstrumentId)) {
                            $userInstrument = $this->previewInstrumentModel ?? \App\Models\Instrument::where('id', $this->previewInstrumentId)->first();
                        }
                    } catch (\Throwable $_) {
                        $userInstrument = null;
                    }
                    if ($userInstrument === null) {
                        $userInstrument = $authUser?->standardInstrument ?? null;
                    }

                    if (!$userLocation || !$userInstrument) {
                        return '<span title="' . e('Best mag requires a standard observing location and instrument in your profile') . '">-</span>';
                    }

                    // If a cached best-mag exists from the DB, show it immediately.
                    $cachedBest = $row->optimum_detection_magnification ?? $row->computed_best_mag ?? null;
                    $metricExists = $row->metric_exists ?? null;

                    // If a metric EXISTS but optimum_detection_magnification is NULL, that means
                    // it was previously calculated and determined to be uncalculable.
                    // Don't attempt recalculation - just show "-".
                    if ($metricExists !== null && $cachedBest === null) {
                        return '<span title="' . e('Best magnification unavailable for this object') . '">-</span>';
                    }

                    if (is_numeric($cachedBest)) {
                        return e((int) $cachedBest) . 'x';
                    }

                    // Fall back to the instrument's fixed magnification or the one given by its
                    // typical eyepiece. The object's visual magnitude is not a magnification.
                    $mag = $userInstrument->fixedMagnification ?? null;
                    if (!$mag && isset($row->typicalEyepieceFocal) && !empty($userInstrument->focal_length_mm)) {
                        $mag = (int) round(($userInstrument->focal_length_mm / $row->typicalEyepieceFocal));
                    }

                    if ($mag) {
                        return e((int) $mag) . 'x';
                    }

                    return '<span title="' . e('Insufficient data to compute best magnification') . '">-</span>';
                } catch (\Throwable $_) {
                    return '<span title="' . e('Error computing best magnification') . '">-</span>';
                }
            })
            // Plain best-mag value for exports (avoid HTML/tooltips).
            // Compute a fallback when no DB-backed `best_mag` exists so exports
            // match the on-screen table which may compute a best mag inline.
            ->add('best_mag_plain', function ($row) {
                try {
                    if (isset($row->computed_best_mag) && is_numeric($row->computed_best_mag)) {
                        return (int) $row->computed_best_mag . 'x';
                    }

                    // Attempt to compute a best-mag fallback using the same
                    // logic used for the interactive column. This ensures
                    // exported files include the computed value even when the
                    // per-user metric hasn't been persisted yet.
                    $authUser = Auth::user();
                    if (!$authUser) {
                        return '-';
                    }

                    $userLocation = $authUser?->standardLocation ?? null;
                    $userInstrument = null;
                    try {
                        if (!empty($this->previewInstrumentId)) {
                            $userInstrument = $this->previewInstrumentModel ?? \App\Models\Instrument::where('id', $this->previewInstrumentId)->first();
                        }
                    } catch (\Throwable $_) {
                        $userInstrument = null;
                    }
                    if (!$userInstrument) {
                        $userInstrument = $authUser?->standardInstrument ?? null;
                    }

                    if (!$userLocation || !$userInstrument) {
                        return '-';
                    }

                    $d1 = $row->diam1 ?? null;
                    $d2 = $row->diam2 ?? null;
                    $origMag = $row->mag ?? null;
                    $origSubr = $row->subr ?? null;

                    $target = new AstroTarget();
                    if ($d1 && $d2) {
                        $target->setDiameter($d1, $d2);
                    }

                    $m = (is_numeric($origMag) && floatval($origMag) != 99.9) ? floatval($origMag) : null;
                    if ($m === null) {
                        $d1f = is_numeric($row->diam1) ? floatval($row->diam1) : (is_numeric($d1) ? floatval($d1) : null);
                        $d2f = is_numeric($row->diam2) ? floatval($row->diam2) : (is_numeric($d2) ? floatval($d2) : null);
                        $subr = is_numeric($origSubr) ? floatval($origSubr) : null;
                        if (($d1f !== null && $d1f > 0) && (empty($d2f) || $d2f <= 0)) {
                            $d2f = $d1f;
                        } elseif (($d2f !== null && $d2f > 0) && (empty($d1f) || $d1f <= 0)) {
                            $d1f = $d2f;
                        }

                        if ($subr !== null && $d1f !== null && $d2f !== null && $d1f > 0 && $d2f > 0) {
                            $area = pi() * ($d1f / 2.0) * ($d2f / 2.0);
                            if ($area > 0) {
                                $estimated = $subr - 2.5 * log10($area);
                                $m = $estimated;
                            }
                        }
                    }
                    if ($m !== null) {
                        $target->setMagnitude($m);
                    }

                    $sbobj = null;
                    try {
                        $sbobj = $target->calculateSBObj();
                    } catch (\Throwable $_) {
                        $sbobj = null;
                    }

                    $sqm = null;
                    try {
                        $sqm = method_exists($userLocation, 'getSqm') ? $userLocation->getSqm() : ($userLocation->sqm ?? null);
                    } catch (\Throwable $_) {
                        $sqm = null;
                    }

                    $aperture = $userInstrument->aperture_mm ?? null;

                    // Determine preview lens factor
                    $lensFactor = 1.0;
                    try {
                        if (!empty($this->previewLensId)) {
                            $ln = $this->previewLensModel ?? \App\Models\Lens::where('id', $this->previewLensId)->first();
                            if ($ln && !empty($ln->factor) && is_numeric($ln->factor)) {
                                $lensFactor = floatval($ln->factor);
                            }
                        }
                    } catch (\Throwable $_) { /* ignore */
                    }

                    // Build possible magnifications (and single fallback mag)
                    $mag = $userInstrument->fixedMagnification ?? null;
                    if (!$mag && isset($row->typicalEyepieceFocal) && !empty($userInstrument->focal_length_mm)) {
                        $mag = (int) round(($userInstrument->focal_length_mm / $row->typicalEyepieceFocal) * $lensFactor);
                    }

                    $possibleMags = [];
                    if (!empty($userInstrument?->focal_length_mm)) {
                        $epFocals = [];
                        if (!empty($this->previewEyepieceId)) {
                            try {
                                $ep = $this->previewEyepieceModel ?? \App\Models\Eyepiece::where('id', $this->previewEyepieceId)->first();
                                if ($ep && !empty($ep->focal_length_mm) && $ep->focal_length_mm > 0) {
                                    $epFocals[] = floatval($ep->focal_length_mm);
                                }
                            } catch (\Throwable $_) { /* ignore */
                            }
                        }

                        $usedSetEyepieces = false;
                        try {
                            $instSet = $authUser?->standardInstrumentSet ?? null;
                            if ($instSet && is_object($instSet) && isset($instSet->eyepieces) && isset($userInstrument->id)) {
                                $useSetEyepieces = false;
                                try {
                                    if (isset($instSet->instruments)) {
                                        foreach ($instSet->instruments as $sinst) {
                                            try {
                                                if (isset($sinst->id) && intval($sinst->id) === intval($userInstrument->id)) {
                                                    $useSetEyepieces = true;
                                                    break;
                                                }
                                            } catch (\Throwable $_) {
                                                continue;
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) { /* ignore */
                                }
                                if ($useSetEyepieces) {
                                    foreach ($instSet->eyepieces as $sep) {
                                        try {
                                            if ($sep->active && !empty($sep->focal_length_mm) && $sep->focal_length_mm > 0) {
                                                $epFocals[] = floatval($sep->focal_length_mm);
                                            }
                                        } catch (\Throwable $_) {
                                            continue;
                                        }
                                    }
                                    $usedSetEyepieces = true;
                                }
                            }
                        } catch (\Throwable $_) { /* ignore */
                        }

                        if (!$usedSetEyepieces) {
                            try {
                                $userEps = $this->getCachedEyepieces($authUser);
                                $foundInstrumentSpecific = false;
                                foreach ($userEps as $ep) {
                                    try {
                                        if (empty($ep->focal_length_mm) || !is_numeric($ep->focal_length_mm))
                                            continue;
                                        $usedWith = [];
                                        try {
                                            $usedWith = method_exists($ep, 'get_used_instruments') ? $ep->get_used_instruments() : [];
                                        } catch (\Throwable $_) {
                                            $usedWith = [];
                                        }
                                        if (!empty($usedWith) && in_array(intval($userInstrument->id), array_map('intval', (array) $usedWith))) {
                                            $foundInstrumentSpecific = true;
                                            $epFocals[] = floatval($ep->focal_length_mm);
                                        }
                                    } catch (\Throwable $_) {
                                        continue;
                                    }
                                }
                                if (!$foundInstrumentSpecific) {
                                    foreach ($userEps as $ep) {
                                        try {
                                            if (!empty($ep->focal_length_mm) && is_numeric($ep->focal_length_mm)) {
                                                $epFocals[] = floatval($ep->focal_length_mm);
                                            }
                                        } catch (\Throwable $_) {
                                            continue;
                                        }
                                    }
                                }
                            } catch (\Throwable $_) { /* ignore */
                            }
                        }

                        $epFocals = array_values(array_unique(array_filter($epFocals)));
                        foreach ($epFocals as $ef) {
                            if ($ef > 0) {
                                $possibleMags[] = (int) round(($userInstrument->focal_length_mm / $ef) * $lensFactor);
                            }
                        }
                        $possibleMags = array_values(array_unique(array_filter($possibleMags)));
                    }

                    if (!empty($possibleMags) && isset($target) && isset($sbobj) && isset($sqm) && isset($aperture)) {
                        try {
                            $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possibleMags);
                            if ($best) {
                                return (int) $best . 'x';
                            }
                        } catch (\Throwable $_) {
                            // ignore
                        }
                    }

                    if ($mag) {
                        return (int) $mag . 'x';
                    }
                } catch (\Throwable $_) {
                    // ignore and fallthrough
                }
                return '-';
            })
            ->add('size', function ($row) {
                $d1 = $row->diam1 ?? null;
                $d2 = $row->diam2 ?? null;
                $pa = $row->pa ?? null;

                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;

                if (!$hasD1 && !$hasD2) {
                    return '';
                }

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
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }

                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }

                return e($size);
            })
            ->add('computed_contrast_reserve', function ($row) {
                try {
                    // Ensure contrast reserve cells re-evaluate when the
                    // preview selection changes (see $this->refreshTick).
                    $refreshDependency = intval($this->refreshTick);
                    $authUser = Auth::user();
                    if (!$authUser) {
                        return '<span title="' . e('Login required to compute contrast reserve') . '">-</span>';
                    }
                    $userLocation = $authUser?->standardLocation ?? null;
                    // Prefer preview instrument when available so CR reflects preview
                    $userInstrument = null;
                    try {
                        if (!empty($this->previewInstrumentId)) {
                            $userInstrument = $this->previewInstrumentModel ?? \App\Models\Instrument::where('id', $this->previewInstrumentId)->first();
                        }
                    } catch (\Throwable $_) {
                        $userInstrument = null;
                    }
                    if (!$userInstrument) {
                        $userInstrument = $authUser?->standardInstrument ?? null;
                    }
                    if (!$userLocation || !$userInstrument) {
                        return '<span title="' . e("Contrast reserve requires a standard observing location and instrument in your profile") . '">-</span>';
                    }

                    $cached = $row->computed_contrast_reserve ?? null;
                    $cachedCat = $row->computed_contrast_reserve_category ?? null;
                    $metricExists = $row->metric_exists ?? null;

                    // The datasource joined the metrics of the instrument shown
                    // (the preview instrument, else the standard one). A metric
                    // without a contrast reserve was calculated and has none
                    // (e.g. no magnitude in the object record).
                    if ($metricExists !== null && $cached === null) {
                        return '<span title="' . e('Contrast reserve unavailable for this object') . '">-</span>';
                    }

                    if (is_numeric($cached)) {
                        $display = number_format(round(floatval($cached), 2), 2);
                        // Derive qualitative category from numeric value to avoid stale cached categories
                        $contrastVal = floatval($cached);
                        if ($contrastVal > 1.0) {
                            $crCat = 'very_easy';
                        } elseif ($contrastVal > 0.5) {
                            $crCat = 'easy';
                        } elseif ($contrastVal > 0.35) {
                            $crCat = 'quite_difficult';
                        } elseif ($contrastVal > 0.1) {
                            $crCat = 'difficult';
                        } elseif ($contrastVal > -0.2) {
                            $crCat = 'questionable';
                        } else {
                            $crCat = 'not_visible';
                        }

                        // Defensive fallback to legacy cached category names
                        if (empty($crCat) && !empty($cachedCat)) {
                            $map = [
                                'excellent' => 'very_easy',
                                'good' => 'easy',
                                'marginal' => 'quite_difficult',
                                'poor' => 'not_visible',
                            ];
                            $crCat = $map[$cachedCat] ?? $cachedCat;
                        }

                        $crClass = 'text-white';
                        if ($crCat === 'very_easy') {
                            $crClass = 'text-green-400';
                        } elseif ($crCat === 'easy') {
                            $crClass = 'text-green-600';
                        } elseif ($crCat === 'quite_difficult') {
                            $crClass = 'text-yellow-400';
                        } elseif ($crCat === 'difficult') {
                            $crClass = 'text-orange-400';
                        } elseif ($crCat === 'questionable') {
                            $crClass = 'text-gray-300';
                        } elseif ($crCat === 'not_visible') {
                            $crClass = 'text-gray-600';
                        }

                        $categoryText = $crCat ? __('contrast.reserve.category.' . $crCat) : __('Unknown');
                        $summaryKey = __('contrast.reserve.summary', ['value' => $display, 'category' => $categoryText]);
                        if (str_contains($summaryKey, 'contrast.reserve.category.') || str_contains($summaryKey, 'contrast.reserve.summary')) {
                            $title = e($display);
                        } else {
                            $title = e($summaryKey);
                        }
                        $instrName = $userInstrument->name ?? ($userInstrument->model ?? null);
                        $locName = $userLocation->name ?? ($userLocation->label ?? null);

                        $html = '<div x-data="{open:false,left:0,top:0,openAt(){this.open = !this.open; if(this.open){ this.$nextTick(()=>{ try{ let r=this.$refs.crbtn.getBoundingClientRect(); this.left = Math.max(8, Math.round(r.left)); this.top = Math.max(8, Math.round(r.bottom)); }catch(e){} }) } }}" class="inline-block">'
                            . '<button x-ref="crbtn" @click.prevent="openAt()" @keydown.escape="open = false" type="button" class="focus:outline-none ' . $crClass . ' font-medium">' . e($display) . '</button>'
                            . '<div x-show="open" x-cloak @click.outside="open = false" x-transition :style="`position:fixed; left:${left}px; top:${top}px;`" class="z-50 w-96 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg" data-dsl-no-overlay-hide="true">'
                            . '<div class="text-sm mb-2">' . $title . '</div>'
                            . '<div class="text-xs text-gray-300 mb-1"><strong>' . e(__('Location')) . ':</strong> ' . e($locName ?? __('Unknown')) . '</div>'
                            . '<div class="text-xs text-gray-300"><strong>' . e(__('Instrument')) . ':</strong> ' . e($instrName ?? __('Unknown')) . '</div>'
                            . '</div></div>';

                        return $html;
                    }

                    // Not calculated yet: queued by triggerBulkPrecompute(); the
                    // table polls while the job runs.
                    $magMissing = !is_numeric($row->mag ?? null) || floatval($row->mag) == 99.9;
                    $subrMissing = !is_numeric($row->subr ?? null) || floatval($row->subr) == 99.9;
                    if ($magMissing && $subrMissing) {
                        return '<span title="' . e('Contrast reserve requires either a magnitude or a surface brightness value in the object record') . '">-</span>';
                    }
                    if ($this->hasPendingCalculations) {
                        return '<span title="' . e(__('Computing...')) . '">…</span>';
                    }

                    return '<span title="' . e('Contrast reserve unavailable for this object') . '">-</span>';
                } catch (\Throwable $ex) {
                    // Contrast reserve outer exception
                }
                return '';
            })
            // Plain contrast reserve for exports (numeric or category)
            ->add('contrast_reserve_plain', function ($row) {
                try {
                    if (isset($row->computed_contrast_reserve) && is_numeric($row->computed_contrast_reserve)) {
                        return number_format(round(floatval($row->computed_contrast_reserve), 2), 2);
                    }
                    if (isset($row->computed_contrast_reserve_category) && !empty($row->computed_contrast_reserve_category)) {
                        return (string) $row->contrast_reserve_category;
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
                return '-';
            })
            ->add('atlas_page', function ($row) {
                if (!$this->includeAtlas) {
                    return '';
                }
                $page = $row->atlas_page ?? null;
                if ($page === null || $page === '') {
                    return '';
                }
                return e($page);
            })
            ->add('ra', function ($row) {
                return $this->formatRA($row->ra ?? null);
            })
            ->add('decl', function ($row) {
                return $this->formatDMS($row->decl ?? null, true);
            })
            ->add('distance_deg', function ($row) {
                return $this->formatAngle($row->distance_deg ?? 0.0);
            })
            // Ephemerides fields: compute per-row ephemerides when ephemerisDate is set.
            // Small in-memory cache (per render) avoids repeated heavy computations.
            ->add('rise', function ($row) {
                try {
                    $refreshDependency = intval($this->refreshTick);
                    $e = $this->computeEphemeridesForRow($row);
                    if (!$e)
                        return '';
                    return e($e['rising'] ?? '-');
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('transit', function ($row) {
                try {
                    $refreshDependency = intval($this->refreshTick);
                    $e = $this->computeEphemeridesForRow($row);
                    if (!$e)
                        return '';
                    return e($e['transit'] ?? '-');
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('setting', function ($row) {
                try {
                    $refreshDependency = intval($this->refreshTick);
                    $e = $this->computeEphemeridesForRow($row);
                    if (!$e)
                        return '';
                    return e($e['setting'] ?? '-');
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('best_time', function ($row) {
                try {
                    $refreshDependency = intval($this->refreshTick);
                    $e = $this->computeEphemeridesForRow($row);
                    if (!$e)
                        return '';
                    return e($e['best_time'] ?? '-');
                } catch (\Throwable $_) {
                    return '';
                }
            })
            ->add('max_altitude', function ($row) {
                try {
                    $refreshDependency = intval($this->refreshTick);
                    $e = $this->computeEphemeridesForRow($row);
                    if (!$e)
                        return '';
                    $v = $e['max_altitude'] ?? null;
                    $vn = $e['max_altitude_at_night'] ?? null;
                    if (is_numeric($v)) {
                        $titleParts = [];
                        if (is_numeric($vn)) {
                            $titleParts[] = __('Max at night') . ': ' . number_format($vn, 1) . '°';
                        }
                        if (is_numeric($v)) {
                            if (floatval($v) >= 0) {
                                $titleParts[] = __('Above horizon');
                            } else {
                                $titleParts[] = __('Below horizon');
                            }
                        }
                        $title = empty($titleParts) ? '' : implode(' | ', $titleParts);
                        $display = number_format($v, 1) . '°';
                        if ($title !== '') {
                            return '<span title="' . e($title) . '">' . e($display) . '</span>';
                        }
                        return e($display);
                    }
                    return e($v ?? '-');
                } catch (\Throwable $_) {
                    return '';
                }
            })
            // Plain max-altitude for exports (no HTML)
            ->add('max_altitude_plain', function ($row) {
                try {
                    $v = $row->max_altitude ?? null;
                    if (is_numeric($v)) {
                        return number_format(floatval($v), 1) . '°';
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
                return '-';
            });
    }

    /**
     * Return a cached list of active eyepieces for the given user.
     * We cache the result in-memory for the duration of the component render to
     * avoid repeated DB queries when rendering many table rows.
     *
     * The returned array is a plain array of eyepiece models (max 200 entries).
     *
     * @param \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return array
     */
    private function getCachedEyepieces($user): array
    {
        if ($this->cachedEyepieces !== null) {
            return $this->cachedEyepieces;
        }

        $this->cachedEyepieces = [];
        try {
            if (!$user || !isset($user->id)) {
                return $this->cachedEyepieces;
            }

            // Limit eyepieces to a reasonable number to avoid loading large collections
            // into memory during a render. If users have many eyepieces, they are
            // unlikely to materially affect CR calculations beyond a small subset.
            $eps = \App\Models\Eyepiece::where('user_id', $user->id)
                ->where('active', 1)
                ->orderBy('focal_length_mm', 'asc')
                ->limit(200)
                ->get();

            foreach ($eps as $ep) {
                $this->cachedEyepieces[] = $ep;
            }
        } catch (\Throwable $_) {
            // ignore and return empty
            $this->cachedEyepieces = [];
        }

        return $this->cachedEyepieces;
    }

    /**
     * Compute ephemerides for a single row and store them in the in-memory cache.
     * Returns the computed ephemerides array or null on failure.
     * Keys: rising, transit, setting, best_time, max_altitude
     *
     * @param object|array $row
     * @return array|null
     */
    private function computeEphemeridesForRow($row): ?array
    {
        try {
            $authUser = Auth::user();
            if (!$authUser)
                return null;
            $userLocation = $authUser?->standardLocation ?? null;
            if (!$userLocation)
                return null;

            // If no ephemeris date was forwarded from the Ephemerides aside,
            // fall back to the current date so the table shows values on initial load.
            if (empty($this->ephemerisDate)) {
                $date = \Carbon\Carbon::now();
                $dateForCache = $date->toDateString();
            } else {
                try {
                    $date = \Carbon\Carbon::parse($this->ephemerisDate);
                } catch (\Throwable $_) {
                    $date = \Carbon\Carbon::now();
                }
                $dateForCache = (string) ($this->ephemerisDate);
            }
            $name = $row->name ?? ($row->object_name ?? null);
            $cacheKey = 'ephem:' . ($name ?? '') . ':' . $dateForCache . ':' . ($userLocation->id ?? ($userLocation->latitude ?? ''));
            // If already computed by another closure in this render, return it
            if (isset($this->cachedEphemerides[$cacheKey])) {
                return $this->cachedEphemerides[$cacheKey];
            }

            $raDeg = null;
            $decDeg = null;
            try {
                if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                    $raDeg = \App\Models\DeepskyObject::raToDecimal($row->ra);
                    $decDeg = \App\Models\DeepskyObject::decToDecimal($row->decl);
                }
            } catch (\Throwable $_) {
                $raDeg = null;
                $decDeg = null;
            }
            if ($raDeg === null || $decDeg === null) {
                $raDeg = is_numeric($row->ra) ? (float) $row->ra : null;
                $decDeg = is_numeric($row->decl) ? (float) $row->decl : null;
            }
            if ($raDeg === null || $decDeg === null) {
                $this->cachedEphemerides[$cacheKey] = ['rising' => null, 'transit' => null, 'setting' => null, 'best_time' => null, 'max_altitude' => null];
                return $this->cachedEphemerides[$cacheKey];
            }

            $tz = $userLocation->timezone ?? config('app.timezone');
            $geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
            $target = new AstroTarget();
            $raHours = (is_numeric($raDeg) ? (float) $raDeg / 15.0 : $raDeg);
            $equa = new \deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates($raHours, $decDeg);
            $target->setEquatorialCoordinates($equa);
            $greenwichSiderialTime = \deepskylog\AstronomyLibrary\Time::apparentSiderialTimeGreenwich($date);
            $deltaT = \deepskylog\AstronomyLibrary\Time::deltaT($date);
            $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
            $transit = $target->getTransit();
            $rising = $target->getRising();
            $setting = $target->getSetting();
            $bestTime = $target->getBestTimeToObserve();
            $maxHeight = $target->getMaxHeight();
            $maxHeightAtNight = null;
            try {
                if (method_exists($target, 'getMaxHeightAtNight')) {
                    $maxHeightAtNight = $target->getMaxHeightAtNight();
                }
            } catch (\Throwable $_) {
                $maxHeightAtNight = null;
            }
            // Some astronomy library APIs return wrapper objects for coordinates
            // (eg. objects exposing getCoordinate()). Normalize those to raw numeric
            // values so the table can render them deterministically.
            try {
                if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) {
                    $maybe = $maxHeight->getCoordinate();
                    if (is_numeric($maybe)) {
                        $maxHeight = $maybe;
                    } else {
                        $maxHeight = null;
                    }
                }
            } catch (\Throwable $_) {
                $maxHeight = null;
            }
            try {
                if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) {
                    $maybe = $maxHeightAtNight->getCoordinate();
                    if (is_numeric($maybe)) {
                        $maxHeightAtNight = $maybe;
                    } else {
                        $maxHeightAtNight = null;
                    }
                }
            } catch (\Throwable $_) {
                $maxHeightAtNight = null;
            }
            if ($transit instanceof \DateTimeInterface) {
                $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm');
            }
            if ($rising instanceof \DateTimeInterface) {
                $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
            }
            if ($setting instanceof \DateTimeInterface) {
                $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
            }
            if ($bestTime instanceof \DateTimeInterface) {
                $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
            }
            if (is_numeric($maxHeight)) {
                $maxHeight = round($maxHeight, 1);
            } else {
                // Ensure non-numeric values do not make it into the table as
                // an object/string which would render empty or unpredictable.
                $maxHeight = null;
            }

            $e = ['rising' => $rising, 'transit' => $transit, 'setting' => $setting, 'best_time' => $bestTime, 'max_altitude' => $maxHeight, 'max_altitude_at_night' => $maxHeightAtNight];
            $this->cachedEphemerides[$cacheKey] = $e;
            return $e;
        } catch (\Throwable $_) {
            return null;
        }
    }

    public function columns(): array
    {
        // Compute atlas column title here so the column header is correct
        // even if `columns()` is evaluated before `mount()` in the lifecycle.
        $atlasTitle = __('Atlas');
        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', $authUser->standardAtlasCode)) {
                $requested = (string) $authUser->standardAtlasCode;
                $m = Atlas::where('code', $requested)->first();
                if (!$m) {
                    $m = Atlas::where('code', $requested)->first();
                }
                if ($m?->name) {
                    $atlasTitle = $m->name;
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }

        // Decide whether to show the Contrast Reserve column. This depends on
        // whether the authenticated user has a standard location and instrument configured.
        $showContrast = false;
        try {
            $authUser = Auth::user();
            // Show contrast reserve if we have a valid location and either the
            // user's standard instrument or a preview instrument forwarded
            // from the Aladin preview aside.
            if ($authUser && $authUser?->standardLocation && ($authUser?->standardInstrument || $this->previewInstrumentId)) {
                $showContrast = true;
            }
        } catch (\Throwable $_) {
            $showContrast = false;
        }

        // Build columns in Astrometry-first order:
        // Name, RA, Dec, Distance, Type, Constellation, Mag, SB, (CR), Size, Atlas
        $authUser = null;
        try {
            $authUser = Auth::user();
        } catch (\Throwable $_) {
            $authUser = null;
        }

        $cols = [
            Column::make(__('Name'), 'name')->searchable()->sortable()->bodyAttribute('class', 'font-medium whitespace-normal')->bodyAttribute('style', 'white-space:normal; overflow:visible;')->headerAttribute('class', 'whitespace-normal')->headerAttribute('style', 'white-space:normal;'),
            // Export-only plain name (no HTML)
            Column::make(__('Name'), 'name_plain')->hidden()->visibleInExport(true),
            Column::make(__('RA'), 'ra')->sortable()->bodyAttribute('class', 'whitespace-nowrap')->bodyAttribute('style', 'white-space:nowrap; overflow:visible;')->headerAttribute('class', 'whitespace-nowrap')->headerAttribute('style', 'white-space:nowrap;'),
            Column::make(__('Dec'), 'decl')->sortable()->bodyAttribute('class', 'whitespace-nowrap')->bodyAttribute('style', 'white-space:nowrap; overflow:visible;')->headerAttribute('class', 'whitespace-nowrap')->headerAttribute('style', 'white-space:nowrap;'),
            Column::make(__('Distance'), 'distance_deg')->sortable()->bodyAttribute('class', 'text-right'),
            // These are computed/aliased fields (from subqueries) so they are not real DB columns.
            // Avoid marking them searchable to prevent PowerGrid generating WHERE clauses
            // that reference non-existent columns during count queries.
            Column::make(__('Type'), 'type_name')->sortable()->bodyAttribute('class', 'whitespace-normal')->bodyAttribute('style', 'white-space:normal; overflow:visible;')->headerAttribute('class', 'whitespace-normal')->headerAttribute('style', 'white-space:normal;'),
            Column::make(__('Constellation'), 'constellation')->sortable()->bodyAttribute('class', 'whitespace-normal')->bodyAttribute('style', 'white-space:normal; overflow:visible;')->headerAttribute('class', 'whitespace-normal')->headerAttribute('style', 'white-space:normal;'),
            Column::make(__('Mag'), 'mag')->sortable()->searchable(),
            Column::make(__('SB'), 'subr')->sortable()->searchable(),
            // Use DB-backed field names for sorting. The fields() mapper
            // provides display renderers (see 'seen' and 'last_seen') while
            // these columns use concrete aliases that exist in the SQL.
            Column::make(__('Seen'), 'seen_link', 'total_observations')->sortable()->bodyAttribute('class', 'text-center')->visibleInExport(false),
            // Export-only plain count (no link)
            Column::make(__('Seen'), 'total_observations')->hidden()->visibleInExport(true),
            // The following columns are sensitive to user-specific context (per-user metrics or ephemerides)
            // and are shown only when an authenticated user is present. See below where we append them
            // when $authUser is available.
        ];

        if ($authUser && $this->canModifyActiveList()) {
            $cols[] = Column::make(__('Add'), 'add_to_list_action')->bodyAttribute('class', 'text-center')->headerAttribute('class', 'text-center');
        }

        // If a user is logged in, add per-user and ephemerides columns
        if ($authUser) {
            $cols[] = Column::make(__('Last seen'), 'your_last_seen_date')->sortable()->bodyAttribute('class', 'text-center');
            // Show Best mag and include it in exports (avoid a separate export-only column
            // which could be toggled/persisted and lead to duplicate headers).
            $cols[] = Column::make(__('Best mag'), 'computed_best_mag')
                ->sortable()
                ->bodyAttribute('class', 'text-center')
                ->visibleInExport(true);
            // Ephemerides columns (rise, transit, set, best time, max altitude)
            $cols[] = Column::make(__('Rise'), 'rise')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Transit'), 'transit')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Set'), 'setting')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Best time'), 'best_time')->bodyAttribute('class', 'text-center');
            // Max altitude visible in the table and included in exports. Remove the
            // separate "_plain" export-only column to avoid duplicate headers.
            $cols[] = Column::make(__('Max altitude'), 'max_altitude')->bodyAttribute('class', 'text-right')->visibleInExport(true);
        }

        if ($showContrast) {
            // Make CR sortable when the per-user cached metric is available (joined into the query).
            // Also include it in exports. Avoid adding a separate export-only column
            // (contrast_reserve_plain) which can lead to duplicate headers when
            // column visibility is persisted or toggled client-side.
            $cols[] = Column::make(__('CR'), 'computed_contrast_reserve')
                ->sortable()
                ->bodyAttribute('class', 'text-center')
                ->visibleInExport(true);
        }
        $cols = array_merge($cols, [
            Column::make(__('Size'), 'size')->sortable()->bodyAttribute('class', 'text-center'),
        ]);

        // Only show the Atlas column when the authenticated user has a configured standard atlas.
        // This prevents showing an empty Atlas column to anonymous visitors.
        try {
            $authUser = Auth::user();
            $showAtlasColumn = false;
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', $authUser->standardAtlasCode)) {
                $showAtlasColumn = true;
            }
        } catch (\Throwable $_) {
            $showAtlasColumn = false;
        }

        if ($showAtlasColumn) {
            $cols[] = Column::make($atlasTitle, 'atlas_page')
                ->sortable()
                ->headerAttribute('class', 'atlas-header')
                ->bodyAttribute('class', 'text-center');
        }

        // (Removed) per-row action column: not needed for Nearby objects UI.

        return $cols;
    }

    /**
     * Define per-row action rules so PowerGrid can attach classes to each row.
     * We use the `rule.loop` closure so we can inspect the loop index and
     * alternate classes for odd/even rows. PowerGrid will evaluate these
     * rules server-side and the frontend will apply the resulting classes.
     *
     * @param mixed $row
     * @return array
     */
    public function actionRules(mixed $row): array
    {
        return [
            [
                'forAction' => 'stripe_odd',
                'rule' => [
                    // apply when the loop index is even (0-based), i.e. 1st,3rd,... rows
                    'loop' => function ($loop) {
                        try {
                            return (isset($loop->index) && intval($loop->index) % 2 === 0);
                        } catch (\Throwable $_) {
                            return false;
                        }
                    },
                    'setAttribute' => [
                        'attribute' => 'class',
                        'value' => 'dsl-row-odd',
                    ],
                ],
            ],
            [
                'forAction' => 'stripe_even',
                'rule' => [
                    // apply when the loop index is odd (0-based), i.e. 2nd,4th,... rows
                    'loop' => function ($loop) {
                        try {
                            return (isset($loop->index) && intval($loop->index) % 2 === 1);
                        } catch (\Throwable $_) {
                            return false;
                        }
                    },
                    'setAttribute' => [
                        'attribute' => 'class',
                        'value' => 'dsl-row-even',
                    ],
                ],
            ],
        ];
    }

    // actionsFromView removed: Nearby objects don't render a per-row actions column.

    /**
     * Export only the object names for the current nearby query to a PDF.
     *
     * This action builds the same datasource used by the table, collects the
     * object names and renders them into a simple Blade view which is then
     * converted to PDF using barryvdh/laravel-dompdf (if installed) or
     * Dompdf directly as a fallback.
     *
     * Invoke this method from the client (Livewire action) or wire a button
     * to call it. It will return a streamed PDF download response.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportNamesPdf()
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();
            $names = $rows->pluck('name')->filter()->values();

            $title = __('Nearby object names');

            $html = view('pdf.nearby_names', ['names' => $names, 'title' => $title])->render();

            // Prefer the barryvdh facade when available
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                $filename = 'nearby_names_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            // Fallback to Dompdf if available
            if (class_exists(\Dompdf\Dompdf::class)) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $output = $dompdf->output();
                $filename = 'nearby_names_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($output) {
                    echo $output;
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            // No PDF library installed
            session()->flash('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportNamesPdf failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate names PDF'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Livewire action invoked from PowerGrid export menu to export APD format.
     * If $selected is true, only export rows selected by checkboxes.
     */
    public function exportToApd($selected = false)
    {
        try {
            return $this->exportApd($selected);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportToApd failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate AstroPlanner APD export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Export nearby objects as a plain TXT file suitable for SkyTools 4.
     * Each line contains only the object name.
     * This is exposed in the export dropdown as type 'stxt'.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportStxt()
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            $names = $rows->pluck('name')->filter()->values();

            $content = implode("\n", $names->all()) . "\n";

            $filename = 'nearby_objects_' . date('Ymd_His') . '.txt';
            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportStxt failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate SkyTools TXT export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Livewire action invoked from PowerGrid export menu to export SkyTools TXT format.
     * If $selected is true, only export rows selected by checkboxes.
     */
    public function exportToStxt($selected = false)
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            if ($selected) {
                $selectedKeys = is_array($this->checkboxValues) ? $this->checkboxValues : [];
                if (!empty($selectedKeys)) {
                    $rows = $rows->filter(function ($r) use ($selectedKeys) {
                        // datasource aliases 'objects.name' as 'id' in the inner subquery,
                        // but PowerGrid checkbox values are object ids (here we used name as id),
                        // so match against the 'id' alias if present, otherwise fall back to name.
                        $key = $r->id ?? $r->name ?? null;
                        return in_array($key, $selectedKeys, true);
                    })->values();
                } else {
                    // nothing selected: return early with a flash
                    session()->flash('error', __('No rows selected for export'));
                    return redirect(request()->header('Referer') ?? url()->current());
                }
            }

            $names = $rows->pluck('name')->filter()->values();
            $content = implode("\n", $names->all()) . "\n";
            $filename = 'nearby_objects_' . date('Ymd_His') . '.txt';
            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportToStxt failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate SkyTools TXT export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Export the full nearby objects table to a landscape PDF.
     * Contains: name, RA, Dec, type, constellation, magnitude, SB, Diameter, position angle,
     * atlas page, contrast reserve, best magnification, seen, last seen.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPdf()
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            // Helper to format diameter similar to the size closure used in fields()
            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
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
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                // Append position angle when present and not the sentinel 999
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }

                return $size;
            };

            $data = $rows->map(function ($r) use ($formatDiameter) {
                $d1 = $r->diam1 ?? null;
                $d2 = $r->diam2 ?? null;
                $pa = $r->pa ?? null;
                return [
                    'name' => $r->name ?? '',
                    'ra' => $this->formatRA($r->ra ?? null),
                    'decl' => $this->formatDMS($r->decl ?? null, true),
                    'type' => $r->type_name ?? $r->type ?? '',
                    'constellation' => $r->constellation ?? '',
                    // Present '-' when the catalogue uses the 99.9 sentinel for unknown values
                    // or when the value is explicitly zero (treat 0.0 as missing for export)
                    'mag' => (is_numeric($r->mag) && floatval($r->mag) != 99.9 && floatval($r->mag) != 0.0) ? (string) $r->mag : '-',
                    'sb' => (is_numeric($r->subr) && floatval($r->subr) != 99.9 && floatval($r->subr) != 0.0) ? (string) $r->subr : '-',
                    'diameter' => $formatDiameter($d1, $d2, $pa),
                    // position angle intentionally omitted from export
                    'atlas_page' => $this->includeAtlas ? ($r->atlas_page ?? '-') : '-',
                    'cr' => isset($r->contrast_reserve) && is_numeric($r->contrast_reserve) ? number_format(round(floatval($r->contrast_reserve), 2), 2) : ($r->contrast_reserve_category ?? '-'),
                    // Determine optimum magnification: prefer persisted optimum_detection_magnification,
                    // otherwise fall back to best_mag. If a numeric CR exists, we expect an optimum mag
                    // to be available; show '-' when none found. Always append 'x' to numeric values.
                    // Use a compute helper which mirrors the interactive fallback
                    // so exports include a computed best-mag when no persisted
                    // per-user metric exists yet.
                    'best_mag' => $this->computeBestMagForExport($r),
                    'seen' => isset($r->total_observations) ? (string) $r->total_observations : '-',
                    'last_seen' => $r->your_last_seen_date ?? '-',
                ];
            })->toArray();

            $title = __('DeepskyLog Objects');

            $html = view('pdf.nearby_objects_table', ['rows' => $data, 'title' => $title])->render();

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
                $filename = 'nearby_objects_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            if (class_exists(\Dompdf\Dompdf::class)) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $output = $dompdf->output();
                $filename = 'nearby_objects_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($output) {
                    echo $output;
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            session()->flash('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportPdf failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate nearby objects PDF'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Export nearby objects in Argo Navis plain text format.
     * Each line uses the format described by the user, prefixed with 'DSL '.
     */
    public function exportArgo()
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            // Mapping for types to Argo labels
            $map = [
                'ASTERISM' => 'ASTERISM',
                'BRIGHT' => 'BRIGHT',
            ];

            // Helper: map our object type code/name to one of the allowed Argo types.
            $typeMap = [
                'ASTERISM' => 'ASTERISM',
            ];

            // Prepare a diameter formatter (same logic as exportPdf's helper)
            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
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
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            // Type code -> Argo label mapping (from user-provided list)
            $typeMap = [
                'ASTER' => 'ASTERISM',
                'BRTNB' => 'BRIGHT',
                'CLANB' => 'NEBULA',
                'DRKNB' => 'DARK',
                'EMINB' => 'NEBULA',
                'ENRNN' => 'NEBULA',
                'ENSTR' => 'NEBULA',
                'GALCL' => 'GALAXY CL',
                'GALXY' => 'GALAXY',
                'GLOCL' => 'GLOBULAR',
                'GXADN' => 'NEBULA',
                'GXAGC' => 'GLOBULAR',
                'GACAN' => 'NEBULA',
                'HII' => 'NEBULA',
                'LMCCN' => 'NEBULA',
                'LMCDN' => 'NEBULA',
                'LMCGC' => 'GLOBULAR',
                'LMCOC' => 'OPEN',
                'NONEX' => 'USER',
                'OPNCL' => 'OPEN',
                'PLNNB' => 'PLANETARY',
                'REFNB' => 'NEBULA',
                'RNHII' => 'NEBULA',
                'SMCCN' => 'OPEN',
                'SMCDN' => 'NEBULA',
                'SMCGC' => 'GLOBULAR',
                'SMCOC' => 'OPEN',
                'SNREM' => 'NEBULA',
                'STNEB' => 'NEBULA',
                'QUASR' => 'USER',
                'WRNEB' => 'NEBULA',
                'AA1STAR' => 'STAR',
                'DS' => 'DOUBLE',
                'AA3STAR' => 'TRIPLE',
                'AA4STAR' => 'ASTERISM',
                'AA8STAR' => 'ASTERISM',
            ];

            // Build lines
            $lines = $rows->map(function ($r) use ($formatDiameter, $typeMap) {
                $name = 'DSL ' . ($r->name ?? '');
                $ra = $this->formatRAColon($r->ra ?? null);
                $dec = $this->formatDecColon($r->decl ?? null);

                $rawTypeCode = strtoupper(trim((string) ($r->type ?? '')));
                $type = '';
                if ($rawTypeCode !== '' && isset($typeMap[$rawTypeCode])) {
                    $type = $typeMap[$rawTypeCode];
                } else {
                    // Fallback to human-readable type name
                    $tn = $r->type_name ?? $r->type ?? '';
                    $type = $tn ? strtoupper(trim((string) $tn)) : 'USER';
                }

                $mag = (is_numeric($r->mag) && floatval($r->mag) != 99.9 && floatval($r->mag) != 0.0) ? (string) $r->mag : '';

                $size = $formatDiameter($r->diam1 ?? null, $r->diam2 ?? null, $r->pa ?? null);
                $atlas = $this->includeAtlas ? ($r->atlas_page ?? '') : '';
                $cr = isset($r->contrast_reserve) && is_numeric($r->contrast_reserve) ? number_format(round(floatval($r->contrast_reserve), 2), 2) : ($r->contrast_reserve_category ?? '-');
                $best = $this->computeBestMagForExport($r);

                $last = implode(';', array_filter([$size, $atlas, 'CR ' . ($cr === '' ? '-' : $cr), $best]));

                return implode('|', [$name, $ra, $dec, $type, $mag, $last]);
            })->values()->all();

            $content = implode("\n", $lines) . "\n";

            $filename = 'nearby_argo_' . date('Ymd_His') . '.argo';
            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportArgo failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate Argo Navis export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Export nearby objects as an AstroPlanner .apd (SQLite) file.
     * If $selected is true, only export selected rows.
     */
    public function exportApd($selected = false)
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            if ($selected) {
                $selectedKeys = is_array($this->checkboxValues) ? $this->checkboxValues : [];
                if (!empty($selectedKeys)) {
                    $rows = $rows->filter(function ($r) use ($selectedKeys) {
                        $key = $r->id ?? $r->name ?? null;
                        return in_array($key, $selectedKeys, true);
                    })->values();
                } else {
                    session()->flash('error', __('No rows selected for export'));
                    return redirect(request()->header('Referer') ?? url()->current());
                }
            }

            // Create a temporary sqlite file
            $tmp = tempnam(sys_get_temp_dir(), 'dsl_apd_');
            if ($tmp === false) {
                throw new \RuntimeException('Failed to create temporary file for APD export');
            }

            // Initialize SQLite DB and create minimal tables AstroPlanner expects
            $db = new \PDO('sqlite:' . $tmp);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Create Objects table matching structure found in sample test.apd
            $db->exec("CREATE TABLE Objects (Number integer PRIMARY KEY, ID varchar, Name varchar, Type varchar, RA double, Dec double, Magnitude double, Magnitude2 double, Separation double, PosAngle integer, Size varchar, Catalogue varchar, Notes varchar, Comp varchar, CatNotes varchar, CatIdx integer, SortOrder integer, Period double, Force integer, UserDefined varchar, ObsID varchar, Association integer, AssociationSeqNumber integer, AssociatedData varchar, Origin varchar, Spect varchar, Orbits VarChar);");
            $db->exec("CREATE TABLE Prefs (ID Integer Primary Key Autoincrement, Name text, Value text);");
            $db->exec("CREATE TABLE ID (UID Text);");

            $insert = $db->prepare('INSERT INTO Objects (Number, ID, Name, Type, RA, Dec, Magnitude, Size, Catalogue, Notes) VALUES (:num, :id, :name, :type, :ra, :dec, :mag, :size, :cat, :notes)');

            $i = 1;
            foreach ($rows as $r) {
                $name = $r->name ?? '';
                $id = $r->id ?? $name;
                $type = $r->type_name ?? $r->type ?? '';
                // RA/Dec as degrees in DB (AstroPlanner appears to store degrees)
                $ra = is_numeric($r->ra) ? floatval($r->ra) : null;
                $dec = is_numeric($r->decl) ? floatval($r->decl) : null;
                $mag = (is_numeric($r->mag) && floatval($r->mag) != 99.9) ? floatval($r->mag) : null;
                $size = '';
                try {
                    $size = $this->formatSizeForApd($r);
                } catch (\Throwable $_) {
                    $size = '';
                }
                $catalogue = $this->includeAtlas ? ($r->atlas_page ?? '') : '';
                $notes = '';

                $insert->execute([
                    ':num' => $i,
                    ':id' => $id,
                    ':name' => $name,
                    ':type' => $type,
                    ':ra' => $ra,
                    ':dec' => $dec,
                    ':mag' => $mag,
                    ':size' => $size,
                    ':cat' => $catalogue,
                    ':notes' => $notes,
                ]);
                $i++;
            }

            // Close DB connection to flush file
            $db = null;

            $filename = 'nearby_objects_' . date('Ymd_His') . '.apd';
            return response()->streamDownload(function () use ($tmp) {
                readfile($tmp);
            }, $filename, ['Content-Type' => 'application/x-sqlite3']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportApd failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate AstroPlanner APD export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Helper: format size string for APD size field similar to Objects.Size
     */
    private function formatSizeForApd($r): string
    {
        $d1 = $r->diam1 ?? null;
        $d2 = $r->diam2 ?? null;
        $pa = $r->pa ?? null;
        $hasD1 = is_numeric($d1) && floatval($d1) > 0;
        $hasD2 = is_numeric($d2) && floatval($d2) > 0;
        if (!$hasD1 && !$hasD2)
            return '';
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
            if ($single > 60.0) {
                $single_fmt = $fmt($single / 60.0) . "'";
            } else {
                $single_fmt = $fmt($single) . "''";
            }
            $size = $single_fmt;
        }
        if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
            $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
        }
        return (string) $size;
    }

    /**
     * Export nearby objects in SkySafari .skylist format.
     * The file starts with SkySafariObservingListVersion=3.0 and each object is a new line.
     */
    public function exportSkylist()
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            // Helper: format diameter/extra data (re-use Argo style summary)
            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
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
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            // Small helper to detect planets by name
            $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

            $blocks = $rows->map(function ($r) use ($formatDiameter, $planetNames) {
                $catalog = trim((string) ($r->name ?? ''));
                $common = '';
                try {
                    $common = (string) ($r->proper_name ?? $r->common_name ?? '');
                } catch (\Throwable $_) {
                    $common = '';
                }

                // DateObserved: use ephemerisDate if set, otherwise omit
                $date = $this->ephemerisDate ? $this->ephemerisDate : null;

                $size = $formatDiameter($r->diam1 ?? null, $r->diam2 ?? null, $r->pa ?? null);
                $atlas = $this->includeAtlas ? ($r->atlas_page ?? '') : '';
                $cr = isset($r->contrast_reserve) && is_numeric($r->contrast_reserve) ? number_format(round(floatval($r->contrast_reserve), 2), 2) : ($r->contrast_reserve_category ?? '');
                $best = $this->computeBestMagForExport($r);

                // Determine object type id: planets=1, stars=2, deep-sky=4 (default).
                $lowerName = strtolower($catalog);
                $objectId = '4,-1,-1';
                if (in_array($lowerName, $planetNames, true) || in_array(strtolower($common), $planetNames, true)) {
                    $objectId = '1,-1,-1';
                } else {
                    $rawType = strtoupper(trim((string) ($r->type ?? '')));
                    $typeName = strtoupper(trim((string) ($r->type_name ?? '')));
                    $starCodes = ['DS', 'AA1STAR', 'AA3STAR', 'AA4STAR', 'AA8STAR'];
                    if ($rawType !== '' && (in_array($rawType, $starCodes, true) || str_contains($rawType, 'STAR') || str_contains($typeName, 'STAR'))) {
                        $objectId = '2,-1,-1';
                    }
                }

                $lines = [];
                $lines[] = "SkyObject=BeginObject";
                $lines[] = "   ObjectID={$objectId}";
                if ($catalog !== '') {
                    $lines[] = "   CatalogNumber=" . str_replace("\n", "\\n", $catalog);
                }
                if ($common !== '') {
                    $lines[] = "   CommonName=" . str_replace("\n", "\\n", $common);
                }
                if ($date) {
                    $lines[] = "   DateObserved={$date}";
                }
                // Comments intentionally omitted for SkySafari format
                $lines[] = "EndObject=SkyObject";

                return implode("\n", $lines);
            })->values()->all();

            $content = "SkySafariObservingListVersion=3.0\n" . implode("\n\n", $blocks) . "\n";

            $filename = 'nearby_objects_' . date('Ymd_His') . '.skylist';
            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportSkylist failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate SkySafari export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Livewire action invoked from PowerGrid export menu to export Skylist format.
     * If $selected is true, only export rows selected by checkboxes.
     */
    public function exportToSkylist($selected = false)
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            if ($selected) {
                $selectedKeys = is_array($this->checkboxValues) ? $this->checkboxValues : [];
                if (!empty($selectedKeys)) {
                    $rows = $rows->filter(function ($r) use ($selectedKeys) {
                        $key = $r->id ?? null;
                        return in_array($key, $selectedKeys, true);
                    })->values();
                } else {
                    session()->flash('error', __('No rows selected for export'));
                    return redirect(request()->header('Referer') ?? url()->current());
                }
            }

            // Reuse the same formatting as exportSkylist by temporarily building the content here
            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
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
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

            $lines = $rows->map(function ($r) use ($formatDiameter, $planetNames) {
                $catalog = trim((string) ($r->name ?? ''));
                $common = '';
                try {
                    $common = (string) ($r->proper_name ?? $r->common_name ?? '');
                } catch (\Throwable $_) {
                    $common = '';
                }
                $date = $this->ephemerisDate ? $this->ephemerisDate : date('Y-m-d');
                $size = $formatDiameter($r->diam1 ?? null, $r->diam2 ?? null, $r->pa ?? null);
                $atlas = $this->includeAtlas ? ($r->atlas_page ?? '') : '';
                $cr = isset($r->contrast_reserve) && is_numeric($r->contrast_reserve) ? number_format(round(floatval($r->contrast_reserve), 2), 2) : ($r->contrast_reserve_category ?? '');
                $best = $this->computeBestMagForExport($r);

                $lowerName = strtolower($catalog);
                $objectId = '4,-1,-1';
                if (in_array($lowerName, $planetNames, true) || in_array(strtolower($common), $planetNames, true)) {
                    $objectId = '1,-1,-1';
                } else {
                    $rawType = strtoupper(trim((string) ($r->type ?? '')));
                    $typeName = strtoupper(trim((string) ($r->type_name ?? '')));
                    $starCodes = ['DS', 'AA1STAR', 'AA3STAR', 'AA4STAR', 'AA8STAR'];
                    if ($rawType !== '' && (in_array($rawType, $starCodes, true) || str_contains($rawType, 'STAR') || str_contains($typeName, 'STAR'))) {
                        $objectId = '2,-1,-1';
                    }
                }

                $safe = function ($v) {
                    $s = trim((string) $v);
                    return str_replace(',', ' ', $s);
                };

                $fields = [$safe($catalog), $safe($common), $safe($date), $objectId];
                return implode(', ', array_filter($fields, function ($f) {
                    return $f !== '' || $f === '0';
                }));
            })->values()->all();

            $content = "SkySafariObservingListVersion=3.0\n" . implode("\n", $lines) . "\n";
            $filename = 'nearby_objects_' . date('Ymd_His') . '.skylist';
            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportToSkylist failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate SkySafari export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Livewire action invoked from PowerGrid export menu to export Argo format.
     * If $selected is true, only export rows selected by checkboxes.
     */
    public function exportToArgo($selected = false)
    {
        try {
            $query = $this->datasource();
            $rows = $query ? $query->get() : collect();

            if ($selected) {
                $selectedKeys = is_array($this->checkboxValues) ? $this->checkboxValues : [];
                if (!empty($selectedKeys)) {
                    $rows = $rows->filter(function ($r) use ($selectedKeys) {
                        // datasource aliases 'objects.name' as 'id' so id is the object name
                        $key = $r->id ?? null;
                        return in_array($key, $selectedKeys, true);
                    })->values();
                } else {
                    // nothing selected: return early with a flash
                    session()->flash('error', __('No rows selected for export'));
                    return redirect(request()->header('Referer') ?? url()->current());
                }
            }

            // Reuse exportArgo formatting by temporarily swapping datasource rows
            // Build content similarly to exportArgo
            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
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
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            $typeMap = [
                'ASTER' => 'ASTERISM',
                'BRTNB' => 'BRIGHT',
                'CLANB' => 'NEBULA',
                'DRKNB' => 'DARK',
                'EMINB' => 'NEBULA',
                'ENRNN' => 'NEBULA',
                'ENSTR' => 'NEBULA',
                'GALCL' => 'GALAXY CL',
                'GALXY' => 'GALAXY',
                'GLOCL' => 'GLOBULAR',
                'GXADN' => 'NEBULA',
                'GXAGC' => 'GLOBULAR',
                'GACAN' => 'NEBULA',
                'HII' => 'NEBULA',
                'LMCCN' => 'NEBULA',
                'LMCDN' => 'NEBULA',
                'LMCGC' => 'GLOBULAR',
                'LMCOC' => 'OPEN',
                'NONEX' => 'USER',
                'OPNCL' => 'OPEN',
                'PLNNB' => 'PLANETARY',
                'REFNB' => 'NEBULA',
                'RNHII' => 'NEBULA',
                'SMCCN' => 'OPEN',
                'SMCDN' => 'NEBULA',
                'SMCGC' => 'GLOBULAR',
                'SMCOC' => 'OPEN',
                'SNREM' => 'NEBULA',
                'STNEB' => 'NEBULA',
                'QUASR' => 'USER',
                'WRNEB' => 'NEBULA',
                'AA1STAR' => 'STAR',
                'DS' => 'DOUBLE',
                'AA3STAR' => 'TRIPLE',
                'AA4STAR' => 'ASTERISM',
                'AA8STAR' => 'ASTERISM',
            ];

            $lines = $rows->map(function ($r) use ($formatDiameter, $typeMap) {
                $name = 'DSL ' . ($r->name ?? '');
                $ra = $this->formatRAColon($r->ra ?? null);
                $dec = $this->formatDecColon($r->decl ?? null);

                $rawTypeCode = strtoupper(trim((string) ($r->type ?? '')));
                $type = '';
                if ($rawTypeCode !== '' && isset($typeMap[$rawTypeCode])) {
                    $type = $typeMap[$rawTypeCode];
                } else {
                    $tn = $r->type_name ?? $r->type ?? '';
                    $type = $tn ? strtoupper(trim((string) $tn)) : 'USER';
                }

                $mag = (is_numeric($r->mag) && floatval($r->mag) != 99.9 && floatval($r->mag) != 0.0) ? (string) $r->mag : '';

                $size = $formatDiameter($r->diam1 ?? null, $r->diam2 ?? null, $r->pa ?? null);
                $atlas = $this->includeAtlas ? ($r->atlas_page ?? '') : '';
                $cr = isset($r->contrast_reserve) && is_numeric($r->contrast_reserve) ? number_format(round(floatval($r->contrast_reserve), 2), 2) : ($r->contrast_reserve_category ?? '-');
                $best = $this->computeBestMagForExport($r);

                $last = implode(';', array_filter([$size, $atlas, 'CR ' . ($cr === '' ? '-' : $cr), $best]));

                return implode('|', [$name, $ra, $dec, $type, $mag, $last]);
            })->values()->all();

            $content = implode("\n", $lines) . "\n";
            $filename = 'nearby_objects_' . date('Ymd_His') . '.argo';
            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('NearbyObjectsTable::exportToArgo failed', ['error' => (string) $ex]);
            session()->flash('error', __('Failed to generate Argo Navis export'));
        }

        return redirect(request()->header('Referer') ?? url()->current());
    }

    /**
     * Format RA as HH:MM:SS (hours, minutes, seconds). Accepts hours or degrees; if >24 treat as degrees and convert.
     */
    private function formatRAColon($ra): string
    {
        if ($ra === null || $ra === '')
            return '';
        if (!is_numeric($ra))
            return (string) $ra;
        $v = floatval($ra);
        if ($v > 24.0) {
            $hours = $v / 15.0;
        } else {
            $hours = $v;
        }
        $hours = fmod($hours, 24.0);
        if ($hours < 0)
            $hours += 24.0;
        $h = floor($hours);
        $mFloat = ($hours - $h) * 60.0;
        $m = floor($mFloat);
        $s = round(($mFloat - $m) * 60.0);
        if ($s >= 60) {
            $s -= 60;
            $m += 1;
        }
        if ($m >= 60) {
            $m -= 60;
            $h += 1;
        }
        $h = $h % 24;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /**
     * Format declination as [+|-]DD:MM:SS
     */
    private function formatDecColon($dec): string
    {
        if ($dec === null || $dec === '')
            return '';
        if (!is_numeric($dec))
            return (string) $dec;
        $v = floatval($dec);
        $sign = ($v < 0) ? '-' : '+';
        $abs = abs($v);
        $d = floor($abs);
        $mFloat = ($abs - $d) * 60.0;
        $m = floor($mFloat);
        $s = round(($mFloat - $m) * 60.0);
        if ($s >= 60) {
            $s -= 60;
            $m += 1;
        }
        if ($m >= 60) {
            $m -= 60;
            $d += 1;
        }
        return sprintf('%s%02d:%02d:%02d', $sign, $d, $m, $s);
    }

    /**
     * Compute a best magnification string for exports.
     * Mirrors the logic used in the interactive `best_mag_plain` field so
     * the exported PDF shows a computed value when no persisted metric exists.
     *
     * @param object|array $row
     * @return string
     */
    private function computeBestMagForExport($row): string
    {
        try {
            if (isset($row->best_mag) && is_numeric($row->best_mag)) {
                return (int) $row->best_mag . 'x';
            }

            $authUser = Auth::user();
            if (!$authUser) {
                return '-';
            }

            $userLocation = $authUser?->standardLocation ?? null;
            $userInstrument = null;
            try {
                if (!empty($this->previewInstrumentId)) {
                    $userInstrument = $this->previewInstrumentModel ?? \App\Models\Instrument::where('id', $this->previewInstrumentId)->first();
                }
            } catch (\Throwable $_) {
                $userInstrument = null;
            }
            if (!$userInstrument) {
                $userInstrument = $authUser?->standardInstrument ?? null;
            }

            if (!$userLocation || !$userInstrument) {
                return '-';
            }

            $d1 = $row->diam1 ?? null;
            $d2 = $row->diam2 ?? null;
            $origMag = $row->mag ?? null;
            $origSubr = $row->subr ?? null;

            $target = new AstroTarget();
            if ($d1 && $d2) {
                $target->setDiameter($d1, $d2);
            }

            $m = (is_numeric($origMag) && floatval($origMag) != 99.9) ? floatval($origMag) : null;
            if ($m === null) {
                $d1f = is_numeric($row->diam1) ? floatval($row->diam1) : (is_numeric($d1) ? floatval($d1) : null);
                $d2f = is_numeric($row->diam2) ? floatval($row->diam2) : (is_numeric($d2) ? floatval($d2) : null);
                $subr = is_numeric($origSubr) ? floatval($origSubr) : null;
                if (($d1f !== null && $d1f > 0) && (empty($d2f) || $d2f <= 0)) {
                    $d2f = $d1f;
                } elseif (($d2f !== null && $d2f > 0) && (empty($d1f) || $d1f <= 0)) {
                    $d1f = $d2f;
                }

                if ($subr !== null && $d1f !== null && $d2f !== null && $d1f > 0 && $d2f > 0) {
                    $area = pi() * ($d1f / 2.0) * ($d2f / 2.0);
                    if ($area > 0) {
                        $estimated = $subr - 2.5 * log10($area);
                        $m = $estimated;
                    }
                }
            }
            if ($m !== null) {
                $target->setMagnitude($m);
            }

            $sbobj = null;
            try {
                $sbobj = $target->calculateSBObj();
            } catch (\Throwable $_) {
                $sbobj = null;
            }

            $sqm = null;
            try {
                $sqm = method_exists($userLocation, 'getSqm') ? $userLocation->getSqm() : ($userLocation->sqm ?? null);
            } catch (\Throwable $_) {
                $sqm = null;
            }

            $aperture = $userInstrument->aperture_mm ?? null;

            // Determine preview lens factor
            $lensFactor = 1.0;
            try {
                if (!empty($this->previewLensId)) {
                    $ln = $this->previewLensModel ?? \App\Models\Lens::where('id', $this->previewLensId)->first();
                    if ($ln && !empty($ln->factor) && is_numeric($ln->factor)) {
                        $lensFactor = floatval($ln->factor);
                    }
                }
            } catch (\Throwable $_) { /* ignore */
            }

            $mag = $userInstrument->fixedMagnification ?? null;
            if (!$mag && isset($row->typicalEyepieceFocal) && !empty($userInstrument->focal_length_mm)) {
                $mag = (int) round(($userInstrument->focal_length_mm / $row->typicalEyepieceFocal) * $lensFactor);
            }

            $possibleMags = [];
            if (!empty($userInstrument?->focal_length_mm)) {
                $epFocals = [];
                if (!empty($this->previewEyepieceId)) {
                    try {
                        $ep = $this->previewEyepieceModel ?? \App\Models\Eyepiece::where('id', $this->previewEyepieceId)->first();
                        if ($ep && !empty($ep->focal_length_mm) && $ep->focal_length_mm > 0) {
                            $epFocals[] = floatval($ep->focal_length_mm);
                        }
                    } catch (\Throwable $_) { /* ignore */
                    }
                }

                // instrument set eyepieces
                try {
                    $instSet = $authUser?->standardInstrumentSet ?? null;
                    if ($instSet && is_object($instSet) && isset($instSet->eyepieces) && isset($userInstrument->id)) {
                        $useSetEyepieces = false;
                        try {
                            if (isset($instSet->instruments)) {
                                foreach ($instSet->instruments as $sinst) {
                                    try {
                                        if (isset($sinst->id) && intval($sinst->id) === intval($userInstrument->id)) {
                                            $useSetEyepieces = true;
                                            break;
                                        }
                                    } catch (\Throwable $_) {
                                        continue;
                                    }
                                }
                            }
                        } catch (\Throwable $_) {
                        }
                        if ($useSetEyepieces) {
                            foreach ($instSet->eyepieces as $sep) {
                                try {
                                    if ($sep->active && !empty($sep->focal_length_mm) && $sep->focal_length_mm > 0) {
                                        $epFocals[] = floatval($sep->focal_length_mm);
                                    }
                                } catch (\Throwable $_) {
                                    continue;
                                }
                            }
                        }
                    }
                } catch (\Throwable $_) {
                }

                if (empty($epFocals)) {
                    try {
                        $userEps = $this->getCachedEyepieces($authUser);
                        foreach ($userEps as $ep) {
                            try {
                                if (!empty($ep->focal_length_mm) && is_numeric($ep->focal_length_mm)) {
                                    $epFocals[] = floatval($ep->focal_length_mm);
                                }
                            } catch (\Throwable $_) {
                                continue;
                            }
                        }
                    } catch (\Throwable $_) {
                    }
                }

                $epFocals = array_values(array_unique(array_filter($epFocals)));
                foreach ($epFocals as $ef) {
                    if ($ef > 0) {
                        $possibleMags[] = (int) round(($userInstrument->focal_length_mm / $ef) * $lensFactor);
                    }
                }
                $possibleMags = array_values(array_unique(array_filter($possibleMags)));
            }

            if (!empty($possibleMags) && isset($target) && isset($sbobj) && isset($sqm) && isset($aperture)) {
                try {
                    $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possibleMags);
                    if ($best) {
                        return (int) $best . 'x';
                    }
                } catch (\Throwable $_) {
                }
            }

            if ($mag) {
                return (int) $mag . 'x';
            }

            return '-';
        } catch (\Throwable $_) {
            return '-';
        }
    }



    /**
     * Format Right Ascension for display.
     * If value looks like hours (<= 24) treat as hours, otherwise treat as degrees and convert to hours.
     * Returns string like "HHh MMm SS.s".
     */
    private function formatRA($ra): string
    {
        if ($ra === null || $ra === '') {
            return '';
        }

        if (!is_numeric($ra)) {
            return e((string) $ra);
        }

        $ra = floatval($ra);

        // If RA is stored in degrees (>24) convert to hours; if <=24 assume hours
        if ($ra > 24.0) {
            // degrees -> hours
            $hours = $ra / 15.0;
        } else {
            $hours = $ra;
        }

        $h = floor($hours);
        $m = floor((abs($hours) - abs($h)) * 60.0);
        $s = (abs($hours) - abs($h) - $m / 60.0) * 3600.0;

        return sprintf('%02dh %02dm %04.1fs', $h, $m, $s);
    }

    /**
     * Format declination or generic degrees value as D° M' S" with sign.
     * If $signed is true, include +/− sign for positive/negative declinations.
     */
    private function formatDMS($deg, bool $signed = false): string
    {
        if ($deg === null || $deg === '') {
            return '';
        }

        if (!is_numeric($deg)) {
            return e((string) $deg);
        }

        $deg = floatval($deg);
        $sign = $deg < 0 ? '-' : ($signed ? '+' : '');
        $abs = abs($deg);
        $d = floor($abs);
        $m = floor(($abs - $d) * 60.0);
        $s = ($abs - $d - $m / 60.0) * 3600.0;

        // Round seconds to one decimal for display and normalize overflow
        $s = round($s, 1);
        if ($s >= 60.0) {
            $s = 0.0;
            $m += 1;
        }
        if ($m >= 60) {
            $m = 0;
            $d += 1;
        }

        return sprintf("%s%02d° %02d' %04.1f\"", $sign, $d, $m, $s);
    }

    /**
     * Format a small angular distance in decimal degrees into degrees, arcminutes, arcseconds.
     * Example: 1.23456 -> "1° 14' 4.4\""
     */
    private function formatAngle($deg): string
    {
        if (!is_numeric($deg)) {
            return e((string) $deg);
        }

        $deg = floatval($deg);
        $d = floor($deg);
        $m = floor(($deg - $d) * 60.0);
        $s = ($deg - $d - $m / 60.0) * 3600.0;

        // Round seconds to one decimal and normalize overflow after rounding to avoid 60.0 being displayed
        $s = round($s, 1);
        if ($s >= 60.0) {
            $s = 0.0;
            $m += 1;
        }
        if ($m >= 60) {
            $m = 0;
            $d += 1;
        }

        // If there are 0 degrees, omit the degrees portion (show minutes and seconds)
        if ((int) $d === 0) {
            return sprintf("%02d' %04.1f\"", $m, $s);
        }

        return sprintf("%d° %02d' %04.1f\"", $d, $m, $s);
    }

    /**
     * Update radius when parent emits nearbyRadiusChanged.
     */
    #[On('nearbyRadiusChanged')]
    public function updateRadius(int $arcmin): void
    {
        $this->radiusArcMin = self::clampRadius($arcmin);
        // Persist the changed radius for the authenticated user
        try {
            $this->saveUserTableSettings(['radiusArcMin' => $this->radiusArcMin]);
        } catch (\Throwable $_) {
            // ignore
        }
        // A larger radius brings objects without metrics.
        $this->triggerBulkPrecompute();
        // Re-render: PowerGrid will call datasource on next render
    }

    /**
     * Update ephemeris date when the global Ephemeris aside dispatches a change.
     * This uses Livewire server-side events only (no JavaScript) to force
     * recomputation of per-row ephemerides shown in the table.
     */
    #[On('ephemerisDateChanged')]
    public function handleEphemerisDateChanged($date = null): void
    {
        try {
            $newDate = $date ?: null;
            if ($newDate !== null) {
                $newDate = (string) $newDate;
            }
            if ($newDate === $this->ephemerisDate) {
                return;
            }
            $this->ephemerisDate = $newDate;
            // Clear in-memory cache so computeEphemeridesForRow recalculates
            $this->cachedEphemerides = [];
            try {
                Log::debug('NearbyObjectsTable: ephemerisDate set from ephemerisDateChanged', ['date' => $this->ephemerisDate]);
            } catch (\Throwable $_) {
            }

            // Bump refreshTick so closures reading it will recompute values
            $this->refreshTick = intval($this->refreshTick) + 1;

            // Instruct PowerGrid to refresh this table server-side and client-side
            try {
                $this->dispatch('pg:eventRefresh-' . $this->tableName);
            } catch (\Throwable $_) {
            }
        } catch (\Throwable $_) {
            // swallow to avoid breaking UI
        }
    }

    /**
     * When the Aladin preview recalculates (instrument/eyepiece/lens changed)
     * forward the notification into this component and force a refresh so
     * CR and Best Mag are re-evaluated for nearby rows.
     *
     * The page forwards the browser event into a Livewire event named
     * "aladinPreviewUpdated" (see resources/views/object/show.blade.php).
     */
    #[On('aladinPreviewUpdated')]
    public function onAladinPreviewUpdated($payload = null): void
    {
        try {
            // Clear in-memory eyepiece cache so subsequent calculations pick up
            // the user's current eyepiece set / instrument selection.
            $this->cachedEyepieces = null;

            // Normalize incoming payload and store preview selections so fields()
            // can prefer the preview instrument/eyepiece when computing values.
            try {
                // Normalize payload into an array regardless of whether the
                // caller forwarded a PHP array/object, or a JSON/string payload
                // from the browser forwarder. Be defensive: Livewire can deliver
                // different shapes depending on how the event was emitted.
                $p = null;
                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);
                    if (is_array($decoded)) {
                        $p = $decoded;
                    }
                }
                if ($p === null && (is_array($payload) || is_object($payload))) {
                    $p = (array) $payload;
                }

                if (is_array($p)) {
                    $this->previewInstrumentId = isset($p['instrument']) && $p['instrument'] !== '' ? intval($p['instrument']) : $this->previewInstrumentId;
                    $this->previewEyepieceId = isset($p['eyepiece']) && $p['eyepiece'] !== '' ? intval($p['eyepiece']) : $this->previewEyepieceId;
                    $this->previewLensId = array_key_exists('lens', $p) && $p['lens'] !== '' ? (is_null($p['lens']) ? null : intval($p['lens'])) : $this->previewLensId;

                    // Helper: recursive finder to locate a key anywhere inside nested payloads
                    $finder = function ($needle, $haystack) use (&$finder) {
                        if (!is_array($haystack))
                            return null;
                        if (array_key_exists($needle, $haystack))
                            return $haystack[$needle];
                        foreach ($haystack as $v) {
                            if (is_array($v)) {
                                $res = $finder($needle, $v);
                                if ($res !== null)
                                    return $res;
                            }
                        }
                        return null;
                    };

                    try {
                        $ephem = null;
                        if (isset($p['ephemerides']) && is_array($p['ephemerides'])) {
                            $ephem = $p['ephemerides'];
                        } else {
                            $found = $finder('ephemerides', $p);
                            if (is_array($found))
                                $ephem = $found;
                        }

                        if ($ephem !== null && isset($ephem['date'])) {
                            $newDate = (string) $ephem['date'];
                            if ($newDate !== $this->ephemerisDate) {
                                $this->ephemerisDate = $newDate;
                                $this->cachedEphemerides = [];
                                try {
                                    Log::debug('NearbyObjectsTable: ephemerisDate set from payload', ['date' => $this->ephemerisDate]);
                                } catch (\Throwable $_) {
                                }
                            }
                        } elseif (isset($p['date'])) {
                            $newDate = (string) $p['date'];
                            if ($newDate !== $this->ephemerisDate) {
                                $this->ephemerisDate = $newDate;
                                $this->cachedEphemerides = [];
                                try {
                                    Log::debug('NearbyObjectsTable: ephemerisDate set from payload.date', ['date' => $this->ephemerisDate]);
                                } catch (\Throwable $_) {
                                }
                            }
                        }
                    } catch (\Throwable $_) {
                        // ignore ephemeris parsing errors
                    }
                }
            } catch (\Throwable $_) {
                // ignore payload parsing errors and leave preview ids null
            }
            // Bump a trivial property so Livewire re-renders the component and
            // datasource()/fields() run again. PowerGrid will fetch fresh data.
            $this->refreshTick = intval($this->refreshTick) + 1;
            // Also dispatch PowerGrid's eventRefresh for this table so the
            // front-end PowerGrid handler forces a full refresh (handles lazy
            // loading and virtual scroll cases). Livewire's dispatch() reaches
            // both server-side listeners and the browser.
            try {
                $this->dispatch('pg:eventRefresh-' . $this->tableName);
            } catch (\Throwable $_) {
                // ignore dispatch failures
            }

            // Trigger bulk precompute when preview settings change
            $this->triggerBulkPrecompute();
        } catch (\Throwable $_) {
            // don't let listener failures break the UI
        }
    }

    /**
     * Generic Livewire updated hook to catch property changes like sortField and sortDirection.
     */
    public function updated($name, $value): void
    {
        try {
            // Direct property updates (simple Livewire updates)
            if ($name === 'sortField') {
                // Remap old field names to new computed column names before saving
                if ($value === 'contrast_reserve') {
                    $value = 'computed_contrast_reserve';
                }
                if ($value === 'best_mag') {
                    $value = 'computed_best_mag';
                }
                $this->sortField = $value; // Update the property too
                $this->saveUserTableSettings(['sortField' => $value]);
                return;
            }
            if ($name === 'sortDirection') {
                $this->saveUserTableSettings(['sortDirection' => $value]);
                return;
            }

            // PowerGrid sends nested payloads under keys like "pg:<table-name>"
            // or sometimes as a separate extras key "pg:<table-name>:extras".
            // Catch these shapes and persist the relevant subkeys.
            if (is_string($name) && str_starts_with($name, 'pg:')) {
                $payload = [];
                // Value sometimes arrives as JSON-encoded string or as an array
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $payload = $decoded;
                    }
                } elseif (is_array($value)) {
                    $payload = $value;
                }

                // If this is the extras payload (perPage/radiusArcMin), it's
                // either in $payload['extras'] or the payload itself.
                $maybe = $payload;
                if (isset($payload['extras']) && is_array($payload['extras'])) {
                    $maybe = $payload['extras'];
                }

                $toSave = [];
                if (isset($payload['sortField'])) {
                    $sortField = $payload['sortField'];
                    // Remap old field names to new computed column names
                    if ($sortField === 'contrast_reserve') {
                        $sortField = 'computed_contrast_reserve';
                    }
                    if ($sortField === 'best_mag') {
                        $sortField = 'computed_best_mag';
                    }
                    $toSave['sortField'] = $sortField;
                    $this->sortField = $sortField; // Update the property too
                }
                if (isset($payload['sortDirection'])) {
                    $toSave['sortDirection'] = $payload['sortDirection'];
                }
                if (isset($maybe['perPage'])) {
                    $toSave['perPage'] = intval($maybe['perPage']);
                }
                if (isset($maybe['radiusArcMin'])) {
                    $toSave['radiusArcMin'] = intval($maybe['radiusArcMin']);
                }

                if (!empty($toSave)) {
                    $this->saveUserTableSettings($toSave);
                }
                return;
            }
            // Some browsers/PowerGrid variants send extras under a compound name
            // like "pg:<table-name>:extras" with a plain array payload.
            if (is_string($name) && str_contains($name, ':extras')) {
                $payload = is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?? []) : []);
                $toSave = [];
                if (isset($payload['perPage'])) {
                    $toSave['perPage'] = intval($payload['perPage']);
                }
                if (isset($payload['radiusArcMin'])) {
                    $toSave['radiusArcMin'] = intval($payload['radiusArcMin']);
                }
                if (!empty($toSave)) {
                    $this->saveUserTableSettings($toSave);
                }
                return;
            }
        } catch (\Throwable $_) {
            // ignore persistence failures — do not block UI
        }
    }

    /**
     * Livewire hook: called when the PowerGrid setUp array changes (captures per-page changes)
     */
    public function updatedSetUp($value): void
    {
        try {
            // setUp.footer.perPage is used by PowerGrid; ensure we persist when it's changed
            if (is_array($this->setUp ?? null) && isset($this->setUp['footer']['perPage'])) {
                $per = intval($this->setUp['footer']['perPage']);
                if ($per > 0) {
                    $this->perPage = $per;
                    $this->saveUserTableSettings(['perPage' => $per]);
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }
    }

    /**
     * Persist a set of settings into user_table_settings for the current user and this table.
     * Merges with existing settings.
     *
     * @param array $overrides
     * @return void
     */
    private function saveUserTableSettings(array $overrides): void
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return;
        }

        $row = UserTableSetting::firstOrNew([
            'user_id' => $authUser->id,
            'table_name' => $this->tableName,
        ]);

        $existing = is_array($row->settings) ? $row->settings : (is_string($row->settings) ? json_decode($row->settings, true) ?? [] : []);
        if (isset($overrides['radiusArcMin'])) {
            $overrides['radiusArcMin'] = self::clampRadius($overrides['radiusArcMin']);
        }
        $merged = array_merge($existing ?? [], $overrides);
        $row->settings = $merged;
        $row->save();
    }

    /**
     * Toggle a column's visibility from the client-side toggle UI.
     * This method is invoked via Livewire.dispatchTo from the page script when
     * the PowerGrid toggle-columns menu emits the pg:toggleColumn event.
     *
     * @param string $field
     * @return void
     */
    // Toggle is handled by the PowerGrid Listeners trait which exposes the
    // #[On('pg:toggleColumn-{tableName}')] listener. We intentionally do not
    // override it here so the vendor-provided handler toggles the column and
    // persists state consistently.

    /**
     * Add all objects currently visible in the nearby table to the user's active observing list.
     */
    public function addAllToActiveList(): void
    {
        $user = auth()->user();
        if (!$user) {
            session()->flash('error', __('You must be logged in.'));
            return;
        }

        /** @var \App\Services\ActiveObservingListService $svc */
        $svc = app(\App\Services\ActiveObservingListService::class);
        $activeList = $svc->getActiveList($user);

        if (!$activeList) {
            session()->flash('error', __('No active observing list set. Please set one first.'));
            return;
        }

        if (!$this->canModifyActiveList()) {
            session()->flash('error', __('You cannot modify this list.'));
            return;
        }

        try {
            $names = $this->datasource()?->pluck('name')->filter()->unique()->values()->toArray() ?? [];
        } catch (\Throwable $_) {
            $names = [];
        }

        $added = 0;
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $item = $activeList->items()->firstOrCreate(
                ['object_name' => $name],
                [
                    'source_mode' => 'manual',
                    'added_by_user_id' => $user->id,
                ]
            );
            if ($item->wasRecentlyCreated) {
                $added++;
            }
        }

        session()->flash('success', __(':count object(s) added to :list.', [
            'count' => $added,
            'list' => $activeList->name,
        ]));
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
}
