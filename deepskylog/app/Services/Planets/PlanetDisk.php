<?php

namespace App\Services\Planets;

use App\Services\News\SkyPositions;

/**
 * The disk of Mars or Venus as a telescope shows it at a moment: the
 * apparent diameter, the illuminated fraction and the position angle of the
 * bright limb, the magnitude and the elongation from the Sun; for Mars also
 * the physical ephemeris of Meeus chapter 42 (checked against example 42.a):
 * the planetocentric declinations of the Earth (DE, the tilt of the disk
 * towards us) and of the Sun (DS), the position angle P of the north pole,
 * the longitude ω of the central meridian (areographic, west, as the maps of
 * the ALPO and BAA give the features) and the season, the areocentric
 * longitude Ls of the Sun.
 *
 * Nutation and aberration are left out (a few hundredths of a degree). The
 * positions come from SkyPositions::planetVectors() (VSOP87 of the library,
 * corrected for the light time).
 */
class PlanetDisk
{
    public const PLANETS = ['mars', 'venus'];

    /** The equatorial semidiameter at 1 AU (arcseconds, Meeus chapter 55; Venus with its clouds). */
    public const SEMIDIAMETER = ['mars' => 4.68, 'venus' => 8.41];

    /** The polar radius of the planet in its equatorial radius. */
    public const FLATTENING = ['mars' => 0.9941, 'venus' => 1.0];

    /**
     * @return array{planet: string, at: int, diameter: float, illuminated: float, phase_angle: float, bright_limb: float, magnitude: ?float, elongation: float, distance: float, sun_distance: float, ra: float, dec: float, de?: float, ds?: float, p?: float, cm?: float, ls?: float, defect?: float}
     */
    public static function at(string $planet, int $timestamp): array
    {
        $v              = SkyPositions::planetVectors($planet, $timestamp);
        [$ex, $ey, $ez] = $v['earth'];
        [$px, $py, $pz] = $v['planet'];
        [$x, $y, $z]    = [$px - $ex, $py - $ey, $pz - $ez];
        $delta          = sqrt($x * $x + $y * $y + $z * $z);
        $r              = sqrt($px * $px + $py * $py + $pz * $pz);
        $earthR         = sqrt($ex * $ex + $ey * $ey + $ez * $ez);
        $lambda         = rad2deg(atan2($y, $x));
        $beta           = rad2deg(atan2($z, sqrt($x * $x + $y * $y)));
        $equatorial     = SkyPositions::toEquatorial($lambda, $beta, $timestamp);
        $sun            = SkyPositions::sun($timestamp);
        $sunEquatorial  = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $timestamp);
        $cosPhase       = max(-1.0, min(1.0, ($r * $r + $delta * $delta - $earthR * $earthR) / (2 * $r * $delta)));
        $diameter       = 2 * self::SEMIDIAMETER[$planet] / $delta;
        $illuminated    = (1 + $cosPhase)                 / 2;
        $elongation     = SkyPositions::separation($sun['lon'], $sun['lat'], $lambda, $beta);

        $result = [
            'planet'      => $planet,
            'at'          => $timestamp,
            'diameter'    => $diameter,
            'illuminated' => $illuminated,
            'phase_angle' => rad2deg(acos($cosPhase)),
            'bright_limb' => self::positionAngle($sunEquatorial['ra'], $sunEquatorial['dec'], $equatorial['ra'], $equatorial['dec']),
            'magnitude'   => SkyPositions::planetMagnitude($planet, $timestamp),
            // positive east of the Sun: an evening sky
            'elongation'   => SkyPositions::wrap180($lambda - $sun['lon']) >= 0 ? $elongation : -$elongation,
            'distance'     => $delta,
            'sun_distance' => $r,
            'ra'           => $equatorial['ra'],
            'dec'          => $equatorial['dec'],
        ];

