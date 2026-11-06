<?php

namespace App\Services\Comets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\NearParabolic;

/**
 * A comet in a clearly hyperbolic orbit, such as the interstellar 3I/ATLAS
 * (e = 6.1).
 *
 * The near-parabolic solution of Meeus chapter 35, which the astronomy
 * library's NearParabolic uses, is a series in (1 - e) / (1 + e) that only
 * converges for an eccentricity near 1: for 3I/ATLAS it gave NaN for the
 * position and magnitude from a few months away from the perihelion. This
 * class solves the hyperbolic Kepler equation e sinh H - H = M instead,
 * which holds for every e > 1, and keeps everything else of NearParabolic
 * (the position as seen from the Earth, the parallax, the photometry), which
 * all goes through _heliocentricRectangularCoordinates().
 */
class HyperbolicOrbit extends NearParabolic
{
    /** The Gaussian gravitational constant. */
    private const K = 0.01720209895;

    private float $q;

    private float $e;

    private float $i;

    private float $omega;

    private float $node;

    private Carbon $perihelion;

    public function setOrbitalElements(float $q, float $e, float $i, float $omega, float $longitude_ascending_node, Carbon $perihelion_date): void
    {
        if ($e <= 1.0) {
            throw new \InvalidArgumentException('A hyperbolic orbit needs an eccentricity above 1.');
        }
        parent::setOrbitalElements($q, $e, $i, $omega, $longitude_ascending_node, $perihelion_date);
        $this->q = $q;
        $this->e = $e;
        $this->i = $i;
        $this->omega = $omega;
        $this->node = $longitude_ascending_node;
        $this->perihelion = $perihelion_date;
    }

    /**
     * The true anomaly (degrees) and the distance to the Sun (AU) a number of
     * days from the perihelion.
     *
     * @return array{0: float, 1: float}
     */
    public function anomalyAndDistance(float $days): array
    {
        $e = $this->e;
        $a = $this->q / ($e - 1.0);
        $M = self::K / ($a ** 1.5) * $days;

        // Newton on e sinh H - H - M = 0, from a start that is close for small
        // and for large mean anomalies.
        $H = abs($M) < 6.0 * $e
            ? asinh($M / $e)
            : ($M < 0 ? -1 : 1) * log(2.0 * abs($M) / $e + 1.8);
        for ($n = 0; $n < 100; $n++) {
            $step = ($e * sinh($H) - $H - $M) / ($e * cosh($H) - 1.0);
            $H -= $step;
            if (abs($step) < 1e-12 * max(1.0, abs($H))) {
                break;
            }
        }

        $v = 2.0 * atan(sqrt(($e + 1.0) / ($e - 1.0)) * tanh($H / 2.0));
        $r = $a * ($e * cosh($H) - 1.0);

        return [rad2deg($v), $r];
    }

    /**
     * The heliocentric rectangular coordinates, in AU, referred to the mean
     * equator and equinox of J2000, as NearParabolic gives them (Meeus
     * chapter 33), from the solution of the hyperbolic Kepler equation.
     */
    protected function _heliocentricRectangularCoordinates(Carbon $date): array
    {
        $days = $this->perihelion->diffInSeconds($date, false) / 86400.0;
        [$v, $r] = $this->anomalyAndDistance($days);

        $node = deg2rad($this->node);
        $i = deg2rad($this->i);
        // cos and sin of the obliquity of the ecliptic of J2000
        $cosEps = 0.917482062;
        $sinEps = 0.397777156;

        $F = cos($node);
        $G = sin($node) * $cosEps;
        $H = sin($node) * $sinEps;
        $P = -sin($node) * cos($i);
        $Q = cos($node) * cos($i) * $cosEps - sin($i) * $sinEps;
        $R = cos($node) * cos($i) * $sinEps + sin($i) * $cosEps;

        $A = rad2deg(atan2($F, $P));
        $B = rad2deg(atan2($G, $Q));
        $C = rad2deg(atan2($H, $R));

        return [
            $r * sqrt($F ** 2 + $P ** 2) * sin(deg2rad($A + $this->omega + $v)),
            $r * sqrt($G ** 2 + $Q ** 2) * sin(deg2rad($B + $this->omega + $v)),
            $r * sqrt($H ** 2 + $R ** 2) * sin(deg2rad($C + $this->omega + $v)),
        ];
    }
}
