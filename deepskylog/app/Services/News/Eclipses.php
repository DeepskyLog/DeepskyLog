<?php

namespace App\Services\News;

/**
 * Solar and lunar eclipses with the method of Meeus, Astronomical Algorithms
 * chapter 54: for each new and full Moon whose argument of latitude allows
 * one, the moment of greatest eclipse (to a few minutes), γ (the least
 * distance of the shadow axis from the centre of the Earth, in Earth radii)
 * and u (the radius of the umbral cone), from which the kind of eclipse, its
 * magnitude and, for a lunar eclipse, the moments of the contacts follow.
 *
 * The local circumstances of a solar eclipse (localSolar()) are found from
 * the topocentric positions of the Sun and the Moon minute by minute, those
 * of a lunar eclipse (localLunar()) from the altitude of the Moon at the
 * contacts.
 */
class Eclipses
{
    /** Minutes between the samples of the local circumstances of a solar eclipse. */
    private const LOCAL_STEP_SECONDS = 60;

    /**
     * The eclipses whose greatest phase falls between two moments.
     *
     * @return list<array{kind: string, at: int, data: array<string, mixed>}>
     */
    public static function between(int $from, int $to): array
    {
        $eclipses = [];
        $start    = floor(((float) gmdate('Y', $from) + ((int) gmdate('z', $from)) / 365.25 - 2000.0) * 12.3685) - 1;
        $end      = ceil(((float) gmdate('Y', $to) + ((int) gmdate('z', $to)) / 365.25 - 2000.0) * 12.3685) + 1;

        for ($k = $start; $k <= $end; $k++) {
            foreach ([0.0, 0.5] as $phase) {
                $eclipse = self::forLunation($k + $phase);
                if ($eclipse !== null && $eclipse['at'] >= $from && $eclipse['at'] < $to) {
                    $eclipses[] = $eclipse;
                }
            }
        }

        usort($eclipses, fn ($a, $b) => $a['at'] <=> $b['at']);

        return $eclipses;
    }

    /**
     * The eclipse at the new Moon (integer k) or full Moon (k + 0.5) of
     * lunation k, counted from the new Moon of 2000 January 6, or null.
     *
     * @return array{kind: string, at: int, data: array<string, mixed>}|null
     */
    public static function forLunation(float $k): ?array
    {
        $t = $k / 1236.85;

        // Meeus 49.2 to 49.7, 54.1: the mean elements of the lunation
        $f = deg2rad(SkyPositions::wrap(self::polynomial($t, [160.7108 + 390.67050284 * $k, 0, -0.0016118, -0.00000227, 0.000000011])));
        if (abs(sin($f)) > 0.36) {
            return null;
        }

        $jde    = self::polynomial($t, [2451550.09766 + 29.530588861 * $k, 0, 0.00015437, -0.000000150, 0.00000000073]);
        $m      = deg2rad(SkyPositions::wrap(self::polynomial($t, [2.5534 + 29.10535670 * $k, 0, -0.0000014, -0.00000011])));
        $mPrime = deg2rad(SkyPositions::wrap(self::polynomial($t, [201.5643 + 385.81693528 * $k, 0, 0.0107582, 0.00001238, -0.000000058])));
        $omega  = deg2rad(SkyPositions::wrap(self::polynomial($t, [124.7746 - 1.56375588 * $k, 0, 0.0020672, 0.00000215])));
        $e      = self::polynomial($t, [1, -0.002516, -0.0000074]);
        $f1     = $f - deg2rad(0.02665) * sin($omega);
        $a1     = deg2rad(SkyPositions::wrap(self::polynomial($t, [299.77 + 0.107408 * $k, 0, -0.009173])));

        // The moment of greatest eclipse: [coefficient, power of E, argument]
        $solar = floor($k) == $k;
        $jde += self::series([
            [$solar ? -0.4075 : -0.4065, 0, sin($mPrime)],
            [$solar ? 0.1721 : 0.1727, 1, sin($m)],
            [0.0161, 0, sin(2 * $mPrime)],
            [-0.0097, 0, sin(2 * $f1)],
            [0.0073, 1, sin($mPrime - $m)],
            [-0.0050, 1, sin($mPrime + $m)],
            [-0.0023, 0, sin($mPrime - 2 * $f1)],
            [0.0021, 1, sin(2 * $m)],
            [0.0012, 0, sin($mPrime + 2 * $f1)],
            [0.0006, 1, sin(2 * $mPrime + $m)],
            [-0.0004, 0, sin(3 * $mPrime)],
            [-0.0003, 1, sin($m + 2 * $f1)],
            [0.0003, 0, sin($a1)],
            [-0.0002, 1, sin($m - 2 * $f1)],
            [-0.0002, 1, sin(2 * $mPrime - $m)],
            [-0.0002, 0, sin($omega)],
        ], $e);

        $p = self::series([
            [0.2070, 1, sin($m)],
            [0.0024, 1, sin(2 * $m)],
            [-0.0392, 0, sin($mPrime)],
            [0.0116, 0, sin(2 * $mPrime)],
            [-0.0073, 1, sin($mPrime + $m)],
            [0.0067, 1, sin($mPrime - $m)],
            [0.0118, 0, sin(2 * $f1)],
        ], $e);
        $q = 5.2207 + self::series([
            [-0.0048, 1, cos($m)],
            [0.0020, 1, cos(2 * $m)],
            [-0.3299, 0, cos($mPrime)],
            [-0.0060, 1, cos($mPrime + $m)],
            [0.0041, 1, cos($mPrime - $m)],
        ], $e);
        $w     = abs(cos($f1));
        $gamma = ($p * cos($f1) + $q * sin($f1)) * (1 - 0.0048 * $w);
        $u     = 0.0059 + self::series([
            [0.0046, 1, cos($m)],
            [-0.0182, 0, cos($mPrime)],
            [0.0004, 0, cos(2 * $mPrime)],
            [-0.0005, 0, cos($m + $mPrime)],
        ], $e);

        // JDE is dynamical time; the moment is stored in UT.
        $at = SkyPositions::timestampFromJd($jde);
        $at -= (int) round(SkyPositions::deltaT($at));

        return $solar ? self::solar($at, $gamma, $u) : self::lunar($at, $gamma, $u, $mPrime);
    }

