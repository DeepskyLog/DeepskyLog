<?php

namespace App\Livewire;

use App\Support\SchemaColumns;
use App\Livewire\Concerns\LimitsExportRows;
use App\Models\SearchIndex;
use App\Models\Atlas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use App\Services\ObjectMetricsQueue;
use App\Services\ObjectObservationStatistics;
use App\Services\ObservationSearchQuery;

#[Lazy]
class SearchResultsTable extends PowerGridComponent
{
    use LimitsExportRows;

    public string $tableName = 'search-results-table';
    public string $primaryKey = 'search_index_id';
    // Default sort field: avoid PowerGrid's default 'id' which becomes ambiguous
    // when the query joins multiple tables. Use display_name by default.
    public string $sortField = 'display_name';

    public string $q = '';
    public array $objectRefs = [];
    // An observation search (['q' => ..., 'filters' => [...]]): list the
    // objects of its observations. Matched with a subquery, because the
    // number of objects can be far too large to pass around as $objectRefs.
    #[Locked]
    public ?array $observationSearch = null;
    public ?int $previewInstrumentId = null;
    public ?int $previewLensId = null;
    public ?int $previewEyepieceId = null;

    public bool $hasPendingCalculations = false;
    // The queued metric jobs of this result set (ObjectMetricsQueue).
    #[Locked]
    public ?string $metricsToken = null;

    private ?\App\Models\Instrument $previewInstrumentModel = null;
    private ?\App\Models\Lens $previewLensModel = null;
    private ?\App\Models\Eyepiece $previewEyepieceModel = null;

    // Ephemeris helpers
    public ?string $ephemerisDate = null;
    private array $cachedEphemerides = [];
    public int $refreshTick = 0;

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    // NOTE: recommendBestMagForRow removed — on-load computation/upsert disabled per request.

