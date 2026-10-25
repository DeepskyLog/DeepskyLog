<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\NewsItem;
use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use App\Services\TonightsBest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    /** The number of news items, sessions, observations and sketches on the home page. */
    private const HOMEPAGE_NEWS = 5;

    private const HOMEPAGE_SESSIONS = 3;

    private const HOMEPAGE_OBSERVATIONS = 8;

    private const HOMEPAGE_SKETCHES = 8;

    /** The observations on the home page: deep-sky, comets and the solar system (old 'solarsystem' and normalized 'planet' and 'lunar'). */
    private const HOMEPAGE_TARGET_TYPES = ['deepsky', 'comet', 'planet', 'solarsystem', 'lunar'];

    /** Query parameters of the old paginated home page, redirected to the page without them. */
    private const OLD_HOMEPAGE_PARAMS = ['sessions', 'sketches', 'observations', 'sketch_type', 'obs_type'];

    public function show(string $user_slug, string $session_slug)
    {
        $user = User::where('slug', $user_slug)->firstOrFail();

        // observation_sessions uses observerid which maps to User::username in this app
        // Try to find by slug first, then by ID if the parameter is numeric
        $session = ObservationSession::where('observerid', $user->username)
            ->where(function ($query) use ($session_slug) {
                $query->where('slug', $session_slug);
                // If the parameter is numeric, also allow lookup by ID
                if (is_numeric($session_slug)) {
                    $query->orWhere('id', $session_slug);
                }
            })
            ->firstOrFail();

        // If the session is inactive (draft / active==0) only the owner or an administrator
        // with override permission may view it. For everyone else, behave as if it does not exist.
        if (isset($session->active) && (int) $session->active === 0) {
            $viewer = Auth::user();
            $allowAdmin = config('sessions.allow_admin_override', false);
            $viewerIsOwner = $viewer && ($viewer->username === $session->observerid || ($viewer->slug ?? null) === ($user->slug ?? null));
            $viewerIsAdmin = $viewer && method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges();

            if (!($viewerIsOwner || ($allowAdmin && $viewerIsAdmin))) {
                abort(404);
            }
        }

        // Load location information
        $location = null;
        $image = null; // default: no image unless found
        if (!empty($session->locationid)) {
            $location = Location::find($session->locationid);
            if ($location && !empty($location->picture)) {
                $image = asset('storage/' . $location->picture);
            }
        }

        // Resolve image via helper (prefers storage/photos/sessions, then public/images, then legacy storage paths)
        $image = $this->resolveSessionImage($session, $location);

        // Load other observers (legacy pivot table 'sessionObservers')
        $observerUsernames = $session->otherObservers();
        $observers = [];
        if (!empty($observerUsernames)) {
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
        // sessionObservations now stores new observations.id values (bigint).
        $newObsIds = DBFacade::table('sessionObservations')
            ->where('sessionid', $session->id)
            ->pluck('observationid')
            ->map(fn($v) => (int) $v)
            ->toArray();

        $obsIds = $newObsIds;

        // Allow viewing observations for a specific observer via ?observer={username}
        $targetObserver = request()->get('observer', $session->observerid);
        $selectedObserverUser = User::where('username', $targetObserver)->first();
        $selectedObserverName = $selectedObserverUser ? $selectedObserverUser->name : $targetObserver;

        // Fetch observations and drawings
        if (!empty($obsIds)) {
            // Fetch observations for the session recorded by the requested observer
            $allObservations = Observation::whereIn('id', $obsIds)
                ->where('observer_username', $targetObserver)
                ->orderBy('id', 'desc')
                ->get();
            [$cometObservations, $deepObservations] = $allObservations->partition(fn($o) => $o->target_type === Observation::TYPE_COMET);

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

            // Drawings for the requested observer
            $allDrawings = $allObservations->where('has_drawing', true)->values();

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

            $objectsDeepSky = $deepObservations->pluck('target_name')->unique()->values()->toArray();
            $objectsComet = $cometObservations->pluck('target_name')->unique()->values()->toArray();
        } else {
            $observations = collect([]);
            $drawings = collect([]);
            $objectsDeepSky = [];
            $objectsComet = [];
        }

        $allObjects = array_values(array_unique(array_merge($objectsDeepSky, $objectsComet)));
        sort($allObjects, SORT_STRING | SORT_FLAG_CASE);

        // Compute number of observations each observer did during this session.
        $observerStats = [];

        $counts = [];
        if (!empty($obsIds)) {
            // Count per observer across deepsky and comet
            $allCounts = Observation::whereIn('id', $newObsIds)
                ->select('observer_username', DBFacade::raw('count(*) as cnt'))
                ->groupBy('observer_username')
                ->pluck('cnt', 'observer_username')
                ->toArray();

            foreach ($allCounts as $who => $c) {
                $counts[$who] = ($counts[$who] ?? 0) + (int) $c;
            }
        }

        // Primary observer count (defaults to zero)
        $primaryCount = isset($counts[$session->observerid]) ? (int) $counts[$session->observerid] : 0;
        // Fallback: if primaryCount is zero but there are totalObservations, try direct count
        if ($primaryCount === 0 && $totalObservations > 0 && !empty($obsIds)) {
            $primaryCount = (int) Observation::whereIn('id', $newObsIds)->where('observer_username', $session->observerid)->count();
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
        $shouldTranslate = Auth::check() && Auth::user()->translate;
        $lang = $shouldTranslate ? (Auth::user()->language ?? config('app.locale')) : null;

        // Only translate session fields when the user's language actually differs from the session's language.
        // When they match (e.g. both English), serve the original HTML to preserve TinyMCE formatting.
        $sessionLanguage = $session->language ?? 'en';
        $needsTranslation = $shouldTranslate && $lang && $lang !== $sessionLanguage;

        $rawWeather = html_entity_decode($session->weather ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $weatherTranslated = $rawWeather;
        if ($needsTranslation && trim(strip_tags($rawWeather)) !== '') {
            $cacheKey = 'session_weather:' . $session->id . ':' . $lang;
            $cached = Cache::get($cacheKey);
            if ($cached !== null && $cached !== '') {
                $weatherTranslated = $cached;
            } else {
                // Translate plain text to avoid Google Translate mangling HTML markup
                $plainWeather = trim(strip_tags($rawWeather));
                try {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                    $t = $tr->translate($plainWeather);
                    if ($t !== null && $t !== '') {
                        Cache::put($cacheKey, $t, now()->addDays(30));
                        $weatherTranslated = $t;
                    }
                } catch (\Throwable $e) {
                    // Fall back to original HTML on translation failure
                }
            }
        }

        $rawEquipment = html_entity_decode($session->equipment ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $equipmentTranslated = $rawEquipment;
        if ($needsTranslation && trim(strip_tags($rawEquipment)) !== '') {
            $cacheKey = 'session_equipment:' . $session->id . ':' . $lang;
            $cached = Cache::get($cacheKey);
            if ($cached !== null && $cached !== '') {
                $equipmentTranslated = $cached;
            } else {
                $plainEquipment = trim(strip_tags($rawEquipment));
                try {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                    $t = $tr->translate($plainEquipment);
                    if ($t !== null && $t !== '') {
                        Cache::put($cacheKey, $t, now()->addDays(30));
                        $equipmentTranslated = $t;
                    }
                } catch (\Throwable $e) {
                    // Fall back to original HTML on translation failure
                }
            }
        }

        $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $commentsTranslated = $rawComments;
        if ($needsTranslation && trim(strip_tags($rawComments)) !== '') {
            $cacheKey = 'session_comments:' . $session->id . ':' . $lang;
            $cached = Cache::get($cacheKey);
            if ($cached !== null && $cached !== '') {
                $commentsTranslated = $cached;
            } else {
                $plainComments = trim(strip_tags($rawComments));
                try {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate($lang);
                    $t = $tr->translate($plainComments);
                    if ($t !== null && $t !== '') {
                        Cache::put($cacheKey, $t, now()->addDays(30));
                        $commentsTranslated = $t;
                    }
                } catch (\Throwable $e) {
                    // Fall back to original HTML on translation failure
                }
            }
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

        // What the observation and sketch cards show (observers, objects,
        // equipment, likes, ...), in grouped queries instead of per card.
        ObservationCardData::instance()->preload($drawings)->preload($observations);

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
        if (!$user) {
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
        ObservationSession::preloadOtherObservers($sessions->getCollection());

        // Prefetch related locations to avoid N+1 when resolving location pictures
        $collection = $sessions->getCollection();
        $locationIds = $collection->pluck('locationid')->filter()->unique()->values()->all();
        $locations = [];
        if (!empty($locationIds)) {
            $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        $sessionImageDir = public_path('images/sessions');
        $storageSessionDir = storage_path('app/public/photos/sessions');

        // Pre-scan image directories once and build maps session_id => image URL
        $storageSessionMap = [];
        if (is_dir($storageSessionDir)) {
            $glob = glob($storageSessionDir . '/*');
            if (!empty($glob)) {
                foreach ($glob as $p) {
                    $base = basename($p);
                    $id = pathinfo($base, PATHINFO_FILENAME);
                    $storageSessionMap[$id] = asset('storage/photos/sessions/' . $base);
                }
            }
        }

        $publicSessionMap = [];
        if (is_dir($sessionImageDir)) {
            $glob = glob($sessionImageDir . '/*');
            if (!empty($glob)) {
                foreach ($glob as $p) {
                    $base = basename($p);
                    $id = pathinfo($base, PATHINFO_FILENAME);
                    $publicSessionMap[$id] = '/images/sessions/' . $base;
                }
            }
        }

        // Precompute observation counts for the sessions in this page to avoid N+1 queries
        $sessionIds = $collection->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (!empty($sessionIds)) {
            $obsCounts = DBFacade::table('sessionObservations')
                ->whereIn('sessionid', $sessionIds)
                ->select('sessionid', DBFacade::raw('count(*) as cnt'))
                ->groupBy('sessionid')
                ->pluck('cnt', 'sessionid')
                ->toArray();

            // Also include any new unified observations from the `observations` table
            // that match the session participants and date range but are not yet
            // present in the `sessionObservations` pivot. This ensures newly
            // created observations appear on the homepage without waiting for
            // background backfills/jobs.
            try {
                foreach ($collection as $session) {
                    $sid = $session->id;

                    // Prepare participants: primary observer + any other observers
                    $participants = [];
                    try {
                        $participants = is_array($session->otherObservers()) ? $session->otherObservers() : [];
                    } catch (\Throwable $_) {
                        $participants = [];
                    }
                    if (!in_array($session->observerid, $participants, true)) {
                        $participants[] = $session->observerid;
                    }

                    if (empty($participants)) {
                        continue;
                    }

                    // Date range
                    $startDate = null;
                    $endDate = null;
                    try {
                        if (!empty($session->begindate)) {
                            $startDate = \Carbon\Carbon::parse($session->begindate)->format('Y-m-d');
                        }
                    } catch (\Throwable $_) {
                        $startDate = null;
                    }
                    try {
                        if (!empty($session->enddate)) {
                            $endDate = \Carbon\Carbon::parse($session->enddate)->format('Y-m-d');
                        }
                    } catch (\Throwable $_) {
                        $endDate = null;
                    }

                    // Determine existing pivot observation ids for this session to avoid double-counting
                    $existingIds = DBFacade::table('sessionObservations')->where('sessionid', $sid)->pluck('observationid')->map(fn($v) => (int) $v)->toArray();

                    $q = DBFacade::table('observations')->whereIn('observer_username', $participants);
                    if ($startDate) {
                        $q->where('observed_on', '>=', $startDate);
                    }
                    if ($endDate) {
                        $q->where('observed_on', '<=', $endDate);
                    }
                    if (!empty($existingIds)) {
                        $q->whereNotIn('id', $existingIds);
                    }

                    $additional = (int) $q->count();
                    $obsCounts[$sid] = (int) ($obsCounts[$sid] ?? 0) + $additional;
                }
            } catch (\Throwable $_) {
                // If anything fails here, fall back to the original counts silently.
            }
        }

        $shouldTranslate = Auth::check() && Auth::user()->translate;
        $lang = $shouldTranslate ? (Auth::user()->language ?? config('app.locale')) : null;

        // Preload primary observers for the sessions so views can safely access $session->observer
        $observerUsernames = $collection->pluck('observerid')->filter()->unique()->values()->all();
        $observerUsers = [];
        if (!empty($observerUsernames)) {
            $observerUsers = User::whereIn('username', $observerUsernames)->get()->keyBy('username');
        }

        // Pre-scan image directories once and build maps session_id => image URL
        $storageSessionDir = storage_path('app/public/photos/sessions');
        $storageSessionMap = [];
        if (is_dir($storageSessionDir)) {
            $glob = glob($storageSessionDir . '/*');
            if (!empty($glob)) {
                foreach ($glob as $p) {
                    $base = basename($p);
                    $id = pathinfo($base, PATHINFO_FILENAME);
                    $storageSessionMap[$id] = asset('storage/photos/sessions/' . $base);
                }
            }
        }

        $publicSessionMap = [];
        if (is_dir($sessionImageDir)) {
            $glob = glob($sessionImageDir . '/*');
            if (!empty($glob)) {
                foreach ($glob as $p) {
                    $base = basename($p);
                    $id = pathinfo($base, PATHINFO_FILENAME);
                    $publicSessionMap[$id] = '/images/sessions/' . $base;
                }
            }
        }

        $collection = $collection->transform(function ($session) use ($locations, $sessionImageDir, $obsCounts, $shouldTranslate, $lang, $observerUsers) {
            // Ensure a stable observer object is always available on the session instance
            if (empty($session->observer)) {
                $session->observer = isset($observerUsers[$session->observerid]) ? $observerUsers[$session->observerid] : (object) ['name' => $session->observerid, 'slug' => null];
            }
            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            $session->preview = $this->resolveSessionImage($session, $loc);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;

            // Prepare translated preview text (comments) if needed and cache per session+lang
            $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = $rawComments;
            if ($shouldTranslate && $lang) {
                $cacheKey = 'session_preview:' . $session->id . ':' . $lang;
                $translated = Cache::remember($cacheKey, now()->addDays(30), function () use ($rawComments, $lang) {
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
                if (!empty($others)) {
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
        $sessions->setCollection($this->prepareSessionCards($sessions->getCollection()));

        return $this->noCacheResponse(response()->view('session.all-sessions', compact('sessions')));
    }

    /**
     * Prepare sessions for x-session-card (the home page and /sessions): the
     * observer, location name, picture, number of observations, likes and the
     * start of the comments, with grouped queries for all sessions together.
     *
     * @param  Collection<int, ObservationSession>  $collection
     * @return Collection<int, ObservationSession>
     */
    private function prepareSessionCards(Collection $collection): Collection
    {
        ObservationSession::preloadOtherObservers($collection);

        // Prefetch related locations to avoid N+1 when resolving location pictures
        $locationIds = $collection->pluck('locationid')->filter()->unique()->values()->all();
        $locations = [];
        if (!empty($locationIds)) {
            $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        // Precompute observation counts for the sessions in this page to avoid N+1 queries
        $sessionIds = $collection->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (!empty($sessionIds)) {
            $obsCounts = DBFacade::table('sessionObservations')
                ->whereIn('sessionid', $sessionIds)
                ->select('sessionid', DBFacade::raw('count(*) as cnt'))
                ->groupBy('sessionid')
                ->pluck('cnt', 'sessionid')
                ->toArray();
        }

        [$likeCounts, $likedIds] = $this->sessionLikes($sessionIds);

        $shouldTranslate = Auth::check() && Auth::user()->translate;
        $lang = $shouldTranslate ? (Auth::user()->language ?? config('app.locale')) : null;

        // Preload primary observers for the sessions so views can safely access $session->observer
        $observerUsernames = $collection->pluck('observerid')->filter()->unique()->values()->all();
        $observerUsers = [];
        if (!empty($observerUsernames)) {
            $observerUsers = User::whereIn('username', $observerUsernames)->get()->keyBy('username');
        }

        return $collection->transform(function ($session) use ($locations, $obsCounts, $shouldTranslate, $lang, $observerUsers, $likeCounts, $likedIds) {
            if (empty($session->observer)) {
                $session->observer = isset($observerUsers[$session->observerid]) ? $observerUsers[$session->observerid] : (object) ['name' => $session->observerid, 'slug' => null];
            }
            $loc = isset($locations[$session->locationid]) ? $locations[$session->locationid] : null;
            $session->location_name = $loc ? ($loc->name ?? null) : null;
            $session->preview = $this->resolveSessionImage($session, $loc);
            $session->observation_count = isset($obsCounts[$session->id]) ? (int) $obsCounts[$session->id] : 0;
            $session->likes_count = (int) ($likeCounts[$session->id] ?? 0);
            $session->liked = isset($likedIds[$session->id]);

            // The start of the comments, translated for observers who asked for translations
            $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $translated = $rawComments;
            if ($shouldTranslate && $lang) {
                $cacheKey = 'session_preview:' . $session->id . ':' . $lang;
                $translated = Cache::remember($cacheKey, now()->addDays(30), function () use ($rawComments, $lang) {
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
    }

    /**
     * Like counts of the given sessions, and which of them the viewer liked.
     *
     * @return array{0: array<int, int>, 1: array<int, int>} counts keyed by session id, and the liked session ids as keys
     */
    private function sessionLikes(array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return [[], []];
        }

        $likeCounts = ObservationLike::where('observation_type', ObservationLike::TYPE_SESSION)
            ->whereIn('observation_id', $sessionIds)
            ->select('observation_id', DBFacade::raw('count(*) as cnt'))
            ->groupBy('observation_id')
            ->pluck('cnt', 'observation_id')
            ->toArray();

        $likedIds = [];
        if (Auth::check()) {
            $likedIds = ObservationLike::where('observation_type', ObservationLike::TYPE_SESSION)
                ->whereIn('observation_id', $sessionIds)
                ->where('user_id', Auth::id())
                ->pluck('observation_id')
                ->flip()
                ->all();
        }

        return [$likeCounts, $likedIds];
    }

    /**
     * Show sessions for a specific user (public view).
     */
    public function user(Request $request, $user)
    {
        // Try to resolve user by slug first, then by username fallback
        $u = User::where('slug', $user)->first();
        if (!$u) {
            $u = User::where('username', $user)->firstOrFail();
        }

        $query = ObservationSession::where('observerid', $u->username)
            ->where('active', 1)
            ->withObserver()
            ->orderByDesc('enddate')
            ->orderByDesc('begindate');

        $perPage = 12;
        $sessions = $query->paginate($perPage)->withQueryString();
        ObservationSession::preloadOtherObservers($sessions->getCollection());

        // Prefetch related locations to avoid N+1 when resolving location pictures
        $collection = $sessions->getCollection();
        $locationIds = $collection->pluck('locationid')->filter()->unique()->values()->all();
        $locations = [];
        if (!empty($locationIds)) {
            $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        }

        $sessionImageDir = public_path('images/sessions');

        // Precompute observation counts for the sessions in this page to avoid N+1 queries
        $sessionIds = $collection->pluck('id')->filter()->unique()->values()->all();
        $obsCounts = [];
        if (!empty($sessionIds)) {
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
            // Attach a human-friendly location name for user sessions view
            $session->location_name = $loc ? ($loc->name ?? null) : null;
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
            // The first 10 drafts, or all of them after "Show more" (?show_drafts=1).
            $showAllDrafts = $request->boolean('show_drafts');
            $allDrafts = ObservationSession::where('observerid', $ownerUsername)
                ->where('active', 0)
                ->with('observer')
                ->orderByDesc('id')
                ->when(!$showAllDrafts, fn ($query) => $query->limit(11))
                ->get();

            if (!$showAllDrafts && $allDrafts->count() > 10) {
                $inactiveMore = true;
            }

            $inactiveSessions = $showAllDrafts ? $allDrafts : $allDrafts->slice(0, 10);

            // Attach display names of other observers for each draft
            $inactiveSessions = $inactiveSessions->map(function ($s) {
                $others = $s->otherObservers();
                $names = [];
                if (!empty($others)) {
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
    public function create(Request $request)
    {
        // ?adapt_from={id} opens an existing session in the form (see adapt()),
        // which saves over it, so it needs the same check as adapt().
        $session = null;
        if ($request->filled('adapt_from')) {
            $session = ObservationSession::findOrFail($request->query('adapt_from'));
            if (!$session->canBeEditedBy($request->user())) {
                abort(403);
            }
        }

        return $this->noCacheResponse(response()->view('session.create', compact('session')));
    }

    /**
     * Redirect to create session with the data from an existing session to adapt it.
     * Lightweight approach: redirect to session.create with adapt_from={id}
     */
    public function adapt(Request $request, $sessionId)
    {
        $session = ObservationSession::findOrFail($sessionId);

        if (!$session->canBeEditedBy(auth()->user())) {
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

        if (!$session->canBeEditedBy(auth()->user())) {
            abort(403);
        }

        // Hard-delete: remove pivot rows and related data, then delete the session row.
        try {
            // Remove legacy pivot links between sessions and observations
            DBFacade::table('sessionObservations')->where('sessionid', $session->id)->delete();

            // Remove legacy session observers pivot rows
            DBFacade::table('sessionObservers')->where('sessionid', $session->id)->delete();

            // Remove observation likes that reference this session (observation_type = 'session')
            \App\Models\ObservationLike::where('observation_type', \App\Models\ObservationLike::TYPE_SESSION)->where('observation_id', $session->id)->delete();

            // Remove any public image files named after the session id
            $sessionImageDir = public_path('images/sessions');
            if (is_dir($sessionImageDir)) {
                $matches = glob($sessionImageDir . '/' . $session->id . '.*');
                if (!empty($matches)) {
                    foreach ($matches as $f) {
                        @unlink($f);
                    }
                }
            }

            // Remove legacy stored picture referenced via storage if present
            if (!empty($session->picture)) {
                // session->picture is usually stored for asset('storage/...') paths, remove from storage/app/public
                $storagePath = storage_path('app/public/' . ltrim($session->picture, '/'));
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
     * The home page (and /dashboard): news, tonight's best, the sketches of the
     * week and month, and a short preview of the newest observations, sketches,
     * sessions and observing lists, each with a link to its full page.
     */
    public function homepage(Request $request)
    {
        // The home page no longer pages its boxes: send the old (crawler) URLs to the page itself.
        if (array_intersect(self::OLD_HOMEPAGE_PARAMS, array_keys($request->query())) !== []) {
            return redirect($request->url(), 301);
        }

        $sessions = $this->prepareSessionCards(ObservationSession::where('active', 1)
            ->withObserver()
            ->orderByDesc('enddate')
            ->orderByDesc('begindate')
            ->limit(self::HOMEPAGE_SESSIONS)
            ->get());

        // The newest observations and sketches, refreshed every 5 minutes and when an observation
        // changes (ObservationObserver::forgetHomepageCaches())
        $observations = Cache::remember('welcome_observations', 300, fn () => Observation::whereIn('target_type', self::HOMEPAGE_TARGET_TYPES)
            ->orderByDesc('observed_on')
            ->orderByDesc('id')
            ->limit(self::HOMEPAGE_OBSERVATIONS)
            ->get());
        $sketches = Cache::remember('welcome_sketches', 300, fn () => Observation::where('has_drawing', true)
            ->whereIn('target_type', self::HOMEPAGE_TARGET_TYPES)
            ->orderByDesc('observed_on')
            ->orderByDesc('id')
            ->limit(self::HOMEPAGE_SKETCHES)
            ->get());

        $today = today()->toDateString();
        $sketchOfTheWeek = Cache::remember('welcome_sketch_week:' . $today, 3600, fn () => SketchOfTheWeek::published()->orderByDesc('date')->first());
        $sketchOfTheMonth = Cache::remember('welcome_sketch_month:' . $today, 3600, fn () => SketchOfTheMonth::published()->with('observation')->orderByDesc('date')->first());

        // Observers, objects, equipment, ... of all observations on the page in grouped queries
        ObservationCardData::instance()->preload($observations->concat($sketches)
            ->push($sketchOfTheWeek?->observation, $sketchOfTheMonth?->observation)
            ->filter());

        // The newest public observing lists with objects, refreshed every 5 minutes as the sketches
        $observingLists = Cache::remember('welcome_observing_lists', 300, fn () => ObservingList::where('public', true)
            ->has('items')
            ->with('owner:id,name,slug')
            ->withCount(['items', 'subscriptions'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get());

        // The objects worth observing tonight, for the observer's telescope or, for guests, the most observed ones
        try {
            $tonightsBest = TonightsBest::for(Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $tonightsBest = null;
        }

        // The current news (#960), with the local circumstances of the events for the observer's location
        try {
            $news = NewsItem::current()->with('author:id,name')->orderByDesc('published_at')->orderByDesc('id')->limit(self::HOMEPAGE_NEWS)->get();
            $newsLocation = $news->contains(fn (NewsItem $item) => $item->isAutomatic()) ? Location::forEphemerides(Auth::user()) : null;
        } catch (\Throwable $e) {
            report($e);
            $news = collect();
            $newsLocation = null;
        }

        return $this->noCacheResponse(response()->view('welcome', compact(
            'sessions',
            'observations',
            'sketches',
            'sketchOfTheWeek',
            'sketchOfTheMonth',
            'observingLists',
            'tonightsBest',
            'news',
            'newsLocation',
        )));
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
    public static function resolveSessionImage($session, $location = null)
    {
        $image = null;

        // 1) storage/app/public/photos/sessions (accessible via /storage/photos/sessions/...)
        $storageSessionDir = storage_path('app/public/photos/sessions');
        if (is_dir($storageSessionDir)) {
            $patterns = [
                $storageSessionDir . '/' . $session->id . '.jpg',
                $storageSessionDir . '/' . $session->id . '.jpeg',
                $storageSessionDir . '/' . $session->id . '.png',
                $storageSessionDir . '/' . $session->id . '.gif',
            ];
            foreach ($patterns as $p) {
                if (file_exists($p)) {
                    $image = asset('storage/photos/sessions/' . basename($p));
                    break;
                }
            }

            if (empty($image)) {
                $glob = glob($storageSessionDir . '/' . $session->id . '.*');
                if (!empty($glob)) {
                    $image = asset('storage/photos/sessions/' . basename($glob[0]));
                }
            }
        }

        // 2) public/images/sessions legacy files
        if (empty($image)) {
            $sessionImageDir = public_path('images/sessions');
            if (is_dir($sessionImageDir)) {
                $patterns = [
                    $sessionImageDir . '/' . $session->id . '.jpg',
                    $sessionImageDir . '/' . $session->id . '.jpeg',
                    $sessionImageDir . '/' . $session->id . '.png',
                    $sessionImageDir . '/' . $session->id . '.gif',
                ];
                foreach ($patterns as $p) {
                    if (file_exists($p)) {
                        $image = '/images/sessions/' . basename($p);
                        break;
                    }
                }

                if (empty($image)) {
                    $glob = glob($sessionImageDir . '/' . $session->id . '.*');
                    if (!empty($glob)) {
                        $image = '/images/sessions/' . basename($glob[0]);
                    }
                }
            }
        }

        // 3) legacy session->picture stored under storage/app/public/... (session->picture is usually a relative path)
        if (empty($image) && !empty($session->picture)) {
            $image = asset('storage/' . $session->picture);
        }

        // 4) location picture (fallback)
        if (empty($image) && $location && !empty($location->picture)) {
            $image = asset('storage/' . $location->picture);
        }

        return $image;
    }
}
