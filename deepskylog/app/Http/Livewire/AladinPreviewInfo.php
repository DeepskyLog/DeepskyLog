<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Asteroid;
use App\Services\AsteroidCoordinates;
use App\Services\ContrastReserveCalculator;
use App\Services\DoubleStarResolvability;
use App\Services\FilterAdvice;
use App\Services\MoonlitContrastReserve;
use App\Support\EphemerisDate;
use deepskylog\AstronomyLibrary\AstronomyLibrary;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

class AladinPreviewInfo extends Component
{
    // debounce window (milliseconds) to ignore rapid repeated preview updates
    private const PREVIEW_DEBOUNCE_MS = 1000;

    public $objectId;
    public $contrast_reserve;
    public $contrast_reserve_category;
    public $contrast_used_location;
    public $contrast_used_sqm;
    public $contrast_used_nelm;
    public $contrast_used_instrument;
    /** The highest altitude of the object (degrees) and the magnitudes the atmosphere dims it there. */
    public $contrast_used_altitude;
    public $contrast_used_extinction;
    /** The contrast reserve with the Moon in the night of the date (MoonlitContrastReserve), or null. */
    public $moonlit = null;
    /** The contrast reserve with each of the observer's nebula filters (FilterAdvice::present()). */
    public $filter_advice = [];
    /** Whether the observer's telescope splits a double star, and at which magnification (DoubleStarResolvability::present()), or null. */
    public $double_star = null;
    public $optimum_detection_magnification;
    public $optimum_eyepieces = [];
    // Raw object type passed from the page (e.g. 'comet', 'planet', etc.)
    public $object_type = null;
    // Planet-specific dynamic values (arcseconds)
    public $planet_diameter_primary = null;
    public $planet_diameter_secondary = null;
    // Latest server-provided coordinates and magnitude (degrees / mag)
    public $object_ra_deg = null;
    public $object_dec_deg = null;
    public $object_mag = null;
    // Keep only last_error public for error reporting; other debug info is internal now
    public $last_error = null;

    protected $listeners = ['aladinUpdated' => 'recalculate', 'ephemerisDateChanged' => 'handleEphemerisDateChange', 'objectEphemeridesUpdated' => 'handleObjectEphemeridesUpdated'];

    public function handleEphemerisDateChange($date)
    {
        // forward the date to recalculate using a normalized payload
        $this->recalculate(['date' => $date, 'objectId' => $this->objectId]);
    }

    public function mount($objectId = null, $initial = [])
    {
        // Normalize empty-string mounts to null so downstream logic treats them as missing
        $this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
        // mount debug logging removed
        // component-mounted object id available via $this->objectId
        $this->contrast_reserve = $initial['contrast_reserve'] ?? null;
        $this->contrast_reserve_category = $initial['contrast_reserve_category'] ?? null;
        $this->contrast_used_location = $initial['contrast_used_location'] ?? null;
        $this->contrast_used_sqm = $initial['contrast_used_sqm'] ?? null;
        $this->contrast_used_nelm = $initial['contrast_used_nelm'] ?? null;
        $this->contrast_used_instrument = $initial['contrast_used_instrument'] ?? null;
        $this->contrast_used_altitude = $initial['contrast_used_altitude'] ?? null;
        $this->contrast_used_extinction = $initial['contrast_used_extinction'] ?? null;
        $this->optimum_detection_magnification = $initial['optimum_detection_magnification'] ?? null;
        $this->optimum_eyepieces = $initial['optimum_eyepieces'] ?? [];
        // accept object_type so blade can decide when to hide comet-only rows
        $this->object_type = $initial['object_type'] ?? null;
    }

    /**
     * Allow client to explicitly set the component object id after mount.
     * This is called from the blade x-init to ensure the component has a usable id
     * even if the server-side mount param was empty for whatever reason.
     */
    public function setObjectId($objectId)
    {
        try {
            $this->objectId = (is_string($objectId) && trim($objectId) === '') ? null : $objectId;
            // component-mounted object id available via $this->objectId
            // setObjectId debug logging removed
        } catch (\Throwable $_) {
            // ignore
        }
    }

