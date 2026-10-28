<?php

namespace App\Services;

use App\Models\Instrument;
use App\Models\Location;
use App\Models\User;
use App\Services\FieldViews\FieldViewBuilder;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Magnitude;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;

/**
 * The contrast reserve of an object in a given night, with the sky lit by the
 * Moon (MoonlightSky): at the moment of the dark hours the sky at the object
 * is darkest while it stands high enough. The stored contrast reserves
 * (user_object_metrics) and ContrastReserveCalculator use the dark sky of
 * the location; the Moon depends on the night and the hour.
 */
class MoonlitContrastReserve
{
    /** The object should stand this high, else at least LOW_ALTITUDE. */
    public const MIN_ALTITUDE = 30.0;

    public const LOW_ALTITUDE = 10.0;

    /**
     * Null when it does not get dark at the location, or the object stays
     * below LOW_ALTITUDE during the dark hours.
     *
     * @param  string  $night  the date (Y-m-d) of the evening of the night, at the location
     * @param  array<int, list<\App\Models\Eyepiece>>|null  $magnifications  from ContrastReserveCalculator::magnifications(), when already known
     * @return array{contrast_reserve: ?float, category: ?string, sqm: float, time: string, altitude: int,
     *     moon_up: bool, moon_fraction: float}|null
     */
    public static function for(AstroTarget $target, float $raDeg, float $decDeg, Instrument $instrument, Location $location, ?User $user, string $night, string $timezone, float $lensFactor = 1.0, ?array $magnifications = null): ?array
    {
        $window = NightWindow::for((float) $location->latitude, (float) $location->longitude, $night, $timezone);
        if ($window === null) {
            return null;
        }
        $moon    = new MoonlightSky($window, (float) $location->longitude, (float) ($location->elevation ?? 0.0));
        $darkSqm = $location->getSqm();
        $darkSqm = is_numeric($darkSqm) && (float) $darkSqm > 0 ? (float) $darkSqm : Magnitude::nelmToSqm(FieldViewBuilder::DEFAULT_NELM);
        $best    = $moon->darkestMoment($raDeg, $decDeg, $darkSqm, self::MIN_ALTITUDE)
            ?? $moon->darkestMoment($raDeg, $decDeg, $darkSqm, self::LOW_ALTITUDE);
        if ($best === null) {
            return null;
        }

        $result = ContrastReserveCalculator::calculate($target, $instrument, $location, $user, $lensFactor, $magnifications, $best['sqm'], $best['altitude']);
        $upAt   = $moon->upBetween();

        return [
            'contrast_reserve' => is_numeric($result['contrast_reserve']) ? (float) $result['contrast_reserve'] : null,
            'category'         => $result['category'],
            'sqm'              => $best['sqm'],
            'time'             => Carbon::createFromTimestamp($best['timestamp'], $timezone)->format('H:i'),
            'altitude'         => (int) round($best['altitude']),
            'moon_up'          => $upAt !== null && $best['timestamp'] >= $upAt[0] && $best['timestamp'] <= $upAt[1] && $best['sqm'] < $darkSqm,
            'moon_fraction'    => round($moon->illuminatedFraction(), 2),
        ];
    }
}
