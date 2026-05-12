<?php

namespace App\Http\Controllers;

use App\Charts\CountriesChart;
use App\Charts\ObjectTypesChart;
use App\Charts\ObservationsPerMonthChart;
use App\Charts\ObservationsPerYearChart;
use App\Jobs\ComputeObserverStats;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ObserverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $user = User::where('slug', $slug)->firstOrFail();

        $cacheKey = 'observer_stats:' . $user->id;
        $inProgressKey = 'observer_stats_in_progress:' . $user->id;
        $stats = Cache::get($cacheKey);

        // Kick off background precomputation when cache is cold
        if (!is_array($stats)) {
            if (Cache::add($inProgressKey, true, 60)) {
                ComputeObserverStats::dispatch($user->id);
            }
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
            $totalObservations = (int) Observation::where('legacy_source', 'deepsky')->count();
            $observationsLastYear = (int) Observation::where('legacy_source', 'deepsky')
                ->where('observed_on', '>=', now()->subYear())
                ->where('observer_user_id', $user->id)->count();
            $totalObservationsLastYear = (int) Observation::where('legacy_source', 'deepsky')
                ->where('observed_on', '>=', now()->subYear())->count();
            $totalNumberOfDrawings = (int) Observation::where('legacy_source', 'deepsky')
                ->where('has_drawing', 1)->count();
            $totalUniqueObjects = Observation::getUniqueObjectsObserved('deepsky');

            $cometObservations = (int) Observation::where('legacy_source', 'comet')
                ->where('observer_user_id', $user->id)->count();
            $totalCometObservations = (int) Observation::where('legacy_source', 'comet')->count();
            $cometObservationsLastYear = $user->getCometObservationsLastYear();
            $totalCometObservationsLastYear = Observation::getTotalObservationsLastYear('comet');
            $cometDrawings = (int) Observation::where('legacy_source', 'comet')
                ->where('observer_user_id', $user->id)->where('has_drawing', 1)->count();
            $totalCometDrawings = (int) Observation::where('legacy_source', 'comet')
                ->where('has_drawing', 1)->count();
            $cometYourDrawingsLastYear = $user->getCometDrawingsLastYear();
            $cometTotalDrawingsLastYear = Observation::getTotalDrawingsLastYear('comet');
            $cometYourUnique = $user->getUniqueCometObservations();
            $cometTotalUnique = Observation::getUniqueObjectsObserved('comet');

            // Avoid loading thousands of IDs into PHP — use a JOIN for the likes check
            $hasPopularObservations = DB::table('observation_likes as ol')
                ->join('observations as o', function ($join) {
                    $join->on('o.id', '=', 'ol.observation_id')
                        ->on('ol.observation_type', '=', 'o.legacy_source');
                })
                ->where(function ($q) use ($user) {
                    $q->where('o.observer_user_id', $user->id)
                        ->orWhere('o.observer_username', $user->username);
                })
                ->whereIn('ol.observation_type', ['deepsky', 'comet'])
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

        return view('observers.show', [
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
        ]);

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
}
