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
        $deepsky = ObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

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
            $deepsky = ObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

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
        if (! $objectName) {
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
        $comet = CometObservationsOld::orderBy('id', 'desc')->paginate(20, ['*'], 'comet')->appends(request()->query());

        return view('observations.show', [
            'user' => '',
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
        ]);
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
        if ($slugified && $slugified !== $lower) $candidates[] = $slugified;
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) $candidates[] = $nospace;
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
        if (! $objectId) {
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
            if ($maybeUser && ! $objectId) {
                return $this->cometShow($slug);
            }
        } catch (\Throwable $_) {
        }

        if (! $objectId) {
            abort(404);
        }

        $comet = CometObservationsOld::where('objectid', $objectId)->orderBy('date', 'desc')->paginate(20, ['*'], 'comet')->appends(request()->query());

        $fakeUser = (object) ['name' => $objectName ?? $slug, 'slug' => $slug, 'username' => null];

        return view('observations.show', [
            'user' => $fakeUser,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
            'objectFilter' => true,
            'objectName' => $objectName,
        ]);
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
        if ($slugified && $slugified !== $lower) $candidates[] = $slugified;
        $nospace = str_replace(' ', '', $lower);
        if ($nospace !== $lower) $candidates[] = $nospace;
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
        if (! $objectId) {
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

        return view('observations.show', [
            'user' => $user,
            'deepsky' => collect(),
            'comet' => $comet,
            'mode' => 'comet',
            'objectFilter' => true,
            'objectName' => $objectName,
        ]);
    }

    /**
     * Show comet-only observations for a specific observer
     */
    public function cometShow(string $slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();

        $comet = CometObservationsOld::where('observerid', $user->username)->orderBy('date', 'desc')->paginate(20, ['*'], 'comet')->appends(request()->query());

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
            $deepsky = ObservationsOld::where('observerid', $user->username)->where('hasDrawing', 1)->orderBy('date', 'desc')->paginate(20, ['*'], 'deepsky')->appends(request()->query());

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

        if (! $objectName) {
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

            return view('observations.show', [
                'user' => $fakeUser,
                'deepsky' => $deepsky,
                'comet' => collect(),
                'mode' => 'deepsky',
            ]);
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

        if (! $objectId) {
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
        if (! $objectName) {
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
        if (! $objectName) {
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

        return view('observations.show', [
            'user' => $user,
            'deepsky' => $deepsky,
            'comet' => collect(),
            'mode' => 'deepsky',
            'objectFilter' => true,
        ]);
    }
}
