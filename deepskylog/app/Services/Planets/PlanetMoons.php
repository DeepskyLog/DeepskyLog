<?php

namespace App\Services\Planets;

use App\Services\News\SkyPositions;

/**
 * The positions of the moons of Jupiter and Saturn, seen from the Earth and
 * from the Sun (where their shadows fall), with the longitude of Jupiter's
 * System II central meridian (for the Great Red Spot).
 *
 * - The Galilean moons: the high accuracy method of Meeus chapter 44 (the
 *   E5 theory of Lieske), good to a few hundredths of a Jupiter radius, so
 *   the moments of their phenomena to about a minute.
 * - The moons of Saturn: Meeus chapter 46 (Titan after Dourneau's theory).
 * - The central meridian: Meeus chapter 42, of the geometric disk.
 *
 * Coordinates are in equatorial radii of the planet, measured from its centre
 * in the plane of the sky: X along the planet's equator, positive to the
 * west; Y positive to the north of it; Z positive when the moon is further
 * away than the planet (behind it). The positions of the Earth and the planet
 * (SkyPositions::planetVectors()) change slowly and are taken every hour and
 * interpolated, so a night can be followed minute by minute.
 */
class PlanetMoons
{
    public const JUPITER_MOONS = ['io', 'europa', 'ganymede', 'callisto'];

    /** The moons of Saturn that a small telescope shows (Mimas, Enceladus and Hyperion are left out). */
    public const SATURN_MOONS = ['tethys', 'dione', 'rhea', 'titan', 'iapetus'];

    /** The polar radius of the planet in its equatorial radius. */
    public const FLATTENING = ['jupiter' => 0.93513, 'saturn' => 0.90196];

    /** The equatorial semidiameter of the planet at 1 AU (arcseconds, Meeus chapter 55). */
    public const SEMIDIAMETER = ['jupiter' => 98.44, 'saturn' => 82.73];

    /** The outer edge of the A ring in Saturn radii. */
    public const RING_RADIUS = 2.27;

    /** @var array<string, array<string, mixed>> the geometry per planet and hour */
    private static array $hours = [];

