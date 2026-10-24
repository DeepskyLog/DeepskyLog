<?php

namespace App\Services\Targets;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\Elliptic;

/**
 * (134340) Pluto: on its elliptic orbit as the other asteroids, but with the
 * magnitude of Mallama & Hilton (2018), V = -1.01 + 5 log(r delta) + 0.041 alpha,
 * which JPL Horizons uses as well. The H-G values of the asteroid elements
 * (H = -0.55) make Pluto about 0.6 magnitude too faint.
 */
class Pluto extends Elliptic
{
    public const NUMBER = 134340;

    public function magnitude(Carbon $date): float
    {
        [$r, $delta, $alpha] = $this->_photometricGeometry($date);

        return -1.01 + 5 * log10($r * $delta) + 0.041 * $alpha;
    }
}