    public function setUp(): array
    {
        $this->persist(['columns', 'filters', 'sorting']);

        return [
            PowerGrid::header()->showSearchInput()->showToggleColumns(),
            PowerGrid::footer()->showPerPage(25)->showRecordCount(),
            PowerGrid::responsive()->fixedColumns('display_name'),
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV, 'argo', 'skylist', 'stxt', 'apd'),
        ];
    }

    /**
     * Restrict the search_index query to the rows this table lists: the
     * objects of an observation search, an explicit list of object
     * references, or the names matching the search term.
     */
    private function constrainToResults($query, $objectRefs, string $like): void
    {
        $name = 'LOWER(COALESCE(name, display_name))';

        if ($this->observationSearch !== null) {
            $targets = app(ObservationSearchQuery::class)->targets(
                trim((string) ($this->observationSearch['q'] ?? '')),
                (array) ($this->observationSearch['filters'] ?? [])
            );
            $query->whereIn(DB::raw("(source_table, {$name})"), $targets);

            return;
        }

        if ($objectRefs->isNotEmpty()) {
            $query->where(function ($query) use ($objectRefs, $name) {
                foreach ($objectRefs as $ref) {
                    $query->orWhere(function ($inner) use ($ref, $name) {
                        $inner
                            ->where('source_table', $ref['source_table'])
                            ->whereRaw("{$name} = ?", [$ref['name']]);
                    });
                }
            });

            return;
        }

        $query->where('name', 'like', $like);
    }

    /**
     * The object references to list, normalized (lower-case names).
     */
    private function normalizedObjectRefs(): \Illuminate\Support\Collection
    {
        return collect($this->objectRefs)
            ->map(function ($ref): ?array {
                if (!is_array($ref)) {
                    return null;
                }

                $sourceTable = strtolower(trim((string) ($ref['source_table'] ?? '')));
                $name = Str::lower(trim((string) ($ref['name'] ?? '')));

                if ($sourceTable === '' || $name === '') {
                    return null;
                }

                return [
                    'source_table' => $sourceTable,
                    'name' => $name,
                ];
            })
            ->filter()
            ->unique(fn(array $ref): string => $ref['source_table'] . ':' . $ref['name'])
            ->values();
    }

    /**
     * Whether the table lists anything: a search term, object references or
     * an observation search.
     */
    private function hasResults(): bool
    {
        return trim($this->q) !== '' || $this->normalizedObjectRefs()->isNotEmpty() || $this->observationSearch !== null;
    }

    /**
     * The LIKE pattern of the search term; '*' and '%' are wildcards.
     */
    private function likePattern(): string
    {
        $q = trim($this->q);
        // Sigma 1, Struve 1: the Struve double star STF 1
        $q = \App\Services\StruveSigmaNames::designation($q) ?? $q;
        // Accept both '*' and '%' as user wildcards and normalize to '*'
        if (strpos($q, '*') !== false || strpos($q, '%') !== false) {
            $trimQ = str_replace('%', '*', $q);
            if (preg_match('/^\*/', $trimQ)) {
                return '%' . ltrim(str_replace('*', '%', $trimQ), '%');
            }
            if (preg_match('/\*\s*$/', $trimQ)) {
                return preg_replace('/\*+\s*$/', '', $trimQ) . '%';
            }

            return '%' . str_replace('*', '%', $trimQ) . '%';
        }

        return '%' . $q . '%';
    }

    /**
     * The names (objects.name) of the deep-sky objects of the result set, the
     * rows that get a contrast reserve, in one light query.
     *
     * @return list<string>
     */
    private function resultObjectNames(): array
    {
        if (!$this->hasResults()) {
            return [];
        }

        $slugs = DB::table('search_index')->where('source_table', 'objects')->select('source_pk');
        $this->constrainToResults($slugs, $this->normalizedObjectRefs(), $this->likePattern());

        return DB::table('objects')->whereIn('slug', $slugs)->distinct()->pluck('name')->all();
    }

    public function datasource(): ?Builder
    {
        $objectRefs = $this->normalizedObjectRefs();

        if (!$this->hasResults()) {
            // Nothing to show, but the objects join is kept so that the searchable
            // objects.* columns still resolve when a search term is typed.
            return SearchIndex::query()
                ->leftJoin('objects', 'search_index.source_pk', '=', 'objects.slug')
                ->whereRaw('0 = 1');
        }

        // Populate per-render cached preview models to avoid N+1 DB lookups
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

        $like = $this->likePattern();

        // Aggregate search_index rows by a normalized display-name so
        // alternate rows with the same visible name (e.g. multiple
        // lunar_features imports) collapse into a single result. When an
        // `objects` row exists for the name we prefer that source and its
        // coordinates; otherwise fall back to the first matching source_pk.
        $sub = DB::table('search_index')
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

        $this->constrainToResults($sub, $objectRefs, $like);

        $sub->groupBy(DB::raw("LOWER(COALESCE(name, display_name))"));

        $query = SearchIndex::query()->fromSub($sub, 'search_index')
            ->leftJoin('objects', function ($join) {
                $join->on('search_index.source_pk', '=', 'objects.slug')
                    ->where('search_index.source_table', '=', 'objects');
            })
            ->leftJoin('deepskytypes', 'deepskytypes.code', '=', 'objects.type')
            ->leftJoin('constellations', 'constellations.id', '=', 'objects.con');

        $select = 'search_index.*, objects.name as obj_name, objects.type as obj_type, objects.ra as obj_ra, objects.decl as obj_decl, deepskytypes.name as type_name, constellations.name as constellation, objects.mag, objects.subr, objects.diam1, objects.diam2, objects.pa';
        // Include the user's preferred atlas column (mapped to alias `atlas_page`) when available.
        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode) && preg_match('/^[A-Za-z0-9_]+$/', (string) $authUser->standardAtlasCode)) {
                $acol = (string) $authUser->standardAtlasCode;
                if (SchemaColumns::has('objects', $acol)) {
                    $select .= ', objects.`' . $acol . '` as atlas_page';
                }
            }
        } catch (\Throwable $_) {
            // ignore failures checking user/profile
        }

        // Observation statistics (total_observations, your_last_seen_date, ...)
        try {
            ObjectObservationStatistics::join($query, Auth::user()?->username);
            $select .= ', ' . ObjectObservationStatistics::selectList();
        } catch (\Throwable $_) {
            // ignore statistics failures
        }

        // Attach per-user cached metrics (contrast reserve / best mag) when possible
        // Strict policy: only use the user's stdtelescope and its lens. If absent, do not join metrics.
        try {
            $authUser = Auth::user();
            if ($authUser && $authUser->standardLocation) {
                $locId = $authUser->standardLocation->id ?? null;
                $instrId = $authUser->stdtelescope ?? null; // use stdtelescope only
                $stdLens = $authUser->standardLensId(); // the lens of the standard telescope

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
            // ignore per-user metric join failures
        }

        // Final builder used for the table - do not force an ORDER BY here.
        // Let the PowerGrid sorting pipeline apply ordering so user clicks
        // change the primary ORDER BY as expected.
        $finalBuilder = $query->selectRaw($select);

        return $finalBuilder;
    }

    /**
     * Queue the contrast reserves missing for the result set, as one job
     * (per thousand objects) instead of computing them in the request.
     */
    public function mount(): void
    {
        if (method_exists(get_parent_class($this), 'mount')) {
            parent::mount();
        }

        try {
            $authUser = Auth::user();
            $instrId = $this->previewInstrumentId ?? ($authUser?->stdtelescope ?: null);
            $locId = $authUser?->standardLocation?->id;
            if ($authUser && $instrId && $locId) {
                $this->metricsToken = ObjectMetricsQueue::queueMissing(
                    $authUser->id,
                    (int) $instrId,
                    (int) $locId,
                    $this->previewLensId,
                    $this->resultObjectNames()
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $this->updateHasPendingCalculations();
    }

    /**
     * Called before each render to update pending state.
     */
    public function rendering(): void
    {
        $this->updateHasPendingCalculations();
    }

    /**
     * Update `hasPendingCalculations` (from the cache, without querying) and
     * dispatch a browser event so JS can poll.
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
     * The observer's standard instrument, which the metrics are joined for.
     */
    private function standardInstrument(): ?\App\Models\Instrument
    {
        $authUser = Auth::user();

        return $authUser?->stdtelescope ? $authUser->standardInstrument : null;
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
                // If this row represents an alternative/alias name and the
                // canonical object name differs, show the canonical name in
                // parentheses for clarity (e.g. "Alt Name (NGC 123)").
                try {
                    if (!empty($canonical) && mb_strtolower(trim($canonical)) !== mb_strtolower(trim($display))) {
                        $html .= ' <span class="text-gray-400">(' . e($canonical) . ')</span>';
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
                $html .= '</a>';
                return $html;
            })
            // Plain name for exports
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
                // Prefer joined type name, fall back to source_type
                return $row->type_name ?? $row->source_type ?? '';
            })
            ->add('constellation')
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
            })
            // Plain contrast reserve for exports (numeric or category)
            ->add('contrast_reserve_plain', function ($row) {
                try {
                    if (isset($row->computed_contrast_reserve) && is_numeric($row->computed_contrast_reserve)) {
                        return number_format(round(floatval($row->computed_contrast_reserve), 2), 2);
                    }
                    if (isset($row->computed_contrast_reserve_category) && !empty($row->computed_contrast_reserve_category)) {
                        return (string) $row->computed_contrast_reserve_category;
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
                return '-';
            })
            ->add('computed_contrast_reserve', function ($row) {
                try {
                    $refreshDependency = intval($this->refreshTick);
                    $authUser = Auth::user();
                    if (!$authUser) {
                        return '<span title="' . e('Login required to compute contrast reserve') . '">-</span>';
                    }
                    $userLocation = $authUser?->standardLocation ?? null;
                    // Strictly use stdtelescope only
                    $userInstrument = $this->standardInstrument();
                    if (!$userLocation || !$userInstrument) {
                        return '<span title="' . e("Contrast reserve requires a standard observing location and instrument in your profile") . '">-</span>';
                    }

                    $cached = $row->computed_contrast_reserve ?? null;
                    $cachedCat = $row->computed_contrast_reserve_category ?? null;
                    $metricExists = $row->metric_exists ?? null;

                    if ($metricExists !== null && $cached === null && ($row->computed_contrast_reserve_category ?? null) === 'not_visible') {
                        // the object never rises at the location (ContrastReserveCalculator)
                        return '<span class="text-gray-600" title="' . e(__('The object never rises above the horizon at this location.')) . '">' . e(__('contrast.reserve.category.not_visible')) . '</span>';
                    }
                    if ($metricExists !== null && $cached === null) {
                        return '<span title="' . e('Contrast reserve unavailable for this object') . '">-</span>';
                    }

                    if (is_numeric($cached)) {
                        $display = number_format(round(floatval($cached), 2), 2);
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

                    return '<span title="' . e('Contrast reserve unavailable for this object') . '">-</span>';
                } catch (\Throwable $_) {
                    return '<span title="' . e('Error computing contrast reserve') . '">-</span>';
                }
            })
            ->add('computed_best_mag', function ($row) {
                try {
                    $authUser = Auth::user();
                    if (!$authUser) {
                        return '<span title="' . e('Login required to compute best magnification') . '">-</span>';
                    }

                    $userLocation = $authUser?->standardLocation ?? null;
                    // Strictly use stdtelescope only
                    $userInstrument = $this->standardInstrument();

                    if (!$userLocation || !$userInstrument) {
                        return '<span title="' . e('Best mag requires a standard observing location and instrument in your profile') . '">-</span>';
                    }

                    $cachedBest = $row->optimum_detection_magnification ?? $row->computed_best_mag ?? null;
                    $metricExists = $row->metric_exists ?? null;

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
            })
            ->add('atlas_page');
    }

    public function columns(): array
    {
        // Compute atlas column title so header is correct even if columns() runs early
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

        // Decide whether to show CR (best-effort; may be empty without per-user join)
        $showContrast = false;
        try {
            $authUser = Auth::user();
            if ($authUser && $authUser?->standardLocation && ($authUser?->standardInstrument || false)) {
                $showContrast = true;
            }
        } catch (\Throwable $_) {
            $showContrast = false;
        }

        $authUser = null;
        try {
            $authUser = Auth::user();
        } catch (\Throwable $_) {
            $authUser = null;
        }

        // Build columns in same order as NearbyObjectsTable (omit distance)
        $cols = [
            Column::make(__('Name'), 'name_link', 'display_name')
                ->searchable()
                ->sortUsing(function ($query, $direction) {
                    // Natural sort: group by non-numeric prefix, then numeric suffix.
                    // Use REGEXP_REPLACE to remove digits for prefix and to extract digits for numeric ordering.
                    // Fallback: empties become 0 so non-numeric names sort by prefix then full name.
                    $prefix = "LOWER(REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[0-9]', ''))";
                    $num = "(CASE WHEN REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[^0-9]', '') = '' THEN 0 ELSE CAST(REGEXP_REPLACE(COALESCE(search_index.display_name, ''), '[^0-9]', '') AS UNSIGNED) END)";
                    $query->orderByRaw("{$prefix} {$direction}, {$num} {$direction}, search_index.display_name {$direction}");
                })
                ->bodyAttribute('class', 'font-medium whitespace-normal')
                ->bodyAttribute('style', 'white-space:normal; overflow:visible;')
                ->headerAttribute('class', 'whitespace-normal')
                ->headerAttribute('style', 'white-space:normal;'),
            Column::make(__('Name'), 'name_plain')->hidden()->visibleInExport(true),
            Column::make(__('RA'), 'ra')
                ->sortUsing(function ($query, $direction) {
                    // Normalize RA values to hours for sorting. Some rows store
                    // RA in degrees (>24) while others use hours (0..24). Convert
                    // degrees to hours by dividing by 15 so the order is consistent.
                    $query->orderByRaw("(CASE WHEN COALESCE(objects.ra, search_index.ra) > 24 THEN COALESCE(objects.ra, search_index.ra) / 15.0 ELSE COALESCE(objects.ra, search_index.ra) END) $direction");
                })
                ->bodyAttribute('class', 'whitespace-nowrap')
                ->bodyAttribute('style', 'white-space:nowrap; overflow:visible;')
                ->headerAttribute('class', 'whitespace-nowrap')
                ->headerAttribute('style', 'white-space:nowrap;'),
            Column::make(__('Dec'), 'decl')
                ->sortUsing(function ($query, $direction) {
                    $query->orderByRaw("COALESCE(objects.decl, search_index.decl) $direction");
                })
                ->bodyAttribute('class', 'whitespace-nowrap')
                ->bodyAttribute('style', 'white-space:nowrap; overflow:visible;')
                ->headerAttribute('class', 'whitespace-nowrap')
                ->headerAttribute('style', 'white-space:nowrap;'),
            Column::make(__('Type'), 'type_name')
                ->sortUsing(function ($query, $direction) {
                    $query->orderByRaw("COALESCE(deepskytypes.name, search_index.source_type) $direction");
                })
                ->bodyAttribute('class', 'whitespace-normal')
                ->bodyAttribute('style', 'white-space:normal; overflow:visible;')
                ->headerAttribute('class', 'whitespace-normal')
                ->headerAttribute('style', 'white-space:normal;'),
            Column::make(__('Constellation'), 'constellation')
                ->sortUsing(function ($query, $direction) {
                    $query->orderBy('constellations.name', $direction);
                })
                ->bodyAttribute('class', 'whitespace-normal')
                ->bodyAttribute('style', 'white-space:normal; overflow:visible;')
                ->headerAttribute('class', 'whitespace-normal')
                ->headerAttribute('style', 'white-space:normal;'),
            Column::make(__('Mag'), 'mag', 'objects.mag')
                ->sortUsing(function ($query, $direction) {
                    $query->orderBy('objects.mag', $direction);
                })
                ->searchable(),
            Column::make(__('SB'), 'subr', 'objects.subr')
                ->sortUsing(function ($query, $direction) {
                    $query->orderBy('objects.subr', $direction);
                })
                ->searchable(),
            Column::make(__('Seen'), 'total_observations')->sortable()->bodyAttribute('class', 'text-center'),
        ];

        if ($authUser) {
            $cols[] = Column::make(__('Last seen'), 'your_last_seen_date')->sortable()->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Best mag'), 'computed_best_mag')->sortable()->bodyAttribute('class', 'text-center')->visibleInExport(true);
            $cols[] = Column::make(__('Rise'), 'rise')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Transit'), 'transit')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Set'), 'setting')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Best time'), 'best_time')->bodyAttribute('class', 'text-center');
            $cols[] = Column::make(__('Max altitude'), 'max_altitude')->bodyAttribute('class', 'text-right')->visibleInExport(true);
        }

        if ($showContrast) {
            $cols[] = Column::make(__('CR'), 'computed_contrast_reserve')->sortable()->bodyAttribute('class', 'text-center')->visibleInExport(true);
        }

        $cols = array_merge($cols, [
            Column::make(__('Size'), 'size')->sortable()->bodyAttribute('class', 'text-center'),
        ]);

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
            $cols[] = Column::make($atlasTitle, 'atlas_page')->sortable()->headerAttribute('class', 'atlas-header')->bodyAttribute('class', 'text-center');
        }

        return $cols;
    }

    public function actionRules(mixed $row): array
    {
        return [
            [
                'forAction' => 'stripe_odd',
                'rule' => [
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

    /**
     * Compute per-row ephemerides using the AstronomyLibrary. Results are cached
     * in-memory for the duration of the render to avoid repeated heavy work.
     *
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

            // Prefer an explicit ephemeris date from the component state. If
            // not present (initial server render), fall back to the canonical
            // aside date stored in session so server-side renders match the
            // aside and object pages.
            $useDateString = null;
            if (!empty($this->ephemerisDate)) {
                $useDateString = (string) $this->ephemerisDate;
            } else {
                $useDateString = \App\Support\EphemerisDate::chosen(\App\Support\EphemerisDate::timezone($userLocation));
            }

            if (empty($useDateString)) {
                $date = \Carbon\Carbon::now();
                $dateForCache = $date->toDateString();
            } else {
                try {
                    $date = \Carbon\Carbon::parse($useDateString);
                } catch (\Throwable $_) {
                    $date = \Carbon\Carbon::now();
                }
                $dateForCache = (string) $useDateString;
            }

            // Localize the date to the user's timezone before computing GST
            try {
                $date = $date->timezone($userLocation->timezone ?? config('app.timezone'));
            } catch (\Throwable $_) {
                // ignore timezone conversion failures
            }

            // Prefer canonical object name for caching so aliases share the same cache
            $name = $row->obj_name ?? $row->name ?? ($row->object_name ?? null);
            $cacheKey = 'ephem:' . ($name ?? '') . ':' . $dateForCache . ':' . ($userLocation->id ?? ($userLocation->latitude ?? ''));
            if (isset($this->cachedEphemerides[$cacheKey])) {
                return $this->cachedEphemerides[$cacheKey];
            }

            // Prefer explicit object coordinates (obj_ra/obj_decl) when available
            $raDeg = null;
            $decDeg = null;
            try {
                $raField = $row->obj_ra ?? $row->ra ?? null;
                $decField = $row->obj_decl ?? $row->decl ?? null;
                if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                    $raDeg = \App\Models\DeepskyObject::raToDecimal($raField);
                    $decDeg = \App\Models\DeepskyObject::decToDecimal($decField);
                }
            } catch (\Throwable $_) {
                $raDeg = null;
                $decDeg = null;
            }
            if ($raDeg === null || $decDeg === null) {
                $raDeg = is_numeric($row->obj_ra ?? $row->ra) ? (float) ($row->obj_ra ?? $row->ra) : null;
                $decDeg = is_numeric($row->obj_decl ?? $row->decl) ? (float) ($row->obj_decl ?? $row->decl) : null;
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
                $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->format('H:i');
            }
            if ($rising instanceof \DateTimeInterface) {
                $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->format('H:i');
            }
            if ($setting instanceof \DateTimeInterface) {
                $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->format('H:i');
            }
            if ($bestTime instanceof \DateTimeInterface) {
                $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->format('H:i');
            }
            if (is_numeric($maxHeight)) {
                $maxHeight = round($maxHeight, 1);
            } else {
                $maxHeight = null;
            }

            $e = ['rising' => $rising, 'transit' => $transit, 'setting' => $setting, 'best_time' => $bestTime, 'max_altitude' => $maxHeight, 'max_altitude_at_night' => $maxHeightAtNight];
            $this->cachedEphemerides[$cacheKey] = $e;
            return $e;
        } catch (\Throwable $_) {
            return null;
        }
    }

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
            // Removed debug logging for ephemerisDate changes

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

}
