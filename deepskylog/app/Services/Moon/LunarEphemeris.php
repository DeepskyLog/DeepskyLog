<?php

namespace App\Services\Moon;

use App\Services\News\SkyPositions;

/**
 * The physical ephemeris of the Moon (Meeus, Astronomical Algorithms, chapter
 * 53): the libration (the selenographic point at the centre of the disk), the
 * position angle of its axis, and the selenographic position of the Sun,
 * which gives the colongitude: the selenographic longitude of the morning
 * terminator is 360° − colongitude, so the terminator moves west by about
 * 12.2° a day, from 270° at new Moon over 0° at first quarter and 90° at full
 * Moon to 180° at last quarter.
 *
 * The positions of the Moon and the Sun come from the series of the astronomy
 * library through SkyPositions, without nutation, which leaves out the same
 * small rotation of all longitudes; the results agree with Meeus' example
 * 53.a to about 0.01°.
 *
 * Selenographic longitudes are east positive, towards Mare Crisium, as in
 * the IAU gazetteer.
 */
class LunarEphemeris
{
    /** The inclination of the mean lunar equator to the ecliptic. */
    private const I = 1.54242;

    /**
     * The physical ephemeris at the moment (a Unix timestamp, UT).
     *
     * @return array{
     *     timestamp: int,
     *     colongitude: float,
     *     sun_lon: float,
     *     sun_lat: float,
     *     libration_lon: float,
     *     libration_lat: float,
     *     axis: float,
     *     fraction: float,
     *     phase_angle: float,
     *     waxing: bool,
     *     ra: float,
     *     dec: float,
     *     distance: float,
     * }
     */
    public static function at(int $timestamp): array
    {
        $moon = SkyPositions::moon($timestamp);
        $sun  = SkyPositions::sun($timestamp);
        $t    = (SkyPositions::jd($timestamp + (int) round(SkyPositions::deltaT($timestamp))) - 2451545.0) / 36525.0;

        $d     = self::polynomial($t, [297.8501921, 445267.1114034, -0.0018819, 1 / 545868.0, -1 / 113065000.0]);
        $m     = self::polynomial($t, [357.5291092, 35999.0502909, -0.0001536, 1 / 24490000.0]);
        $mp    = self::polynomial($t, [134.9633964, 477198.8675055, 0.0087414, 1 / 69699.0, -1 / 14712000.0]);
        $f     = self::polynomial($t, [93.2720950, 483202.0175233, -0.0036539, -1 / 3526000.0, 1 / 863310000.0]);
        $omega = self::polynomial($t, [125.0445479, -1934.1362891, 0.0020754, 1 / 467441.0, -1 / 60616000.0]);
        $e     = 1 - 0.002516 * $t - 0.0000074 * $t * $t;

        // The optical libration (53.1)
        [$a, $lOptical, $bOptical] = self::optical($moon['lon'], $moon['lat'], $omega, $f);

        // The physical libration (53.2)
        $k1  = 119.75                                                     + 131.849 * $t;
        $k2  = 72.56                                                      + 20.186   * $t;
        $rho = -0.02752        * self::cos($mp) - 0.02245 * self::sin($f) + 0.00684 * self::cos($mp - 2 * $f)
                                                - 0.00293          * self::cos(2 * $f) - 0.00085 * self::cos(2 * $f - 2 * $d) - 0.00054 * self::cos($mp - 2 * $d)
                                                - 0.00020          * self::sin($mp + $f) - 0.00020 * self::cos($mp + 2 * $f) - 0.00020 * self::cos($mp - $f)
                                                                                                                                                        + 0.00014          * self::cos($mp + 2 * $f - 2 * $d);
        $sigma = -0.02816      * self::sin($mp)                                                                                                         + 0.02244 * self::cos($f) - 0.00682 * self::sin($mp - 2 * $f)
                                                                          - 0.00279          * self::sin(2 * $f) - 0.00083 * self::sin(2 * $f - 2 * $d) + 0.00069 * self::sin($mp - 2 * $d)
                                                                                                                                                        + 0.00040          * self::cos($mp + $f)                      - 0.00025 * self::sin(2 * $mp) - 0.00023 * self::sin($mp + 2 * $f)
                                                                                                                                                        + 0.00020          * self::cos($mp - $f) + 0.00019 * self::sin($mp - $f) + 0.00013 * self::sin($mp + 2 * $f - 2 * $d)
                                                                                            - 0.00010          * self::cos($mp - 3 * $f);
        $tau = 0.02520         * $e * self::sin($m)                                                                                   + 0.00473 * self::sin(2 * $mp - 2 * $f) - 0.00467 * self::sin($mp)
                                                                                                                                      + 0.00396          * self::sin($k1) + 0.00276 * self::sin(2 * $mp - 2 * $d) + 0.00196 * self::sin($omega)
                                                                                             - 0.00183          * self::cos($mp - $f) + 0.00115 * self::sin($mp - 2 * $d) - 0.00096 * self::sin($mp - $d)
                                                                                                                                      + 0.00046          * self::sin(2 * $f - 2 * $d)                                  - 0.00039 * self::sin($mp - $f) - 0.00032 * self::sin($mp - $m - $d)
                                                                                                                                      + 0.00027          * self::sin(2 * $mp - $m - 2 * $d) + 0.00023 * self::sin($k2) - 0.00014 * self::sin(2 * $d)
                                                                                                                                      + 0.00014          * self::cos(2 * $mp - 2 * $f)                                 - 0.00012 * self::sin($mp - 2 * $f) - 0.00012 * self::sin(2 * $mp)
                                                                                                                                      + 0.00011          * self::sin(2 * $mp - 2 * $m - 2 * $d);
        $physical = fn (float $a, float $b) => [
            -$tau + ($rho * self::cos($a) + $sigma * self::sin($a)) * tan(deg2rad($b)),
            $sigma                                                  * self::cos($a) - $rho * self::sin($a),
        ];
        [$lPhysical, $bPhysical] = $physical($a, $bOptical);
        $l                       = $lOptical + $lPhysical;
        $b                       = $bOptical + $bPhysical;

        // The position angle of the axis
        $equatorial = SkyPositions::toEquatorial($moon['lon'], $moon['lat'], $timestamp);
        $epsilon    = SkyPositions::obliquity($timestamp);
        $v          = $omega + $sigma / self::sin(self::I);
        $x          = self::sin(self::I + $rho) * self::sin($v);
        $y          = self::sin(self::I + $rho) * self::cos($v) * self::cos($epsilon) - self::cos(self::I + $rho) * self::sin($epsilon);
        $w          = rad2deg(atan2($x, $y));
        $axis       = rad2deg(asin(max(-1.0, min(1.0, sqrt($x * $x + $y * $y) * self::cos($equatorial['ra'] - $w) / self::cos($b)))));

        // The selenographic position of the Sun: the point of the Moon seen
        // from the Sun, the heliocentric direction of the Moon
        $ratio                     = $moon['distance'] / ($sun['distance'] * SkyPositions::AU_KM);
        $lonH                      = $sun['lon'] + 180 + $ratio * rad2deg(1) * self::cos($moon['lat']) * self::sin($sun['lon'] - $moon['lon']);
        $latH                      = $ratio                     * $moon['lat'];
        [$aSun, $l0, $b0]          = self::optical($lonH, $latH, $omega, $f);
        [$l0Physical, $b0Physical] = $physical($aSun, $b0);
        $l0                        = self::wrap180($l0 + $l0Physical);
        $b0 += $b0Physical;

        // The phase angle (48.2 from the elongation, 48.3)
        $sunEquatorial = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $timestamp);
        $elongation    = acos(max(-1.0, min(1.0, self::sin($sunEquatorial['dec']) * self::sin($equatorial['dec'])
            + self::cos($sunEquatorial['dec'])                                 * self::cos($equatorial['dec']) * self::cos($sunEquatorial['ra'] - $equatorial['ra']))));
        $r          = $sun['distance'] * SkyPositions::AU_KM;
        $phaseAngle = rad2deg(atan2($r * sin($elongation), $moon['distance'] - $r * cos($elongation)));