    /**
     * The four Galilean moons seen from the Earth ('earth') and the Sun
     * ('sun'), Jupiter's distance (AU) and the System II longitude of its
     * central meridian (degrees).
     *
     * @return array{earth: array<string, array{x: float, y: float, z: float}>, sun: array<string, array{x: float, y: float, z: float}>, distance: float, cm2: float}
     */
    public static function jupiter(int $timestamp): array
    {
        $g   = self::geometry('jupiter', $timestamp);
        $jde = $g['jde'];
        $t   = $jde - 2443000.5 - $g['tau'];

        $l1 = 106.07719 + 203.488955790 * $t;
        $l2 = 175.73161 + 101.374724735 * $t;
        $l3 = 120.55883 + 50.317609207  * $t;
        $l4 = 84.44459  + 21.571071177   * $t;
        $p1 = 97.0881   + 0.16138586      * $t;
        $p2 = 154.8663  + 0.04726307     * $t;
        $p3 = 188.1840  + 0.00712734     * $t;
        $p4 = 335.2868  + 0.00184000     * $t;
        $o1 = 312.3346 - 0.13279386     * $t;
        $o2 = 100.4411 - 0.03263064     * $t;
        $o3 = 119.1942 - 0.00717703     * $t;
        $o4 = 322.6186 - 0.00175934     * $t;
        // The principal inequality in the longitude of Jupiter
        $gamma = 0.33033 * self::sin(163.679 + 0.0010512 * $t) + 0.03439 * self::sin(34.486 - 0.0161731 * $t);
        // The libration of the inner moons
        $phi = 199.6766 + 0.17379190 * $t;
        // The node of Jupiter's equator on the ecliptic
        $psi = 316.5182 - 0.00000208 * $t;
        // The mean anomalies of Jupiter and Saturn, the perihelion of Jupiter
        $G  = 30.23756 + 0.0830925701 * $t + $gamma;
        $G_ = 31.97853 + 0.0334597339 * $t;
        $PI = 13.469942;

        $s = fn (float $x) => self::sin($x);
        $c = fn (float $x) => self::cos($x);

        $sum1 = 0.47259 * $s(2 * ($l1 - $l2))     - 0.03478 * $s($p3 - $p4)                                                                 + 0.01081 * $s($l2 - 2 * $l3 + $p3) + 0.00738 * $s($phi)
                                                                                                                                            + 0.00713   * $s($l2 - 2 * $l3 + $p2) - 0.00674 * $s($p1 + $p3 - 2 * $PI - 2 * $G) + 0.00666 * $s($l2 - 2 * $l3 + $p4)
                                                                                                                                            + 0.00445   * $s($l1 - $p3)           - 0.00354 * $s($l1 - $l2) - 0.00317 * $s(2 * $psi - 2 * $PI) + 0.00265 * $s($l1 - $p4)
                                                  - 0.00186   * $s($G)                                                                      + 0.00162 * $s($p2 - $p3) + 0.00158 * $s(4 * ($l1 - $l2)) - 0.00155 * $s($l1 - $l3)
                                                  - 0.00138   * $s($psi + $o3 - 2 * $PI - 2 * $G) - 0.00115 * $s(2 * ($l1 - 2 * $l2 + $o2)) + 0.00089 * $s($p2 - $p4)
                                                                                                                                            + 0.00085   * $s($l1 + $p3 - 2 * $PI - 2 * $G) + 0.00083 * $s($o2 - $o3) + 0.00053 * $s($psi - $o2);

        $sum2 = 1.06476 * $s(2 * ($l2 - $l3))                                                                             + 0.04256 * $s($l1 - 2 * $l2 + $p3) + 0.03581 * $s($l2 - $p3)
                                                                                                                          + 0.02395   * $s($l1 - 2 * $l2 + $p4) + 0.01984 * $s($l2 - $p4) - 0.01778 * $s($phi) + 0.01654 * $s($l2 - $p2)
                                                                                                                          + 0.01334   * $s($l2 - 2 * $l3 + $p2) + 0.01294 * $s($p3 - $p4) - 0.01142 * $s($l2 - $l3) - 0.01057 * $s($G)
                                                                            - 0.00775   * $s(2 * ($psi - $PI))            + 0.00524 * $s(2 * ($l1 - $l2)) - 0.00460 * $s($l1 - $l3)
                                                                                                                          + 0.00316   * $s($psi - 2 * $G + $o3 - 2 * $PI)                 - 0.00203 * $s($p1 + $p3 - 2 * $PI - 2 * $G) + 0.00146 * $s($psi - $o3)
                                                                            - 0.00145   * $s(2 * $G)                      + 0.00125 * $s($psi - $o4) - 0.00115 * $s($l1 - 2 * $l3 + $p3) - 0.00094 * $s(2 * ($l2 - $o2))
                                                                                                                          + 0.00086   * $s(2 * ($l1 - 2 * $l2 + $o2))                     - 0.00086 * $s(5 * $G_ - 2 * $G + 52.225) - 0.00078 * $s($l2 - $l4)
                                                                            - 0.00064   * $s(3 * $l3 - 7 * $l4 + 4 * $p4) + 0.00064 * $s($p1 - $p4) - 0.00063 * $s($l1 - 2 * $l3 + $p4)
                                                                                                                          + 0.00058   * $s($o3 - $o4) + 0.00056 * $s(2 * ($psi - $PI - $G)) + 0.00056 * $s(2 * ($l2 - $l4))
                                                                                                                          + 0.00055   * $s(2 * ($l1 - $l3)) + 0.00052 * $s(3 * $l3 - 7 * $l4 + $p3 + 3 * $p4) - 0.00043 * $s($l1 - $p3)
                                                                                                                          + 0.00041   * $s(5 * ($l2 - $l3)) + 0.00041 * $s($p4 - $PI) + 0.00032 * $s($o2 - $o3) + 0.00032 * $s(2 * ($l3 - $G - $PI));

        $sum3 = 0.16490 * $s($l3 - $p3)                                                                                                                                  + 0.09081 * $s($l3 - $p4) - 0.06907 * $s($l2 - $l3) + 0.03784 * $s($p3 - $p4)
                                                                                                                                                                         + 0.01846   * $s(2 * ($l3 - $l4))                     - 0.01340 * $s($G) - 0.01014 * $s(2 * ($psi - $PI)) + 0.00704 * $s($l2 - 2 * $l3 + $p3)
                                                                  - 0.00620   * $s($l2 - 2 * $l3 + $p2) - 0.00541 * $s($l3 - $l4)                                        + 0.00381 * $s($l2 - 2 * $l3 + $p4)
                                                                                                                                                                         + 0.00235   * $s($psi - $o3) + 0.00198 * $s($psi - $o4) + 0.00176 * $s($phi) + 0.00130 * $s(3 * ($l3 - $l4))
                                                                                                                                                                         + 0.00125   * $s($l1 - $l3)                                                    - 0.00119 * $s(5 * $G_ - 2 * $G + 52.225) + 0.00109 * $s($l1 - $l2)
                                                                                           - 0.00100   * $s(3 * $l3 - 7 * $l4 + 4 * $p4)                                 + 0.00091 * $s($o3 - $o4) + 0.00080 * $s(3 * $l3 - 7 * $l4 + $p3 + 3 * $p4)
                                                                                           - 0.00075   * $s(2 * $l2 - 3 * $l3 + $p3)                                     + 0.00072 * $s($p1 + $p3 - 2 * $PI - 2 * $G) + 0.00069 * $s($p4 - $PI)
                                                                                           - 0.00058   * $s(2 * $l3 - 3 * $l4 + $p4) - 0.00057 * $s($l3 - 2 * $l4 + $p4) + 0.00056 * $s($l3 + $p3 - 2 * $PI - 2 * $G)
                                                                                           - 0.00052   * $s($l2 - 2 * $l3 + $p1) - 0.00050 * $s($p2 - $p3)               + 0.00048 * $s($l3 - 2 * $l4 + $p3)
                                                                                           - 0.00045   * $s(2 * $l2 - 3 * $l3 + $p4) - 0.00041 * $s($p2 - $p4) - 0.00038 * $s(2 * $G)
                                                                                           - 0.00037   * $s($p3 - $p4 + $o3 - $o4) - 0.00032 * $s(3 * $l3 - 7 * $l4 + 2 * $p3 + 2 * $p4)
                                        + 0.00030   * $s(4 * ($l3 - $l4)) + 0.00029 * $s($l3 + $p4 - 2 * $PI - 2 * $G) - 0.00028 * $s($o3 + $psi - 2 * $PI - 2 * $G)
                                        + 0.00026   * $s($l3 - $PI - $G) + 0.00024 * $s($l2 - 3 * $l3 + 2 * $l4) + 0.00021 * $s(2 * ($l3 - $PI - $G))
            - 0.00021   * $s($l3 - $p2) + 0.00017 * $s(2 * ($l3 - $p3));

        $sum4 = 0.84287 * $s($l4 - $p4)                                                                                 + 0.03431 * $s($p4 - $p3) - 0.03305 * $s(2 * ($psi - $PI)) - 0.03211 * $s($G)
                                                                  - 0.01862   * $s($l4 - $p3)                           + 0.01186 * $s($psi - $o4) + 0.00623 * $s($l4 + $p4 - 2 * $G - 2 * $PI)
                                                                                                                        + 0.00387   * $s(2 * ($l4 - $p4))                     - 0.00284 * $s(5 * $G_ - 2 * $G + 52.225) - 0.00234 * $s(2 * ($psi - $p4))
                                                                  - 0.00223   * $s($l3 - $l4) - 0.00208 * $s($l4 - $PI) + 0.00178 * $s($psi + $o4 - 2 * $p4) + 0.00134 * $s($p4 - $PI)
                                                                                                                        + 0.00125   * $s(2 * ($l4 - $G - $PI))                - 0.00117 * $s(2 * $G) - 0.00112 * $s(2 * ($l3 - $l4))
                                                                                                                        + 0.00107   * $s(3 * $l3 - 7 * $l4 + 4 * $p4) + 0.00102 * $s($l4 - $G - $PI) + 0.00096 * $s(2 * $l4 - $psi - $o4)
                                                                                                                        + 0.00087   * $s(2 * ($psi - $o4)) - 0.00085 * $s(3 * $l3 - 7 * $l4 + $p3 + 3 * $p4) + 0.00085 * $s($l3 - 2 * $l4 + $p4)
                                               - 0.00081   * $s(2 * ($l4 - $psi))                                       + 0.00071 * $s($l4 + $p4 - 2 * $PI - 3 * $G) + 0.00061 * $s($l1 - $l4)
                                               - 0.00056   * $s($psi - $o3) - 0.00054 * $s($l3 - 2 * $l4 + $p3)         + 0.00051 * $s($l2 - $l4)
                                                                                                                        + 0.00042   * $s(2 * ($psi - $G - $PI)) + 0.00039 * $s(2 * ($p4 - $o4)) + 0.00036 * $s($psi + $PI - $p4 - $o4)
                                                                                                                        + 0.00035   * $s(2 * $G_ - $G + 188.37) - 0.00035 * $s($l4 - $p4 + 2 * $PI - 2 * $psi) - 0.00032 * $s($l4 + $p4 - 2 * $PI - $G)
                                                                                                                        + 0.00030   * $s(2 * $G_ - 2 * $G + 149.15) + 0.00029 * $s(3 * $l3 - 7 * $l4 + 2 * $p3 + 2 * $p4)
                                                                                                                        + 0.00028   * $s($l4 - $p4 + 2 * $psi - 2 * $PI) - 0.00028 * $s(2 * ($l4 - $o4)) - 0.00027 * $s($p3 - $p4 + $o3 - $o4)
                                                             - 0.00026   * $s(5 * $G_ - 3 * $G + 188.37)                + 0.00025 * $s($o4 - $o3) - 0.00025 * $s($l2 - 3 * $l3 + 2 * $l4)
                                                             - 0.00023   * $s(3 * ($l3 - $l4))                          + 0.00021 * $s(2 * $l4 - 2 * $PI - 3 * $G) - 0.00021 * $s(2 * $l3 - 3 * $l4 + $p4)
                                                                                                                        + 0.00019   * $s($l4 - $p4 - $G)                 - 0.00019 * $s(2 * $l4 - $p3 - $p4) - 0.00018 * $s($l4 - $p4 + $G)
                                                             - 0.00016   * $s($l4 + $p3 - 2 * $PI - 2 * $G);

        $L1 = $l1 + $sum1;
        $L2 = $l2 + $sum2;
        $L3 = $l3 + $sum3;
        $L4 = $l4 + $sum4;

        // The latitudes above Jupiter's equator
        $B1 = atan(0.0006393 * $s($L1 - $o1) + 0.0001825 * $s($L1 - $o2) + 0.0000329 * $s($L1 - $o3) - 0.0000311 * $s($L1 - $psi)
                                             + 0.0000093      * $s($L1 - $o4) + 0.0000075 * $s(3 * $L1 - 4 * $l2 - 1.9927 * $sum1 + $o2) + 0.0000046 * $s($L1 + $psi - 2 * $PI - 2 * $G));
        $B2 = atan(0.0081004 * $s($L2 - $o2) + 0.0004512 * $s($L2 - $o3) - 0.0003284 * $s($L2 - $psi) + 0.0001160 * $s($L2 - $o4)
                                             + 0.0000272      * $s($l1 - 2 * $l3 + 1.0146 * $sum2 + $o2)  - 0.0000144 * $s($L2 - $o1) + 0.0000143 * $s($L2 + $psi - 2 * $PI - 2 * $G)
                                             + 0.0000035      * $s($L2 - $psi + $G)                       - 0.0000028 * $s($l1 - 2 * $l3 + 1.0146 * $sum2 + $o3));
        $B3 = atan(0.0032402 * $s($L3 - $o3)                                                       - 0.0016911 * $s($L3 - $psi)                + 0.0006847 * $s($L3 - $o4) - 0.0002797 * $s($L3 - $o2)
                                                                                                                                               + 0.0000321      * $s($L3 + $psi - 2 * $PI - 2 * $G) + 0.0000051 * $s($L3 - $psi + $G) - 0.0000045 * $s($L3 - $psi - $G)
                                                                                                   - 0.0000045      * $s($L3 + $psi - 2 * $PI) + 0.0000037 * $s($L3 + $psi - 2 * $PI - 3 * $G)
                                                                                                                                               + 0.0000030      * $s(2 * $l2 - 3 * $L3 + 4.03 * $sum3 + $o2)                          - 0.0000021 * $s(2 * $l2 - 3 * $L3 + 4.03 * $sum3 + $o3));
        $B4 = atan(-0.0076579 * $s($L4 - $psi) + 0.0044134 * $s($L4 - $o4)                          - 0.0005112 * $s($L4 - $o3)
                                               + 0.0000773       * $s($L4 + $psi - 2 * $PI - 2 * $G) + 0.0000104 * $s($L4 - $psi + $G) - 0.0000102 * $s($L4 - $psi - $G)
                                               + 0.0000088       * $s($L4 + $psi - 2 * $PI - 3 * $G)                                   - 0.0000038 * $s($L4 + $psi - 2 * $PI - $G));

        // The radius vectors in Jupiter radii
        $R1 = 5.90569 * (1                             - 0.0041339 * $c(2 * ($l1 - $l2)) - 0.0000387 * $c($l1 - $p3) - 0.0000214 * $c($l1 - $p4)
            + 0.0000170                * $c($l1 - $l2) - 0.0000131 * $c(4 * ($l1 - $l2)) + 0.0000106 * $c($l1 - $l3) - 0.0000066 * $c($l1 + $p3 - 2 * $PI - 2 * $G));
        $R2 = 9.39657 * (1 + 0.0093848 * $c($l1 - $l2)                             - 0.0003116 * $c($l2 - $p3) - 0.0001744 * $c($l2 - $p4) - 0.0001442 * $c($l2 - $p2)
                           + 0.0000553                * $c($l2 - $l3) + 0.0000523 * $c($l1 - $l3) - 0.0000290 * $c(2 * ($l1 - $l2)) + 0.0000164 * $c(2 * ($l2 - $o2))
                           + 0.0000107                * $c($l1 - 2 * $l3 + $p3)                   - 0.0000102 * $c($l2 - $p1) - 0.0000091 * $c(2 * ($l1 - $l3)));
        $R3 = 14.98832 * (1                                           - 0.0014388 * $c($l3 - $p3) - 0.0007919 * $c($l3 - $p4) + 0.0006342 * $c($l2 - $l3) - 0.0001761 * $c(2 * ($l3 - $l4))
                                                                                                                              + 0.0000294                 * $c($l3 - $l4)               - 0.0000156 * $c(3 * ($l3 - $l4)) + 0.0000156 * $c($l1 - $l3) - 0.0000153 * $c($l1 - $l2)
                                                                                                                              + 0.0000070                 * $c(2 * $l2 - 3 * $l3 + $p3) - 0.0000051 * $c($l3 + $p3 - 2 * $PI - 2 * $G));
        $R4 = 26.36273 * (1                                   - 0.0073546 * $c($l4 - $p4)                                                                      + 0.0001621 * $c($l4 - $p3) + 0.0000974 * $c($l3 - $l4)
                                                              - 0.0000543                 * $c($l4 + $p4 - 2 * $PI - 2 * $G) - 0.0000271 * $c(2 * ($l4 - $p4)) + 0.0000182 * $c($l4 - $PI)
                                                                                                                                                               + 0.0000177                 * $c(2 * ($l3 - $l4)) - 0.0000167 * $c(2 * $l4 - $psi - $o4) + 0.0000167 * $c($psi - $o4)
                                                              - 0.0000155                 * $c(2 * ($l4 - $PI - $G))                                           + 0.0000142 * $c(2 * ($l4 - $psi)) + 0.0000105 * $c($l1 - $l4)
                                                                                                                                                               + 0.0000092                 * $c($l2 - $l4)       - 0.0000089 * $c($l4 - $PI - $G) - 0.0000062 * $c($l4 + $p4 - 2 * $PI - 3 * $G)
                                                                                                                                                               + 0.0000048                 * $c(2 * ($l4 - $o4)));

        // The precession from B1950 to the date, added to the longitudes and to psi
        $T0 = ($jde - 2433282.423) / 36525.0;
        $P  = 1.3966626 * $T0 + 0.0003088 * $T0 * $T0;
        $psi += $P;

        // Jupiter's orbit on the ecliptic of the date and its equator on its orbit
        $T     = ($jde - $g['tau'] - 2451545.0) / 36525.0;
        $node  = 100.464407                  + 1.0209774 * $T + 0.00040315 * $T * $T + 0.000000404 * $T ** 3;
        $incl  = 1.303267 - 0.0054965   * $T + 0.00000466 * $T * $T - 0.000000002 * $T                  ** 3;
        $equat = 3.120262                    + 0.0006      * ($jde - 2415020.5) / 36525.0;

        $vectors = ['pole' => [0.0, 0.0, 1.0]];
        foreach (['io' => [$L1, $B1, $R1], 'europa' => [$L2, $B2, $R2], 'ganymede' => [$L3, $B3, $R3], 'callisto' => [$L4, $B4, $R4]] as $moon => [$L, $B, $R]) {
            $vectors[$moon] = [$R * $c($L + $P - $psi) * cos($B), $R * $s($L + $P - $psi) * cos($B), $R * sin($B)];
        }
        foreach ($vectors as $moon => [$x, $y, $z]) {
            // to Jupiter's orbit, to its ascending node, to the ecliptic, to the equinox
            [$y, $z]        = [$y * $c($equat) - $z * $s($equat), $y * $s($equat) + $z * $c($equat)];
            [$x, $y]        = [$x * $c($psi - $node) - $y * $s($psi - $node), $x * $s($psi - $node) + $y * $c($psi - $node)];
            [$y, $z]        = [$y * $c($incl) - $z * $s($incl), $y * $s($incl) + $z * $c($incl)];
            [$x, $y]        = [$x * $c($node) - $y * $s($node), $x * $s($node) + $y * $c($node)];
            $vectors[$moon] = [$x, $y, $z];
        }

        $radii = ['io' => [$R1, 17295], 'europa' => [$R2, 21819], 'ganymede' => [$R3, 27558], 'callisto' => [$R4, 36548]];

        return [
            'earth'    => self::project($vectors, $g['earth'], $radii, 2095.0),
            'sun'      => self::project($vectors, $g['sun'], [], 2095.0),
            'distance' => $g['earth']['distance'],
            'cm2'      => self::centralMeridian($g),
        ];
    }

