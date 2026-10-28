<?php

namespace App\Services\Observations;

use App\Models\Location;
use App\Services\CometCoordinates;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Jupiter;
use deepskylog\AstronomyLibrary\Targets\Mars;
use deepskylog\AstronomyLibrary\Targets\Mercury;
use deepskylog\AstronomyLibrary\Targets\Moon;
use deepskylog\AstronomyLibrary\Targets\Neptune;
use deepskylog\AstronomyLibrary\Targets\Saturn;
use deepskylog\AstronomyLibrary\Targets\Sun;
use deepskylog\AstronomyLibrary\Targets\Uranus;
use deepskylog\AstronomyLibrary\Targets\Venus;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Facades\DB;

/**
 * The altitude of the target of an observation above the horizon of its
 * location, to check that the object could be seen at the date and time
 * that were entered.
 */
class TargetAltitude
{
    /**
     * An observation is only refused when the object stands lower than this:
     * refraction lifts an object at the horizon by about half a degree, and
     * times are rounded and the coordinates of the location approximate.
     */
    public const TOLERANCE = 1.0;

    private const PLANETS = [
        'sun' => Sun::class,
        'mercury' => Mercury::class,
        'venus' => Venus::class,
        'mars' => Mars::class,
        'jupiter' => Jupiter::class,
        'saturn' => Saturn::class,
        'uranus' => Uranus::class,
        'neptune' => Neptune::class,
    ];

    /**
     * The altitude in degrees of the target at the moment (UT), or, without a
     * time, the highest altitude it reaches on that date. Null when it cannot
     * be computed: no usable location, or a target without known coordinates.
     *
     * @param  array{type: string, table: string, key: string, name: string}  $target  as stored on the observation
     */
    public static function forObservation(array $target, ?Location $location, string $dateUtc, ?string $timeUtc): ?float
    {
        if (! self::usable($location)) {
            return null;
        }

        try {
            if ($timeUtc !== null && $timeUtc !== '') {
                return self::at($target, $location, Carbon::parse(substr($dateUtc, 0, 10).' '.$timeUtc, 'UTC'));
            }

            return self::highestOnDate($target, $location, Carbon::parse(substr($dateUtc, 0, 10), 'UTC'));
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * The altitude in degrees of the target at the moment, or null.
     */
    public static function at(array $target, Location $location, Carbon $moment): ?float
    {
        $geo = self::geo($location);
        $coordinates = self::coordinates($target, $moment, $geo, (float) ($location->elevation ?? 0.0));
        if ($coordinates === null) {
            return null;
        }

        $sidereal = Time::meanSiderialTime($moment->copy()->utc(), $geo);

        return $coordinates->convertToHorizontal($geo, $sidereal)->getAltitude()->getCoordinate();
    }

    /**
     * The highest altitude in degrees the target reaches on the date (UT), at
     * its transit, or null. A Solar System object moves during the day, so its
     * declination is taken at the start, middle and end of the date.
     */
    public static function highestOnDate(array $target, Location $location, Carbon $date): ?float
    {
        $geo = self::geo($location);
        $latitude = (float) $location->latitude;
        $highest = null;
        foreach ([0, 12, 24] as $hours) {
            $coordinates = self::coordinates($target, $date->copy()->startOfDay()->addHours($hours), $geo, (float) ($location->elevation ?? 0.0));
            if ($coordinates === null) {
                return null;
            }
            $altitude = 90.0 - abs($latitude - $coordinates->getDeclination()->getCoordinate());
            $highest = $highest === null ? $altitude : max($highest, $altitude);
            if ($target['type'] === 'deepsky') {
                break;
            }
        }

        return $highest;
    }

    private static function usable(?Location $location): bool
    {
        if ($location === null || $location->latitude === null || $location->longitude === null) {
            return false;
        }
        $latitude = (float) $location->latitude;
        $longitude = (float) $location->longitude;

        // 0, 0 is the placeholder of a location without coordinates.
        return abs($latitude) <= 90 && abs($longitude) <= 180 && ($latitude != 0.0 || $longitude != 0.0);
    }

    private static function geo(Location $location): GeographicalCoordinates
    {
        return new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
    }

    private static function coordinates(array $target, Carbon $moment, GeographicalCoordinates $geo, float $height): ?EquatorialCoordinates
    {
        switch ($target['table']) {
            case 'objects':
                $object = DB::table('objects')->where('name', $target['key'])->first(['ra', 'decl']);

                return $object && $object->ra !== null && $object->decl !== null
                    ? new EquatorialCoordinates((float) $object->ra, (float) $object->decl)
                    : null;

            case 'cometobjects':
                return CometCoordinates::forName($target['name'], $moment, $geo, $height);

            case 'planets':
                return self::bodyCoordinates((string) $target['key'], $moment, $geo, $height);

            case 'moons':
                // A moon of another planet stands next to its planet.
                $planetId = DB::table('moons')->where('slug', $target['key'])->value('planet_id');
                $planet = $planetId !== null ? DB::table('planets')->where('id', $planetId)->value('slug') : null;

                return self::bodyCoordinates($planet === null || $planet === 'earth' ? 'moon' : (string) $planet, $moment, $geo, $height);

            case 'lunar_features':
                return self::bodyCoordinates('moon', $moment, $geo, $height);
        }

        return null;
    }

    private static function bodyCoordinates(string $slug, Carbon $moment, GeographicalCoordinates $geo, float $height): ?EquatorialCoordinates
    {
        if ($slug === 'moon') {
            $body = new Moon();
            $body->calculateEquatorialCoordinates($moment->copy(), $geo, $height);

            return $body->getEquatorialCoordinatesToday();
        }

        $class = self::PLANETS[$slug] ?? null;
        if ($class === null) {
            return null;
        }
        $body = new $class();
        if ($body instanceof Sun) {
            $body->calculateEquatorialCoordinates($moment->copy());
        } else {
            $body->calculateEquatorialCoordinates($moment->copy(), $geo, $height);
        }

        return $body->getEquatorialCoordinatesToday();
    }
}
