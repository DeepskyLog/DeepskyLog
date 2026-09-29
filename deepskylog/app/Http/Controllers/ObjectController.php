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
use App\Services\CometCoordinates;
use App\Services\ObjectSearchIndex;
use App\Support\EphemerisDate;
use Illuminate\Support\Facades\Cache;

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
        $pk = $slug;
        $resolutionCacheKey = null;
        $tStart = microtime(true);
        try {
            Log::debug('ObjectController: show start', ['slug' => $slug]);
        } catch (\Throwable $_) {
        }

        // Build a list of slug candidates to try (case-insensitive and normalized variants)
        $candidates = [];
        if (!empty($slug)) {
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

        // Fastest path: reuse a short-lived slug resolution cache to avoid repeating
        // the expensive cross-table alias lookups for frequently viewed objects.
        if (!empty($slug)) {
            try {
                $resolutionCacheKey = 'object:resolve:v1:' . sha1(mb_strtolower((string) $slug));
                $cachedResolution = Cache::get($resolutionCacheKey);
                if (is_array($cachedResolution) && !empty($cachedResolution['type']) && array_key_exists('pk', $cachedResolution)) {
                    $type = $cachedResolution['type'];
                    $pk = $cachedResolution['pk'];

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
                        case 'moon':
                        case 'moons':
                            if (is_numeric($pk)) {
                                $record = Moon::find($pk);
                            } else {
                                $record = Moon::where('name', $pk)->first();
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
                        case 'asteroid':
                        case 'asteroids':
                            if (is_numeric($pk)) {
                                $record = Asteroid::find($pk);
                            } else {
                                $record = Asteroid::where('name', $pk)->first();
                            }
                            break;
                        default:
                            $record = null;
                    }
                }
            } catch (\Throwable $_) {
                $record = null;
            }
        }

        if ($record) {
            goto render_object;
        }

        // Fast path: accept canonical slugs (preferred). Check objectnames.slug then objects.slug.
        if (!empty($slug)) {
            // Try all slug candidate variants against objectnames.slug
            $on = null;
            foreach ($candidates as $cand) {
                // 1) exact slug match
                $on = DB::table('objectnames')->where('slug', $cand)->first();
                if ($on) {
                    break;
                }

                // 2) exact name/altname (case-insensitive)
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(objectname) = ?', [$cand])
                    ->orWhereRaw('LOWER(altname) = ?', [$cand])
                    ->first();
                if ($on) {
                    break;
                }

                // 3) name/altname with spaces/dashes removed (e.g. M 31 -> m31)
                $on = DB::table('objectnames')
                    ->whereRaw('LOWER(REPLACE(objectname, " ", "")) = ?', [$cand])
                    ->orWhereRaw('LOWER(REPLACE(altname, " ", "")) = ?', [$cand])
                    ->orWhereRaw('LOWER(REPLACE(objectname, "-", "")) = ?', [$cand])
                    ->orWhereRaw('LOWER(REPLACE(altname, "-", "")) = ?', [$cand])
                    ->first();
                if ($on) {
                    break;
                }
            }
            if ($on) {
                // Found a canonical alias mapping to a deepsky object
                $record = DB::table('objects')->where('name', $on->objectname)->first();
                if ($record) {
                    $type = 'deepsky';
                }
            }

            if (!$record) {
                // Try slug candidates on objects.slug too
                $o = null;
                foreach ($candidates as $cand) {
                    $o = DB::table('objects')->where('slug', $cand)->first();
                    if ($o) {
                        break;
                    }
                }
                if ($o) {
                    // If the objects table contains the slug, load via ObjectsOld
                    $record = DB::table('objects')->where('name', $o->name)->first();
                    if ($record) {
                        $type = 'deepsky';
                    }
                }

                // Try slug candidates on cometobjects.slug so comet pages can be addressed by slug
                if (!$record) {
                    $co = null;
                    foreach ($candidates as $cand) {
                        try {
                            $co = DB::table('cometobjects')->where('slug', $cand)->first();
                        } catch (\Throwable $_) {
                            $co = null;
                        }
                        if ($co)
                            break;
                    }
                    if ($co) {
                        $record = $co;
                        $type = 'comet';
                    }
                }

                // planets
                if (!$record && Schema::hasColumn('planets', 'slug')) {
                    // Try canonical slug first
                    $p = DB::table('planets')->where('slug', $slug)->first();
                    if (!$p) {
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
                        if ($record) {
                            $type = 'planet';
                        }
                    }
                }

                // moons
                if (!$record && Schema::hasColumn('moons', 'slug')) {
                    // Try canonical slug first
                    $m = DB::table('moons')->where('slug', $slug)->first();
                    if (!$m) {
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
                        if ($record) {
                            $type = 'moon';
                        }
                    }
                }

                // lunar features
                if (!$record && Schema::hasColumn('lunar_features', 'slug')) {
                    $lf = DB::table('lunar_features')->where('slug', $slug)->first();
                    if ($lf) {
                        $record = LunarFeature::where('id', $lf->id)->first();
                        if ($record) {
                            $type = 'lunar_feature';
                        }
                    }
                }

                // asteroids
                if (!$record && Schema::hasColumn('asteroids', 'slug')) {
                    $a = DB::table('asteroids')->where('slug', $slug)->first();
                    if ($a) {
                        $record = Asteroid::where('id', $a->id)->first();
                        if ($record) {
                            $type = 'asteroid';
                        }
                    }
                }
            }
        }

        // If slug fast-path resolved an object, skip the heavier lookup and render directly.
        if ($record) {
            try {
                $elapsed = round((microtime(true) - $tStart) * 1000, 2);
                Log::debug('ObjectController: fast-path hit', ['slug' => $slug, 'type' => $type ?? null, 'resolved_id' => $record->id ?? null, 'elapsed_ms' => $elapsed]);
            } catch (\Throwable $_) {
            }
            goto render_object;
        }

        // Prepare primary key variable for further resolution attempts
        $pk = $pk ?? $slug;

        // If the slug fast-path didn't find a record, try the broader resolution logic.
        if (!$record) {
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
            if (!$record) {
                // Planets
                $record = Planet::where('name', $pk)->first();
                if ($record) {
                    $type = 'planet';
                }

                // Moons
                if (!$record) {
                    $record = Moon::where('name', $pk)->first();
                    if ($record) {
                        $type = 'moon';
                    }
                }

                // Lunar features
                if (!$record) {
                    $record = LunarFeature::where('name', $pk)->first();
                    if ($record) {
                        $type = 'lunar_feature';
                    }
                }

                // Comets (numeric ids or names) and Deepsky objects.
                // Try objectnames aliases first for non-numeric slugs because many
                // canonical deepsky names and aliases are stored in that table.
                if (!$record) {
                    $on = null;
                    if (!is_numeric($pk)) {
                        $on = DB::table('objectnames')->where('objectname', $pk)->orWhere('altname', $pk)->first();
                    }

                    if ($on) {
                        // objectnames.objectname is canonical for objects (legacy table)
                        $record = DB::table('objects')->where('name', $on->objectname)->first();
                        if ($record) {
                            $type = 'deepsky';
                        }
                    }

                    if (!$record) {
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

                    if (!$record) {
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
                if (!$record) {
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
        if (!$record) {
            // Planets
            $record = Planet::where('name', $pk)->first();
            if ($record) {
                $type = 'planet';
            }

            // Moons
            if (!$record) {
                $record = Moon::where('name', $pk)->first();
                if ($record) {
                    $type = 'moon';
                }
            }

            // Lunar features
            if (!$record) {
                $record = LunarFeature::where('name', $pk)->first();
                if ($record) {
                    $type = 'lunar_feature';
                }
            }

            // Comets (numeric ids or names) and Deepsky objects.
            // Try objectnames aliases first for non-numeric slugs because many
            // canonical deepsky names and aliases are stored in that table.
            if (!$record) {
                $on = null;
                if (!is_numeric($pk)) {
                    $on = DB::table('objectnames')->where('objectname', $pk)->orWhere('altname', $pk)->first();
                }

                if ($on) {
                    // objectnames.objectname is canonical for Objects
                    $record = DB::table('objects')->where('name', $on->objectname)->first();
                    if ($record) {
                        $type = 'deepsky';
                    }
                }

                if (!$record) {
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

                if (!$record) {
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
            if (!$record) {
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

        if (!$record) {
            abort(404);
        }

        try {
            $preRenderElapsed = round((microtime(true) - $tStart) * 1000, 2);
            Log::debug('ObjectController: pre-render timing', ['slug' => $slug, 'pre_render_elapsed_ms' => $preRenderElapsed]);
        } catch (\Throwable $_) {
        }

        render_object:
        // Cache successful slug resolution so subsequent requests can avoid
        // the expensive alias/name probing path in this controller.
        if (!empty($resolutionCacheKey) && $record && $type) {
            try {
                Cache::put($resolutionCacheKey, [
                    'type' => $type,
                    'pk' => $record->id ?? ($record->name ?? null),
                ], now()->addHours(6));
            } catch (\Throwable $_) {
                // ignore cache store failures
            }
        }

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
        } elseif (!empty($type)) {
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
        // Defer legacy counts to either controller-calculated values or the view fallbacks.
        // Set to null so the view will call legacy helpers when controller doesn't supply counts.
        $totalObservations = null;
        $observations = collect([]);
        $drawings = null;
        $observerStats = [];
        $selectedObserverUsername = null;
        $selectedObserverName = null;
        $yourObservations = null;
        $yourDrawings = null;
        $lastObservationDate = null;
        $lastDrawingDate = null;

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
                    if (!$mag && $userInstrument->focal_length_mm && isset($record->typicalEyepieceFocal)) {
                        // edge case: estimate magnification if eyepiece focal length stored on record (rare)
                        $mag = round($userInstrument->focal_length_mm / $record->typicalEyepieceFocal);
                    }

                    // Determine if the user has a default lens configured and load it.
                    $defaultLensId = $authUser?->stdlens ?? null;
                    try {
                        if (!$defaultLensId && Schema::hasColumn('users', 'preferences') && is_array($authUser?->preferences) && isset($authUser->preferences['aladin_default_lens'])) {
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
                                if (!is_numeric($lensFactor) || $lensFactor <= 0) {
                                    $lensFactor = 1.0;
                                }
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
                    if (!$mag && $sbobj !== null && $sqm !== null && $aperture) {
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
                                        if ($sep->active && !empty($sep->focal_length_mm) && $sep->focal_length_mm > 0) {
                                            // account for default lens factor when deriving achievable magnifications
                                            $derived[] = (int) round(($userInstrument->focal_length_mm / $sep->focal_length_mm) * $lensFactor);
                                        }
                                    }
                                    $derived = array_values(array_unique(array_filter($derived)));
                                    if (!empty($derived)) {
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
                                    // Precompute instruments used per eyepiece to avoid per-eyepiece queries
                                    try {
                                        $eyepieceIds = [];
                                        foreach ($set->eyepieces as $tmpEp) {
                                            if (isset($tmpEp->id) && $tmpEp->id)
                                                $eyepieceIds[] = $tmpEp->id;
                                        }
                                        $eyepieceIds = array_values(array_unique($eyepieceIds));
                                        if (!empty($eyepieceIds)) {
                                            $map = \App\Models\Observation::getInstrumentsForEyepieceIds($eyepieceIds);
                                            \App\Models\Eyepiece::setBulkUsedInstrumentsMap($map);
                                            try {
                                                $firstMap = \App\Models\Observation::getFirstObservationDateAndIdForEyepieceIds($eyepieceIds);
                                                \App\Models\Eyepiece::setBulkFirstObservationMap($firstMap);
                                            } catch (\Throwable $_) {
                                            }
                                            try {
                                                $lastMap = \App\Models\Observation::getLastObservationDateAndIdForEyepieceIds($eyepieceIds);
                                                \App\Models\Eyepiece::setBulkLastObservationMap($lastMap);
                                            } catch (\Throwable $_) {
                                            }
                                        }
                                    } catch (\Throwable $_) {
                                    }

                                    // Batch user slug lookup to avoid N+1 queries
                                    $epUserIds = [];
                                    foreach ($set->eyepieces as $tmpEp) {
                                        if (isset($tmpEp->user_id) && $tmpEp->user_id)
                                            $epUserIds[] = $tmpEp->user_id;
                                    }
                                    $epUserIds = array_values(array_unique($epUserIds));
                                    $epUserSlugMap = [];
                                    if (!empty($epUserIds)) {
                                        try {
                                            $epUserSlugMap = \App\Models\User::whereIn('id', $epUserIds)->pluck('slug', 'id')->toArray();
                                        } catch (\Throwable $_) {
                                            $epUserSlugMap = [];
                                        }
                                    }
                                    foreach ($set->eyepieces as $ep) {
                                        if ($ep->active && $ep->focal_length_mm) {
                                            $usedSetEyepieces = true;
                                            $ef = $ep->focal_length_mm;
                                            $eyepieceFocals[] = $ef;
                                            // Attempt to include slugs so the view can link to eyepiece pages
                                            $userSlug = $epUserSlugMap[$ep->user_id] ?? null;
                                            // Build display name; if a default lens is set, append the lens name
                                            $displayName = $ep->fullName();
                                            if (!empty($defaultLensName)) {
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
                                    // Batch user slug lookup for user's eyepieces
                                    $epUserIds = [];
                                    foreach ($userEps as $tmpEp) {
                                        if (isset($tmpEp->user_id) && $tmpEp->user_id)
                                            $epUserIds[] = $tmpEp->user_id;
                                    }
                                    $epUserIds = array_values(array_unique($epUserIds));
                                    $epUserSlugMap = [];
                                    if (!empty($epUserIds)) {
                                        try {
                                            $epUserSlugMap = \App\Models\User::whereIn('id', $epUserIds)->pluck('slug', 'id')->toArray();
                                        } catch (\Throwable $_) {
                                            $epUserSlugMap = [];
                                        }
                                    }
                                    foreach ($userEps as $ep) {
                                        if (!empty($ep->focal_length_mm)) {
                                            $ef = $ep->focal_length_mm;
                                            $eyepieceFocals[] = $ef;
                                        } else {
                                            $ef = null;
                                        }
                                        $userSlug = $epUserSlugMap[$ep->user_id] ?? null;
                                        $displayName = $ep->fullName() ?? $ep->name ?? null;
                                        if (!empty($defaultLensName) && !empty($displayName)) {
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
                            if (!empty($eyepieceFocals) && $userInstrument?->focal_length_mm) {
                                foreach ($eyepiecesForDisplay as $epInfo) {
                                    $ef = $epInfo['focal'];
                                    if ($ef > 0) {
                                        // account for default lens factor in produced magnification
                                        $m = (int) round(($userInstrument->focal_length_mm / $ef) * $lensFactor);
                                        if ($m > 0) {
                                            if (!isset($epMap[$m])) {
                                                $epMap[$m] = [];
                                            }
                                            $epMap[$m][] = $epInfo;
                                        }
                                    }
                                }
                            }

                            // Compute candidate magnifications given instrument focal length and eyepiece focals
                            // Prefer using keys from epMap (magnifications produced by actual eyepieces)
                            $possibleMags = [];
                            if (!empty($epMap)) {
                                $possibleMags = array_values(array_unique(array_keys($epMap)));
                            } elseif (!empty($eyepieceFocals) && $userInstrument?->focal_length_mm) {
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
                            if (!empty($possibleMags) && $sbobj !== null && $sqm !== null && $aperture) {
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
                            if (!empty($best) && isset($epMap[(int) $best])) {
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
                                    if (!isset($uniq[$k])) {
                                        $uniq[$k] = true;
                                        $finalEps[] = $e;
                                    }
                                }
                                // If we could not map eyepieces to mags (empty epMap) but
                                // we collected eyepieces for display, show those so names
                                // appear in the view even without a configured instrument.
                                if (empty($finalEps) && !empty($eyepiecesForDisplay)) {
                                    // Deduplicate eyepiecesForDisplay as well
                                    $uniq = [];
                                    $finalEps = [];
                                    foreach ($eyepiecesForDisplay as $e) {
                                        $k = ($e['name'] ?? '') . '|' . ($e['focal'] ?? '');
                                        if (!isset($uniq[$k])) {
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
        // Populate numeric display fields defensively: some source tables (eg. comets)
        // may not provide these columns. Use null when missing and only perform
        // numeric transformations when values are present and numeric.
        $session->mag = isset($record->mag) ? $record->mag : null;

        if (isset($record->diam1) && is_numeric($record->diam1)) {
            $session->diam1 = round($record->diam1 / 60.0, 1);
        } else {
            $session->diam1 = null;
        }
        if (isset($record->diam2) && is_numeric($record->diam2)) {
            $session->diam2 = round($record->diam2 / 60.0, 1);
        } else {
            $session->diam2 = $session->diam1;
        }

        // surface brightness column sometimes abbreviated as subr or sb or surface_brightness
        $session->subr = (isset($record->subr) && is_numeric($record->subr)) ? round($record->subr, 1) : null;
        // position angle: pa is common
        $session->pa = isset($record->pa) ? $record->pa : null;

        // If this is a deepsky object, attempt to resolve the legacy type code to a human-friendly label
        if (($type === 'deepsky' || $type === 'objects') && isset($record->type)) {
            try {
                // Normalize legacy type codes: trim and uppercase so lookups succeed regardless of case/whitespace
                $rawCode = trim((string) $record->type);
                $normCode = strtoupper($rawCode);

                $dst = DeepskyType::find($normCode);
                if ($dst && !empty($dst->name)) {
                    // Normalize label: remove any trailing ' (legacy)' marker stored in the DB
                    $label = $dst->name;
                    try {
                        $label = preg_replace('/\s*\(legacy\)\s*$/i', '', (string) $label);
                        $label = trim((string) $label);
                    } catch (\Throwable $_) {
                        // If normalization fails, fall back to the original DB value
                        $label = $dst->name;
                    }
                    $session->source_type = $label;
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
            if (!empty($record->con)) {
                try {
                    $cons = ConstellationModel::where('id', $record->con)->first();
                    if ($cons && !empty($cons->name)) {
                        $session->constellation = $cons->name;
                        $session->constellation_code = $record->con;
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
            }
        }

        // Provide a preview image if available in legacy storage paths
        if (!empty($record->picture)) {
            $image = asset('storage/' . $record->picture);
            $session->preview = $image;
        }

        // Load alternative names (aliases) from legacy objectnames table when available.
        // Use the mysqlOld connection which contains the legacy `objectnames` table.
        $alternatives = [];
        try {
            if (!empty($record->name)) {
                $rows = DB::connection('mysql')
                    ->table('objectnames')
                    ->select(['objectname', 'altname', 'catalog', 'catindex'])
                    ->where('objectname', $record->name)
                    ->orWhere('altname', $record->name)
                    ->get();

                foreach ($rows as $r) {
                    // Collect non-empty alternative strings excluding the canonical name
                    if (!empty($r->altname) && strcasecmp($r->altname, $record->name) !== 0) {
                        $alternatives[] = $r->altname;
                    }
                    // Also include objectname values that differ in case/spacing if useful
                    if (!empty($r->objectname) && strcasecmp($r->objectname, $record->name) !== 0) {
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
            if (!empty($record->slug)) {
                $canonicalSlug = (string) $record->slug;
            } elseif (!empty($record->name)) {
                $canonicalSlug = \Illuminate\Support\Str::slug($record->name, '-');
            }
        } catch (\Throwable $_) {
            $canonicalSlug = null;
        }

        // Ensure observation-related variables are defined so view compact() doesn't fail
        $totalObservations = $totalObservations ?? null;
        $drawings = $drawings ?? null;
        $yourObservations = $yourObservations ?? null;
        $yourDrawings = $yourDrawings ?? null;
        $lastObservationDate = $lastObservationDate ?? null;
        $lastDrawingDate = $lastDrawingDate ?? null;

        // Observation and drawing counts of this object. Migrated and new
        // observations are stored the same way (see App\Models\Observation).
        try {
            $objectObservations = null;
            $solarSystemTables = ['planet' => 'planets', 'sun' => 'planets', 'moon' => 'moons', 'lunar_feature' => 'lunar_features'];
            if ($sourceTypeRaw === 'comet' && isset($record->id) && is_numeric($record->id)) {
                $objectObservations = fn() => \App\Models\Observation::ofComet((int) $record->id);
            } elseif ($sourceTypeRaw === 'object' && !empty($session->name)) {
                $objectName = \App\Models\Observation::canonicalDeepskyName($session->name) ?? $session->name;
                $objectObservations = fn() => \App\Models\Observation::ofDeepskyObject($objectName);
            } elseif (isset($solarSystemTables[$sourceTypeRaw]) && !empty($record->slug)) {
                $objectObservations = fn() => \App\Models\Observation::where('target_table', $solarSystemTables[$sourceTypeRaw])
                    ->where('target_key', $record->slug);
            }

            if ($objectObservations !== null) {
                $totalObservations = $objectObservations()->count();
                $drawings = $objectObservations()->where('has_drawing', 1)->count();

                $authUser = Auth::user();
                if ($authUser) {
                    $yours = fn() => $objectObservations()->where('observer_user_id', $authUser->id);
                    $yourObservations = $yours()->count();
                    $yourDrawings = $yours()->where('has_drawing', 1)->count();

                    $lastObsRaw = $yours()->max('observed_on');
                    $lastObservationDate = $lastObsRaw ? \Carbon\Carbon::parse($lastObsRaw) : null;
                    $lastDrawingRaw = $yours()->where('has_drawing', 1)->max('observed_on');
                    $lastDrawingDate = $lastDrawingRaw ? \Carbon\Carbon::parse($lastDrawingRaw) : null;
                }
            }
        } catch (\Throwable $_) {
            // defensive: ignore count errors and leave variables as-is
        }

        // Atlas page: if a user is logged in and they have a standardAtlasCode set,
        // check the objects table for a column with that name and read the stored page value.
        $atlasPage = null;
        $atlasName = null;
        try {
            $authUser = Auth::user();
            if ($authUser && !empty($authUser->standardAtlasCode)) {
                $atlasCode = $authUser->standardAtlasCode;
                // Be defensive: only try to read the column if it exists on objects table
                if (Schema::hasColumn('objects', $atlasCode) && isset($record->{$atlasCode})) {
                    // For Millenium Star Atlas use the formatted 'millenium' column (e.g. "158/I") instead of the bare base number
                    $atlasReadColumn = ($atlasCode === 'milleniumbase' && isset($record->millenium) && !empty($record->millenium))
                        ? 'millenium'
                        : $atlasCode;
                    $atlasPage = $record->{$atlasReadColumn};
                    // Try to load a human-friendly atlas name from Atlas model
                    try {
                        $atlasModel = Atlas::where('code', $atlasCode)->first();
                        if ($atlasModel && !empty($atlasModel->name)) {
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
                if (!$ep && $authUser) {
                    $userEps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->first();
                    if ($userEps) {
                        $ep = [
                            'focal_length_mm' => $userEps->focal_length_mm ?? null,
                            'apparent_fov_deg' => $userEps->apparentFOV ?? null,
                        ];
                    }
                }

                if ($aladinDefaults === null) {
                    $aladinDefaults = [];
                }
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
            if (!isset($aladinDefaults['object_diam_arcmin'])) {
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
                // Use a canonical ephemeris date: prefer session or request, fall back to now
                $date = $this->getCanonicalEphemerisDate($userLocation);

                $geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);

                // Prepare target and equatorial coordinates
                $target = new AstroTarget();
                // record->ra/decl can be strings like "00 42 44.3" or decimals; try conversion helper if available
                $raDeg = null;
                $decDeg = null;
                try {
                    if (method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                        $raDeg = \App\Models\DeepskyObject::raToDecimal($record->ra);
                        $decDeg = \App\Models\DeepskyObject::decToDecimal($record->decl);
                    }
                } catch (\Throwable $_) {
                    $raDeg = null;
                    $decDeg = null;
                }

                if ($raDeg === null || $decDeg === null) {
                    // fallback: try numeric cast (assume stored as degrees)
                    $raDeg = is_numeric($record->ra) ? (float) $record->ra : null;
                    $decDeg = is_numeric($record->decl) ? (float) $record->decl : null;
                }

                if ($raDeg !== null && $decDeg !== null) {
                    // EquatorialCoordinates expects RA in hours (0..24); $raDeg is in degrees.
                    $raHours = (float) $raDeg / 15.0;
                    $equa = new EquatorialCoordinates($raHours, (float) $decDeg);
                    $target->setEquatorialCoordinates($equa);

                    $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
                    $deltaT = Time::deltaT($date);

                    $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);

                    // Localize results to user's timezone where applicable
                    $transit = null;
                    $rising = null;
                    $setting = null;
                    $bestTime = null;
                    $maxHeightAtNight = null;
                    $maxHeight = null;
                    try {
                        $transit = $target->getTransit();
                    } catch (\Throwable $_) {
                        $transit = null;
                    }
                    try {
                        $rising = $target->getRising();
                    } catch (\Throwable $_) {
                        $rising = null;
                    }
                    try {
                        $setting = $target->getSetting();
                    } catch (\Throwable $_) {
                        $setting = null;
                    }
                    try {
                        $bestTime = $target->getBestTimeToObserve();
                    } catch (\Throwable $_) {
                        $bestTime = null;
                    }
                    try {
                        $maxHeightAtNight = $target->getMaxHeightAtNight();
                    } catch (\Throwable $_) {
                        $maxHeightAtNight = null;
                    }
                    try {
                        $maxHeight = $target->getMaxHeight();
                    } catch (\Throwable $_) {
                        $maxHeight = null;
                    }

                    // Format timezone-aware strings when Carbon instances returned
                    $tz = $userLocation->timezone ?? config('app.timezone');
                    if ($transit instanceof \DateTimeInterface) {
                        try {
                            $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $transit = (string) $transit;
                        }
                    }
                    if ($rising instanceof \DateTimeInterface) {
                        try {
                            $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $rising = (string) $rising;
                        }
                    }
                    if ($setting instanceof \DateTimeInterface) {
                        try {
                            $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $setting = (string) $setting;
                        }
                    }
                    if ($bestTime instanceof \DateTimeInterface) {
                        try {
                            $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $bestTime = (string) $bestTime;
                        }
                    }
                    // The astronomy library may return Coordinate objects. Convert to numeric values
                    try {
                        if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) {
                            $maxHeightAtNight = $maxHeightAtNight->getCoordinate();
                        }
                    } catch (\Throwable $_) {
                    }
                    try {
                        if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) {
                            $maxHeight = $maxHeight->getCoordinate();
                        }
                    } catch (\Throwable $_) {
                    }
                    if (is_numeric($maxHeightAtNight))
                        $maxHeightAtNight = round($maxHeightAtNight, 1);
                    if (is_numeric($maxHeight))
                        $maxHeight = round($maxHeight, 1);

                    // Altitude graph HTML provided by target if available
                    $altitudeGraph = null;
                    try {
                        $altitudeGraph = $target->altitudeGraph($geo_coords, $date);
                    } catch (\Throwable $_) {
                        $altitudeGraph = null;
                    }

                    $yearGraph = $target->yearGraph($geo_coords, $date);
                    $ephemerides = [
                        'date' => $date->timezone($tz)->toDateString(),
                        'rising' => $rising,
                        'transit' => $transit,
                        'setting' => $setting,
                        'best_time' => $bestTime,
                        'max_height_at_night' => $maxHeightAtNight,
                        'max_height' => $maxHeight,
                        'altitude_graph' => $altitudeGraph,
                        'year_graph' => $yearGraph,
                    ];
                }
            }
        } catch (\Throwable $_) {
            $ephemerides = null;
        }

        // If this is a planet, compute current RA/Dec and magnitude using the
        // installed astronomy library when possible. We prefer topocentric
        // coordinates if the authenticated user has a standard location set;
        // otherwise fall back to apparent (geocentric) coordinates.
        try {
            if ($sourceTypeRaw === 'planet' || $type === 'planet') {
                $planetName = $record->name ?? null;
                if (!empty($planetName)) {
                    $key = mb_strtolower(trim($planetName));
                    $map = [
                        'mercury' => 'Mercury',
                        'venus' => 'Venus',
                        'earth' => 'Earth',
                        'mars' => 'Mars',
                        'jupiter' => 'Jupiter',
                        'saturn' => 'Saturn',
                        'uranus' => 'Uranus',
                        'neptune' => 'Neptune',
                        'pluto' => 'Pluto',
                    ];
                    if (isset($map[$key])) {
                        $className = $map[$key];
                        $fqcn = "\\deepskylog\\AstronomyLibrary\\Targets\\{$className}";
                        if (class_exists($fqcn)) {
                            $planet = new $fqcn();
                            $date = $this->getCanonicalEphemerisDate();

                            // Prefer user's standard location for topocentric coords
                            $authUser = Auth::user();
                            $userLocation = $authUser?->standardLocation ?? null;
                            if ($userLocation && isset($userLocation->longitude) && isset($userLocation->latitude)) {
                                $geo = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                                $height = $userLocation->elevation ?? 0.0;
                                try {
                                    $planet->calculateEquatorialCoordinates($date->copy(), $geo, $height);
                                } catch (\Throwable $_) {
                                    // fallback to apparent if topocentric calc fails
                                    try {
                                        $planet->calculateApparentEquatorialCoordinates($date->copy());
                                    } catch (\Throwable $_) {
                                        // give up silently
                                    }
                                }
                            } else {
                                // no user location: use apparent (geocentric) coordinates
                                try {
                                    if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
                                        $planet->calculateApparentEquatorialCoordinates($date);
                                    }
                                } catch (\Throwable $_) {
                                    // ignore
                                }
                            }

                            // Read equatorial coordinates when available
                            try {
                                if (method_exists($planet, 'getEquatorialCoordinatesToday')) {
                                    $coords = $planet->getEquatorialCoordinatesToday();
                                } elseif (method_exists($planet, 'getEquatorialCoordinates')) {
                                    $coords = $planet->getEquatorialCoordinates();
                                } else {
                                    $coords = null;
                                }
                                if ($coords) {
                                    // EquatorialCoordinates provides human-readable printing methods
                                    try {
                                        if (method_exists($coords, 'printRA')) {
                                            $session->ra = $coords->printRA();
                                        }
                                    } catch (\Throwable $_) {
                                    }
                                    try {
                                        if (method_exists($coords, 'printDeclination')) {
                                            $session->decl = $coords->printDeclination();
                                        }
                                    } catch (\Throwable $_) {
                                    }
                                }
                            } catch (\Throwable $_) {
                                // ignore coordinate extraction errors
                            }

                            // Try to derive constellation from computed equatorial coordinates
                            try {
                                // Preferred: EquatorialCoordinates or Planet may expose a helper
                                $consName = null;
                                $consCode = null;
                                if (isset($coords) && $coords) {
                                    // Common method names to try on the coords object
                                    if (method_exists($coords, 'getConstellation')) {
                                        try {
                                            $c = $coords->getConstellation();
                                            if (is_string($c) && !empty($c)) {
                                                $consName = $c;
                                            } elseif (is_object($c)) {
                                                // If object has name/id
                                                if (isset($c->name))
                                                    $consName = $c->name;
                                                if (isset($c->id))
                                                    $consCode = $c->id;
                                            }
                                        } catch (\Throwable $_) {
                                            // ignore
                                        }
                                    }
                                    // Try alternative accessor names
                                    if (!$consName && method_exists($coords, 'constellation')) {
                                        try {
                                            $c = $coords->constellation();
                                            if (is_string($c) && !empty($c))
                                                $consName = $c;
                                        } catch (\Throwable $_) {
                                        }
                                    }
                                }

                                // Planet-level helpers
                                if (!$consName && isset($planet) && $planet) {
                                    if (method_exists($planet, 'getConstellation')) {
                                        try {
                                            $c = $planet->getConstellation();
                                            if (is_string($c) && !empty($c))
                                                $consName = $c;
                                        } catch (\Throwable $_) {
                                        }
                                    }
                                    if (!$consName && method_exists($planet, 'constellation')) {
                                        try {
                                            $c = $planet->constellation();
                                            if (is_string($c) && !empty($c))
                                                $consName = $c;
                                        } catch (\Throwable $_) {
                                        }
                                    }
                                }

                                // If we found a constellation name, try to resolve a human name and id from DB
                                if ($consName) {
                                    try {
                                        $found = ConstellationModel::where('name', $consName)->orWhere('id', $consName)->first();
                                        if ($found) {
                                            $session->constellation = $found->name;
                                            $session->constellation_code = $found->id;
                                        } else {
                                            // If no DB mapping, just expose the raw name
                                            $session->constellation = $consName;
                                        }
                                    } catch (\Throwable $_) {
                                        $session->constellation = $consName;
                                    }
                                } elseif ($consCode) {
                                    try {
                                        $found = ConstellationModel::where('id', $consCode)->first();
                                        if ($found) {
                                            $session->constellation = $found->name;
                                            $session->constellation_code = $found->id;
                                        }
                                    } catch (\Throwable $_) {
                                    }
                                }
                            } catch (\Throwable $_) {
                                // ignore constellation resolution errors
                            }

                            // Compute ephemerides (rise/transit/set, best time, max altitude) for planets
                            try {
                                $authUser = Auth::user();
                                $userLocation = $authUser?->standardLocation ?? null;
                                if ($userLocation) {
                                    // Try to obtain numeric RA/Dec in degrees from $coords
                                    $raDeg = null;
                                    $decDeg = null;
                                    try {
                                        if (isset($coords) && $coords) {
                                            // Prefer numeric accessors
                                            if (method_exists($coords, 'getRA')) {
                                                $raObj = $coords->getRA();
                                                if (is_object($raObj) && method_exists($raObj, 'getCoordinate')) {
                                                    $raHours = $raObj->getCoordinate();
                                                    if (is_numeric($raHours))
                                                        $raDeg = (float) $raHours * 15.0;
                                                } elseif (is_numeric($raObj)) {
                                                    // sometimes RA provided as hours numeric
                                                    $raDeg = (float) $raObj * 15.0;
                                                }
                                            }
                                            if (method_exists($coords, 'getDeclination')) {
                                                $decObj = $coords->getDeclination();
                                                if (is_object($decObj) && method_exists($decObj, 'getCoordinate')) {
                                                    $dec = $decObj->getCoordinate();
                                                    if (is_numeric($dec))
                                                        $decDeg = (float) $dec;
                                                } elseif (is_numeric($decObj)) {
                                                    $decDeg = (float) $decObj;
                                                }
                                            }
                                            // Fallback: try parsing printed strings using DeepskyObject helpers
                                            if (($raDeg === null || $decDeg === null) && method_exists($coords, 'printRA') && method_exists($coords, 'printDeclination')) {
                                                try {
                                                    $raStr = $coords->printRA();
                                                    $decStr = $coords->printDeclination();
                                                    if (($raDeg === null) && method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                                                        $tmp = \App\Models\DeepskyObject::raToDecimal($raStr);
                                                        if (is_numeric($tmp))
                                                            $raDeg = (float) $tmp;
                                                    }
                                                    if (($decDeg === null) && method_exists(\App\Models\DeepskyObject::class, 'decToDecimal')) {
                                                        $tmp = \App\Models\DeepskyObject::decToDecimal($decStr);
                                                        if (is_numeric($tmp))
                                                            $decDeg = (float) $tmp;
                                                    }
                                                } catch (\Throwable $_) {
                                                    // ignore parse failures
                                                }
                                            }
                                        }
                                    } catch (\Throwable $_) {
                                        $raDeg = null;
                                        $decDeg = null;
                                    }

                                    if (is_numeric($raDeg) && is_numeric($decDeg)) {
                                        try {
                                            $geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                                            $target = new AstroTarget();
                                            // EquatorialCoordinates expects RA in hours (0..24); $raDeg is in degrees.
                                            $raHours = (float) $raDeg / 15.0;
                                            $equa = new EquatorialCoordinates($raHours, (float) $decDeg);
                                            $target->setEquatorialCoordinates($equa);

                                            $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich($date);
                                            $deltaT = Time::deltaT($date);

                                            $target->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);

                                            // Extract results similar to the deepsky branch
                                            $transit = null;
                                            $rising = null;
                                            $setting = null;
                                            $bestTime = null;
                                            $maxHeightAtNight = null;
                                            $maxHeight = null;
                                            try {
                                                $transit = $target->getTransit();
                                            } catch (\Throwable $_) {
                                                $transit = null;
                                            }
                                            try {
                                                $rising = $target->getRising();
                                            } catch (\Throwable $_) {
                                                $rising = null;
                                            }
                                            try {
                                                $setting = $target->getSetting();
                                            } catch (\Throwable $_) {
                                                $setting = null;
                                            }
                                            try {
                                                $bestTime = $target->getBestTimeToObserve();
                                            } catch (\Throwable $_) {
                                                $bestTime = null;
                                            }
                                            try {
                                                $maxHeightAtNight = $target->getMaxHeightAtNight();
                                            } catch (\Throwable $_) {
                                                $maxHeightAtNight = null;
                                            }
                                            try {
                                                $maxHeight = $target->getMaxHeight();
                                            } catch (\Throwable $_) {
                                                $maxHeight = null;
                                            }

                                            // Format timezone-aware strings when Carbon instances returned
                                            $tz = $userLocation->timezone ?? config('app.timezone');
                                            if ($transit instanceof \DateTimeInterface) {
                                                try {
                                                    $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $transit = (string) $transit;
                                                }
                                            }
                                            if ($rising instanceof \DateTimeInterface) {
                                                try {
                                                    $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $rising = (string) $rising;
                                                }
                                            }
                                            if ($setting instanceof \DateTimeInterface) {
                                                try {
                                                    $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $setting = (string) $setting;
                                                }
                                            }
                                            if ($bestTime instanceof \DateTimeInterface) {
                                                try {
                                                    $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $bestTime = (string) $bestTime;
                                                }
                                            }
                                            try {
                                                if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate'))
                                                    $maxHeightAtNight = $maxHeightAtNight->getCoordinate();
                                            } catch (\Throwable $_) {
                                            }
                                            try {
                                                if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate'))
                                                    $maxHeight = $maxHeight->getCoordinate();
                                            } catch (\Throwable $_) {
                                            }
                                            if (is_numeric($maxHeightAtNight))
                                                $maxHeightAtNight = round($maxHeightAtNight, 1);
                                            if (is_numeric($maxHeight))
                                                $maxHeight = round($maxHeight, 1);

                                            $altitudeGraph = null;
                                            try {
                                                $altitudeGraph = $target->altitudeGraph($geo_coords, $date);
                                            } catch (\Throwable $_) {
                                                $altitudeGraph = null;
                                            }
                                            $yearGraph = null;
                                            try {
                                                $yearGraph = $target->yearGraph($geo_coords, $date);
                                            } catch (\Throwable $_) {
                                                $yearGraph = null;
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
                                                'year_graph' => $yearGraph,
                                            ];
                                        } catch (\Throwable $_) {
                                            // ignore ephemerides errors for planets
                                        }
                                    } // end if numeric coords
                                }
                            } catch (\Throwable $_) {
                                // ignore top-level planet ephemerides errors
                            }

                            // Attempt to read magnitude from the planet implementation.
                            // If a magnitude(Carbon $date) method exists use it; otherwise
                            // fall back to legacy DB value (handled later).
                            try {
                                $mag = null;
                                if (method_exists($planet, 'magnitude')) {
                                    $mag = $planet->magnitude($date);
                                } elseif (method_exists($planet, 'getMagnitude')) {
                                    $mag = $planet->getMagnitude();
                                }
                                if (is_numeric($mag)) {
                                    // round to two decimals for display
                                    $session->mag = is_float($mag) ? round($mag, 2) : $mag;
                                }
                                // Attempt to calculate diameter from the planet implementation.
                                try {
                                    if (method_exists($planet, 'calculateDiameter')) {
                                        $planet->calculateDiameter($date);
                                        $pd = $planet->getDiameter();
                                        if (is_array($pd) && isset($pd[0]) && is_numeric($pd[0])) {
                                            // store planet diameters (arcseconds) separately to avoid
                                            // conflicting units with deepsky object diam1/diam2 (arcminutes)
                                            $session->planet_diam1 = is_float($pd[0]) ? round($pd[0], 1) : $pd[0];
                                            $session->planet_diam2 = isset($pd[1]) && is_numeric($pd[1]) ? (is_float($pd[1]) ? round($pd[1], 1) : $pd[1]) : $session->planet_diam1;
                                        }
                                    }
                                } catch (\Throwable $_) {
                                    // ignore diameter calculation errors
                                }
                                // Compute illuminated fraction for planets for the initial page payload
                                try {
                                    $illum = null;
                                    if (method_exists($planet, 'illuminatedFraction')) {
                                        $v = $planet->illuminatedFraction($date);
                                        if (is_numeric($v))
                                            $illum = (float) $v;
                                        elseif ($v instanceof \JsonSerializable || is_string($v))
                                            $illum = (float) $v;
                                    }
                                } catch (\Throwable $_) {
                                    $illum = null;
                                }
                                try {
                                    $session->illuminated_fraction = $illum ?? null;
                                } catch (\Throwable $_) {
                                }
                                try {
                                    if (isset($ephemerides) && is_array($ephemerides)) {
                                        $ephemerides['illuminated_fraction'] = $illum ?? ($ephemerides['illuminated_fraction'] ?? null);
                                    }
                                } catch (\Throwable $_) {
                                }
                                // Also compute initial contrast reserve and optimum detection magnification for planets
                                // so the detail page shows values even before Livewire recalculation runs.
                                try {
                                    $authUser = Auth::user();
                                    $userLocation = $authUser?->standardLocation ?? null;
                                    $userInstrument = $authUser?->standardInstrument ?? null;

                                    if ($userLocation && $userInstrument && isset($session->planet_diam1) && isset($session->mag)) {
                                        $target = new AstroTarget();
                                        // planet diameters are in arcseconds
                                        $d1 = is_numeric($session->planet_diam1) ? $session->planet_diam1 : null;
                                        $d2 = is_numeric($session->planet_diam2) ? $session->planet_diam2 : $d1;
                                        if ($d1)
                                            $target->setDiameter($d1, $d2 ?? $d1);
                                        $mval = is_numeric($session->mag) ? $session->mag : null;
                                        if ($mval !== null)
                                            $target->setMagnitude($mval);

                                        $sbobj = $target->calculateSBObj();
                                        $sqm = $userLocation->getSqm();
                                        $aperture = $userInstrument->aperture_mm ?? null;

                                        // Lens factor / default lens handling similar to deepsky branch
                                        $defaultLensId = $authUser?->stdlens ?? null;
                                        try {
                                            if (!$defaultLensId && Schema::hasColumn('users', 'preferences') && is_array($authUser?->preferences) && isset($authUser->preferences['aladin_default_lens'])) {
                                                $defaultLensId = $authUser->preferences['aladin_default_lens'];
                                            }
                                        } catch (\Throwable $_) {
                                            // ignore
                                        }
                                        $defaultLens = null;
                                        $lensFactor = 1.0;
                                        if ($defaultLensId) {
                                            try {
                                                $defaultLens = \App\Models\Lens::where('id', $defaultLensId)->first();
                                                if ($defaultLens) {
                                                    $lensFactor = $defaultLens->factor ?? 1.0;
                                                    if (!is_numeric($lensFactor) || $lensFactor <= 0)
                                                        $lensFactor = 1.0;
                                                }
                                            } catch (\Throwable $_) {
                                                $defaultLens = null;
                                            }
                                        }

                                        // Choose candidate magnifications and compute best when possible
                                        if ($sbobj !== null && $sqm !== null && $aperture && $userInstrument) {
                                            $mag = $userInstrument->fixedMagnification ?? null;
                                            if (!$mag && $userInstrument->focal_length_mm && isset($session->typicalEyepieceFocal)) {
                                                $mag = round($userInstrument->focal_length_mm / $session->typicalEyepieceFocal);
                                            }

                                            $possible = [25, 50, 75, 100, 150, 200];
                                            if ($lensFactor !== 1.0)
                                                $possible = array_map(fn($v) => (int) round($v * $lensFactor), $possible);

                                            // Try deriving from user's instrument set eyepieces
                                            $instSet = $authUser?->standardInstrumentSet ?? null;
                                            if ($instSet && $userInstrument?->focal_length_mm) {
                                                try {
                                                    $setModel = $instSet;
                                                    if ($setModel && count($setModel->eyepieces) > 0) {
                                                        $derived = [];
                                                        foreach ($setModel->eyepieces as $sep) {
                                                            if ($sep->active && !empty($sep->focal_length_mm) && $sep->focal_length_mm > 0) {
                                                                $derived[] = (int) round(($userInstrument->focal_length_mm / $sep->focal_length_mm) * $lensFactor);
                                                            }
                                                        }
                                                        $derived = array_values(array_unique(array_filter($derived)));
                                                        if (!empty($derived)) {
                                                            $possible = $derived;
                                                        }
                                                    }
                                                } catch (\Throwable $_) {
                                                }
                                            }

                                            $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possible);
                                            $session->optimum_detection_magnification = $best ? (int) $best : null;

                                            if (!empty($session->optimum_detection_magnification)) {
                                                $contrast = $target->calculateContrastReserve($sbobj, $sqm, $aperture, $session->optimum_detection_magnification);
                                                $session->contrast_reserve = is_numeric($contrast) ? round($contrast, 2) : null;
                                                $cat = null;
                                                if (is_numeric($session->contrast_reserve)) {
                                                    $c = (float) $session->contrast_reserve;
                                                    if ($c > 1.0)
                                                        $cat = 'very_easy';
                                                    elseif ($c > 0.5)
                                                        $cat = 'easy';
                                                    elseif ($c > 0.35)
                                                        $cat = 'quite_difficult';
                                                    elseif ($c > 0.1)
                                                        $cat = 'difficult';
                                                    elseif ($c > -0.2)
                                                        $cat = 'questionable';
                                                    else
                                                        $cat = 'not_visible';
                                                }
                                                $session->contrast_reserve_category = $cat;
                                                $session->contrast_used_location = $userLocation?->name ?? null;
                                                $session->contrast_used_instrument = $userInstrument?->fullName() ?? ($userInstrument?->name ?? null);
                                                // Build eyepiece display list and map eyepieces to produced magnifications
                                                try {
                                                    $eyepiecesForDisplay = [];
                                                    $epMap = [];
                                                    $defaultLensName = $defaultLens ? ($defaultLens->fullName() ?? $defaultLens->name) : null;

                                                    // Prefer eyepieces from the user's standard instrument set when present
                                                    $instSet = $authUser?->standardInstrumentSet ?? null;
                                                    if ($instSet) {
                                                        try {
                                                            $setModel = $instSet;
                                                            if ($setModel && count($setModel->eyepieces) > 0) {
                                                                // Precompute instruments for eyepieces in this set to avoid per-eyepiece legacy queries
                                                                try {
                                                                    $eyepieceIds = [];
                                                                    foreach ($setModel->eyepieces as $tmpEp) {
                                                                        if (isset($tmpEp->id) && $tmpEp->id)
                                                                            $eyepieceIds[] = $tmpEp->id;
                                                                    }
                                                                    $eyepieceIds = array_values(array_unique($eyepieceIds));
                                                                    if (!empty($eyepieceIds)) {
                                                                        $map = \App\Models\Observation::getInstrumentsForEyepieceIds($eyepieceIds);
                                                                        \App\Models\Eyepiece::setBulkUsedInstrumentsMap($map);
                                                                        try {
                                                                            $firstMap = \App\Models\Observation::getFirstObservationDateAndIdForEyepieceIds($eyepieceIds);
                                                                            \App\Models\Eyepiece::setBulkFirstObservationMap($firstMap);
                                                                        } catch (\Throwable $_) {
                                                                        }
                                                                        try {
                                                                            $lastMap = \App\Models\Observation::getLastObservationDateAndIdForEyepieceIds($eyepieceIds);
                                                                            \App\Models\Eyepiece::setBulkLastObservationMap($lastMap);
                                                                        } catch (\Throwable $_) {
                                                                        }
                                                                    }
                                                                } catch (\Throwable $_) {
                                                                }

                                                                // Batch user slug lookup for set eyepieces
                                                                $epUserIds = [];
                                                                foreach ($setModel->eyepieces as $tmpEp) {
                                                                    if (isset($tmpEp->user_id) && $tmpEp->user_id)
                                                                        $epUserIds[] = $tmpEp->user_id;
                                                                }
                                                                $epUserIds = array_values(array_unique($epUserIds));
                                                                $epUserSlugMap = [];
                                                                if (!empty($epUserIds)) {
                                                                    try {
                                                                        $epUserSlugMap = \App\Models\User::whereIn('id', $epUserIds)->pluck('slug', 'id')->toArray();
                                                                    } catch (\Throwable $_) {
                                                                        $epUserSlugMap = [];
                                                                    }
                                                                }
                                                                foreach ($setModel->eyepieces as $ep) {
                                                                    if (!$ep->active)
                                                                        continue;
                                                                    $ef = $ep->focal_length_mm ?? null;
                                                                    $userSlug = $epUserSlugMap[$ep->user_id] ?? null;
                                                                    $displayName = $ep->fullName() ?? $ep->name ?? null;
                                                                    if (!empty($defaultLensName) && !empty($displayName)) {
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
                                                        } catch (\Throwable $_) {
                                                            // ignore
                                                        }
                                                    }

                                                    // If no eyepieces found in the instrument set, fall back to user's eyepieces
                                                    if (empty($eyepiecesForDisplay)) {
                                                        try {
                                                            $eps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->get();
                                                            // Batch user slug lookup for user's eyepieces
                                                            $epUserIds = [];
                                                            foreach ($eps as $tmpEp) {
                                                                if (isset($tmpEp->user_id) && $tmpEp->user_id)
                                                                    $epUserIds[] = $tmpEp->user_id;
                                                            }
                                                            $epUserIds = array_values(array_unique($epUserIds));
                                                            $epUserSlugMap = [];
                                                            if (!empty($epUserIds)) {
                                                                try {
                                                                    $epUserSlugMap = \App\Models\User::whereIn('id', $epUserIds)->pluck('slug', 'id')->toArray();
                                                                } catch (\Throwable $_) {
                                                                    $epUserSlugMap = [];
                                                                }
                                                            }
                                                            foreach ($eps as $ep) {
                                                                $ef = $ep->focal_length_mm ?? null;
                                                                $userSlug = $epUserSlugMap[$ep->user_id] ?? null;
                                                                $displayName = $ep->fullName() ?? $ep->name ?? null;
                                                                if (!empty($defaultLensName) && !empty($displayName)) {
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

                                                    // Build mapping magnification -> eyepieces that produce it
                                                    if (!empty($eyepiecesForDisplay) && $userInstrument?->focal_length_mm) {
                                                        foreach ($eyepiecesForDisplay as $epInfo) {
                                                            $ef = $epInfo['focal'];
                                                            if ($ef > 0) {
                                                                $m = (int) round(($userInstrument->focal_length_mm / $ef) * $lensFactor);
                                                                if ($m > 0) {
                                                                    if (!isset($epMap[$m]))
                                                                        $epMap[$m] = [];
                                                                    $epMap[$m][] = $epInfo;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Candidate magnifications: prefer eyepiece-produced mags, else fall back to earlier possible list
                                                    $possibleMags = [];
                                                    if (!empty($epMap)) {
                                                        $possibleMags = array_values(array_unique(array_keys($epMap)));
                                                    } elseif (!empty($possible)) {
                                                        $possibleMags = $possible;
                                                    }

                                                    // Select eyepieces corresponding to the computed best mag (if any)
                                                    if (!empty($best) && isset($epMap[(int) $best])) {
                                                        $session->optimum_eyepieces = $epMap[(int) $best];
                                                    } else {
                                                        $selectedEps = [];
                                                        foreach ($possibleMags as $pm) {
                                                            if (isset($epMap[$pm])) {
                                                                foreach ($epMap[$pm] as $epInfo) {
                                                                    $selectedEps[] = $epInfo;
                                                                }
                                                            }
                                                        }
                                                        $uniq = [];
                                                        $finalEps = [];
                                                        foreach ($selectedEps as $e) {
                                                            $k = ($e['name'] ?? '') . '|' . ($e['focal'] ?? '');
                                                            if (!isset($uniq[$k])) {
                                                                $uniq[$k] = true;
                                                                $finalEps[] = $e;
                                                            }
                                                        }
                                                        if (empty($finalEps) && !empty($eyepiecesForDisplay)) {
                                                            $uniq = [];
                                                            $finalEps = [];
                                                            foreach ($eyepiecesForDisplay as $e) {
                                                                $k = ($e['name'] ?? '') . '|' . ($e['focal'] ?? '');
                                                                if (!isset($uniq[$k])) {
                                                                    $uniq[$k] = true;
                                                                    $finalEps[] = $e;
                                                                }
                                                            }
                                                        }
                                                        $session->optimum_eyepieces = $finalEps;
                                                    }
                                                } catch (\Throwable $_) {
                                                    $session->optimum_eyepieces = [];
                                                }
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) {
                                    // don't let this break the planet rendering
                                }
                            } catch (\Throwable $_) {
                                // ignore magnitude errors and keep legacy fallback
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $_) {
            // Defensive: never break object page rendering if the library fails
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
                usort($availableInstruments, function ($a, $b) {
                    $av = $a['aperture_mm'] ?? null;
                    $bv = $b['aperture_mm'] ?? null;
                    if ($av === $bv)
                        return 0;
                    if ($av === null)
                        return 1;
                    if ($bv === null)
                        return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                usort($availableEyepieces, function ($a, $b) {
                    $av = $a['focal_length_mm'] ?? null;
                    $bv = $b['focal_length_mm'] ?? null;
                    if ($av === $bv)
                        return 0;
                    if ($av === null)
                        return 1;
                    if ($bv === null)
                        return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                usort($availableLenses, function ($a, $b) {
                    $av = $a['factor'] ?? null;
                    $bv = $b['factor'] ?? null;
                    if ($av === $bv)
                        return 0;
                    if ($av === null)
                        return 1;
                    if ($bv === null)
                        return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                try {
                    $instrElapsed = round((microtime(true) - $tStart) * 1000, 2);
                    Log::debug('ObjectController: loaded instruments/eyepieces/lenses', ['user_id' => $authUser->id ?? null, 'instr_elapsed_ms' => $instrElapsed, 'counts' => ['instruments' => count($availableInstruments), 'eyepieces' => count($availableEyepieces), 'lenses' => count($availableLenses)]]);
                } catch (\Throwable $_) {
                }
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

        // Comets: show the current position, calculated from the orbital elements.
        // The rest of the ephemerides of comets comes from the Livewire components.
        try {
            if ($sourceTypeRaw === 'comet' || $type === 'comet') {
                $userLocation = Auth::user()?->standardLocation ?? null;
                $geo_coords = $userLocation
                    ? new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude)
                    : new GeographicalCoordinates(0.0, 0.0);
                $coords = CometCoordinates::forName($record->name ?? null, $this->getCanonicalEphemerisDate(), $geo_coords, (float) ($userLocation->elevation ?? 0.0));
                if ($coords) {
                    $session->ra = DeepskyObject::formatRa($coords->getRA()->getCoordinate());
                    $session->decl = DeepskyObject::formatDec($coords->getDeclination()->getCoordinate());
                }
            }
        } catch (\Throwable $_) {
            // ignore: the page is shown without coordinates
        }

        // Prepare legacy comet magnitude time-series when available
        $comet_magnitudes = [];
        $comet_min_mag = null;
        $comet_max_mag = null;
        $cometMagnitudeChart = null;
        try {
            $coObjId = null;
            if (isset($session->id) && is_numeric($session->id)) {
                $coObjId = (int) $session->id;
            } else {
                try {
                    $coModel = \App\Models\CometObject::where('name', $session->name ?? '')->first();
                    if ($coModel)
                        $coObjId = $coModel->id ?? null;
                } catch (\Throwable $_) {
                }
            }

            if (!empty($coObjId)) {
                try {
                    // Magnitude is stored in comet_observation_details, not in observations itself
                    $rows = DB::table('observations')
                        ->join('comet_observation_details', 'comet_observation_details.observation_id', '=', 'observations.id')
                        ->where('observations.target_type', \App\Models\Observation::TYPE_COMET)
                        ->where('observations.target_key', (string) $coObjId)
                        ->whereNotNull('comet_observation_details.magnitude')
                        ->orderBy('observations.observed_on')
                        ->select('observations.observed_on', 'comet_observation_details.magnitude')
                        ->get();
                    foreach ($rows as $r) {
                        try {
                            $mag = $r->magnitude !== null ? floatval($r->magnitude) : null;
                            if ($mag === null)
                                continue;

                            $formatted = \Carbon\Carbon::parse($r->observed_on)->toDateString();

                            if ($formatted) {
                                $comet_magnitudes[] = ['date' => $formatted, 'mag' => $mag];
                                if (!is_null($mag)) {
                                    if (is_null($comet_min_mag) || $mag < $comet_min_mag)
                                        $comet_min_mag = $mag;
                                    if (is_null($comet_max_mag) || $mag > $comet_max_mag)
                                        $comet_max_mag = $mag;
                                }
                            }
                        } catch (\Throwable $_) {
                            // ignore individual row errors
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore fetch errors
                }
            }
        } catch (\Throwable $_) {
            // defensive: ignore overall errors
            $comet_magnitudes = [];
        }
        // Filter out sentinel/invalid magnitudes (e.g. -99.9) before building chart
        $filtered_points = [];
        try {
            if (!empty($comet_magnitudes) && is_array($comet_magnitudes)) {
                $filtered_points = array_values(array_filter($comet_magnitudes, function ($p) {
                    if (!is_array($p))
                        return false;
                    if (!isset($p['mag']))
                        return false;
                    if (!is_numeric($p['mag']))
                        return false;
                    $m = floatval($p['mag']);
                    // exclude common sentinel values and obviously invalid mags
                    if ($m === 99.9 || $m === -99.9)
                        return false;
                    if (!is_finite($m))
                        return false;
                    return true;
                }));

                // sort by date ascending when possible
                usort($filtered_points, function ($a, $b) {
                    $da = strtotime($a['date'] ?? '');
                    $db = strtotime($b['date'] ?? '');
                    return $da <=> $db;
                });

                // recompute min/max from filtered points
                $comet_min_mag = null;
                $comet_max_mag = null;
                foreach ($filtered_points as $p) {
                    $m = floatval($p['mag']);
                    if (is_null($comet_min_mag) || $m < $comet_min_mag)
                        $comet_min_mag = $m;
                    if (is_null($comet_max_mag) || $m > $comet_max_mag)
                        $comet_max_mag = $m;
                }
            }
        } catch (\Throwable $_) {
            $filtered_points = [];
        }

        // Build Larapex chart if we have valid filtered points
        try {
            if (!empty($filtered_points) && class_exists(\App\Charts\CometMagnitudeChart::class)) {
                $chartBuilder = new \App\Charts\CometMagnitudeChart();
                $cometMagnitudeChart = $chartBuilder->build($session->name ?? '', $filtered_points);
            }
        } catch (\Throwable $_) {
            $cometMagnitudeChart = null;
        }

        // For comet detail pages we should not embed server-side ephemerides into
        // the initial HTML payload. Ephemerides can be stale across sessions;
        // prefer Livewire's EphemerisAside as the authoritative source so
        // interactive date selection and reloads match.
        try {
            if ((($session->source_type_raw ?? '') === 'comet') || (($type ?? '') === 'comet')) {
                $ephemerides = null;
            }
        } catch (\Throwable $_) {
            // defensive: ignore errors and leave ephemerides as-is if something fails
        }

        // Use the moon-style page for lunar features so coordinates/constellation are hidden
        if ((($session->source_type_raw ?? '') === 'lunar_feature') || (($type ?? '') === 'lunar_feature')) {
            return response()->view('object.moon-page', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName', 'atlasPage', 'atlasName', 'alternatives', 'canonicalSlug', 'aladinDefaults', 'availableInstruments', 'availableEyepieces', 'availableLenses', 'selectedInstrumentId', 'selectedEyepieceId', 'selectedLensId', 'ephemerides', 'yourObservations', 'yourDrawings', 'lastObservationDate', 'lastDrawingDate', 'comet_magnitudes', 'comet_min_mag', 'comet_max_mag', 'cometMagnitudeChart'));
        }

        $vars = compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName', 'atlasPage', 'atlasName', 'alternatives', 'canonicalSlug', 'aladinDefaults', 'availableInstruments', 'availableEyepieces', 'availableLenses', 'selectedInstrumentId', 'selectedEyepieceId', 'selectedLensId', 'ephemerides', 'yourObservations', 'yourDrawings', 'lastObservationDate', 'lastDrawingDate', 'comet_magnitudes', 'comet_min_mag', 'comet_max_mag', 'cometMagnitudeChart');
        try {
            if (($type === 'deepsky' || $type === 'objects')) {
                $vars['dsl_deepsky_full_container'] = true;
            }
        } catch (\Throwable $_) {
            // ignore
        }

        try {
            Log::info('ObjectController: counts', [
                'slug' => $slug,
                'name' => $session->name ?? null,
                'type' => $session->source_type_raw ?? $type ?? null,
                'possibleKeys' => $possibleKeys ?? null,
                'totalObservations' => $totalObservations ?? null,
                'drawings' => $drawings ?? null,
                'yourObservations' => $yourObservations ?? null,
                'yourDrawings' => $yourDrawings ?? null,
            ]);
        } catch (\Throwable $_) {
        }

        return response()->view('object.show', $vars);
    }

    /**
     * Return the canonical ephemeris date for this request, in the timezone of
     * the location (else of the user's standard location).
     * Preference order: request query `date`, session `dsl_ephemeris_date`, now().
     * A date without a time is a date of the observer: noon in their timezone.
     *
     * @return \Carbon\Carbon
     */
    private function getCanonicalEphemerisDate($location = null)
    {
        $tz = EphemerisDate::timezone($location instanceof \App\Models\Location ? $location : null);

        $value = null;
        try {
            $value = request()->query('date');
        } catch (\Throwable $_) {
        }
        if (empty($value)) {
            try {
                $value = session()->get(EphemerisDate::SESSION_KEY);
            } catch (\Throwable $_) {
            }
        }

        return EphemerisDate::parse(is_string($value) ? $value : null, $tz);
    }

    /**
     * Search for deepsky objects (API endpoint for WireUI select).
     */
    public function searchObjects(Request $request)
    {
        $search = $request->get('search', '');
        $selected = $request->get('selected', []);

        $results = [];

        // If selected IDs provided, fetch those first
        if (!empty($selected)) {
            $selectedObjects = DB::table('objects')
                ->whereIn('name', $selected)
                ->select('name', 'type')
                ->get();

            foreach ($selectedObjects as $obj) {
                $results[] = [
                    'label' => $obj->name . ($obj->type ? " ({$obj->type})" : ''),
                    'value' => $obj->name,
                ];
            }
        }

        // Search in objects table (name)
        if (!empty($search)) {
            $objects = DB::table('objects')
                ->where('name', 'LIKE', "%{$search}%")
                ->select('name', 'type')
                ->limit(50)
                ->get();

            foreach ($objects as $obj) {
                $label = $obj->name . ($obj->type ? " ({$obj->type})" : '');
                $results[] = [
                    'label' => $label,
                    'value' => $obj->name,
                ];
            }

            // Also search in alternative names
            $altNames = DB::table('objectnames')
                ->where('altname', 'LIKE', "%{$search}%")
                ->orWhere('objectname', 'LIKE', "%{$search}%")
                ->select('objectname', 'altname')
                ->limit(50)
                ->get();

            foreach ($altNames as $alt) {
                $obj = DB::table('objects')->where('name', $alt->objectname)->first();
                if ($obj) {
                    $label = $alt->altname . " → " . $obj->name . ($obj->type ? " ({$obj->type})" : '');
                    $results[] = [
                        'label' => $label,
                        'value' => $obj->name,
                    ];
                }
            }
        }

        // Remove duplicates based on value
        $results = collect($results)->unique('value')->values()->all();

        return response()->json($results);
    }

    /**
     * Get constellation from coordinates (API endpoint).
     */
    public function getConstellationFromCoords(Request $request)
    {
        $ra = $request->get('ra');
        $decl = $request->get('decl');

        if ($ra === null || $decl === null) {
            return response()->json(['error' => 'Missing ra or decl parameters'], 400);
        }

        try {
            // Convert to numeric values
            $ra = floatval($ra);
            $decl = floatval($decl);

            // Validate RA is in hours (0-24) and Decl is in degrees (-90 to 90)
            if ($ra < 0 || $ra > 24) {
                return response()->json(['error' => 'RA must be between 0 and 24 hours'], 400);
            }

            if ($decl < -90 || $decl > 90) {
                return response()->json(['error' => 'Declination must be between -90 and 90 degrees'], 400);
            }

            $coords = new \deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates(
                $ra,
                $decl,
                2000.0, // J2000 epoch
                0.0,    // proper motion RA
                0.0     // proper motion Dec
            );

            $constellation = $coords->getConstellation();

            return response()->json([
                'constellation' => $constellation,
                'ra' => $ra,
                'decl' => $decl,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing a deepsky object.
     * Only accessible by Administrators and Database Experts.
     */
    public function edit(string $slug)
    {
        // Find the object using similar logic to show()
        $record = null;

        // Try objectnames table first (for aliases)
        $on = DB::table('objectnames')
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(objectname) = ?', [mb_strtolower($slug)])
            ->orWhereRaw('LOWER(altname) = ?', [mb_strtolower($slug)])
            ->first();

        if ($on) {
            $record = DB::table('objects')->where('name', $on->objectname)->first();
        }

        // Try direct slug match on objects table
        if (!$record) {
            $record = DB::table('objects')->where('slug', $slug)->first();
        }

        // Try direct name match
        if (!$record) {
            $record = DB::table('objects')->where('name', $slug)->first();
        }

        if (!$record) {
            abort(404, 'Object not found');
        }

        // Convert to DeepskyObject model instance
        $object = DeepskyObject::where('name', $record->name)->firstOrFail();

        // Authorize
        $this->authorize('edit', $object);

        // Get all constellations and types for dropdowns
        $constellations = ConstellationModel::orderBy('id')->get();
        $types = DeepskyType::orderBy('code')->get();

        // Get alternative names if they exist
        $alternativeNames = DB::table('objectnames')
            ->where('objectname', $object->name)
            ->pluck('altname')
            ->filter()
            ->unique()
            ->values();

        // Get objects this object is part of
        $partOfObjects = DB::table('objectpartof')
            ->where('objectname', $object->name)
            ->pluck('partofname')
            ->unique()
            ->values();

        // Get objects that are part of this object
        $containsObjects = DB::table('objectpartof')
            ->where('partofname', $object->name)
            ->pluck('objectname')
            ->unique()
            ->values();

        // Get SIMBAD-compatible name for external link
        $simbadName = $this->translateObjectNameForSimbad($object->name);

        return view('object.edit', compact('object', 'constellations', 'types', 'alternativeNames', 'partOfObjects', 'containsObjects', 'simbadName'));
    }

    /**
     * The constellation and the atlas pages of a position (RA in hours, declination
     * in degrees), keyed by their objects column. Empty when the calculation fails.
     */
    private function coordinateDerivedFields(float $raHours, float $declDegrees, string $context): array
    {
        $fields = [];
        try {
            $coords = new EquatorialCoordinates($raHours, $declDegrees);
            $fields['con'] = $coords->getConstellation();

            foreach ([
                'urano',
                'urano_new',
                'sky',
                'taki',
                'psa',
                'torresB',
                'torresBC',
                'torresC',
                'milleniumbase',
                'DSLDL',
                'DSLDP',
                'DSLLL',
                'DSLLP',
                'DSLOL',
                'DSLOP',
                'DeepskyHunter',
                'Interstellarum'
            ] as $atlasKey) {
                $fields[$atlasKey] = $coords->calculateAtlasPage($atlasKey);
            }

            // Millenium Star Atlas full ref: page/volume (I=0-8h, II=8-16h, III=16-24h)
            $milVol = $raHours <= 8 ? 'I' : ($raHours <= 16 ? 'II' : 'III');
            $fields['millenium'] = $fields['milleniumbase'] . '/' . $milVol;
        } catch (\Throwable $e) {
            Log::warning('Constellation/atlas calculation failed during ' . $context, ['error' => $e->getMessage()]);

            return [];
        }

        return $fields;
    }

    /**
     * SBObj from the magnitude and the diameters in arcseconds: mag + 2.5 * log10(π * diam1 * diam2 / 4),
     * with diam2 = diam1 for a circular object. Null without a magnitude or a diameter.
     */
    private static function surfaceBrightness($mag, $diam1, $diam2): ?float
    {
        if ($mag === null || $mag == 99.9 || !($diam1 > 0)) {
            return null;
        }
        $effectiveDiam2 = ($diam2 > 0) ? $diam2 : $diam1;

        return $mag + 2.5 * log10(M_PI * $diam1 * $effectiveDiam2 / 4);
    }

    /**
     * The brightness and size columns of an object from the create or edit form, with the
     * sentinels of the legacy data for a missing value: mag and subr 99.9, pa 999, SBObj -999
     * and diameter 0. A magnitude or position angle of 0 is a real value. The form gives the
     * diameters in arcminutes, the objects table stores arcseconds.
     */
    private static function measurementColumns(array $validated): array
    {
        $given = fn(string $key) => isset($validated[$key]) && $validated[$key] !== '';

        $mag = $given('mag') ? (float) $validated['mag'] : 99.9;
        $diam1 = $given('diam1') ? $validated['diam1'] * 60.0 : 0;
        $diam2 = $given('diam2') ? $validated['diam2'] * 60.0 : 0;

        return [
            'mag' => $mag,
            'subr' => $given('subr') ? (float) $validated['subr'] : 99.9,
            'diam1' => $diam1,
            'diam2' => $diam2,
            'pa' => $given('pa') ? (int) $validated['pa'] : 999,
            'SBObj' => self::surfaceBrightness($mag, $diam1, $diam2) ?? -999,
        ];
    }

    /**
     * RA in hours from a SIMBAD VOTable cell: RA_d is decimal degrees, RA is sexagesimal hours.
     */
    private static function simbadRaHours(string $fieldName, string $value): ?float
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if ($fieldName === 'RA_d') {
            return is_numeric($value) ? fmod((float) $value, 360.0) / 15.0 : null;
        }
        if (is_numeric($value)) {
            return null; // a bare number in the RA column is ambiguous
        }
        $raDeg = DeepskyObject::raToDecimal($value);

        return $raDeg === null ? null : $raDeg / 15.0;
    }

    /**
     * Parse RA input (supports both decimal hours and sexagesimal hh mm ss.sss format).
     * Returns RA in decimal hours (0-24), the unit of the objects.ra column.
     */
    private function parseRaInput(?string $input): ?float
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $input = trim($input);

        // Check if input contains spaces (sexagesimal format: hh mm ss.sss)
        if (preg_match('/^(\d+)\s+(\d+)\s+([\d.]+)$/', $input, $matches)) {
            $hours = floatval($matches[1]);
            $minutes = floatval($matches[2]);
            $seconds = floatval($matches[3]);

            return $hours + ($minutes / 60.0) + ($seconds / 3600.0);
        }

        if (!is_numeric($input)) {
            return null;
        }

        // Decimal format: hours, unless the value can only be degrees (> 24)
        $value = floatval($input);

        return $value > 24 ? $value / 15.0 : $value;
    }

    /**
     * Parse Declination input (supports both decimal degrees and sexagesimal dd mm ss.sss format).
     * Returns Decl in degrees (-90 to +90) for database storage.
     */
    private function parseDeclInput(?string $input): ?float
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $input = trim($input);

        // Check if input contains spaces (sexagesimal format: dd mm ss.sss or -dd mm ss.sss)
        if (preg_match('/^([+-]?\d+)\s+(\d+)\s+([\d.]+)$/', $input, $matches)) {
            // Sexagesimal format
            $degrees = floatval($matches[1]);
            $minutes = floatval($matches[2]);
            $seconds = floatval($matches[3]);

            // Handle negative values
            $sign = $degrees < 0 ? -1 : 1;
            $absDegrees = abs($degrees);

            // Convert to decimal degrees
            $decimalDegrees = $absDegrees + ($minutes / 60.0) + ($seconds / 3600.0);

            return $sign * $decimalDegrees;
        }

        // Decimal format (already in degrees)
        return floatval($input);
    }

    /**
     * Update a deepsky object in storage.
     * Only accessible by Administrators and Database Experts.
     */
    public function update(Request $request, string $slug)
    {
        // Find the object using similar logic to show()
        $record = null;

        // Try objectnames table first (for aliases)
        $on = DB::table('objectnames')
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(objectname) = ?', [mb_strtolower($slug)])
            ->orWhereRaw('LOWER(altname) = ?', [mb_strtolower($slug)])
            ->first();

        if ($on) {
            $record = DB::table('objects')->where('name', $on->objectname)->first();
        }

        // Try direct slug match on objects table
        if (!$record) {
            $record = DB::table('objects')->where('slug', $slug)->first();
        }

        // Try direct name match
        if (!$record) {
            $record = DB::table('objects')->where('name', $slug)->first();
        }

        if (!$record) {
            abort(404, 'Object not found');
        }

        // Convert to DeepskyObject model instance
        $object = DeepskyObject::where('name', $record->name)->firstOrFail();

        // Authorize
        $this->authorize('update', $object);

        // Debug logging to see what's being submitted
        \Log::info('Object update request data:', [
            'part_of' => $request->input('part_of'),
            'contains' => $request->input('contains'),
            'part_of_type' => gettype($request->input('part_of')),
            'contains_type' => gettype($request->input('contains')),
            'alternative_names' => $request->input('alternative_names'),
            'all_input' => $request->all(),
        ]);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ra' => 'required|string',
            'decl' => 'required|string',
            'con' => 'nullable|string|max:5',
            'type' => 'required|string|exists:deepskytypes,code',
            'mag' => 'nullable|numeric',
            'subr' => 'nullable|numeric',
            'diam1' => 'nullable|numeric',
            'diam2' => 'nullable|numeric',
            'pa' => 'nullable|integer',
            'description' => 'nullable|string|max:1024',
            'datasource' => 'nullable|string|max:50',
            'alternative_names' => 'nullable|string',
            'part_of' => 'nullable|array',
            'part_of.*' => 'string|max:255',
            'contains' => 'nullable|array',
            'contains.*' => 'string|max:255',
        ]);

        // Parse RA and Decl (support both decimal and sexagesimal formats)
        $raHours = $this->parseRaInput($validated['ra']);
        $declDegrees = $this->parseDeclInput($validated['decl']);

        // Ensure RA and Decl were parsed successfully
        if ($raHours === null || $declDegrees === null) {
            return back()->withErrors([
                'ra' => $raHours === null ? 'Invalid RA format' : null,
                'decl' => $declDegrees === null ? 'Invalid Declination format' : null,
            ])->withInput();
        }

        // Store the original name and slug before updating, for updating related tables
        $originalName = $object->getOriginal('name');
        $originalSlug = $object->getOriginal('slug');
        $renamed = $originalName !== $validated['name'];

        // Auto-recalculate constellation and all atlas pages from the new coordinates.
        $atlasData = $this->coordinateDerivedFields($raHours, $declDegrees, 'object update');
        $newCon = $atlasData['con'] ?? ($validated['con'] ?? $object->con);
        unset($atlasData['con']);

        // Update the object
        $object->fill(array_merge([
            'name' => $validated['name'],
            'ra' => $raHours,
            'decl' => $declDegrees,
            'con' => $newCon,
            'type' => $validated['type'],
            'description' => $validated['description'] ?? $object->description,
            'datasource' => $validated['datasource'] ?? $object->datasource,
            'timestamp' => now(),
        ], self::measurementColumns($validated), $atlasData));

        // A renamed object gets the slug of its new name, unless another object has it
        if ($renamed) {
            $newSlug = \Illuminate\Support\Str::slug($validated['name'], '-');
            if ($newSlug !== '' && !DB::table('objects')->where('slug', $newSlug)->where('name', '!=', $originalName)->exists()) {
                $object->slug = $newSlug;
            }
        }

        $object->save();

        // If name changed, update all related tables
        if ($renamed) {
            // Observations and observing list entries refer to the object by name
            \App\Models\Observation::ofDeepskyObject($originalName)
                ->update(['target_key' => $validated['name'], 'target_name' => $validated['name']]);

            DB::table('observing_list_items')
                ->where('object_name', $originalName)
                ->update(['object_name' => $validated['name']]);

            // Update objectnames table
            DB::table('objectnames')
                ->where('objectname', $originalName)
                ->update(['objectname' => $validated['name']]);

            // Update objectpartof table (both where this object is parent and child)
            DB::table('objectpartof')
                ->where('objectname', $originalName)
                ->update(['objectname' => $validated['name']]);

            DB::table('objectpartof')
                ->where('partofname', $originalName)
                ->update(['partofname' => $validated['name']]);
        }

        // Handle alternative names - only if the field was actually submitted
        // Check if alternative_names key exists in the request (even if empty)
        if ($request->has('alternative_names')) {
            $altNamesInput = trim($validated['alternative_names'] ?? '');

            // Aliases that are kept keep their catalog and catindex: these come from the
            // old database and cannot always be derived from the name.
            $previousNames = DB::table('objectnames')
                ->where('objectname', $object->name)
                ->get(['altname', 'catalog', 'catindex'])
                ->keyBy('altname');

            // Delete existing alternative names for this object
            DB::table('objectnames')->where('objectname', $object->name)->delete();

            // Add new alternative names if any were provided
            if (!empty($altNamesInput)) {
                $altNames = array_filter(array_map('trim', explode(',', $altNamesInput)));

                \Log::info('Processing alternative names:', [
                    'object' => $object->name,
                    'raw' => $altNamesInput,
                    'parsed' => $altNames,
                    'count' => count($altNames),
                ]);

                foreach ($altNames as $altName) {
                    if (!empty($altName)) {

                        $previous = $previousNames->get($altName);
                        if ($previous) {
                            $catalog = (string) $previous->catalog;
                            $catindex = (string) $previous->catindex;
                        } else {
                            [$catalog, $catindex] = \App\Models\ObjectName::splitCatalogDesignation($altName);
                        }

                        $slug = \Illuminate\Support\Str::slug($altName);

                        // Check if this slug exists for another object
                        $existingEntry = DB::table('objectnames')->where('slug', $slug)->first();

                        if ($existingEntry) {
                            // Check if the object this entry points to actually exists
                            $objectExists = DB::table('objects')->where('name', $existingEntry->objectname)->exists();

                            if (!$objectExists || $existingEntry->objectname === $object->name) {
                                // Safe to delete: either orphaned or belongs to current object
                                DB::table('objectnames')->where('slug', $slug)->delete();
                                \Log::info('Deleted orphaned/old alternative name entry:', ['slug' => $slug, 'old_object' => $existingEntry->objectname]);
                            } else {
                                // Slug is in use by a different, valid object - skip
                                \Log::warning('Skipped alternative name due to slug conflict with existing object:', [
                                    'altName' => $altName,
                                    'slug' => $slug,
                                    'conflicting_object' => $existingEntry->objectname,
                                ]);
                                continue; // Skip this alternative name
                            }
                        }

                        // Now insert the new entry
                        DB::table('objectnames')->insert([
                            'objectname' => $object->name,
                            'catalog' => $catalog,
                            'catindex' => $catindex,
                            'altname' => $altName,
                            'slug' => $slug,
                            'timestamp' => now(),
                        ]);
                        \Log::info('Inserted alternative name:', ['altName' => $altName, 'slug' => $slug]);
                    }
                }
            } else {
                \Log::info('No alternative names provided, all removed for object:', ['object' => $object->name]);
            }
        }

        // Handle "part of" relationships (this object is part of...)
        // Delete existing "part of" relationships for this object first
        DB::table('objectpartof')->where('objectname', $object->name)->delete();

        if (isset($validated['part_of']) && is_array($validated['part_of'])) {
            // Add new "part of" relationships
            foreach ($validated['part_of'] as $parentName) {
                if (!empty($parentName) && is_string($parentName)) {
                    DB::table('objectpartof')->insert([
                        'objectname' => $object->name,
                        'partofname' => $parentName,
                        'timestamp' => now(),
                    ]);
                }
            }
        }

        // Handle "contains" relationships (objects that are part of this object)
        // Delete existing "contains" relationships for this object first
        DB::table('objectpartof')->where('partofname', $object->name)->delete();

        if (isset($validated['contains']) && is_array($validated['contains'])) {
            // Add new "contains" relationships
            foreach ($validated['contains'] as $childName) {
                if (!empty($childName) && is_string($childName)) {
                    DB::table('objectpartof')->insert([
                        'objectname' => $childName,
                        'partofname' => $object->name,
                        'timestamp' => now(),
                    ]);
                }
            }
        }

        // Always ensure the object's own canonical name has an entry in objectnames.
        // This row stores the catalog designation (e.g. catalog='M', catindex='65' for 'M 65')
        // and may have been deleted above if the user did not include it in the form.
        $canonicalSlug = \Illuminate\Support\Str::slug($object->name);
        $canonicalExists = DB::table('objectnames')
            ->where('objectname', $object->name)
            ->where('altname', $object->name)
            ->exists();
        if (!$canonicalExists) {
            [$cat, $catIdx] = \App\Models\ObjectName::splitCatalogDesignation($object->name);
            // Only insert if the slug is not already claimed by a different object
            $slugTaken = DB::table('objectnames')
                ->where('slug', $canonicalSlug)
                ->where('objectname', '!=', $object->name)
                ->exists();
            if (!$slugTaken) {
                DB::table('objectnames')->insert([
                    'objectname' => $object->name,
                    'catalog' => $cat,
                    'catindex' => $catIdx,
                    'altname' => $object->name,
                    'slug' => $canonicalSlug,
                    'timestamp' => now(),
                ]);
            }
        }

        ObjectSearchIndex::syncObject($object->name, [$originalName, $originalSlug]);

        return redirect()->route('object.show', ['slug' => $object->slug ?? $object->name])
            ->with('success', __('Object updated successfully.'));
    }

    /**
     * Translate object name to SIMBAD-compatible format.
     * Handles catalog naming differences.
     */
    private function translateObjectNameForSimbad(string $objectName): string
    {
        // Common catalog name translations for SIMBAD
        $translations = [
            // Hickson Compact Groups
            '/^Hickson\s+/i' => 'HCG ',
        ];

        foreach ($translations as $pattern => $replacement) {
            $objectName = preg_replace($pattern, $replacement, $objectName);
        }

        return $objectName;
    }

    /**
     * Update object data from SIMBAD.
     * Only accessible by Administrators and Database Experts.
     */
    /**
     * Fetch SIMBAD data for an object without updating it.
     * Returns JSON with the fetched data.
     */
    public function fetchSimbadData(Request $request, string $slug)
    {
        // Find the object
        $record = null;

        $on = DB::table('objectnames')
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(objectname) = ?', [mb_strtolower($slug)])
            ->orWhereRaw('LOWER(altname) = ?', [mb_strtolower($slug)])
            ->first();

        if ($on) {
            $record = DB::table('objects')->where('name', $on->objectname)->first();
        }

        if (!$record) {
            $record = DB::table('objects')->where('slug', $slug)->first();
        }

        if (!$record) {
            $record = DB::table('objects')->where('name', $slug)->first();
        }

        if (!$record) {
            return response()->json(['error' => 'Object not found'], 404);
        }

        $object = DeepskyObject::where('name', $record->name)->firstOrFail();
        $this->authorize('update', $object);

        try {
            $objectName = $this->translateObjectNameForSimbad($object->name);
            $simbadUrl = "https://simbad.cds.unistra.fr/simbad/sim-id?output.format=votable&Ident=" . urlencode($objectName);

            $response = @file_get_contents($simbadUrl);

            if ($response === false) {
                return response()->json(['error' => 'Failed to fetch data from SIMBAD.'], 500);
            }

            $xml = simplexml_load_string($response);
            if (!$xml) {
                return response()->json(['error' => 'Failed to parse SIMBAD response.'], 500);
            }

            $namespaces = $xml->getNamespaces(true);
            $votNs = $namespaces[''] ?? null;

            if ($votNs) {
                $xml->registerXPathNamespace('vot', $votNs);
                $rows = $xml->xpath('//vot:TR');
            } else {
                $rows = $xml->xpath('//TR');
            }

            if (empty($rows)) {
                return response()->json(['error' => 'No data found in SIMBAD for this object.'], 404);
            }

            $row = $rows[0];
            $cells = $votNs ? $row->children($votNs) : $row->children();
            $fields = $votNs ? $xml->xpath('//vot:FIELD') : $xml->xpath('//FIELD');

            $data = [];
            $columnIndex = 0;

            foreach ($fields as $field) {
                $fieldName = (string) $field['name'];
                $value = (string) $cells[$columnIndex];

                switch ($fieldName) {
                    case 'RA':
                    case 'RA_d':
                        if (!empty($value) && is_numeric($value)) {
                            $data['ra'] = floatval($value);
                        }
                        break;
                    case 'DEC':
                    case 'DEC_d':
                        if (!empty($value) && is_numeric($value)) {
                            $data['decl'] = floatval($value);
                        }
                        break;
                    case 'GALDIM_MAJAXIS':
                        if (!empty($value) && is_numeric($value)) {
                            $data['diam1'] = floatval($value);
                        }
                        break;
                    case 'GALDIM_MINAXIS':
                        if (!empty($value) && is_numeric($value)) {
                            $data['diam2'] = floatval($value);
                        }
                        break;
                    case 'GALDIM_ANGLE':
                        if (!empty($value) && is_numeric($value)) {
                            $data['pa'] = intval($value);
                        }
                        break;
                    case 'FLUX_V':
                        if (!empty($value) && is_numeric($value)) {
                            $data['mag'] = floatval($value);
                        }
                        break;
                }

                $columnIndex++;
            }

            if (empty($data)) {
                return response()->json(['error' => 'No relevant data found in SIMBAD response.'], 404);
            }

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            Log::error('SIMBAD fetch failed', ['error' => $e->getMessage(), 'object' => $object->name]);
            return response()->json(['error' => 'An error occurred while fetching from SIMBAD: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show initial create form (enter name/catalog)
     */
    public function create(Request $request)
    {
        // Use distinct object catalog codes from objectnames, not atlas chart codes
        $catalogs = DB::table('objectnames')
            ->select('catalog')
            ->whereNotNull('catalog')
            ->where('catalog', '!=', '')
            ->distinct()
            ->orderBy('catalog')
            ->pluck('catalog');

        return view('object.create_name', compact('catalogs'));
    }

    /**
     * Show a simple comet-selection form (select from comets_orbital_elements)
     */
    public function createComet(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = DB::table('comets_orbital_elements')
            ->whereRaw('NOT EXISTS (SELECT 1 FROM cometobjects WHERE LOWER(cometobjects.name) = LOWER(comets_orbital_elements.name))');

        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        $comets = $query->orderBy('name')->limit(200)->get();

        return view('object.create_comet', compact('q', 'comets'));
    }

    /**
     * Persist selected comet from comets_orbital_elements into cometobjects if missing.
     */
    public function storeComet(Request $request)
    {
        $request->validate([
            'comet_name' => 'required|string|exists:comets_orbital_elements,name',
        ]);

        $name = (string) $request->input('comet_name');

        // Check if the comet already exists (case-insensitive match on name)
        $existing = DB::table('cometobjects')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        if ($existing) {
            // Redirect to existing comet page (prefer slug when available)
            $slug = $existing->slug ?? null;
            if (!empty($slug)) {
                return redirect()->route('object.show', ['slug' => $slug])->with('success', __('Comet already exists.'));
            }
            return redirect()->route('object.show', ['slug' => $existing->id])->with('success', __('Comet already exists.'));
        }

        // Prepare insert data
        $icqname = mb_substr($name, 0, 11);
        $timestamp = now();
        $data = [
            'name' => $name,
            'icqname' => $icqname,
            'timestamp' => $timestamp,
        ];

        // If the cometobjects table has a slug column, generate a unique slug
        if (Schema::hasColumn('cometobjects', 'slug')) {
            $base = \Illuminate\Support\Str::slug($name, '-');
            if (empty($base)) {
                $base = 'comet';
            }
            $slug = $base;
            $i = 0;
            while (DB::table('cometobjects')->where('slug', $slug)->exists()) {
                $i++;
                $slug = $base . '-' . $i;
            }
            $data['slug'] = $slug;
        }

        try {
            $id = DB::table('cometobjects')->insertGetId($data);
        } catch (\Exception $e) {
            Log::error('Failed to create cometobject', ['error' => $e->getMessage(), 'name' => $name]);
            return redirect()->back()->with('error', __('Failed to add comet: :msg', ['msg' => $e->getMessage()]));
        }

        // Ensure slug exists if column is present but wasn't set for some reason
        if (!isset($data['slug']) && Schema::hasColumn('cometobjects', 'slug')) {
            $base = \Illuminate\Support\Str::slug($name, '-');
            if (empty($base)) {
                $base = 'comet';
            }
            $slug = $base;
            $i = 0;
            while (DB::table('cometobjects')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $i++;
                $slug = $base . '-' . $i;
            }
            try {
                DB::table('cometobjects')->where('id', $id)->update(['slug' => $slug]);
            } catch (\Throwable $_) {
                // ignore update failures
            }
        }

        $row = DB::table('cometobjects')->where('id', $id)->first();

        // Add a search_index row so the comet is immediately discoverable
        try {
            DB::table('search_index')->where('source_table', 'cometobjects')->where('source_pk', (string) $id)->delete();
            DB::table('search_index')->insert([
                'name' => $row->name,
                'name_normalized' => mb_strtolower($row->name),
                'source_table' => 'cometobjects',
                'source_pk' => (string) $id,
                'display_name' => $row->name,
                'source_type' => 'comet',
                'ra' => null,
                'decl' => null,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update search_index for cometobjects', ['error' => $e->getMessage(), 'id' => $id]);
        }

        if (!empty($row->slug)) {
            return redirect()->route('object.show', ['slug' => $row->slug])->with('success', __('Comet added.'));
        }

        return redirect()->route('object.show', ['slug' => $row->id])->with('success', __('Comet added.'));
    }

    /**
     * POST step 1: validate the name input and redirect to the GET search page.
     */
    public function checkName(Request $request)
    {
        $request->validate([
            'catalog' => 'nullable|string',
            'number' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        return redirect()->route('object.nameSearch', $request->only(['catalog', 'number', 'name']));
    }

    /**
     * GET: show paginated similar-name candidates.
     *
     * Search strategy when a catalog + numeric number are given (e.g. catalog=M, number=111):
     *  1. Every object sharing that catindex in *any* catalog (Bochum 111, NGC 111, …)
     *  2. Objects in the *same* catalog whose catindex is within ±3 (M 108 … M 114)
     * For a free-text name (DB experts): substring match on objectname / altname.
     */
    public function nameSearch(Request $request)
    {
        $catalog = trim((string) $request->input('catalog', ''));
        $number = trim((string) $request->input('number', ''));
        $freeName = trim((string) $request->input('name', ''));

        // Canonical display name carried through the wizard
        if ($freeName !== '') {
            $name = $freeName;
        } elseif ($catalog !== '' || $number !== '') {
            $name = trim("$catalog $number");
        } else {
            $name = '';
        }

        // Base query: objectnames → objects → target_types (for human-readable type label).
        // Joined on the object name: an alias has its own slug, not the one of its object.
        $base = DB::table('objectnames')
            ->join('objects', 'objectnames.objectname', '=', 'objects.name')
            ->leftJoin('target_types', 'objects.type', '=', 'target_types.id')
            ->select(
                'objectnames.objectname',
                'objectnames.altname',
                'objectnames.catalog',
                'objectnames.catindex',
                'objects.ra',
                'objects.decl',
                'objects.type',
                'target_types.type as type_label',
                'objects.slug'
            );

        $catindex = is_numeric($number) ? (int) $number : null;

        if ($catindex !== null) {
            $candidates = (clone $base)
                ->where(function ($q) use ($catindex, $catalog) {
                    $q->where('objectnames.catindex', $catindex);
                    if ($catalog !== '') {
                        $q->orWhere(function ($q2) use ($catalog, $catindex) {
                            $q2->where('objectnames.catalog', $catalog)
                                ->whereBetween('objectnames.catindex', [$catindex - 3, $catindex + 3]);
                        });
                    }
                })
                ->orderBy('objectnames.catalog')
                ->orderBy('objectnames.catindex')
                ->paginate(25)
                ->withQueryString();
        } elseif ($name !== '') {
            $q = mb_strtolower($name);
            $candidates = (clone $base)
                ->where(function ($qb) use ($q) {
                    $qb->whereRaw('LOWER(objectnames.objectname) LIKE ?', ["%{$q}%"])
                        ->orWhereRaw('LOWER(objectnames.altname) LIKE ?', ["%{$q}%"]);
                })
                ->orderBy('objectnames.catalog')
                ->orderBy('objectnames.catindex')
                ->paginate(25)
                ->withQueryString();
        } else {
            $candidates = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        }

        return view('object.name_check', compact('name', 'candidates'));
    }

    /**
     * Show coordinates entry form
     */
    public function coordsForm(Request $request)
    {
        $name = $request->input('name');
        return view('object.coords', compact('name'));
    }

    /**
     * Check nearby objects for given coords
     */
    public function checkCoords(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'ra' => 'required|string',
            'decl' => 'required|string',
        ]);

        // Validate coordinates before redirecting so we can surface errors early
        if (
            DeepskyObject::raToDecimal($request->input('ra')) === null ||
            DeepskyObject::decToDecimal($request->input('decl')) === null
        ) {
            return redirect()->back()->withErrors(['coords' => __('Invalid coordinates provided.')])->withInput();
        }

        return redirect()->route('object.coordsSearch', $request->only(['name', 'ra', 'decl']));
    }

    /**
     * GET: show paginated list of objects near the given coordinates.
     */
    public function coordsSearch(Request $request)
    {
        $name = $request->input('name');
        $raInput = $request->input('ra');
        $decInput = $request->input('decl');

        $raDeg = DeepskyObject::raToDecimal($raInput);
        $decDeg = DeepskyObject::decToDecimal($decInput);

        if ($raDeg === null || $decDeg === null) {
            return redirect()->route('object.coordsForm', ['name' => $name])
                ->withErrors(['coords' => __('Invalid coordinates provided.')]);
        }

        // DB stores RA in decimal hours (0–24); raToDecimal() returns degrees (0–360).
        // Convert to hours so the whereBetween comparison is in the same unit as the DB.
        $raHours = $raDeg / 15.0;
        $decRadius = 0.5;          // degrees
        $raRadius = $decRadius / 15.0; // equivalent hours

        $nearby = DB::table('objects')
            ->leftJoin('target_types', 'objects.type', '=', 'target_types.id')
            ->select(
                'objects.name',
                'objects.slug',
                'objects.ra',
                'objects.decl',
                'objects.type',
                'target_types.type as type_label'
            )
            ->whereBetween('objects.ra', [$raHours - $raRadius, $raHours + $raRadius])
            ->whereBetween('objects.decl', [$decDeg - $decRadius, $decDeg + $decRadius])
            ->orderByRaw('ABS(objects.ra - ?) + ABS(objects.decl - ?)', [$raHours, $decDeg])
            ->paginate(25)
            ->withQueryString();

        return view('object.coords_check', compact('name', 'raInput', 'decInput', 'nearby'));
    }

    /**
     * Show details form for final input
     */
    public function detailsForm(Request $request)
    {
        $name = $request->input('name');
        $ra = $request->input('ra');
        $decl = $request->input('decl');
        $types = DeepskyType::all();

        $constellation = null;
        $constellationName = null;
        try {
            $raDeg = DeepskyObject::raToDecimal($ra);
            $decDeg = DeepskyObject::decToDecimal($decl);
            if ($raDeg !== null && $decDeg !== null) {
                $raHours = $raDeg / 15.0;
                $coords = new EquatorialCoordinates($raHours, $decDeg);
                $constellation = $coords->getConstellation();
                $constellationName = ConstellationModel::find($constellation)?->name ?? $constellation;
            }
        } catch (\Throwable $e) {
            // Leave $constellation null; it will be auto-calculated in store()
        }

        return view('object.details', compact('name', 'ra', 'decl', 'types', 'constellation', 'constellationName'));
    }

    /**
     * Persist the new object to DB
     */
    public function store(Request $request)
    {
        // If a new catalog code was provided, normalize to uppercase before validation
        if ($request->filled('catalog_create_code')) {
            $request->merge(['catalog_create_code' => strtoupper(trim($request->input('catalog_create_code')))]);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'ra' => 'nullable|string',
            'decl' => 'nullable|string',
            'type' => 'required|string|exists:deepskytypes,code',
            'mag' => 'nullable|numeric',
            'subr' => 'nullable|numeric',
            'diam1' => 'nullable|numeric',
            'diam2' => 'nullable|numeric',
            'pa' => 'nullable|integer',
            'catalog' => 'nullable|string',
            'catalog_create_code' => ['nullable', 'string', 'regex:/^[A-Z0-9_-]{1,10}$/', 'unique:atlases,code'],
            'catalog_create_description' => ['nullable', 'string', 'max:255'],
        ]);

        // Convert RA to decimal hours (DB stores hours, not degrees)
        if (!empty($validated['ra'])) {
            $raDeg = DeepskyObject::raToDecimal($validated['ra']);
            if ($raDeg !== null)
                $validated['ra'] = $raDeg / 15.0;
        }
        if (!empty($validated['decl'])) {
            $decDeg = DeepskyObject::decToDecimal($validated['decl']);
            if ($decDeg !== null)
                $validated['decl'] = $decDeg;
        }

        // Auto-calculate constellation and atlas pages from RA/Dec
        if (!empty($validated['ra']) && !empty($validated['decl'])) {
            $validated = array_merge($validated, $this->coordinateDerivedFields((float) $validated['ra'], (float) $validated['decl'], 'object creation'));
        }

        // Magnitudes, diameters (arcminutes → arcseconds), position angle and SBObj
        $validated = array_merge($validated, self::measurementColumns($validated));

        // If privileged user requested creating a new catalog, persist it first
        try {
            $user = Auth::user();
            if ($user && ($user->isAdministrator() || $user->isDatabaseExpert())) {
                $newCode = trim($request->input('catalog_create_code'));
                if (!empty($newCode)) {
                    // Insert if not exists
                    DB::table('atlases')->insertOrIgnore([
                        'code' => $newCode,
                        'name' => $newCode,
                    ]);
                    // Make sure the object's catalog references the new code
                    $validated['catalog'] = $newCode;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Catalog creation during object add failed', ['error' => $e->getMessage()]);
        }

        // Create a DeepskyObject record — only pass columns that exist in the objects table.
        // Provide empty/zero defaults for legacy NOT NULL columns that have no DB default.
        try {
            $defaults = [
                'ra' => 0.0,
                'decl' => 0.0,
                'type' => '',
                'con' => '',
                'mag' => 99.9,
                'subr' => 99.9,
                'pa' => 999,
                'diam1' => 0.0,
                'diam2' => 0.0,
                'urano' => 0,
                'urano_new' => 0,
                'sky' => 0,
                'millenium' => '',
                'taki' => '',
                'SBObj' => -999,
                'description' => '',
                'psa' => '',
                'torresB' => '',
                'torresBC' => '',
                'torresC' => '',
                'milleniumbase' => '',
                'DSLDL' => '',
                'DSLDP' => '',
                'DSLLL' => '',
                'DSLLP' => '',
                'DSLOL' => '',
                'DSLOP' => '',
                'DeepskyHunter' => '',
                'Interstellarum' => '',
            ];
            $userValues = array_filter(array_intersect_key($validated, array_flip([
                'name',
                'type',
                'con',
                'ra',
                'decl',
                'mag',
                'subr',
                'pa',
                'diam1',
                'diam2',
                'SBObj',
                'urano',
                'urano_new',
                'sky',
                'millenium',
                'taki',
                'psa',
                'torresB',
                'torresBC',
                'torresC',
                'milleniumbase',
                'DSLDL',
                'DSLDP',
                'DSLLL',
                'DSLLP',
                'DSLOL',
                'DSLOP',
                'DeepskyHunter',
                'Interstellarum',
            ])), fn($v) => $v !== null && $v !== '');
            $objectData = array_merge($defaults, $userValues);
            $objectData['name'] = $validated['name'];
            $objectData['slug'] = \Illuminate\Support\Str::slug($validated['name'], '-');
            $objectData['timestamp'] = now();
            $obj = DeepskyObject::create($objectData);
        } catch (\Exception $e) {
            Log::error('Failed to create object', ['error' => $e->getMessage(), 'data' => $validated]);
            return redirect()->back()->withInput()->with('error', __('Failed to create object: :msg', ['msg' => $e->getMessage()]));
        }

        // Insert a primary entry into objectnames so the catalog appears in the dropdown
        // on the create page and so object lookups via objectnames.slug work correctly.
        try {
            $objName = $objectData['name'];
            $objSlug = $objectData['slug'];

            // Extract catalog code and catindex from the name (e.g. "Wdm 1" → "Wdm", "1")
            [$catalogCode, $catindex] = \App\Models\ObjectName::splitCatalogDesignation($objName);

            // Only insert if there is no existing entry for this object name
            $exists = DB::table('objectnames')->where('objectname', $objName)->exists();
            if (!$exists) {
                DB::table('objectnames')->insert([
                    'objectname' => $objName,
                    'catalog' => $catalogCode,
                    'catindex' => $catindex,
                    'altname' => $objName,
                    'slug' => $objSlug,
                    'timestamp' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to insert objectnames entry for new object', [
                'error' => $e->getMessage(),
                'name' => $objectData['name'] ?? null,
            ]);
        }

        ObjectSearchIndex::syncObject($obj->name);

        return redirect()->route('object.show', ['slug' => $obj->slug ?? $obj->name])->with('success', __('Object added successfully.'));
    }

    /**
     * Lookup SIMBAD by arbitrary name (for create wizard)
     */
    public function simbadLookupByName(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $name = $request->input('name');
        $name = $this->translateObjectNameForSimbad($name);

        $simbadUrl = "https://simbad.cds.unistra.fr/simbad/sim-id?output.format=votable&Ident=" . urlencode($name);
        $response = @file_get_contents($simbadUrl);
        if ($response === false) {
            return response()->json(['error' => 'Failed to fetch data from SIMBAD.'], 500);
        }

        $xml = simplexml_load_string($response);
        if (!$xml) {
            return response()->json(['error' => 'Failed to parse SIMBAD response.'], 500);
        }

        $namespaces = $xml->getNamespaces(true);
        $votNs = $namespaces[''] ?? null;

        if ($votNs) {
            $xml->registerXPathNamespace('vot', $votNs);
            $rows = $xml->xpath('//vot:TR');
        } else {
            $rows = $xml->xpath('//TR');
        }

        if (empty($rows)) {
            return response()->json(['error' => 'No data found in SIMBAD for this name.'], 404);
        }

        $row = $rows[0];
        $cells = $votNs ? $row->children($votNs) : $row->children();
        $fields = $votNs ? $xml->xpath('//vot:FIELD') : $xml->xpath('//FIELD');

        // Mapping from SIMBAD OTYPE_S short codes to DeepskyLog type codes
        $simbadTypeMap = [
            // Galaxies
            'G' => 'GALXY',
            'GiC' => 'GALXY',
            'GiG' => 'GALXY',
            'GiP' => 'GALXY',
            'GiA' => 'GALXY',
            'BiG' => 'GALXY',
            'LSB' => 'GALXY',
            'bCG' => 'GALXY',
            'EmG' => 'GALXY',
            'H2G' => 'GALXY',
            'HzG' => 'GALXY',
            'IG' => 'GALXY',
            'Compact_Eg' => 'GALXY',
            'Sa' => 'GALXY',
            'SB' => 'GALXY',
            // AGN / Quasars
            'AGN' => 'QUASR',
            'SyG' => 'QUASR',
            'Sy1' => 'QUASR',
            'Sy2' => 'QUASR',
            'QSO' => 'QUASR',
            'BLL' => 'QUASR',
            'LIN' => 'QUASR',
            'Seyfert' => 'QUASR',
            // Galaxy groups/clusters
            'ClG' => 'GALCL',
            'GrG' => 'GALCL',
            'CGG' => 'GALCL',
            'PaG' => 'GALCL',
            // Globular clusters
            'GlC' => 'GLOCL',
            'GlA' => 'GLOCL',
            // Open clusters
            'Cl*' => 'OPNCL',
            'OpC' => 'OPNCL',
            // Associations / Asterisms
            'As*' => 'ASTER',
            // Planetary nebulae
            'PN' => 'PLNNB',
            'PNe' => 'PLNNB',
            // Supernova remnants
            'SNR' => 'SNREM',
            // Supernovae
            'SN*' => 'SNOVA',
            // HII regions
            'HII' => 'HII',
            // Reflection nebulae
            'RNe' => 'REFNB',
            'RfN' => 'REFNB',
            // Dark nebulae / molecular clouds
            'DkN' => 'DRKNB',
            'MoC' => 'DRKNB',
            'DNe' => 'DRKNB',
            // Double/multiple stars
            '**' => 'DS',
            // Stars
            '*' => 'AA1STAR',
            'V*' => 'AA1STAR',
            // Emission nebulae
            'EmO' => 'EMINB',
            'Neb' => 'BRTNB',
        ];

        // Priority order for magnitude: V > B > R > G > I (use best available optical band)
        $magPriority = ['FLUX_V', 'FLUX_B', 'FLUX_R', 'FLUX_G', 'FLUX_I'];
        $magBestRank = count($magPriority) + 1;

        $data = [];
        $columnIndex = 0;
        foreach ($fields as $field) {
            $fieldName = (string) $field['name'];
            $value = trim((string) ($cells[$columnIndex] ?? ''));
            switch ($fieldName) {
                case 'RA':
                case 'RA_d':
                    // Returned in hours, as the RA inputs of the create wizard expect
                    $raHours = self::simbadRaHours($fieldName, $value);
                    if ($raHours !== null) {
                        $data['ra'] = $raHours;
                    }
                    break;
                case 'DEC':
                case 'DEC_d':
                    if ($value !== '' && is_numeric($value)) {
                        $data['decl'] = floatval($value);
                    }
                    break;
                case 'FLUX_V':
                case 'FLUX_B':
                case 'FLUX_R':
                case 'FLUX_G':
                case 'FLUX_I':
                    if ($value !== '' && is_numeric($value)) {
                        $rank = array_search($fieldName, $magPriority);
                        if ($rank < $magBestRank) {
                            $magBestRank = $rank;
                            $data['mag'] = round(floatval($value), 2);
                        }
                    }
                    break;
                case 'OTYPE_S':
                    $otype = $value;
                    // Strip candidate suffix (e.g. AGN_Candidate -> AGN, G_Candidate -> G)
                    $otypeBase = preg_replace('/_Candidate$/i', '', $otype);
                    foreach ([$otype, $otypeBase] as $candidate) {
                        if (!empty($candidate) && isset($simbadTypeMap[$candidate])) {
                            $data['type_code'] = $simbadTypeMap[$candidate];
                            break;
                        }
                    }
                    break;
                case 'GALDIM_MAJAXIS':
                    if ($value !== '' && is_numeric($value)) {
                        $data['diam1'] = round(floatval($value), 2);
                    }
                    break;
                case 'GALDIM_MINAXIS':
                    if ($value !== '' && is_numeric($value)) {
                        $data['diam2'] = round(floatval($value), 2);
                    }
                    break;
                case 'GALDIM_ANGLE':
                    if ($value !== '' && is_numeric($value)) {
                        $data['pa'] = intval(round(floatval($value)));
                    }
                    break;
            }
            $columnIndex++;
        }

        // Calculate surface brightness from mag + diameters when available
        // GALDIM values are in arcminutes; formula: mag + 2.5*log10(2827 * d1 * d2)
        if (
            isset($data['mag']) && isset($data['diam1']) && isset($data['diam2'])
            && $data['diam1'] > 0 && $data['diam2'] > 0
        ) {
            $data['subr'] = round(
                $data['mag'] + 2.5 * log10(2827.0 * ($data['diam1'] / 60.0) * ($data['diam2'] / 60.0)),
                2
            );
        }

        if (empty($data)) {
            return response()->json(['error' => 'No relevant data found in SIMBAD response.'], 404);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function updateFromSimbad(Request $request, string $slug)
    {
        // Find the object using similar logic to show()
        $record = null;

        // Try objectnames table first (for aliases)
        $on = DB::table('objectnames')
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(objectname) = ?', [mb_strtolower($slug)])
            ->orWhereRaw('LOWER(altname) = ?', [mb_strtolower($slug)])
            ->first();

        if ($on) {
            $record = DB::table('objects')->where('name', $on->objectname)->first();
        }

        // Try direct slug match on objects table
        if (!$record) {
            $record = DB::table('objects')->where('slug', $slug)->first();
        }

        // Try direct name match
        if (!$record) {
            $record = DB::table('objects')->where('name', $slug)->first();
        }

        if (!$record) {
            abort(404, 'Object not found');
        }

        // Convert to DeepskyObject model instance
        $object = DeepskyObject::where('name', $record->name)->firstOrFail();

        // Authorize
        $this->authorize('update', $object);

        try {
            // Query SIMBAD for object data
            // Using SIMBAD TAP service or basic query
            $objectName = $this->translateObjectNameForSimbad($object->name);

            // Build SIMBAD query URL (using votable format for easy parsing)
            $simbadUrl = "https://simbad.cds.unistra.fr/simbad/sim-id?output.format=votable&Ident=" . urlencode($objectName);

            // Fetch data from SIMBAD
            $response = @file_get_contents($simbadUrl);

            if ($response === false) {
                return redirect()->back()->with('error', __('Failed to fetch data from SIMBAD.'));
            }

            // Parse VOTable response
            $xml = simplexml_load_string($response);
            if (!$xml) {
                return redirect()->back()->with('error', __('Failed to parse SIMBAD response.'));
            }

            // Extract data from VOTable
            // SIMBAD returns data in RESOURCE/TABLE/DATA/TABLEDATA/TR/TD elements
            $namespaces = $xml->getNamespaces(true);
            $votNs = $namespaces[''] ?? null;

            // Register namespace if needed
            if ($votNs) {
                $xml->registerXPathNamespace('vot', $votNs);
                $rows = $xml->xpath('//vot:TR');
            } else {
                $rows = $xml->xpath('//TR');
            }

            if (empty($rows)) {
                return redirect()->back()->with('error', __('No data found in SIMBAD for this object.'));
            }

            // Parse the first row of data
            $row = $rows[0];
            $cells = $votNs ? $row->children($votNs) : $row->children();

            // Typical SIMBAD votable structure has: MAIN_ID, RA, DEC, MAJ_AXIS, MIN_AXIS, etc.
            // We'll need to check the FIELD definitions to know which column is which
            $fields = $votNs ? $xml->xpath('//vot:FIELD') : $xml->xpath('//FIELD');

            $updates = [];
            $columnIndex = 0;

            foreach ($fields as $field) {
                $fieldName = (string) $field['name'];
                $value = (string) $cells[$columnIndex];

                switch ($fieldName) {
                    case 'RA':
                    case 'RA_d':
                        // The database stores RA in decimal hours (0..24)
                        $raHours = self::simbadRaHours($fieldName, $value);
                        if ($raHours !== null) {
                            $updates['ra'] = $raHours;
                        }
                        break;
                    case 'DEC':
                    case 'DEC_d':
                        if (!empty($value) && is_numeric($value)) {
                            $updates['decl'] = floatval($value);
                        }
                        break;
                    case 'GALDIM_MAJAXIS':
                        if (!empty($value) && is_numeric($value)) {
                            // SIMBAD returns diameter in arcminutes, convert to arcseconds for database
                            $updates['diam1'] = floatval($value) * 60;
                        }
                        break;
                    case 'GALDIM_MINAXIS':
                        if (!empty($value) && is_numeric($value)) {
                            // SIMBAD returns diameter in arcminutes, convert to arcseconds for database
                            $updates['diam2'] = floatval($value) * 60;
                        }
                        break;
                    case 'GALDIM_ANGLE':
                        if (!empty($value) && is_numeric($value)) {
                            $updates['pa'] = intval($value);
                        }
                        break;
                    case 'FLUX_V':
                        if (!empty($value) && is_numeric($value)) {
                            $updates['mag'] = floatval($value);
                        }
                        break;
                }

                $columnIndex++;
            }

            if (empty($updates)) {
                return redirect()->back()->with('warning', __('No relevant data found in SIMBAD response.'));
            }

            // Update the object with SIMBAD data
            $updates['datasource'] = 'SIMBAD';
            $object->fill($updates);

            // Keep the columns derived from the position and the brightness in step
            if (isset($updates['ra']) || isset($updates['decl'])) {
                $object->fill($this->coordinateDerivedFields((float) $object->ra, (float) $object->decl, 'SIMBAD update'));
            }
            if (isset($updates['mag']) || isset($updates['diam1']) || isset($updates['diam2'])) {
                $object->SBObj = self::surfaceBrightness($object->mag, $object->diam1, $object->diam2) ?? -999;
            }
            $object->timestamp = now();
            $object->save();
            ObjectSearchIndex::syncObject($object->name);

            return redirect()->route('object.show', ['slug' => $object->slug ?? $object->name])
                ->with('success', __('Object updated successfully from SIMBAD.'));

        } catch (\Exception $e) {
            Log::error('SIMBAD update failed', ['error' => $e->getMessage(), 'object' => $object->name]);
            return redirect()->back()->with('error', __('An error occurred while updating from SIMBAD: ') . $e->getMessage());
        }
    }

    /**
     * Delete an object. If a moveToSlug is provided, move all observations to the target object first.
     * Only Administrators and Database Experts can delete objects.
     */
    public function destroy(Request $request, string $slug)
    {
        // Resolve the object
        $record = null;
        $on = DB::table('objectnames')
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(objectname) = ?', [mb_strtolower($slug)])
            ->orWhereRaw('LOWER(altname) = ?', [mb_strtolower($slug)])
            ->first();

        if ($on) {
            $record = DB::table('objects')->where('name', $on->objectname)->first();
        }
        if (!$record) {
            $record = DB::table('objects')->where('slug', $slug)->first();
        }
        if (!$record) {
            $record = DB::table('objects')->where('name', $slug)->first();
        }

        if (!$record) {
            abort(404, 'Object not found');
        }

        $object = DeepskyObject::where('name', $record->name)->firstOrFail();

        // Authorize – only admins and database experts
        $this->authorize('delete', $object);

        $objectName = $object->name;

        // Move observations to another object if requested
        $moveToSlug = $request->input('move_to_slug');
        if (!empty($moveToSlug)) {
            // Resolve the target object
            $targetRecord = null;
            $targetOn = DB::table('objectnames')
                ->where('slug', $moveToSlug)
                ->orWhereRaw('LOWER(objectname) = ?', [mb_strtolower($moveToSlug)])
                ->orWhereRaw('LOWER(altname) = ?', [mb_strtolower($moveToSlug)])
                ->first();

            if ($targetOn) {
                $targetRecord = DB::table('objects')->where('name', $targetOn->objectname)->first();
            }
            if (!$targetRecord) {
                $targetRecord = DB::table('objects')->where('slug', $moveToSlug)->first();
            }
            if (!$targetRecord) {
                $targetRecord = DB::table('objects')->where('name', $moveToSlug)->first();
            }

            if (!$targetRecord) {
                return redirect()->back()->with('error', __('Target object not found.'));
            }

            $targetName = $targetRecord->name;

            // Move the observations (see the target scheme in App\Models\Observation)
            \App\Models\Observation::ofDeepskyObject($objectName)
                ->update(['target_table' => 'objects', 'target_key' => $targetName, 'target_name' => $targetName]);

            // Move observing list entries in the new observing_list_items table
            DB::table('observing_list_items')
                ->where('object_name', $objectName)
                ->update(['object_name' => $targetName]);
        } else {
            // No move target — remove any observing list entries for this object
            DB::table('observing_list_items')
                ->where('object_name', $objectName)
                ->delete();
        }

        // Remove associated records; the search_index rows of the object and
        // its aliases all have the slug of the object as source_pk
        ObjectSearchIndex::removeObject($objectName, $object->slug);
        DB::table('objectnames')->where('objectname', $objectName)->delete();
        DB::table('objectpartof')->where('objectname', $objectName)->orWhere('partofname', $objectName)->delete();

        // Delete the object itself
        $object->delete();

        Log::info('Object deleted', [
            'object' => $objectName,
            'moved_to' => $moveToSlug ?? null,
            'deleted_by' => Auth::id(),
        ]);

        return redirect()->route('dashboard')->with('success', __('Object :name has been deleted.', ['name' => $objectName]));
    }
}
