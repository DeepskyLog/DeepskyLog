<?php

namespace App\Services;

/**
 * The faintest star visible in an instrument, from the naked-eye limiting
 * magnitude of the sky.
 *
 * Two effects add to the naked-eye limit:
 *
 * - the light gain of the aperture over the pupil of the eye, 5 log(D / p),
 *   less the light lost in the optics (transmission and central
 *   obstruction). When the exit pupil is larger than the pupil of the eye,
 *   only the part of the aperture the eye takes in counts: D = M p.
 * - the darker sky background at higher magnifications. The surface
 *   brightness of the sky drops with (p / exit pupil)². Against a bright
 *   background the threshold of a star goes with the square root of the
 *   background (2.5 log(p / exit pupil)); near the absolute threshold of
 *   the eye, as in the eyepiece under a dark sky, it hardly depends on it.
 *   DARKENING_COEFFICIENT lies in between, which brings the result close
 *   to the limits experienced observers reach (about 14.5 for a 200 mm
 *   telescope at high power under a sky of 6.5). Below an exit pupil of
 *   MIN_EXIT_PUPIL_MM seeing and diffraction spread the star out as much as
 *   the background darkens, so the gain stops there.
 */
class TelescopicLimitingMagnitude
{
    /** Pupil of the dark-adapted eye. */
    public const EYE_PUPIL_MM = 7.0;

    /** Magnitudes gained per tenfold smaller exit pupil. */
    public const DARKENING_COEFFICIENT = 1.25;

    /** Exit pupil below which more magnification shows no fainter stars. */
    public const MIN_EXIT_PUPIL_MM = 0.5;

    /** Transmission of a lens system (refractor, finder, binoculars). */
    public const LENS_TRANSMISSION = 0.9;

    /** Transmission of a mirror system: two aluminium coatings. */
    public const MIRROR_TRANSMISSION = 0.8;

    /** The faintest limit returned: the depth of the star catalogue. */
    public const MAX_MAGNITUDE = 18.0;

    /**
     * @param  float  $nelm  naked-eye limiting magnitude of the sky
     * @param  float  $apertureMm  aperture of the instrument
     * @param  float|null  $magnification  magnification; null for the lowest useful one (exit pupil = eye pupil)
     * @param  float  $obstructionPerc  central obstruction, as a percentage of the aperture
     * @param  bool  $mirrors  whether the instrument has mirrors (lower transmission)
     */
    public static function calculate(float $nelm, float $apertureMm, ?float $magnification = null, float $obstructionPerc = 0.0, bool $mirrors = false): float
    {
        $pupil = self::EYE_PUPIL_MM;

        if ($apertureMm <= 0) {
            return $nelm;
        }

        $magnification = $magnification !== null && $magnification > 0 ? $magnification : $apertureMm / $pupil;
        $exitPupil     = $apertureMm                                                                  / $magnification;

        // The eye takes in no more than its pupil of the light cone.
        $effectiveAperture = $exitPupil > $pupil ? $magnification * $pupil : $apertureMm;

        $obstruction  = max(0.0, min(0.9, $obstructionPerc / 100.0));
        $transmission = ($mirrors ? self::MIRROR_TRANSMISSION : self::LENS_TRANSMISSION) * (1.0 - $obstruction * $obstruction);

        $gain = 5.0 * log10($effectiveAperture / $pupil) + 2.5 * log10($transmission);

        $darkening = self::DARKENING_COEFFICIENT * log10($pupil / max(self::MIN_EXIT_PUPIL_MM, min($pupil, $exitPupil)));

        return min(self::MAX_MAGNITUDE, round($nelm + max(0.0, $gain) + $darkening, 1));
    }
}