    /**
     * The moons of SATURN_MOONS seen from the Earth and the Sun, Saturn's
     * distance (AU) and the tilt of its rings (degrees, positive when their
     * northern face is turned towards the Earth).
     *
     * @return array{earth: array<string, array{x: float, y: float, z: float}>, sun: array<string, array{x: float, y: float, z: float}>, distance: float, ring_tilt: float}
     */
    public static function saturn(int $timestamp): array
    {
        $g = self::geometry('saturn', $timestamp);
        $q = new SaturnMoonTheory($g['jde'] - $g['tau']);

        $vectors = ['pole' => [0.0, 0.0, 1.0]];
        $radii   = [];
        foreach (self::SATURN_MOONS as $moon) {
            ['lambda' => $lambda, 'r' => $r, 'gamma' => $gamma, 'omega' => $omega] = $q->{$moon}();
            $u                                                                     = $lambda - $omega;
            $w                                                                     = $omega  - deg2rad(168.8112);
            $vectors[$moon]                                                        = [
                $r * (cos($u) * cos($w) - sin($u) * cos($gamma) * sin($w)),
                $r * (sin($u) * cos($w) * cos($gamma) + cos($u) * sin($w)),
                $r * sin($u) * sin($gamma),
            ];
            $radii[$moon] = [$r, SaturnMoonTheory::K[$moon]];
        }
        foreach ($vectors as $moon => [$x, $y, $z]) {
            // to the ecliptic and equinox of B1950
            [$y, $z]        = [$q->c1 * $y - $q->s1 * $z, $q->s1 * $y + $q->c1 * $z];
            [$x, $y]        = [$q->c2 * $x - $q->s2 * $y, $q->s2 * $x + $q->c2 * $y];
            $vectors[$moon] = [$x, $y, $z];
        }

        // The theory is referred to B1950: the directions of the date are precessed back.
        $T0    = ($g['jde'] - 2433282.423) / 36525.0;
        $P     = 1.3966626 * $T0                    + 0.0003088 * $T0 * $T0;
        $earth = ['lon' => $g['earth']['lon'] - $P] + $g['earth'];
        $sun   = ['lon' => $g['sun']['lon'] - $P]   + $g['sun'];

        $projected = self::project($vectors, $earth, $radii, 2475.0, true);
        $pole      = $projected['pole'];
        unset($projected['pole']);

        return [
            'earth'     => $projected,
            'sun'       => self::project($vectors, $sun, [], 2475.0),
            'distance'  => $g['earth']['distance'],
            'ring_tilt' => rad2deg(asin(max(-1.0, min(1.0, -$pole['z'])))),
        ];
    }

