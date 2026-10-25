<?php

namespace App\Services\News;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\Earth;
use deepskylog\AstronomyLibrary\Targets\Jupiter;
use deepskylog\AstronomyLibrary\Targets\Mars;
use deepskylog\AstronomyLibrary\Targets\Mercury;
use deepskylog\AstronomyLibrary\Targets\Moon;
use deepskylog\AstronomyLibrary\Targets\Neptune;
use deepskylog\AstronomyLibrary\Targets\Saturn;
use deepskylog\AstronomyLibrary\Targets\Sun;
use deepskylog\AstronomyLibrary\Targets\Uranus;
use deepskylog\AstronomyLibrary\Targets\Venus;
use deepskylog\AstronomyLibrary\Time;

/**
 * Geocentric positions of the Sun, the Moon and the planets for the
 * astronomical events of the news (#960), from the series of the astronomy
 * library (VSOP87 and Meeus chapter 47) without the library's three-day
 * ephemerides, so a year can be scanned hour by hour in a few seconds.
 *
 * Positions are ecliptical of the date (longitude, latitude in degrees); the
 * moments are Unix timestamps in UT, to which ΔT is added before the series
 * are evaluated.
 */
class SkyPositions
{
    public const PLANETS = [
        'mercury' => Mercury::class,
        'venus'   => Venus::class,
        'mars'    => Mars::class,
        'jupiter' => Jupiter::class,
        'saturn'  => Saturn::class,
        'uranus'  => Uranus::class,
        'neptune' => Neptune::class,
    ];

    /** Equatorial radius of the Earth in km. */
    public const EARTH_RADIUS_KM = 6378.14;

    public const AU_KM = 149597870.7;

    /** @var array<string, object> */
    private static array $targets = [];

    /** @var array<int, float> ΔT in seconds per year */
    private static array $deltaT = [];

    public static function jd(int $timestamp): float
    {
        return $timestamp / 86400.0 + 2440587.5;
    }

    public static function timestampFromJd(float $jd): int
    {
        return (int) round(($jd - 2440587.5) * 86400.0);
    }

    public static function deltaT(int $timestamp): float
    {
        $year = (int) gmdate('Y', $timestamp);

        return self::$deltaT[$year] ??= (float) Time::deltaT(Carbon::createFromTimestamp($timestamp, 'UTC'));
    }

