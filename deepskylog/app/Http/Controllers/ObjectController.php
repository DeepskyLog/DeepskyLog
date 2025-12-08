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
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Targets\Parabolic;
use deepskylog\AstronomyLibrary\Targets\NearParabolic;
use App\Helpers\HorizonsWrapper;
use App\Helpers\HorizonsDesignation;

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

            if (! $record) {
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
                if (! $record) {
                    $co = null;
                    foreach ($candidates as $cand) {
                        try {
                            $co = DB::table('cometobjects')->where('slug', $cand)->first();
                        } catch (\Throwable $_) {
                            $co = null;
                        }
                        if ($co) break;
                    }
                    if ($co) {
                        $record = $co;
                        $type = 'comet';
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
                        if ($record) {
                            $type = 'planet';
                        }
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
                        if ($record) {
                            $type = 'moon';
                        }
                    }
                }

                // lunar features
                if (! $record && Schema::hasColumn('lunar_features', 'slug')) {
                    $lf = DB::table('lunar_features')->where('slug', $slug)->first();
                    if ($lf) {
                        $record = LunarFeature::where('id', $lf->id)->first();
                        if ($record) {
                            $type = 'lunar_feature';
                        }
                    }
                }

                // asteroids
                if (! $record && Schema::hasColumn('asteroids', 'slug')) {
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

        // Track if a wrapper-provided Horizons coordinate was used for this request.
        // This ensures later ephemerides calculations do not overwrite wrapper results.
        $wrapperUsedGlobal = false;
        $wrapperRaHours = null;
        $wrapperDecDeg = null;

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

        // Enrich metadata for display if available
        if (isset($record->ra) && isset($record->decl)) {
            // Format RA/Dec for human readable output using runtime DeepskyObject
            $session->ra = DeepskyObject::formatRa($record->ra);
            $session->decl = DeepskyObject::formatDec($record->decl);
        }

        // Prefer project wrapper Horizons diagnostics for comets when available
        try {
            $isComet = ($sourceTypeRaw === 'comet' || $type === 'comet') || (isset($record->name) && preg_match('/\b(\d{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $record->name));
            if ($isComet) {
                // Build canonical candidate list for Horizons lookup
                $cands = [];
                // Prefer a canonicalized designation for consistent vendor queries
                $canon = HorizonsDesignation::canonicalize($record->name ?? null);
                if ($canon) $cands[] = $canon;
                // Also add any short numeric periodic code if present
                if (! empty($record->name) && preg_match('/\b(\d{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $record->name, $m)) {
                    $short = HorizonsDesignation::canonicalize(strtoupper($m[1]));
                    if ($short) $cands[] = $short;
                }
                $res = HorizonsWrapper::latestCoordinatesForDesignation($cands, null, 86400);
                if ($res && isset($res['ra_hours']) && isset($res['dec_deg'])) {
                    $session->ra = \App\Models\DeepskyObject::formatRa($res['ra_hours']);
                    $session->decl = \App\Models\DeepskyObject::formatDec($res['dec_deg']);
                    // Preserve wrapper coords for later ephemerides computation
                    $wrapperUsedGlobal = true;
                    $wrapperRaHours = $res['ra_hours'];
                    $wrapperDecDeg = $res['dec_deg'];
                    // Force skipping any external Horizons helper for this request
                    $forceUseWrapperSkipHorizons = true;
                    // Provide initial ephemerides payload so Livewire mounts with wrapper coords
                    try {
                        $ephemerides = [
                            'date' => \Carbon\Carbon::now()->toDateString(),
                            'raDeg' => (float)$res['ra_hours'] * 15.0,
                            'decDeg' => (float)$res['dec_deg'],
                            // Mark that these coordinates came from the server-side wrapper
                            // so Livewire can avoid re-calling the external Horizons helper.
                            '_usedWrapper' => true,
                            '_wrapper_source_file' => $res['source_file'] ?? null,
                        ];
                    } catch (\Throwable $_) {
                        // ignore
                    }
                    try {
                        Log::info('ObjectController: using HorizonsWrapper coords for page', ['file' => $res['source_file'] ?? null, 'ra_hours' => $res['ra_hours'], 'dec_deg' => $res['dec_deg'], 'object' => $record->name ?? null]);
                    } catch (\Throwable $_) {
                        // ignore logging error
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore helper failures
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
                                if (! is_numeric($lensFactor) || $lensFactor <= 0) {
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
                                            if (! isset($epMap[$m])) {
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
                if ($dst && ! empty($dst->name)) {
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
            $image = asset('storage/' . $record->picture);
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

        // Request-scoped guard to force skipping any external Horizons helper calls
        // when wrapper-provided coordinates are available.
        $forceUseWrapperSkipHorizons = false;

        // Early: prefer project wrapper Horizons diagnostics for comets when available.
        // Doing this early ensures downstream ephemerides code and Livewire mounting
        // can observe wrapper-provided coordinates and avoid re-calling the external
        // Horizons helper.
        try {
            $isCometEarly = ($sourceTypeRaw === 'comet' || $type === 'comet') || (isset($record->name) && preg_match('/\b(\d{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $record->name));
            if ($isCometEarly) {
                $earlyCands = [];
                $canonName = \App\Helpers\HorizonsDesignation::canonicalize($record->name ?? null);
                if ($canonName) $earlyCands[] = $canonName;
                if (! empty($record->name) && preg_match('/\b(\d{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $record->name, $mm)) {
                    $short = \App\Helpers\HorizonsDesignation::canonicalize(strtoupper($mm[1]));
                    if ($short) $earlyCands[] = $short;
                }
                if (! empty($earlyCands)) {
                    $earlyRes = \App\Helpers\HorizonsWrapper::latestCoordinatesForDesignation($earlyCands, null, 86400);
                    if ($earlyRes && isset($earlyRes['ra_hours']) && isset($earlyRes['dec_deg'])) {
                        // set session RA/Dec display and preserve wrapper coords for later logic
                        try {
                            $session->ra = \App\Models\DeepskyObject::formatRa($earlyRes['ra_hours']);
                            $session->decl = \App\Models\DeepskyObject::formatDec($earlyRes['dec_deg']);
                        } catch (\Throwable $_) {
                            // ignore formatting errors
                        }
                        $wrapperUsedGlobal = true;
                        $wrapperRaHours = $earlyRes['ra_hours'];
                        $wrapperDecDeg = $earlyRes['dec_deg'];
                        // Strong guard: if we found wrapper coords early, force skipping
                        // any subsequent calls to the external Horizons helper for
                        // this request so wrapper results remain authoritative.
                        $forceUseWrapperSkipHorizons = true;
                        try {
                            $ephemerides = [
                                'date' => \Carbon\Carbon::now()->toDateString(),
                                'raDeg' => (float)$earlyRes['ra_hours'] * 15.0,
                                'decDeg' => (float)$earlyRes['dec_deg'],
                                // Mark these as wrapper-provided so Livewire can avoid recalc
                                '_usedWrapper' => true,
                                '_wrapper_source_file' => $earlyRes['source_file'] ?? null,
                            ];
                        } catch (\Throwable $_) {
                            // ignore
                        }
                        try {
                            Log::info('ObjectController: early using HorizonsWrapper coords for page', ['file' => $earlyRes['source_file'] ?? null, 'ra_hours' => $earlyRes['ra_hours'], 'dec_deg' => $earlyRes['dec_deg'], 'object' => $record->name ?? null]);
                        } catch (\Throwable $_) {
                            // ignore logging errors
                        }
                    }
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }

        // Ensure observation-related variables are defined so view compact() doesn't fail
        $totalObservations = $totalObservations ?? null;
        $drawings = $drawings ?? null;
        $yourObservations = $yourObservations ?? null;
        $yourDrawings = $yourDrawings ?? null;

        // Compute legacy observation/drawing counts when possible so the view shows accurate totals
        try {
            if (! empty($session->name)) {
                // Skip legacy name-based counts for planets — legacy table rows for planet names
                // can be noisy. Only consult the legacy `observations` table for non-planet objects.
                if ((($session->source_type_raw ?? '') !== 'planet') && class_exists(\App\Models\ObservationsOld::class)) {
                    try {
                        $totalObservations = \App\Models\ObservationsOld::getObservationsCountForObject($session->name);
                    } catch (\Throwable $_) {
                        $totalObservations = $totalObservations ?? null;
                    }

                    try {
                        // Keep $drawings as null so the view calls the fallback helper which returns a count
                        // If you prefer we can fetch and pass a collection here instead.
                        // No-op here; let the view compute drawings count via ObservationsOld when $drawings is null.
                    } catch (\Throwable $_) {
                        // ignore
                    }
                }
                // If this object is a comet, also attempt to read legacy comet observations
                try {
                    if (class_exists(\App\Models\CometObservationsOld::class)) {
                        $hasCometObsTable = false;
                        try {
                            $hasCometObsTable = DB::connection('mysqlOld')->getSchemaBuilder()->hasTable('cometobservations');
                        } catch (\Throwable $_) {
                            $hasCometObsTable = false;
                        }

                        if ($hasCometObsTable) {
                            $coObjId = null;
                            // Prefer numeric session id when available (legacy cometobservations.objectid stores numeric comet id)
                            if (isset($session->id) && is_numeric($session->id)) {
                                $coObjId = (int) $session->id;
                            } else {
                                // Try resolving by modern CometObject (legacy table fallback removed)
                                try {
                                    $coModel = \App\Models\CometObject::where('name', $session->name ?? '')->first();
                                    if ($coModel) $coObjId = $coModel->id ?? null;
                                } catch (\Throwable $_) {
                                }
                                if (empty($coObjId)) {
                                    try {
                                        if (class_exists(\App\Models\CometObject::class)) {
                                            $coOld = \App\Models\CometObject::where('name', $session->name ?? '')->first();
                                            if ($coOld) $coObjId = $coOld->id ?? null;
                                        }
                                    } catch (\Throwable $_) {
                                    }
                                }
                            }

                            if (! empty($coObjId)) {
                                try {
                                    $totalObservations = \App\Models\CometObservationsOld::where('objectid', $coObjId)->count();
                                } catch (\Throwable $_) {
                                    // ignore and leave existing totalObservations
                                }

                                try {
                                    // Provide drawings as a numeric count; view will handle numeric values
                                    $drawings = \App\Models\CometObservationsOld::where('objectid', $coObjId)->where('hasDrawing', 1)->count();
                                } catch (\Throwable $_) {
                                    $drawings = $drawings ?? null;
                                }

                                // Provide per-user counts when user is authenticated
                                try {
                                    if (Auth::check()) {
                                        $uname = Auth::user()->username ?? Auth::user()->slug ?? null;
                                        if (! empty($uname)) {
                                            $yourObservations = \App\Models\CometObservationsOld::where('objectid', $coObjId)->where('observerid', $uname)->count();
                                            $yourDrawings = \App\Models\CometObservationsOld::where('objectid', $coObjId)->where('observerid', $uname)->where('hasDrawing', 1)->count();
                                        }
                                    }
                                } catch (\Throwable $_) {
                                    // ignore per-user errors
                                }
                            }
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore comet counting errors
                }
            }
        } catch (\Throwable $_) {
            // defensive: ignore count errors and leave variables as-is
        }

        // Defensive override: ensure planets never show legacy observation counts.
        // There are currently no planet observations in the database; force zeros
        // to avoid misleading totals derived from legacy name-based rows.
        try {
            if (($session->source_type_raw ?? '') === 'planet' || ($type ?? '') === 'planet') {
                $totalObservations = 0;
                $drawings = 0;
                $yourObservations = 0;
                $yourDrawings = 0;
            }
        } catch (\Throwable $_) {
            // ignore
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
                // Use a default date for now (no Livewire yet)
                $date = \Carbon\Carbon::now();
                // Use user's timezone if available
                try {
                    $date = $date->timezone($userLocation->timezone ?? config('app.timezone'));
                } catch (\Throwable $_) {
                }

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
                    $raDeg = is_numeric($record->ra) ? (float)$record->ra : null;
                    $decDeg = is_numeric($record->decl) ? (float)$record->decl : null;
                }

                if ($raDeg !== null && $decDeg !== null) {
                    // EquatorialCoordinates expects RA in hours (0..24).
                    // Some legacy storage may hold RA as degrees (>24) or as hours (<=24).
                    // Normalize: if value > 24 assume degrees and convert to hours.
                    $raHours = (is_numeric($raDeg) && $raDeg > 24.0) ? ((float)$raDeg / 15.0) : (float)$raDeg;
                    $equa = new EquatorialCoordinates($raHours, (float)$decDeg);
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
                            $transit = (string)$transit;
                        }
                    }
                    if ($rising instanceof \DateTimeInterface) {
                        try {
                            $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $rising = (string)$rising;
                        }
                    }
                    if ($setting instanceof \DateTimeInterface) {
                        try {
                            $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $setting = (string)$setting;
                        }
                    }
                    if ($bestTime instanceof \DateTimeInterface) {
                        try {
                            $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
                        } catch (\Throwable $_) {
                            $bestTime = (string)$bestTime;
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
                    if (is_numeric($maxHeightAtNight)) $maxHeightAtNight = round($maxHeightAtNight, 1);
                    if (is_numeric($maxHeight)) $maxHeight = round($maxHeight, 1);

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
                if (! empty($planetName)) {
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
                            $date = \Carbon\Carbon::now();

                            // Prefer user's standard location for topocentric coords
                            $authUser = Auth::user();
                            $userLocation = $authUser?->standardLocation ?? null;
                            if ($userLocation && isset($userLocation->longitude) && isset($userLocation->latitude)) {
                                $geo = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                                $height = $userLocation->elevation ?? 0.0;
                                try {
                                    // Prefer wrapper-provided coordinates via proxy; fall back to library
                                    try {
                                        $proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($planet, $date, $geo, $height, ['obj' => null, 'designation' => $planetName ?? null]);
                                    } catch (\Throwable $_) {
                                        $proxyRes = null;
                                    }
                                    if (empty($proxyRes) || empty($proxyRes['usedWrapper'])) {
                                        // calculateEquatorialCoordinates populates topocentric coords
                                        if (method_exists($planet, 'calculateEquatorialCoordinates')) {
                                            $planet->calculateEquatorialCoordinates($date, $geo, $height);
                                        } elseif (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
                                            $planet->calculateApparentEquatorialCoordinates($date);
                                        }
                                    } else {
                                        // Apply wrapper coords into the planet instance so subsequent
                                        // accessors (getEquatorialCoordinatesToday, etc.) can read them.
                                        $coords = $proxyRes['coords'] ?? null;
                                        if ($coords && method_exists($planet, 'setEquatorialCoordinates')) {
                                            try {
                                                $planet->setEquatorialCoordinates($coords);
                                            } catch (\Throwable $_) {
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) {
                                    // fallback to apparent if topocentric calc fails
                                    try {
                                        if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
                                            $planet->calculateApparentEquatorialCoordinates($date);
                                        }
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
                                if (empty($usedWrapper) && $coords) {
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
                                            if (is_string($c) && ! empty($c)) {
                                                $consName = $c;
                                            } elseif (is_object($c)) {
                                                // If object has name/id
                                                if (isset($c->name)) $consName = $c->name;
                                                if (isset($c->id)) $consCode = $c->id;
                                            }
                                        } catch (\Throwable $_) {
                                            // ignore
                                        }
                                    }
                                    // Try alternative accessor names
                                    if (! $consName && method_exists($coords, 'constellation')) {
                                        try {
                                            $c = $coords->constellation();
                                            if (is_string($c) && ! empty($c)) $consName = $c;
                                        } catch (\Throwable $_) {
                                        }
                                    }
                                }

                                // Planet-level helpers
                                if (! $consName && isset($planet) && $planet) {
                                    if (method_exists($planet, 'getConstellation')) {
                                        try {
                                            $c = $planet->getConstellation();
                                            if (is_string($c) && ! empty($c)) $consName = $c;
                                        } catch (\Throwable $_) {
                                        }
                                    }
                                    if (! $consName && method_exists($planet, 'constellation')) {
                                        try {
                                            $c = $planet->constellation();
                                            if (is_string($c) && ! empty($c)) $consName = $c;
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
                                                    if (is_numeric($raHours)) $raDeg = (float) $raHours * 15.0;
                                                } elseif (is_numeric($raObj)) {
                                                    // sometimes RA provided as hours numeric
                                                    $raDeg = (float) $raObj * 15.0;
                                                }
                                            }
                                            if (method_exists($coords, 'getDeclination')) {
                                                $decObj = $coords->getDeclination();
                                                if (is_object($decObj) && method_exists($decObj, 'getCoordinate')) {
                                                    $dec = $decObj->getCoordinate();
                                                    if (is_numeric($dec)) $decDeg = (float) $dec;
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
                                                        if (is_numeric($tmp)) $raDeg = (float) $tmp;
                                                    }
                                                    if (($decDeg === null) && method_exists(\App\Models\DeepskyObject::class, 'decToDecimal')) {
                                                        $tmp = \App\Models\DeepskyObject::decToDecimal($decStr);
                                                        if (is_numeric($tmp)) $decDeg = (float) $tmp;
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
                                            // EquatorialCoordinates expects RA in hours (0..24).
                                            $raHours = (is_numeric($raDeg) && $raDeg > 24.0) ? ((float)$raDeg / 15.0) : (float)$raDeg;
                                            $equa = new EquatorialCoordinates($raHours, (float)$decDeg);
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
                                                    $transit = (string)$transit;
                                                }
                                            }
                                            if ($rising instanceof \DateTimeInterface) {
                                                try {
                                                    $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $rising = (string)$rising;
                                                }
                                            }
                                            if ($setting instanceof \DateTimeInterface) {
                                                try {
                                                    $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $setting = (string)$setting;
                                                }
                                            }
                                            if ($bestTime instanceof \DateTimeInterface) {
                                                try {
                                                    $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $bestTime = (string)$bestTime;
                                                }
                                            }
                                            try {
                                                if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) $maxHeightAtNight = $maxHeightAtNight->getCoordinate();
                                            } catch (\Throwable $_) {
                                            }
                                            try {
                                                if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) $maxHeight = $maxHeight->getCoordinate();
                                            } catch (\Throwable $_) {
                                            }
                                            if (is_numeric($maxHeightAtNight)) $maxHeightAtNight = round($maxHeightAtNight, 1);
                                            if (is_numeric($maxHeight)) $maxHeight = round($maxHeight, 1);

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
                                        if ($d1) $target->setDiameter($d1, $d2 ?? $d1);
                                        $mval = is_numeric($session->mag) ? $session->mag : null;
                                        if ($mval !== null) $target->setMagnitude($mval);

                                        $sbobj = $target->calculateSBObj();
                                        $sqm = $userLocation->getSqm();
                                        $aperture = $userInstrument->aperture_mm ?? null;

                                        // Lens factor / default lens handling similar to deepsky branch
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
                                        if ($defaultLensId) {
                                            try {
                                                $defaultLens = \App\Models\Lens::where('id', $defaultLensId)->first();
                                                if ($defaultLens) {
                                                    $lensFactor = $defaultLens->factor ?? 1.0;
                                                    if (! is_numeric($lensFactor) || $lensFactor <= 0) $lensFactor = 1.0;
                                                }
                                            } catch (\Throwable $_) {
                                                $defaultLens = null;
                                            }
                                        }

                                        // Choose candidate magnifications and compute best when possible
                                        if ($sbobj !== null && $sqm !== null && $aperture && $userInstrument) {
                                            $mag = $userInstrument->fixedMagnification ?? null;
                                            if (! $mag && $userInstrument->focal_length_mm && isset($session->typicalEyepieceFocal)) {
                                                $mag = round($userInstrument->focal_length_mm / $session->typicalEyepieceFocal);
                                            }

                                            $possible = [25, 50, 75, 100, 150, 200];
                                            if ($lensFactor !== 1.0) $possible = array_map(fn($v) => (int) round($v * $lensFactor), $possible);

                                            // Try deriving from user's instrument set eyepieces
                                            $instSet = $authUser?->standardInstrumentSet ?? null;
                                            if ($instSet && $userInstrument?->focal_length_mm) {
                                                try {
                                                    $setModel = $instSet;
                                                    if ($setModel && count($setModel->eyepieces) > 0) {
                                                        $derived = [];
                                                        foreach ($setModel->eyepieces as $sep) {
                                                            if ($sep->active && ! empty($sep->focal_length_mm) && $sep->focal_length_mm > 0) {
                                                                $derived[] = (int) round(($userInstrument->focal_length_mm / $sep->focal_length_mm) * $lensFactor);
                                                            }
                                                        }
                                                        $derived = array_values(array_unique(array_filter($derived)));
                                                        if (! empty($derived)) {
                                                            $possible = $derived;
                                                        }
                                                    }
                                                } catch (\Throwable $_) {
                                                }
                                            }

                                            $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possible);
                                            $session->optimum_detection_magnification = $best ? (int) $best : null;

                                            if (! empty($session->optimum_detection_magnification)) {
                                                $contrast = $target->calculateContrastReserve($sbobj, $sqm, $aperture, $session->optimum_detection_magnification);
                                                $session->contrast_reserve = is_numeric($contrast) ? round($contrast, 2) : null;
                                                $cat = null;
                                                if (is_numeric($session->contrast_reserve)) {
                                                    $c = (float) $session->contrast_reserve;
                                                    if ($c > 1.0) $cat = 'very_easy';
                                                    elseif ($c > 0.5) $cat = 'easy';
                                                    elseif ($c > 0.35) $cat = 'quite_difficult';
                                                    elseif ($c > 0.1) $cat = 'difficult';
                                                    elseif ($c > -0.2) $cat = 'questionable';
                                                    else $cat = 'not_visible';
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
                                                                foreach ($setModel->eyepieces as $ep) {
                                                                    if (! $ep->active) continue;
                                                                    $ef = $ep->focal_length_mm ?? null;
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
                                                            }
                                                        } catch (\Throwable $_) {
                                                            // ignore
                                                        }
                                                    }

                                                    // If no eyepieces found in the instrument set, fall back to user's eyepieces
                                                    if (empty($eyepiecesForDisplay)) {
                                                        try {
                                                            $eps = \App\Models\Eyepiece::where('user_id', $authUser->id)->where('active', 1)->get();
                                                            foreach ($eps as $ep) {
                                                                $ef = $ep->focal_length_mm ?? null;
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

                                                    // Build mapping magnification -> eyepieces that produce it
                                                    if (! empty($eyepiecesForDisplay) && $userInstrument?->focal_length_mm) {
                                                        foreach ($eyepiecesForDisplay as $epInfo) {
                                                            $ef = $epInfo['focal'];
                                                            if ($ef > 0) {
                                                                $m = (int) round(($userInstrument->focal_length_mm / $ef) * $lensFactor);
                                                                if ($m > 0) {
                                                                    if (! isset($epMap[$m])) $epMap[$m] = [];
                                                                    $epMap[$m][] = $epInfo;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Candidate magnifications: prefer eyepiece-produced mags, else fall back to earlier possible list
                                                    $possibleMags = [];
                                                    if (! empty($epMap)) {
                                                        $possibleMags = array_values(array_unique(array_keys($epMap)));
                                                    } elseif (! empty($possible)) {
                                                        $possibleMags = $possible;
                                                    }

                                                    // Select eyepieces corresponding to the computed best mag (if any)
                                                    if (! empty($best) && isset($epMap[(int) $best])) {
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
                                                            if (! isset($uniq[$k])) {
                                                                $uniq[$k] = true;
                                                                $finalEps[] = $e;
                                                            }
                                                        }
                                                        if (empty($finalEps) && ! empty($eyepiecesForDisplay)) {
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
                    if ($av === $bv) return 0;
                    if ($av === null) return 1;
                    if ($bv === null) return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                usort($availableEyepieces, function ($a, $b) {
                    $av = $a['focal_length_mm'] ?? null;
                    $bv = $b['focal_length_mm'] ?? null;
                    if ($av === $bv) return 0;
                    if ($av === null) return 1;
                    if ($bv === null) return -1;
                    return ($av > $bv) ? -1 : 1;
                });
                usort($availableLenses, function ($a, $b) {
                    $av = $a['factor'] ?? null;
                    $bv = $b['factor'] ?? null;
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

        // If ephemerides still empty and this is a comet, attempt a minimal
        // server-side calculation so the initial inline payload contains
        // coordinates when possible. Keep this logic compact to avoid heavy
        // nesting and reduce risk of errors during page render.
        try {
            if (empty($ephemerides) && ($sourceTypeRaw === 'comet' || $type === 'comet')) {
                $authUser = Auth::user();
                $userLocation = $authUser?->standardLocation ?? null;
                if ($userLocation && Schema::hasTable('comets_orbital_elements')) {
                    $nameToMatch = $record->name ?? null;
                    if (! empty($nameToMatch)) {
                        $cometRow = DB::table('comets_orbital_elements')->where('name', $nameToMatch)->first();
                        if (! $cometRow) {
                            $clean = preg_replace('/\s+/', ' ', trim($nameToMatch));
                            $cometRow = DB::table('comets_orbital_elements')->where('name', 'like', "%{$clean}%")->first();
                        }
                        // Fallback: some comet names include the discoverer in
                        // parentheses (e.g. "C/2023 A3 (Tsuchinshan-ATLAS)").
                        // Try matching after removing parentheses from the
                        // stored name to handle records where the object
                        // name in our objects table doesn't include them.
                        if (! $cometRow) {
                            try {
                                $cleanNoPar = preg_replace('/[()]/', '', $clean);
                                $pattern = '%' . strtolower($cleanNoPar) . '%';
                                $cometRow = DB::table('comets_orbital_elements')
                                    ->whereRaw("LOWER(REPLACE(REPLACE(name, '(', ''), ')', '')) LIKE ?", [$pattern])
                                    ->first();
                            } catch (\Throwable $_) {
                                // ignore DB errors and continue without cometRow
                                $cometRow = null;
                            }
                        }
                        if ($cometRow) {
                            try {
                                Log::info('ObjectController: comets_orbital_elements match', ['name' => $cometRow->name ?? null, 'Tp' => $cometRow->Tp ?? null, 'epoch' => $cometRow->epoch ?? null]);
                            } catch (\Throwable $_) {
                                // ignore logging errors
                            }
                            // Basic perihelion parsing
                            $peri = null;
                            $Tp = $cometRow->Tp ?? null;
                            if ($Tp !== null && is_numeric($Tp)) {
                                $tpInt = (int)$Tp;
                                $tpStr = str_pad((string)$tpInt, 8, '0', STR_PAD_LEFT);
                                $Y = substr($tpStr, 0, 4);
                                $M = substr($tpStr, 4, 2) ?: '01';
                                $D = substr($tpStr, 6, 2) ?: '01';
                                if ($M === '00') $M = '01';
                                if ($D === '00') $D = '01';
                                $peri = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', "{$Y}-{$M}-{$D} 12:00:00", 'UTC');
                            } elseif (! empty($Tp)) {
                                try {
                                    $peri = \Carbon\Carbon::parse($Tp);
                                } catch (\Throwable $_) {
                                    $peri = null;
                                }
                            }
                            if (! $peri && isset($cometRow->epoch) && is_numeric($cometRow->epoch)) {
                                try {
                                    $peri = Time::fromJd($cometRow->epoch);
                                } catch (\Throwable $_) {
                                    $peri = null;
                                }
                            }
                            if (! $peri) $peri = \Carbon\Carbon::now('UTC');

                            // semi-major axis
                            $a = null;
                            if (isset($cometRow->q) && isset($cometRow->e) && ((float)$cometRow->e != 1.0)) {
                                $a = (float)$cometRow->q / (1.0 - (float)$cometRow->e);
                            } elseif (isset($cometRow->a)) {
                                $a = (float)$cometRow->a;
                            }

                            if ($a !== null) {
                                $date = \Carbon\Carbon::now();
                                try {
                                    $date = $date->timezone($userLocation->timezone ?? config('app.timezone'));
                                } catch (\Throwable $_) {
                                }
                                $geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);

                                $eVal = isset($cometRow->e) ? (float)$cometRow->e : 0.0;
                                $qVal = isset($cometRow->q) ? (float)$cometRow->q : null;
                                $coords = null;

                                try {
                                    if ($eVal === 1.0) {
                                        // Parabolic
                                        $par = new Parabolic();
                                        $par->setOrbitalElements((float)$qVal, (float)($cometRow->i ?? 0.0), (float)($cometRow->w ?? 0.0), (float)($cometRow->node ?? 0.0), $peri);
                                        $coordsFromProxy = false;
                                        try {
                                            try {
                                                $proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($par, $date, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $hDesig ?? null, 'obj' => $record ?? null]);
                                            } catch (\Throwable $_) {
                                                $proxyRes = null;
                                            }
                                            if (empty($proxyRes) || empty($proxyRes['usedWrapper'])) {
                                                try {
                                                    $par->calculateEquatorialCoordinates($date, $geo_coords);
                                                } catch (\Throwable $_) {
                                                    try {
                                                        $par->calculateEquatorialCoordinates($date);
                                                    } catch (\Throwable $_) {
                                                    }
                                                }
                                            } else {
                                                $coords = $proxyRes['coords'] ?? null;
                                                $coordsFromProxy = true;
                                                if ($coords && method_exists($par, 'setEquatorialCoordinates')) {
                                                    try {
                                                        $par->setEquatorialCoordinates($coords);
                                                    } catch (\Throwable $_) {
                                                    }
                                                }
                                            }
                                        } catch (\Throwable $_) { /* ignore */
                                        }
                                        if (!empty($coordsFromProxy)) {
                                            // coords already set from proxy
                                        } else {
                                            if (method_exists($par, 'getEquatorialCoordinatesToday')) $coords = $par->getEquatorialCoordinatesToday();
                                            elseif (method_exists($par, 'getEquatorialCoordinates')) $coords = $par->getEquatorialCoordinates();
                                        }
                                    } elseif ($eVal < 1.0) {
                                        // Elliptic
                                        $ell = new Elliptic();
                                        $ell->setOrbitalElements((float)$a, $eVal, (float)($cometRow->i ?? 0.0), (float)($cometRow->w ?? 0.0), (float)($cometRow->node ?? 0.0), $peri);
                                        try {
                                            // Prefer Horizons ephemerides when supported by the library.
                                            try {
                                                if (method_exists($ell, 'setUseHorizons')) {
                                                    $ell->setUseHorizons(true);
                                                    // Attempt to derive a Horizons designation from the orbital row or object name.
                                                    // Prefer the full resolved object name (e.g. "12P/Pons-Brooks") as the
                                                    // Horizons designation. Fall back to legacy `designation` or a
                                                    // short-code extracted from the name when full name is not available.
                                                    $hDesig = null;
                                                    $fullName = $record->name ?? ($cometRow->name ?? null);
                                                    if (! empty($fullName)) {
                                                        $hDesig = \App\Helpers\HorizonsDesignation::canonicalize((string) $fullName);
                                                    } elseif (isset($cometRow->designation) && ! empty($cometRow->designation)) {
                                                        $hDesig = \App\Helpers\HorizonsDesignation::canonicalize((string) $cometRow->designation);
                                                    } else {
                                                        $nameCandidate = $cometRow->name ?? $record->name ?? null;
                                                        if (! empty($nameCandidate) && preg_match('/\b([0-9]{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $nameCandidate, $m)) {
                                                            $hDesig = \App\Helpers\HorizonsDesignation::canonicalize(strtoupper($m[1]));
                                                        }
                                                    }
                                                    if ($hDesig && method_exists($ell, 'setHorizonsDesignation')) {
                                                        $ell->setHorizonsDesignation($hDesig);
                                                    }
                                                }
                                            } catch (\Throwable $_) {
                                                // non-fatal: fall back to local propagation if Horizons not available
                                            }
                                            // If Horizons mode is enabled, Horizons helper expects UTC datetimes.
                                            $calcDate = $date;
                                            try {
                                                if (method_exists($ell, 'setUseHorizons') && $ell instanceof Elliptic && property_exists($ell, '_useHorizons')) {
                                                    // Use a UTC clone so the helper receives UTC time string
                                                    $calcDate = $date->copy()->timezone('UTC');
                                                }
                                            } catch (\Throwable $_) {
                                                $calcDate = $date;
                                            }

                                            // If an earlier wrapper lookup supplied coordinates for this request,
                                            // prefer those and skip calling the Horizons helper again. Honor
                                            // the explicit request-scoped guard `$forceUseWrapperSkipHorizons`
                                            // if it was set during early lookup so we never hit the helper.
                                            try {
                                                if (! empty($forceUseWrapperSkipHorizons) && is_numeric($wrapperRaHours) && is_numeric($wrapperDecDeg)) {
                                                    $coords = new EquatorialCoordinates($wrapperRaHours, $wrapperDecDeg);
                                                    $usedWrapper = true;
                                                    $raDeg = (float)$wrapperRaHours * 15.0;
                                                    $decDeg = (float)$wrapperDecDeg;
                                                    $wrapperUsedGlobal = true;
                                                    \Illuminate\Support\Facades\Log::info('Elliptic: using HorizonsWrapper coords (controller, forced skip)', ['ra_hours' => $wrapperRaHours, 'dec_deg' => $wrapperDecDeg, 'designation' => $hDesig ?? null]);
                                                } elseif (! empty($wrapperUsedGlobal) && is_numeric($wrapperRaHours) && is_numeric($wrapperDecDeg)) {
                                                    $coords = new EquatorialCoordinates($wrapperRaHours, $wrapperDecDeg);
                                                    $usedWrapper = true;
                                                    $raDeg = (float)$wrapperRaHours * 15.0;
                                                    $decDeg = (float)$wrapperDecDeg;
                                                    \Illuminate\Support\Facades\Log::info('Elliptic: using HorizonsWrapper coords (controller, global)', ['ra_hours' => $wrapperRaHours, 'dec_deg' => $wrapperDecDeg, 'designation' => $hDesig ?? null]);
                                                } else {
                                                    // Check project wrapper diagnostics and use them if available
                                                    try {
                                                        // Build canonical candidate list for wrapper lookup
                                                        $candList = [];
                                                        if (! empty($hDesig)) {
                                                            $candList[] = \App\Helpers\HorizonsDesignation::canonicalize($hDesig);
                                                        }
                                                        if (! empty($hDesig) && preg_match('/\b(\d{1,4}P|C\/\d{4}[A-Z0-9-]*)\b/i', $hDesig, $mm)) {
                                                            $candList[] = \App\Helpers\HorizonsDesignation::canonicalize(strtoupper($mm[1]));
                                                        }
                                                        // Also include the record slug/name to match wrapper runs that used alternate identifiers
                                                        try {
                                                            if (isset($record) && ! empty($record->slug)) {
                                                                $candList[] = \App\Helpers\HorizonsDesignation::canonicalize($record->slug);
                                                            }
                                                        } catch (\Throwable $_) {
                                                        }
                                                        try {
                                                            if (! empty($fullName) && ($fullName !== ($hDesig ?? null))) {
                                                                $candList[] = \App\Helpers\HorizonsDesignation::canonicalize($fullName);
                                                            }
                                                        } catch (\Throwable $_) {
                                                        }
                                                        $candList = array_values(array_unique(array_filter($candList)));
                                                        $wrapperCoords = \App\Helpers\HorizonsWrapper::latestCoordinatesForDesignation($candList, $calcDate, 86400, 120);
                                                        if ($wrapperCoords && isset($wrapperCoords['ra_hours']) && isset($wrapperCoords['dec_deg'])) {
                                                            try {
                                                                $coords = new EquatorialCoordinates($wrapperCoords['ra_hours'], $wrapperCoords['dec_deg']);
                                                                // Treat wrapper result as authoritative for this request
                                                                $usedWrapper = true;
                                                                $raDeg = (float)$wrapperCoords['ra_hours'] * 15.0;
                                                                $decDeg = (float)$wrapperCoords['dec_deg'];
                                                                // Also record globally so other blocks can observe we're using wrapper
                                                                $wrapperUsedGlobal = true;
                                                                $wrapperRaHours = $wrapperCoords['ra_hours'];
                                                                $wrapperDecDeg = $wrapperCoords['dec_deg'];
                                                                \Illuminate\Support\Facades\Log::info('Elliptic: using HorizonsWrapper coords (controller)', ['file' => $wrapperCoords['source_file'] ?? null, 'ra_hours' => $wrapperCoords['ra_hours'], 'dec_deg' => $wrapperCoords['dec_deg'], 'designation' => $hDesig ?? null]);
                                                            } catch (\Throwable $_) {
                                                                // failed to construct coords; fall back to helper
                                                            }
                                                        }
                                                    } catch (\Throwable $_) {
                                                        // ignore wrapper failures
                                                    }
                                                }
                                            } catch (\Throwable $_) {
                                                // ignore global wrapper handling errors
                                            }

                                            // Only call the Horizons helper if we don't already have coords from the wrapper
                                            if (empty($coords)) {
                                                // Perform a permissive wrapper lookup immediately before calling the Horizons helper
                                                try {
                                                    $robustCandidates = $candList ?? [];
                                                    if (! empty($hDesig)) $robustCandidates[] = $hDesig;
                                                    try {
                                                        if (! empty($fullName)) $robustCandidates[] = $fullName;
                                                    } catch (\Throwable $_) {
                                                    }
                                                    try {
                                                        if (isset($record) && ! empty($record->slug)) $robustCandidates[] = $record->slug;
                                                    } catch (\Throwable $_) {
                                                    }
                                                    $extra = [];
                                                    foreach ($robustCandidates as $rc) {
                                                        if (! $rc) continue;
                                                        $s = trim((string)$rc);
                                                        $extra[] = $s;
                                                        $extra[] = strtoupper($s);
                                                        $extra[] = strtolower($s);
                                                        $extra[] = str_replace('/', ' ', $s);
                                                        $extra[] = str_replace(' ', '', $s);
                                                    }
                                                    $robustCandidates = array_values(array_unique(array_filter(array_merge($robustCandidates, $extra))));
                                                    $robWrapper = \App\Helpers\HorizonsWrapper::latestCoordinatesForDesignation($robustCandidates, $calcDate, 7 * 86400, 3600);
                                                    if ($robWrapper && isset($robWrapper['ra_hours']) && isset($robWrapper['dec_deg'])) {
                                                        $coords = new EquatorialCoordinates($robWrapper['ra_hours'], $robWrapper['dec_deg']);
                                                        $usedWrapper = true;
                                                        $raDeg = (float)$robWrapper['ra_hours'] * 15.0;
                                                        $decDeg = (float)$robWrapper['dec_deg'];
                                                        $wrapperUsedGlobal = true;
                                                        $wrapperRaHours = $robWrapper['ra_hours'];
                                                        $wrapperDecDeg = $robWrapper['dec_deg'];
                                                        \Illuminate\Support\Facades\Log::info('Elliptic: using HorizonsWrapper coords (pre-call robust)', ['file' => $robWrapper['source_file'] ?? null, 'ra_hours' => $robWrapper['ra_hours'], 'dec_deg' => $robWrapper['dec_deg'], 'designation' => $hDesig ?? null]);
                                                    }
                                                } catch (\Throwable $_) {
                                                    // ignore robust probe failures and continue
                                                }

                                                if (empty($coords)) {
                                                    try {
                                                        \Illuminate\Support\Facades\Log::info('Elliptic: calling Horizons helper', [
                                                            'designation' => $hDesig ?? null,
                                                            'date_utc' => $calcDate->toIso8601String(),
                                                            'object' => $record->name ?? null,
                                                        ]);
                                                    } catch (\Throwable $_) {
                                                        // ignore logging errors
                                                    }

                                                    // Use centralized proxy to consult HorizonsWrapper first
                                                    try {
                                                        $proxyResult = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($ell, $calcDate, $geo_coords, $userLocation->elevation ?? 0.0, ['designation' => $hDesig ?? null, 'obj' => $record ?? null]);
                                                        if (!empty($proxyResult) && !empty($proxyResult['usedWrapper'])) {
                                                            $coords = $proxyResult['coords'] ?? null;
                                                            $usedWrapper = true;
                                                            $wrapperUsedGlobal = true;
                                                            if ($coords && method_exists($coords, 'getRA')) {
                                                                try {
                                                                    $raHoursVal = $coords->getRA()->getCoordinate();
                                                                    $raDeg = is_numeric($raHoursVal) ? (float)$raHoursVal * 15.0 : $raDeg;
                                                                } catch (\Throwable $_) {
                                                                }
                                                            }
                                                            if ($coords && method_exists($coords, 'getDeclination')) {
                                                                try {
                                                                    $decVal = $coords->getDeclination()->getCoordinate();
                                                                    $decDeg = is_numeric($decVal) ? (float)$decVal : $decDeg;
                                                                } catch (\Throwable $_) {
                                                                }
                                                            }
                                                            $wrapperRaHours = $wrapperRaHours ?? ($coords && method_exists($coords, 'getRA') ? $coords->getRA()->getCoordinate() : $wrapperRaHours);
                                                            $wrapperDecDeg = $wrapperDecDeg ?? ($coords && method_exists($coords, 'getDeclination') ? $coords->getDeclination()->getCoordinate() : $wrapperDecDeg);
                                                        }
                                                    } catch (\Throwable $_) {
                                                        // ignore proxy failures and allow underlying code to continue
                                                    }
                                                } else {
                                                    // Using wrapper coords - skip helper
                                                }
                                            } else {
                                                // Using wrapper coords - skip helper (coords pre-populated)
                                            }
                                        } catch (\Throwable $_) { /* ignore */
                                        }
                                        if (method_exists($ell, 'getEquatorialCoordinatesToday')) $coords = $ell->getEquatorialCoordinatesToday();
                                        elseif (method_exists($ell, 'getEquatorialCoordinates')) $coords = $ell->getEquatorialCoordinates();
                                        try {
                                            if ($coords) {
                                                $raLog = null;
                                                $decLog = null;
                                                try {
                                                    $raObj = method_exists($coords, 'getRA') ? $coords->getRA() : ($coords->ra ?? null);
                                                    $raLog = (is_object($raObj) && method_exists($raObj, 'getCoordinate')) ? $raObj->getCoordinate() : $raObj;
                                                } catch (\Throwable $_) {
                                                }
                                                try {
                                                    $decObj = method_exists($coords, 'getDeclination') ? $coords->getDeclination() : ($coords->dec ?? null);
                                                    $decLog = (is_object($decObj) && method_exists($decObj, 'getCoordinate')) ? $decObj->getCoordinate() : $decObj;
                                                } catch (\Throwable $_) {
                                                }
                                                \Illuminate\Support\Facades\Log::info('Elliptic: coords after calculate', ['designation' => $hDesig ?? null, 'ra' => $raLog, 'dec' => $decLog, 'object' => $record->name ?? null]);
                                            }
                                        } catch (\Throwable $_) {
                                            // ignore logging errors
                                        }
                                    } else {
                                        // Hyperbolic / near-parabolic
                                        $near = new NearParabolic();
                                        $near->setOrbitalElements((float)$qVal, $eVal, (float)($cometRow->i ?? 0.0), (float)($cometRow->w ?? 0.0), (float)($cometRow->node ?? 0.0), $peri);
                                        $coordsFromProxy = false;
                                        try {
                                            try {
                                                $proxyRes = \App\Helpers\HorizonsProxy::calculateEquatorialCoordinates($near, $date, null, null, ['designation' => $hDesig ?? null, 'obj' => $record ?? null]);
                                            } catch (\Throwable $_) {
                                                $proxyRes = null;
                                            }
                                            if (empty($proxyRes) || empty($proxyRes['usedWrapper'])) {
                                                try {
                                                    $near->calculateEquatorialCoordinates($date);
                                                } catch (\Throwable $_) { /* ignore */
                                                }
                                            } else {
                                                $coords = $proxyRes['coords'] ?? null;
                                                $coordsFromProxy = true;
                                                if ($coords && method_exists($near, 'setEquatorialCoordinates')) {
                                                    try {
                                                        $near->setEquatorialCoordinates($coords);
                                                    } catch (\Throwable $_) {
                                                    }
                                                }
                                            }
                                        } catch (\Throwable $_) { /* ignore */
                                        }
                                        if (! empty($coordsFromProxy)) {
                                            // coords already fulfilled by proxy
                                        } else {
                                            if (method_exists($near, 'getEquatorialCoordinatesToday')) $coords = $near->getEquatorialCoordinatesToday();
                                            elseif (method_exists($near, 'getEquatorialCoordinates')) $coords = $near->getEquatorialCoordinates();
                                        }
                                    }
                                } catch (\Throwable $_) {
                                    $coords = null;
                                }

                                if ($coords) {
                                    $raDeg = null;
                                    $decDeg = null;
                                    try {
                                        $raObj = method_exists($coords, 'getRA') ? $coords->getRA() : ($coords->ra ?? null);
                                        $raVal = (is_object($raObj) && method_exists($raObj, 'getCoordinate')) ? $raObj->getCoordinate() : $raObj;
                                        if (is_numeric($raVal)) $raDeg = ((float)$raVal <= 24.0) ? ((float)$raVal * 15.0) : (float)$raVal;
                                    } catch (\Throwable $_) {
                                        $raDeg = null;
                                    }
                                    try {
                                        $decObj = method_exists($coords, 'getDeclination') ? $coords->getDeclination() : ($coords->dec ?? null);
                                        $decVal = (is_object($decObj) && method_exists($decObj, 'getCoordinate')) ? $decObj->getCoordinate() : $decObj;
                                        if (is_numeric($decVal)) $decDeg = (float)$decVal;
                                    } catch (\Throwable $_) {
                                        $decDeg = null;
                                    }

                                    if (is_numeric($raDeg) && is_numeric($decDeg)) {
                                        // Build ephemerides briefly using AstroTarget so view gets rising/transit/setting
                                        try {
                                            $raHours = ($raDeg > 24.0) ? ($raDeg / 15.0) : $raDeg;
                                            $equa = new EquatorialCoordinates((float)$raHours, (float)$decDeg);
                                            $target = new AstroTarget();
                                            $target->setEquatorialCoordinates($equa);
                                            $gst = Time::apparentSiderialTimeGreenwich($date);
                                            $dt = Time::deltaT($date);
                                            $target->calculateEphemerides($geo_coords, $gst, $dt);

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

                                            $tz = $userLocation->timezone ?? config('app.timezone');
                                            if ($transit instanceof \DateTimeInterface) {
                                                try {
                                                    $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $transit = (string)$transit;
                                                }
                                            }
                                            if ($rising instanceof \DateTimeInterface) {
                                                try {
                                                    $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $rising = (string)$rising;
                                                }
                                            }
                                            if ($setting instanceof \DateTimeInterface) {
                                                try {
                                                    $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $setting = (string)$setting;
                                                }
                                            }
                                            if ($bestTime instanceof \DateTimeInterface) {
                                                try {
                                                    $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
                                                } catch (\Throwable $_) {
                                                    $bestTime = (string)$bestTime;
                                                }
                                            }

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
                                                'raDeg' => $raDeg,
                                                'decDeg' => $decDeg,
                                                // Expose comet magnitude when available (legacy DB value)
                                                'mag' => $record->mag ?? null,
                                            ];
                                            try {
                                                Log::info('ObjectController: computed comet ephemerides for initial payload', ['name' => $record->name ?? null, 'raDeg' => $raDeg, 'decDeg' => $decDeg]);
                                            } catch (\Throwable $_) {
                                            }
                                        } catch (\Throwable $_) { /* ignore */
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $_) { /* ignore */
        }

        // If we discovered wrapper coordinates at any point but did not populate
        // the initial ephemerides payload, ensure the view receives the wrapper
        // coordinates so Livewire mounts with authoritative values and will
        // avoid calling the external Horizons helper.
        try {
            if (! empty($wrapperUsedGlobal) && is_numeric($wrapperRaHours) && is_numeric($wrapperDecDeg)) {
                if (empty($ephemerides) || ! (isset($ephemerides['raDeg']) && isset($ephemerides['decDeg']))) {
                    $raDegVal = (float)$wrapperRaHours * 15.0;
                    $decDegVal = (float)$wrapperDecDeg;
                    $extra = [];
                    // Attempt to compute rise/transit/setting and constellation when we have a user location
                    try {
                        if (isset($userLocation) && $userLocation) {
                            $geo_coords_local = new \deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                            $tmpTarget = new AstroTarget();
                            // EquatorialCoordinates constructor expects RA in hours and declination in degrees
                            $equaTmp = new EquatorialCoordinates((float)$wrapperRaHours, (float)$wrapperDecDeg);
                            $tmpTarget->setEquatorialCoordinates($equaTmp);
                            $gstTmp = Time::apparentSiderialTimeGreenwich(\Carbon\Carbon::now());
                            $dtTmp = Time::deltaT(\Carbon\Carbon::now());
                            try {
                                $tmpTarget->calculateEphemerides($geo_coords_local, $gstTmp, $dtTmp);
                                $trans = null;
                                $rising = null;
                                $setting = null;
                                $bestTime = null;
                                $maxHeightAtNight = null;
                                $maxHeight = null;
                                try {
                                    $trans = $tmpTarget->getTransit();
                                } catch (\Throwable $_) {
                                    $trans = null;
                                }
                                try {
                                    $rising = $tmpTarget->getRising();
                                } catch (\Throwable $_) {
                                    $rising = null;
                                }
                                try {
                                    $setting = $tmpTarget->getSetting();
                                } catch (\Throwable $_) {
                                    $setting = null;
                                }
                                try {
                                    $bestTime = $tmpTarget->getBestTimeToObserve();
                                } catch (\Throwable $_) {
                                    $bestTime = null;
                                }
                                try {
                                    $maxHeightAtNight = $tmpTarget->getMaxHeightAtNight();
                                } catch (\Throwable $_) {
                                    $maxHeightAtNight = null;
                                }
                                try {
                                    $maxHeight = $tmpTarget->getMaxHeight();
                                } catch (\Throwable $_) {
                                    $maxHeight = null;
                                }
                                $tzLocal = $userLocation->timezone ?? config('app.timezone');
                                if ($trans instanceof \DateTimeInterface) {
                                    try {
                                        $trans = \Carbon\Carbon::instance($trans)->timezone($tzLocal)->isoFormat('HH:mm');
                                    } catch (\Throwable $_) {
                                        $trans = (string)$trans;
                                    }
                                }
                                if ($rising instanceof \DateTimeInterface) {
                                    try {
                                        $rising = \Carbon\Carbon::instance($rising)->timezone($tzLocal)->isoFormat('HH:mm');
                                    } catch (\Throwable $_) {
                                        $rising = (string)$rising;
                                    }
                                }
                                if ($setting instanceof \DateTimeInterface) {
                                    try {
                                        $setting = \Carbon\Carbon::instance($setting)->timezone($tzLocal)->isoFormat('HH:mm');
                                    } catch (\Throwable $_) {
                                        $setting = (string)$setting;
                                    }
                                }
                                if ($bestTime instanceof \DateTimeInterface) {
                                    try {
                                        $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tzLocal)->isoFormat('HH:mm');
                                    } catch (\Throwable $_) {
                                        $bestTime = (string)$bestTime;
                                    }
                                }
                                if (is_object($maxHeightAtNight) && method_exists($maxHeightAtNight, 'getCoordinate')) $maxHeightAtNight = $maxHeightAtNight->getCoordinate();
                                if (is_object($maxHeight) && method_exists($maxHeight, 'getCoordinate')) $maxHeight = $maxHeight->getCoordinate();
                                if (is_numeric($maxHeightAtNight)) $maxHeightAtNight = round($maxHeightAtNight, 1);
                                if (is_numeric($maxHeight)) $maxHeight = round($maxHeight, 1);
                                $extra['transit'] = $trans;
                                $extra['rising'] = $rising;
                                $extra['setting'] = $setting;
                                $extra['best_time'] = $bestTime;
                                $extra['max_height_at_night'] = $maxHeightAtNight;
                                $extra['max_height'] = $maxHeight;
                            } catch (\Throwable $_) {
                                // ignore ephemerides calc failures
                            }
                            // Attempt to derive constellation
                            try {
                                $cons = null;
                                if (method_exists($equaTmp, 'getConstellation')) {
                                    $cons = $equaTmp->getConstellation();
                                } elseif (method_exists($tmpTarget, 'getConstellation')) {
                                    $cons = $tmpTarget->getConstellation();
                                }
                                if ($cons) {
                                    // resolve DB mapping when possible
                                    try {
                                        $foundCons = ConstellationModel::where('name', $cons)->orWhere('id', $cons)->first();
                                        if ($foundCons) {
                                            $extra['constellation'] = $foundCons->name;
                                            $extra['constellation_code'] = $foundCons->id;
                                        } else {
                                            $extra['constellation'] = $cons;
                                        }
                                    } catch (\Throwable $_) {
                                        $extra['constellation'] = $cons;
                                    }
                                }
                            } catch (\Throwable $_) {
                                // ignore
                            }
                        }
                    } catch (\Throwable $_) {
                        // ignore location-based calculations
                    }

                    $ephemerides = array_merge([
                        'date' => \Carbon\Carbon::now()->toDateString(),
                        'raDeg' => $raDegVal,
                        'decDeg' => $decDegVal,
                        '_usedWrapper' => true,
                        '_wrapper_source_file' => $wrapperSourceFile ?? ($wrapperCoords['source_file'] ?? null) ?? null,
                    ], $extra);
                    try {
                        Log::info('ObjectController: forcing ephemerides from wrapper before render', ['ra_hours' => $wrapperRaHours, 'dec_deg' => $wrapperDecDeg, 'object' => $record->name ?? null]);
                    } catch (\Throwable $_) {
                    }
                    // Also ensure downstream code honors the wrapper for this request
                    $forceUseWrapperSkipHorizons = true;
                }
            }
        } catch (\Throwable $_) {
            // ignore
        }

        // Prepare legacy comet magnitude time-series when available
        $comet_magnitudes = [];
        $comet_min_mag = null;
        $comet_max_mag = null;
        $cometMagnitudeChart = null;
        try {
            if (class_exists(\App\Models\CometObservationsOld::class)) {
                $hasCometObsTable = false;
                try {
                    $hasCometObsTable = DB::connection('mysqlOld')->getSchemaBuilder()->hasTable('cometobservations');
                } catch (\Throwable $_) {
                    $hasCometObsTable = false;
                }

                if ($hasCometObsTable) {
                    $coObjId = null;
                    if (isset($session->id) && is_numeric($session->id)) {
                        $coObjId = (int) $session->id;
                    } else {
                        try {
                            $coModel = \App\Models\CometObject::where('name', $session->name ?? '')->first();
                            if ($coModel) $coObjId = $coModel->id ?? null;
                        } catch (\Throwable $_) {
                        }
                    }

                    if (!empty($coObjId)) {
                        try {
                            $rows = \App\Models\CometObservationsOld::where('objectid', $coObjId)->orderBy('date')->get();
                            foreach ($rows as $r) {
                                try {
                                    $attrs = $r->getAttributes();
                                    $mag = null;
                                    foreach (['mag', 'mag_v', 'magnitude', 'estmag', 'mag_v_est', 'vmag', 'magv', 'm'] as $k) {
                                        if (array_key_exists($k, $attrs) && $attrs[$k] !== null && $attrs[$k] !== '') {
                                            $mag = floatval($attrs[$k]);
                                            break;
                                        }
                                    }
                                    if ($mag === null) continue;

                                    $dateStr = (string) ($r->date ?? '');
                                    $formatted = null;
                                    if (preg_match('/^\d{8}$/', $dateStr)) {
                                        $formatted = substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2);
                                    } else {
                                        try {
                                            $formatted = \Carbon\Carbon::parse($r->date)->toDateString();
                                        } catch (\Throwable $_) {
                                            $formatted = null;
                                        }
                                    }

                                    if ($formatted) {
                                        $comet_magnitudes[] = ['date' => $formatted, 'mag' => $mag];
                                        if (!is_null($mag)) {
                                            if (is_null($comet_min_mag) || $mag < $comet_min_mag) $comet_min_mag = $mag;
                                            if (is_null($comet_max_mag) || $mag > $comet_max_mag) $comet_max_mag = $mag;
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
                    if (!is_array($p)) return false;
                    if (!isset($p['mag'])) return false;
                    if (!is_numeric($p['mag'])) return false;
                    $m = floatval($p['mag']);
                    // exclude common sentinel values and obviously invalid mags
                    if ($m === 99.9 || $m === -99.9) return false;
                    if (!is_finite($m)) return false;
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
                    if (is_null($comet_min_mag) || $m < $comet_min_mag) $comet_min_mag = $m;
                    if (is_null($comet_max_mag) || $m > $comet_max_mag) $comet_max_mag = $m;
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

        return response()->view('object.show', compact('session', 'user', 'location', 'image', 'observers', 'totalObservations', 'observations', 'drawings', 'observerStats', 'selectedObserverUsername', 'selectedObserverName', 'atlasPage', 'atlasName', 'alternatives', 'canonicalSlug', 'aladinDefaults', 'availableInstruments', 'availableEyepieces', 'availableLenses', 'selectedInstrumentId', 'selectedEyepieceId', 'selectedLensId', 'ephemerides', 'yourObservations', 'yourDrawings', 'comet_magnitudes', 'comet_min_mag', 'comet_max_mag', 'cometMagnitudeChart'));
    }
}