    /**
     * Whether a point of the plane of the sky (X, Y in equatorial radii) lies
     * within the planet's disk: below 0 inside, above 0 outside.
     */
    public static function disk(string $planet, float $x, float $y): float
    {
        return $x * $x + ($y / self::FLATTENING[$planet]) ** 2 - 1.0;
    }

    /**
     * The geometry of the planet at the moment, interpolated between the
     * whole hours: the directions (ecliptic of the date) from the Earth and
     * from the Sun, the distances, the light time and the JDE.
     *
     * @return array{jde: float, tau: float, earth: array{lon: float, lat: float, distance: float, x: float, y: float, z: float}, sun: array{lon: float, lat: float, distance: float}}
     */
    private static function geometry(string $planet, int $timestamp): array
    {
        $hour = intdiv($timestamp, 3600) * 3600;
        $a    = self::hour($planet, $hour);
        $b    = self::hour($planet, $hour + 3600);
        $f    = ($timestamp - $hour) / 3600.0;
        $mix  = fn (array $p, array $q) => [$p[0] + ($q[0] - $p[0]) * $f, $p[1] + ($q[1] - $p[1]) * $f, $p[2] + ($q[2] - $p[2]) * $f];

        $view  = $mix($a['view'], $b['view']);
        $helio = $mix($a['helio'], $b['helio']);

        return [
            'jde'   => SkyPositions::jd($timestamp) + SkyPositions::deltaT($timestamp) / 86400.0,
            'tau'   => $a['tau']                    + ($b['tau'] - $a['tau']) * $f,
            'earth' => self::spherical($view)       + ['x' => $view[0], 'y' => $view[1], 'z' => $view[2]],
            'sun'   => self::spherical($helio),
        ];
    }

