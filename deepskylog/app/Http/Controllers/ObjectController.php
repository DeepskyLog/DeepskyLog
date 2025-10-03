<?php

namespace App\Http\Controllers;

// Legacy models removed: access legacy tables via DB queries instead of Old models
use App\Models\Planet;
use App\Models\LunarFeature;
use App\Models\Moon;
use App\Models\Asteroid;
use App\Models\SearchIndex;
use App\Models\User;
// objectnames legacy table accessed via DB facade
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class ObjectController extends Controller
{
    /**
     * Show an object by slug (or id). Uses the same layout as session.show.
     * The controller will auto-detect the underlying object type by checking in
     * this order: search_index, planets, moons, lunar_features, cometobjects, objects (deepsky), asteroids
     */
    public function show(string $slug)
    {
        $record = null;
        $type = null;

        // Build a list of slug candidates to try (case-insensitive and normalized variants)
        $candidates = [];
        if (! empty($slug)) {
            $raw = (string) $slug;
            $lower = mb_strtolower($raw);
            $candidates[] = $lower;

            // slugify using Laravel helper
            $slugified = \Illuminate\Support\Str::slug($raw, '-');
            if ($slugified && $slugified !== $lower) {
                $candidates[] = $slugified;
            }

            // remove spaces
            $nospace = str_replace(' ', '', $lower);
            if ($nospace && $nospace !== $lower) {
                $candidates[] = $nospace;
            }

            // try removing any leading zeros in numeric part (e.g. m-031 -> m-31)
            $candidates[] = preg_replace('/(?<=\D)0+(?=\d+)/', '', $lower);

            // try dash/no-dash variants (e.g. m-31 / m31)
            $candidates[] = str_replace('-', '', $lower);
            $candidates[] = str_replace(' ', '-', $lower);

            // unique candidates, keep order
            $candidates = array_values(array_unique(array_filter($candidates)));
        }

        // Fast path: accept canonical slugs (preferred). Check objectnames.slug then objects.slug.
        if (! empty($slug)) {
            // Try all slug candidate variants against objectnames.slug
            $on = null;
            foreach ($candidates as $cand) {
                // 1) exact slug match
                $on = DB::table('objectnames')->where('slug', $cand)->first();
                if ($on) { break; }

                // 2) exact name/altname (case-insensitive)
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) { break; }

                // 3) name/altname with spaces/dashes removed (e.g. M 31 -> m31)
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(REPLACE(objectname, " ", "")) = ?', [$cand])
                    ->orWhereRaw('LOWER(REPLACE(altname, " ", "")) = ?', [$cand])
                    ->orWhereRaw('LOWER(REPLACE(objectname, "-", "")) = ?', [$cand])
                    ->orWhereRaw('LOWER(REPLACE(altname, "-", "")) = ?', [$cand])
                    ->first();
                if ($on) { break; }
            }
            if ($on) {
                // Found a canonical alias mapping to a deepsky object
                    $record = DB::table('objects')->where('name', $on->objectname)->first();
                if ($record) {
                    $type = 'deepsky';
                }
            }

            if (! $record) {
                // Try slug candidates on objects.slug too
                $o = null;
                foreach ($candidates as $cand) {
                    $o = DB::table('objects')->where('slug', $cand)->first();
                    if ($o) { break; }
                }
                if ($o) {
                    // If the objects table contains the slug, load via ObjectsOld
                        $record = DB::table('objects')->where('name', $o->name)->first();
                    if ($record) {
                        $type = 'deepsky';
                    }
                }

                // planets
                if (! $record && Schema::hasColumn('planets', 'slug')) {
                    $p = DB::table('planets')->where('slug', $slug)->first();
                    if ($p) {
                        $record = Planet::where('id', $p->id)->first();
                        if ($record) { $type = 'planet'; }
                    }
                }

                // moons
                if (! $record && Schema::hasColumn('moons', 'slug')) {
                    $m = DB::table('moons')->where('slug', $slug)->first();
                    if ($m) {
                        $record = Moon::where('id', $m->id)->first();
                        if ($record) { $type = 'moon'; }
                    }
                }

                // lunar features
                if (! $record && Schema::hasColumn('lunar_features', 'slug')) {
                    $lf = DB::table('lunar_features')->where('slug', $slug)->first();
                    if ($lf) {
                        $record = LunarFeature::where('id', $lf->id)->first();
                        if ($record) { $type = 'lunar_feature'; }
                    }
                }

                // asteroids
                if (! $record && Schema::hasColumn('asteroids', 'slug')) {
                    $a = DB::table('asteroids')->where('slug', $slug)->first();
                    if ($a) {
                        $record = Asteroid::where('id', $a->id)->first();
                        if ($record) { $type = 'asteroid'; }
                    }
                }
            }
        }

        // If slug fast-path resolved an object, skip the heavier lookup and render directly.
        if ($record) {
            goto render_object;
        }

    // Prepare primary key variable for further resolution attempts
    $pk = $slug;

    // If the slug fast-path didn't find a record, try the broader resolution logic.
    if (! $record) {
            // Try to resolve via search_index first (helps with aliases and normalized names)
            $si = SearchIndex::where('name', $slug)->orWhere('name_normalized', mb_strtolower($slug))->first();
            if ($si) {
            // normalize some common table names to our internal types
            switch ($si->source_table) {
                case 'objects':
                    $type = 'deepsky';
                    break;
                case 'cometobjects':
                    $type = 'comet';
                    break;
                case 'planets':
                    $type = 'planet';
                    break;
                case 'moons':
                    $type = 'moon';
                    break;
                case 'lunar_features':
                    $type = 'lunar_feature';
                    break;
                case 'asteroids':
                    $type = 'asteroid';
                    break;
                default:
                    $type = $si->source_table;
            }

            $pk = $si->source_pk;
            } else {
                $pk = $slug;
            }

            // If we resolved a type from search_index, use the existing switch logic
            if ($type) {
                switch ($type) {
                    case 'deepsky':
                    case 'objects':
                        if (is_numeric($pk)) {
                            $record = DB::table('objects')->where('id', $pk)->first();
                        } else {
                            $record = DB::table('objects')->where('name', $pk)->first();
                        }
                        break;
                    case 'comet':
                    case 'cometobjects':
                        if (is_numeric($pk)) {
                            $record = DB::table('cometobjects')->where('id', $pk)->first();
                        } else {
                            $record = DB::table('cometobjects')->where('name', $pk)->first();
                        }
                        break;
                    case 'planet':
                    case 'planets':
                        if (is_numeric($pk)) {
                            $record = Planet::find($pk);
                        } else {
                            $record = Planet::where('name', $pk)->first();
                        }
                        break;
                    case 'lunar_feature':
                    case 'lunar_features':
                        if (is_numeric($pk)) {
                            $record = LunarFeature::find($pk);
                        } else {
                            $record = LunarFeature::where('name', $pk)->first();
                        }
                        break;
                    case 'moon':
                    case 'moons':
                        if (is_numeric($pk)) {
                            $record = Moon::find($pk);
                        } else {
                            $record = Moon::where('name', $pk)->first();
                        }
                        break;
                    case 'asteroid':
                    case 'asteroids':
                        if (is_numeric($pk)) {
                            $record = Asteroid::find($pk);
                        } else {
                            $record = Asteroid::where('name', $pk)->first();
                        }
                        break;
                    default:
                        // fall through to detection below
                        $record = null;
                }
            }

            // If we don't yet have a record, try a best-effort detection across tables.
            if (! $record) {
                // Planets
                $record = Planet::where('name', $pk)->first();
                if ($record) {
                    $type = 'planet';
                }

                // Moons
                if (! $record) {
                    $record = Moon::where('name', $pk)->first();
                    if ($record) {
                        $type = 'moon';
                    }
                }

                // Lunar features
                if (! $record) {
                    $record = LunarFeature::where('name', $pk)->first();
                    if ($record) {
                        $type = 'lunar_feature';
                    }
                }

                // Comets (numeric ids or names) and Deepsky objects.
                // Try objectnames aliases first for non-numeric slugs because many
                // canonical deepsky names and aliases are stored in that table.
                if (! $record) {
                    $on = null;
                    if (! is_numeric($pk)) {
                        $on = DB::table('objectnames')->where('objectname', $pk)->orWhere('altname', $pk)->first();
                    }

                    if ($on) {
                        // objectnames.objectname is canonical for objects (legacy table)
                        $record = DB::table('objects')->where('name', $on->objectname)->first();
                        if ($record) {
                            $type = 'deepsky';
                        }
                    }

                    if (! $record) {
                        // check comets by id or name
                        if (is_numeric($pk)) {
                            $record = DB::table('cometobjects')->where('id', $pk)->first();
                        } else {
                            $record = DB::table('cometobjects')->where('name', $pk)->first();
                        }
                        if ($record) {
                            $type = 'comet';
                        }
                    }

                    if (! $record) {
                        // Finally fallback to direct deepsky lookup by name or id
                        if (is_numeric($pk)) {
                            $record = DB::table('objects')->where('id', $pk)->first();
                        } else {
                            $record = DB::table('objects')->where('name', $pk)->first();
                        }
                        if ($record) {
                            $type = 'deepsky';
                        }
                    }
                }

                // Asteroids
                if (! $record) {
                    if (is_numeric($pk)) {
                        $record = Asteroid::find($pk);
                    } else {
                        $record = Asteroid::where('name', $pk)->first();
                    }
                    if ($record) {
                        $type = 'asteroid';
                    }
                }
            }
        }

        // If we resolved a type from search_index, use the existing switch logic
        if ($type) {
            switch ($type) {
                case 'deepsky':
                case 'objects':
                    if (is_numeric($pk)) {
                            $record = DB::table('objects')->where('id', $pk)->first();
                    } else {
                            $record = DB::table('objects')->where('name', $pk)->first();
                    }
                    break;
                case 'comet':
                case 'cometobjects':
                    if (is_numeric($pk)) {
                            $record = DB::table('cometobjects')->where('id', $pk)->first();
                    } else {
                            $record = DB::table('cometobjects')->where('name', $pk)->first();
                    }
                    break;
                case 'planet':
                case 'planets':
                    if (is_numeric($pk)) {
                        $record = Planet::find($pk);
                    } else {
                        $record = Planet::where('name', $pk)->first();
                    }
                    break;
                case 'lunar_feature':
                case 'lunar_features':
                    if (is_numeric($pk)) {
                        $record = LunarFeature::find($pk);
                    } else {
                        $record = LunarFeature::where('name', $pk)->first();
                    }
                    break;
                case 'moon':
                case 'moons':
                    if (is_numeric($pk)) {
                        $record = Moon::find($pk);
                    } else {
                        $record = Moon::where('name', $pk)->first();
                    }
                    break;
                case 'asteroid':
                case 'asteroids':
                    if (is_numeric($pk)) {
                        $record = Asteroid::find($pk);
                    } else {
                        $record = Asteroid::where('name', $pk)->first();
                    }
                    break;
                default:
                    // fall through to detection below
                    $record = null;
            }
        }

        // If we don't yet have a record, try a best-effort detection across tables.
        if (! $record) {
            // Planets
            $record = Planet::where('name', $pk)->first();
            if ($record) {
                $type = 'planet';
            }

            // Moons
            if (! $record) {
                $record = Moon::where('name', $pk)->first();
                if ($record) {
                    $type = 'moon';
                }
            }

            // Lunar features
            if (! $record) {
                $record = LunarFeature::where('name', $pk)->first();
                if ($record) {
                    $type = 'lunar_feature';
                }
            }

            // Comets (numeric ids or names) and Deepsky objects.
            // Try objectnames aliases first for non-numeric slugs because many
            // canonical deepsky names and aliases are stored in that table.
                if (! $record) {
                    $on = null;
                    if (! is_numeric($pk)) {
                        $on = DB::table('objectnames')->where('objectname', $pk)->orWhere('altname', $pk)->first();
                    }

                if ($on) {
                    // objectnames.objectname is canonical for ObjectsOld
                        $record = DB::table('objects')->where('name', $on->objectname)->first();
                    if ($record) {
                        $type = 'deepsky';
                    }
                }

                if (! $record) {
                    // check comets by id or name
                    if (is_numeric($pk)) {
                            $record = DB::table('cometobjects')->where('id', $pk)->first();
                    } else {
                            $record = DB::table('cometobjects')->where('name', $pk)->first();
                    }
                    if ($record) {
                        $type = 'comet';
                    }
                }

                if (! $record) {
                    // Finally fallback to direct deepsky lookup by name or id
                    if (is_numeric($pk)) {
                            $record = DB::table('objects')->where('id', $pk)->first();
                    } else {
                            $record = DB::table('objects')->where('name', $pk)->first();
                    }
                    if ($record) {
                        $type = 'deepsky';
                    }
                }
            }

            // Asteroids
            if (! $record) {
                if (is_numeric($pk)) {
                    $record = Asteroid::find($pk);
                } else {
                    $record = Asteroid::where('name', $pk)->first();
                }
                if ($record) {
                    $type = 'asteroid';
                }
            }
        }

        if (! $record) {
            abort(404);
        }

        render_object:

        // Build a minimal $user-like object for links (use current authenticated user if available)
    $user = Auth::user() ?? (object) ['name' => $record->name ?? ($record->display_name ?? $slug), 'slug' => null, 'username' => null];

        // Map properties to variables used by session.show so the view can reuse layout
        $session = (object) [
            'name' => $record->name ?? ($record->display_name ?? $slug),
            'comments' => $record->description ?? ($record->notes ?? null),
            'preview' => null,
            'observerid' => null,
            'picture' => null,
            'begindate' => null,
            'enddate' => null,
            'slug' => $slug,
            'id' => $record->id ?? null,
        ];

        // Location and observations are not relevant; pass empty arrays where session.show expects them
        $location = null;
        $image = null;
        $observers = [];
        $totalObservations = 0;
        $observations = collect([]);
        $drawings = collect([]);
        $observerStats = [];
        $selectedObserverUsername = null;
        $selectedObserverName = null;

        // Enrich metadata for display if available
        if (isset($record->ra) && isset($record->decl)) {
            $session->ra = $record->ra;
            $session->decl = $record->decl;
        }

        // Provide a preview image if available in legacy storage paths
        if (! empty($record->picture)) {
            $image = asset('storage/'.$record->picture);
            $session->preview = $image;
        }

        return response()->view('object.show', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName'));
    }
}