    /**
     * @param  list<float|int>  $coefficients  of t^0, t^1, ...
     */
    private static function polynomial(float $t, array $coefficients): float
    {
        $value = 0.0;
        foreach (array_reverse($coefficients) as $coefficient) {
            $value = $value * $t + $coefficient;
        }

        return $value;
    }

    /**
     * The sum of the terms coefficient × E^power × argument.
     *
     * @param  list<array{0: float, 1: int, 2: float}>  $terms
     */
    private static function series(array $terms, float $e): float
    {
        $sum = 0.0;
        foreach ($terms as [$coefficient, $power, $argument]) {
            $sum += $coefficient * $e ** $power * $argument;
        }

        return $sum;
    }

    /**
     * @return array{kind: string, at: int, data: array<string, mixed>}|null
     */
    private static function solar(int $at, float $gamma, float $u): ?array
    {
        $absGamma = abs($gamma);
        if ($absGamma > 1.5433 + $u) {
            return null;
        }

        if ($absGamma < 0.9972) {
            $omega = 0.00464 * sqrt(1 - $gamma * $gamma);
            $type  = match (true) {
                $u < 0      => 'total',
                $u > 0.0047 => 'annular',
                $u < $omega => 'hybrid',
                default     => 'annular',
            };
            $magnitude = null;
        } else {
            // Not central: partial, unless the edge of the umbral cone grazes
            // the polar regions (|γ| below 1.026).
            $type      = $absGamma < 1.0260 ? ($u < 0 ? 'total' : 'annular') : 'partial';
            $magnitude = round((1.5433 + $u - $absGamma) / (0.5461 + 2 * $u), 3);
        }

        [$latitude, $longitude] = self::greatestEclipse($at);

        return [
            'kind' => 'solar_eclipse',
            'at'   => $at,
            'data' => [
                'type'      => $type,
                'central'   => $absGamma < 0.9972,
                'gamma'     => round($gamma, 4),
                'magnitude' => $magnitude,
                'latitude'  => round($latitude, 1),
                'longitude' => round($longitude, 1),
            ],
        ];
    }

