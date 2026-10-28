<?php

namespace App\Services\Planets;

/**
 * The orbits of the larger moons of Saturn at a moment (Meeus chapter 46),
 * used by PlanetMoons::saturn(): for each moon its longitude, radius vector
 * (Saturn radii), inclination and node on the plane of the ecliptic of B1950
 * turned by 168.8112° and 28.0817° (Saturn's equator). Angles in radians.
 * Mimas, Enceladus and Hyperion are left out: too faint for most telescopes.
 */
class SaturnMoonTheory
{
    /** The factors of the differential light time correction (Meeus table 46.A). */
    public const K = ['tethys' => 26382, 'dione' => 29876, 'rhea' => 35313, 'titan' => 53800, 'iapetus' => 91820];

    public float $s1;

    public float $c1;

    public float $s2;

    public float $c2;

    private float $t1;

    private float $t2;

    private float $t4;

    private float $t5;

    private float $t7;

    private float $t10;

    private float $t11;

    private float $W0;

    private float $W1;

    private float $W2;

    private float $W3;

    private float $W4;

    private float $W5;

    private float $W6;

    private float $W7;

    private float $W8;

    private float $e1;

    /** @param  float  $jde  the moment less the light time */
    public function __construct(float $jde)
    {
        $d         = M_PI / 180.0;
        $this->t1  = $jde - 2411093.0;
        $this->t2  = $this->t1            / 365.25;
        $t3        = ($jde - 2433282.423) / 365.25 + 1950.0;
        $this->t4  = $jde - 2411368.0;
        $this->t5  = $this->t4 / 365.25;
        $t6        = $jde - 2415020.0;
        $this->t7  = $t6 / 36525.0;
        $this->t10 = $jde - 2409786.0;
        $this->t11 = $this->t10 / 36525.0;

        $this->W0 = 5.095                              * $d * ($t3 - 1866.39);
        $this->W1 = (74.4 + 32.39 * $this->t2)         * $d;
        $this->W2 = (134.3 + 92.62 * $this->t2)        * $d;
        $this->W3 = (42.0 - 0.5118 * $this->t5)        * $d;
        $this->W4 = (276.59 + 0.5118 * $this->t5)      * $d;
        $this->W5 = (267.2635 + 1222.1136 * $this->t7) * $d;
        $this->W6 = (175.4762 + 1221.5515 * $this->t7) * $d;
        $this->W7 = (2.4891 + 0.002435 * $this->t7)    * $d;
        $this->W8 = (113.35 - 0.2597 * $this->t7)      * $d;
        $this->s1 = sin(28.0817 * $d);
        $this->c1 = cos(28.0817 * $d);
        $this->s2 = sin(168.8112 * $d);
        $this->c2 = cos(168.8112 * $d);
        $this->e1 = 0.05589 - 0.000346 * $this->t7;
    }

    /** @return array{lambda: float, r: float, gamma: float, omega: float} */
    public function tethys(): array
    {
        $d = M_PI / 180.0;

        return [
            'lambda' => (285.306 + 190.69791226 * $this->t1 + 2.063 * sin($this->W0) + 0.03409 * sin(3 * $this->W0) + 0.001015 * sin(5 * $this->W0)) * $d,
            'r'      => 4.880998,
            'gamma'  => 1.0976                         * $d,
            'omega'  => (111.33 - 72.2441 * $this->t2) * $d,
        ];
    }

    /** @return array{lambda: float, r: float, gamma: float, omega: float} */
    public function dione(): array
    {
        $d = M_PI / 180.0;
        $L = (254.712 + 131.53493193 * $this->t1 - 0.0215 * sin($this->W1) - 0.01733 * sin($this->W2)) * $d;
        $p = (174.8 + 30.82 * $this->t2)                                                               * $d;
        $M = $L - $p;
        $C = (0.24717 * sin($M) + 0.00033 * sin(2 * $M)) * $d;

        return [
            'lambda' => $L + $C,
            'r'      => 6.24871 / (1 + 0.002157 * cos($M + $C)),
            'gamma'  => 0.0139                      * $d,
            'omega'  => (232.0 - 30.27 * $this->t2) * $d,
        ];
    }

