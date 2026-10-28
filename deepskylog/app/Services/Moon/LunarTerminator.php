<?php

namespace App\Services\Moon;

use App\Models\Location;
use App\Models\User;
use App\Services\News\SkyPositions;
use App\Services\NightWindow;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The lunar features at the terminator in a night: the Moon at the observer's
 * location between sunset and sunrise (night()), and the features of the
 * lunar_features table that the low Sun shows in relief then (features()):
 *
 * - the Sun between MIN_SUN_ALTITUDE and maxSunAltitude() above the feature's
 *   horizon, which grows with its size (a small crater needs long shadows, a
 *   large walled plain or a mountain range stands out longer);
 * - at most MAX_LIMB_DISTANCE from the centre of the disk, so it is not lost
 *   in the foreshortening of the limb (libration brings some closer);
 * - between MIN_DIAMETER and MAX_DIAMETER: the large maria are no
 *   terminator objects, the smallest craters too small for most telescopes.
 *
 * The showpieces of SHOWPIECES come first, then the largest features. The
 * Moon is taken at the moment it stands highest in the night, or at a chosen
 * moment; the terminator moves about half a degree of longitude an hour.
 */
class LunarTerminator
{
    /** The Sun has to be at least this high above the feature (degrees). */
    public const MIN_SUN_ALTITUDE = 0.0;

    /** The Sun at most this high above the smallest and largest features. */
    public const LOW_SUN_ALTITUDE = 3.0;

    public const HIGH_SUN_ALTITUDE = 12.0;

    /** The furthest from the centre of the disk a feature is shown (degrees). */
    public const MAX_LIMB_DISTANCE = 80.0;

    /** A feature further than this from the centre is marked as near the limb. */
    public const LIMB_DISTANCE = 65.0;

    /** Larger features (km), the maria, are left out. */
    public const MAX_DIAMETER = 300.0;

    /** Smaller features (km) are too small for most telescopes. */
    public const MIN_DIAMETER = 10.0;

    /** The Moon is up when this high above the horizon (degrees). */
    public const MIN_MOON_ALTITUDE = 5.0;

    /** The home page shows the Moon from this illuminated fraction on. */
    public const HOMEPAGE_MIN_FRACTION = 0.1;

    /** The features of the home page. */
    public const HOMEPAGE_FEATURES = 8;

    /** The Moon is followed through the night every this many minutes. */
    public const STEP_MINUTES = 15;

    /** The features most observers look for, shown first when well placed. */
    public const SHOWPIECES = [
        'Alphonsus', 'Archimedes', 'Aristarchus', 'Aristoteles', 'Arzachel', 'Atlas', 'Bailly', 'Clavius',
        'Copernicus', 'Eratosthenes', 'Eudoxus', 'Gassendi', 'Grimaldi', 'Hercules', 'Hipparchus', 'Janssen',
        'Kepler', 'Langrenus', 'Longomontanus', 'Maginus', 'Maurolycus', 'Mons Pico', 'Mons Piton', 'Mons Rümker',
        'Montes Alpes', 'Montes Apenninus', 'Montes Caucasus', 'Montes Jura', 'Montes Teneriffe', 'Petavius',
        'Plato', 'Posidonius', 'Promontorium Laplace', 'Ptolemaeus', 'Purbach', 'Rima Ariadaeus', 'Rima Hyginus',
        'Rupes Recta', 'Schickard', 'Sinus Iridum', 'Stöfler', 'Theophilus', 'Tycho', 'Vallis Alpes',
        'Vallis Rheita', 'Vallis Schröteri', 'Walther', 'Wargentin', 'Cyrillus', 'Catharina', 'Fracastorius',
        'Albategnius', 'Endymion', 'Proclus', 'Messier', 'Cassini', 'Deslandres', 'Schiller', 'Reiner Gamma',
    ];

    /**
     * After the showpieces, the craters, mountains and valleys come first,
     * then the rilles, ridges and capes, then the bays, lakes and marshes.
     */
    private const TYPE_ORDER = ['crater' => 0, 'mountain' => 0, 'valley' => 0, 'other' => 1, 'mare' => 2];