    /**
     * @return array{kind: string, at: int, data: array<string, mixed>}|null
     */
    private static function lunar(int $at, float $gamma, float $u, float $mPrime): ?array
    {
        $absGamma  = abs($gamma);
        $penumbral = (1.5573 + $u - $absGamma) / 0.5450;
        $umbral    = (1.0128 - $u - $absGamma) / 0.5450;
        if ($penumbral <= 0) {
            return null;
        }

        $n        = 0.5458 + 0.0400 * cos($mPrime);
        $semi     = fn (float $x): ?int => $x > $absGamma ? (int) round(60.0 / $n * sqrt($x * $x - $gamma * $gamma) * 60) : null;
        $penumbra = $semi(1.5573 + $u);
        $partial  = $semi(1.0128 - $u);
        $totality = $semi(0.4678 - $u);

        return [
            'kind' => 'lunar_eclipse',
            'at'   => $at,
            'data' => [
                'type'                => $umbral >= 1 ? 'total' : ($umbral > 0 ? 'partial' : 'penumbral'),
                'gamma'               => round($gamma, 4),
                'umbral_magnitude'    => round($umbral, 3),
                'penumbral_magnitude' => round($penumbral, 3),
                'contacts'            => [
                    'p1'  => $penumbra !== null ? $at - $penumbra : null,
                    'u1'  => $partial  !== null ? $at - $partial : null,
                    'u2'  => $totality !== null ? $at - $totality : null,
                    'max' => $at,
                    'u3'  => $totality !== null ? $at + $totality : null,
                    'u4'  => $partial  !== null ? $at + $partial : null,
                    'p4'  => $penumbra !== null ? $at + $penumbra : null,
                ],
            ],
        ];
    }

    /**
     * The point of the Earth (latitude, east longitude) where the axis of the
     * Moon's shadow meets the surface at greatest eclipse, or the point
     * closest to it when the axis misses the Earth.
     *
     * @return array{0: float, 1: float}
     */
    public static function greatestEclipse(int $at): array
    {
        $sun  = SkyPositions::sun($at);
        $moon = SkyPositions::moon($at);
        $s    = self::vector($sun['lon'], $sun['lat'], $sun['distance'] * SkyPositions::AU_KM, $at);
        $m    = self::vector($moon['lon'], $moon['lat'], $moon['distance'], $at);

        $d   = [$m[0] - $s[0], $m[1] - $s[1], $m[2] - $s[2]];
        $len = sqrt($d[0] ** 2 + $d[1] ** 2 + $d[2] ** 2);
        $d   = [$d[0] / $len, $d[1] / $len, $d[2] / $len];
        $md  = $m[0] * $d[0] + $m[1] * $d[1] + $m[2] * $d[2];

        // Where the axis meets the sphere of the Earth: |m + t d| = R
        $disc = $md ** 2 + SkyPositions::EARTH_RADIUS_KM ** 2 - ($m[0] ** 2 + $m[1] ** 2 + $m[2] ** 2);
        // Nearest the Moon; the point closest to the axis when it misses the Earth
        $tMin = $disc >= 0 ? -$md - sqrt($disc) : -$md;
        $p    = [$m[0] + $tMin * $d[0], $m[1] + $tMin * $d[1], $m[2] + $tMin * $d[2]];

        $r          = sqrt($p[0] ** 2 + $p[1] ** 2 + $p[2] ** 2);
        $geocentric = asin($p[2] / $r);
        // Geodetic latitude from the geocentric one (Clarke flattening).
        $latitude  = rad2deg(atan(tan($geocentric) / 0.99330562));
        $longitude = SkyPositions::wrap180(rad2deg(atan2($p[1], $p[0])) - SkyPositions::siderealTime($at));

        return [$latitude, $longitude];
    }

    /**
     * What a solar eclipse looks like from a place: the moments of the first
     * contact, greatest eclipse and last contact (while the Sun is up or not),
     * the magnitude (fraction of the Sun's diameter covered), the obscuration
     * (fraction of its area) and whether the eclipse is total or annular
     * there. Null when the Moon's penumbra does not reach the place.
     *
     * @return array<string, mixed>|null
     */
    public static function localSolar(int $at, float $latitude, float $longitude, float $height = 0.0): ?array
    {
        $samples = [];
        for ($t = $at - 4 * 3600; $t <= $at + 4 * 3600; $t += self::LOCAL_STEP_SECONDS) {
            $samples[$t] = self::solarDisks($t, $latitude, $longitude, $height);
        }

        $begin = $end = $max = null;
        foreach ($samples as $t => $sample) {
            if ($sample['overlap'] <= 0) {
                continue;
            }
            $begin ??= $t;
            $end = $t;
            if ($max === null || $sample['magnitude'] > $samples[$max]['magnitude']) {
                $max = $t;
            }
        }
        if ($max === null) {
            return null;
        }

        $best     = $samples[$max];
        $sunUp    = array_filter($samples, fn ($sample, $t) => $t >= $begin && $t <= $end && $sample['altitude'] > -0.8, ARRAY_FILTER_USE_BOTH);
        $central  = $best['separation'] < abs($best['moon'] - $best['sun']);
        $obscured = self::overlapArea($best['sun'], $best['moon'], $best['separation']) / (M_PI * $best['sun'] ** 2);

        return [
            'begin'       => $begin,
            'max'         => $max,
            'end'         => $end,
            'magnitude'   => round($best['magnitude'], 3),
            'obscuration' => round(min(1.0, $obscured), 3),
            'type'        => $central ? ($best['moon'] >= $best['sun'] ? 'total' : 'annular') : 'partial',
            'altitude'    => round($best['altitude'], 1),
            'visible'     => $sunUp !== [],
            'visible_max' => $best['altitude'] > -0.8,
        ];
    }

