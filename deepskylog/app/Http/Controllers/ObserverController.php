<?php

namespace App\Http\Controllers;

use App\Charts\CountriesChart;
use App\Charts\ObjectTypesChart;
use App\Charts\ObservationsPerMonthChart;
use App\Charts\ObservationsPerYearChart;
use App\Models\ObservationsOld;
use App\Models\User;
use Illuminate\Http\Request;

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
    public function show(string $slug, ObservationsPerYearChart $chart, ObservationsPerMonthChart $chart2,
        ObjectTypesChart $chart3, CountriesChart $chart4)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $observationsOld = $user->observations();
        $totalObservations = ObservationsOld::getTotalObservations();

        // Get the observations from the last year
        $lastYear = date('Ymd') - 10000;

        $observationsLastYear = ObservationsOld::where('date', '>=', $lastYear)->where('observerid', $user->username)->count();
        $totalObservationsLastYear = ObservationsOld::where('date', '>=', $lastYear)->count();

        $totalNumberOfDrawings = ObservationsOld::where('hasDrawing', 1)->count();
        $totalUniqueObjects = ObservationsOld::getUniqueObjectsObserved();

        // Determine if this user has any observations with likes (deepsky or comet)
        $hasPopularObservations = false;
        $username = $user->username;

        $deepskyIds = ObservationsOld::where('observerid', $username)->pluck('id')->toArray();
        $cometIds = \App\Models\CometObservationsOld::where('observerid', $username)->pluck('id')->toArray();

        if (! empty($deepskyIds) || ! empty($cometIds)) {
            $query = \App\Models\ObservationLike::query();
            $query->where(function ($q) use ($deepskyIds, $cometIds) {
                if (! empty($deepskyIds)) {
                    $q->orWhere(function ($q2) use ($deepskyIds) {
                        $q2->where('observation_type', 'deepsky')->whereIn('observation_id', $deepskyIds);
                    });
                }

                if (! empty($cometIds)) {
                    $q->orWhere(function ($q2) use ($cometIds) {
                        $q2->where('observation_type', 'comet')->whereIn('observation_id', $cometIds);
                    });
                }
            });

            $hasPopularObservations = $query->exists();
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
        if (! empty($locationIds)) {
            $locations = \App\Models\Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        // Precompute observation counts for these sessions
        $sessionIds = $sessions->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (! empty($sessionIds)) {
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
                $glob = glob($storageSessionDir.'/'.$session->id.'.*');
                if (! empty($glob)) {
                    $image = asset('storage/photos/sessions/'.basename($glob[0]));
                }
            }

            // public/images/sessions
            if (empty($image)) {
                if (is_dir($sessionImageDir)) {
                    $glob = glob($sessionImageDir.'/'.$session->id.'.*');
                    if (! empty($glob)) {
                        $image = '/images/sessions/'.basename($glob[0]);
                    }
                }
            }

            // legacy session->picture
            if (empty($image) && ! empty($session->picture)) {
                $image = asset('storage/'.$session->picture);
            }

            // location picture fallback
            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            if (empty($image) && $loc && ! empty($loc->picture)) {
                $image = asset('storage/'.$loc->picture);
            }

            $session->preview = $image;
            $session->preview_text = \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;
            $session->location_name = $loc ? ($loc->name ?? null) : null;

            return $session;
        });

        return view('observers.show', [
            'user' => $user,
            // sessions prepared above
            'sessions' => $sessions,
            'observations' => $observationsOld,
            'totalObservations' => $totalObservations,
            'observationsLastYear' => $observationsLastYear,
            'totalObservationsLastYear' => $totalObservationsLastYear,
            'totalNumberOfDrawings' => $totalNumberOfDrawings,
            'totalUniqueObjects' => $totalUniqueObjects,
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
