<?php

namespace App\Http\Controllers;

use App\Models\ObservationsOld;
use App\Models\CometObservationsOld;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ObservationsController extends Controller
{
    /**
     * Show global list of observations (both deepsky and comet) paginated.
     */
    public function index()
    {
        // Default observations page shows deepsky observations (mirrors /drawings behavior)
        $deepsky = ObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'deepsky');

        return view('observations.show', [
            'user' => '',
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
        ]);
    }

    /**
     * Show observations for a specific observer (both deepsky and comet).
     */
    public function show(string $slug)
    {
        // Try to resolve the slug as a user first (existing behaviour)
        $user = User::where('slug', $slug)->first();
        if ($user) {
            $deepsky = ObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky');

            return view('observations.show', [
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
            ]);
        }

        // Not a user: attempt to resolve the slug to a deepsky object (objectnames / objects)
        $raw = (string) $slug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) { $candidates[] = $slugified; }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) { $candidates[] = $nospace; }
        // remove leading zeros in numeric portions (m-031 -> m-31)
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) { $objectName = $on->objectname; break; }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) { $objectName = $o->name; break; }
        }

        // Fallback: try case-insensitive name/altname matches
        if (! $objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) { $objectName = $on->objectname; break; }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) { $objectName = $o->name; break; }
            }
        }

        if ($objectName) {
            $deepsky = ObservationsOld::where('objectname', $objectName)->orderBy('date','desc')->paginate(20, ['*'], 'deepsky');

            // Provide a lightweight user-like object so the view header can show a title
            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            return view('observations.show', [
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'drawingsOnly' => true,
                'objectName' => $objectName,
            ]);
        }

        abort(404);
    }

    /**
     * Show comet-only observations (global)
     */
    public function cometIndex()
    {
        $comet = CometObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'comet');

        return view('observations.show', [
            'user' => '',
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ]);
    }

    /**
     * Show comet-only observations for a specific observer
     */
    public function cometShow(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $comet = CometObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'comet');

        return view('observations.show', [
            'user' => $user,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ]);
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
            $deepsky = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky');

            return view('observations.show', [
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'drawingsOnly' => true,
            ]);
        }

        // Attempt to resolve the slug to an object name (objectnames / objects)
        $raw = (string) $slug;
        $lower = mb_strtolower($raw);
        $candidates = [$lower];
        $slugified = Str::slug($raw, '-');
        if ($slugified && $slugified !== $lower) { $candidates[] = $slugified; }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) { $candidates[] = $nospace; }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) { $objectName = $on->objectname; break; }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) { $objectName = $o->name; break; }
        }

        if (! $objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) { $objectName = $on->objectname; break; }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) { $objectName = $o->name; break; }
            }
        }

        if ($objectName) {
            $deepsky = ObservationsOld::where('objectname', $objectName)->where('hasDrawing', 1)->orderBy('date','desc')->paginate(20, ['*'], 'deepsky');

            $fakeUser = (object) ['name' => $objectName, 'slug' => $slug, 'username' => null];

            return view('observations.show', [
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
            ]);
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
        if ($slugified && $slugified !== $lower) { $candidates[] = $slugified; }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) { $candidates[] = $nospace; }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) { $objectName = $on->objectname; break; }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) { $objectName = $o->name; break; }
        }
        if (! $objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) { $objectName = $on->objectname; break; }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) { $objectName = $o->name; break; }
            }
        }

        $query = ObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc');
        if ($objectName) {
            $query->where('objectname', $objectName);
        }

        $deepsky = $query->paginate(20, ['*'], 'deepsky');

            return view('observations.show', [
                'user' => $user,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
                'objectFilter' => true,
                'objectName' => $objectName,
            ]);
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
        if ($slugified && $slugified !== $lower) { $candidates[] = $slugified; }
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) { $candidates[] = $nospace; }
        $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);
        $candidates[] = str_replace('-', '', $lower);
        $candidates = array_values(array_unique(array_filter($candidates)));

        $objectName = null;
        foreach ($candidates as $cand) {
            $on = DB::table('objectnames')->where('slug', $cand)->first();
            if ($on) { $objectName = $on->objectname; break; }
            $o = DB::table('objects')->where('slug', $cand)->first();
            if ($o) { $objectName = $o->name; break; }
        }
        if (! $objectName) {
            foreach ($candidates as $cand) {
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) { $objectName = $on->objectname; break; }

                $o = DB::table('objects')->whereRaw('LOWER(name) = ?', [$cand])->first();
                if ($o) { $objectName = $o->name; break; }
            }
        }

        $query = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->orderBy('date', 'desc');
        if ($objectName) {
            $query->where('objectname', $objectName);
        }

        $deepsky = $query->paginate(20, ['*'], 'deepsky');

        return view('observations.show', [
            'user' => $user,
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
            'objectFilter' => true,
        ]);
    }
}