    /** The moment in dynamical time, as the library's series expect it. */
    private static function tt(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp + (int) round(self::deltaT($timestamp)), 'UTC');
    }

    /**
     * The Sun: apparent longitude, latitude and distance (AU).
     *
     * @return array{lon: float, lat: float, distance: float}
     */
    public static function sun(int $timestamp): array
    {
        [$lon, $lat, $r] = self::target(Sun::class)->calculateOdotBetaR(self::tt($timestamp));
        // Aberration (Meeus 25.10)
        $lon -= 0.00569;

        return ['lon' => self::wrap($lon), 'lat' => (float) $lat, 'distance' => (float) $r];
    }

    /**
     * The Moon: geocentric longitude, latitude and distance (km).
     *
     * @return array{lon: float, lat: float, distance: float}
     */
    public static function moon(int $timestamp): array
    {
        [$lon, $lat, $r] = self::target(Moon::class)->calculateHeliocentricCoordinates(self::tt($timestamp));

        return ['lon' => self::wrap((float) $lon), 'lat' => (float) $lat, 'distance' => (float) $r];
    }

    /**
     * A planet seen from the Earth, corrected for the light time: longitude,
     * latitude, distance to the Earth and to the Sun (AU).
     *
     * @return array{lon: float, lat: float, distance: float, r: float}
     */
    public static function planet(string $planet, int $timestamp): array
    {
        $date  = self::tt($timestamp);
        $earth = self::rectangular(self::target(Earth::class)->calculateHeliocentricCoordinates($date));
        $body  = self::target(self::PLANETS[$planet]);

        $tau = 0.0;
        for ($i = 0; $i < 2; $i++) {
            $helio = $body->calculateHeliocentricCoordinates($date->copy()->subMicroseconds((int) round($tau * 86400e6)));
            $p     = self::rectangular($helio);
            $x     = $p[0] - $earth[0];
            $y     = $p[1] - $earth[1];
            $z     = $p[2] - $earth[2];
            $delta = sqrt($x * $x + $y * $y + $z * $z);
            $tau   = 0.0057755183 * $delta;
        }

        return [
            'lon'      => self::wrap(rad2deg(atan2($y, $x)) - 0.00569),
            'lat'      => rad2deg(atan2($z, sqrt($x * $x + $y * $y))),
            'distance' => $delta,
            'r'        => (float) $helio[2],
        ];
    }

    /**
     * The apparent visual magnitude of a planet, from the formulas of the
     * library (Meeus chapter 41) with the distances it would compute itself.
     */
    public static function planetMagnitude(string $planet, int $timestamp): ?float
    {
        try {
            return round((float) self::target(self::PLANETS[$planet])->magnitude(self::tt($timestamp)), 1);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The angular distance (degrees) between two points given in the same
     * spherical coordinates.
     */
    public static function separation(float $lon1, float $lat1, float $lon2, float $lat2): float
    {
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $dLon = deg2rad($lon2 - $lon1);
        $dLat = $lat2 - $lat1;
        // Haversine: accurate for the small distances that matter here.
        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;

        return rad2deg(2 * asin(min(1.0, sqrt($a))));
    }

    /** The mean obliquity of the ecliptic of the date (degrees). */
    public static function obliquity(int $timestamp): float
    {
        $t = (self::jd($timestamp) - 2451545.0) / 36525.0;

        return 23.4392911 - 0.0130042 * $t - 0.00000016 * $t * $t;
    }

    /**
     * Ecliptical to equatorial coordinates of the date.
     *
     * @return array{ra: float, dec: float} in degrees
     */
    public static function toEquatorial(float $lon, float $lat, int $timestamp): array
    {
        $e   = deg2rad(self::obliquity($timestamp));
        $l   = deg2rad($lon);
        $b   = deg2rad($lat);
        $ra  = atan2(sin($l) * cos($e) - tan($b) * sin($e), cos($l));
        $dec = asin(sin($b) * cos($e) + cos($b) * sin($e) * sin($l));

        return ['ra' => self::wrap(rad2deg($ra)), 'dec' => rad2deg($dec)];
    }

    /**
     * J2000 equatorial coordinates (an object of the database) to ecliptical
     * coordinates of the date: the general precession in longitude is added,
     * which is accurate to a few arcseconds for the coming decades.
     *
     * @return array{lon: float, lat: float}
     */
    public static function eclipticalOfDate(float $raDeg, float $decDeg, int $timestamp): array
    {
        $e   = deg2rad(23.4392911);
        $ra  = deg2rad($raDeg);
        $dec = deg2rad($decDeg);
        $lon = atan2(sin($ra) * cos($e) + tan($dec) * sin($e), cos($ra));
        $lat = asin(sin($dec) * cos($e) - cos($dec) * sin($e) * sin($ra));
        $t   = (self::jd($timestamp) - 2451545.0) / 36525.0;

        return ['lon' => self::wrap(rad2deg($lon) + 1.396971 * $t), 'lat' => rad2deg($lat)];
    }

    /** Greenwich mean sidereal time (degrees, Meeus 12.4). */
    public static function siderealTime(int $timestamp): float
    {
        $jd = self::jd($timestamp);
        $t  = ($jd - 2451545.0) / 36525.0;

        return self::wrap(280.46061837 + 360.98564736629 * ($jd - 2451545.0) + 0.000387933 * $t * $t - $t * $t * $t / 38710000.0);
    }

    /**
     * The altitude (degrees, without refraction) of a point of the sky at a
     * place; the longitude is east positive.
     */
    public static function altitude(float $raDeg, float $decDeg, float $latitude, float $longitude, int $timestamp): float
    {
        $h   = deg2rad(self::siderealTime($timestamp) + $longitude - $raDeg);
        $dec = deg2rad($decDeg);
        $lat = deg2rad($latitude);

        return rad2deg(asin(max(-1.0, min(1.0, sin($lat) * sin($dec) + cos($lat) * cos($dec) * cos($h)))));
    }

    /**
     * Topocentric equatorial coordinates of a nearby body (the Moon), from its
     * geocentric ones and its distance in km (Meeus chapter 40).
     *
     * @return array{ra: float, dec: float}
     */
    public static function topocentric(float $raDeg, float $decDeg, float $distanceKm, float $latitude, float $longitude, float $height, int $timestamp): array
    {
        // ρ sin φ′ and ρ cos φ′ of the place (Meeus 11.1 to 11.3)
        $u         = atan(0.99664719 * tan(deg2rad($latitude)));
        $elevation = $height / 6378140.0;
        $rhoSin    = $elevation * sin(deg2rad($latitude)) + 0.99664719 * sin($u);
        $rhoCos    = $elevation * cos(deg2rad($latitude)) + cos($u);

        $sinPi    = self::EARTH_RADIUS_KM / $distanceKm;
        $h        = deg2rad(self::siderealTime($timestamp) + $longitude - $raDeg);
        $dec      = deg2rad($decDeg);
        $deltaRa  = atan2(-$rhoCos * $sinPi * sin($h), cos($dec) - $rhoCos * $sinPi * cos($h));
        $decPrime = atan2((sin($dec) - $rhoSin * $sinPi) * cos($deltaRa), cos($dec) - $rhoCos * $sinPi * cos($h));

        return ['ra' => self::wrap($raDeg + rad2deg($deltaRa)), 'dec' => rad2deg($decPrime)];
    }

    /** The illuminated fraction of the Moon (0-1, Meeus 48.2 with 48.3). */
    public static function moonIllumination(int $timestamp): float
    {
        $sun  = self::sun($timestamp);
        $moon = self::moon($timestamp);
        $psi  = deg2rad(self::separation($sun['lon'], $sun['lat'], $moon['lon'], $moon['lat']));
        $r    = $sun['distance'] * self::AU_KM;
        $i    = atan2($r * sin($psi), $moon['distance'] - $r * cos($psi));

        return (1 + cos($i)) / 2;
    }

    /** An angle in [0, 360). */
    public static function wrap(float $degrees): float
    {
        $degrees = fmod($degrees, 360.0);

        return $degrees < 0 ? $degrees + 360.0 : $degrees;
    }

    /** An angle in [-180, 180). */
    public static function wrap180(float $degrees): float
    {
        return self::wrap($degrees + 180.0) - 180.0;
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $spherical  longitude, latitude (degrees), radius
     * @return array{0: float, 1: float, 2: float}
     */
    private static function rectangular(array $spherical): array
    {
        $l = deg2rad((float) $spherical[0]);
        $b = deg2rad((float) $spherical[1]);
        $r = (float) $spherical[2];

        return [$r * cos($b) * cos($l), $r * cos($b) * sin($l), $r * sin($b)];
    }

    private static function target(string $class): object
    {
        return self::$targets[$class] ??= new $class();
    }
}
