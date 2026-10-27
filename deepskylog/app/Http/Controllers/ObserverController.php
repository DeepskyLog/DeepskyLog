<?php

namespace App\Http\Controllers;

use App\Charts\CountriesChart;
use App\Charts\ObjectTypesChart;
use App\Charts\ObservationsPerMonthChart;
use App\Charts\ObservationsPerYearChart;
use App\Jobs\ComputeObserverStats;
use App\Models\Country;
use App\Models\Observation;
use App\Models\ObservingChallenge;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObjectSources;
use App\Services\ObserverCountries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ObserverController extends Controller
{
    /**
     * The ranking of /observers groups all observations; it and the counts of
     * the catalogs are fresh for an hour, and for a day after that the old
     * values are shown while they are computed again after the response is
     * sent (Cache::flexible()). The counts of observing lists are not cached:
     * their owners expect to see an edit at once.
     */
    private const RANKING_CACHE_SECONDS = [3600, 86400];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $sourceOptions = ObjectSources::options($request->user());
        $selectedSource = ObjectSources::resolve((string) $request->query('source', ''), $request->user(), $sourceOptions);
        $currentSort = $this->resolveObserverSort((string) $request->query('sort', 'rank'));
        $currentDirection = $this->resolveObserverSortDirection((string) $request->query('direction', 'asc'));
        $observerSearch = trim((string) $request->query('observer', ''));
        $countryOptions = ObserverCountries::options();
        $selectedCountry = strtoupper(trim((string) $request->query('country', '')));
        $selectedCountry = array_key_exists($selectedCountry, $countryOptions) ? $selectedCountry : null;
        $sourceObjectTotal = $this->sourceObjectTotal($selectedSource);
        $observerRows = collect(Cache::flexible('observers:ranking:country', self::RANKING_CACHE_SECONDS, fn() => $this->observerRankingRows()));

        // A country ranks its own observers: before ranking, so that the
        // ranks are the national ones.
        if ($selectedCountry !== null) {
            $observerRows = $observerRows->filter(fn($row) => strtoupper((string) ($row->country ?? '')) === $selectedCountry);
        }

        $sourceCounts = $this->sourceObserverCounts($selectedSource);

        // Observers with the same number of observations share a rank, as in User::getRank().
        $rank = 0;
        $previousCount = null;
        $observerRows = $observerRows
            ->values()
            ->map(function ($row, int $index) use ($sourceCounts, $sourceObjectTotal, &$rank, &$previousCount) {
                $username = (string) $row->username;
                $selectedSourceCount = (int) ($sourceCounts[$username] ?? 0);
                if ((int) $row->observations_count !== $previousCount) {
                    $rank = $index + 1;
                    $previousCount = (int) $row->observations_count;
                }

                return [
                    'rank' => $rank,
                    'name' => (string) $row->name,
                    'username' => $username,
                    'slug' => (string) ($row->slug ?: $username),
                    'observations_count' => (int) $row->observations_count,
                    'drawings_count' => (int) $row->drawings_count,
                    'observations_last_year' => (int) $row->observations_last_year,
                    'drawings_last_year' => (int) $row->drawings_last_year,
                    'different_objects_count' => (int) $row->different_objects_count,
                    'selected_source_count' => $selectedSourceCount,
                    'selected_source_percentage' => $sourceObjectTotal > 0
                        ? round(($selectedSourceCount / $sourceObjectTotal) * 100, 1)
                        : 0.0,
                ];
            });

        // Search after ranking, so that an observer keeps their rank among all observers.
        if ($observerSearch !== '') {
            $observerRows = $observerRows
                ->filter(fn(array $row) => Str::contains($row['name'], $observerSearch, ignoreCase: true))
                ->values();
        }

        $observerRows = $this->sortObserverRows($observerRows, $currentSort, $currentDirection);
        $observerRows = $this->paginateObserverRows($observerRows, 25, $request);

        return view('observers.index', [
            'observerRows' => $observerRows,
            'selectedSource' => $selectedSource,
            'sourceOptions' => $sourceOptions,
            'sourceColumnLabel' => $this->sourceColumnLabel($selectedSource),
            'currentSort' => $currentSort,
            'currentDirection' => $currentDirection,
            'observerSearch' => $observerSearch,
            'countryOptions' => $countryOptions,
            'selectedCountry' => $selectedCountry,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(
        string $slug,
        ObservationsPerYearChart $chart,
        ObservationsPerMonthChart $chart2,
        ObjectTypesChart $chart3,
        CountriesChart $chart4
    ) {
        $user = User::where('slug', $slug)
            ->withCount(['locations', 'instruments', 'eyepieces', 'filters', 'lenses'])
            ->firstOrFail();

        $cacheKey = 'observer_stats:' . $user->id;
        $inProgressKey = 'observer_stats_in_progress:' . $user->id;
        $stats = Cache::get($cacheKey);

        // Compute the statistics when the cache is cold: in the request for a
        // logged-in observer, on the queue for guests (crawlers would otherwise
        // run the counts for every observer). The job clears the in-progress
        // flag when it is done; until then it is not queued again.
        if (!is_array($stats)) {
            if (Cache::add($inProgressKey, true, 600)) {
                try {
                    if (request()->user()) {
                        ComputeObserverStats::dispatchSync($user->id);
                    } else {
                        ComputeObserverStats::dispatch($user->id);
                    }
                } catch (\Throwable $e) {
                    report($e);
                    Cache::forget($inProgressKey);
                }
            }
            $stats = Cache::get($cacheKey);
        }

        // A page without the statistics must not be served from the response
        // cache after they are computed.
        if (!is_array($stats)) {
            request()->attributes->set('responsecache.doNotCache', true);
        }

        $observationsOld = $user->observations();
        $username = $user->username;

        if (is_array($stats)) {
            // ── Fast path: all values come from cache ──────────────────────
            $allYourObservations = $stats['allYourObservations'] ?? 0;
            $allTotalObservations = $stats['allTotalObservations'] ?? 0;
            $allYourObservationsLastYear = $stats['allYourObservationsLastYear'] ?? 0;
            $allTotalObservationsLastYear = $stats['allTotalObservationsLastYear'] ?? 0;
            $allYourDrawings = $stats['allYourDrawings'] ?? 0;
            $allTotalDrawings = $stats['allTotalDrawings'] ?? 0;
            $allYourDrawingsLastYear = $stats['allYourDrawingsLastYear'] ?? 0;
            $allTotalDrawingsLastYear = $stats['allTotalDrawingsLastYear'] ?? 0;
            $allYourUnique = $stats['allYourUnique'] ?? 0;
            $allTotalUnique = $stats['allTotalUnique'] ?? 0;

            $deepskyYourObservations = $stats['deepskyYourObservations'] ?? 0;
            $deepskyTotalObservations = $stats['deepskyTotalObservations'] ?? 0;
            $deepskyYourObservationsLastYear = $stats['deepskyYourObservationsLastYear'] ?? 0;
            $deepskyTotalObservationsLastYear = $stats['deepskyTotalObservationsLastYear'] ?? 0;
            $deepskyYourDrawings = $stats['deepskyYourDrawings'] ?? 0;
            $deepskyTotalDrawings = $stats['deepskyTotalDrawings'] ?? 0;
            $deepskyYourDrawingsLastYear = $stats['deepskyYourDrawingsLastYear'] ?? 0;
            $deepskyTotalDrawingsLastYear = $stats['deepskyTotalDrawingsLastYear'] ?? 0;
            $deepskyYourUnique = $stats['deepskyYourUnique'] ?? 0;
            $deepskyTotalUnique = $stats['deepskyTotalUnique'] ?? 0;

            // Legacy view-compat aliases used in older view sections
            $totalObservations = $deepskyTotalObservations;
            $observationsLastYear = $deepskyYourObservationsLastYear;
            $totalObservationsLastYear = $deepskyTotalObservationsLastYear;
            $totalNumberOfDrawings = $deepskyTotalDrawings;
            $totalUniqueObjects = $deepskyTotalUnique;

            $cometObservations = $stats['cometYourObservations'] ?? 0;
            $totalCometObservations = $stats['cometTotalObservations'] ?? 0;
            $cometObservationsLastYear = $stats['cometYourObservationsLastYear'] ?? 0;
            $totalCometObservationsLastYear = $stats['cometTotalObservationsLastYear'] ?? 0;
            $cometDrawings = $stats['cometYourDrawings'] ?? 0;
            $totalCometDrawings = $stats['cometTotalDrawings'] ?? 0;
            $cometYourDrawingsLastYear = $stats['cometYourDrawingsLastYear'] ?? 0;
            $cometTotalDrawingsLastYear = $stats['cometTotalDrawingsLastYear'] ?? 0;
            $cometYourUnique = $stats['cometYourUnique'] ?? 0;
            $cometTotalUnique = $stats['cometTotalUnique'] ?? 0;

            $doubleYourObservations = $stats['doubleYourObservations'] ?? 0;
            $doubleTotalObservations = $stats['doubleTotalObservations'] ?? 0;
            $doubleYourObservationsLastYear = $stats['doubleYourObservationsLastYear'] ?? 0;
            $doubleTotalObservationsLastYear = $stats['doubleTotalObservationsLastYear'] ?? 0;
            $doubleYourDrawings = $stats['doubleYourDrawings'] ?? 0;
            $doubleTotalDrawings = $stats['doubleTotalDrawings'] ?? 0;
            $doubleYourDrawingsLastYear = $stats['doubleYourDrawingsLastYear'] ?? 0;
            $doubleTotalDrawingsLastYear = $stats['doubleTotalDrawingsLastYear'] ?? 0;
            $doubleYourUnique = $stats['doubleYourUnique'] ?? 0;
            $doubleTotalUnique = $stats['doubleTotalUnique'] ?? 0;

            $planetsYourObservations = $stats['planetsYourObservations'] ?? 0;
            $planetsTotalObservations = $stats['planetsTotalObservations'] ?? 0;
            $planetsYourObservationsLastYear = $stats['planetsYourObservationsLastYear'] ?? 0;
            $planetsTotalObservationsLastYear = $stats['planetsTotalObservationsLastYear'] ?? 0;
            $planetsYourDrawings = $stats['planetsYourDrawings'] ?? 0;
            $planetsTotalDrawings = $stats['planetsTotalDrawings'] ?? 0;
            $planetsYourDrawingsLastYear = $stats['planetsYourDrawingsLastYear'] ?? 0;
            $planetsTotalDrawingsLastYear = $stats['planetsTotalDrawingsLastYear'] ?? 0;
            $planetsYourUnique = $stats['planetsYourUnique'] ?? 0;
            $planetsTotalUnique = $stats['planetsTotalUnique'] ?? 0;

            $sunYourObservations = $stats['sunYourObservations'] ?? 0;
            $sunTotalObservations = $stats['sunTotalObservations'] ?? 0;
            $sunYourObservationsLastYear = $stats['sunYourObservationsLastYear'] ?? 0;
            $sunTotalObservationsLastYear = $stats['sunTotalObservationsLastYear'] ?? 0;
            $sunYourDrawings = $stats['sunYourDrawings'] ?? 0;
            $sunTotalDrawings = $stats['sunTotalDrawings'] ?? 0;
            $sunYourDrawingsLastYear = $stats['sunYourDrawingsLastYear'] ?? 0;
            $sunTotalDrawingsLastYear = $stats['sunTotalDrawingsLastYear'] ?? 0;
            $sunYourUnique = $stats['sunYourUnique'] ?? 0;
            $sunTotalUnique = $stats['sunTotalUnique'] ?? 0;

            $moonYourObservations = $stats['moonYourObservations'] ?? 0;
            $moonTotalObservations = $stats['moonTotalObservations'] ?? 0;
            $moonYourObservationsLastYear = $stats['moonYourObservationsLastYear'] ?? 0;
            $moonTotalObservationsLastYear = $stats['moonTotalObservationsLastYear'] ?? 0;
            $moonYourDrawings = $stats['moonYourDrawings'] ?? 0;
            $moonTotalDrawings = $stats['moonTotalDrawings'] ?? 0;
            $moonYourDrawingsLastYear = $stats['moonYourDrawingsLastYear'] ?? 0;
            $moonTotalDrawingsLastYear = $stats['moonTotalDrawingsLastYear'] ?? 0;
            $moonYourUnique = $stats['moonYourUnique'] ?? 0;
            $moonTotalUnique = $stats['moonTotalUnique'] ?? 0;

            $hasPopularObservations = $stats['hasPopularObservations'] ?? false;
        } else {
            // ── Slow path: cache cold, use fast queries (no per-year loop, no ID pluck) ──
            $totalObservations = (int) Observation::where('target_type', 'deepsky')->count();
            $observationsLastYear = (int) Observation::where('target_type', 'deepsky')
                ->where('observed_on', '>=', now()->subYear())
                ->where('observer_user_id', $user->id)->count();
            $totalObservationsLastYear = (int) Observation::where('target_type', 'deepsky')
                ->where('observed_on', '>=', now()->subYear())->count();
            $totalNumberOfDrawings = (int) Observation::where('target_type', 'deepsky')
                ->where('has_drawing', 1)->count();
            $totalUniqueObjects = Observation::getUniqueObjectsObserved('deepsky');

            $cometObservations = (int) Observation::where('target_type', 'comet')
                ->where('observer_user_id', $user->id)->count();
            $totalCometObservations = (int) Observation::where('target_type', 'comet')->count();
            $cometObservationsLastYear = $user->getCometObservationsLastYear();
            $totalCometObservationsLastYear = Observation::getTotalObservationsLastYear('comet');
            $cometDrawings = (int) Observation::where('target_type', 'comet')
                ->where('observer_user_id', $user->id)->where('has_drawing', 1)->count();
            $totalCometDrawings = (int) Observation::where('target_type', 'comet')
                ->where('has_drawing', 1)->count();
            $cometYourDrawingsLastYear = $user->getCometDrawingsLastYear();
            $cometTotalDrawingsLastYear = Observation::getTotalDrawingsLastYear('comet');
            $cometYourUnique = $user->getUniqueCometObservations();
            $cometTotalUnique = Observation::getUniqueObjectsObserved('comet');

            // Avoid loading thousands of IDs into PHP — use a JOIN for the likes check
            $hasPopularObservations = DB::table('observation_likes as ol')
                ->join('observations as o', 'o.id', '=', 'ol.observation_id')
                ->where(function ($q) use ($user) {
                    $q->where('o.observer_user_id', $user->id)
                        ->orWhere('o.observer_username', $user->username);
                })
                ->where('ol.observation_type', \App\Models\ObservationLike::TYPE_OBSERVATION)
                ->exists();

            // Category stats will show as zeroes until cache is warm
            $allYourObservations = $allTotalObservations = 0;
            $allYourObservationsLastYear = $allTotalObservationsLastYear = 0;
            $allYourDrawings = $allTotalDrawings = 0;
            $allYourDrawingsLastYear = $allTotalDrawingsLastYear = 0;
            $allYourUnique = $allTotalUnique = 0;

            $deepskyYourObservations = $deepskyTotalObservations = 0;
            $deepskyYourObservationsLastYear = $deepskyTotalObservationsLastYear = 0;
            $deepskyYourDrawings = $deepskyTotalDrawings = 0;
            $deepskyYourDrawingsLastYear = $deepskyTotalDrawingsLastYear = 0;
            $deepskyYourUnique = $deepskyTotalUnique = 0;

            $doubleYourObservations = $doubleTotalObservations = 0;
            $doubleYourObservationsLastYear = $doubleTotalObservationsLastYear = 0;
            $doubleYourDrawings = $doubleTotalDrawings = 0;
            $doubleYourDrawingsLastYear = $doubleTotalDrawingsLastYear = 0;
            $doubleYourUnique = $doubleTotalUnique = 0;

            $planetsYourObservations = $planetsTotalObservations = 0;
            $planetsYourObservationsLastYear = $planetsTotalObservationsLastYear = 0;
            $planetsYourDrawings = $planetsTotalDrawings = 0;
            $planetsYourDrawingsLastYear = $planetsTotalDrawingsLastYear = 0;
            $planetsYourUnique = $planetsTotalUnique = 0;

            $sunYourObservations = $sunTotalObservations = 0;
            $sunYourObservationsLastYear = $sunTotalObservationsLastYear = 0;
            $sunYourDrawings = $sunTotalDrawings = 0;
            $sunYourDrawingsLastYear = $sunTotalDrawingsLastYear = 0;
            $sunYourUnique = $sunTotalUnique = 0;

            $moonYourObservations = $moonTotalObservations = 0;
            $moonYourObservationsLastYear = $moonTotalObservationsLastYear = 0;
            $moonYourDrawings = $moonTotalDrawings = 0;
            $moonYourDrawingsLastYear = $moonTotalDrawingsLastYear = 0;
            $moonYourUnique = $moonTotalUnique = 0;
        }

        // Load up to 3 most recent active sessions for this observer
        $sessionsQuery = \App\Models\ObservationSession::where('observerid', $user->username)
            ->where('active', 1)
            ->withObserver()
            ->orderByDesc('enddate')
            ->orderByDesc('begindate')
            ->limit(3);

        $sessions = $sessionsQuery->get();
        \App\Models\ObservationSession::preloadOtherObservers($sessions);

        // Prefetch related locations to avoid N+1
        $locationIds = $sessions->pluck('locationid')->filter()->unique()->values()->all();
        $locations = [];
        if (!empty($locationIds)) {
            $locations = \App\Models\Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        // Precompute observation counts for these sessions
        $sessionIds = $sessions->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (!empty($sessionIds)) {
            $obsCounts = \Illuminate\Support\Facades\DB::table('sessionObservations')
                ->whereIn('sessionid', $sessionIds)
                ->select('sessionid', \Illuminate\Support\Facades\DB::raw('count(*) as cnt'))
                ->groupBy('sessionid')
                ->pluck('cnt', 'sessionid')
                ->toArray();
        }

        $sessionImageDir = public_path('images/sessions');

        // Prepare preview, preview_text and location_name for each session
        $sessions = $sessions->map(function ($session) use ($locations, $sessionImageDir, $obsCounts) {
            $image = null;

            // Prefer storage/photos/sessions
            $storageSessionDir = storage_path('app/public/photos/sessions');
            if (is_dir($storageSessionDir)) {
                $glob = glob($storageSessionDir . '/' . $session->id . '.*');
                if (!empty($glob)) {
                    $image = asset('storage/photos/sessions/' . basename($glob[0]));
                }
            }

            // public/images/sessions
            if (empty($image)) {
                if (is_dir($sessionImageDir)) {
                    $glob = glob($sessionImageDir . '/' . $session->id . '.*');
                    if (!empty($glob)) {
                        $image = '/images/sessions/' . basename($glob[0]);
                    }
                }
            }

            // legacy session->picture
            if (empty($image) && !empty($session->picture)) {
                $image = asset('storage/' . $session->picture);
            }

            // location picture fallback
            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            if (empty($image) && $loc && !empty($loc->picture)) {
                $image = asset('storage/' . $loc->picture);
            }

            $session->preview = $image;
            $session->preview_text = \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;
            $session->location_name = $loc ? ($loc->name ?? null) : null;

            return $session;
        });

        // Following (issue #966): the follow button for other observers, the
        // lists of followers and followed observers on one's own page.
        $viewer = request()->user();
        $ownPage = $viewer && (int) $viewer->id === (int) $user->id;
        $follow = [
            'followersCount' => $user->followers()->count(),
            'isFollowing' => $viewer && !$ownPage && $viewer->isFollowing($user),
            'canFollow' => $viewer && $viewer->canFollow($user),
            'followerList' => $ownPage ? $user->followers()->orderBy('name')->get(['users.id', 'name', 'slug']) : null,
            'followingList' => $ownPage ? $user->following()->orderBy('name')->get(['users.id', 'name', 'slug']) : null,
        ];

        return view('observers.show', $follow + [
            'user' => $user,
            'sessions' => $sessions,
            'observations' => $observationsOld,
            // Legacy aliases
            'totalObservations' => $totalObservations,
            'observationsLastYear' => $observationsLastYear,
            'totalObservationsLastYear' => $totalObservationsLastYear,
            'totalNumberOfDrawings' => $totalNumberOfDrawings,
            'totalUniqueObjects' => $totalUniqueObjects,
            // All-categories totals
            'allYourObservations' => $allYourObservations,
            'allTotalObservations' => $allTotalObservations,
            'allYourObservationsLastYear' => $allYourObservationsLastYear,
            'allTotalObservationsLastYear' => $allTotalObservationsLastYear,
            'allYourDrawings' => $allYourDrawings,
            'allTotalDrawings' => $allTotalDrawings,
            'allYourDrawingsLastYear' => $allYourDrawingsLastYear,
            'allTotalDrawingsLastYear' => $allTotalDrawingsLastYear,
            'allYourUnique' => $allYourUnique,
            'allTotalUnique' => $allTotalUnique,
            // Deepsky
            'deepskyYourObservations' => $deepskyYourObservations,
            'deepskyTotalObservations' => $deepskyTotalObservations,
            'deepskyYourObservationsLastYear' => $deepskyYourObservationsLastYear,
            'deepskyTotalObservationsLastYear' => $deepskyTotalObservationsLastYear,
            'deepskyYourDrawings' => $deepskyYourDrawings,
            'deepskyTotalDrawings' => $deepskyTotalDrawings,
            'deepskyYourDrawingsLastYear' => $deepskyYourDrawingsLastYear,
            'deepskyTotalDrawingsLastYear' => $deepskyTotalDrawingsLastYear,
            'deepskyYourUnique' => $deepskyYourUnique,
            'deepskyTotalUnique' => $deepskyTotalUnique,
            // Comets
            'cometObservations' => $cometObservations,
            'totalCometObservations' => $totalCometObservations,
            'cometObservationsLastYear' => $cometObservationsLastYear,
            'totalCometObservationsLastYear' => $totalCometObservationsLastYear,
            'cometDrawings' => $cometDrawings,
            'totalCometDrawings' => $totalCometDrawings,
            'cometYourDrawingsLastYear' => $cometYourDrawingsLastYear,
            'cometTotalDrawingsLastYear' => $cometTotalDrawingsLastYear,
            'cometYourUnique' => $cometYourUnique,
            'cometTotalUnique' => $cometTotalUnique,
            // Double stars
            'doubleYourObservations' => $doubleYourObservations,
            'doubleTotalObservations' => $doubleTotalObservations,
            'doubleYourObservationsLastYear' => $doubleYourObservationsLastYear,
            'doubleTotalObservationsLastYear' => $doubleTotalObservationsLastYear,
            'doubleYourDrawings' => $doubleYourDrawings,
            'doubleTotalDrawings' => $doubleTotalDrawings,
            'doubleYourDrawingsLastYear' => $doubleYourDrawingsLastYear,
            'doubleTotalDrawingsLastYear' => $doubleTotalDrawingsLastYear,
            'doubleYourUnique' => $doubleYourUnique,
            'doubleTotalUnique' => $doubleTotalUnique,
            // Planets
            'planetsYourObservations' => $planetsYourObservations,
            'planetsTotalObservations' => $planetsTotalObservations,
            'planetsYourObservationsLastYear' => $planetsYourObservationsLastYear,
            'planetsTotalObservationsLastYear' => $planetsTotalObservationsLastYear,
            'planetsYourDrawings' => $planetsYourDrawings,
            'planetsTotalDrawings' => $planetsTotalDrawings,
            'planetsYourDrawingsLastYear' => $planetsYourDrawingsLastYear,
            'planetsTotalDrawingsLastYear' => $planetsTotalDrawingsLastYear,
            'planetsYourUnique' => $planetsYourUnique,
            'planetsTotalUnique' => $planetsTotalUnique,
            // Asteroids: metric => [the observer's, all observers'] (0 in
            // statistics cached before the asteroids were added)
            'asteroidCounts' => collect(['Observations', 'ObservationsLastYear', 'Drawings', 'DrawingsLastYear', 'Unique'])
                ->mapWithKeys(fn (string $metric) => [$metric => [
                    (int) ($stats['asteroidsYour'.$metric] ?? 0),
                    (int) ($stats['asteroidsTotal'.$metric] ?? 0),
                ]])->all(),
            // Sun
            'sunYourObservations' => $sunYourObservations,
            'sunTotalObservations' => $sunTotalObservations,
            'sunYourObservationsLastYear' => $sunYourObservationsLastYear,
            'sunTotalObservationsLastYear' => $sunTotalObservationsLastYear,
            'sunYourDrawings' => $sunYourDrawings,
            'sunTotalDrawings' => $sunTotalDrawings,
            'sunYourDrawingsLastYear' => $sunYourDrawingsLastYear,
            'sunTotalDrawingsLastYear' => $sunTotalDrawingsLastYear,
            'sunYourUnique' => $sunYourUnique,
            'sunTotalUnique' => $sunTotalUnique,
            // Moon
            'moonYourObservations' => $moonYourObservations,
            'moonTotalObservations' => $moonTotalObservations,
            'moonYourObservationsLastYear' => $moonYourObservationsLastYear,
            'moonTotalObservationsLastYear' => $moonTotalObservationsLastYear,
            'moonYourDrawings' => $moonYourDrawings,
            'moonTotalDrawings' => $moonTotalDrawings,
            'moonYourDrawingsLastYear' => $moonYourDrawingsLastYear,
            'moonTotalDrawingsLastYear' => $moonTotalDrawingsLastYear,
            'moonYourUnique' => $moonYourUnique,
            'moonTotalUnique' => $moonTotalUnique,
            // Charts
            'observationsPerYearChart' => $chart->build($user),
            'observationsPerMonthChart' => $chart2->build($user),
            'objectTypesChart' => $chart3->build($user),
            'countriesChart' => $chart4->build($user),
            'hasPopularObservations' => $hasPopularObservations,
            'username' => $username,
            'rank' => $user->getRank(),
            'rankedObserverCount' => User::rankedObserverCount(),
            'categoryRanks' => $user->getCategoryRanks(),
            'countryRanks' => $user->getCountryRanks(),
            'countryLabel' => ObserverCountries::label($user->country),
            'observedCountries' => $this->observedCountries($user),
            'catalogCounts' => $this->catalogCounts($user, ['M', 'Caldwell', 'H400', 'H400-II']),
            'challengeTrophies' => ObservingChallenge::trophiesFor($user),
        ]);

    }

    /**
     * The number of countries the observer observed in (from the locations of
     * the observations) and the share of the observations made outside the
     * country of residence, null when that is not set or no observation has
     * a location in a known country.
     *
     * @return array{countries: int, abroad_percentage: ?float}
     */
    private function observedCountries(User $user): array
    {
        $perCountry = [];
        DB::table('observations')
            ->join('locations', 'observations.location_id', '=', 'locations.id')
            ->where('observations.observer_username', $user->username)
            ->groupBy('locations.country')
            ->selectRaw('locations.country, COUNT(*) as count')
            ->get()
            ->each(function ($row) use (&$perCountry) {
                $code = Country::codeFor($row->country);
                if ($code !== null) {
                    $perCountry[$code] = ($perCountry[$code] ?? 0) + (int) $row->count;
                }
            });

        $total = array_sum($perCountry);
        $home = strtoupper((string) $user->country);

        return [
            'countries' => count($perCountry),
            'abroad_percentage' => $total > 0 && $home !== ''
                ? round(100 * ($total - ($perCountry[$home] ?? 0)) / $total, 1)
                : null,
        ];
    }

    /**
     * The number of different objects of each catalog the observer has seen
     * and drawn, in one query.
     *
     * @param  list<string>  $catalogs
     * @return array<string, array{observed: int, drawn: int}>
     */
    private function catalogCounts(User $user, array $catalogs): array
    {
        $rows = DB::table('objectnames')
            ->join('observations', 'objectnames.objectname', '=', 'observations.target_name')
            ->leftJoin('deepsky_observation_details', 'observations.id', '=', 'deepsky_observation_details.observation_id')
            ->where(function ($sub) {
                $sub->whereNull('deepsky_observation_details.visibility')
                    ->orWhere('deepsky_observation_details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN);
            })
            ->where('observations.observer_username', $user->username)
            ->whereIn('objectnames.catalog', $catalogs)
            ->groupBy('objectnames.catalog')
            ->select('objectnames.catalog')
            ->selectRaw('COUNT(DISTINCT objectnames.catindex) as observed')
            ->selectRaw('COUNT(DISTINCT CASE WHEN observations.has_drawing = 1 THEN objectnames.catindex END) as drawn')
            ->get()
            ->keyBy(fn($row) => Str::lower((string) $row->catalog));

        $counts = [];
        foreach ($catalogs as $catalog) {
            $row = $rows->get(Str::lower($catalog));
            $counts[$catalog] = ['observed' => (int) ($row->observed ?? 0), 'drawn' => (int) ($row->drawn ?? 0)];
        }

        return $counts;
    }

    public function catalog(Request $request, string $observer): View
    {
        $user = User::where('slug', $observer)->orWhere('username', $observer)->firstOrFail();
        $grid = $this->catalogGrid($request, $user);

        return view('observers.catalog', [
            'user' => $user,
            ...$grid,
            'observedCount' => $grid['sourceObjects']->where('observed', true)->count(),
        ]);
    }

    /**
     * Make an observing list of the logged-in observer with the objects of
     * the catalog page that are red: not seen yet by the observer of the page,
     * or not drawn yet in the drawing mode.
     */
    public function createCatalogList(Request $request, string $observer): RedirectResponse
    {
        $this->authorize('create', ObservingList::class);

        $user = User::where('slug', $observer)->orWhere('username', $observer)->firstOrFail();
        $grid = $this->catalogGrid($request, $user);
        $viewer = $request->user();

        $objectNames = $grid['sourceObjects']
            ->reject(fn(array $object) => $object['observed'])
            ->pluck('object_name')
            ->filter(fn($name) => $name !== '')
            ->unique()
            ->values();

        $back = route('observer.catalog', [
            'observer' => $user->slug ?: $user->username,
            'source' => $grid['selectedSource']['key'],
            'include_subobjects' => $grid['includeSubobjects'] ? 1 : null,
            'mode' => $grid['mode'] === 'drawn' ? 'drawn' : null,
        ]);

        if ($objectNames->isEmpty()) {
            return redirect($back)->with('error', $grid['mode'] === 'drawn'
                ? __('All objects of this catalog or list are drawn already.')
                : __('All objects of this catalog or list are seen already.'));
        }

        $list = ObservingList::createWithObjects($viewer, $this->catalogListName($grid, $user, $viewer), $objectNames);

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list created with :count object(s).', ['count' => $objectNames->count()]));
    }

    /**
     * The objects of the selected catalog or list with their status for the
     * observer: seen, or drawn when the mode is "drawn".
     *
     * @return array{selectedSource: array, sourceOptions: Collection, sourceObjects: Collection, includeSubobjects: bool, mode: string}
     */
    private function catalogGrid(Request $request, User $user): array
    {
        $sourceOptions = ObjectSources::options($request->user());
        $selectedSource = ObjectSources::resolve((string) $request->input('source', ''), $request->user(), $sourceOptions);
        $includeSubobjects = $request->boolean('include_subobjects');
        $mode = $request->input('mode') === 'drawn' ? 'drawn' : 'observed';
        $sourceObjects = ObjectSources::objects($selectedSource);
        $observedLookup = $this->observedObjectLookup($user, $sourceObjects, $includeSubobjects, $mode === 'drawn');

        $sourceObjects = $sourceObjects->values()->map(function (array $object, int $index) use ($user, $observedLookup) {
            $slug = $object['slug'] ?: Str::slug((string) ($object['display_name'] ?: $object['object_name']), '-');
            $observed = (bool) ($observedLookup[$object['object_name']] ?? false);

            $object['position'] = $index + 1;
            $object['slug'] = $slug;
            $object['observed'] = $observed;
            $object['target_url'] = $observed
                ? route('observations.user.object', ['observer' => $user->slug ?: $user->username, 'object' => $slug])
                : route('object.show', ['slug' => $slug]);

            return $object;
        });

        return [
            'selectedSource' => $selectedSource,
            'sourceOptions' => $sourceOptions,
            'sourceObjects' => $sourceObjects,
            'includeSubobjects' => $includeSubobjects,
            'mode' => $mode,
        ];
    }

    private function catalogListName(array $grid, User $user, User $viewer): string
    {
        $source = Str::limit((string) $grid['selectedSource']['short_label'], 150, '…');
        $drawn = $grid['mode'] === 'drawn';

        if ($user->is($viewer)) {
            return $drawn
                ? __(':source - not drawn yet', ['source' => $source])
                : __(':source - not seen yet', ['source' => $source]);
        }

        $observer = Str::limit(html_entity_decode((string) $user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 80, '…');

        return $drawn
            ? __(':source - not drawn yet by :observer', ['source' => $source, 'observer' => $observer])
            : __(':source - not seen yet by :observer', ['source' => $source, 'observer' => $observer]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function admin()
    {
        return view('observers.admin');
    }

    /**
     * The observers with their counts, most observations first. The
     * observations are counted per observer before joining the users, and the
     * different objects in a query of their own that skips the objects not
     * seen: counting them in the query with the details joined took a second.
     *
     * @return list<object>
     */
    private function observerRankingRows(): array
    {
        $lastYear = now()->subYear()->toDateString();

        $counts = DB::table('observations')
            ->select('observer_username')
            ->selectRaw('COUNT(*) as observations_count')
            ->selectRaw('SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) as drawings_count')
            ->selectRaw('SUM(CASE WHEN observed_on >= ? THEN 1 ELSE 0 END) as observations_last_year', [$lastYear])
            ->selectRaw('SUM(CASE WHEN has_drawing = 1 AND observed_on >= ? THEN 1 ELSE 0 END) as drawings_last_year', [$lastYear])
            ->groupBy('observer_username');

        // Keyed by user id: joined as a second derived table, the two
        // subqueries took longer than both queries together.
        $differentObjects = DB::table('observations')
            ->join('users', 'users.username', '=', 'observations.observer_username')
            ->whereNotExists(function ($details) {
                $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN);
            })
            ->groupBy('users.id')
            ->selectRaw('users.id, COUNT(DISTINCT ' . Observation::DIFFERENT_OBJECT_SQL . ') as different_objects_count')
            ->pluck('different_objects_count', 'id');

        return DB::table('users')
            ->joinSub($counts, 'counts', 'counts.observer_username', '=', 'users.username')
            ->select('users.id', 'users.name', 'users.username', 'users.slug', 'users.country')
            ->addSelect('counts.observations_count', 'counts.drawings_count', 'counts.observations_last_year', 'counts.drawings_last_year')
            ->orderByDesc('counts.observations_count')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->get()
            ->each(function ($row) use ($differentObjects) {
                $row->different_objects_count = (int) ($differentObjects[$row->id] ?? 0);
            })
            ->all();
    }

    private function sourceColumnLabel(array $source): string
    {
        return __('Observed :source', ['source' => $source['short_label'] ?? $source['label'] ?? __('catalog')]);
    }

    private function resolveObserverSort(string $sort): string
    {
        $allowedSorts = [
            'rank',
            'name',
            'observations_count',
            'drawings_count',
            'observations_last_year',
            'drawings_last_year',
            'different_objects_count',
            'selected_source_count',
        ];

        return in_array($sort, $allowedSorts, true) ? $sort : 'rank';
    }

    private function resolveObserverSortDirection(string $direction): string
    {
        return Str::lower($direction) === 'desc' ? 'desc' : 'asc';
    }

    private function sourceObjectTotal(array $source): int
    {
        if (($source['type'] ?? null) === 'list') {
            return (int) DB::table('observing_list_items')
                ->where('observing_list_id', $source['list_id'])
                ->distinct('object_name')
                ->count('object_name');
        }

        $catalog = (string) ($source['catalog'] ?? '');

        return Cache::flexible('observers:catalog_total:' . md5(Str::lower($catalog)), self::RANKING_CACHE_SECONDS, fn() => (int) DB::table('objectnames')
            ->where('catalog', $catalog)
            ->distinct('catindex')
            ->count('catindex'));
    }

    private function sortObserverRows(Collection $rows, string $sort, string $direction): Collection
    {
        $sortedRows = $rows->all();

        usort($sortedRows, function (array $left, array $right) use ($sort, $direction) {
            $leftValue = $left[$sort] ?? null;
            $rightValue = $right[$sort] ?? null;

            if ($sort === 'name') {
                $comparison = strcasecmp((string) $leftValue, (string) $rightValue);
            } else {
                $comparison = $leftValue <=> $rightValue;
            }

            if ($direction === 'desc') {
                $comparison *= -1;
            }

            if ($comparison !== 0) {
                return $comparison;
            }

            $rankComparison = ($left['rank'] ?? 0) <=> ($right['rank'] ?? 0);
            if ($rankComparison !== 0) {
                return $rankComparison;
            }

            return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        });

        return collect($sortedRows)->values();
    }

    private function paginateObserverRows(Collection $rows, int $perPage, Request $request): LengthAwarePaginator
    {
        $currentPage = max(1, (int) $request->query('page', 1));
        $total = $rows->count();
        $slice = $rows->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function sourceObserverCounts(array $source): array
    {
        if (($source['type'] ?? null) === 'list') {
            $objectNames = DB::table('observing_list_items')
                ->where('observing_list_id', $source['list_id'])
                ->pluck('object_name')
                ->filter()
                ->unique()
                ->values();

            if ($objectNames->isEmpty()) {
                return [];
            }

            return DB::table('observations as observations')
                ->join('users', 'users.username', '=', 'observations.observer_username')
                ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
                ->where('observations.target_type', 'deepsky')
                ->where(function ($query) {
                    $query->whereNull('details.visibility')
                        ->orWhere('details.visibility', '!=', 7);
                })
                ->whereIn('observations.target_name', $objectNames->all())
                ->groupBy('users.username')
                ->select('users.username', DB::raw('COUNT(DISTINCT observations.target_name) as observed_count'))
                ->pluck('observed_count', 'users.username')
                ->map(fn($count) => (int) $count)
                ->all();
        }

        $catalog = (string) ($source['catalog'] ?? '');

        return Cache::flexible('observers:catalog_counts:' . md5(Str::lower($catalog)), self::RANKING_CACHE_SECONDS, fn() => DB::table('objectnames as objectnames')
            ->join('observations as observations', 'objectnames.objectname', '=', 'observations.target_name')
            ->join('users', 'users.username', '=', 'observations.observer_username')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.target_type', 'deepsky')
            ->where(function ($query) {
                $query->whereNull('details.visibility')
                    ->orWhere('details.visibility', '!=', 7);
            })
            ->where('objectnames.catalog', $catalog)
            ->groupBy('users.username')
            ->select('users.username', DB::raw('COUNT(DISTINCT objectnames.catindex) as observed_count'))
            ->pluck('observed_count', 'users.username')
            ->map(fn($count) => (int) $count)
            ->all());
    }


    private function observedObjectLookup(User $user, Collection $sourceObjects, bool $includeSubobjects, bool $drawingsOnly = false): array
    {
        $objectNames = $sourceObjects->pluck('object_name')->filter()->unique()->values();

        if ($objectNames->isEmpty()) {
            return [];
        }

        $seenNames = fn(array $names) => DB::table('observations as observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $user->username)
            ->where('observations.target_type', 'deepsky')
            ->where(function ($query) {
                $query->whereNull('details.visibility')
                    ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN);
            })
            ->when($drawingsOnly, fn($query) => $query->where('observations.has_drawing', true))
            ->whereIn('observations.target_name', $names)
            ->pluck('observations.target_name')
            ->mapWithKeys(fn($name) => [(string) $name => true])
            ->all();

        $observedNames = $seenNames($objectNames->all());

        if (!$includeSubobjects) {
            return $observedNames;
        }

        $partOfRelations = DB::table('objectpartof')
            ->whereIn('partofname', $objectNames->all())
            ->get(['partofname', 'objectname'])
            ->groupBy('partofname');

        $partObjectNames = $partOfRelations
            ->flatten(1)
            ->pluck('objectname')
            ->filter()
            ->unique()
            ->values();

        if ($partObjectNames->isEmpty()) {
            return $observedNames;
        }

        $observedPartNames = $seenNames($partObjectNames->all());

        foreach ($partOfRelations as $parentName => $children) {
            if (isset($observedNames[$parentName])) {
                continue;
            }

            foreach ($children as $child) {
                if (isset($observedPartNames[(string) $child->objectname])) {
                    $observedNames[(string) $parentName] = true;
                    break;
                }
            }
        }

        return $observedNames;
    }
}
