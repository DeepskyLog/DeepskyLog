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
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Atlas;
use App\Models\DeepskyObject;
use App\Models\DeepskyType;
use App\Models\Constellation as ConstellationModel;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;

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
            // Provide a usable id for views: prefer numeric id but fall back to canonical name
            'id' => $record->id ?? ($record->name ?? null),
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
                // Remember the user's standard instrument set id (if any). We'll prefer
                // eyepieces from this set when computing magnifications.
                $instSet = $authUser?->standardInstrumentSet ?? null;

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

                    // Determine if the user has a default lens configured and load it.
                    $defaultLensId = $authUser?->stdlens ?? null;
                    try {
                        if (! $defaultLensId && Schema::hasColumn('users', 'preferences') && is_array($authUser?->preferences) && isset($authUser->preferences['aladin_default_lens'])) {
                            $defaultLensId = $authUser->preferences['aladin_default_lens'];
                        }
                    } catch (\Throwable $_) {
                        // ignore
                    }
                    $defaultLens = null;
                    $lensFactor = 1.0;
                    $defaultLensName = null;
                    if ($defaultLensId) {
                        try {
                            $defaultLens = \App\Models\Lens::where('id', $defaultLensId)->first();
                            if ($defaultLens) {
                                $lensFactor = $defaultLens->factor ?? 1.0;
                                // defensive: numeric and > 0
                                if (! is_numeric($lensFactor) || $lensFactor <= 0) { $lensFactor = 1.0; }
                                $defaultLensName = $defaultLens->name ?? null;
                            }
                        } catch (\Throwable $_) {
                            $defaultLens = null;
                        }
                    }

                    // If magnification not available, attempt a set of magnifications and pick best.
                    // Prefer magnifications that are actually producible by eyepieces from the
                    // user's standard instrument set when such a set is configured. If no
                    // standard set exists, fall back to a list of common magnifications.
                    if (! $mag && $sbobj !== null && $sqm !== null && $aperture) {
                        // Candidate magnifications used for the contrast calculation.
                        // We'll keep this around so the optimum-detection pass can
                        // fall back to the same candidates if no eyepieces from the
                        // selected set produce usable values.
                        $possible = [25, 50, 75, 100, 150, 200];
                        // Apply default lens factor to generic candidate magnifications so
                        // contrast calculation reflects the selected lens when present.
                        if ($lensFactor !== 1.0) {
                            $possible = array_map(fn($v) => (int) round($v * $lensFactor), $possible);
                        }
                        $possibleUsedForContrast = $possible;

                        // Try to derive possible magnifications from eyepieces in the
                        // standard instrument set (if present and instrument focal length available).
                        if ($instSet && $userInstrument?->focal_length_mm) {
                            try {
                                $setModel = $instSet;

                                if ($setModel && count($setModel->eyepieces) > 0) {
                                    $derived = [];
                                    // Only consider active eyepieces here to match the
                                    // behaviour later when building the epMap / display list.
                                    foreach ($setModel->eyepieces as $sep) {
                                        if ($sep->active && ! empty($sep->focal_length_mm) && $sep->focal_length_mm > 0) {
                                            // account for default lens factor when deriving achievable magnifications
                                            $derived[] = (int) round(($userInstrument->focal_length_mm / $sep->focal_length_mm) * $lensFactor);
                                        }
                                    }
                                    $derived = array_values(array_unique(array_filter($derived)));
                                    if (! empty($derived)) {
                                        $possible = $derived;
                                        $possibleUsedForContrast = $possible;
                                    }
                                }
                            } catch (\Throwable $_) {
                                // ignore and use default possible list
                            }
                        }

                        $mag = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possible);
                    }

                    if ($sbobj !== null && $sqm !== null && $aperture && $mag) {
                            // Show which eyepieces are used for contrast reserve calculation
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


                            // If user has a standard instrument set, use its eyepieces. When a
                            // standard set is configured we will NOT fall back to the user's
                            // entire eyepiece collection; this ensures the calculations only use
                            // equipment from the selected set.
                            $instSet = $authUser?->standardInstrumentSet ?? null;
                            // `standardInstrumentSet` may be stored as an InstrumentSet model
                            // or as an id. Normalize to an id so lookups succeed.
                            if ($instSet && is_object($instSet) && isset($instSet->id)) {
                                $instSet = $instSet->id;
                            }
                            $usedSetEyepieces = false;
                            if ($instSet) {
                                $set = \App\Models\InstrumentSet::where('id', $instSet)->first();
                                if ($set) {
                                    foreach ($set->eyepieces as $ep) {
                                        if ($ep->active && $ep->focal_length_mm) {
                                            $usedSetEyepieces = true;
                                            $ef = $ep->focal_length_mm;
                                            $eyepieceFocals[] = $ef;
                                            // Attempt to include slugs so the view can link to eyepiece pages
                                            $userSlug = null;
                                            try {
                                                $userSlug = $ep->user?->slug ?? \App\Models\User::where('id', $ep->user_id)->value('slug');
                                            } catch (\Throwable $_) {
                                                $userSlug = null;
                                            }
                                            // Build display name; if a default lens is set, append the lens name
                                            $displayName = $ep->fullName();
                                            if (! empty($defaultLensName)) {
                                                $displayName = $displayName . ' (' . $defaultLensName . ')';
                                            }
                                            $eyepiecesForDisplay[] = [
                                                'name' => $displayName,
                                                'focal' => $ef,
                                                'slug' => $ep->slug ?? null,
                                                'user_slug' => $userSlug,
                                            ];
                                        }
                                    }
                                }
                            }

                            // If we did not get eyepieces from a standard set, fall back
                            // to the user's active eyepieces so we can at least display
                            // names even when no default instrument is configured.
                            if (empty($eyepiecesForDisplay)) {
                                try {
                                    $userEps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->get();
                                    foreach ($userEps as $ep) {
                                        if (! empty($ep->focal_length_mm)) {
                                            $ef = $ep->focal_length_mm;
                                            $eyepieceFocals[] = $ef;
                                        } else {
                                            $ef = null;
                                        }
                                        $userSlug = null;
                                        try {
                                            $userSlug = $ep->user?->slug ?? \App\Models\User::where('id', $ep->user_id)->value('slug');
                                        } catch (\Throwable $_) {
                                            $userSlug = null;
                                        }
                                        $displayName = $ep->fullName() ?? $ep->name ?? null;
                                        if (! empty($defaultLensName) && ! empty($displayName)) {
                                            $displayName = $displayName . ' (' . $defaultLensName . ')';
                                        }
                                        $eyepiecesForDisplay[] = [
                                            'name' => $displayName,
                                            'focal' => $ef,
                                            'slug' => $ep->slug ?? null,
                                            'user_slug' => $userSlug,
                                        ];
                                    }
                                } catch (\Throwable $_) {
                                    // ignore
                                }
                            }

                            // IMPORTANT: when a standard instrument set is configured we
                            // must only use eyepieces from that set for the calculation.
                            // Do not fall back to the user's broader eyepiece collection here.
                            // If the set contains no usable eyepieces, the calculation will
                            // not produce an optimum magnification (behaviour requested).

                            // Build a mapping from magnification -> eyepieces (that generated that mag)
                            if (! empty($eyepieceFocals) && $userInstrument?->focal_length_mm) {
                                foreach ($eyepiecesForDisplay as $epInfo) {
                                    $ef = $epInfo['focal'];
                                    if ($ef > 0) {
                                        // account for default lens factor in produced magnification
                                        $m = (int) round(($userInstrument->focal_length_mm / $ef) * $lensFactor);
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

                                // If no eyepieces are available, fall back to the same candidate magnifications used for contrast reserve
                                if (empty($possibleMags) && !empty($possibleUsedForContrast)) {
                                    $possibleMags = $possibleUsedForContrast;
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
                                // If we could not map eyepieces to mags (empty epMap) but
                                // we collected eyepieces for display, show those so names
                                // appear in the view even without a configured instrument.
                                if (empty($finalEps) && ! empty($eyepiecesForDisplay)) {
                                    // Deduplicate eyepiecesForDisplay as well
                                    $uniq = [];
                                    $finalEps = [];
                                    foreach ($eyepiecesForDisplay as $e) {
                                        $k = ($e['name'] ?? '') . '|' . ($e['focal'] ?? '');
                                        if (! isset($uniq[$k])) {
                                            $uniq[$k] = true;
                                            $finalEps[] = $e;
                                        }
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

        // Load alternative names (aliases) from legacy objectnames table when available.
        // Use the mysqlOld connection which contains the legacy `objectnames` table.
        $alternatives = [];
        try {
            if (! empty($record->name)) {
                $rows = DB::connection('mysql')
                    ->table('objectnames')
                    ->select(['objectname', 'altname', 'catalog', 'catindex'])
                    ->where('objectname', $record->name)
                    ->orWhere('altname', $record->name)
                    ->get();

                foreach ($rows as $r) {
                    // Collect non-empty alternative strings excluding the canonical name
                    if (! empty($r->altname) && strcasecmp($r->altname, $record->name) !== 0) {
                        $alternatives[] = $r->altname;
                    }
                    // Also include objectname values that differ in case/spacing if useful
                    if (! empty($r->objectname) && strcasecmp($r->objectname, $record->name) !== 0) {
                        $alternatives[] = $r->objectname;
                    }
                }

                // Deduplicate while preserving order
                $alternatives = array_values(array_unique(array_filter($alternatives)));

                // sort alphabetically
                sort($alternatives, SORT_NATURAL | SORT_FLAG_CASE);
            }
        } catch (\Throwable $_) {
            // Fail silently; alternatives remain an empty array
            $alternatives = [];
        }

        // Determine a canonical slug for this object so primary name links to the correct canonical page.
        // Prefer a slug property on the record if present, else slugify the canonical name.
        $canonicalSlug = null;
        try {
            if (! empty($record->slug)) {
                $canonicalSlug = (string) $record->slug;
            } elseif (! empty($record->name)) {
                $canonicalSlug = \Illuminate\Support\Str::slug($record->name, '-');
            }
        } catch (\Throwable $_) {
            $canonicalSlug = null;
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

        // Provide minimal defaults for Aladin Lite preview: raw coordinates and user instrument/eyepiece hints
        $aladinDefaults = null;
        try {
            if (isset($record->ra) && isset($record->decl)) {
                // raw numeric RA/Dec from legacy record are stored as strings like "00 42 44.3" or decimal degrees
                // Attempt to parse to decimal degrees using DeepskyObject helper if available
                $rawRa = $record->ra;
                $rawDec = $record->decl;
                // If DeepskyObject has a helper to produce decimal degrees, use it; otherwise pass raw strings
                $raDeg = null;
                $decDeg = null;
                try {
                    if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                        $raDeg = \App\Models\DeepskyObject::raToDecimal($rawRa);
                        $decDeg = \App\Models\DeepskyObject::decToDecimal($rawDec);
                    }
                } catch (\Throwable $_) {
                    // ignore conversion errors
                }

                $aladinDefaults = [
                    'ra_raw' => $rawRa,
                    'dec_raw' => $rawDec,
                    'ra_deg' => $raDeg,
                    'dec_deg' => $decDeg,
                ];
            }

            // Add simple instrument / eyepiece hints if authenticated user has defaults
            $authUser = Auth::user();
                if ($authUser) {
                $userInstrument = $authUser->standardInstrument ?? null;
                $inst = null;
                if ($userInstrument) {
                    $inst = [
                        'aperture_mm' => $userInstrument->aperture_mm ?? null,
                        'focal_length_mm' => $userInstrument->focal_length_mm ?? null,
                        'fixedMagnification' => $userInstrument->fixedMagnification ?? null,
                    ];
                }

                // pick a default eyepiece: prefer first of standard set or user's eyepieces
                $ep = null;
                $instSet = $authUser?->standardInstrumentSet ?? null;
                // Normalize to id if a model was returned
                if ($instSet && is_object($instSet) && isset($instSet->id)) {
                    $instSet = $instSet->id;
                }
                if ($instSet) {
                    $set = \App\Models\InstrumentSet::where('id', $instSet)->first();
                    if ($set && count($set->eyepieces) > 0) {
                        $first = $set->eyepieces[0];
                        $ep = [
                            'focal_length_mm' => $first->focal_length_mm ?? null,
                            'apparent_fov_deg' => $first->apparentFOV ?? null,
                        ];
                    }
                }
                if (! $ep && $authUser) {
                    $userEps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->first();
                    if ($userEps) {
                        $ep = [
                            'focal_length_mm' => $userEps->focal_length_mm ?? null,
                            'apparent_fov_deg' => $userEps->apparentFOV ?? null,
                        ];
                    }
                }

                if ($aladinDefaults === null) { $aladinDefaults = []; }
                $aladinDefaults['instrument'] = $inst;
                $aladinDefaults['eyepiece'] = $ep;
                // Provide object diameter in arcminutes (view derives this from legacy record diam1 which is arcseconds)
                try {
                    // record->diam1 stored in arcseconds in legacy table; convert to arcminutes with one decimal
                    if (isset($record->diam1) && is_numeric($record->diam1) && $record->diam1 > 0) {
                        $aladinDefaults['object_diam_arcmin'] = round(($record->diam1 / 60.0), 1);
                    } else {
                        $aladinDefaults['object_diam_arcmin'] = null;
                    }
                } catch (\Throwable $_) {
                    $aladinDefaults['object_diam_arcmin'] = null;
                }
            }
            // If user is not authenticated, still expose object diameter to aladinDefaults
            if (! isset($aladinDefaults['object_diam_arcmin'])) {
                try {
                    if (isset($record->diam1) && is_numeric($record->diam1) && $record->diam1 > 0) {
                        $aladinDefaults['object_diam_arcmin'] = round(($record->diam1 / 60.0), 1);
                    } else {
                        $aladinDefaults['object_diam_arcmin'] = null;
                    }
                } catch (\Throwable $_) {
                    $aladinDefaults['object_diam_arcmin'] = null;
                }
            }
        } catch (\Throwable $_) {
            // defensive: don't break rendering
        }
        // Ephemerides: compute rise/transit/set, best time and max altitude when possible
        $ephemerides = null;
        try {
            $authUser = Auth::user();
            $userLocation = $authUser?->standardLocation ?? null;
            if ($userLocation && isset($record->ra) && isset($record->decl)) {
                // Use a default date or query param 'ephem_date' if provided
                $date = \Carbon\Carbon::now();
                try {
                    $reqDate = request()->query('ephem_date');
                    if ($reqDate) {
                        $parsed = \Carbon\Carbon::parse($reqDate);
                        if ($parsed) {
                            $date = $parsed;
                        }
                    }
                } catch (\Throwable $_) {}
                // Use user's timezone if available
                try { $date = $date->timezone($userLocation->timezone ?? config('app.timezone')); } catch (\Throwable $_) {}

                $geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);

                // Prepare target and equatorial coordinates
                $target = new AstroTarget();
                // record->ra/decl can be strings like "00 42 44.3" or decimals; try conversion helper if available
                $raDeg = null; $decDeg = null;
                try {
                    if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                        $raDeg = \App\Models\DeepskyObject::raToDecimal($record->ra);
                        $decDeg = \App\Models\DeepskyObject::decToDecimal($record->decl);
                    }
                } catch (\Throwable $_) { $raDeg = null; $decDeg = null; }

                if ($raDeg === null || $decDeg === null) {
                    // fallback: try numeric cast (assume stored as degrees)
                    $raDeg = is_numeric($record->ra) ? (float)$record->ra : null;
                    $decDeg = is_numeric($record->decl) ? (float)$record->decl : null;
                }

                if ($raDeg !== null && $decDeg !== null) {
                    $equa = new EquatorialCoordinates($raDeg, $decDeg);
                    $target->setEquatorialCoordinates($equa);

                    $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
                    $deltaT = Time::deltaT($date);

                    $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);

                    // Localize results to user's timezone where applicable
                    $transit = null; $rising = null; $setting = null; $bestTime = null; $maxHeightAtNight = null; $maxHeight = null;
                    try { $transit = $target->getTransit(); } catch (\Throwable $_) { $transit = null; }
                    try { $rising = $target->getRising(); } catch (\Throwable $_) { $rising = null; }
                    try { $setting = $target->getSetting(); } catch (\Throwable $_) { $setting = null; }
                    try { $bestTime = $target->getBestTimeToObserve(); } catch (\Throwable $_) { $bestTime = null; }
                    try { $maxHeightAtNight = $target->getMaxHeightAtNight(); } catch (\Throwable $_) { $maxHeightAtNight = null; }
                    try { $maxHeight = $target->getMaxHeight(); } catch (\Throwable $_) { $maxHeight = null; }

                    // Format timezone-aware strings when Carbon instances returned
                    $tz = $userLocation->timezone ?? config('app.timezone');
                    if ($transit instanceof \DateTimeInterface) { try { $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $transit = (string)$transit; } }
                    if ($rising instanceof \DateTimeInterface) { try { $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $rising = (string)$rising; } }
                    if ($setting instanceof \DateTimeInterface) { try { $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $setting = (string)$setting; } }
                    if ($bestTime instanceof \DateTimeInterface) { try { $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $bestTime = (string)$bestTime; } }
                    // The astronomy library may return Coordinate objects. Convert to numeric values
                    try {
                        if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) {
                            $maxHeightAtNight = $maxHeightAtNight->getCoordinate();
                        }
                    } catch (\Throwable $_) {}
                    try {
                        if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) {
                            $maxHeight = $maxHeight->getCoordinate();
                        }
                    } catch (\Throwable $_) {}
                    if (is_numeric($maxHeightAtNight)) $maxHeightAtNight = round($maxHeightAtNight, 1);
                    if (is_numeric($maxHeight)) $maxHeight = round($maxHeight, 1);

                    // Altitude graph HTML provided by target if available
                    $altitudeGraph = null;
                    try { $altitudeGraph = $target->altitudeGraph($geo_coords, $date); } catch (\Throwable $_) { $altitudeGraph = null; }

                    // Additional moon & sun information for the aside
                    $moonPhase = null;
                    $moonIlluminated = null;
                    $nextNewMoonDate = null;
                    try {
                        $moon = new AstroMoon();
                        $moonPhase = $moon->getPhaseRatio($date);
                        $moonIlluminated = $moon->illuminatedFraction($date);
                        $nextNewMoon = $moon->newMoonDate($date);
                        if ($nextNewMoon instanceof \DateTimeInterface) {
                            try { $nextNewMoonDate = \Carbon\Carbon::instance($nextNewMoon)->timezone($tz)->toDateString(); } catch (\Throwable $_) { $nextNewMoonDate = (string)$nextNewMoon; }
                        }
                    } catch (\Throwable $_) {
                        $moonPhase = null; $moonIlluminated = null; $nextNewMoonDate = null;
                    }

                    $sunrise = $sunset = $nauticalBegin = $nauticalEnd = $astroBegin = $astroEnd = null;
                    try {
                        $sunInfo = @date_sun_info($date->copy()->startOfDay()->timestamp, $userLocation->latitude, $userLocation->longitude);
                        if (is_array($sunInfo)) {
                            if (! empty($sunInfo['sunrise'])) { try { $sunrise = \Carbon\Carbon::createFromTimestamp($sunInfo['sunrise'])->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $sunrise = null; } }
                            if (! empty($sunInfo['sunset'])) { try { $sunset = \Carbon\Carbon::createFromTimestamp($sunInfo['sunset'])->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $sunset = null; } }
                            if (! empty($sunInfo['nautical_twilight_begin'])) { try { $nauticalBegin = \Carbon\Carbon::createFromTimestamp($sunInfo['nautical_twilight_begin'])->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $nauticalBegin = null; } }
                            if (! empty($sunInfo['nautical_twilight_end'])) { try { $nauticalEnd = \Carbon\Carbon::createFromTimestamp($sunInfo['nautical_twilight_end'])->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $nauticalEnd = null; } }
                            if (! empty($sunInfo['astronomical_twilight_begin'])) { try { $astroBegin = \Carbon\Carbon::createFromTimestamp($sunInfo['astronomical_twilight_begin'])->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $astroBegin = null; } }
                            if (! empty($sunInfo['astronomical_twilight_end'])) { try { $astroEnd = \Carbon\Carbon::createFromTimestamp($sunInfo['astronomical_twilight_end'])->timezone($tz)->isoFormat('HH:mm'); } catch (\Throwable $_) { $astroEnd = null; } }
                        }
                    } catch (\Throwable $_) {
                        // ignore
                    }

                    $ephemerides = [
                        'date' => $date->timezone($tz)->toDateString(),
                        'rising' => $rising,
                        'transit' => $transit,
                        'setting' => $setting,
                        'best_time' => $bestTime,
                        'max_height_at_night' => $maxHeightAtNight,
                        'max_height' => $maxHeight,
                        'altitude_graph' => $altitudeGraph,
                        'moon_phase_ratio' => $moonPhase,
                        'moon_illuminated' => $moonIlluminated,
                        'next_new_moon' => $nextNewMoonDate,
                        'sunrise' => $sunrise,
                        'sunset' => $sunset,
                        'nautical_twilight_begin' => $nauticalBegin,
                        'nautical_twilight_end' => $nauticalEnd,
                        'astronomical_twilight_begin' => $astroBegin,
                        'astronomical_twilight_end' => $astroEnd,
                    ];
                }
            }
        } catch (\Throwable $_) {
            $ephemerides = null;
        }

        // Also expose available instruments, eyepieces and lenses for the Aladin preview selects
        $availableInstruments = [];
        $availableEyepieces = [];
        $availableLenses = [];
        $selectedInstrumentId = null;
        $selectedEyepieceId = null;
        $selectedLensId = null;
        try {
            $authUser = Auth::user();
            if ($authUser) {
                // Read persisted selections from user columns or preferences json
                try {
                    if (Schema::hasColumn('users', 'stdtelescope') && $authUser->stdtelescope) {
                        $selectedInstrumentId = $authUser->stdtelescope;
                    } elseif ($authUser->standardInstrument) {
                        $selectedInstrumentId = $authUser->standardInstrument->id ?? null;
                    }
                    if (Schema::hasColumn('users', 'stdeyepiece') && $authUser->stdeyepiece) {
                        $selectedEyepieceId = $authUser->stdeyepiece;
                    } elseif (Schema::hasColumn('users', 'preferences') && is_array($authUser->preferences) && isset($authUser->preferences['aladin_default_eyepiece'])) {
                        $selectedEyepieceId = $authUser->preferences['aladin_default_eyepiece'];
                    }
                    if (Schema::hasColumn('users', 'stdlens') && $authUser->stdlens) {
                        $selectedLensId = $authUser->stdlens;
                    } elseif (Schema::hasColumn('users', 'preferences') && is_array($authUser->preferences) && isset($authUser->preferences['aladin_default_lens'])) {
                        $selectedLensId = $authUser->preferences['aladin_default_lens'];
                    }
                } catch (\Throwable $_) {
                    // ignore preference read errors
                }
                // Instruments: expose user's active instruments
                $instrs = \App\Models\Instrument::where('user_id', $authUser->id)->where('active', 1)->get();
                foreach ($instrs as $i) {
                    $availableInstruments[] = [
                        'id' => $i->id,
                        'name' => $i->fullName() ?? $i->name,
                        'focal_length_mm' => $i->focal_length_mm ?? null,
                        'aperture_mm' => $i->aperture_mm ?? null,
                        'fixedMagnification' => $i->fixedMagnification ?? null,
                        // Include flip/flop flags so client can mirror preview appropriately
                        'flip_image' => isset($i->flip_image) ? boolval($i->flip_image) : false,
                        'flop_image' => isset($i->flop_image) ? boolval($i->flop_image) : false,
                    ];
                }

                // Eyepieces: prefer eyepieces from the user's standard instrument set
                // when a default set is configured. This ensures the Aladin preview
                // selects only eyepieces that belong to the selected set.
                $instSet = $authUser?->standardInstrumentSet ?? null;
                if ($instSet) {
                    $setModel = \App\Models\InstrumentSet::where('id', $instSet)->first();
                    if ($setModel) {
                        foreach ($setModel->eyepieces as $ep) {
                            if ($ep->active) {
                                $availableEyepieces[] = [
                                    'id' => $ep->id,
                                    'name' => $ep->fullName() ?? $ep->name,
                                    'focal_length_mm' => $ep->focal_length_mm ?? null,
                                    'apparent_fov_deg' => $ep->apparentFOV ?? null,
                                ];
                            }
                        }
                    }
                }

                // If no standard set or the set yielded no eyepieces, fall back to user's eyepieces
                if (empty($availableEyepieces)) {
                    $eps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->get();
                    foreach ($eps as $ep) {
                        $availableEyepieces[] = [
                            'id' => $ep->id,
                            'name' => $ep->fullName() ?? $ep->name,
                            'focal_length_mm' => $ep->focal_length_mm ?? null,
                            'apparent_fov_deg' => $ep->apparentFOV ?? null,
                        ];
                    }
                }
                // Lenses: expose user's active lenses
                $lns = \App\Models\Lens::where('user_id', $authUser->id)->where('active', 1)->get();
                foreach ($lns as $ln) {
                    $availableLenses[] = [
                        'id' => $ln->id,
                        'name' => $ln->fullName() ?? $ln->name,
                        'focal_length_mm' => $ln->focal_length_mm ?? null,
                        'factor' => $ln->factor ?? null,
                    ];
                }
                // Sort available lists for better UX: instruments by aperture desc, eyepieces by focal_length_mm desc, lenses by factor desc
                usort($availableInstruments, function($a, $b) {
                    $av = $a['aperture_mm'] ?? null; $bv = $b['aperture_mm'] ?? null;
                    if ($av === $bv) return 0;
                    if ($av === null) return 1;
                    if ($bv === null) return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                usort($availableEyepieces, function($a, $b) {
                    $av = $a['focal_length_mm'] ?? null; $bv = $b['focal_length_mm'] ?? null;
                    if ($av === $bv) return 0;
                    if ($av === null) return 1;
                    if ($bv === null) return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                usort($availableLenses, function($a, $b) {
                    $av = $a['factor'] ?? null; $bv = $b['factor'] ?? null;
                    if ($av === $bv) return 0;
                    if ($av === null) return 1;
                    if ($bv === null) return -1;
                    return ($av > $bv) ? -1 : 1;
                });
            }
        } catch (\Throwable $_) {
            $availableInstruments = [];
            $availableEyepieces = [];
            $availableLenses = [];
        }

    // Debug: log which record was resolved for this slug so we can verify id/name mapping
    try {
        Log::info('ObjectController: resolved record for show()', [
            'requested_slug' => $slug,
            'resolved_name' => $record->name ?? null,
            'resolved_id' => $record->id ?? null,
            'resolved_type' => $type ?? null,
        ]);
    } catch (\Throwable $_) {
        // ignore logging failures
    }

    return response()->view('object.show', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName', 'atlasPage', 'atlasName', 'alternatives', 'canonicalSlug', 'aladinDefaults', 'availableInstruments', 'availableEyepieces', 'availableLenses', 'selectedInstrumentId', 'selectedEyepieceId', 'selectedLensId', 'ephemerides'));
    }
}
