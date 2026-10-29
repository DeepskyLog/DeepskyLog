<?php

namespace App\Services\Observations;

use App\Models\DeepskyObject;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Services\AtTheEyepiece;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;

/**
 * How a drawing of a deep-sky observation lies on the sky, to lay it over
 * the DSS image in Aladin on the observation page: the centre (the object),
 * the true field the width of the drawing spans (of a sketch of the sketch
 * pad at the eyepiece the part AtTheEyepiece::SKETCH_FIELD, inside N and W),
 * and
 * the turn and mirroring that bring the drawing back to north up, east left.
 *
 * The drawing is taken to be as the field views draw the eyepiece
 * (FieldViewRenderer): zenith up with an alt-az mount at the moment of the
 * observation, north up with an equatorial mount, mirrored per the
 * instrument's flip/flop (never binoculars). The observer fine-tunes it on
 * the page, as a scanned drawing has margins and was often turned.
 */
class SketchOverlay
{
    /** The field (degrees) assumed when the equipment does not give one. */
    public const DEFAULT_FIELD_DEG = 0.5;

    /**
     * The overlay of the observation's drawing, or null for an observation
     * without a drawing or of an object without a position (comets and the
     * other moving objects are not laid over a photo).
     *
     * @return array{ra: float, dec: float, field_deg: float, field_estimated: bool, rotation_deg: float, flip: bool, flop: bool, up: string, image: string, field_fraction: float}|null
     */
    public static function for(Observation $observation): ?array
    {
        if (! $observation->has_drawing || $observation->target_type !== Observation::TYPE_DEEPSKY) {
            return null;
        }

        $object = DeepskyObject::where('name', $observation->target_key)->first();
        if (! $object || ! is_numeric($object->ra) || ! is_numeric($object->decl)) {
            return null;
        }

        $raDeg  = (float) $object->ra * 15.0;
        $decDeg = (float) $object->decl;

        $instrument          = $observation->instrument_id ? Instrument::with('mount_type')->find($observation->instrument_id) : null;
        $lens                = $observation->lens_id ? Lens::find($observation->lens_id) : null;
        [$field, $estimated] = self::field($observation, $instrument, $lens, $object);

        $binocular  = $instrument?->isBinocular() ?? false;
        $equatorial = $instrument?->mount_type?->name === 'Equatorial';

        $rotation = 0.0;
        $up       = 'north';
        if (! $equatorial) {
            $location = $observation->location_id ? Location::find($observation->location_id) : null;
            $zenith   = self::zenithRotation($observation, $location, $raDeg, $decDeg);
            if ($zenith !== null) {
                $rotation = $zenith;
                $up       = 'zenith';
            }
        }

        return [
            'ra'              => $raDeg,
            'dec'             => $decDeg,
            'field_deg'       => $field,
            'field_estimated' => $estimated,
            'rotation_deg'    => round($rotation, 1),
            'flip'            => $instrument !== null && ! $binocular && (bool) $instrument->flip_image,
            'flop'            => $instrument !== null && ! $binocular && (bool) $instrument->flop_image,
            'up'              => $up,
            'image'           => (string) $observation->drawingUrl(),
            // A sketch of the sketch pad has N and W around its field
            'field_fraction'  => str_starts_with((string) $observation->drawing_filename, AtTheEyepiece::DRAWING_PREFIX) ? AtTheEyepiece::SKETCH_FIELD : 1.0,
        ];
    }

    /**
     * The true field (degrees) of the eyepiece (or of an instrument with a
     * fixed magnification), else of an ordinary eyepiece at the recorded
     * magnification, else three times the object's size; whether it is
     * estimated.
     *
     * @return array{0: float, 1: bool}
     */
    private static function field(Observation $observation, ?Instrument $instrument, ?Lens $lens, DeepskyObject $object): array
    {
        if ($instrument) {
            $eyepiece = $observation->eyepiece_id ? Eyepiece::find($observation->eyepiece_id) : null;
            $field    = $instrument->trueFieldOfViewDeg($eyepiece, $lens);
            if ($field !== null && $field > 0) {
                return [$field, $eyepiece === null && $instrument->hasEstimatedFieldOfView()];
            }
        }

        $magnification = (float) Observation::normalizeMagnification($observation->magnification);
        if ($magnification > 0) {
            return [Instrument::FIXED_MAGNIFICATION_APPARENT_FOV / $magnification, true];
        }

        $size = max((float) $object->diam1, (float) $object->diam2) / 3600.0;

        return [$size > 0 ? min(5.0, max(0.1, 3 * $size)) : self::DEFAULT_FIELD_DEG, true];
    }

    /**
     * The angle (degrees, counter-clockwise) by which the field views turn
     * the sky to put the zenith up (FieldViewRenderer::resolveRotation()), at
     * the moment and place of the observation; null without both.
     */
    public static function zenithRotation(Observation $observation, ?Location $location, float $raDeg, float $decDeg): ?float
    {
        $moment = $observation->localObservedAt($location);

        return $moment === null ? null : self::zenithRotationAt($location, $moment, $raDeg, $decDeg);
    }

    /**
     * The same at any moment; null without a place.
     */
    public static function zenithRotationAt(?Location $location, Carbon $moment, float $raDeg, float $decDeg): ?float
    {
        if ($location === null || ! is_numeric($location->latitude) || ! is_numeric($location->longitude)) {
            return null;
        }

        $geo      = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
        $sidereal = Time::meanSiderialTime($moment->copy()->utc(), $geo);
        $lst      = ($sidereal->hour + $sidereal->minute / 60 + ($sidereal->second + $sidereal->microsecond / 1e6) / 3600) * 15.0;

        return self::rotationTowards($raDeg, $decDeg, $lst, (float) $location->latitude);
    }

    /**
     * Where north and west lie in the telescope view of the field views
     * (FieldViewRenderer::project()), as unit vectors on the screen (x to the
     * right, y down), for the N and W of the sketch pad: turned by the
     * rotation (degrees, counter-clockwise; 0 north up) and mirrored per
     * flip (top-bottom) and flop (left-right).
     *
     * @return array{north: array{0: float, 1: float}, west: array{0: float, 1: float}}
     */
    public static function compass(float $rotationDeg, bool $flip, bool $flop): array
    {
        $rotation = deg2rad($rotationDeg);
        $screen   = function (float $x, float $y) use ($rotation, $flip, $flop): array {
            $xr = $x * cos($rotation) - $y * sin($rotation);
            $yr = $x * sin($rotation) + $y * cos($rotation);

            return [round($flop ? -$xr : $xr, 4), round($flip ? $yr : -$yr, 4)];
        };

        // On the sky west is to the right of north up, east left
        return ['north' => $screen(0.0, 1.0), 'west' => $screen(1.0, 0.0)];
    }

    /**
     * The angle (degrees) that turns the direction of the point (RA, Dec)
     * as seen from the centre to the top, in the projection of the field
     * views (east left, north up); null at the centre itself.
     */
    public static function rotationTowards(float $ra0Deg, float $dec0Deg, float $raDeg, float $decDeg): ?float
    {
        $ra0  = deg2rad($ra0Deg);
        $dec0 = deg2rad($dec0Deg);
        $ra   = deg2rad($raDeg);
        $dec  = deg2rad($decDeg);
        $dRa  = $ra - $ra0;

        $x = -cos($dec) * sin($dRa);
        $y = cos($dec0) * sin($dec) - sin($dec0) * cos($dec) * cos($dRa);
        if (hypot($x, $y) < 1.0e-9) {
            return null;
        }

        return rad2deg(M_PI / 2 - atan2($y, $x));
    }
}
