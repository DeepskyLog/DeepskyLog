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
use App\Models\Atlas;
use App\Models\DeepskyObject;
use App\Models\DeepskyType;
use App\Models\Constellation as ConstellationModel;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;

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
                    // Try canonical slug first
                    $p = DB::table('planets')->where('slug', $slug)->first();
                    if (! $p) {
                        // Try localized names for the current locale and fall back to any locale
                        $locale = app()->getLocale();
                        $trans = DB::table('object_name_translations')
                            ->where('name', 'like', $slug)
                            ->where(function ($q) use ($locale) {
                                $q->where('locale', $locale)->orWhereNull('locale');
                            })
                            ->first();
                        if ($trans) {
                            // Map canonical name back to planets table
                            $p = DB::table('planets')->where('name', $trans->objectname)->first();
                        }
                    }

                    if ($p) {
                        $record = Planet::where('id', $p->id)->first();
                        if ($record) { $type = 'planet'; }
                    }
                }

                // moons
                if (! $record && Schema::hasColumn('moons', 'slug')) {
                    // Try canonical slug first
                    $m = DB::table('moons')->where('slug', $slug)->first();
                    if (! $m) {
                        // Try localized names for the current locale and fall back to any locale
                        $locale = app()->getLocale();
                        $trans = DB::table('object_name_translations')
                            ->where('name', 'like', $slug)
                            ->where(function ($q) use ($locale) {
                                $q->where('locale', $locale)->orWhereNull('locale');
                            })
                            ->first();
                        if ($trans) {
                            // Map canonical name back to moons table
                            $m = DB::table('moons')->where('name', $trans->objectname)->first();
                        }
                    }

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
                        // objectnames.objectname is canonical for Objects
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

    // Normalize source type (used by the view) and map properties to variables
    // used by session.show so the view can reuse layout
    $sourceTypeLabel = null;
    $sourceTypeRaw = $type ?? null;
    // Preserve the legacy/record type code if present - initialize to avoid undefined variable errors
    $originalType = $record->type ?? null;
        // Special-case: if the resolved record or slug refers to the Sun, label it explicitly
        $lowerName = mb_strtolower($record->name ?? '');
        $lowerSlug = mb_strtolower($slug ?? '');
        if ($lowerName === 'sun' || $lowerSlug === 'sun') {
            $sourceTypeLabel = __('Sun');
            $sourceTypeRaw = 'sun';
        } elseif (! empty($type)) {
            switch ($type) {
                case 'deepsky':
                case 'objects':
                    $sourceTypeLabel = __('Deep-sky');
                    $sourceTypeRaw = 'object';
                    $originalType = $record->type ?? null;
                    break;
                case 'comet':
                case 'cometobjects':
                    $sourceTypeLabel = __('Comet');
                    $sourceTypeRaw = 'comet';
                    break;
                case 'planet':
                case 'planets':
                    $sourceTypeLabel = __('Planet');
                    $sourceTypeRaw = 'planet';
                    break;
                case 'moon':
                case 'moons':
                    $sourceTypeLabel = __('Moon');
                    $sourceTypeRaw = 'moon';
                    break;
                case 'lunar_feature':
                case 'lunar_features':
                    $sourceTypeLabel = __('Lunar feature');
                    $sourceTypeRaw = 'lunar_feature';
                    break;
                case 'asteroid':
                case 'asteroids':
                    $sourceTypeLabel = __('Asteroid');
                    $sourceTypeRaw = 'asteroid';
                    break;
                default:
                    // fallback: humanize the raw type value
                    $sourceTypeLabel = ucfirst(str_replace('_', ' ', $type));
                    $sourceTypeRaw = $type;
            }
        }

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
            // Human-friendly, translated label for display in views
            'source_type' => $sourceTypeLabel,
            // Raw machine-readable source type (useful for APIs/filters)
            'source_type_raw' => $sourceTypeRaw,
            // Keep the raw original legacy type code for reference; translation is resolved below when possible
            'original_type' => $originalType,
            // Additional deepsky metadata commonly used by views
            'mag' => null,
            'diam1' => null,
            'diam2' => null,
            'subr' => null,
            'pa' => null,
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
                    // Format RA/Dec for human readable output using runtime DeepskyObject
                    $session->ra = DeepskyObject::formatRa($record->ra);
                    $session->decl = DeepskyObject::formatDec($record->decl);
        }

        // Compute contrast reserve for deep-sky objects when possible
        $session->contrast_reserve = null;
        try {
            if (($type === 'deepsky' || $type === 'objects') && isset($record->mag)) {
                // Retrieve authenticated user defaults if available
                $authUser = Auth::user();

                $userLocation = $authUser?->standardLocation ?? null;
                $userInstrument = $authUser?->standardInstrument ?? null;

                if ($userLocation && $userInstrument) {
                    // Build AstronomyLibrary instance for today at location
                    $date = \Carbon\Carbon::now();
                    $coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                    $astrolib = new AstronomyLibrary($date, $coords, $userLocation->elevation ?? 0.0);

                    // Use Target helper from astronomy library
                    $target = new AstroTarget();
                    // legacy objects store diam1/diam2 in arcseconds; use these if present
                    $diam1 = $record->diam1 ?? null;
                    $diam2 = $record->diam2 ?? null;
                    if ($diam1 && $diam2) {
                        // set diameter in Target expects arcseconds
                        $target->setDiameter($diam1, $diam2);
                    }

                    // set magnitude
                    $m = ($record->mag && $record->mag != 99.9) ? $record->mag : null;
                    if ($m !== null) {
                        $target->setMagnitude($m);
                    }

                    // compute SBObj using library helper
                    $sbobj = $target->calculateSBObj();

                    // get SQM from user's location (Location model has getSqm())
                    $sqm = $userLocation->getSqm();

                    // instrument aperture in mm
                    $aperture = $userInstrument->aperture_mm ?? null;

                    // choose a reasonable magnification: use instrument fixedMagnification if set, else 1x as fallback
                    $mag = $userInstrument->fixedMagnification ?? null;
                    if (! $mag && $userInstrument->focal_length_mm && isset($record->typicalEyepieceFocal)) {
                        // edge case: estimate magnification if eyepiece focal length stored on record (rare)
                        $mag = round($userInstrument->focal_length_mm / $record->typicalEyepieceFocal);
                    }

                    // If magnification not available, attempt a set of common magnifications and pick best
                    if (! $mag && $sbobj !== null && $sqm !== null && $aperture) {
                        $possible = [25, 50, 75, 100, 150, 200];
                        $mag = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possible);
                    }

                    if ($sbobj !== null && $sqm !== null && $aperture && $mag) {
                        $contrast = $target->calculateContrastReserve($sbobj, $sqm, $aperture, $mag);
                        $session->contrast_reserve = is_numeric($contrast) ? round($contrast, 2) : null;

                        // Determine a human category based on numeric ranges
                        $cat = null;
                        if (is_numeric($session->contrast_reserve)) {
                            $c = (float) $session->contrast_reserve;
                            if ($c > 1.0) {
                                $cat = 'very_easy';
                            } elseif ($c > 0.5) {
                                $cat = 'easy';
                            } elseif ($c > 0.35) {
                                $cat = 'quite_difficult';
                            } elseif ($c > 0.1) {
                                $cat = 'difficult';
                            } elseif ($c > -0.2) {
                                $cat = 'questionable';
                            } else {
                                $cat = 'not_visible';
                            }
                        }
                        $session->contrast_reserve_category = $cat;

                        // Attach used location and instrument user-friendly strings
                        $session->contrast_used_location = $userLocation?->name ?? null;
                        $session->contrast_used_instrument = $userInstrument?->fullName() ?? ($userInstrument?->name ?? null);
                        // Compute optimum detection magnification using available eyepieces
                        try {
                            $eyepieceFocals = [];
                            // Prepare an array to store eyepiece display info
                            $eyepiecesForDisplay = [];
                            // Map magnification => eyepieces that produce it
                            $epMap = [];


                            // If user has a standard instrument set, use its eyepieces
                            $instSet = $authUser?->standardInstrumentSet ?? null;
                            if ($instSet) {
                                $set = \App\Models\InstrumentSet::where('id', $instSet)->first();
                                if ($set) {
                                    foreach ($set->eyepieces as $ep) {
                                        if ($ep->active && $ep->focal_length_mm) {
                                            $ef = $ep->focal_length_mm;
                                            $eyepieceFocals[] = $ef;
                                            $eyepiecesForDisplay[] = ['name' => $ep->fullName(), 'focal' => $ef];
                                        }
                                    }
                                }
                            }

                            // If no eyepieces from set, fall back to all active eyepieces of the user
                            if (empty($eyepieceFocals) && $authUser) {
                                $userEps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->get();
                                foreach ($userEps as $ep) {
                                    if ($ep->focal_length_mm) {
                                        $ef = $ep->focal_length_mm;
                                        $eyepieceFocals[] = $ef;
                                        $eyepiecesForDisplay[] = ['name' => $ep->fullName(), 'focal' => $ef];
                                    }
                                }
                            }

                            // Build a mapping from magnification -> eyepieces (that generated that mag)
                            if (! empty($eyepieceFocals) && $userInstrument?->focal_length_mm) {
                                foreach ($eyepiecesForDisplay as $epInfo) {
                                    $ef = $epInfo['focal'];
                                    if ($ef > 0) {
                                        $m = (int) round($userInstrument->focal_length_mm / $ef);
                                        if ($m > 0) {
                                            if (! isset($epMap[$m])) { $epMap[$m] = []; }
                                            $epMap[$m][] = $epInfo;
                                        }
                                    }
                                }
                            }

                            // Compute candidate magnifications given instrument focal length and eyepiece focals
                            // Prefer using keys from epMap (magnifications produced by actual eyepieces)
                            $possibleMags = [];
                            if (! empty($epMap)) {
                                $possibleMags = array_values(array_unique(array_keys($epMap)));
                            } elseif (! empty($eyepieceFocals) && $userInstrument?->focal_length_mm) {
                                foreach ($eyepieceFocals as $ef) {
                                    // avoid division by zero
                                    if ($ef > 0) {
                                        $possibleMags[] = (int) round($userInstrument->focal_length_mm / $ef);
                                    }
                                }
                                // ensure uniqueness and reasonable order
                                $possibleMags = array_values(array_unique(array_filter($possibleMags)));
                            }

                            if (! empty($possibleMags) && $sbobj !== null && $sqm !== null && $aperture) {
                                $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possibleMags);
                                $session->optimum_detection_magnification = $best ? (int) $best : null;
                            } else {
                                $session->optimum_detection_magnification = null;
                            }

                            // Prefer eyepieces that produce the computed best magnification.
                            // If the target algorithm returned a best magnification and we have
                            // eyepieces that produce that magnification, use those as the
                            // "used" eyepiece(s). Otherwise fall back to collecting all
                            // eyepieces that contributed to the candidate magnifications.
                            if (! empty($best) && isset($epMap[(int) $best])) {
                                $session->optimum_eyepieces = $epMap[(int) $best];
                            } else {
                                // Attach only eyepieces that contributed to the possibleMags
                                $selectedEps = [];
                                foreach ($possibleMags as $pm) {
                                    if (isset($epMap[$pm])) {
                                        foreach ($epMap[$pm] as $epInfo) {
                                            $selectedEps[] = $epInfo;
                                        }
                                    }
                                }
                                // Deduplicate by name+focal
                                $uniq = [];
                                $finalEps = [];
                                foreach ($selectedEps as $e) {
                                    $k = ($e['name'] ?? '') . '|' . ($e['focal'] ?? '');
                                    if (! isset($uniq[$k])) {
                                        $uniq[$k] = true;
                                        $finalEps[] = $e;
                                    }
                                }
                                $session->optimum_eyepieces = $finalEps;
                            }
                        } catch (\Throwable $_) {
                            $session->optimum_detection_magnification = null;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Defensive: do not break page rendering if astronomy library errors occur
            $session->contrast_reserve = null;
        }

    // Populate additional display fields if present on the legacy objects row.
    // Use common column names and fallbacks where appropriate.
    $session->mag = $record->mag;
    $session->diam1 = round($record->diam1 / 60.0, 1);
    $session->diam2 = round($record->diam2 / 60.0, 1);
    // surface brightness column sometimes abbreviated as subr or sb or surface_brightness
    $session->subr = round($record->subr, 1);
    // position angle: pa is common
    $session->pa = $record->pa;

        // If this is a deepsky object, attempt to resolve the legacy type code to a human-friendly label
        if (($type === 'deepsky' || $type === 'objects') && isset($record->type)) {
            try {
                // Normalize legacy type codes: trim and uppercase so lookups succeed regardless of case/whitespace
                $rawCode = trim((string) $record->type);
                $normCode = strtoupper($rawCode);

                $dst = DeepskyType::find($normCode);
                if ($dst && ! empty($dst->name)) {
                    $session->source_type = $dst->name;
                    // keep normalized raw code too
                    $session->source_type_raw = $normCode;
                } else {
                    // fallback: if no mapping, still expose the normalized code
                    $session->source_type_raw = $normCode;
                }
            } catch (\Throwable $_) {
                // ignore lookup errors
            }

            // Resolve constellation code to human name if possible
            if (! empty($record->con)) {
                try {
                    $cons = ConstellationModel::where('id', $record->con)->first();
                    if ($cons && ! empty($cons->name)) {
                        $session->constellation = $cons->name;
                        $session->constellation_code = $record->con;
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
            }
        }

        // Provide a preview image if available in legacy storage paths
        if (! empty($record->picture)) {
            $image = asset('storage/'.$record->picture);
            $session->preview = $image;
        }

        // Atlas page: if a user is logged in and they have a standardAtlasCode set,
        // check the objects table for a column with that name and read the stored page value.
    $atlasPage = null;
    $atlasName = null;
        try {
            $authUser = Auth::user();
            if ($authUser && ! empty($authUser->standardAtlasCode)) {
                $atlasCode = $authUser->standardAtlasCode;
                // Be defensive: only try to read the column if it exists on objects table
                if (Schema::hasColumn('objects', $atlasCode) && isset($record->{$atlasCode})) {
                    $atlasPage = $record->{$atlasCode};
                    // Try to load a human-friendly atlas name from Atlas model
                    try {
                        $atlasModel = Atlas::where('code', $atlasCode)->first();
                        if ($atlasModel && ! empty($atlasModel->name)) {
                            $atlasName = $atlasModel->name;
                        }
                    } catch (\Throwable $_) {
                        // ignore errors; atlasName remains null
                    }
                    // No legacy viewer URL is built; we only expose atlasName and atlasPage.
                }
            }
        } catch (\Throwable $e) {
            // Never let atlas enrichment break the object page; log silently in debugbar / logs if needed.
            $atlasPage = null;
        }

        return response()->view('object.show', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName', 'atlasPage', 'atlasName'));
    }
}