    private const CATALOGUE_KEY = 'lunar_terminator:catalogue:v1';

    /**
     * The Sun may stand this high (degrees) above a feature of this diameter
     * (km) for it to be well placed.
     */
    public static function maxSunAltitude(float $diameter): float
    {
        return max(self::LOW_SUN_ALTITUDE, min(self::HIGH_SUN_ALTITUDE, $diameter / 10.0));
    }

    /**
     * The colongitude at which the Sun rises over a feature at this longitude
     * (sets 180° later), when the Sun stands above the lunar equator.
     */
    public static function sunriseColongitude(float $lon): float
    {
        return fmod(fmod(-$lon, 360.0) + 360.0, 360.0);
    }

    /**
     * The lunar features with a position and diameter, cached a day
     * (forgotten by LunarGazetteer::apply()).
     *
     * @return list<array{id: int, name: string, slug: ?string, type: string, lat: float, lon: float, diameter: float}>
     */
    public static function catalogue(): array
    {
        return Cache::remember(self::CATALOGUE_KEY, 86400, fn () => DB::table('lunar_features')
            ->whereNotNull('lat')->whereNotNull('lon')->whereNotNull('diameter')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'feature_type', 'lat', 'lon', 'diameter'])
            ->map(fn ($row) => [
                'id'       => (int) $row->id,
                'name'     => (string) $row->name,
                'slug'     => $row->slug,
                'type'     => (string) $row->feature_type,
                'lat'      => (float) $row->lat,
                'lon'      => (float) $row->lon,
                'diameter' => (float) $row->diameter,
            ])
            ->all());
    }

    public static function forgetCatalogue(): void
    {
        Cache::forget(self::CATALOGUE_KEY);
    }

    /**
     * How well placed the feature is in the physical ephemeris: the Sun's
     * altitude over it, rising or setting, its distance from the centre of
     * the disk, and whether that makes it well placed.
     *
     * @param  array{lat: float, lon: float, diameter: float}  $feature
     * @return array{sun_altitude: float, rising: bool, centre_distance: float, limb: bool, well_placed: bool}
     */
    public static function placement(array $ephemeris, array $feature): array
    {
        $altitude = LunarEphemeris::sunAltitude($ephemeris, $feature['lat'], $feature['lon']);
        $distance = LunarEphemeris::distanceFromCentre($ephemeris, $feature['lat'], $feature['lon']);

        return [
            'sun_altitude'    => $altitude,
            'rising'          => LunarEphemeris::sunRising($ephemeris, $feature['lon']),
            'centre_distance' => $distance,
            'limb'            => $distance > self::LIMB_DISTANCE,
            'well_placed'     => $altitude > self::MIN_SUN_ALTITUDE
                && $altitude            <= self::maxSunAltitude($feature['diameter'])
                && $distance            <= self::MAX_LIMB_DISTANCE
                && $feature['diameter'] <= self::MAX_DIAMETER
                && $feature['diameter'] >= self::MIN_DIAMETER,
        ];
    }

    /**
     * The features well placed in the physical ephemeris, the showpieces
     * first, then by TYPE_ORDER and diameter.
     *
     * @return list<array<string, mixed>> the features of catalogue() with placement() and showpiece
     */
    public static function features(array $ephemeris, ?int $limit = null): array
    {
        $showpieces = array_flip(array_map('mb_strtolower', self::SHOWPIECES));
        $features   = [];
        foreach (self::catalogue() as $feature) {
            $placement = self::placement($ephemeris, $feature);
            if ($placement['well_placed']) {
                $features[] = $feature + $placement + ['showpiece' => isset($showpieces[mb_strtolower($feature['name'])])];
            }
        }
        $rank = fn (array $feature) => [! $feature['showpiece'], self::TYPE_ORDER[$feature['type']] ?? 1, -$feature['diameter']];
        usort($features, fn (array $a, array $b) => $rank($a) <=> $rank($b));

        return $limit === null ? $features : array_slice($features, 0, $limit);
    }

    /**
     * The Moon in the night that starts on the evening of $night (Y-m-d in
     * $timezone) at the location: between sunset and sunrise, when it is up
     * (at least MIN_MOON_ALTITUDE high), the moment it stands highest then
     * and the physical ephemeris at that moment, or at $at (a timestamp) when
     * given. When the Moon is not up in the night, the ephemeris is that of
     * the moment it is highest in the day around it (noon to noon). Cached a
     * day per place and night.
     *
     * @return array{night: string, timezone: string, sunset: int, sunrise: int, up_from: ?int, up_until: ?int, best: ?int, best_altitude: ?float, moment: int, ephemeris: array<string, mixed>, phase: string}
     */
    public static function night(float $latitude, float $longitude, string $night, string $timezone, ?int $at = null): array
    {
        $key    = 'lunar_terminator:night:v1:'.md5(json_encode([round($latitude, 2), round($longitude, 2), $night, $timezone]));
        $result = Cache::remember($key, 86400, fn () => self::computeNight($latitude, $longitude, $night, $timezone));

        if ($at !== null) {
            $result['moment']    = $at;
            $result['ephemeris'] = LunarEphemeris::at($at);
            $result['phase']     = self::phase($result['ephemeris']);
        }

        return $result;
    }

    /**
     * The night of night() at a location of the database.
     */
    public static function nightAt(Location $location, string $night, string $timezone, ?int $at = null): array
    {
        return self::night((float) $location->latitude, (float) $location->longitude, $night, $timezone, $at);
    }

    /**
     * The coming nights from $night on with the colongitude and the best
     * features of each.
     *
     * @return list<array<string, mixed>> night() with 'features'
     */
    public static function upcoming(Location $location, string $night, string $timezone, int $nights = 14, int $perNight = 5): array
    {
        $date   = Carbon::parse($night);
        $result = [];
        for ($i = 0; $i < $nights; $i++) {
            $entry             = self::nightAt($location, $date->copy()->addDays($i)->toDateString(), $timezone);
            $entry['features'] = $entry['best'] === null ? [] : self::features($entry['ephemeris'], $perNight);
            $result[]          = $entry;
        }

        return $result;
    }

    /**
     * The nights from $night on, over $nights nights, in which the Moon is up
     * and the feature well placed at the moment the Moon is highest.
     *
     * @return list<array<string, mixed>> night() with the feature's placement()
     */
    public static function nightsFor(array $feature, Location $location, string $night, string $timezone, int $nights = 30): array
    {
        $date   = Carbon::parse($night);
        $result = [];
        for ($i = 0; $i < $nights; $i++) {
            $entry = self::nightAt($location, $date->copy()->addDays($i)->toDateString(), $timezone);
            if ($entry['best'] === null) {
                continue;
            }
            $placement = self::placement($entry['ephemeris'], $feature);
            if ($placement['well_placed']) {
                $result[] = $entry + $placement;
            }
        }

        return $result;
    }

    /**
     * The name of the phase: new, waxing_crescent, first_quarter,
     * waxing_gibbous, full, waning_gibbous, last_quarter or waning_crescent,
     * from the colongitude (270° new, 0° first quarter, 90° full, 180° last
     * quarter), with ±7° (about 14 hours) for the moments themselves.
     */
    public static function phase(array $ephemeris): string
    {
        $c    = $ephemeris['colongitude'];
        $near = fn (float $target) => abs(fmod($c - $target + 540.0, 360.0) - 180.0) <= 7.0;

        return match (true) {
            $near(270.0) => 'new',
            $near(0.0)   => 'first_quarter',
            $near(90.0)  => 'full',
            $near(180.0) => 'last_quarter',
            $c > 270.0   => 'waxing_crescent',
            $c < 90.0    => 'waxing_gibbous',
            $c < 180.0   => 'waning_gibbous',
            default      => 'waning_crescent',
        };
    }

    /**
     * The Moon of tonight for the home page at the observer's location (else
     * Location::forEphemerides()) with its first features at the terminator,
     * or null when the Moon is not up in the night or too thin a crescent
     * (less than HOMEPAGE_MIN_FRACTION lit).
     *
     * @return array{moon: array<string, mixed>, features: list<array<string, mixed>>}|null
     */
    public static function forHomePage(?User $user): ?array
    {
        $location = Location::forEphemerides($user);
        if (! $location || ! $location->hasValidCoordinates()) {
            return null;
        }
        $timezone = EphemerisDate::timezone($location);
        $moon     = self::nightAt($location, EphemerisDate::current($timezone), $timezone);
        if ($moon['best'] === null || $moon['ephemeris']['fraction'] < self::HOMEPAGE_MIN_FRACTION) {
            return null;
        }
        $features = self::features($moon['ephemeris'], self::HOMEPAGE_FEATURES);

        return $features === [] ? null : ['moon' => $moon, 'features' => $features];
    }

    /**
     * The phase of phase() in the language of the page.
     */
    public static function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'new'             => __('New Moon'),
            'waxing_crescent' => __('Waxing crescent'),
            'first_quarter'   => __('First quarter'),
            'waxing_gibbous'  => __('Waxing gibbous'),
            'full'            => __('Full Moon'),
            'waning_gibbous'  => __('Waning gibbous'),
            'last_quarter'    => __('Last quarter'),
            default           => __('Waning crescent'),
        };
    }

    /**
     * The type of a lunar feature in the language of the page.
     */
    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'crater'   => __('Crater'),
            'mare'     => __('Mare'),
            'mountain' => __('Mountain'),
            'valley'   => __('Valley'),
            default    => __('Other'),
        };
    }

    private static function computeNight(float $latitude, float $longitude, string $night, string $timezone): array
    {
        $noon     = Carbon::parse($night.' 12:00', $timezone)->getTimestamp();
        $today    = date_sun_info($noon, $latitude, $longitude);
        $tomorrow = date_sun_info($noon + 86400, $latitude, $longitude);
        // no sunset or sunrise (midnight Sun, polar night): the whole day
        $sunset  = is_int($today['sunset'] ?? null)     && $today['sunset']         > $noon ? $today['sunset'] : $noon;
        $sunrise = is_int($tomorrow['sunrise'] ?? null) && $tomorrow['sunrise'] > $sunset ? $tomorrow['sunrise'] : $noon + 86400;

        $window       = NightWindow::span($latitude, $longitude, $noon, $noon + 86400);
        $step         = self::STEP_MINUTES * 60;
        $upFrom       = $upUntil = $best = $dayBest = null;
        $bestAltitude = $dayBestAltitude = -90.0;
        for ($t = $noon; $t <= $noon + 86400; $t += $step) {
            $altitude = self::moonAltitude($window, $t);
            if ($altitude > $dayBestAltitude) {
                $dayBestAltitude = $altitude;
                $dayBest         = $t;
            }
            if ($t < $sunset || $t > $sunrise || $altitude < self::MIN_MOON_ALTITUDE) {
                continue;
            }
            $upFrom ??= $t;
            $upUntil = $t;
            if ($altitude > $bestAltitude) {
                $bestAltitude = $altitude;
                $best         = $t;
            }
        }

        $moment    = $best ?? $dayBest;
        $ephemeris = LunarEphemeris::at($moment);

        return [
            'night'         => $night,
            'timezone'      => $timezone,
            'sunset'        => $sunset,
            'sunrise'       => $sunrise,
            'up_from'       => $upFrom,
            'up_until'      => $upUntil,
            'best'          => $best,
            'best_altitude' => $best === null ? null : $bestAltitude,
            'moment'        => $moment,
            'ephemeris'     => $ephemeris,
            'phase'         => self::phase($ephemeris),
        ];
    }

    /**
     * The altitude of the Moon (degrees) at the place of the window, corrected
     * for its parallax.
     */
    private static function moonAltitude(NightWindow $window, int $timestamp): float
    {
        $moon       = SkyPositions::moon($timestamp);
        $equatorial = SkyPositions::toEquatorial($moon['lon'], $moon['lat'], $timestamp);
        $altitude   = $window->altitude($equatorial['ra'], $equatorial['dec'], $timestamp);
        $parallax   = rad2deg(asin(SkyPositions::EARTH_RADIUS_KM / $moon['distance']));

        return $altitude - $parallax * cos(deg2rad($altitude));
    }
}