    /** @return array{lambda: float, r: float, gamma: float, omega: float} */
    public function rhea(): array
    {
        $d      = M_PI / 180.0;
        $pPrime = (342.7 + 10.057 * $this->t2) * $d;
        $a1     = 0.000265                     * sin($pPrime) + 0.001 * sin($this->W4);
        $a2     = 0.000265                     * cos($pPrime) + 0.001 * cos($this->W4);
        $e      = hypot($a1, $a2);
        $p      = atan2($a1, $a2);
        $N      = (345.0 - 10.057 * $this->t2)                             * $d;
        $lambda = (359.244 + 79.6900472 * $this->t1 + 0.086754 * sin($N))  * $d;
        $i      = (28.0362 + 0.346898 * cos($N) + 0.0193 * cos($this->W3)) * $d;
        $omega  = (168.8034 + 0.736936 * sin($N) + 0.041 * sin($this->W3)) * $d;

        return $this->subr($lambda, $p, $e, 8.725924, $omega, $i);
    }

    /** @return array{lambda: float, r: float, gamma: float, omega: float} */
    public function titan(): array
    {
        $d      = M_PI / 180.0;
        $L      = (261.1582 + 22.57697855 * $this->t4 + 0.074025 * sin($this->W3)) * $d;
        $iPrime = (27.45141 + 0.295999 * cos($this->W3))                           * $d;
        $oPrime = (168.66925 + 0.628808 * sin($this->W3))                          * $d;
        $a1     = sin($this->W7)                                                   * sin($oPrime - $this->W8);
        $a2     = cos($this->W7)                                                   * sin($iPrime) - sin($this->W7) * cos($iPrime) * cos($oPrime - $this->W8);
        $g0     = 102.8623                                                         * $d;
        $psi    = atan2($a1, $a2);
        $s      = hypot($a1, $a2);
        $g      = $this->W4 - $oPrime - $psi;
        $varpi  = 0.0;
        for ($k = 0; $k < 3; $k++) {
            $varpi = $this->W4 + 0.37515 * $d * (sin(2 * $g) - sin(2 * $g0));
            $g     = $varpi - $oPrime - $psi;
        }
        $ePrime = 0.029092 + 0.00019048 * (cos(2 * $g) - cos(2 * $g0));
        $qq     = 2                     * ($this->W5 - $varpi);
        $b1     = sin($iPrime)          * sin($oPrime - $this->W8);
        $b2     = cos($this->W7)        * sin($iPrime) * cos($oPrime - $this->W8) - sin($this->W7) * cos($iPrime);
        $theta  = atan2($b1, $b2)                                      + $this->W8;
        $e      = $ePrime                                              + 0.002778797 * $ePrime * cos($qq);
        $p      = $varpi                                               + 0.159215     * $d * sin($qq);
        $u      = 2                     * $this->W5 - 2 * $theta       + $psi;
        $h      = 0.9375                * $ePrime * $ePrime * sin($qq) + 0.1875 * $s * $s * sin(2 * ($this->W5 - $theta));
        $lambda = $L - 0.254744         * $d * ($this->e1 * sin($this->W6) + 0.75 * $this->e1 * $this->e1 * sin(2 * $this->W6) + $h);
        $i      = $iPrime + 0.031843    * $d * $s * cos($u);
        $omega  = $oPrime + 0.031843    * $d * $s * sin($u) / sin($iPrime);

        return $this->subr($lambda, $p, $e, 20.216193, $omega, $i);
    }

