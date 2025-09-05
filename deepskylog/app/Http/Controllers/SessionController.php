<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\Location;
use App\Models\ObservationSession;
use App\Models\ObservationsOld;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as DBFacade;

class SessionController extends Controller
{
    public function show(string $user_slug, string $session_slug)
    {
        $user = User::where('slug', $user_slug)->firstOrFail();

        // observation_sessions uses observerid which maps to User::username in this app
        $session = ObservationSession::where('slug', $session_slug)
            ->where('observerid', $user->username)
            ->firstOrFail();

        // Load location information
        $location = null;
        $image = null; // default: no image unless found
        if (! empty($session->locationid)) {
            $location = Location::find($session->locationid);
            if ($location) {
                if (! empty($location->picture)) {
                    // location pictures are stored in storage; keep existing behavior for location images
                    $image = '/storage/'.asset($location->picture);
                }
            }
        }

        // Try to find a session image in public/images/sessions. We prefer filenames matching the session id
        // or a filename stored in legacy 'picture' column if available during migration.
        $sessionImageDir = public_path('images/sessions');
        if (is_dir($sessionImageDir)) {
            // look for files like {id}.(jpg|jpeg|png|gif)
            $patterns = [
                $sessionImageDir.'/'.$session->id.'.jpg',
                $sessionImageDir.'/'.$session->id.'.jpeg',
                $sessionImageDir.'/'.$session->id.'.png',
                $sessionImageDir.'/'.$session->id.'.gif',
            ];
            foreach ($patterns as $p) {
                if (file_exists($p)) {
                    $image = '/images/sessions/'.basename($p);
                    break;
                }
            }

            // If not found, try files that use the session id as the full basename before the extension
            // e.g. '4.jpg' but NOT '419.jpg'. This avoids accidental prefix matches.
            if (empty($image)) {
                $glob = glob($sessionImageDir.'/'.$session->id.'.*');
                if (! empty($glob)) {
                    $image = '/images/sessions/'.basename($glob[0]);
                }
            }
        }

        // Load other observers (legacy pivot table 'sessionObservers')
        $observerUsernames = $session->otherObservers();
        $observers = [];
        if (! empty($observerUsernames)) {
            foreach ($observerUsernames as $uname) {
                // Skip the primary observer (observerid) if present in the pivot
                if ($uname === $session->observerid) {
                    continue;
                }
                $u = User::where('username', $uname)->first();
                if ($u) {
                    // store the original username so counts can be matched against legacy observerid
                    $observers[] = ['username' => $uname, 'name' => $u->name, 'slug' => $u->slug, 'user' => ['name' => $u->name, 'slug' => $u->slug]];
                } else {
                    // fallback to raw username if no User record exists
                    $observers[] = ['username' => $uname, 'name' => $uname, 'slug' => null, 'user' => null];
                }
            }
        }

        // Total number of observations in this session
        $totalObservations = DBFacade::table('sessionObservations')->where('sessionid', $session->id)->count();

        // Build list of observation IDs for this session so we can filter old observations
        $obsIds = DBFacade::table('sessionObservations')->where('sessionid', $session->id)->pluck('observationid')->toArray();

        // List of objects observed by THIS observer in THIS session (distinct objectname from old observations + comets)
        if (! empty($obsIds)) {
            // Fetch observations (deep-sky and comet) for this session made by the session owner
            $deepObservations = ObservationsOld::whereIn('id', $obsIds)
                ->where('observerid', $session->observerid)
                ->orderBy('id', 'desc')
                ->get();

            $cometObservations = CometObservationsOld::whereIn('id', $obsIds)
                ->where('observerid', $session->observerid)
                ->orderBy('id', 'desc')
                ->get();

            // Merge and sort by id desc so newest observations first
            $allObservations = $deepObservations->merge($cometObservations)->sortByDesc('id')->values();

            // Manual pagination for combined observations
            $perPage = 20;
            $page = (int) request()->get('page', 1);
            $total = $allObservations->count();
            $offset = ($page - 1) * $perPage;
            $itemsForCurrentPage = $allObservations->slice($offset, $perPage)->values();

            $observations = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            // Drawings (deep & comet) by this observer during this session
            $drawingDeep = ObservationsOld::whereIn('id', $obsIds)->where('observerid', $session->observerid)->where('hasDrawing', 1)->orderBy('id', 'desc')->get();
            $drawingComet = CometObservationsOld::whereIn('id', $obsIds)->where('observerid', $session->observerid)->where('hasDrawing', 1)->orderBy('id', 'desc')->get();
            $drawings = $drawingDeep->merge($drawingComet)->sortByDesc('id')->values();

            // Build per-session object name lists (used historically by the page). Keep as arrays so
            // downstream code that paginates names continues to work.
            $objectsDeepSky = $deepObservations->pluck('objectname')->unique()->values()->toArray();
            $objectsComet = $cometObservations->pluck('objectname')->unique()->values()->toArray();
        } else {
            $observations = collect([]);
            $drawings = collect([]);
            $objectsDeepSky = [];
            $objectsComet = [];
        }

        $allObjects = array_values(array_unique(array_merge($objectsDeepSky, $objectsComet)));
        sort($allObjects, SORT_STRING | SORT_FLAG_CASE);

        // Manual pagination for an array of object names (works across connections)
        $perPage = 20;
        $page = (int) request()->get('page', 1);
        $total = count($allObjects);
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = array_slice($allObjects, $offset, $perPage);

        $objectList = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // Compute number of observations each observer did during this session
        $observerStats = [];

        // Build map of observationid -> observerid for old observations (both tables)
        if (! empty($obsIds)) {
            // deep-sky observations
            $deepRows = ObservationsOld::whereIn('id', $obsIds)->select('id', 'observerid')->get();
            // comet observations
            $cometRows = CometObservationsOld::whereIn('id', $obsIds)->select('id', 'observerid')->get();

            $allRows = $deepRows->merge($cometRows);
            // group by observerid and count
            $grouped = $allRows->groupBy('observerid')->map(function ($group) {
                return $group->count();
            });

            // ensure primary observer is included
            $primaryCount = $grouped->get($session->observerid, 0);
            $observerStats[] = ['username' => $session->observerid, 'count' => $primaryCount, 'user' => $user];

            // add other observers - use stored legacy username to match grouped observerid keys
            foreach ($observers as $obs) {
                $legacyUsername = $obs['username'];
                $count = $grouped->get($legacyUsername, 0);
                $observerStats[] = ['username' => $legacyUsername, 'count' => $count, 'user' => $obs['user'] ?? null];
            }
        }

        return view('session.show', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats'));
    }

    /**
     * Show a paginated list of sessions for the current authenticated user.
     */
    public function mine(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        // Sessions use observerid to store the legacy username.
        $query = ObservationSession::where('observerid', $user->username)
            ->where('active', 1)
            ->withObserver()
            ->orderByDesc('enddate')
            ->orderByDesc('begindate');

        $perPage = 12;
        $sessions = $query->paginate($perPage)->withQueryString();

        // Prefetch related locations to avoid N+1 when resolving location pictures
        $collection = $sessions->getCollection();
        $locationIds = $collection->pluck('locationid')->filter()->unique()->values()->all();
        $locations = [];
        if (! empty($locationIds)) {
            $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        $sessionImageDir = public_path('images/sessions');

        // Precompute observation counts for the sessions in this page to avoid N+1 queries
        $sessionIds = $collection->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (! empty($sessionIds)) {
            $obsCounts = DBFacade::table('sessionObservations')
                ->whereIn('sessionid', $sessionIds)
                ->select('sessionid', DBFacade::raw('count(*) as cnt'))
                ->groupBy('sessionid')
                ->pluck('cnt', 'sessionid')
                ->toArray();
        }

        $collection = $collection->transform(function ($session) use ($locations, $sessionImageDir, $obsCounts) {
            $image = null;

            // Prefer images stored under public/images/sessions/{id}.*
            if (is_dir($sessionImageDir)) {
                $patterns = [
                    $sessionImageDir.'/'.$session->id.'.jpg',
                    $sessionImageDir.'/'.$session->id.'.jpeg',
                    $sessionImageDir.'/'.$session->id.'.png',
                    $sessionImageDir.'/'.$session->id.'.gif',
                ];
                foreach ($patterns as $p) {
                    if (file_exists($p)) {
                        $image = '/images/sessions/'.basename($p);
                        break;
                    }
                }

                if (empty($image)) {
                    $glob = glob($sessionImageDir.'/'.$session->id.'.*');
                    if (! empty($glob)) {
                        $image = '/images/sessions/'.basename($glob[0]);
                    }
                }
            }

            // Fallback: session->picture (legacy) if present
            if (empty($image) && ! empty($session->picture)) {
                $image = asset('storage/'.$session->picture);
            }

            // Fallback: location picture if available
            if (empty($image) && ! empty($session->locationid) && isset($locations[$session->locationid])) {
                $loc = $locations[$session->locationid];
                if (! empty($loc->picture)) {
                    $image = asset('storage/'.$loc->picture);
                }
            }

            $session->preview = $image;
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;

            return $session;
        });

        $sessions->setCollection($collection);

        return view('session.my-sessions', compact('sessions'));
    }

    /**
     * Show a paginated list of all sessions (public view).
     */
    public function all(Request $request)
    {
        // Sessions ordered by most recent enddate
        $query = ObservationSession::where('active', 1)
            ->withObserver()
            ->orderByDesc('enddate')
            ->orderByDesc('begindate');

        $perPage = 12;
        $sessions = $query->paginate($perPage)->withQueryString();

        // Prefetch related locations to avoid N+1 when resolving location pictures
        $collection = $sessions->getCollection();
        $locationIds = $collection->pluck('locationid')->filter()->unique()->values()->all();
        $locations = [];
        if (! empty($locationIds)) {
            $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        $sessionImageDir = public_path('images/sessions');

        // Precompute observation counts for the sessions in this page to avoid N+1 queries
        $sessionIds = $collection->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (! empty($sessionIds)) {
            $obsCounts = DBFacade::table('sessionObservations')
                ->whereIn('sessionid', $sessionIds)
                ->select('sessionid', DBFacade::raw('count(*) as cnt'))
                ->groupBy('sessionid')
                ->pluck('cnt', 'sessionid')
                ->toArray();
        }

        $collection = $collection->transform(function ($session) use ($locations, $sessionImageDir, $obsCounts) {
            $image = null;

            // Prefer images stored under public/images/sessions/{id}.*
            if (is_dir($sessionImageDir)) {
                $patterns = [
                    $sessionImageDir.'/'.$session->id.'.jpg',
                    $sessionImageDir.'/'.$session->id.'.jpeg',
                    $sessionImageDir.'/'.$session->id.'.png',
                    $sessionImageDir.'/'.$session->id.'.gif',
                ];
                foreach ($patterns as $p) {
                    if (file_exists($p)) {
                        $image = '/images/sessions/'.basename($p);
                        break;
                    }
                }

                if (empty($image)) {
                    $glob = glob($sessionImageDir.'/'.$session->id.'.*');
                    if (! empty($glob)) {
                        $image = '/images/sessions/'.basename($glob[0]);
                    }
                }
            }

            // Fallback: session->picture (legacy) if present
            if (empty($image) && ! empty($session->picture)) {
                $image = asset('storage/'.$session->picture);
            }

            // Fallback: location picture if available
            if (empty($image) && ! empty($session->locationid) && isset($locations[$session->locationid])) {
                $loc = $locations[$session->locationid];
                if (! empty($loc->picture)) {
                    $image = asset('storage/'.$loc->picture);
                }
            }

            $session->preview = $image;
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;

            return $session;
        });

        $sessions->setCollection($collection);

        return view('session.all-sessions', compact('sessions'));
    }
}