        return $planet === 'mars' ? $result + self::mars($timestamp, $v, $x, $y, $z, $lambda, $beta, $equatorial, $diameter, $illuminated) : $result;
    }

    /**
     * The physical ephemeris of Mars (Meeus chapter 42).
     *
     * @param  array{earth: array{0: float, 1: float, 2: float}, planet: array{0: float, 1: float, 2: float}, tau: float}  $v
     * @param  array{ra: float, dec: float}  $equatorial
     * @return array{de: float, ds: float, p: float, cm: float, ls: float, defect: float}
     */
    private static function mars(int $timestamp, array $v, float $x, float $y, float $z, float $lambda, float $beta, array $equatorial, float $diameter, float $illuminated): array
    {
        $jde = SkyPositions::jd($timestamp) + SkyPositions::deltaT($timestamp) / 86400.0;
        $t   = ($jde - 2451545.0)                                              / 36525.0;

        // The north pole of Mars in ecliptical coordinates of the date
        $lambda0 = 352.9065 + 1.17330 * $t;
        $beta0   = 63.2818 - 0.00394  * $t;
        $pole    = self::unit($lambda0, $beta0);

        $sinDe = -self::sin($beta0) * self::sin($beta) - self::cos($beta0) * self::cos($beta) * self::cos($lambda0 - $lambda);

        // The direction of the Sun seen from Mars, and the plane of the orbit from the motion in a day
        [$px, $py, $pz] = $v['planet'];
        $r              = sqrt($px * $px + $py * $py + $pz * $pz);
        $sun            = [-$px / $r, -$py / $r, -$pz / $r];
        $normal         = self::normalize(self::cross($v['planet'], SkyPositions::planetVectors('mars', $timestamp + 86400)['planet']));
        // Mars's vernal equinox: where the Sun crosses its equator going north
        $equinox = self::normalize(self::cross($pole, $normal));
        $ls      = SkyPositions::wrap(rad2deg(atan2(self::dot(self::cross($equinox, $sun), $normal), self::dot($equinox, $sun))));

        $w       = 11.504 + 350.89200025 * ($jde - $v['tau'] - 2433282.5);
        $poleEq  = SkyPositions::toEquatorial($lambda0, $beta0, $timestamp);
        $epsilon = deg2rad(SkyPositions::obliquity($timestamp));
        $u       = $y * cos($epsilon) - $z * sin($epsilon);
        $vv      = $y * sin($epsilon) + $z * cos($epsilon);
        $alpha   = atan2($u, $x);
        $delta   = atan2($vv, sqrt($x * $x + $u * $u));
        $alpha0  = deg2rad($poleEq['ra']);
        $delta0  = deg2rad($poleEq['dec']);
        $zeta    = atan2(sin($delta0) * cos($delta) * cos($alpha0 - $alpha) - sin($delta) * cos($delta0), cos($delta) * sin($alpha0 - $alpha));

        return [
            'de'     => rad2deg(asin(max(-1.0, min(1.0, $sinDe)))),
            'ds'     => rad2deg(asin(max(-1.0, min(1.0, self::dot($sun, $pole))))),
            'p'      => self::positionAngle($poleEq['ra'], $poleEq['dec'], $equatorial['ra'], $equatorial['dec']),
            'cm'     => SkyPositions::wrap($w - rad2deg($zeta)),
            'ls'     => $ls,
            'defect' => (1 - $illuminated) * $diameter,
        ];
    }

    /**
     * The position angle (degrees from the north through the east) of the
     * direction towards a point, seen from the centre of the disk (Meeus 48.5).
     */
    private static function positionAngle(float $toRa, float $toDec, float $ra, float $dec): float
    {
        $dRa = deg2rad($toRa - $ra);

        return SkyPositions::wrap(rad2deg(atan2(
            self::cos($toDec) * sin($dRa),
            self::sin($toDec) * self::cos($dec) - self::cos($toDec) * self::sin($dec) * cos($dRa)
        )));
    }

    /** @return array{0: float, 1: float, 2: float} */
    private static function unit(float $lon, float $lat): array
    {
        return [self::cos($lat) * self::cos($lon), self::cos($lat) * self::sin($lon), self::sin($lat)];
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $a
     * @param  array{0: float, 1: float, 2: float}  $b
     * @return array{0: float, 1: float, 2: float}
     */
    private static function cross(array $a, array $b): array
    {
        return [$a[1] * $b[2] - $a[2] * $b[1], $a[2] * $b[0] - $a[0] * $b[2], $a[0] * $b[1] - $a[1] * $b[0]];
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $a
     * @param  array{0: float, 1: float, 2: float}  $b
     */
    private static function dot(array $a, array $b): float
    {
        return $a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2];
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $a
     * @return array{0: float, 1: float, 2: float}
     */
    private static function normalize(array $a): array
    {
        $length = sqrt(self::dot($a, $a));

        return [$a[0] / $length, $a[1] / $length, $a[2] / $length];
    }

    private static function sin(float $degrees): float
    {
        return sin(deg2rad($degrees));
    }

    private static function cos(float $degrees): float
    {
        return cos(deg2rad($degrees));
    }
}