        return [
            'timestamp'     => $timestamp,
            'colongitude'   => self::wrap360(90.0 - $l0),
            'sun_lon'       => $l0,
            'sun_lat'       => $b0,
            'libration_lon' => self::wrap180($l),
            'libration_lat' => $b,
            'axis'          => self::wrap360($axis),
            'fraction'      => (1 + cos(deg2rad($phaseAngle))) / 2,
            'phase_angle'   => $phaseAngle,
            // the Moon east of the Sun: the evening sky, between new and full Moon
            'waxing'   => self::wrap360($moon['lon'] - $sun['lon']) < 180.0,
            'ra'       => $equatorial['ra'],
            'dec'      => $equatorial['dec'],
            'distance' => $moon['distance'],
        ];
    }

    /**
     * The altitude of the Sun (degrees) above the horizon of a point of the
     * Moon, from the selenographic position of the Sun.
     */
    public static function sunAltitude(array $ephemeris, float $lat, float $lon): float
    {
        return rad2deg(asin(max(-1.0, min(
            1.0,
            self::sin($ephemeris['sun_lat'])   * self::sin($lat)
            + self::cos($ephemeris['sun_lat']) * self::cos($lat) * self::cos($lon - $ephemeris['sun_lon'])
        ))));
    }

    /**
     * Whether the Sun rises at the point (the morning terminator, the Moon
     * waxing over it) rather than sets.
     */
    public static function sunRising(array $ephemeris, float $lon): bool
    {
        // the subsolar point moves to the west, to smaller longitudes: it
        // rises where the subsolar point is less than 180° to the east
        return self::sin($lon - $ephemeris['sun_lon']) < 0;
    }

    /**
     * The angular distance (degrees) of the point from the centre of the disk
     * as seen from the Earth: 90° is the limb.
     */
    public static function distanceFromCentre(array $ephemeris, float $lat, float $lon): float
    {
        return rad2deg(acos(max(-1.0, min(
            1.0,
            self::sin($ephemeris['libration_lat'])   * self::sin($lat)
            + self::cos($ephemeris['libration_lat']) * self::cos($lat) * self::cos($lon - $ephemeris['libration_lon'])
        ))));
    }

    /**
     * The point on the disk as seen from the Earth, celestial north up and
     * east left, in radii of the disk from its centre (x right, y up), and
     * whether it is on the side facing the Earth.
     *
     * @return array{x: float, y: float, visible: bool}
     */
    public static function project(array $ephemeris, float $lat, float $lon): array
    {
        $dLon = $lon - $ephemeris['libration_lon'];
        $b    = $ephemeris['libration_lat'];
        // orthographic, selenographic north up and east (Mare Crisium) right
        $x = self::cos($lat) * self::sin($dLon);
        $y = self::cos($b)   * self::sin($lat) - self::sin($b) * self::cos($lat) * self::cos($dLon);
        $z = self::sin($b)   * self::sin($lat) + self::cos($b) * self::cos($lat) * self::cos($dLon);
        // the north pole of the Moon is turned by the position angle of the
        // axis from celestial north towards the east, to the left
        $p = $ephemeris['axis'];

        return [
            'x'       => $x * self::cos($p) - $y * self::sin($p),
            'y'       => $x * self::sin($p) + $y * self::cos($p),
            'visible' => $z >= 0,
        ];
    }

    /**
     * The direction of the Sun seen from the Moon in the frame of project():
     * x right, y up, z towards the Earth.
     *
     * @return array{x: float, y: float, z: float}
     */
    public static function sunDirection(array $ephemeris): array
    {
        $point = self::project($ephemeris, $ephemeris['sun_lat'], $ephemeris['sun_lon']);
        $dLon  = $ephemeris['sun_lon'] - $ephemeris['libration_lon'];
        $b     = $ephemeris['libration_lat'];
        $lat   = $ephemeris['sun_lat'];

        return [
            'x' => $point['x'],
            'y' => $point['y'],
            'z' => self::sin($b) * self::sin($lat) + self::cos($b) * self::cos($lat) * self::cos($dLon),
        ];
    }

    /**
     * The optical libration of a direction of the ecliptic (53.1): A, the
     * selenographic longitude and latitude.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private static function optical(float $lon, float $lat, float $omega, float $f): array
    {
        $w = $lon - $omega;
        $a = rad2deg(atan2(
            self::sin($w) * self::cos($lat) * self::cos(self::I) - self::sin($lat) * self::sin(self::I),
            self::cos($w) * self::cos($lat)
        ));
        $b = rad2deg(asin(max(-1.0, min(1.0, -self::sin($w) * self::cos($lat) * self::sin(self::I) - self::sin($lat) * self::cos(self::I)))));

        return [$a, self::wrap180($a - $f), $b];
    }

    private static function polynomial(float $t, array $coefficients): float
    {
        $value = 0.0;
        foreach ($coefficients as $power => $coefficient) {
            $value += $coefficient * $t ** $power;
        }

        return self::wrap360($value);
    }

    private static function sin(float $degrees): float
    {
        return sin(deg2rad($degrees));
    }

    private static function cos(float $degrees): float
    {
        return cos(deg2rad($degrees));
    }

    private static function wrap360(float $degrees): float
    {
        return fmod(fmod($degrees, 360.0) + 360.0, 360.0);
    }

    private static function wrap180(float $degrees): float
    {
        $degrees = self::wrap360($degrees);

        return $degrees > 180.0 ? $degrees - 360.0 : $degrees;
    }
}