    public function recalculate($payload = null)
    {
        // payload expected: ['instrument' => id|null, 'eyepiece' => id|null, 'lens' => id|null]
        // Log raw request body to help diagnose cases where payload arrives empty
        // Raw request inspection/logging removed

        // If payload is empty (Livewire sometimes triggers handlers without args),
        // inspect the posted input and try to extract any nested payload/objectSlug
        if (empty($payload) || (is_array($payload) && count($payload) === 0)) {
            try {
                $input = request()->all();
                // Recursive search helper
                $finder = function ($needle, $haystack) use (&$finder) {
                    if (is_array($haystack)) {
                        if (array_key_exists($needle, $haystack))
                            return $haystack[$needle];
                        foreach ($haystack as $v) {
                            $res = $finder($needle, $v);
                            if (!is_null($res))
                                return $res;
                        }
                    } elseif (is_object($haystack)) {
                        $arr = (array) $haystack;
                        if (array_key_exists($needle, $arr))
                            return $arr[$needle];
                        foreach ($arr as $v) {
                            $res = $finder($needle, $v);
                            if (!is_null($res))
                                return $res;
                        }
                    }
                    return null;
                };
                $foundSlug = $finder('objectSlug', $input);
                $foundOid = $finder('objectId', $input);
                if ($foundOid) {
                    $payload = (array) ($payload ?? []);
                    $payload['objectId'] = $foundOid;
                } elseif ($foundSlug) {
                    $payload = (array) ($payload ?? []);
                    $payload['objectSlug'] = $foundSlug;
                } else {
                    // no objectId/objectSlug found in request input
                }
            } catch (\Throwable $_) { /* failed to inspect request input */
            }
        }
        // make payload optional to avoid container DI errors if Livewire triggers
        // the method without an argument (defensive). Normalize to array/object
        if (is_null($payload)) {
            $payload = [];
        }
        // Log receipt for debugging
        try {
            // recalculate invoked (debug logging removed)
            $tStart = microtime(true);
            try {
                Log::debug('AladinPreviewInfo: recalculate start', ['user_id' => Auth::id(), 'objectId' => $this->objectId]);
            } catch (\Throwable $_) {
            }
            $obj = null;
            // Prefer explicit objectId provided in the payload (from the page) to ensure
            // we operate on the correct object instance. Fall back to the component's
            // mounted objectId when payload doesn't include one.
            $payloadObjectId = null;
            try {
                if (is_array($payload) || is_object($payload)) {
                    // Guard against payload values like 'undefined' or 'null' strings
                    if (array_key_exists('objectId', (array) $payload)) {
                        $raw = (string) ($payload['objectId'] ?? '');
                        $raw_trim = trim($raw);
                        if ($raw_trim !== '' && strtolower($raw_trim) !== 'undefined' && strtolower($raw_trim) !== 'null') {
                            $payloadObjectId = $raw_trim;
                        }
                    }
                }
            } catch (\Throwable $_) {
                $payloadObjectId = null;
            }
            // If neither the payload nor the component mount provided an object id,
            // attempt a conservative server-side fallback: check the current HTTP
            // request for a route parameter or query parameter named 'id' or 'object'
            // (common patterns), and as a last resort use the component's originally
            // mounted object id. Log which path was used for diagnostics.
            $useObjectId = $payloadObjectId ?: $this->objectId;
            if (empty($useObjectId)) {
                try {
                    $req = request();
                    $candidate = null;
                    // route parameter 'id' or 'object' (e.g., object.show route)
                    if ($req && method_exists($req, 'route')) {
                        try {
                            $candidate = $req->route('id') ?? $req->route('object') ?? null;
                        } catch (\Throwable $_) {
                            $candidate = null;
                        }
                    }
                    // query string fallback
                    if ((!$candidate || trim((string) $candidate) === '') && $req) {
                        try {
                            $candidate = $req->query('id') ?? $req->query('object') ?? null;
                        } catch (\Throwable $_) {
                            $candidate = null;
                        }
                    }
                    // lastly, check for an explicitly injected server variable via Livewire's initial payload
                    if ((!$candidate || trim((string) $candidate) === '') && !empty($this->objectId)) {
                        $candidate = $this->objectId;
                    }
                    if ($candidate && trim((string) $candidate) !== '') {
                        $useObjectId = trim((string) $candidate);
                    }
                    // If still empty, attempt to resolve from payload.objectSlug if provided
                    if (empty($useObjectId) && !empty($payload) && (is_array($payload) || is_object($payload))) {
                        try {
                            $ps = (array) $payload;
                            if ((!isset($ps['objectId']) || empty($ps['objectId'])) && !empty($ps['objectSlug'])) {
                                $slugCandidate = trim((string) $ps['objectSlug']);
                                if ($slugCandidate !== '') {
                                    try {
                                        // First, check legacy objectnames table for the slug to find canonical objectname
                                        $objNameRow = DB::table('objectnames')->where('slug', $slugCandidate)->first();
                                        if (!$objNameRow) {
                                            // try altname/name variants in the legacy table
                                            $objNameRow = DB::table('objectnames')
                                                ->where('objectname', $slugCandidate)
                                                ->orWhere('altname', $slugCandidate)
                                                ->first();
                                        }
                                        if ($objNameRow && !empty($objNameRow->objectname)) {
                                            // The canonical legacy object name is stored in objectnames.objectname
                                            $canonicalName = $objNameRow->objectname;
                                            // The objects table links via 'name' (legacy) or sometimes 'object' column. Try both.
                                            $found = DB::table('objects')->where('name', $canonicalName)->first();
                                            if (!$found && Schema::hasColumn('objects', 'object')) {
                                                $found = DB::table('objects')->where('object', $canonicalName)->first();
                                            }
                                            if ($found) {
                                                $useObjectId = (string) ($found->id ?? $found->ID ?? null);
                                            }
                                        } else {
                                            // As a fallback, also try direct objects.slug/name lookup (existing behaviour)
                                            $found = DB::table('objects')->where('slug', $slugCandidate)->orWhere('name', $slugCandidate)->first();
                                            if ($found) {
                                                $useObjectId = (string) ($found->id ?? $found->ID ?? null);
                                            }
                                        }
                                    } catch (\Throwable $_) {
                                        // ignore lookup errors
                                    }
                                }
                            }
                        } catch (\Throwable $_) {
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore fallback errors
                }
            }
            // component-mounted id is $this->objectId
            // Asteroids: their ids overlap with those of the deep-sky objects
            $isAsteroid = $this->object_type === 'asteroid';
            if ($useObjectId && $isAsteroid) {
                $obj = is_numeric((string) $useObjectId) ? Asteroid::find((int) $useObjectId) : Asteroid::where('slug', (string) $useObjectId)->first();
            } elseif ($useObjectId) {
                try {
                    if (is_numeric((string) $useObjectId)) {
                        $obj = \App\Models\DeepskyObject::where('id', $useObjectId)->first();
                    } else {
                        // non-numeric identifier: try to find by name or legacy 'object' column
                        $found = DB::table('objects')->where('name', $useObjectId)->first();
                        if (!$found && Schema::hasColumn('objects', 'object')) {
                            $found = DB::table('objects')->where('object', $useObjectId)->first();
                        }
                        if ($found) {
                            // Use DB row as object reference (stdClass) - property access works later
                            $obj = $found;
                        }
                    }

                    // If we still don't have an object but the caller provided an objectSlug in the payload,
                    // try a direct lookup in the objects table. This addresses legacy rows where the
                    // 'id' column may not be present or is NULL.
                    try {
                        if (empty($obj) && !empty($payload) && (is_array($payload) || is_object($payload))) {
                            $ps = (array) $payload;
                            if (!empty($ps['objectSlug'])) {
                                $slugCandidate = trim((string) $ps['objectSlug']);
                                if ($slugCandidate !== '') {
                                    $foundBySlug = DB::table('objects')->where('slug', $slugCandidate)->first();
                                    if ($foundBySlug)
                                        $obj = $foundBySlug;
                                }
                            }
                        }
                    } catch (\Throwable $_) { /* ignore slug lookup errors */
                    }
                } catch (\Throwable $_) {
                    $obj = null;
                }
            }

            // Server-side debounce: several events often carry the same change
            // (the same selection or date arriving twice), so skip a request
            // identical to one handled less than the debounce window ago. A
            // different request (a second, quick change) is always handled:
            // skipping it would leave the preview showing the first change.
            try {
                $userId = Auth::id() ?? 'guest';
                $incomingObj = $payloadObjectId ?: $this->objectId ?: 'none';
                $cacheKey = "aladin_preview_last_dispatch:{$userId}:" . ($isAsteroid ? 'asteroid:' : '') . $incomingObj;
                $fingerprintPayload = is_array($payload) ? $payload : (is_object($payload) ? (array) $payload : []);
                unset($fingerprintPayload['_ts']);
                $fingerprint = md5(serialize($fingerprintPayload));
                $last = Cache::get($cacheKey);
                if (is_array($last) && ($last['fingerprint'] ?? null) === $fingerprint
                    && (microtime(true) - (float) ($last['at'] ?? 0)) < (self::PREVIEW_DEBOUNCE_MS / 1000)) {
                    try {
                        Log::debug('AladinPreviewInfo: recalc debounced', ['user_id' => $userId, 'object' => $incomingObj, 'since_last_ms' => round((microtime(true) - (float) $last['at']) * 1000, 2)]);
                    } catch (\Throwable $_) {
                    }
                    return;
                }
                // Keep the cache entry for 30s to avoid leaving stale keys around.
                Cache::put($cacheKey, ['fingerprint' => $fingerprint, 'at' => microtime(true)], 30);
            } catch (\Throwable $_) {
                // don't block processing if cache/backend fails
            }

            // populate local debug variables for logging
            $debug_obj_id = $obj?->id ?? null;
            $debug_diam1 = $obj?->diam1 ?? null;
            $debug_diam2 = $obj?->diam2 ?? null;
            $debug_obj_mag = ($obj && isset($obj->mag) && $obj->mag != 99.9) ? $obj->mag : null;
            $debug_typical_eyepiece_focal = $obj?->typicalEyepieceFocal ?? null;

            $authUser = Auth::user();
            $userLocation = $authUser?->standardLocation ?? null;
            $userInstrument = null;

            // expose debug flags early
            $debug_user_location = (bool) $userLocation;
            $debug_user_instrument = false;

            // If the user selected a specific instrument in the aladin selects, use that
            if (!empty($payload['instrument'])) {
                try {
                    $userInstrument = \App\Models\Instrument::where('id', $payload['instrument'])->first();
                } catch (\Throwable $_) {
                    $userInstrument = null;
                }
            }

            // Fall back to authenticated user's standard instrument if not provided
            if (!$userInstrument) {
                $userInstrument = $authUser?->standardInstrument ?? null;
            }

            $debug_user_instrument = (bool) $userInstrument;

            // If the authenticated user has no configured standard instrument set
            // and the incoming payload does not supply any instrument/eyepiece/lens
            // or appearance values, short-circuit to avoid performing heavy
            // astronomy calculations.
            // This addresses slow page loads for logged-in users who haven't
            // configured an instrument set yet — the Sky preview is effectively
            // incomplete until the user provides selections.
            // The payload as an array, and the appearance values it may provide
            // (used by the guard below and by the calculations further on)
            $payloadArr = is_array($payload) ? $payload : (is_object($payload) ? (array) $payload : []);
            $payloadDiam1 = $payloadArr['diam1'] ?? null;
            $payloadMag = array_key_exists('mag', $payloadArr) ? $payloadArr['mag'] : null;

            try {
                $hasStdSet = false;
                try {
                    $hasStdSet = (bool) ($authUser?->stdinstrumentset ?? null);
                } catch (\Throwable $_) {
                    $hasStdSet = false;
                }
                $hasPayloadSelection = false;
                try {
                    $hasPayloadSelection = (
                        array_key_exists('instrument', $payloadArr) && !empty($payloadArr['instrument'])
                    ) || (
                        array_key_exists('eyepiece', $payloadArr) && !empty($payloadArr['eyepiece'])
                    ) || (
                        array_key_exists('lens', $payloadArr) && !empty($payloadArr['lens'])
                    );
                } catch (\Throwable $_) {
                    $hasPayloadSelection = false;
                }

                if (Auth::check() && !$hasStdSet && !$hasPayloadSelection && $payloadDiam1 === null && $payloadMag === null) {
                    try {
                        $this->dispatch('aladin-preview-info-updated', ...[
                            'status' => 'incomplete',
                            'contrast_reserve' => $this->contrast_reserve,
                            'optimum_detection_magnification' => $this->optimum_detection_magnification,
                            'payload' => $payload,
                            'objectId' => $useObjectId ?? null,
                        ]);
                    } catch (\Throwable $_) {
                    }
                    return;
                }
            } catch (\Throwable $_) {
                // ignore guard failures and continue with normal processing
            }

            $defaultLens = null;
            $lensFactor = 1.0;
            $defaultLensName = null;
            // Respect an explicit lens key in the payload. If the payload includes
            // the 'lens' key and it's null, treat that as an intentional "no lens"
            // selection and do NOT fall back to the user's default lens. Only when
            // the payload does NOT include the 'lens' key at all should we fall
            // back to the user's stored/default preference.
            $payloadArr = is_array($payload) ? $payload : (is_object($payload) ? (array) $payload : []);
            // Add a small debug log to record what lens value we received so we can
            // confirm whether clears from the UI reach this method as explicit null.
            // Logging removed: payload receipt logged during debugging
            $lensKeyPresent = array_key_exists('lens', $payloadArr);
            // When payload explicitly contained 'lens' => null, remember that the user
            // intentionally removed the lens so we must not append default lens names
            // to eyepieces nor apply any lens factor to magnification calculations.
            $explicitNoLens = false;
            if ($lensKeyPresent) {
                // explicit selection (could be null) — try to load when non-empty.
                // Treat string 'null' or 'undefined' (occasionally sent by the client) as explicit null.
                $rawLens = $payloadArr['lens'] ?? null;
                $rawLensStr = is_null($rawLens) ? null : (is_string($rawLens) ? trim($rawLens) : (string) $rawLens);
                if ($rawLensStr === null || $rawLensStr === '' || in_array(strtolower($rawLensStr), ['null', 'undefined'], true)) {
                    // explicit null/empty -> do not fallback; keep $defaultLens null
                    $defaultLens = null;
                    $explicitNoLens = true;
                } else {
                    try {
                        $defaultLens = \App\Models\Lens::where('id', $rawLensStr)->first();
                    } catch (\Throwable $_) {
                        $defaultLens = null;
                    }
                }
            } else {
                // No lens specified in payload: fall back to user's default lens/preferences
                $defaultLensId = $authUser?->standardLensId();
                try {
                    if (!$defaultLensId && Schema::hasColumn('users', 'preferences') && is_array($authUser?->preferences) && isset($authUser->preferences['aladin_default_lens'])) {
                        $defaultLensId = $authUser->preferences['aladin_default_lens'];
                    }
                } catch (\Throwable $_) {
                }
                if ($defaultLensId) {
                    try {
                        $defaultLens = \App\Models\Lens::where('id', $defaultLensId)->first();
                    } catch (\Throwable $_) {
                        $defaultLens = null;
                    }
                }
            }
            if ($defaultLens) {
                $lensFactor = $defaultLens->factor ?? 1.0;
                if (!is_numeric($lensFactor) || $lensFactor <= 0)
                    $lensFactor = 1.0;
                $defaultLensName = $defaultLens->name ?? null;
            }
            // expose lens factor for debugging
            $debug_lens_factor = $lensFactor;

            // If we don't have required pieces for any computation, bail out early.
            // Ephemerides only need an object and a user location; instrument is not required.
            // However, if the payload provides appearance values (diameter/magnitude)
            // we can proceed without a DB object as long as the user's location is
            // available. This lets ObjectEphemerides drive updates for planets that
            // don't exist in the DB.
            $payloadDiam1 = $payloadArr['diam1'] ?? null;
            $payloadMag = array_key_exists('mag', $payloadArr) ? $payloadArr['mag'] : null;

            if (empty($userLocation)) {
                try {
                    $this->dispatch('aladin-preview-info-updated', ...[
                        'status' => 'incomplete',
                        'contrast_reserve' => $this->contrast_reserve,
                        'optimum_detection_magnification' => $this->optimum_detection_magnification,
                        'payload' => $payload,
                        'objectId' => $useObjectId ?? null,
                    ]);
                } catch (\Throwable $_) {
                }
                return;
            }

            // If we have neither an object record nor payload-provided diam/mag,
            // we cannot compute contrast/optimum.
            if (!$obj && $payloadDiam1 === null && $payloadMag === null) {
                try {
                    $this->dispatch('aladin-preview-info-updated', ...[
                        'status' => 'incomplete',
                        'contrast_reserve' => $this->contrast_reserve,
                        'optimum_detection_magnification' => $this->optimum_detection_magnification,
                        'payload' => $payload,
                        'objectId' => $useObjectId ?? null,
                    ]);
                } catch (\Throwable $_) {
                }
                return;
            }

            // perform calculations similar to ObjectController::show
            // allow caller to provide a date in payload (ISO string or Y-m-d format);
            // a date without a time is a date of the observer, read in their timezone
            $payloadDate = (!empty($payload) && (is_array($payload) || is_object($payload))) ? (((array) $payload)['date'] ?? null) : null;
            $date = EphemerisDate::parse($payloadDate, EphemerisDate::timezone($userLocation));
            $coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
            $astrolib = new AstronomyLibrary($date, $coords, $userLocation->elevation ?? 0.0);

            // If this object is a planet, use the astronomy library to compute
            // topocentric/apparent coordinates, diameter and magnitude for the
            // requested date so ephemerides and contrast adapt when the date changes.
            // However, prefer numeric values provided in the payload (emitted by
            // ObjectEphemerides) to avoid redundant heavy calculations and to
            // guarantee the preview updates when the date changes even if the
            // DB record is missing.
            $computedDiam1 = null;
            $computedDiam2 = null;
            $computedMag = null;
            $computedCoords = null; // EquatorialCoordinates object when available
            $skipPlanetCompute = false;
            // Accept payload-provided values when present
            try {
                if (!empty($payloadArr['diam1']) || !empty($payloadArr['diam2']) || isset($payloadArr['mag'])) {
                    $computedDiam1 = $payloadArr['diam1'] ?? null;
                    $computedDiam2 = $payloadArr['diam2'] ?? null;
                    $computedMag = array_key_exists('mag', $payloadArr) ? $payloadArr['mag'] : null;
                    $skipPlanetCompute = true;
                }
                // If RA/Dec were provided, we could construct computedCoords here,
                // but ephemerides are already computed server-side by ObjectEphemerides
                // and included in the payload; the preview primarily needs diam/mag
                // to update contrast/optimum calculations.
            } catch (\Throwable $_) {
                // fall back to computing via AstronomyLibrary below
                $computedDiam1 = null;
                $computedDiam2 = null;
                $computedMag = null;
                $skipPlanetCompute = false;
            }

            try {
                $isPlanet = false;
                $planetClass = null;
                // Try to detect planetary objects by a raw type field or by name
                try {
                    $rawType = $obj->source_type_raw ?? $obj->source_type ?? null;
                    if (is_string($rawType) && strtolower($rawType) === 'planet')
                        $isPlanet = true;
                } catch (\Throwable $_) {
                }
                // Fallback: check name mapping
                if (!$isPlanet) {
                    try {
                        $pname = trim(strtolower($obj->name ?? ''));
                        $map = ['mercury' => 'Mercury', 'venus' => 'Venus', 'earth' => 'Earth', 'mars' => 'Mars', 'jupiter' => 'Jupiter', 'saturn' => 'Saturn', 'uranus' => 'Uranus', 'neptune' => 'Neptune', 'sun' => 'Sun', 'moon' => 'Moon'];
                        $key = preg_replace('/[^a-z]/', '', $pname);
                        if ($key && isset($map[$key])) {
                            $isPlanet = true;
                            $planetClass = "\\deepskylog\\AstronomyLibrary\\Targets\\" . $map[$key];
                        }
                    } catch (\Throwable $_) {
                    }
                }

                if ($isPlanet && empty($planetClass)) {
                    // If we detected planet via raw type but didn't set class, still try by name
                    try {
                        $pname = trim(strtolower($obj->name ?? ''));
                        $map = ['mercury' => 'Mercury', 'venus' => 'Venus', 'earth' => 'Earth', 'mars' => 'Mars', 'jupiter' => 'Jupiter', 'saturn' => 'Saturn', 'uranus' => 'Uranus', 'neptune' => 'Neptune', 'sun' => 'Sun', 'moon' => 'Moon'];
                        $key = preg_replace('/[^a-z]/', '', $pname);
                        if ($key && isset($map[$key])) {
                            $planetClass = "\\deepskylog\\AstronomyLibrary\\Targets\\" . $map[$key];
                        }
                    } catch (\Throwable $_) {
                    }
                }

                if (!$skipPlanetCompute && $isPlanet && $planetClass && class_exists($planetClass)) {
                    $planet = new $planetClass();
                    // determine date from payload if provided
                    $calcDate = $date->copy();
                    // Use user's location for topocentric coords when available
                    if ($userLocation && isset($userLocation->longitude) && isset($userLocation->latitude)) {
                        $geo = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                        $height = $userLocation->elevation ?? 0.0;
                        try {
                            $planet->calculateEquatorialCoordinates($calcDate->copy(), $geo, $height);
                        } catch (\Throwable $_) {
                            try {
                                if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
                                    $planet->calculateApparentEquatorialCoordinates($calcDate->copy());
                                }
                            } catch (\Throwable $_) {
                            }
                        }
                    } else {
                        try {
                            if (method_exists($planet, 'calculateApparentEquatorialCoordinates')) {
                                $planet->calculateApparentEquatorialCoordinates($calcDate->copy());
                            }
                        } catch (\Throwable $_) {
                        }
                    }

                    // extract equatorial coordinates when available
                    try {
                        if (method_exists($planet, 'getEquatorialCoordinatesToday')) {
                            $computedCoords = $planet->getEquatorialCoordinatesToday();
                        } elseif (method_exists($planet, 'getEquatorialCoordinates')) {
                            $computedCoords = $planet->getEquatorialCoordinates();
                        }
                    } catch (\Throwable $_) {
                        $computedCoords = null;
                    }

                    // calculate diameter and magnitude when supported
                    try {
                        if (method_exists($planet, 'calculateDiameter')) {
                            $planet->calculateDiameter($calcDate);
                            $pd = $planet->getDiameter();
                            if (is_array($pd) && isset($pd[0])) {
                                $computedDiam1 = $pd[0];
                                $computedDiam2 = $pd[1] ?? $pd[0];
                            }
                        }
                    } catch (\Throwable $_) {
                    }
                    try {
                        if (method_exists($planet, 'magnitude')) {
                            $computedMag = $planet->magnitude($calcDate);
                        }
                    } catch (\Throwable $_) {
                    }
                }
            } catch (\Throwable $_) {
                // ignore errors and fall back to DB values
                $computedDiam1 = null;
                $computedDiam2 = null;
                $computedMag = null;
                $computedCoords = null;
            }

            $target = new AstroTarget();
            // Prefer computed planetary values when available
            $diam1 = $computedDiam1 ?? ($obj->diam1 ?? null);
            $diam2 = $computedDiam2 ?? ($obj->diam2 ?? null);
            if ($diam1) {
                // For planets use the primary diameter as the effective diameter
                // when calculating surface brightness / contrast reserve. This
                // ensures the first diameter is always the input into contrast
                // calculations as requested.
                if (!empty($isPlanet)) {
                    $target->setDiameter($diam1, $diam1);
                } else {
                    $target->setDiameter($diam1, $diam2 ?? $diam1);
                }
            }
            $m = ($computedMag !== null) ? $computedMag : (($obj->mag && $obj->mag != 99.9) ? $obj->mag : null);
            if ($m !== null) {
                $target->setMagnitude($m);
            }
            // expose planet diameters for the Livewire view so the UI can update when the date changes
            try {
                $this->planet_diameter_primary = is_numeric($diam1) ? round($diam1, 1) : null;
                $this->planet_diameter_secondary = is_numeric($diam2) ? round($diam2, 1) : $this->planet_diameter_primary;
            } catch (\Throwable $_) {
                $this->planet_diameter_primary = null;
                $this->planet_diameter_secondary = null;
            }
            try {
                $sbT0 = microtime(true);
                $sbobj = $target->calculateSBObj();
                $sbElapsed = round((microtime(true) - $sbT0) * 1000, 2);
                try {
                    \Illuminate\Support\Facades\Log::debug('AladinPreviewInfo: calculateSBObj elapsed', ['elapsed_ms' => $sbElapsed, 'objectId' => $obj->id ?? null]);
                } catch (\Throwable $_) {
                }
            } catch (\Throwable $sbEx) {
                try {
                    \Illuminate\Support\Facades\Log::error('AladinPreviewInfo: calculateSBObj exception', ['message' => $sbEx->getMessage()]);
                } catch (\Throwable $_) {
                }
                $sbobj = null;
            }
            // show sbobj presence/value for debug (locals)
            $debug_sbobj = $sbobj !== null ? $sbobj : null;
            $sqm = $userLocation->getSqm();
            $debug_sqm = $sqm;
            $aperture = $userInstrument->aperture_mm ?? null;
            $debug_aperture = $aperture;

            // Contrast reserve at the best of the observer's magnifications, as
            // for the stored metrics of the tables (ContrastReserveCalculator),
            // dimmed by the atmosphere at the highest altitude of the object:
            // the declination for the date (ObjectEphemerides, a planet), else
            // the stored one.
            $crResult = null;
            $crDecl = null;
            if (is_numeric($payloadArr['decDeg'] ?? null)) {
                $crDecl = (float) $payloadArr['decDeg'];
            } elseif ($computedCoords) {
                try {
                    $crDecl = $computedCoords->getDeclination()->getCoordinate();
                } catch (\Throwable $_) {
                    $crDecl = null;
                }
            } elseif (is_numeric($obj->decl ?? null)) {
                $crDecl = (float) $obj->decl;
            }
            $crAltitude = ContrastReserveCalculator::highestAltitude($crDecl, $userLocation);
            if ($isAsteroid && $obj instanceof Asteroid && $userInstrument) {
                // A point source: its predicted magnitude against the limiting
                // magnitude of the instrument
                try {
                    $asteroidMag = is_numeric($computedMag) ? (float) $computedMag : null;
                    if ($asteroidMag === null && ($asteroidTarget = AsteroidCoordinates::target(AsteroidCoordinates::orbitalElements($obj->number) ?? (object) []))) {
                        $asteroidMag = AsteroidCoordinates::magnitude($asteroidTarget, $date);
                    }
                    if ($asteroidMag !== null) {
                        $this->object_mag = round($asteroidMag, 2);
                        $crResult = ContrastReserveCalculator::pointSource($asteroidMag, $userInstrument, $userLocation, $authUser, $lensFactor, null, $crAltitude);
                    }
                } catch (\Throwable $_) {
                    $crResult = null;
                }
            } elseif ($sbobj !== null && $sqm !== null && $aperture && $userInstrument && ContrastReserveCalculator::appliesTo($obj->type ?? null)) {
                try {
                    $crT0 = microtime(true);
                    $crResult = ContrastReserveCalculator::calculate($target, $userInstrument, $userLocation, $authUser, $lensFactor, null, null, $crAltitude);
                    $crElapsed = round((microtime(true) - $crT0) * 1000, 2);
                    try {
                        \Illuminate\Support\Facades\Log::debug('AladinPreviewInfo: calculateContrastReserve elapsed', ['elapsed_ms' => $crElapsed, 'objectId' => $obj->id ?? null]);
                    } catch (\Throwable $_) {
                    }
                } catch (\Throwable $crEx) {
                    try {
                        \Illuminate\Support\Facades\Log::error('AladinPreviewInfo: calculateContrastReserve exception', ['message' => $crEx->getMessage()]);
                    } catch (\Throwable $_) {
                    }
                    $crResult = null;
                }
            }
            $debug_mag = $crResult['magnification'] ?? null;

            if ($crResult && $crResult['magnification']) {
                $contrast = $crResult['contrast_reserve'];
                $this->contrast_reserve = is_numeric($contrast) ? round($contrast, 2) : null;
                $this->contrast_reserve_category = $crResult['category'];
                $this->contrast_used_location = $userLocation?->name ?? null;
                $this->contrast_used_instrument = $userInstrument?->fullName() ?? ($userInstrument?->name ?? null);
                $this->contrast_used_altitude = $crAltitude !== null ? (int) round($crAltitude) : null;
                $this->contrast_used_extinction = is_numeric($crResult['extinction'] ?? null) ? round($crResult['extinction'], 2) : null;
                // expose SQM and NELM used for the calculation when available
                try {
                    $this->contrast_used_sqm = is_numeric($sqm) ? round($sqm, 2) : null;
                } catch (\Throwable $_) {
                    $this->contrast_used_sqm = null;
                }
                try {
                    $this->contrast_used_nelm = null;
                    if ($userLocation && method_exists($userLocation, 'getNelm')) {
                        $this->contrast_used_nelm = $userLocation->getNelm();
                    }
                } catch (\Throwable $_) {
                    $this->contrast_used_nelm = null;
                }
            } else {
                $this->contrast_reserve = null;
                // not_visible for an object that never rises at the location
                $this->contrast_reserve_category = $crResult['category'] ?? null;
                $this->contrast_used_location = null;
                $this->contrast_used_instrument = null;
                $this->contrast_used_altitude = null;
                $this->contrast_used_extinction = null;
            }

            // The contrast reserve in the night of the date, with the sky lit
            // by the Moon, for deep-sky objects (planets and asteroids move,
            // and the stored metrics are for a dark sky).
            $this->moonlit = null;
            if ($this->contrast_reserve !== null && empty($isPlanet) && !$isAsteroid && is_numeric($obj->ra ?? null) && is_numeric($obj->decl ?? null)) {
                try {
                    $timezone = EphemerisDate::timezone($userLocation);
                    $this->moonlit = MoonlitContrastReserve::for($target, (float) $obj->ra * 15.0, (float) $obj->decl, $userInstrument, $userLocation, $authUser,
                        $date->copy()->timezone($timezone)->toDateString(), $timezone, $lensFactor) ?? ['contrast_reserve' => null, 'category' => null];
                    if (is_numeric($this->moonlit['contrast_reserve'])) {
                        $this->moonlit['contrast_reserve'] = round($this->moonlit['contrast_reserve'], 2);
                    }
                } catch (\Throwable $e) {
                    report($e);
                    $this->moonlit = null;
                }
            }

            // Which of the observer's nebula filters help, for an emission
            // nebula, under the same dark sky as the contrast reserve above.
            $this->filter_advice = [];
            if ($this->contrast_reserve !== null && $authUser && empty($isPlanet) && !$isAsteroid && FilterAdvice::spectrum($obj->type ?? null, $obj->name ?? null) !== null) {
                try {
                    $this->filter_advice = FilterAdvice::present(FilterAdvice::forObject($target, $obj->type ?? null, $obj->name ?? null, $userInstrument, $userLocation, $authUser, $lensFactor, $crAltitude));
                } catch (\Throwable $e) {
                    report($e);
                    $this->filter_advice = [];
                }
            }

            // Whether the telescope splits a double star, under the same sky.
            $this->double_star = null;
            if ($obj && $userInstrument && empty($isPlanet) && !$isAsteroid) {
                try {
                    $doubleStar = DoubleStarResolvability::forObject($obj, $userInstrument, $userLocation, $authUser, $lensFactor);
                    $this->double_star = $doubleStar !== null ? DoubleStarResolvability::present($doubleStar) : null;
                } catch (\Throwable $e) {
                    report($e);
                    $this->double_star = null;
                }
            }

            // log intermediate debug values for easier diagnosis
            // Debug logging removed to reduce noise. Local debug variables computed above

            // Compute optimum detection magnification using available eyepieces
            try {
                $eyepieceFocals = [];
                $eyepiecesForDisplay = [];
                $epMap = [];

                $instSet = $authUser?->standardInstrumentSet ?? null;
                if ($instSet && is_object($instSet) && isset($instSet->id))
                    $instSet = $instSet->id;
                $usedSetEyepieces = false;
                if ($instSet) {
                    $set = \App\Models\InstrumentSet::where('id', $instSet)->first();
                    if ($set) {
                        // Precompute instruments for eyepieces in this set to avoid per-eyepiece legacy queries
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

                        // Batch user slug lookup for set eyepieces
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
                                $userSlug = $epUserSlugMap[$ep->user_id] ?? null;
                                $displayName = $ep->fullName();
                                if (!$explicitNoLens && !empty($defaultLensName)) {
                                    $displayName = $displayName . ' (' . $defaultLensName . ')';
                                }
                                $eyepiecesForDisplay[] = ['name' => $displayName, 'focal' => $ef, 'slug' => $ep->slug ?? null, 'user_slug' => $userSlug];
                            }
                        }
                    }
                }
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
                            if (!$explicitNoLens && !empty($defaultLensName) && !empty($displayName)) {
                                $displayName = $displayName . ' (' . $defaultLensName . ')';
                            }
                            $eyepiecesForDisplay[] = ['name' => $displayName, 'focal' => $ef, 'slug' => $ep->slug ?? null, 'user_slug' => $userSlug];
                        }
                    } catch (\Throwable $_) {
                    }
                }

                if (!empty($eyepieceFocals) && $userInstrument?->focal_length_mm) {
                    foreach ($eyepiecesForDisplay as $epInfo) {
                        $ef = $epInfo['focal'];
                        if ($ef > 0) {
                            $m = (int) round(($userInstrument->focal_length_mm / $ef) * ($explicitNoLens ? 1.0 : $lensFactor));
                            if ($m > 0) {
                                if (!isset($epMap[$m]))
                                    $epMap[$m] = [];
                                $epMap[$m][] = $epInfo;
                            }
                        }
                    }
                }

                // The optimum magnification is the one of the contrast reserve
                $best = $crResult['magnification'] ?? null;
                $this->optimum_detection_magnification = $best ? (int) $best : null;
                // (no eyepieces with a fixed magnification or the generic magnifications)
                $this->optimum_eyepieces = ($best && !empty($crResult['eyepieces'])) ? ($epMap[(int) $best] ?? []) : [];
            } catch (\Throwable $_) {
                $this->optimum_detection_magnification = null;
                $this->optimum_eyepieces = [];
            }

            // Build ephemerides for UI when possible (object coordinates and user location available)
            $ephemerides = null;
            try {
                // If planetary coords were computed above, prefer them over stored RA/Dec
                if (isset($computedCoords) && $computedCoords) {
                    $coords = $computedCoords;
                }
                $payloadHasCoords = is_numeric($payloadArr['raDeg'] ?? null) && is_numeric($payloadArr['decDeg'] ?? null);
                if ($obj && $userLocation && (isset($obj->ra) && isset($obj->decl) || isset($coords) || $payloadHasCoords)) {
                    $tz = $userLocation->timezone ?? config('app.timezone');
                    $raDeg = null;
                    $decDeg = null;
                    // Coordinates for the date: the ones ObjectEphemerides sent, else
                    // the planet coordinates calculated above. Planets have no
                    // RA/Dec in the database.
                    if ($payloadHasCoords) {
                        $raDeg = (float) $payloadArr['raDeg'];
                        $decDeg = (float) $payloadArr['decDeg'];
                    } elseif (isset($coords) && $coords) {
                        try {
                            $raDeg = $coords->getRA()->getCoordinate() * 15.0;
                            $decDeg = $coords->getDeclination()->getCoordinate();
                        } catch (\Throwable $_) {
                            $raDeg = null;
                            $decDeg = null;
                        }
                    }
                    // attempt to convert RA/Dec using DeepskyObject helpers when available
                    try {
                        if (($raDeg === null || $decDeg === null) && method_exists(\App\Models\DeepskyObject::class, 'raToDecimal')) {
                            $raDeg = \App\Models\DeepskyObject::raToDecimal($obj->ra ?? null);
                            $decDeg = \App\Models\DeepskyObject::decToDecimal($obj->decl ?? null);
                        }
                    } catch (\Throwable $_) {
                        $raDeg = null;
                        $decDeg = null;
                    }
                    if ($raDeg === null || $decDeg === null) {
                        $raDeg = is_numeric($obj->ra ?? null) ? (float) $obj->ra : null;
                        $decDeg = is_numeric($obj->decl ?? null) ? (float) $obj->decl : null;
                    }
                    if ($raDeg !== null && $decDeg !== null) {
                        $geo_coords = new GeographicalCoordinates($userLocation->longitude, $userLocation->latitude);
                        $target2 = new AstroTarget();
                        $raHours = (is_numeric($raDeg) ? (float) $raDeg / 15.0 : $raDeg);
                        $equa = new \deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates($raHours, $decDeg);
                        $target2->setEquatorialCoordinates($equa);
                        // compute siderial and deltaT
                        try {
                            $greenwichSiderialTime = \deepskylog\AstronomyLibrary\Time::apparentSiderialTimeGreenwich($date);
                            $deltaT = \deepskylog\AstronomyLibrary\Time::deltaT($date);
                            $target2->calculateEphemerides($geo_coords, $greenwichSiderialTime, $deltaT);
                            $transit = $target2->getTransit();
                            $rising = $target2->getRising();
                            $setting = $target2->getSetting();
                            $bestTime = $target2->getBestTimeToObserve();
                            $maxHeightAtNight = $target2->getMaxHeightAtNight();
                            $maxHeight = $target2->getMaxHeight();
                            $altitudeGraph = null;
                            try {
                                $altitudeGraph = $target2->altitudeGraph($geo_coords, $date);
                            } catch (\Throwable $_) {
                                $altitudeGraph = null;
                            }
                            $yearGraph = null;
                            try {
                                $yearGraph = $target2->yearGraph($geo_coords, $date);
                            } catch (\Throwable $_) {
                                $yearGraph = null;
                            }
                            // Prefer a payload-provided year magnitude graph when available,
                            // otherwise compute via the target helper.
                            $yearMagGraph = null;
                            try {
                                if (!empty($payloadArr['ephemerides']['year_magnitude_graph'])) {
                                    $yearMagGraph = $payloadArr['ephemerides']['year_magnitude_graph'];
                                } else {
                                    $yearMagGraph = $target2->yearMagnitudeGraph($geo_coords, $date);
                                }
                            } catch (\Throwable $_) {
                                $yearMagGraph = null;
                            }
                            // Prefer a payload-provided year diameter graph when available,
                            // otherwise compute via the target helper.
                            $yearDiamGraph = null;
                            try {
                                if (!empty($payloadArr['ephemerides']['year_diameter_graph'])) {
                                    $yearDiamGraph = $payloadArr['ephemerides']['year_diameter_graph'];
                                } else {
                                    $yearDiamGraph = $target2->yearDiameterGraph($geo_coords, $date);
                                }
                            } catch (\Throwable $_) {
                                $yearDiamGraph = null;
                            }
                            if ($transit instanceof \DateTimeInterface) {
                                try {
                                    $transit = \Carbon\Carbon::instance($transit)->timezone($tz)->isoFormat('HH:mm');
                                } catch (\Throwable $_) {
                                }
                            }
                            if ($rising instanceof \DateTimeInterface) {
                                try {
                                    $rising = \Carbon\Carbon::instance($rising)->timezone($tz)->isoFormat('HH:mm');
                                } catch (\Throwable $_) {
                                }
                            }
                            if ($setting instanceof \DateTimeInterface) {
                                try {
                                    $setting = \Carbon\Carbon::instance($setting)->timezone($tz)->isoFormat('HH:mm');
                                } catch (\Throwable $_) {
                                }
                            }
                            if ($bestTime instanceof \DateTimeInterface) {
                                try {
                                    $bestTime = \Carbon\Carbon::instance($bestTime)->timezone($tz)->isoFormat('HH:mm');
                                } catch (\Throwable $_) {
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
                            $ephemerides = [
                                'date' => $date->toDateString(),
                                'rising' => $rising,
                                'transit' => $transit,
                                'setting' => $setting,
                                'best_time' => $bestTime,
                                'max_height_at_night' => $maxHeightAtNight,
                                'max_height' => $maxHeight,
                                'altitude_graph' => $altitudeGraph,
                                'year_graph' => $yearGraph,
                                'year_magnitude_graph' => $yearMagGraph,
                                'year_diameter_graph' => $yearDiamGraph,
                            ];
                        } catch (\Throwable $_) {
                            $ephemerides = null;
                        }
                    }
                }
            } catch (\Throwable $_) {
                $ephemerides = null;
            }

            // Notify frontend that a recalc finished and provide computed values
            try {
                // Use Livewire's server dispatch to notify other Livewire components
                // (this will run server-side listeners such as NearbyObjectsTable::onAladinPreviewUpdated)
                // Logging removed: server dispatch debug log
                try {
                    $this->dispatch('aladinPreviewUpdated', $payload);
                    // Logging removed: server dispatch debug log
                } catch (\Throwable $ex) {
                    try {
                        Log::error('AladinPreviewInfo: dispatch(aladinPreviewUpdated) exception', ['error' => $ex->getMessage(), 'class' => get_class($ex)]);
                    } catch (\Throwable $_) {
                    }
                }

                // Also dispatch a browser-facing event name so client-side listeners
                // (the Aladin preview forwarder) receive the update. Livewire will
                // include dispatched events in the response effects so the frontend
                // can fire them as browser events.
                // Logging removed: browser dispatch debug log
                try {
                    // Remove heavy/large embedded graph HTML from the preview payload.
                    // Altitude and year graphs belong in the main page display only.
                    $previewEphemerides = $ephemerides;
                    if (is_array($previewEphemerides)) {
                        unset($previewEphemerides['altitude_graph'], $previewEphemerides['year_graph'], $previewEphemerides['year_magnitude_graph'], $previewEphemerides['year_diameter_graph']);
                    }

                    $this->dispatch('aladin-preview-info-updated', ...[
                        'status' => 'ok',
                        'contrast_reserve' => $this->contrast_reserve,
                        'contrast_reserve_category' => $this->contrast_reserve_category ?? null,
                        'optimum_detection_magnification' => $this->optimum_detection_magnification,
                        'optimum_eyepieces' => $this->optimum_eyepieces ?? [],
                        'payload' => $payload,
                        'objectId' => $useObjectId ?? null,
                        'ephemerides' => $previewEphemerides,
                    ]);
                    // Logging removed: browser dispatch debug log
                } catch (\Throwable $ex) {
                    try {
                        Log::error('AladinPreviewInfo: dispatch(aladin-preview-info-updated) exception', ['error' => $ex->getMessage(), 'class' => get_class($ex)]);
                    } catch (\Throwable $_) {
                    }
                }
                try {
                    if (isset($tStart)) {
                        $totalElapsed = round((microtime(true) - $tStart) * 1000, 2);
                        Log::debug('AladinPreviewInfo: recalculate end', ['user_id' => Auth::id(), 'objectId' => $this->objectId, 'total_elapsed_ms' => $totalElapsed]);
                    }
                } catch (\Throwable $_) {
                }
            } catch (\Throwable $_) {
            }
        } catch (\Throwable $e) {
            // clear on error and log
            $this->contrast_reserve = null;
            $this->contrast_reserve_category = null;
            $this->optimum_detection_magnification = null;
            $this->optimum_eyepieces = [];
            $this->filter_advice = [];
            $this->double_star = null;
            $this->last_error = (string) $e->getMessage();
            Log::error('AladinPreviewInfo: recalculate() failed', ['object_id' => $this->objectId, 'user_id' => Auth::id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            try {
                $this->dispatch('aladin-preview-info-updated', ...[
                    'status' => 'error',
                    'error' => $this->last_error,
                    'payload' => $payload,
                    'objectId' => $useObjectId ?? null,
                ]);
            } catch (\Throwable $_) {
            }
        }
    }

    /**
     * Handle ephemerides emitted by the server-side ObjectEphemerides component.
     * This ensures the preview component updates diameters/magnitude and then
     * triggers a recalc so contrast reserve and optimum detection magnification
     * are recalculated immediately when the date changes.
     */
    public function handleObjectEphemeridesUpdated($payload = null)
    {
        try {
            try {
                Log::info('AladinPreviewInfo: received objectEphemeridesUpdated', ['payload' => is_array($payload) ? $payload : (is_object($payload) ? (array) $payload : $payload), 'objectId' => $this->objectId, 'user_id' => Auth::id()]);
            } catch (\Throwable $_) {
                // ignore logging errors
            }
            $p = is_array($payload) ? $payload : (is_object($payload) ? (array) $payload : []);
            if (!empty($p['diam1'])) {
                $this->planet_diameter_primary = is_numeric($p['diam1']) ? round($p['diam1'], 1) : $p['diam1'];
            }
            if (!empty($p['diam2'])) {
                $this->planet_diameter_secondary = is_numeric($p['diam2']) ? round($p['diam2'], 1) : $p['diam2'];
            }
            // store server-provided RA/Dec and magnitude so the preview can
            // display them immediately and use them when building ephemerides
            if (isset($p['raDeg'])) {
                $this->object_ra_deg = is_numeric($p['raDeg']) ? (float) $p['raDeg'] : null;
            }
            if (isset($p['decDeg'])) {
                $this->object_dec_deg = is_numeric($p['decDeg']) ? (float) $p['decDeg'] : null;
            }
            if (array_key_exists('mag', $p)) {
                $this->object_mag = is_numeric($p['mag']) ? (float) $p['mag'] : $p['mag'];
            }

            // Trigger a recalc using the provided date/objectId so all dependent
            // preview values (contrast reserve, optimum detection magnification)
            // are recomputed on the server with the new geometry.
            $date = $p['date'] ?? null;
            $oid = $p['objectId'] ?? $this->objectId;
            // Ensure the payload we pass into recalculate contains the useful
            // numeric values emitted by ObjectEphemerides so recalculate() can
            // prefer them without needing a DB lookup / heavy recompute.
            $recalcPayload = $p;
            if (empty($recalcPayload['objectId']))
                $recalcPayload['objectId'] = $oid;
            $this->recalculate($recalcPayload);
        } catch (\Throwable $_) {
            // non-fatal
        }
    }

    public function render()
    {
        return view('livewire.aladin-preview-info');
    }
}
