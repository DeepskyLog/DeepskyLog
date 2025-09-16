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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function show(string $user_slug, string $session_slug)
    {
        $user = User::where('slug', $user_slug)->firstOrFail();

        // observation_sessions uses observerid which maps to User::username in this app
        $session = ObservationSession::where('slug', $session_slug)
            ->where('observerid', $user->username)
            ->firstOrFail();

        // If the session is inactive (draft / active==0) only the owner or an administrator
        // with override permission may view it. For everyone else, behave as if it does not exist.
        if (isset($session->active) && (int) $session->active === 0) {
            $viewer = Auth::user();
            $allowAdmin = config('sessions.allow_admin_override', false);
            $viewerIsOwner = $viewer && ($viewer->username === $session->observerid || ($viewer->slug ?? null) === ($user->slug ?? null));
            $viewerIsAdmin = $viewer && method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges();

            if (! ($viewerIsOwner || ($allowAdmin && $viewerIsAdmin))) {
                abort(404);
            }
        }

        // Load location information
        $location = null;
        $image = null; // default: no image unless found
        if (! empty($session->locationid)) {
            $location = Location::find($session->locationid);
            if ($location && ! empty($location->picture)) {
                $image = asset('storage/'.$location->picture);
            }
        }

        // Resolve image via helper (prefers storage/photos/sessions, then public/images, then legacy storage paths)
        $image = $this->resolveSessionImage($session, $location);

        // Load other observers (legacy pivot table 'sessionObservers')
        $observerUsernames = $session->otherObservers();
        $observers = [];
        if (! empty($observerUsernames)) {
            foreach ($observerUsernames as $uname) {
                if ($uname === $session->observerid) {
                    continue;
                }
                $u = User::where('username', $uname)->first();
                if ($u) {
                    $observers[] = ['username' => $uname, 'name' => $u->name, 'slug' => $u->slug, 'user' => ['name' => $u->name, 'slug' => $u->slug]];
                } else {
                    $observers[] = ['username' => $uname, 'name' => $uname, 'slug' => null, 'user' => null];
                }
            }
        }

        // Total number of observations in this session
        $totalObservations = DBFacade::table('sessionObservations')->where('sessionid', $session->id)->count();

        // Build list of observation IDs for this session so we can filter old observations
        $obsIds = DBFacade::table('sessionObservations')->where('sessionid', $session->id)->pluck('observationid')->toArray();

    // Allow viewing observations for a specific observer via ?observer={username}
    $targetObserver = request()->get('observer', $session->observerid);
    $selectedObserverUser = User::where('username', $targetObserver)->first();
    $selectedObserverName = $selectedObserverUser ? $selectedObserverUser->name : $targetObserver;

        // Fetch observations and drawings
        if (! empty($obsIds)) {
            // Fetch observations for the session regardless of which observer recorded them.
            // Previously this filtered by the session owner, preventing other viewers from seeing observations.
            // Show observations recorded by the requested observer (defaults to session owner)
            $deepObservations = ObservationsOld::whereIn('id', $obsIds)
                ->where('observerid', $targetObserver)
                ->orderBy('id', 'desc')
                ->get();

            $cometObservations = CometObservationsOld::whereIn('id', $obsIds)
                ->where('observerid', $targetObserver)
                ->orderBy('id', 'desc')
                ->get();

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

            // Drawings should also be visible to viewers of the session regardless of who made them.
            // Drawings for the requested observer
            $drawingDeep = ObservationsOld::whereIn('id', $obsIds)->where('observerid', $targetObserver)->where('hasDrawing', 1)->orderBy('id', 'desc')->get();
            $drawingComet = CometObservationsOld::whereIn('id', $obsIds)->where('observerid', $targetObserver)->where('hasDrawing', 1)->orderBy('id', 'desc')->get();
            $allDrawings = $drawingDeep->merge($drawingComet)->sortByDesc('id')->values();

            // Paginate drawings separately from observations. Use a distinct page name to avoid conflicts.
            $drawingsPerPage = 8;
            $drawingsPage = (int) request()->get('drawings_page', 1);
            $drawingsTotal = $allDrawings->count();
            $drawingsOffset = ($drawingsPage - 1) * $drawingsPerPage;
            $drawingsSlice = $allDrawings->slice($drawingsOffset, $drawingsPerPage)->values();

            $drawings = new LengthAwarePaginator(
                $drawingsSlice,
                $drawingsTotal,
                $drawingsPerPage,
                $drawingsPage,
                [
                    'path' => request()->url(),
                    'query' => array_merge(request()->query(), ['drawings_page' => $drawingsPage]),
                    'pageName' => 'drawings_page',
                ]
            );

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

        // Compute number of observations each observer did during this session.
        // Counts are calculated only when there are session observation ids available.
        $observerStats = [];

        $counts = [];
        if (! empty($obsIds)) {
            // Count per observer in deep observations
            $deepCounts = ObservationsOld::whereIn('id', $obsIds)
                ->select('observerid', DBFacade::raw('count(*) as cnt'))
                ->groupBy('observerid')
                ->pluck('cnt', 'observerid')
                ->toArray();

            // Count per observer in comet observations
            $cometCounts = CometObservationsOld::whereIn('id', $obsIds)
                ->select('observerid', DBFacade::raw('count(*) as cnt'))
                ->groupBy('observerid')
                ->pluck('cnt', 'observerid')
                ->toArray();

            // Merge counts
            foreach ($deepCounts as $who => $c) {
                $counts[$who] = ($counts[$who] ?? 0) + (int) $c;
            }
            foreach ($cometCounts as $who => $c) {
                $counts[$who] = ($counts[$who] ?? 0) + (int) $c;
            }
        }

        // Primary observer count (defaults to zero)
        $primaryCount = isset($counts[$session->observerid]) ? (int) $counts[$session->observerid] : 0;
        // Fallback: if primaryCount is zero but there are totalObservations, try direct count
        if ($primaryCount === 0 && $totalObservations > 0 && ! empty($obsIds)) {
            $primaryCount = (int) (
                (int) (ObservationsOld::whereIn('id', $obsIds)->where('observerid', $session->observerid)->count()) +
                (int) (CometObservationsOld::whereIn('id', $obsIds)->where('observerid', $session->observerid)->count())
            );
        }

        // Always include the primary observer (even when there are no observations)
        $observerStats[] = ['username' => $session->observerid, 'count' => $primaryCount, 'user' => $user];

        // Add other observers (counts default to zero when there are no observations)
        foreach ($observers as $obs) {
            $legacyUsername = $obs['username'];
            $count = isset($counts[$legacyUsername]) ? (int) $counts[$legacyUsername] : 0;
            $observerStats[] = ['username' => $legacyUsername, 'count' => $count, 'user' => $obs['user'] ?? null];
        }

        // Prepare translations for session fields (weather, equipment, comments) if user requested
        // Allow forcing translation via query string for testing: ?force_translate=1
        $forceTranslate = request()->boolean('force_translate');
        $forceLang = request()->get('force_lang');
        $shouldTranslate = (Auth::check() && Auth::user()->translate) || $forceTranslate;
        $lang = null;
        if ($shouldTranslate) {
            if (! empty($forceLang)) {
                $lang = $forceLang;
            } elseif (Auth::check()) {
                $lang = Auth::user()->language ?? config('app.locale');
            } else {
                $lang = config('app.locale');
            }
        }

        $rawWeather = html_entity_decode($session->weather ?? __('Unknown'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $weatherTranslated = $rawWeather;
        if ($shouldTranslate && $lang) {
            $cacheKey = 'session_weather:'.$session->id.':'.$lang;
            $weatherTranslated = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($rawWeather, $lang) {
                try {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                    $t = $tr->translate($rawWeather);

                    return $t !== null ? $t : $rawWeather;
                } catch (\Throwable $e) {
                    return $rawWeather;
                }
            });
        }

        $rawEquipment = html_entity_decode($session->equipment ?? __('Unknown'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $equipmentTranslated = $rawEquipment;
        if ($shouldTranslate && $lang) {
            $cacheKey = 'session_equipment:'.$session->id.':'.$lang;
            $equipmentTranslated = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($rawEquipment, $lang) {
                try {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                    $t = $tr->translate($rawEquipment);

                    return $t !== null ? $t : $rawEquipment;
                } catch (\Throwable $e) {
                    return $rawEquipment;
                }
            });
        }

        $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $commentsTranslated = $rawComments;
        if ($shouldTranslate && $lang) {
            $cacheKey = 'session_comments:'.$session->id.':'.$lang;
            $commentsTranslated = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($rawComments, $lang) {
                try {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                    $t = $tr->translate($rawComments);

                    return $t !== null ? $t : $rawComments;
                } catch (\Throwable $e) {
                    return $rawComments;
                }
            });
        }

        // Ensure preview and observation counts are available to views
        $session->preview = $image;
        $session->observation_count = $totalObservations;

        // Attach translated fields to the session instance for the view
        $session->weather_translated = $weatherTranslated;
        $session->equipment_translated = $equipmentTranslated;
        $session->comments_translated = $commentsTranslated;

        // Debug logging to help diagnose missing observations/drawings (only in debug mode)
        try {
            if (config('app.debug')) {
                \Illuminate\Support\Facades\Log::info('session.show debug', [
                    'session_id' => $session->id,
                    'session_obs_ids' => is_array($obsIds) ? count($obsIds) : null,
                    'observations_count' => isset($observations) && is_object($observations) ? $observations->count() : (isset($observations) ? count($observations) : null),
                    'observations_total' => (isset($observations) && is_object($observations) && method_exists($observations, 'total')) ? $observations->total() : null,
                    'drawings_count' => isset($drawings) && is_object($drawings) ? $drawings->count() : (isset($drawings) ? count($drawings) : null),
                    'drawings_total' => (isset($drawings) && is_object($drawings) && method_exists($drawings, 'total')) ? $drawings->total() : null,
                ]);
            }
        } catch (\Throwable $e) {
            // ignore logging failures
        }

    // Provide the selected observer username and display name to the view so it can highlight the active observer in the sidebar
    $selectedObserverUsername = $targetObserver;

    return $this->noCacheResponse(response()->view('session.show', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName')));
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

        // Allow forcing translation via query string for testing: ?force_translate=1
        $forceTranslate = request()->boolean('force_translate');
        $forceLang = request()->get('force_lang');
        $shouldTranslate = (Auth::check() && Auth::user()->translate) || $forceTranslate;
        $lang = null;
        if ($shouldTranslate) {
            if (! empty($forceLang)) {
                $lang = $forceLang;
            } elseif (Auth::check()) {
                $lang = Auth::user()->language ?? config('app.locale');
            } else {
                $lang = config('app.locale');
            }
        }

        $collection = $collection->transform(function ($session) use ($locations, $sessionImageDir, $obsCounts, $shouldTranslate, $lang) {
            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            $session->preview = $this->resolveSessionImage($session, $loc);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;

            // Prepare translated preview text (comments) if needed and cache per session+lang
            $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = $rawComments;
            if ($shouldTranslate && $lang) {
                $cacheKey = 'session_preview:'.$session->id.':'.$lang;
                $translated = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($rawComments, $lang) {
                    try {
                        $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                        $t = $tr->translate($rawComments);

                        return $t !== null ? $t : $rawComments;
                    } catch (\Throwable $e) {
                        return $rawComments;
                    }
                });
            }

            $session->preview_text = Str::limit(strip_tags($translated), 180);

            return $session;
        });

        $sessions->setCollection($collection);

        $user = Auth::user();
        $userSlug = $user ? $user->slug ?? $user->username : null;

        // Fetch inactive (draft) sessions for the current user so they can be shown at the top
        $inactiveSessions = collect();
        $inactiveMore = false;
        if ($user) {
            $allDrafts = ObservationSession::where('observerid', $user->username)
                ->where('active', 0)
                ->with('observer')
                ->orderByDesc('id')
                ->limit(11)
                ->get();

            if ($allDrafts->count() > 10) {
                $inactiveMore = true;
            }

            $inactiveSessions = $allDrafts->slice(0, 10);

            // Attach display names of other observers for each draft
            $inactiveSessions = $inactiveSessions->map(function ($s) {
                $others = $s->otherObservers();
                $names = [];
                if (! empty($others)) {
                    foreach ($others as $uname) {
                        $u = User::where('username', $uname)->first();
                        if ($u) {
                            $names[] = $u->name;
                        } else {
                            $names[] = $uname;
                        }
                    }
                }
                $s->otherObserversDisplay = implode(', ', $names);
                return $s;
            });
        }

        // Reuse the user-facing sessions view but mark it as the owner view when appropriate
        return $this->noCacheResponse(response()->view('session.user-sessions', compact('sessions', 'user', 'userSlug', 'inactiveSessions', 'inactiveMore')));
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

        $shouldTranslate = Auth::check() && Auth::user()->translate;
        $lang = $shouldTranslate ? (Auth::user()->language ?? config('app.locale')) : null;

        $collection = $collection->transform(function ($session) use ($locations, $sessionImageDir, $obsCounts, $shouldTranslate, $lang) {
            $image = null;

            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            $session->preview = $this->resolveSessionImage($session, $loc);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;

            // Prepare translated preview text (comments) for homepage
            $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = $rawComments;
            if ($shouldTranslate && $lang) {
                $cacheKey = 'session_preview:'.$session->id.':'.$lang;
                $translated = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($rawComments, $lang) {
                    try {
                        $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                        $t = $tr->translate($rawComments);

                        return $t !== null ? $t : $rawComments;
                    } catch (\Throwable $e) {
                        return $rawComments;
                    }
                });
            }

            $session->preview_text = Str::limit(strip_tags($translated), 180);

            return $session;
        });

        $sessions->setCollection($collection);

        return $this->noCacheResponse(response()->view('session.all-sessions', compact('sessions')));
    }

    /**
     * Show sessions for a specific user (public view).
     */
    public function user(Request $request, $user)
    {
        // Try to resolve user by slug first, then by username fallback
        $u = User::where('slug', $user)->first();
        if (! $u) {
            $u = User::where('username', $user)->firstOrFail();
        }

        $query = ObservationSession::where('observerid', $u->username)
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

            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            $session->preview = $this->resolveSessionImage($session, $loc);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;

            return $session;
        });

        $sessions->setCollection($collection);

        $userSlug = $u->slug ?? $u->username;

        // Pass the currently authenticated user so the view can detect the owner and show owner-only actions
        $user = Auth::user();

        // Only show inactive (draft) sessions to the page owner or administrators.
        $inactiveSessions = collect();
        $inactiveMore = false;
        $ownerUsername = $u->username ?? ($u->slug ?? null);

        $viewer = Auth::user();
        $viewerIsOwner = $viewer && ($viewer->username === $ownerUsername || ($viewer->slug ?? null) === ($u->slug ?? null));

        // Only show inactive (draft) sessions to the page owner. Do not allow administrator override here.
        if ($ownerUsername && $viewerIsOwner) {
            $allDrafts = ObservationSession::where('observerid', $ownerUsername)
                ->where('active', 0)
                ->with('observer')
                ->orderByDesc('id')
                ->limit(11)
                ->get();

            if ($allDrafts->count() > 10) {
                $inactiveMore = true;
            }

            $inactiveSessions = $allDrafts->slice(0, 10);

            // Attach display names of other observers for each draft
            $inactiveSessions = $inactiveSessions->map(function ($s) {
                $others = $s->otherObservers();
                $names = [];
                if (! empty($others)) {
                    foreach ($others as $uname) {
                        $u = User::where('username', $uname)->first();
                        if ($u) {
                            $names[] = $u->name;
                        } else {
                            $names[] = $uname;
                        }
                    }
                }
                $s->otherObserversDisplay = implode(', ', $names);
                return $s;
            });
        }

        return $this->noCacheResponse(response()->view('session.user-sessions', compact('sessions', 'u', 'userSlug', 'user', 'inactiveSessions', 'inactiveMore')));
    }

    /**
     * Show form to create a new observation session.
     */
    public function create()
    {
        // Only authenticated users can create sessions (route is protected by middleware)
        $users = User::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return $this->noCacheResponse(response()->view('session.create', compact('users', 'locations')));
    }

    /**
     * Store a newly created observation session.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'observer' => 'required|string|exists:users,username',
            'locationid' => 'required|integer|exists:locations,id',
            'begindate' => 'required|date',
            'enddate' => 'required|date',
            'weather' => 'nullable|string',
            'equipment' => 'nullable|string',
            'comments' => 'nullable|string',
            'active' => 'sometimes|boolean',
        ]);

        $session = new ObservationSession;
        $session->name = $validated['name'];
        $session->observerid = $validated['observer'];
        $session->locationid = $validated['locationid'] ?? null;
        // Debug: log incoming raw date values
        try {
            \Illuminate\Support\Facades\Log::info('SessionController.store raw dates', ['begindate_raw' => $request->input('begindate'), 'enddate_raw' => $request->input('enddate')]);
        } catch (\Throwable $e) {
            // ignore
        }

        // Normalize date-only inputs to literal datetimes (no timezone conversion)
        if (! empty($validated['begindate'])) {
            try {
                $raw = (string) $validated['begindate'];
                if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
                    $session->begindate = $m[1].' 00:00:00';
                } else {
                    $session->begindate = $validated['begindate'];
                }
            } catch (\Throwable $e) {
                $session->begindate = $validated['begindate'];
            }
        } else {
            $session->begindate = null;
        }

        if (! empty($validated['enddate'])) {
            try {
                $raw = (string) $validated['enddate'];
                if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $m)) {
                    $session->enddate = $m[1].' 23:59:59';
                } else {
                    $session->enddate = $validated['enddate'];
                }
            } catch (\Throwable $e) {
                $session->enddate = $validated['enddate'];
            }
        } else {
            $session->enddate = null;
        }

        // Validate that enddate is not before begindate
        try {
            if (! empty($session->begindate) && ! empty($session->enddate)) {
                $begin = \Carbon\Carbon::parse($session->begindate);
                $end = \Carbon\Carbon::parse($session->enddate);
                if ($end->lt($begin)) {
                    return redirect()->back()->withInput()->withErrors(['enddate' => __('The end date must be the same as or after the begin date.')]);
                }
            }
        } catch (\Throwable $e) {
            // If parsing fails, allow the save to proceed and let DB constraints handle it if necessary
        }
        $session->weather = $validated['weather'] ?? null;
        $session->equipment = $validated['equipment'] ?? null;
        $session->comments = $validated['comments'] ?? null;
        $session->active = isset($validated['active']) ? (int) $validated['active'] : 1;

        $session->save();

        // After saving the primary session, dispatch job to build per-user copies and sessionObservations
        try {
            $otherObservers = $request->input('otherObservers');
            $otherArray = [];
            if (! empty($otherObservers)) {
                if (is_array($otherObservers)) {
                    $otherArray = $otherObservers;
                } else {
                    $otherArray = array_filter(array_map('trim', explode(',', $otherObservers)));
                }
            }

            $participants = array_values(array_unique(array_filter(array_merge([$session->observerid], $otherArray))));
            \App\Jobs\BuildSessionCopies::dispatch($session->id, $participants);

            // Dispatch invitation messages asynchronously
            try {
                $sender = auth()->check() ? auth()->user()->username : ($session->observerid ?? 'admin');
                $creator = auth()->check() ? auth()->user()->username : null;
                $invitees = $participants;
                if (! empty($creator)) {
                    $invitees = array_values(array_filter($participants, function ($u) use ($creator) {
                        return $u !== $creator;
                    }));
                }

                if (! empty($invitees)) {
                    \App\Jobs\SendSessionInvitations::dispatch($session->id, $invitees, $sender);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        } catch (\Throwable $e) {
            report($e);
        }
        // Redirect to the newly created session page. Find the user by username to get the slug for URL.
        $user = User::where('username', $session->observerid)->first();

        return redirect()->route('session.show', [$user ? $user->slug : $session->observerid, $session->slug ?? $session->id]);
    }

    /**
     * Redirect to create session with the data from an existing session to adapt it.
     * Lightweight approach: redirect to session.create with adapt_from={id}
     */
    public function adapt(Request $request, $sessionId)
    {
        $session = ObservationSession::findOrFail($sessionId);

        // Allow owner or administrators (when feature flag enabled) to adapt the session
        $allowAdmin = config('sessions.allow_admin_override', false);
        if (! auth()->check() || (auth()->user()->username !== $session->observerid && ! ($allowAdmin && method_exists(auth()->user(), 'hasAdministratorPrivileges') && auth()->user()->hasAdministratorPrivileges()))) {
            abort(403);
        }

        // Redirect to create with query parameter referencing the source session id
        return redirect()->route('session.create', ['adapt_from' => $session->id]);
    }

    /**
     * Mark a session as deleted (inactive). Only the owner may do this.
     */
    public function destroy(Request $request, $sessionId)
    {
        $session = ObservationSession::findOrFail($sessionId);

        // Allow owner or administrators (when feature flag enabled) to delete the session
        $allowAdmin = config('sessions.allow_admin_override', false);
        if (! auth()->check() || (auth()->user()->username !== $session->observerid && ! ($allowAdmin && method_exists(auth()->user(), 'hasAdministratorPrivileges') && auth()->user()->hasAdministratorPrivileges()))) {
            abort(403);
        }

        // Hard-delete: remove pivot rows and related data, then delete the session row.
        try {
            // Remove legacy pivot links between sessions and observations
            DBFacade::table('sessionObservations')->where('sessionid', $session->id)->delete();

            // Remove legacy session observers pivot rows
            DBFacade::table('sessionObservers')->where('sessionid', $session->id)->delete();

            // Remove observation likes that reference this session (observation_type = 'session')
            \App\Models\ObservationLike::where('observation_type', 'session')->where('observation_id', $session->id)->delete();

            // Remove any public image files named after the session id
            $sessionImageDir = public_path('images/sessions');
            if (is_dir($sessionImageDir)) {
                $matches = glob($sessionImageDir.'/'.$session->id.'.*');
                if (! empty($matches)) {
                    foreach ($matches as $f) {
                        @unlink($f);
                    }
                }
            }

            // Remove legacy stored picture referenced via storage if present
            if (! empty($session->picture)) {
                // session->picture is usually stored for asset('storage/...') paths, remove from storage/app/public
                $storagePath = storage_path('app/public/'.ltrim($session->picture, '/'));
                if (file_exists($storagePath)) {
                    @unlink($storagePath);
                }
            }

            // Finally delete the session row from DB
            $session->delete();
        } catch (\Throwable $e) {
            // If anything goes wrong, log and return a message but do not reveal internals
            report($e);

            // Redirect the owner to their sessions page (use slug when available)
            $owner = auth()->user();
            $ownerSlug = $owner ? ($owner->slug ?? $owner->username) : ($session->observerid ?? null);

            return redirect()->route('session.user', [$ownerSlug])->with('status', __('Failed to delete session'));
        }

        $owner = auth()->user();
        $ownerSlug = $owner ? ($owner->slug ?? $owner->username) : ($session->observerid ?? null);

        return redirect()->route('session.user', [$ownerSlug])->with('status', __('Session deleted'));
    }

    /**
     * Homepage sessions (mirrors previous route closure) - shows 5 newest active sessions.
     */
    public function homepage(Request $request)
    {
        $query = ObservationSession::where('active', 1)
            ->withObserver()
            ->orderByDesc('enddate')
            ->orderByDesc('begindate');

        $perPage = 6;
        $sessions = $query->paginate($perPage, $columns = ['*'], $pageName = 'sessions')->appends(request()->except('sessions'));

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

        // Allow forcing translation via query string for testing: ?force_translate=1
        $forceTranslate = request()->boolean('force_translate');
        $forceLang = request()->get('force_lang');
        $shouldTranslate = (Auth::check() && Auth::user()->translate) || $forceTranslate;
        $lang = null;
        if ($shouldTranslate) {
            if (! empty($forceLang)) {
                $lang = $forceLang;
            } elseif (Auth::check()) {
                $lang = Auth::user()->language ?? config('app.locale');
            } else {
                $lang = config('app.locale');
            }
        }

        $collection = $collection->transform(function ($session) use ($locations, $sessionImageDir, $obsCounts, $shouldTranslate, $lang) {
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

            // Prepare translated preview text (comments) for homepage
            $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = $rawComments;
            if ($shouldTranslate && $lang) {
                $cacheKey = 'session_preview:'.$session->id.':'.$lang;
                $translated = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($rawComments, $lang) {
                    try {
                        $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                        $t = $tr->translate($rawComments);

                        return $t !== null ? $t : $rawComments;
                    } catch (\Throwable $e) {
                        return $rawComments;
                    }
                });
            }

            $session->preview_text = Str::limit(strip_tags($translated), 180);

            return $session;
        });

        $sessions->setCollection($collection);

        return $this->noCacheResponse(response()->view('welcome', compact('sessions')));
    }

    /**
     * Attach headers to ensure responses are not cached by intermediate proxies or browsers.
     */
    protected function noCacheResponse($response)
    {
        try {
            return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Throwable $e) {
            return $response;
        }
    }

    /**
     * Resolve a session image URL preferring storage/app/public/photos/sessions (served via /storage/photos/sessions)
     * then public/images/sessions, then legacy session->picture and finally the location picture.
     *
     * @param  \App\Models\ObservationSession  $session
     * @param  \App\Models\Location|null  $location
     * @return string|null
     */
    protected function resolveSessionImage($session, $location = null)
    {
        $image = null;

        // 1) storage/app/public/photos/sessions (accessible via /storage/photos/sessions/...)
        $storageSessionDir = storage_path('app/public/photos/sessions');
        if (is_dir($storageSessionDir)) {
            $patterns = [
                $storageSessionDir.'/'.$session->id.'.jpg',
                $storageSessionDir.'/'.$session->id.'.jpeg',
                $storageSessionDir.'/'.$session->id.'.png',
                $storageSessionDir.'/'.$session->id.'.gif',
            ];
            foreach ($patterns as $p) {
                if (file_exists($p)) {
                    $image = asset('storage/photos/sessions/'.basename($p));
                    break;
                }
            }

            if (empty($image)) {
                $glob = glob($storageSessionDir.'/'.$session->id.'.*');
                if (! empty($glob)) {
                    $image = asset('storage/photos/sessions/'.basename($glob[0]));
                }
            }
        }

        // 2) public/images/sessions legacy files
        if (empty($image)) {
            $sessionImageDir = public_path('images/sessions');
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
        }

        // 3) legacy session->picture stored under storage/app/public/... (session->picture is usually a relative path)
        if (empty($image) && ! empty($session->picture)) {
            $image = asset('storage/'.$session->picture);
        }

        // 4) location picture (fallback)
        if (empty($image) && $location && ! empty($location->picture)) {
            $image = asset('storage/'.$location->picture);
        }

        return $image;
    }
}