    /** @return array{lambda: float, r: float, gamma: float, omega: float} */
    public function iapetus(): array
    {
        $d      = M_PI / 180.0;
        $L      = (261.1582 + 22.57697855 * $this->t4) * $d;
        $varpiP = (91.796 + 0.562 * $this->t7)         * $d;
        $psi    = (4.367 - 0.195 * $this->t7)          * $d;
        $theta  = (146.819 - 3.198 * $this->t7)        * $d;
        $phi    = (60.47 + 1.521 * $this->t7)          * $d;
        $Phi    = (205.055 - 2.091 * $this->t7)        * $d;
        $ePrime = 0.028298 + 0.001156                  * $this->t11;
        $varpi0 = (352.91 + 11.71 * $this->t11)        * $d;
        $mu     = (76.3852 + 4.53795125 * $this->t10)  * $d;
        $t      = $this->t11;
        $iPrime = (18.4602 - 0.9518 * $t - 0.072 * $t * $t + 0.0054 * $t ** 3) * $d;
        $oPrime = (143.198 - 3.919 * $t + 0.116 * $t * $t + 0.008 * $t ** 3)   * $d;
        $l      = $mu       - $varpi0;
        $g      = $varpi0   - $oPrime - $psi;
        $g1     = $varpi0   - $oPrime - $phi;
        $ls     = $this->W5 - $varpiP;
        $gs     = $varpiP   - $theta;
        $lT     = $L        - $this->W4;
        $gT     = $this->W4 - $Phi;
        $u1     = 2 * ($l + $g - $ls - $gs);
        $u2     = $l  + $g1 - $lT - $gT;
        $u3     = $l  + 2 * ($g - $ls - $gs);
        $u4     = $lT + $gT - $g1;
        $u5     = 2                    * ($ls + $gs);
        $a      = 58.935028                             + 0.004638 * cos($u1) + 0.058222 * cos($u2);
        $e      = $ePrime - 0.0014097  * cos($g1 - $gT) + 0.0003733 * cos($u5 - 2 * $g) + 0.000118 * cos($u3)
                                                        + 0.0002408                * cos($l) + 0.0002849 * cos($l + $u2) + 0.000619 * cos($u4);
        $w = (0.08077 * sin($g1 - $gT) + 0.02139 * sin($u5 - 2 * $g) - 0.00676 * sin($u3) + 0.0138 * sin($l)
                                       + 0.01632 * sin($l + $u2) + 0.03547 * sin($u4)) * $d;
        $p      = $varpi0 + $w / $ePrime;
        $lambda = $mu     + (-0.04299 * sin($u2) - 0.00789 * sin($u1) - 0.06312 * sin($ls) - 0.00295 * sin(2 * $ls)
                                             - 0.02231             * sin($u5) + 0.0065 * sin($u5 + $psi))                                                     * $d;
        $i      = $iPrime + (0.04204 * cos($u5 + $psi) + 0.00235 * cos($l + $g1 + $lT + $gT + $phi) + 0.0036 * cos($u2 + $phi)) * $d;
        $wPrime = (0.04204 * sin($u5 + $psi) + 0.00235 * sin($l + $g1 + $lT + $gT + $phi) + 0.00358 * sin($u2 + $phi))          * $d;
        $omega  = $oPrime + $wPrime / sin($iPrime);

        return $this->subr($lambda, $p, $e, $a, $omega, $i);
    }

    /**
     * An elliptic orbit (longitude, perisaturnium, eccentricity, axis, node
     * and inclination on the ecliptic of B1950) referred to Saturn's equator.
     *
     * @return array{lambda: float, r: float, gamma: float, omega: float}
     */
    private function subr(float $lambda, float $p, float $e, float $a, float $omega, float $i): array
    {
        $M  = $lambda - $p;
        $e2 = $e                                         * $e;
        $e3 = $e2                                        * $e;
        $e4 = $e2                                        * $e2;
        $e5 = $e3                                        * $e2;
        $C  = (2 * $e - 0.25 * $e3 + 0.0520833333 * $e5) * sin($M) + (1.25 * $e2 - 0.458333333 * $e4) * sin(2 * $M)
                                                                   + (1.083333333 * $e3 - 0.671875 * $e5)       * sin(3 * $M) + 1.072917 * $e4 * sin(4 * $M) + 1.142708 * $e5 * sin(5 * $M);
        $g   = $omega - deg2rad(168.8112);
        $a1  = sin($i)   * sin($g);
        $a2  = $this->c1 * sin($i) * cos($g) - $this->s1 * cos($i);
        $u   = atan2($a1, $a2);
        $h   = $this->c1 * sin($i) - $this->s1 * cos($i) * cos($g);
        $psi = atan2($this->s1 * sin($g), $h);

        return [
            'lambda' => $lambda + $C + $u - $g - $psi,
            'r'      => $a * (1 - $e2) / (1 + $e * cos($M + $C)),
            'gamma'  => asin(min(1.0, hypot($a1, $a2))),
            'omega'  => deg2rad(168.8112) + $u,
        ];
    }
}
