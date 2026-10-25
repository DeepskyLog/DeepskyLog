<?php

namespace App\Services;

/**
 * The dimming of an object by the atmosphere: the longer the path of its
 * light through the air (the air mass), the fainter it gets. The sky
 * brightness and limiting magnitude of a location are measured at the
 * zenith, so the loss is counted against the zenith (air mass 1).
 */
class AtmosphericExtinction
{
    /** Atmospheric extinction in the visual, magnitudes per air mass. */
    public const COEFFICIENT = 0.25;

    /**
     * The air mass at this altitude (Kasten & Young, 1989), which stays
     * finite down to the horizon (about 38); INF below it.
     */
    public static function airmass(float $altDeg): float
    {
        if ($altDeg <= 0) {
            return INF;
        }

        return 1.0 / (sin(deg2rad($altDeg)) + 0.50572 * pow($altDeg + 6.07995, -1.6364));
    }

    /**
     * Magnitudes an object loses at this altitude, against the zenith. Below
     * the horizon an object is never seen (INF).
     */
    public static function magnitudes(float $altDeg): float
    {
        if ($altDeg <= 0) {
            return INF;
        }

        return self::COEFFICIENT * (self::airmass($altDeg) - 1.0);
    }

    /**
     * The highest altitude an object reaches at this latitude, when it
     * crosses the meridian: negative for an object that never rises.
     */
    public static function highestAltitude(float $decDeg, float $latDeg): float
    {
        return 90.0 - abs($latDeg - $decDeg);
    }
}
