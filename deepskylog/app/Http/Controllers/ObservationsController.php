<?php

namespace App\Http\Controllers;

use App\Helpers\ListingPagination;
use App\Models\Observation;
use App\Models\User;
use App\Models\Target;
use App\Models\Location;
use App\Models\Instrument;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Lens;
use App\Models\Constellation;
use App\Models\SketchOfTheWeek;
use App\Models\SketchOfTheMonth;
use App\Models\ObservationLike;
use App\Models\ObservationComment;
use App\Services\ObservationSearchQuery;
use App\Services\Observations\ObservationCardData;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ObservationsController extends Controller
{
    /**
     * Show a single observation detail page for deepsky or comet observations.
     * URL: /observation/{observer}/{date}/{object}/{index?}
     */
    public function detail(string $observer, string $date, string $object, ?int $index = null)
    {
        $wantedIndex = max(1, (int) ($index ?? 1));
        $observerUser = User::where('slug', $observer)->orWhere('username', $observer)->firstOrFail();

        try {
            \Log::debug('ObservationsController::detail: entry', [
                'observer_param' => $observer,
                'date_param' => $date,
                'object_param' => $object,
                'resolved_username' => $observerUser?->username,
                'resolved_slug' => $observerUser?->slug,
                'resolved_id' => $observerUser?->id,
            ]);
        } catch (\Throwable $_) {
        }

        // Resolve candidates from the unified observations table first.
        $unifiedMatches = Observation::where('observer_username', $observerUser->username)
            ->where('observed_on', $date)
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get()
            ->filter(fn($candidate) => $this->slugMatches($object, (string) ($candidate->target_name ?? '')))
            ->values();

        try {
            \Log::debug('ObservationsController::detail: unifiedMatches', [
                'count' => $unifiedMatches->count(),
                'ids' => $unifiedMatches->pluck('id')->values()->all(),
                'target_names' => $unifiedMatches->pluck('target_name')->values()->all(),
            ]);
        } catch (\Throwable $_) {
        }

        if ($unifiedMatches->isEmpty()) {
            abort(404);
        }

        $selectedUnified = $unifiedMatches->get($wantedIndex - 1);
        if (!$selectedUnified) {
            abort(404);
        }

        $canonicalUrl = $this->buildObservationDetailUrl(
            $observerUser->slug ?: $observerUser->username,
            $this->normalizeObservedOnDate($selectedUnified->observed_on, $date),
            Str::slug((string) ($selectedUnified->target_name ?? ''), '-'),
            $wantedIndex
        );

        if (request()->url() !== $canonicalUrl) {
            return redirect($canonicalUrl);
        }

        $sketchOfTheWeek = SketchOfTheWeek::where('observation_id', $selectedUnified->id)->first();
        $sketchOfTheMonth = SketchOfTheMonth::where('observation_id', $selectedUnified->id)->first();

        $obsType = $selectedUnified->target_type === Observation::TYPE_COMET ? 'comet' : 'deepsky';

        $selected = $selectedUnified;

        // Load modern deepsky detail row (if present) and attach expected legacy-shaped properties
        try {
            $details = \Illuminate\Support\Facades\DB::table('deepsky_observation_details')->where('observation_id', $selectedUnified->id)->first();
            if ($details) {
                $selected->visibility = $details->visibility ?? null;
                $selected->smallDiameter = $details->small_diameter ?? null;
                $selected->largeDiameter = $details->large_diameter ?? null;
                $selected->small_diameter = $details->small_diameter ?? null;
                $selected->large_diameter = $details->large_diameter ?? null;

                $selected->clusterType = $details->cluster_type ?? null;
                $selected->cluster_type = $details->cluster_type ?? null;

                $selected->stellar = isset($details->stellar) ? (int) $details->stellar : 0;
                $selected->extended = isset($details->extended) ? (int) $details->extended : 0;
                $selected->resolved = isset($details->resolved) ? (int) $details->resolved : 0;
                $selected->mottled = isset($details->mottled) ? (int) $details->mottled : 0;
                $selected->unusualShape = $details->unusual_shape ?? null;
                $selected->unusual_shape = $details->unusual_shape ?? null;
                $selected->partlyUnresolved = $details->partly_unresolved ?? null;
                $selected->partly_unresolved = $details->partly_unresolved ?? null;
                $selected->colorContrasts = $details->color_contrasts ?? null;
                $selected->color_contrasts = $details->color_contrasts ?? null;
                $selected->equalBrightness = $details->equal_brightness ?? null;
                $selected->equal_brightness = $details->equal_brightness ?? null;
                $selected->niceField = $details->nice_field ?? null;
                $selected->nice_field = $details->nice_field ?? null;

                $selected->component1 = $details->component1 ?? null;
                $selected->component2 = $details->component2 ?? null;
            }
        } catch (\Throwable $_) {
            // ignore
        }

        $socialData = $this->buildSocialData((int) $selectedUnified->id);
        if ($obsType === 'comet') {
            // For unified observations that target a comet, ensure we have
            // a resolved comet name and preload comet-specific data so
            // views can display the comet name correctly.
            $selected->resolved_comet_name = (string) ($selectedUnified->target_name ?? '');
            $preloadedData = $this->preloadCometData(collect([$selected]));
        } else {
            $preloadedData = $this->preloadDeepskyData(collect([$selected]));
        }
        $moonContext = $this->buildMoonContext($selected, $preloadedData['preloaded_locations']->get($selected->locationid) ?? null);

        $lens = null;
        if (!empty($selected->lensid) && $selected->lensid > 0) {
            $lens = ($preloadedData['preloaded_lenses'] ?? collect())->get($selected->lensid) ?? Lens::find($selected->lensid);
        }

        return view('observations.detail', array_merge([
            'observationType' => $obsType,
            'observation' => $selected,
            'detail_url' => $canonicalUrl,
            'lens' => $lens,
            'sketchOfTheWeek' => $sketchOfTheWeek,
            'sketchOfTheMonth' => $sketchOfTheMonth,
            // Unified observation id from the unified `observations` table
            'unified_observation_id' => $selectedUnified->id,
            'drawing_filename' => $selectedUnified->drawing_filename,
            'moonContext' => $moonContext,
        ], $preloadedData, $socialData));
    }

    /**
     * Compare the current observation with the logged in user's latest
     * observation of the same object.
     */
    public function compareWithMyLast(string $observer, string $date, string $object, ?int $index = null)
    {
        $viewer = auth()->user();
        if (!$viewer) {
            abort(403);
        }

        $wantedIndex = max(1, (int) ($index ?? 1));
        $observerUser = User::where('slug', $observer)->orWhere('username', $observer)->firstOrFail();

        $unifiedMatches = Observation::where('observer_username', $observerUser->username)
            ->where('observed_on', $date)
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get()
            ->filter(fn($candidate) => $this->slugMatches($object, (string) ($candidate->target_name ?? '')))
            ->values();

        if ($unifiedMatches->isEmpty()) {
            abort(404);
        }

        $selectedUnified = $unifiedMatches->get($wantedIndex - 1);
        if (!$selectedUnified) {
            abort(404);
        }

        $canonicalCompareUrl = $this->buildObservationCompareUrl(
            $observerUser->slug ?: $observerUser->username,
            $this->normalizeObservedOnDate($selectedUnified->observed_on, $date),
            Str::slug((string) ($selectedUnified->target_name ?? ''), '-'),
            $wantedIndex
        );

        if (request()->url() !== $canonicalCompareUrl) {
            return redirect($canonicalCompareUrl);
        }

        $currentObservation = $selectedUnified;
        $isComet = $currentObservation->target_type === Observation::TYPE_COMET;

        $myLastObservation = Observation::where('observer_username', $viewer->username)
            ->where('target_type', $currentObservation->target_type)
            ->where('target_key', $currentObservation->target_key)
            ->where('id', '!=', $currentObservation->id)
            ->orderByDesc('observed_on')
            ->orderByDesc('legacy_date_int')
            ->orderByDesc('id')
            ->first();

        $observations = collect([$currentObservation]);
        if ($myLastObservation) {
            $observations->push($myLastObservation);
        }

        $preloadedData = $isComet ? $this->preloadCometData($observations) : $this->preloadDeepskyData($observations);
        if ($isComet) {
            $comet = ($preloadedData['preloaded_comets'] ?? collect())->get($currentObservation->objectid);
            $objectName = (string) ($comet->name ?? $selectedUnified->target_name ?? __('Unknown comet'));
        } else {
            $objectName = (string) ($currentObservation->objectname ?? '');
        }

        return view('observations.compare-my-last', array_merge([
            'observationType' => $isComet ? 'comet' : 'deepsky',
            'currentObservation' => $currentObservation,
            'myLastObservation' => $myLastObservation,
            'objectName' => $objectName,
            'objectSlug' => Str::slug($objectName, '-'),
            'currentObserver' => $observerUser,
            'currentDetailUrl' => $this->buildObservationDetailUrl(
                $observerUser->slug ?: $observerUser->username,
                $this->normalizeObservedOnDate($selectedUnified->observed_on, $date),
                Str::slug((string) ($selectedUnified->target_name ?? ''), '-'),
                $wantedIndex
            ),
            'myLastDetailUrl' => $myLastObservation ? $this->buildObservationDetailUrlForUnified((int) $myLastObservation->id) : null,
        ], $preloadedData));
    }

    /**
     * Show the observation search page with an optional object name filter.
     */
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');

        return view('observations.search', ['q' => $q]);
    }

    /**
     * Extract unique objects from the current observation search result set.
     * Redirect directly to the object page when only one unique object remains.
     */
    public function searchObjects(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $filters = $this->extractObservationSearchFilters($request);
        // Two rows are enough to tell "none", "exactly one" and "several" apart;
        // the results table matches the full set in the database itself.
        $objects = app(ObservationSearchQuery::class)->targets($q, $filters)->limit(2)->get();

        if ($objects->isEmpty()) {
            $redirectParams = $q !== '' ? ['q' => $q] : [];
            $redirectParams = array_merge($redirectParams, $filters);

            return redirect()
                ->route(
                    $filters !== [] ? 'search.observations.advanced.results' : 'observations.search',
                    $redirectParams
                )
                ->with('error', __('No objects were found in the selected observations.'));
        }

        if ($objects->count() === 1) {
            $object = $objects->first();

            return redirect()->route('object.show', [
                'slug' => $this->extractSearchObjectSlug([
                    'source_table' => $object->source_table,
                    'name' => $object->name,
                ]),
            ]);
        }

        return view('search.results', [
            'q' => $q,
            'title' => $q !== ''
                ? __('Objects from observations matching ":q"', ['q' => $q])
                : ($filters !== [] ? __('Objects from selected observations') : __('Objects from observations')),
            'observationSearch' => ['q' => $q, 'filters' => $filters],
        ]);
    }

    /**
     * Show global list of observations (both deepsky and comet) paginated.
     */
    public function index()
    {
        // Reject pages past the cap (or no page number) before doing any work.
        if (!ListingPagination::isValidPage(request(), 'deepsky')) {
            abort(404);
        }

        $obsType = request()->query('obs_type', 'all');
        $allowedTypes = ['all', 'deepsky', 'comet', 'solarsystem'];
        if (!in_array($obsType, $allowedTypes)) {
            $obsType = 'all';
        }

        $sort = request()->query('sort', 'observed');

        $query = Observation::query();
        if ($obsType === 'all') {
            $query->whereIn('target_type', ['deepsky', 'comet', 'planet', 'solarsystem', 'lunar']);
        } elseif ($obsType === 'solarsystem') {
            $query->whereIn('target_type', ['planet', 'solarsystem', 'lunar']);
        } else {
            $query->where('target_type', $obsType);
        }

        if ($sort === 'id') {
            $query->orderByDesc('id');
        } else {
            $query->orderByDesc('observed_on')->orderByDesc('observed_time')->orderByDesc('id');
        }

        $deepsky = ListingPagination::cap($query->deferredPaginate(20, 'deepsky'));

        $this->preloadCards($deepsky);

        return view('observations.show', [
            'user' => '',
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
            'obsType' => $obsType,
        ]);
    }

    /**
     * Turn the table and slug posted by the observation form into the target
     * stored on the observation (see the scheme in App\Models\Observation):
     * deep-sky objects by their canonical name, comets by their id, other
     * bodies by their slug. Returns null when the target does not exist.
     *
     * @return array{type: string, table: string, key: string, name: string}|null
     */
    private function resolveObservationTarget(string $table, string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        if ($table === 'objects' || $table === 'objectnames') {
            $name = Observation::canonicalDeepskyName($slug);

            return $name === null ? null : ['type' => Observation::TYPE_DEEPSKY, 'table' => 'objects', 'key' => $name, 'name' => $name];
        }

        if ($table === 'cometobjects' || $table === 'comets') {
            $row = DB::table('cometobjects')->where('slug', $slug)->first();
            if (!$row && ctype_digit($slug)) {
                $row = DB::table('cometobjects')->where('id', (int) $slug)->first();
            }

            return $row ? ['type' => Observation::TYPE_COMET, 'table' => 'cometobjects', 'key' => (string) $row->id, 'name' => $row->name] : null;
        }

        $types = ['planets' => 'planet', 'moons' => 'lunar', 'lunar_features' => 'lunar'];
        if (!isset($types[$table])) {
            return null;
        }
        $row = DB::table($table)->where('slug', $slug)->first();
        if (!$row && ctype_digit($slug)) {
            $row = DB::table($table)->where('id', (int) $slug)->first();
        }

        return $row ? ['type' => $types[$table], 'table' => $table, 'key' => $row->slug, 'name' => $row->name] : null;
    }

    private function extractObservationSearchFilters(Request $request): array
    {
        $filters = $request->except(['_token', 'page', 'q']);

        return array_filter($filters, function ($value): bool {
            if (is_array($value)) {
                return collect($value)
                    ->flatten()
                    ->contains(fn($item): bool => trim((string) $item) !== '');
            }

            return trim((string) $value) !== '';
        });
    }

    /**
     * @param  array{source_table: string, name: string}  $object
     */
    private function extractSearchObjectSlug(array $object): string
    {
        $row = DB::table('search_index')
            ->select(['source_table', 'source_pk', 'name', 'display_name'])
            ->where('source_table', $object['source_table'])
            ->whereRaw('LOWER(COALESCE(name, display_name)) = ?', [Str::lower($object['name'])])
            ->orderBy('id')
            ->first();

        if ($row && ($row->source_table ?? null) === 'objects' && !empty($row->source_pk)) {
            return (string) $row->source_pk;
        }

        return Str::slug((string) ($row->name ?? $row->display_name ?? $object['name']), '-');
    }

    /**
     * Show observations for a specific observer (both deepsky and comet).
     */
    public function show(string $slug)
    {
        // Try to resolve the slug as a user first (existing behaviour)
        $user = User::where('slug', $slug)->first();
        if ($user) {
            // Show only unified observations for this observer (no legacy rows)
            $sort = request()->query('sort', 'observed');

            if ($sort === 'id') {
                $query = Observation::where('observer_username', $user->username)->orderByDesc('id');
            } else {
                $query = Observation::where('observer_username', $user->username)
                    ->orderByDesc('observed_on')
                    ->orderByDesc('observed_time')
                    ->orderByDesc('id');
            }

            $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'sort' => $sort,
            ], $preloadedData));
        }

        // Not a user: attempt to resolve the slug to a deepsky object (objectnames / objects)
        $raw = (string) $slug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) {
            $candidates[] = $slugified;
        }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) {
            $candidates[] = $nospace;
        }
        // remove leading zeros in numeric portions (m-031 -> m-31)
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                $objectName = $on->objectname;
                break;
            }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                $objectName = $o->name;
                break;
            }
        }

        // Fallback: try case-insensitive name/altname matches
        if (!$objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->where('objectname', $cand)
                    ->orWhere('altname', $cand)
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->where('name', $cand)->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        if ($objectName) {
            $deepsky = Observation::ofDeepskyObject($objectName)->orderBy('observed_on', 'desc')->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            // Provide a lightweight user-like object so the view header can show a title
            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $objectName,
            ], $preloadedData));
        }

        abort(404);
    }

    /**
     * Show comet-only observations (global)
     */
    public function cometIndex()
    {
        if (!ListingPagination::isValidPage(request(), 'comet')) {
            abort(404);
        }

        $comet = ListingPagination::cap(Observation::comets()->orderBy('id', 'desc')->deferredPaginate(20, 'comet'));

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCards($comet);

        return view('observations.show', array_merge([
            'user' => '',
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ], $preloadedData));
    }

    /**
     * Show comet observations filtered by object slug (object-scoped comet list).
     * URL: /cometobservations/{object}, which is also /cometobservations/{observer}: a slug that
     * is no comet but a user shows the comet observations of that observer (cometShow()).
     */
    public function cometIndexByObject(string $slug)
    {
        // Try to resolve slug to a comet object id/name. Support both modern and legacy tables.
        $raw = (string) $slug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower)
            $candidates[] = $slugified;
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower)
            $candidates[] = $nospace;
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        $objectId = null;

        // Try modern CometObject first
        try {
            foreach ($candidates as $cand) {
                $co = \App\Models\CometObject::where('name', $cand)->orWhere('slug', $cand)->first();
                if ($co) {
                    $objectName = $co->name;
                    $objectId = $co->id;
                    break;
                }
            }
        } catch (\Throwable $_) {
        }

        // Try the cometobjects table
        if (!$objectId) {
            try {
                foreach ($candidates as $cand) {
                    $row = DB::table('cometobjects')->where('name', $cand)->orWhere('slug', $cand)->first();
                    if ($row) {
                        $objectName = $row->name ?? null;
                        $objectId = $row->id ?? null;
                        break;
                    }
                }
            } catch (\Throwable $_) {
            }
        }

        // If slug resolves to a user instead, forward to cometShow (observer view)
        try {
            $maybeUser = User::where('slug', $slug)->first();
            if ($maybeUser && !$objectId) {
                return $this->cometShow($slug);
            }
        } catch (\Throwable $_) {
        }

        if (!$objectId) {
            abort(404);
        }

        $comet = Observation::ofComet($objectId)->orderBy('observed_on', 'desc')->paginate(20, ['*'], 'comet')->appends(ListingPagination::query());

        $fakeUser = (object) ['name' => $objectName ?? $slug, 'slug' => $slug, 'username' => null];

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCards($comet);

        return view('observations.show', array_merge([
            'user' => $fakeUser,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
            'objectFilter' => true,
            'objectName' => $objectName,
        ], $preloadedData));
    }

    /**
     * Show comet observations for a specific observer filtered by object.
     * URL: /cometobservations/{observer}/{object}
     */
    public function cometShowObserverObject(string $observerSlug, string $objectSlug)
    {
        $user = User::where('slug', $observerSlug)->firstOrFail();

        // Resolve object slug to comet object id (reuse logic above)
        $raw = (string) $objectSlug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower)
            $candidates[] = $slugified;
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower)
            $candidates[] = $nospace;
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        $objectId = null;
        try {
            foreach ($candidates as $cand) {
                $co = \App\Models\CometObject::where('name', $cand)->orWhere('slug', $cand)->first();
                if ($co) {
                    $objectName = $co->name;
                    $objectId = $co->id;
                    break;
                }
            }
        } catch (\Throwable $_) {
        }
        if (!$objectId) {
            try {
                foreach ($candidates as $cand) {
                    $row = DB::table('cometobjects')->where('name', $cand)->orWhere('slug', $cand)->first();
                    if ($row) {
                        $objectName = $row->name ?? null;
                        $objectId = $row->id ?? null;
                        break;
                    }
                }
            } catch (\Throwable $_) {
            }
        }

        $query = Observation::comets()->where('observer_username', $user->username)->orderBy('observed_on', 'desc');
        if ($objectId) {
            $query->where('target_key', $objectId);
        }

        $comet = $query->paginate(20, ['*'], 'comet')->appends(ListingPagination::query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCards($comet);

        return view('observations.show', array_merge([
            'user' => $user,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
            'objectFilter' => true,
            'objectName' => $objectName,
        ], $preloadedData));
    }

    /**
     * Show comet-only observations for a specific observer
     */
    public function cometShow(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $comet = Observation::comets()->where('observer_username', $user->username)->orderBy('observed_on', 'desc')->paginate(20, ['*'], 'comet')->appends(ListingPagination::query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCards($comet);

        return view('observations.show', array_merge([
            'user' => $user,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ], $preloadedData));
    }

    /**
     * Show drawings for a specific observer or object.
     * If slug resolves to a user, show that user's drawings; otherwise try to resolve
     * the slug to a deepsky object and show drawings for that object (hasDrawing = 1).
     */
    public function showObjectDrawings(string $slug)
    {
        // If slug matches a user, reuse existing pattern but filter drawings only
        $user = User::where('slug', $slug)->first();
        if ($user) {
            $deepsky = Observation::where('observer_username', $user->username)->where('has_drawing', 1)->orderBy('observed_on', 'desc')->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'drawingsOnly' => true,
            ], $preloadedData));
        }

        // Attempt to resolve the slug to an object name (objectnames / objects)
        $raw = (string) $slug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) {
            $candidates[] = $slugified;
        }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) {
            $candidates[] = $nospace;
        }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                $objectName = $on->objectname;
                break;
            }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                $objectName = $o->name;
                break;
            }
        }

        if (!$objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->where('objectname', $cand)
                    ->orWhere('altname', $cand)
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->where('name', $cand)->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        if ($objectName) {
            $deepsky = Observation::ofDeepskyObject($objectName)->where('has_drawing', 1)->orderBy('observed_on', 'desc')->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
            ], $preloadedData));
        }

        // Not a legacy deepsky object — try solar-system targets (Sun, planets, Moon and lunar features)
        $solarTarget = $this->resolveSolarSystemTarget($candidates);
        $planet = $solarTarget['record'] ?? null;

        if ($planet) {
            $possibleKeys = $solarTarget['keys'];

            // Query unified observations for planet-targeted rows that have drawings
            $query = Observation::where('target_table', $solarTarget['table'])
                ->whereIn('target_key', $possibleKeys)
                ->where('has_drawing', 1)
                ->orderBy('observed_on', 'desc');

            $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            $fakeUser = (object) ['name' => $planet->name ?? $slug, 'slug' => $slug, 'username' => null];

            // Preload users for unified observations
            $usernames = $deepsky->pluck('observer_username')->unique()->filter();
            $users = $usernames->isNotEmpty() ? User::whereIn('username', $usernames)->get()->keyBy('username') : collect();

            $preloadedData = [
                'preloaded_users' => $users,
                'preloaded_objects' => collect(),
                'preloaded_locations' => collect(),
                'preloaded_instruments' => collect(),
                'preloaded_eyepieces' => collect(),
                'preloaded_filters' => collect(),
                'preloaded_constellations' => collect(),
            ];

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $planet->name ?? $slug,
                'drawingsOnly' => true,
            ], $preloadedData));
        }

        // Not a deepsky object — maybe this slug refers to a comet object (modern or legacy).
        // If so, redirect to the comet drawings handler so comet drawings are displayed.
        $objectSlug = $slug;
        $objectId = null;
        try {
            foreach ($candidates as $cand) {
                $co = \App\Models\CometObject::where('name', $cand)->orWhere('slug', $cand)->first();
                if ($co) {
                    $objectId = $co->id;
                    break;
                }
            }
        } catch (\Throwable $_) {
        }

        if (!$objectId) {
            try {
                foreach ($candidates as $cand) {
                    $row = DB::table('cometobjects')->where('name', $cand)->orWhere('slug', $cand)->first();
                    if ($row) {
                        $objectId = $row->id ?? null;
                        break;
                    }
                }
            } catch (\Throwable $_) {
            }
        }

        if ($objectId) {
            return redirect()->route('cometdrawings.show', ['observer' => $objectSlug]);
        }

        abort(404);
    }

    /**
     * Show observations for a specific observer filtered by object.
     * URL: /observations/{observer}/{object}
     */
    public function showObserverObject(string $observerSlug, string $objectSlug)
    {
        $user = User::where('slug', $observerSlug)->firstOrFail();

        // Resolve object slug to canonical objectname
        $raw = (string) $objectSlug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) {
            $candidates[] = $slugified;
        }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) {
            $candidates[] = $nospace;
        }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                $objectName = $on->objectname;
                break;
            }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                $objectName = $o->name;
                break;
            }
        }
        if (!$objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->where('objectname', $cand)
                    ->orWhere('altname', $cand)
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->where('name', $cand)->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        // Default query: observations by this observer
        $legacyQuery = Observation::where('observer_username', $user->username)->orderBy('observed_on', 'desc');

        if ($objectName) {
            $legacyQuery->ofDeepskyObject($objectName);

            $deepsky = $legacyQuery->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $objectName,
            ], $preloadedData));
        }

        // Not a legacy deepsky object — try solar-system targets (Sun, planets, Moon and lunar features)
        $solarTarget = $this->resolveSolarSystemTarget($candidates);
        $planet = $solarTarget['record'] ?? null;

        if ($planet) {
            $possibleKeys = $solarTarget['keys'];

            $query = Observation::where('target_table', $solarTarget['table'])
                ->whereIn('target_key', $possibleKeys)
                ->where('observer_username', $user->username)
                ->orderBy('observed_on', 'desc');

            $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            // Preload users for unified observations
            $usernames = $deepsky->pluck('observer_username')->unique()->filter();
            $users = $usernames->isNotEmpty() ? User::whereIn('username', $usernames)->get()->keyBy('username') : collect();

            $preloadedData = [
                'preloaded_users' => $users,
                'preloaded_objects' => collect(),
                'preloaded_locations' => collect(),
                'preloaded_instruments' => collect(),
                'preloaded_eyepieces' => collect(),
                'preloaded_filters' => collect(),
                'preloaded_constellations' => collect(),
            ];

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $planet->name ?? $objectSlug,
            ], $preloadedData));
        }

        abort(404);
    }

    /**
     * Show drawings for a specific observer filtered by object.
     * URL: /observations/drawings/{observer}/{object}
     */
    public function showObserverObjectDrawings(string $observerSlug, string $objectSlug)
    {
        $user = User::where('slug', $observerSlug)->firstOrFail();

        // Resolve object slug to canonical objectname (reuse same logic)
        $raw = (string) $objectSlug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) {
            $candidates[] = $slugified;
        }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) {
            $candidates[] = $nospace;
        }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                $objectName = $on->objectname;
                break;
            }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                $objectName = $o->name;
                break;
            }
        }
        if (!$objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->where('objectname', $cand)
                    ->orWhere('altname', $cand)
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->where('name', $cand)->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        // Query for drawings by this observer
        $legacyQuery = Observation::where('observer_username', $user->username)->where('has_drawing', 1)->orderBy('observed_on', 'desc');

        if ($objectName) {
            $legacyQuery->ofDeepskyObject($objectName);

            $deepsky = $legacyQuery->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $objectName,
            ], $preloadedData));
        }

        // Not a legacy deepsky object — try solar-system targets (Sun, planets, Moon and lunar features)
        $solarTarget = $this->resolveSolarSystemTarget($candidates);
        $planet = $solarTarget['record'] ?? null;

        if ($planet) {
            $possibleKeys = $solarTarget['keys'];

            $query = Observation::where('target_table', $solarTarget['table'])
                ->whereIn('target_key', $possibleKeys)
                ->where('has_drawing', 1)
                ->where('observer_username', $user->username)
                ->orderBy('observed_on', 'desc');

            $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            // Preload users for unified observations
            $usernames = $deepsky->pluck('observer_username')->unique()->filter();
            $users = $usernames->isNotEmpty() ? User::whereIn('username', $usernames)->get()->keyBy('username') : collect();

            $preloadedData = [
                'preloaded_users' => $users,
                'preloaded_objects' => collect(),
                'preloaded_locations' => collect(),
                'preloaded_instruments' => collect(),
                'preloaded_eyepieces' => collect(),
                'preloaded_filters' => collect(),
                'preloaded_constellations' => collect(),
            ];

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $planet->name ?? $objectSlug,
            ], $preloadedData));
        }

        abort(404);
    }

    /**
     * Show a global object-scoped observations listing (legacy only).
     * URL: /observations/object/{slug}
     */
    public function showObject(string $slug)
    {
        $raw = (string) $slug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) {
            $candidates[] = $slugified;
        }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) {
            $candidates[] = $nospace;
        }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) {
                $objectName = $on->objectname;
                break;
            }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) {
                $objectName = $o->name;
                break;
            }
        }

        if (!$objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->where('objectname', $cand)
                    ->orWhere('altname', $cand)
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->where('name', $cand)->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        if ($objectName) {
            $deepsky = Observation::ofDeepskyObject($objectName)->orderBy('observed_on', 'desc')->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadCards($deepsky);

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $objectName,
            ], $preloadedData));
        }

        // Not found in legacy object names — try solar-system targets (Sun, planets, Moon and lunar features)
        $solarTarget = $this->resolveSolarSystemTarget($candidates);
        $planet = $solarTarget['record'] ?? null;

        if ($planet) {
            $possibleKeys = $solarTarget['keys'];

            // Query unified observations table for planet-targeted rows
            $query = Observation::where('target_table', $solarTarget['table'])
                ->whereIn('target_key', $possibleKeys)
                ->orderBy('observed_on', 'desc');

            $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(ListingPagination::query());

            $fakeUser = (object) ['name' => $planet->name ?? $slug, 'slug' => $slug, 'username' => null];

            // Preload users for unified observations to improve rendering
            $usernames = $deepsky->pluck('observer_username')->unique()->filter();
            $users = $usernames->isNotEmpty() ? User::whereIn('username', $usernames)->get()->keyBy('username') : collect();

            $preloadedData = [
                'preloaded_users' => $users,
                'preloaded_objects' => collect(),
                'preloaded_locations' => collect(),
                'preloaded_instruments' => collect(),
                'preloaded_eyepieces' => collect(),
                'preloaded_filters' => collect(),
                'preloaded_constellations' => collect(),
            ];

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $planet->name ?? $slug,
            ], $preloadedData));
        }

        abort(404);
    }

    /**
     * Resolve slug candidates to a solar-system target stored in the unified
     * tables: a planet (including the Sun), a lunar feature or a moon.
     *
     * Returns the matched record, its table (the value of `target_table` in
     * the observations table) and the `target_key` values that may refer to
     * it, or null when nothing matches.
     */
    private function resolveSolarSystemTarget(array $candidates): ?array
    {
        foreach (['planets', 'lunar_features', 'moons'] as $table) {
            $record = null;
            foreach ($candidates as $cand) {
                try {
                    $record = DB::table($table)->where('slug', $cand)->first();
                } catch (\Throwable $_) {
                    $record = null;
                }
                if ($record)
                    break;
            }

            if (!$record) {
                foreach ($candidates as $cand) {
                    try {
                        $record = DB::table($table)->where('name', $cand)->first();
                    } catch (\Throwable $_) {
                        $record = null;
                    }
                    if ($record)
                        break;
                }
            }

            if ($record) {
                $keys = [];
                if (!empty($record->slug))
                    $keys[] = (string) $record->slug;
                if (!empty($record->name)) {
                    $keys[] = (string) $record->name;
                    $keys[] = mb_strtolower((string) $record->name);
                }
                if (!empty($record->id))
                    $keys[] = (string) $record->id;

                return [
                    'record' => $record,
                    'table' => $table,
                    'keys' => array_values(array_unique(array_filter(array_merge($keys, $candidates)))),
                ];
            }
        }

        return null;
    }

    /**
     * Preload what the observation cards of a list page show (observers,
     * objects, equipment, likes, ...) in grouped queries; the cards read it
     * from ObservationCardData, so nothing is passed to the view.
     */
    private function preloadCards($observations): array
    {
        ObservationCardData::instance()->preload($observations);

        return [];
    }

    /**
     * Preload all related data for deepsky observations to avoid N+1 queries.
     */
    private function preloadDeepskyData($observations)
    {
        if ($observations->isEmpty()) {
            return [];
        }

        // Extract all unique IDs from the observations
        $usernames = $observations->pluck('observerid')->unique()->filter();
        $objectNames = $observations->pluck('objectname')->unique()->filter();
        $locationIds = $observations->pluck('locationid')->unique()->filter();
        $instrumentIds = $observations->pluck('instrumentid')->unique()->filter();
        $eyepieceIds = $observations->pluck('eyepieceid')->unique()->filter()->reject(fn($id) => $id <= 0);
        $filterIds = $observations->pluck('filterid')->unique()->filter()->reject(fn($id) => $id <= 0);
        $lensIds = $observations->pluck('lensid')->unique()->filter()->reject(fn($id) => $id <= 0);

        // Batch load all related models
        $users = User::whereIn('username', $usernames)->get()->keyBy('username');
        $objects = Target::whereIn('name', $objectNames)->get()->keyBy('name');
        // Also index fetched objects by slug so views can resolve objects when
        // the observation contains a slug-like `objectname` value instead of
        // the canonical object `name`.
        try {
            $objects = $objects->merge($objects->mapWithKeys(fn($o) => [$o->slug => $o]));
        } catch (\Throwable $_) {
            // ignore problems mapping slugs
        }
        $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        $instruments = Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');
        $eyepieces = $eyepieceIds->isNotEmpty() ? Eyepiece::whereIn('id', $eyepieceIds)->get()->keyBy('id') : collect();
        $filters = $filterIds->isNotEmpty() ? Filter::whereIn('id', $filterIds)->get()->keyBy('id') : collect();
        $lenses = $lensIds->isNotEmpty() ? Lens::whereIn('id', $lensIds)->get()->keyBy('id') : collect();

        // Load constellations for all objects
        $constellationIds = $objects->pluck('con')->unique()->filter();
        $constellations = Constellation::whereIn('id', $constellationIds)->get()->keyBy('id');

        return [
            'preloaded_users' => $users,
            'preloaded_objects' => $objects,
            'preloaded_locations' => $locations,
            'preloaded_instruments' => $instruments,
            'preloaded_eyepieces' => $eyepieces,
            'preloaded_filters' => $filters,
            'preloaded_lenses' => $lenses,
            'preloaded_constellations' => $constellations,
        ];
    }

    /**
     * Preload all related data for comet observations to avoid N+1 queries.
     */
    private function preloadCometData($observations)
    {
        if ($observations->isEmpty()) {
            return [];
        }

        // Extract all unique IDs from the observations
        $usernames = $observations->pluck('observerid')->unique()->filter();
        $objectIds = $observations->pluck('objectid')->unique()->filter();
        $locationIds = $observations->pluck('locationid')->unique()->filter();
        $instrumentIds = $observations->pluck('instrumentid')->unique()->filter();

        // Batch load all related models
        $users = User::whereIn('username', $usernames)->get()->keyBy('username');
        $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        $instruments = Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');

        // Load comet objects from modern table
        $comets = collect();
        try {
            $comets = \App\Models\CometObject::whereIn('id', $objectIds)->get()->keyBy('id');
        } catch (\Throwable $_) {
            // Fallback to legacy table if modern table doesn't exist
            try {
                $legacyComets = DB::table('cometobjects')
                    ->whereIn('id', $objectIds)
                    ->get();
                $comets = collect($legacyComets)->keyBy('id');
            } catch (\Throwable $_) {
            }
        }

        // Attach comet-specific observation detail fields (from
        // `comet_observation_details`) to each observation object so
        // legacy templates can read expected properties like `mag`,
        // `maguncertain`, `lessmagnitude`, `dc`, `coma`, `tail`, `pa`,
        // `methode` and `chart`.
        try {
            $obsIds = $observations->pluck('id')->unique()->filter()->values()->all();
            if (!empty($obsIds)) {
                $detailRows = DB::table('comet_observation_details')
                    ->whereIn('observation_id', $obsIds)
                    ->get()
                    ->keyBy('observation_id');

                foreach ($observations as $obsItem) {
                    $detail = $detailRows->get($obsItem->id) ?? null;
                    if ($detail) {
                        $obsItem->mag = isset($detail->magnitude) ? (float) $detail->magnitude : null;
                        $obsItem->maguncertain = !empty($detail->magnitude_uncertain) ? 1 : 0;
                        $obsItem->lessmagnitude = !empty($detail->less_magnitude) ? 1 : 0;
                        $obsItem->dc = $detail->dc ?? null;
                        $obsItem->coma = isset($detail->coma) ? (int) $detail->coma : null;
                        $obsItem->tail = isset($detail->tail) ? (int) $detail->tail : null;
                        $obsItem->pa = isset($detail->position_angle) ? (int) $detail->position_angle : null;
                        $obsItem->methode = $detail->method ?? null;
                        $obsItem->chart = $detail->chart ?? null;
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore detail loading failures to avoid breaking page render
        }

        return [
            'preloaded_users' => $users,
            'preloaded_comets' => $comets,
            'preloaded_locations' => $locations,
            'preloaded_instruments' => $instruments,
        ];
    }

    /**
     * Compare object identifiers by normalizing to common slug variants.
     */
    private function slugMatches(string $routeValue, string $name): bool
    {
        return Observation::slugMatches($routeValue, $name);
    }

    /**
     * Build canonical observation detail URL.
     * Index is omitted for the first observation.
     */
    private function buildObservationDetailUrl(string $observer, string $date, string $object, int $index): string
    {
        $params = [
            'observer' => $observer,
            'date' => $date,
            'object' => $object,
        ];

        if ($index > 1) {
            $params['index'] = $index;
        }

        return route('observations.detail', $params);
    }

    /**
     * Build canonical compare URL.
     * The index is always included: it sits in the middle of the URL
     * (.../{index?}/compare-my-last), so leaving it out gives an empty segment
     * that does not match the route.
     */
    private function buildObservationCompareUrl(string $observer, string $date, string $object, int $index): string
    {
        return route('observations.compare.my-last', [
            'observer' => $observer,
            'date' => $date,
            'object' => $object,
            'index' => max(1, $index),
        ]);
    }

    /**
     * Build canonical detail URL for a unified observation id.
     */
    private function buildObservationDetailUrlForUnified(int $unifiedId): ?string
    {
        return Observation::query()->find($unifiedId)?->detailUrl();
    }

    /**
     * Load likes/comments for a detail page in one place.
     *
     * @param  int  $observationId  the id in the `observations` table
     * @return array<string, mixed>
     */
    private function buildSocialData(int $observationId): array
    {
        $likesCount = ObservationLike::query()
            ->where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->where('observation_id', $observationId)
            ->count();

        $liked = auth()->check() && ObservationLike::query()
            ->where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->where('observation_id', $observationId)
            ->where('user_id', auth()->id())
            ->exists();

        $comments = ObservationComment::query()
            ->with('user')
            ->where('observation_type', ObservationComment::TYPE_OBSERVATION)
            ->where('observation_id', $observationId)
            ->latest()
            ->get();

        return [
            'likesCount' => $likesCount,
            'liked' => $liked,
            'comments' => $comments,
            'commentsCount' => $comments->count(),
        ];
    }

    /**
     * Build moon phase and horizon context for a specific observation.
     *
     * @param  object  $observation
     * @return array<string, mixed>|null
     */
    private function buildMoonContext(object $observation, ?Location $location): ?array
    {
        $observedAt = $this->resolveObservationDateTime($observation, $location);
        if (!$observedAt) {
            return null;
        }

        $timezone = $location?->timezone ?? config('app.timezone');
        $moon = new AstroMoon();

        try {
            $illuminatedFraction = $moon->illuminatedFraction($observedAt->copy());
        } catch (\Throwable $_) {
            $illuminatedFraction = null;
        }

        try {
            $phaseRatio = $moon->getPhaseRatio($observedAt->copy());
        } catch (\Throwable $_) {
            $phaseRatio = null;
        }

        $frameIndex = $this->determineMoonFrameIndex($phaseRatio, $illuminatedFraction);

        $context = [
            'observed_at' => $observedAt,
            'timezone' => $timezone,
            'has_specific_time' => $this->hasObservationTime($observation),
            'phase_ratio' => $phaseRatio,
            'illuminated_fraction' => $illuminatedFraction,
            'illumination_percent' => is_null($illuminatedFraction)
                ? null
                : (int) round(max(0.0, min(1.0, (float) $illuminatedFraction)) * 100),
            'frame_index' => $frameIndex,
            'frame_path' => $frameIndex !== null ? asset("images/moon/m{$frameIndex}.gif") : null,
            'phase_name' => $this->determineMoonPhaseName($phaseRatio, $illuminatedFraction),
            'horizon_status' => 'unknown',
            'altitude_degrees' => null,
        ];

        if (!$context['has_specific_time'] || !$location || !is_numeric($location->latitude) || !is_numeric($location->longitude)) {
            return $context;
        }

        try {
            $coords = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
            $siderealTime = Time::apparentSiderialTime($observedAt->copy(), $coords);

            $moon->calculateEquatorialCoordinates($observedAt->copy(), $coords, (float) ($location->elevation ?? 0.0));
            $horizontal = $moon
                ->getEquatorialCoordinatesToday()
                ->convertToHorizontal($coords, $siderealTime);

            $altitude = (float) $horizontal->getAltitude()->getCoordinate();

            $context['altitude_degrees'] = round($altitude, 1);
            $context['horizon_status'] = $altitude >= 0 ? 'above' : 'below';
        } catch (\Throwable $_) {
            $context['horizon_status'] = 'unknown';
            $context['altitude_degrees'] = null;
        }

        return $context;
    }

    /**
     * Resolve an observation's local datetime, falling back to noon for date-only observations.
     */
    private function resolveObservationDateTime(object $observation, ?Location $location): ?Carbon
    {
        $dateValue = trim((string) ($observation->date ?? ''));
        if ($dateValue === '') {
            return null;
        }

        $formattedDate = strlen($dateValue) === 8
            ? substr($dateValue, 0, 4) . '-' . substr($dateValue, 4, 2) . '-' . substr($dateValue, 6, 2)
            : $dateValue;

        $timezone = $location?->timezone ?? config('app.timezone');

        try {
            if ($this->hasObservationTime($observation)) {
                $timeValue = (int) $observation->time;
                $hours = intdiv($timeValue, 100);
                $minutes = $timeValue % 100;

                return Carbon::createFromFormat(
                    'Y-m-d H:i',
                    sprintf('%s %02d:%02d', $formattedDate, $hours, $minutes),
                    // Observation times are UT.
                    'UTC',
                );
            }

            return Carbon::createFromFormat('Y-m-d H:i', $formattedDate . ' 12:00', $timezone);
        } catch (\Throwable $_) {
            return null;
        }
    }

    /**
     * Determine whether a legacy observation has a meaningful time value.
     */
    private function hasObservationTime(object $observation): bool
    {
        if (!isset($observation->time) || $observation->time === null || $observation->time === '') {
            return false;
        }

        $timeValue = (int) $observation->time;

        return $timeValue >= 0 && $timeValue <= 2359 && ($timeValue % 100) < 60;
    }

    /**
     * Map moon phase values to the pre-rendered moon frame set.
     */
    private function determineMoonFrameIndex(mixed $phaseRatio, mixed $illuminatedFraction): ?int
    {
        if (is_null($phaseRatio) && is_null($illuminatedFraction)) {
            return null;
        }

        $phase = is_null($phaseRatio) ? 0.0 : max(0.0, min(1.0, (float) $phaseRatio));
        $illum = is_null($illuminatedFraction) ? 0.0 : max(0.0, min(1.0, (float) $illuminatedFraction));
        $idxHalf = max(0, min(20, (int) round($illum * 20)));

        return $phase <= 0.5 ? $idxHalf : 40 - $idxHalf;
    }

    /**
     * Build a simple human-readable moon phase label.
     */
    private function determineMoonPhaseName(mixed $phaseRatio, mixed $illuminatedFraction): string
    {
        $phase = is_null($phaseRatio) ? null : max(0.0, min(1.0, (float) $phaseRatio));
        $illum = is_null($illuminatedFraction) ? null : max(0.0, min(1.0, (float) $illuminatedFraction));

        if (!is_null($illum)) {
            if ($illum <= 0.02) {
                return __('New moon');
            }

            if ($illum >= 0.98) {
                return __('Full moon');
            }
        }

        if (is_null($phase)) {
            return __('Moon phase');
        }

        if ($phase < 0.25) {
            return __('Waxing moon');
        }

        if ($phase < 0.5) {
            return __('Waxing gibbous');
        }

        if ($phase < 0.75) {
            return __('Waning moon');
        }

        return __('Waning crescent');
    }

    /**
     * Normalize observed_on values from the unified table to Y-m-d.
     */
    private function normalizeObservedOnDate(mixed $observedOn, string $fallback): string
    {
        if (empty($observedOn)) {
            return $fallback;
        }

        try {
            return \Carbon\Carbon::parse($observedOn)->format('Y-m-d');
        } catch (\Throwable $_) {
            return $fallback;
        }
    }

    /**
     * Simple redirector for the old selection page.
     */
    public function select(Request $request)
    {
        // Render the selection/search page directly so users can enter an
        // object name before opening the full observation form.
        return view('observations.add_select');
    }

    /**
     * Show the observation creation form. Accepts an optional `slug` query
     * parameter to pre-resolve a target (object / planet / comet) and supply
     * lightweight summary counts used by the view.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $slug = (string) $request->query('slug', '');

        // If no slug was provided, show the selection/search page so the
        // user can type or pick an object before opening the detailed
        // observation form. This avoids showing a "No object found for ''"
        // message when visiting /observation/add directly.
        if (empty($slug)) {
            return view('observations.add_select');
        }

        $resolved = ['type' => '', 'table' => '', 'record' => null];
        $objectSummary = null;

        // Instrument sets and locations for the current user (used by the form)
        $instrumentSets = collect();
        $instrumentSet = null;
        $locations = collect();

        try {
            if ($user) {
                $instrumentSets = \App\Models\InstrumentSet::where('user_id', $user->id)->where('active', 1)->get();
                if ($request->query('instrument_set')) {
                    $instrumentSet = $instrumentSets->firstWhere('id', $request->query('instrument_set'));
                }

                $locations = Location::where('user_id', $user->id)->where('active', 1)->get();
            }
        } catch (\Throwable $_) {
            $instrumentSets = collect();
            $locations = collect();
        }

        // Check whether the user added an observation within the last 15 minutes.
        // If so, pre-fill the form with those settings so observers can quickly
        // enter many observations from the same observing session in a row.
        // Otherwise fall back to the user's standard defaults (handled in the view).
        $prefill = [];
        if ($user) {
            try {
                $recentObs = Observation::where('observer_user_id', $user->id)
                    ->where('created_at', '>=', Carbon::now()->subMinutes(15))
                    ->orderByDesc('created_at')
                    ->first();
                if ($recentObs) {
                    $prefill = [
                        'observed_on' => $recentObs->observed_on
                            ? Carbon::parse($recentObs->observed_on)->format('Y-m-d')
                            : Carbon::yesterday()->format('Y-m-d'),
                        'location_id' => $recentObs->location_id,
                        'instrument_id' => $recentObs->instrument_id,
                        'eyepiece_id' => $recentObs->eyepiece_id,
                        'lens_id' => $recentObs->lens_id,
                        'filter_id' => $recentObs->filter_id,
                    ];
                }
            } catch (\Throwable $_) {
                $prefill = [];
            }
        }

        if (!empty($slug)) {
            $raw = (string) $slug;
            $candidates = Observation::slugCandidates($raw);
            try {
                \Log::debug('ObservationsController::create: candidates', ['slug' => $raw, 'candidates' => $candidates]);
            } catch (\Throwable $_) {
            }

            // Try legacy deepsky object names / objects first
            $objectName = null;
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')->where('slug', $cand)->first();
                if ($on) {
                    $objectName = $on->objectname;
                    $resolved['type'] = 'deepsky';
                    $resolved['table'] = 'objectnames';
                    // objectnames rows store `objectname` instead of `name`.
                    // Normalize so the view can always use ->name.
                    $on->name = $on->objectname ?? ($on->name ?? null);
                    $resolved['record'] = $on;
                    break;
                }
                $o = DB::table('objects')->where('slug', $cand)->first();
                if ($o) {
                    $objectName = $o->name;
                    $resolved['type'] = 'deepsky';
                    $resolved['table'] = 'objects';
                    $resolved['record'] = $o;
                    break;
                }
            }

            if (!$objectName) {
                foreach ($candidates as $cand) {
                    $on = DB::table('objectnames')
                        ->where('objectname', $cand)
                        ->orWhere('altname', $cand)
                        ->first();
                    if ($on) {
                        $objectName = $on->objectname;
                        $resolved['type'] = 'deepsky';
                        $resolved['table'] = 'objectnames';
                        $on->name = $on->objectname ?? ($on->name ?? null);
                        $resolved['record'] = $on;
                        break;
                    }

                    $o = DB::table('objects')->where('name', $cand)->first();
                    if ($o) {
                        $objectName = $o->name;
                        $resolved['type'] = 'deepsky';
                        $resolved['table'] = 'objects';
                        $resolved['record'] = $o;
                        break;
                    }
                }
            }

            if ($objectName) {
                $objectName = Observation::canonicalDeepskyName($objectName) ?? $objectName;
                $possibleKeys = array_values(array_unique(array_filter(array_merge($candidates, [$objectName]))));
                $objectObservations = fn() => Observation::ofDeepskyObject($objectName);
                $mine = fn($q) => $q->where('observer_username', $user->username);

                $objectSummary = [
                    'object_slug' => Str::slug($objectName, '-'),
                    'total_observations' => $objectObservations()->count(),
                    'your_observations' => $user ? $mine($objectObservations())->count() : 0,
                    'total_drawings' => $objectObservations()->where('has_drawing', 1)->count(),
                    'your_drawings' => $user ? $mine($objectObservations())->where('has_drawing', 1)->count() : 0,
                    'type_raw' => null,
                    'type_label' => null,
                    'constellation' => null,
                ];

                // Try to resolve the canonical object in the `objects` table to
                // provide a human-friendly type label (e.g. "Double star") and
                // ensure we display the canonical `name` rather than the slug.
                try {
                    $target = \App\Models\Target::where('name', $objectName)
                        ->orWhereIn('slug', $possibleKeys)
                        ->first();
                    if ($target) {
                        $objectSummary['type_raw'] = $target->type ?? null;
                        $objectSummary['type_label'] = $target->long_type();
                        // Ensure the resolved record exposes a `name` property for views
                        if (!empty($resolved['record']) && empty($resolved['record']->name) && !empty($target->name)) {
                            $resolved['record']->name = $target->name;
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore - best-effort only
                }
                // Fallback: if we still don't have a type_raw, try a direct DB lookup
                // to catch cases where name/slug variants differ.
                if (empty($objectSummary['type_raw'])) {
                    try {
                        $o = DB::table('objects')
                            ->whereIn('slug', $possibleKeys)
                            ->orWhere('name', $objectName)
                            ->first();
                        if ($o) {
                            $objectSummary['type_raw'] = $o->type ?? null;
                            $tt = DB::table('target_types')->where('id', $o->type)->first();
                            $objectSummary['type_label'] = $tt ? __($tt->type) : null;
                            if (!empty($resolved['record']) && empty($resolved['record']->name) && !empty($o->name)) {
                                $resolved['record']->name = $o->name;
                            }
                        }
                    } catch (\Throwable $_) {
                        // ignore
                    }
                }
            } else {
                // Try planets (Sun / Moon / planets)
                $planet = null;
                foreach ($candidates as $cand) {
                    try {
                        $planet = DB::table('planets')->where('slug', $cand)->first();
                    } catch (\Throwable $_) {
                        $planet = null;
                    }
                    if ($planet)
                        break;
                }

                if (!$planet) {
                    foreach ($candidates as $cand) {
                        try {
                            $planet = DB::table('planets')->where('name', $cand)->first();
                        } catch (\Throwable $_) {
                            $planet = null;
                        }
                        if ($planet)
                            break;
                    }
                }

                if ($planet) {
                    $resolved['type'] = 'planet';
                    $resolved['table'] = 'planets';
                    $resolved['record'] = $planet;

                    $possibleKeys = [];
                    if (!empty($planet->slug))
                        $possibleKeys[] = (string) $planet->slug;
                    if (!empty($planet->name))
                        $possibleKeys[] = (string) $planet->name;
                    if (!empty($planet->id))
                        $possibleKeys[] = (string) $planet->id;
                    $possibleKeys = array_values(array_unique(array_filter(array_merge($possibleKeys, $candidates))));

                    $unifiedTotal = Observation::where('target_table', 'planets')->whereIn('target_key', $possibleKeys)->count();
                    $unifiedYour = $user ? Observation::where('target_table', 'planets')->whereIn('target_key', $possibleKeys)->where('observer_username', $user->username)->count() : 0;
                    $unifiedDrawings = Observation::where('target_table', 'planets')->whereIn('target_key', $possibleKeys)->where('has_drawing', 1)->count();
                    $unifiedYourDrawings = $user ? Observation::where('target_table', 'planets')->whereIn('target_key', $possibleKeys)->where('observer_username', $user->username)->where('has_drawing', 1)->count() : 0;

                    $objectSummary = [
                        'object_slug' => $planet->slug ?? $slug,
                        'total_observations' => $unifiedTotal,
                        'your_observations' => $unifiedYour,
                        'total_drawings' => $unifiedDrawings,
                        'your_drawings' => $unifiedYourDrawings,
                        'type_raw' => null,
                        'type_label' => null,
                        'constellation' => null,
                    ];
                } else {
                    // Try moons and lunar features
                    $lunar = null;
                    foreach ($candidates as $cand) {
                        try {
                            $lunar = DB::table('lunar_features')->where('slug', $cand)->first();
                        } catch (\Throwable $_) {
                            $lunar = null;
                        }
                        if ($lunar)
                            break;
                    }

                    if (!$lunar) {
                        foreach ($candidates as $cand) {
                            try {
                                $lunar = DB::table('lunar_features')->where('name', $cand)->first();
                            } catch (\Throwable $_) {
                                $lunar = null;
                            }
                            if ($lunar)
                                break;
                        }
                    }

                    if (!$lunar) {
                        // Maybe it's a moon (e.g., different table); try moons table
                        foreach ($candidates as $cand) {
                            try {
                                $moon = DB::table('moons')->where('slug', $cand)->first();
                            } catch (\Throwable $_) {
                                $moon = null;
                            }
                            if (!empty($moon)) {
                                $lunar = $moon;
                                break;
                            }
                        }
                        if (!$lunar) {
                            foreach ($candidates as $cand) {
                                try {
                                    $moon = DB::table('moons')->where('name', $cand)->first();
                                } catch (\Throwable $_) {
                                    $moon = null;
                                }
                                if (!empty($moon)) {
                                    $lunar = $moon;
                                    break;
                                }
                            }
                        }
                    }

                    if ($lunar) {
                        // Determine whether this is a lunar feature or a moon record
                        $isMoonRecord = isset($lunar->feature_type) ? false : true;
                        if ($isMoonRecord) {
                            $resolved['type'] = 'moon';
                            $resolved['table'] = 'moons';
                            $resolved['record'] = $lunar;

                            $possibleKeys = [];
                            if (!empty($lunar->slug))
                                $possibleKeys[] = (string) $lunar->slug;
                            if (!empty($lunar->name))
                                $possibleKeys[] = (string) $lunar->name;
                            if (!empty($lunar->id))
                                $possibleKeys[] = (string) $lunar->id;
                            $possibleKeys = array_values(array_unique(array_filter(array_merge($possibleKeys, $candidates))));

                            $unifiedTotal = Observation::where('target_table', 'moons')->whereIn('target_key', $possibleKeys)->count();
                            $unifiedYour = $user ? Observation::where('target_table', 'moons')->whereIn('target_key', $possibleKeys)->where('observer_username', $user->username)->count() : 0;
                            $unifiedDrawings = Observation::where('target_table', 'moons')->whereIn('target_key', $possibleKeys)->where('has_drawing', 1)->count();
                            $unifiedYourDrawings = $user ? Observation::where('target_table', 'moons')->whereIn('target_key', $possibleKeys)->where('observer_username', $user->username)->where('has_drawing', 1)->count() : 0;

                            $objectSummary = [
                                'object_slug' => $lunar->slug ?? $slug,
                                'total_observations' => $unifiedTotal,
                                'your_observations' => $unifiedYour,
                                'total_drawings' => $unifiedDrawings,
                                'your_drawings' => $unifiedYourDrawings,
                                'type_raw' => null,
                                'type_label' => null,
                                'constellation' => null,
                            ];
                        } else {
                            // lunar feature
                            $resolved['type'] = 'lunar';
                            $resolved['table'] = 'lunar_features';
                            $resolved['record'] = $lunar;

                            $possibleKeys = [];
                            if (!empty($lunar->slug))
                                $possibleKeys[] = (string) $lunar->slug;
                            if (!empty($lunar->name))
                                $possibleKeys[] = (string) $lunar->name;
                            if (!empty($lunar->id))
                                $possibleKeys[] = (string) $lunar->id;
                            $possibleKeys = array_values(array_unique(array_filter(array_merge($possibleKeys, $candidates))));

                            $unifiedTotal = Observation::where('target_table', 'lunar_features')->whereIn('target_key', $possibleKeys)->count();
                            $unifiedYour = $user ? Observation::where('target_table', 'lunar_features')->whereIn('target_key', $possibleKeys)->where('observer_username', $user->username)->count() : 0;
                            $unifiedDrawings = Observation::where('target_table', 'lunar_features')->whereIn('target_key', $possibleKeys)->where('has_drawing', 1)->count();
                            $unifiedYourDrawings = $user ? Observation::where('target_table', 'lunar_features')->whereIn('target_key', $possibleKeys)->where('observer_username', $user->username)->where('has_drawing', 1)->count() : 0;

                            $objectSummary = [
                                'object_slug' => $lunar->slug ?? $slug,
                                'total_observations' => $unifiedTotal,
                                'your_observations' => $unifiedYour,
                                'total_drawings' => $unifiedDrawings,
                                'your_drawings' => $unifiedYourDrawings,
                                'type_raw' => $lunar->feature_type ?? null,
                                'type_label' => !empty($lunar->feature_type) ? ucfirst($lunar->feature_type) : null,
                                'constellation' => null,
                            ];
                        }
                    } else {
                        // Try comets (cometobjects table)
                        $comet = null;
                        foreach ($candidates as $cand) {
                            try {
                                $comet = DB::table('cometobjects')->where('slug', $cand)->first();
                            } catch (\Throwable $_) {
                                $comet = null;
                            }
                            if ($comet)
                                break;
                        }

                        if (!$comet) {
                            foreach ($candidates as $cand) {
                                try {
                                    $comet = DB::table('cometobjects')->where('name', $cand)->first();
                                } catch (\Throwable $_) {
                                    $comet = null;
                                }
                                if ($comet)
                                    break;
                            }
                        }

                        if ($comet) {
                            $resolved['type'] = 'comet';
                            $resolved['table'] = 'cometobjects';
                            $resolved['record'] = $comet;

                            $cometObservations = fn() => Observation::ofComet($comet->id);
                            $mine = fn($q) => $q->where('observer_username', $user->username);

                            $objectSummary = [
                                'object_slug' => $comet->slug ?? $slug,
                                'total_observations' => $cometObservations()->count(),
                                'your_observations' => $user ? $mine($cometObservations())->count() : 0,
                                'total_drawings' => $cometObservations()->where('has_drawing', 1)->count(),
                                'your_drawings' => $user ? $mine($cometObservations())->where('has_drawing', 1)->count() : 0,
                                'type_raw' => null,
                                'type_label' => null,
                                'constellation' => null,
                            ];
                        }
                    }
                }
            }

            try {
                \Log::debug('ObservationsController::create: resolved', ['slug' => $slug, 'resolved' => $resolved, 'objectSummary' => $objectSummary]);
            } catch (\Throwable $_) {
            }

            return view('observations.create', [
                'resolved' => $resolved,
                'slug' => $slug,
                'objectSummary' => $objectSummary,
                'instrumentSets' => $instrumentSets,
                'instrumentSet' => $instrumentSet,
                'locations' => $locations,
                'prefill' => $prefill,
                // Provide server upload limits to the form so the UI can show helpful guidance
                'php_upload_max_filesize' => ini_get('upload_max_filesize'),
                'php_post_max_size' => ini_get('post_max_size'),
                'php_upload_max_bytes' => $this->phpSizeToBytes(ini_get('upload_max_filesize')),
                'php_post_max_bytes' => $this->phpSizeToBytes(ini_get('post_max_size')),
            ]);
        }
    }

    /**
     * Store a new unified observation (basic implementation).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        // Debug logging to aid diagnosing upload issues
        try {
            \Log::debug('ObservationsController::store: incoming', [
                'user_id' => $user->id,
                'target_table' => $request->input('target_table'),
                'target_slug' => $request->input('target_slug'),
                'language_raw' => $request->input('language'),
                'has_sketch' => $request->hasFile('sketch'),
                'php_upload_max_filesize' => ini_get('upload_max_filesize'),
                'php_post_max_size' => ini_get('post_max_size'),
                'storage_public_exists' => is_dir(storage_path('app/public')),
                'public_storage_link' => is_link(public_path('storage')),
                'storage_writable' => is_writable(storage_path('app/public')),
            ]);

            if ($request->hasFile('sketch')) {
                $file = $request->file('sketch');
                \Log::debug('ObservationsController::store: sketch file info', [
                    'client_name' => $file->getClientOriginalName(),
                    'client_size' => $file->getSize(),
                    'client_mime' => $file->getMimeType(),
                    'error' => $file->getError(),
                ]);
            }
        } catch (\Throwable $_) {
            // ensure logging never breaks the request
        }

        // Pre-flight checks for uploads: detect oversized POSTs or PHP upload errors
        $uploadMaxBytes = $this->phpSizeToBytes(ini_get('upload_max_filesize') ?: '0');
        $postMaxBytes = $this->phpSizeToBytes(ini_get('post_max_size') ?: '0');

        // If the Content-Length exceeds PHP's post_max_size, PHP may drop the POST/FILES entirely.
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : null;
        if ($contentLength && $postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            return redirect()->back()->withInput()->withErrors([
                'sketch' => __('The uploaded sketch appears larger than the server maximum POST size (:size). Please reduce the file size or update your PHP settings.', ['size' => ini_get('post_max_size')])
            ]);
        }

        // If a file was provided but PHP reported an upload error, map it to a friendly message
        if ($request->hasFile('sketch')) {
            $file = $request->file('sketch');
            if (!$file->isValid()) {
                $err = $file->getError();
                $msg = __('The sketch failed to upload.');
                if ($err === UPLOAD_ERR_INI_SIZE) {
                    $msg = __('The uploaded sketch exceeds the server upload_max_filesize (:size). Reduce the file or update PHP settings.', ['size' => ini_get('upload_max_filesize')]);
                } elseif ($err === UPLOAD_ERR_FORM_SIZE) {
                    $msg = __('The uploaded sketch exceeds the maximum size allowed by the form.');
                } elseif ($err === UPLOAD_ERR_PARTIAL) {
                    $msg = __('The uploaded sketch was only partially uploaded. Please try again.');
                } elseif ($err === UPLOAD_ERR_NO_TMP_DIR) {
                    $msg = __('Missing a temporary folder on the server. Contact your administrator.');
                } elseif ($err === UPLOAD_ERR_CANT_WRITE) {
                    $msg = __('Failed to write uploaded file to disk. Check server permissions.');
                }

                return redirect()->back()->withInput()->withErrors(['sketch' => $msg]);
            }

            // Additional safety: ensure the file size does not exceed PHP reported upload_max_filesize
            $fileSize = $file->getSize() ?? 0;
            if ($uploadMaxBytes > 0 && $fileSize > $uploadMaxBytes) {
                return redirect()->back()->withInput()->withErrors([
                    'sketch' => __('The uploaded sketch is larger than the server maximum upload size (:size).', ['size' => ini_get('upload_max_filesize')])
                ]);
            }
        }

        // The equipment and location must belong to the observer: the
        // logged in user, or the owner of the observation being edited
        // (an administrator may edit someone else's observation).
        $equipmentOwnerId = $user->id;
        $editing = Observation::find($request->input('edit_unified_id'));
        if ($editing) {
            $equipmentOwnerId = $editing->observer_user_id
                ?? User::where('username', $editing->observer_username)->value('id')
                ?? $user->id;
        }
        $owned = fn (string $table) => Rule::exists($table, 'id')->where('user_id', $equipmentOwnerId);

        $validated = $request->validate([
            'target_table' => 'required|string',
            'target_slug' => 'required|string',
            'observed_on' => 'required|date',
            'observed_time' => 'nullable|string',
            'description' => 'nullable|string',
            'language' => 'nullable|string',
            'instrument_id' => ['nullable', 'integer', $owned('instruments')],
            'eyepiece_id' => ['nullable', 'integer', $owned('eyepieces')],
            'lens_id' => ['nullable', 'integer', $owned('lenses')],
            'filter_id' => ['nullable', 'integer', $owned('filters')],
            'location_id' => ['nullable', 'integer', $owned('locations')],
            'magnification' => 'nullable|string|max:32',
            'seeing' => 'nullable|integer|between:1,5',
            'transparency' => 'nullable|integer|between:1,5',
            'sqm' => 'nullable|numeric|between:10,23',
            'limiting_magnitude' => 'nullable|numeric|between:0,8',
            // Keep a reasonable validator cap (in kilobytes). Note: PHP limits may be lower than this.
            'sketch' => 'nullable|file|image|max:5120',
            // Deepsky-specific detail fields (optional)
            'visibility' => 'nullable|integer|min:0|max:7',
            'small_diameter' => 'nullable|numeric',
            'large_diameter' => 'nullable|numeric',
            'cluster_type' => 'nullable|string|max:1',
            'stellar' => 'nullable|integer',
            'extended' => 'nullable|integer',
            'resolved' => 'nullable|integer',
            'mottled' => 'nullable|integer',
            'unusual_shape' => 'nullable|integer',
            'partly_unresolved' => 'nullable|integer',
            'color_contrasts' => 'nullable|integer',
            'equal_brightness' => 'nullable|integer',
            'nice_field' => 'nullable|integer',
            'component1' => 'nullable|integer',
            'component2' => 'nullable|integer',
            // Comet-specific detail fields
            'comet_method' => 'nullable|string|max:1',
            'comet_chart' => 'nullable|string|max:2',
            'comet_magnitude_estimate' => 'nullable|numeric',
            'comet_magnitude_uncertain' => 'nullable',
            'comet_less_magnitude' => 'nullable',
            'comet_dc' => 'nullable|string|max:1',
            'comet_coma' => 'nullable|integer',
            'comet_tail_length' => 'nullable|integer',
            'comet_position_angle' => 'nullable|integer|between:0,360',
        ]);

        // Support updating an existing unified observation when `edit_unified_id` is provided.
        $editId = $request->input('edit_unified_id');
        if (!empty($editId)) {
            $obs = Observation::find($editId);
            if (!$obs) {
                return redirect()->back()->withInput()->withErrors(['edit_unified_id' => __('Observation not found for editing')]);
            }

            // Authorization: only the owner or an administrator may update
            $viewer = $request->user();
            $owns = ($viewer && ($viewer->id && $obs->observer_user_id == $viewer->id || $viewer->username && $obs->observer_username == $viewer->username));
            $isAdmin = $viewer && (method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges() || $viewer->isAdministrator());
            if (!$owns && !$isAdmin) {
                abort(403);
            }
        } else {
            $obs = new Observation();
            $obs->observer_username = $user->username;
            $obs->observer_user_id = $user->id;
        }
        $target = $this->resolveObservationTarget((string) $validated['target_table'], (string) $validated['target_slug']);
        if ($target === null) {
            return redirect()->back()->withInput()->withErrors(['target_slug' => __('No object found for') . " '" . $validated['target_slug'] . "'."]);
        }
        $obs->target_type = $target['type'];
        $obs->target_table = $target['table'];
        $obs->target_key = $target['key'];
        $obs->target_name = $target['name'];
        // The form gives the date and time in the time zone of the location;
        // they are stored in UT.
        $timezone = !empty($validated['location_id']) ? Location::whereKey($validated['location_id'])->value('timezone') : null;
        [$obs->observed_on, $obs->observed_time] = Observation::utcFromLocal(
            (string) $validated['observed_on'],
            $validated['observed_time'] ?? null,
            $timezone,
        );
        // Migrated observations keep the old YYYYMMDD / HHMM columns, which
        // the model and several views prefer over observed_on; keep them in
        // step with the edited date and time (-9999 = no time, as migrated).
        if ($obs->legacy_date_int !== null) {
            $obs->legacy_date_int = (int) Carbon::parse($obs->observed_on)->format('Ymd');
            $obs->legacy_time_int = $obs->observed_time !== null
                ? (int) str_replace(':', '', substr($obs->observed_time, 0, 5))
                : -9999;
        }
        $obs->description = $validated['description'] ?? null;
        // Accept either a plain language id or an object-like string (e.g. "{label:'English',value:'en',}")
        $langInput = $validated['language'] ?? null;
        $langValue = null;
        if (!empty($langInput)) {
            if (is_string($langInput)) {
                $trim = trim($langInput);
                // Quick JSON-ish detection: try JSON decode after normalizing single quotes
                if ((str_starts_with($trim, '{') && str_ends_with($trim, '}')) || str_contains($trim, 'label:')) {
                    $json = preg_replace('/([a-zA-Z0-9_]+)\s*:/', '"$1":', $trim);
                    $json = str_replace("'", '"', $json);
                    $json = preg_replace('/,\s*}/', '}', $json);
                    $data = json_decode($json, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                        if (!empty($data['value'])) {
                            $langValue = $data['value'];
                        } elseif (!empty($data['id'])) {
                            $langValue = $data['id'];
                        }
                    }

                    if ($langValue === null) {
                        if (preg_match('/value\s*[:=]\s*(?:"|\')?([a-zA-Z\-]+)(?:"|\')?/', $trim, $m)) {
                            $langValue = $m[1];
                        }
                    }
                }

                // If still null, assume the input itself is the language id
                if ($langValue === null) {
                    $langValue = $langInput;
                }
            } else {
                $langValue = $langInput;
            }
        }

        $obs->language = $langValue ?: ($obs->language ?: 'en');

        // Persist instrument/location and related IDs when present
        $obs->instrument_id = $validated['instrument_id'] ?? null;
        $obs->eyepiece_id = $validated['eyepiece_id'] ?? null;
        $obs->lens_id = $validated['lens_id'] ?? null;
        $obs->filter_id = $validated['filter_id'] ?? null;
        $obs->location_id = $validated['location_id'] ?? null;
        // The magnification is stored in the detail table of the target
        // type: the form fills it from the equipment, but it can be changed
        // (zoom eyepieces), and migrated observations have their own.
        $magnification = Observation::normalizeMagnification($validated['magnification'] ?? null);
        $obs->seeing = $validated['seeing'] ?? null;
        $obs->transparency = $validated['transparency'] ?? null;
        $obs->sqm = isset($validated['sqm']) && $validated['sqm'] !== '' ? round((float) $validated['sqm'], 2) : null;
        $obs->limiting_magnitude = isset($validated['limiting_magnitude']) && $validated['limiting_magnitude'] !== '' ? round((float) $validated['limiting_magnitude'], 2) : null;

        // Handle optional sketch upload
        if ($request->hasFile('sketch') && $request->file('sketch')->isValid()) {
            try {
                $path = $request->file('sketch')->store('observations', 'public');
                if ($path) {
                    // store the relative path so Storage::disk('public')->exists() can find it
                    $obs->drawing_filename = $path;
                    $obs->has_drawing = 1;
                } else {
                    return redirect()->back()->withInput()->withErrors(['sketch' => 'The sketch failed to upload.']);
                }
            } catch (\Throwable $_) {
                return redirect()->back()->withInput()->withErrors(['sketch' => 'The sketch failed to upload.']);
            }
        }

        $obs->save();

        // Persist comet-specific detail row when the observation targets a comet
        try {
            $isComet = $obs->target_type === Observation::TYPE_COMET;
            if ($isComet) {
                $cometDetailValues = [
                    'method' => $validated['comet_method'] ?? null,
                    'chart' => $validated['comet_chart'] ?? null,
                    'magnitude' => array_key_exists('comet_magnitude_estimate', $validated) && $validated['comet_magnitude_estimate'] !== '' ? (float) $validated['comet_magnitude_estimate'] : null,
                    'magnitude_uncertain' => !empty($validated['comet_magnitude_uncertain']) ? 1 : 0,
                    'less_magnitude' => !empty($validated['comet_less_magnitude']) ? 1 : 0,
                    'dc' => $validated['comet_dc'] ?? null,
                    'coma' => array_key_exists('comet_coma', $validated) && $validated['comet_coma'] !== '' ? (int) $validated['comet_coma'] : null,
                    'tail' => array_key_exists('comet_tail_length', $validated) && $validated['comet_tail_length'] !== '' ? (int) $validated['comet_tail_length'] : null,
                    'position_angle' => array_key_exists('comet_position_angle', $validated) && $validated['comet_position_angle'] !== '' ? (int) $validated['comet_position_angle'] : null,
                    'magnification' => $magnification,
                ];
                \Illuminate\Support\Facades\DB::table('comet_observation_details')->updateOrInsert(
                    ['observation_id' => $obs->id],
                    $cometDetailValues
                );
            }
        } catch (\Throwable $e) {
            try {
                \Log::warning('Failed to persist comet observation details', ['id' => $obs->id, 'error' => $e->getMessage()]);
            } catch (\Throwable $_) {
            }
        }

        // Persist deepsky-specific detail row when the observation targets a deepsky object
        try {
            $isDeepsky = $obs->target_type === Observation::TYPE_DEEPSKY;
            if ($isDeepsky) {
                $detailValues = [
                    'visibility' => array_key_exists('visibility', $validated) ? ($validated['visibility'] !== '' ? (int) $validated['visibility'] : null) : null,
                    'small_diameter' => array_key_exists('small_diameter', $validated) && $validated['small_diameter'] !== '' ? (float) $validated['small_diameter'] : null,
                    'large_diameter' => array_key_exists('large_diameter', $validated) && $validated['large_diameter'] !== '' ? (float) $validated['large_diameter'] : null,
                    'stellar' => !empty($validated['stellar']) ? 1 : 0,
                    'extended' => !empty($validated['extended']) ? 1 : 0,
                    'resolved' => !empty($validated['resolved']) ? 1 : 0,
                    'mottled' => !empty($validated['mottled']) ? 1 : 0,
                    'cluster_type' => $validated['cluster_type'] ?? null,
                    'unusual_shape' => !empty($validated['unusual_shape']) ? 1 : 0,
                    'partly_unresolved' => !empty($validated['partly_unresolved']) ? 1 : 0,
                    'color_contrasts' => !empty($validated['color_contrasts']) ? 1 : 0,
                    'equal_brightness' => !empty($validated['equal_brightness']) ? 1 : 0,
                    'nice_field' => !empty($validated['nice_field']) ? 1 : 0,
                    'component1' => array_key_exists('component1', $validated) && $validated['component1'] !== '' ? (int) $validated['component1'] : null,
                    'component2' => array_key_exists('component2', $validated) && $validated['component2'] !== '' ? (int) $validated['component2'] : null,
                    'magnification' => $magnification,
                ];

                // Use updateOrInsert to create or update the details row keyed by observation_id
                \Illuminate\Support\Facades\DB::table('deepsky_observation_details')->updateOrInsert(
                    ['observation_id' => $obs->id],
                    $detailValues
                );
            }
        } catch (\Throwable $e) {
            try {
                \Log::warning('Failed to persist deepsky observation details', ['id' => $obs->id, 'error' => $e->getMessage()]);
            } catch (\Throwable $_) {
            }
        }

        // The detail rows are written without model events, so an edit that
        // only changes them would otherwise leave the cached pages stale.
        Observation::clearResponseCache();

        // Prefer redirecting to the canonical detail page for the newly-created unified observation
        try {
            $detailUrl = $this->buildObservationDetailUrlForUnified((int) $obs->id);
            if ($detailUrl) {
                return redirect($detailUrl)->with('success', __('Observation saved'));
            }
        } catch (\Throwable $_) {
            // fallback below
        }

        return redirect()->route('observations.show', ['observer' => $user->slug])->with('success', __('Observation saved'));
    }

    /**
     * Show the edit form by flashing the current observation values into old() and
     * redirecting to the create page. Only owner or admin may edit.
     */
    public function edit(Request $request, string $observer, string $date, string $object, ?int $index = null)
    {
        $wantedIndex = max(1, (int) ($index ?? 1));
        $observerUser = User::where('slug', $observer)->orWhere('username', $observer)->firstOrFail();

        $unifiedMatches = Observation::where('observer_username', $observerUser->username)
            ->where('observed_on', $date)
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get()
            ->filter(fn($candidate) => $this->slugMatches($object, (string) ($candidate->target_name ?? '')))
            ->values();

        if ($unifiedMatches->isEmpty()) {
            abort(404);
        }

        $selectedUnified = $unifiedMatches->get($wantedIndex - 1);
        if (!$selectedUnified) {
            abort(404);
        }

        $viewer = $request->user();
        if (!$viewer) {
            abort(403);
        }

        $owns = ($viewer->id && $selectedUnified->observer_user_id && $viewer->id == $selectedUnified->observer_user_id) || ($viewer->username && $selectedUnified->observer_username && $viewer->username == $selectedUnified->observer_username);
        $isAdmin = (method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges()) || $viewer->isAdministrator();
        if (!$owns && !$isAdmin) {
            abort(403);
        }

        // Prepare flashed input so create view is prefilled
        // The form shows the date and time in the time zone of the location.
        $prefill = [];
        try {
            $prefill['observed_on'] = $selectedUnified->localDate();
        } catch (\Throwable $_) {
        }
        $localTime = $selectedUnified->localTime();
        if ($localTime !== null) {
            $prefill['observed_time'] = $localTime;
        }
        $prefill['description'] = $selectedUnified->description ?? null;
        $prefill['language'] = $selectedUnified->language ?? null;
        $prefill['instrument_id'] = $selectedUnified->instrument_id ?? null;
        $prefill['eyepiece_id'] = $selectedUnified->eyepiece_id ?? null;
        $prefill['filter_id'] = $selectedUnified->filter_id ?? null;
        $prefill['lens_id'] = $selectedUnified->lens_id ?? null;
        $prefill['location_id'] = $selectedUnified->location_id ?? null;
        $prefill['magnification'] = $selectedUnified->magnification ?? null;
        $prefill['seeing'] = $selectedUnified->seeing ?? null;
        $prefill['transparency'] = $selectedUnified->transparency ?? null;
        // Migrated observations use -1 (SQM) and 0 (limiting magnitude) for
        // "not given"; only prefill values the form accepts.
        $sqm = $selectedUnified->getRawOriginal('sqm');
        if ($sqm !== null && (float) $sqm >= 10 && (float) $sqm <= 23) {
            $prefill['sqm'] = (float) $sqm;
        }
        $limitingMagnitude = $selectedUnified->getRawOriginal('limiting_magnitude');
        if ($limitingMagnitude !== null && (float) $limitingMagnitude > 0 && (float) $limitingMagnitude <= 8) {
            $prefill['limiting_magnitude'] = (float) $limitingMagnitude;
        }
        $prefill['edit_unified_id'] = $selectedUnified->id;

        // The create form is opened by slug; deep-sky objects and comets are
        // stored by name and id, so look up their slug.
        $slug = $selectedUnified->target_key ?? $selectedUnified->target_name ?? null;
        if ($selectedUnified->target_type === Observation::TYPE_DEEPSKY) {
            $slug = DB::table('objects')->where('name', $selectedUnified->target_key)->value('slug') ?? $slug;
        } elseif ($selectedUnified->target_type === Observation::TYPE_COMET) {
            $slug = DB::table('cometobjects')->where('id', (int) $selectedUnified->target_key)->value('slug') ?? $slug;
        }

        // If this unified observation targets a deepsky object, preload detail fields
        try {
            if ($selectedUnified->target_type === Observation::TYPE_DEEPSKY) {
                $details = \Illuminate\Support\Facades\DB::table('deepsky_observation_details')->where('observation_id', $selectedUnified->id)->first();
                if ($details) {
                    $prefill['visibility'] = $details->visibility ?? null;
                    $prefill['small_diameter'] = $details->small_diameter ?? null;
                    $prefill['large_diameter'] = $details->large_diameter ?? null;
                    if (!empty($details->cluster_type))
                        $prefill['cluster_type'] = $details->cluster_type;
                    if ((int) ($details->stellar ?? 0) === 1)
                        $prefill['stellar'] = 1;
                    if ((int) ($details->extended ?? 0) === 1)
                        $prefill['extended'] = 1;
                    if ((int) ($details->resolved ?? 0) === 1)
                        $prefill['resolved'] = 1;
                    if ((int) ($details->mottled ?? 0) === 1)
                        $prefill['mottled'] = 1;
                    if ((int) ($details->unusual_shape ?? 0) === 1)
                        $prefill['unusual_shape'] = 1;
                    if ((int) ($details->partly_unresolved ?? 0) === 1)
                        $prefill['partly_unresolved'] = 1;
                    if ((int) ($details->color_contrasts ?? 0) === 1)
                        $prefill['color_contrasts'] = 1;
                    if ((int) ($details->equal_brightness ?? 0) === 1)
                        $prefill['equal_brightness'] = 1;
                    if ((int) ($details->nice_field ?? 0) === 1)
                        $prefill['nice_field'] = 1;
                    if (!empty($details->component1))
                        $prefill['component1'] = (int) $details->component1;
                    if (!empty($details->component2))
                        $prefill['component2'] = (int) $details->component2;
                }
            }
        } catch (\Throwable $_) {
            // ignore prefill failures
        }

        // Prefill comet-specific detail fields when editing a comet observation
        try {
            if ($selectedUnified->target_type === Observation::TYPE_COMET) {
                $details = \Illuminate\Support\Facades\DB::table('comet_observation_details')
                    ->where('observation_id', $selectedUnified->id)
                    ->first();
                if ($details) {
                    if (!empty($details->method))
                        $prefill['comet_method'] = $details->method;
                    if (!empty($details->chart))
                        $prefill['comet_chart'] = $details->chart;
                    if (isset($details->magnitude) && $details->magnitude !== null)
                        $prefill['comet_magnitude_estimate'] = (float) $details->magnitude;
                    if (!empty($details->magnitude_uncertain))
                        $prefill['comet_magnitude_uncertain'] = 1;
                    if (!empty($details->less_magnitude))
                        $prefill['comet_less_magnitude'] = 1;
                    if ($details->dc !== null && $details->dc !== '')
                        $prefill['comet_dc'] = $details->dc;
                    if (isset($details->coma) && $details->coma !== null)
                        $prefill['comet_coma'] = (int) $details->coma;
                    if (isset($details->tail) && $details->tail !== null)
                        $prefill['comet_tail_length'] = (int) $details->tail;
                    if (isset($details->position_angle) && $details->position_angle !== null)
                        $prefill['comet_position_angle'] = (int) $details->position_angle;
                }
            }
        } catch (\Throwable $_) {
            // ignore prefill failures
        }

        session()->flashInput($prefill);

        return redirect()->route('observation.create', ['slug' => $slug]);
    }

    /**
     * Delete an observation (owner or admin only).
     */
    public function destroy(Request $request, string $observer, string $date, string $object, ?int $index = null)
    {
        $wantedIndex = max(1, (int) ($index ?? 1));
        $observerUser = User::where('slug', $observer)->orWhere('username', $observer)->firstOrFail();

        $unifiedMatches = Observation::where('observer_username', $observerUser->username)
            ->where('observed_on', $date)
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get()
            ->filter(fn($candidate) => $this->slugMatches($object, (string) ($candidate->target_name ?? '')))
            ->values();

        if ($unifiedMatches->isEmpty()) {
            abort(404);
        }

        $selectedUnified = $unifiedMatches->get($wantedIndex - 1);
        if (!$selectedUnified) {
            abort(404);
        }

        $viewer = $request->user();
        if (!$viewer) {
            abort(403);
        }

        $owns = ($viewer->id && $selectedUnified->observer_user_id && $viewer->id == $selectedUnified->observer_user_id) || ($viewer->username && $selectedUnified->observer_username && $viewer->username == $selectedUnified->observer_username);
        $isAdmin = (method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges()) || $viewer->isAdministrator();
        if (!$owns && !$isAdmin) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Remove likes and comments
            ObservationLike::where('observation_type', ObservationLike::TYPE_OBSERVATION)->where('observation_id', $selectedUnified->id)->delete();
            ObservationComment::where('observation_type', ObservationComment::TYPE_OBSERVATION)->where('observation_id', $selectedUnified->id)->delete();

            // Remove sketch associations
            SketchOfTheWeek::where('observation_id', $selectedUnified->id)->delete();
            SketchOfTheMonth::where('observation_id', $selectedUnified->id)->delete();

            // Remove stored drawing file when present
            if (!empty($selectedUnified->drawing_filename)) {
                try {
                    if (Storage::disk('public')->exists($selectedUnified->drawing_filename)) {
                        Storage::disk('public')->delete($selectedUnified->drawing_filename);
                    }
                } catch (\Throwable $_) {
                }
            }

            // Delete the unified observation row via the model so observers fire
            $selectedUnified->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            try {
                \Log::error('Failed to delete observation', ['id' => $selectedUnified->id, 'error' => (string) $e]);
            } catch (\Throwable $_) {
            }
            return redirect()->back()->with('error', __('Failed to delete observation'));
        }

        return redirect()->route('observations.show', ['observer' => $observerUser->slug])->with('success', __('Observation deleted'));
    }

    /**
     * Convert a PHP ini size string (e.g. "2M", "8M") into bytes.
     */
    private function phpSizeToBytes(?string $size): int
    {
        if (empty($size)) {
            return 0;
        }

        $s = trim($size);
        $last = strtolower($s[strlen($s) - 1]);

        if (in_array($last, ['g', 'm', 'k'])) {
            $num = (float) substr($s, 0, -1);
            switch ($last) {
                case 'g':
                    return (int) round($num * 1024 * 1024 * 1024);
                case 'm':
                    return (int) round($num * 1024 * 1024);
                case 'k':
                    return (int) round($num * 1024);
            }
        }

        return (int) $s;
    }
}