    /**
     * @return array{view: array{0: float, 1: float, 2: float}, helio: array{0: float, 1: float, 2: float}, tau: float}
     */
    private static function hour(string $planet, int $hour): array
    {
        $key = $planet . ':' . $hour;
        if (! isset(self::$hours[$key])) {
            if (count(self::$hours) > 2000) {
                self::$hours = [];
            }
            $v                 = SkyPositions::planetVectors($planet, $hour);
            self::$hours[$key] = [
                'view'  => [$v['planet'][0] - $v['earth'][0], $v['planet'][1] - $v['earth'][1], $v['planet'][2] - $v['earth'][2]],
                'helio' => $v['planet'],
                'tau'   => $v['tau'],
            ];
        }

        return self::$hours[$key];
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $v
     * @return array{lon: float, lat: float, distance: float}
     */
    private static function spherical(array $v): array
    {
        return [
            'lon'      => SkyPositions::wrap(rad2deg(atan2($v[1], $v[0]))),
            'lat'      => rad2deg(atan2($v[2], sqrt($v[0] * $v[0] + $v[1] * $v[1]))),
            'distance' => sqrt($v[0] * $v[0] + $v[1] * $v[1] + $v[2] * $v[2]),
        ];
    }

    /**
     * The moons' vectors (ecliptic coordinates centred on the planet, with
     * the planet's pole as 'pole') seen from a direction: rotated so the
     * planet's equator is along X (Meeus 44 and 46), with the corrections for
     * the differential light time (when $radii gives each moon's radius
     * vector and K) and for the perspective.
     *
     * @param  array<string, array{0: float, 1: float, 2: float}>  $vectors
     * @param  array{lon: float, lat: float, distance: float}  $from
     * @param  array<string, array{0: float, 1: int}>  $radii
     * @return array<string, array{x: float, y: float, z: float}>
     */
    private static function project(array $vectors, array $from, array $radii, float $perspective, bool $withPole = false): array
    {
        $l  = deg2rad($from['lon']);
        $b  = deg2rad($from['lat']);
        $in = [];
        foreach ($vectors as $moon => [$x, $y, $z]) {
            $a         = $x * sin($l) - $y * cos($l);
            $y         = $x * cos($l) + $y * sin($l);
            $in[$moon] = [$a, $z * sin($b) + $y * cos($b), $z * cos($b) - $y * sin($b)];
        }
        $d = atan2($in['pole'][0], $in['pole'][2]);

        $result = [];
        foreach ($in as $moon => [$a, $depth, $c]) {
            $x = $a * cos($d) - $c * sin($d);
            $y = $a * sin($d) + $c * cos($d);
            if ($moon === 'pole') {
                if ($withPole) {
                    $result[$moon] = ['x' => $x, 'y' => $y, 'z' => $depth];
                }

                continue;
            }
            if (isset($radii[$moon])) {
                [$r, $k] = $radii[$moon];
                $x += abs($depth) / $k * sqrt(max(0.0, 1 - ($x / $r) ** 2));
            }
            $w             = $from['distance'] / ($from['distance'] + $depth / $perspective);
            $result[$moon] = ['x' => $x * $w, 'y' => $y * $w, 'z' => $depth];
        }

        return $result;
    }

    /**
     * The System II longitude of Jupiter's central meridian (Meeus chapter
     * 42, without the correction for the phase): the Great Red Spot crosses
     * it when its longitude is this.
     *
     * @param  array<string, mixed>  $g  geometry()
     */
    private static function centralMeridian(array $g): float
    {
        $d  = $g['jde'] - 2433282.5;
        $t1 = $d / 36525.0;
        $a0 = deg2rad(268.00 + 0.1061 * $t1);
        $d0 = deg2rad(64.50 - 0.0164 * $t1);

        $e                                = deg2rad(SkyPositions::obliquity(SkyPositions::timestampFromJd($g['jde'])));
        ['x' => $x, 'y' => $y, 'z' => $z] = $g['earth'];
        $u                                = $y * cos($e) - $z * sin($e);
        $v                                = $y * sin($e) + $z * cos($e);
        $alpha                            = atan2($u, $x);
        $delta                            = atan($v / sqrt($x * $x + $u * $u));
        $zeta                             = atan2(sin($d0) * cos($delta) * cos($a0 - $alpha) - sin($delta) * cos($d0), cos($delta) * sin($a0 - $alpha));

        return SkyPositions::wrap(16.838 + 870.27003539 * $d - rad2deg($zeta) - 5.02626 * $g['earth']['distance']);
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