    /**
     * What a lunar eclipse looks like from a place: the altitude of the Moon
     * at each contact.
     *
     * @param  array<string, int|null>  $contacts
     * @return array<string, float|null>
     */
    public static function localLunar(array $contacts, float $latitude, float $longitude): array
    {
        $altitudes = [];
        foreach ($contacts as $contact => $t) {
            if ($t === null) {
                $altitudes[$contact] = null;

                continue;
            }
            $moon                = SkyPositions::moon($t);
            $equatorial          = SkyPositions::toEquatorial($moon['lon'], $moon['lat'], $t);
            $topocentric         = SkyPositions::topocentric($equatorial['ra'], $equatorial['dec'], $moon['distance'], $latitude, $longitude, 0.0, $t);
            $altitudes[$contact] = round(SkyPositions::altitude($topocentric['ra'], $topocentric['dec'], $latitude, $longitude, $t), 1);
        }

        return $altitudes;
    }

    /**
     * The semidiameters (degrees) of the Sun and the topocentric Moon, their
     * distance, the magnitude of the eclipse and the Sun's altitude.
     *
     * @return array{sun: float, moon: float, separation: float, overlap: float, magnitude: float, altitude: float}
     */
    private static function solarDisks(int $t, float $latitude, float $longitude, float $height): array
    {
        $sun     = SkyPositions::sun($t);
        $moon    = SkyPositions::moon($t);
        $sunEq   = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);
        $moonEq  = SkyPositions::toEquatorial($moon['lon'], $moon['lat'], $t);
        $moonTop = SkyPositions::topocentric($moonEq['ra'], $moonEq['dec'], $moon['distance'], $latitude, $longitude, $height, $t);
        $sunTop  = SkyPositions::topocentric($sunEq['ra'], $sunEq['dec'], $sun['distance'] * SkyPositions::AU_KM, $latitude, $longitude, $height, $t);

        $rSun = 959.63 / $sun['distance'] / 3600.0;
        // The topocentric Moon is a little larger than the geocentric one.
        $altitude   = SkyPositions::altitude($moonTop['ra'], $moonTop['dec'], $latitude, $longitude, $t);
        $rMoon      = 358473400.0 / $moon['distance'] / 3600.0 * (1 + sin(deg2rad(max(0.0, $altitude))) * SkyPositions::EARTH_RADIUS_KM / $moon['distance']);
        $separation = SkyPositions::separation($sunTop['ra'], $sunTop['dec'], $moonTop['ra'], $moonTop['dec']);

        return [
            'sun'        => $rSun,
            'moon'       => $rMoon,
            'separation' => $separation,
            'overlap'    => $rSun + $rMoon - $separation,
            'magnitude'  => ($rSun + $rMoon - $separation) / (2 * $rSun),
            'altitude'   => SkyPositions::altitude($sunTop['ra'], $sunTop['dec'], $latitude, $longitude, $t),
        ];
    }

    /** The area two discs of radius r1 and r2 at distance d have in common. */
    private static function overlapArea(float $r1, float $r2, float $d): float
    {
        if ($d >= $r1 + $r2) {
            return 0.0;
        }
        if ($d <= abs($r1 - $r2)) {
            return M_PI * min($r1, $r2) ** 2;
        }
        $a = $r1 * $r1 * acos(($d * $d + $r1 * $r1 - $r2 * $r2) / (2 * $d * $r1));
        $b = $r2 * $r2 * acos(($d * $d + $r2 * $r2 - $r1 * $r1) / (2 * $d * $r2));
        $c = 0.5 * sqrt((-$d + $r1 + $r2) * ($d + $r1 - $r2) * ($d - $r1 + $r2) * ($d + $r1 + $r2));

        return $a + $b - $c;
    }

    /**
     * Geocentric equatorial rectangular coordinates (km) of an ecliptical
     * position of the date.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private static function vector(float $lon, float $lat, float $distance, int $at): array
    {
        $equatorial = SkyPositions::toEquatorial($lon, $lat, $at);
        $ra         = deg2rad($equatorial['ra']);
        $dec        = deg2rad($equatorial['dec']);

        return [$distance * cos($dec) * cos($ra), $distance * cos($dec) * sin($ra), $distance * sin($dec)];
    }
}
