<?php

namespace App\Http\Controllers;

use App\Models\ObservationsOld;
use App\Models\CometObservationsOld;
use App\Models\Observation;
use App\Models\User;
use App\Models\ObjectsOld;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        // Resolve candidates from the unified observations table first.
        $unifiedMatches = Observation::where('observer_username', $observerUser->username)
            ->whereDate('observed_on', $date)
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

        if ($selectedUnified->legacy_source === 'deepsky') {
            $selected = ObservationsOld::find($selectedUnified->legacy_id);
            if (!$selected) {
                abort(404);
            }

            $socialData = $this->buildSocialData('deepsky', (int) $selected->id);

            $preloadedData = $this->preloadDeepskyData(collect([$selected]));

            $lens = null;
            if (!empty($selected->lensid) && $selected->lensid > 0) {
                $lens = Lens::find($selected->lensid);
            }

            return view('observations.detail', array_merge([
                'observationType' => 'deepsky',
                'observation' => $selected,
                'detail_url' => $canonicalUrl,
                'lens' => $lens,
                'sketchOfTheWeek' => $sketchOfTheWeek,
                'sketchOfTheMonth' => $sketchOfTheMonth,
                // Unified observation id from the unified `observations` table
                'unified_observation_id' => $selectedUnified->id,
            ], $preloadedData, $socialData));
        }

        if ($selectedUnified->legacy_source === 'comet') {
            $selected = CometObservationsOld::find($selectedUnified->legacy_id);
            if (!$selected) {
                abort(404);
            }

            $socialData = $this->buildSocialData('comet', (int) $selected->id);

            // Keep this field for existing comet detail rendering expectations.
            $selected->resolved_comet_name = (string) ($selectedUnified->target_name ?? '');

            $preloadedData = $this->preloadCometData(collect([$selected]));

            return view('observations.detail', array_merge([
                'observationType' => 'comet',
                'observation' => $selected,
                'detail_url' => $canonicalUrl,
                'lens' => null,
                'sketchOfTheWeek' => $sketchOfTheWeek,
                'sketchOfTheMonth' => $sketchOfTheMonth,
                // Unified observation id from the unified `observations` table
                'unified_observation_id' => $selectedUnified->id,
            ], $preloadedData, $socialData));
        }

        abort(404);
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
            ->whereDate('observed_on', $date)
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

        if ($selectedUnified->legacy_source === 'deepsky') {
            $currentObservation = ObservationsOld::find($selectedUnified->legacy_id);
            if (!$currentObservation) {
                abort(404);
            }

            $myLastObservation = ObservationsOld::query()
                ->where('observerid', $viewer->username)
                ->where('objectname', $currentObservation->objectname)
                ->where('id', '!=', $currentObservation->id)
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->orderByDesc('id')
                ->first();

            $observations = collect([$currentObservation]);
            if ($myLastObservation) {
                $observations->push($myLastObservation);
            }

            return view('observations.compare-my-last', array_merge([
                'observationType' => 'deepsky',
                'currentObservation' => $currentObservation,
                'myLastObservation' => $myLastObservation,
                'objectName' => (string) ($currentObservation->objectname ?? ''),
                'objectSlug' => Str::slug((string) ($currentObservation->objectname ?? ''), '-'),
                'currentObserver' => $observerUser,
                'currentDetailUrl' => $this->buildObservationDetailUrl(
                    $observerUser->slug ?: $observerUser->username,
                    $this->normalizeObservedOnDate($selectedUnified->observed_on, $date),
                    Str::slug((string) ($selectedUnified->target_name ?? ''), '-'),
                    $wantedIndex
                ),
                'myLastDetailUrl' => $myLastObservation
                    ? $this->buildObservationDetailUrlForLegacy('deepsky', (int) $myLastObservation->id)
                    : null,
            ], $this->preloadDeepskyData($observations)));
        }

        if ($selectedUnified->legacy_source === 'comet') {
            $currentObservation = CometObservationsOld::find($selectedUnified->legacy_id);
            if (!$currentObservation) {
                abort(404);
            }

            $myLastObservation = CometObservationsOld::query()
                ->where('observerid', $viewer->username)
                ->where('objectid', $currentObservation->objectid)
                ->where('id', '!=', $currentObservation->id)
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->orderByDesc('id')
                ->first();

            $observations = collect([$currentObservation]);
            if ($myLastObservation) {
                $observations->push($myLastObservation);
            }

            $preloadedData = $this->preloadCometData($observations);
            $comet = ($preloadedData['preloaded_comets'] ?? collect())->get($currentObservation->objectid);
            $objectName = (string) ($comet->name ?? $selectedUnified->target_name ?? __('Unknown comet'));

            return view('observations.compare-my-last', array_merge([
                'observationType' => 'comet',
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
                'myLastDetailUrl' => $myLastObservation
                    ? $this->buildObservationDetailUrlForLegacy('comet', (int) $myLastObservation->id)
                    : null,
            ], $preloadedData));
        }

        abort(404);
    }

    /**
     * Show global list of observations (both deepsky and comet) paginated.
     */
    public function index()
    {
        // Default observations page shows deepsky observations (mirrors /drawings behavior)
        $deepsky = ObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadDeepskyData($deepsky);

        return view('observations.show', array_merge([
            'user' => '',
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
        ], $preloadedData));
    }

    /**
     * Show observations for a specific observer (both deepsky and comet).
     */
    public function show(string $slug)
    {
        // Try to resolve the slug as a user first (existing behaviour)
        $user = User::where('slug', $slug)->first();
        if ($user) {
            $deepsky = ObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadDeepskyData($deepsky);

            return view('observations.show', array_merge([
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
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
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        if ($objectName) {
            $deepsky = ObservationsOld::where('objectname', $objectName)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

            // Provide a lightweight user-like object so the view header can show a title
            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadDeepskyData($deepsky);

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'drawingsOnly' => true,
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
        $comet = CometObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'comet')->appends(request()->query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCometData($comet);

        return view('observations.show', array_merge([
            'user' => '',
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ], $preloadedData));
    }

    /**
     * Show comet observations filtered by object slug (object-scoped comet list).
     * URL: /cometobservations/{object}
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
                $co = \App\Models\CometObject::whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
                if ($co) {
                    $objectName = $co->name;
                    $objectId = $co->id;
                    break;
                }
            }
        } catch (\Throwable $_) {
        }

        // Try legacy cometobjects on mysqlOld
        if (!$objectId) {
            try {
                foreach ($candidates as $cand) {
                    $row = DB::connection('mysqlOld')->table('cometobjects')->whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
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

        $comet = CometObservationsOld::where('objectid', $objectId)->orderBy('date', 'desc')->paginate(20, ['*'], 'comet')->appends(request()->query());

        $fakeUser = (object) ['name' => $objectName ?? $slug, 'slug' => $slug, 'username' => null];

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCometData($comet);

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
                $co = \App\Models\CometObject::whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
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
                    $row = DB::connection('mysqlOld')->table('cometobjects')->whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
                    if ($row) {
                        $objectName = $row->name ?? null;
                        $objectId = $row->id ?? null;
                        break;
                    }
                }
            } catch (\Throwable $_) {
            }
        }

        $query = CometObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc');
        if ($objectId) {
            $query->where('objectid', $objectId);
        }

        $comet = $query->paginate(20, ['*'], 'comet')->appends(request()->query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCometData($comet);

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

        $comet = CometObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'comet')->appends(request()->query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadCometData($comet);

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
            $deepsky = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadDeepskyData($deepsky);

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
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        if ($objectName) {
            $deepsky = ObservationsOld::where('objectname', $objectName)->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            // Preload related data to avoid N+1 queries
            $preloadedData = $this->preloadDeepskyData($deepsky);

            return view('observations.show', array_merge([
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
            ], $preloadedData));
        }

        // Not a deepsky object — maybe this slug refers to a comet object (modern or legacy).
        // If so, redirect to the comet drawings handler so comet drawings are displayed.
        $objectSlug = $slug;
        $objectId = null;
        try {
            foreach ($candidates as $cand) {
                $co = \App\Models\CometObject::whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
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
                    $row = DB::connection('mysqlOld')->table('cometobjects')->whereRaw('LOWER(name) = ?', [$cand])->orWhereRaw('LOWER(slug) = ?', [$cand])->first();
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
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        $query = ObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc');
        if ($objectName) {
            $query->where('objectname', $objectName);
        }

        $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(request()->query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadDeepskyData($deepsky);

        return view('observations.show', array_merge([
            'user' => $user,
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
            'objectFilter' => true,
            'objectName' => $objectName,
        ], $preloadedData));
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
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) {
                    $objectName = $on->objectname;
                    break;
                }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) {
                    $objectName = $o->name;
                    break;
                }
            }
        }

        $query = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->orderBy('date', 'desc');
        if ($objectName) {
            $query->where('objectname', $objectName);
        }

        $deepsky = $query->paginate(20, ['*'], 'deepsky')->appends(request()->query());

        // Preload related data to avoid N+1 queries
        $preloadedData = $this->preloadDeepskyData($deepsky);

        return view('observations.show', array_merge([
            'user' => $user,
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
            'objectFilter' => true,
        ], $preloadedData));
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

        // Batch load all related models
        $users = User::whereIn('username', $usernames)->get()->keyBy('username');
        $objects = ObjectsOld::whereIn('name', $objectNames)->get()->keyBy('name');
        $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        $instruments = Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');
        $eyepieces = $eyepieceIds->isNotEmpty() ? Eyepiece::whereIn('id', $eyepieceIds)->get()->keyBy('id') : collect();
        $filters = $filterIds->isNotEmpty() ? Filter::whereIn('id', $filterIds)->get()->keyBy('id') : collect();

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
                $legacyComets = DB::connection('mysqlOld')->table('cometobjects')
                    ->whereIn('id', $objectIds)
                    ->get();
                $comets = collect($legacyComets)->keyBy('id');
            } catch (\Throwable $_) {
            }
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
        if ($name === '') {
            return false;
        }

        $routeCandidates = $this->buildSlugCandidates($routeValue);
        $nameCandidates = $this->buildSlugCandidates($name);

        return count(array_intersect($routeCandidates, $nameCandidates)) > 0;
    }

    /**
     * Build candidate variants used for tolerant slug matching.
     */
    private function buildSlugCandidates(string $value): array
    {
        $lower = mb_strtolower(trim($value));
        $slug = Str::slug($value, '-');
        $compact = str_replace([' ', '-'], '', $lower);
        $noLeadingZeros = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower) ?? $lower;

        return array_values(array_unique(array_filter([
            $lower,
            $slug,
            $compact,
            $noLeadingZeros,
            str_replace('-', '', $slug),
        ])));
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
     * Index is omitted for the first observation.
     */
    private function buildObservationCompareUrl(string $observer, string $date, string $object, int $index): string
    {
        $params = [
            'observer' => $observer,
            'date' => $date,
            'object' => $object,
        ];

        if ($index > 1) {
            $params['index'] = $index;
        }

        return route('observations.compare.my-last', $params);
    }

    /**
     * Build detail URL for a legacy observation record by resolving it in the
     * unified observations table and deriving its index for that day/object.
     */
    private function buildObservationDetailUrlForLegacy(string $legacySource, int $legacyId): ?string
    {
        $unified = Observation::query()
            ->where('legacy_source', $legacySource)
            ->where('legacy_id', $legacyId)
            ->first();

        if (!$unified || !$unified->observed_on) {
            return null;
        }

        $observerUser = User::where('username', $unified->observer_username)->first();
        $observerParam = $observerUser?->slug ?: $unified->observer_username;
        $objectSlug = Str::slug((string) ($unified->target_name ?? ''), '-');
        $date = $this->normalizeObservedOnDate($unified->observed_on, '');
        if ($date === '') {
            return null;
        }

        $matches = Observation::where('observer_username', $unified->observer_username)
            ->whereDate('observed_on', $date)
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get()
            ->filter(fn($candidate) => $this->slugMatches($objectSlug, (string) ($candidate->target_name ?? '')))
            ->values();

        $index = 1;
        foreach ($matches as $position => $candidate) {
            if ((int) $candidate->id === (int) $unified->id) {
                $index = $position + 1;
                break;
            }
        }

        return $this->buildObservationDetailUrl($observerParam, $date, $objectSlug, $index);
    }

    /**
     * Load likes/comments for a detail page in one place.
     *
     * @return array<string, mixed>
     */
    private function buildSocialData(string $observationType, int $observationId): array
    {
        $likesCount = ObservationLike::query()
            ->where('observation_type', $observationType)
            ->where('observation_id', $observationId)
            ->count();

        $liked = auth()->check() && ObservationLike::query()
            ->where('observation_type', $observationType)
            ->where('observation_id', $observationId)
            ->where('user_id', auth()->id())
            ->exists();

        $comments = ObservationComment::query()
            ->with('user')
            ->where('observation_type', $observationType)
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
}
