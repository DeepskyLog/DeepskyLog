<?php

namespace App\Services;

/**
 * The light of the twilight in the sky: the altitude of the Sun at a moment
 * and the brightness the sunlight scattered by the upper atmosphere adds to
 * the sky at the zenith.
 *
 * The brightness is the fit of Patat, Ugolnikov and Postylyakov (2006, A&A
 * 455, 385) to the zenith sky in V at Paranal, a0 + a1 (ζ − 95) + a2 (ζ − 95)²
 * for a Sun at zenith distance ζ of 95° to 105°. Their measurements include
 * the night sky, which they reach at ζ = 105°–106°, so the light of the
 * twilight alone is the fit less the dark sky of Paranal (Patat 2003): it
 * adds 2 magnitudes to a dark site at the end of nautical twilight, a few
 * tenths with the Sun 15° below the horizon and nothing from 16°. The sky
 * towards the Sun and near the horizon is brighter than the zenith; that is
 * not modelled.
 */
class TwilightSky
{
    /** The altitude of the Sun at the end of astronomical twilight. */
    public const ASTRONOMICAL = -18.0;

    /** The fit in V (mag/arcsec²) of Patat et al. (2006), Table 1. */
    private const A0 = 11.84;

    private const A1 = 1.518;

    private const A2 = -0.057;

    /** The dark night sky in V at the zenith of Paranal (Patat 2003). */
    private const PARANAL_DARK_SKY = 21.61;

    /**
     * The light (nanolamberts) the twilight adds to the zenith sky with the
     * Sun at this altitude: 0 once the fit reaches the dark sky, and the
     * value at ζ = 95° for a higher Sun, which hides everything already.
     */
    public static function light(float $sunAltitude): float
    {
        $x   = min(max(0.0, 90.0 - $sunAltitude - 95.0), -self::A1 / (2 * self::A2));
        $sky = self::A0 + self::A1 * $x + self::A2 * $x * $x;
        if ($sky >= self::PARANAL_DARK_SKY) {
            return 0.0;
        }

        return MoonlightSky::toNanoLambert($sky) - MoonlightSky::toNanoLambert(self::PARANAL_DARK_SKY);
    }

    /**
     * The altitude of the Sun in degrees (without refraction) at the moment
     * (a timestamp) at the location (longitude east positive), with the low
     * accuracy formulae of Meeus (chapter 25, about 0.01°).
     */
    public static function sunAltitude(int $timestamp, float $latitude, float $longitude): float
    {
        $sun       = self::sun($timestamp);
        $days      = $timestamp / 86400.0 + 2440587.5 - 2451545.0;
        $hourAngle = deg2rad(280.46061837 + 360.98564736629 * $days + $longitude - $sun['ra']);
        $lat       = deg2rad($latitude);
        $dec       = deg2rad($sun['dec']);

        return rad2deg(asin(max(-1.0, min(1.0, sin($lat) * sin($dec) + cos($lat) * cos($dec) * cos($hourAngle)))));
    }

    /**
     * The apparent right ascension and declination of the Sun (degrees) at
     * the moment (Meeus, chapter 25, low accuracy).
     *
     * @return array{ra: float, dec: float}
     */
    public static function sun(int $timestamp): array
    {
        $t         = ($timestamp / 86400.0 + 2440587.5 - 2451545.0) / 36525.0;
        $meanLong  = 280.46646 + 36000.76983 * $t;
        $anomaly   = deg2rad(357.52911 + 35999.05029 * $t);
        $center    = (1.914602 - 0.004817 * $t) * sin($anomaly) + (0.019993 - 0.000101 * $t) * sin(2 * $anomaly) + 0.000289 * sin(3 * $anomaly);
        $node      = deg2rad(125.04 - 1934.136 * $t);
        $longitude = deg2rad($meanLong + $center - 0.00569 - 0.00478 * sin($node));
        $obliquity = deg2rad(23.439291 - 0.0130042 * $t + 0.00256 * cos($node));

        return [
            'ra'  => fmod(rad2deg(atan2(cos($obliquity) * sin($longitude), cos($longitude))) + 360.0, 360.0),
            'dec' => rad2deg(asin(sin($obliquity) * sin($longitude))),
        ];
    }
}
