<?php

namespace App\Services;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon;
use Illuminate\Support\Facades\DB;

/**
 * The positions of the objects of an observing list (by their names in the
 * list, objects.name or search_index.name) at a moment: deep-sky objects
 * from the objects table, the planets (TonightsBest::PLANETS), comets
 * (CometCoordinates), asteroids (AsteroidCoordinates) and the Moon calculated
 * for the moment. A planet's moon gets the position of its planet, a lunar
 * feature that of the Moon. Within one night the moving objects are taken at
 * that moment.
 */
class ListTargetPositions
{
    /**
     * @param  list<string>  $names
     * @return array<string, array{ra: float, dec: float}> by name, RA and declination in degrees
     */
    public static function at(array $names, float $latitude, float $longitude, Carbon $moment): array
    {
        $positions = [];
        $byName    = [];
        foreach ($names as $name) {
            $byName[mb_strtolower($name)] = $name;
        }

        foreach (DB::table('objects')->whereIn('name', $names)->get(['name', 'ra', 'decl']) as $object) {
            if (is_numeric($object->ra) && is_numeric($object->decl) && isset($byName[mb_strtolower((string) $object->name)])) {
                $positions[$byName[mb_strtolower((string) $object->name)]] = ['ra' => (float) $object->ra * 15.0, 'dec' => (float) $object->decl];
            }
        }

        $remaining = array_values(array_diff($names, array_keys($positions)));
        if ($remaining === []) {
            return $positions;
        }

        $geo  = new GeographicalCoordinates($longitude, $latitude);
        $rows = DB::table('search_index')
            ->whereIn('name', $remaining)
            ->whereIn('source_table', ['objects', 'planets', 'moons', 'lunar_features', 'cometobjects', 'asteroids'])
            ->where('source_type', '<>', 'alias')
            ->get(['name', 'source_table', 'source_pk']);
        foreach ($rows as $row) {
            $name = $byName[mb_strtolower((string) $row->name)] ?? null;
            if ($name === null || isset($positions[$name])) {
                continue;
            }
            try {
                $position = self::position((string) $row->source_table, (string) $row->source_pk, $moment, $geo);
            } catch (\Throwable $e) {
                report($e);
                $position = null;
            }
            if ($position !== null) {
                $positions[$name] = $position;
            }
        }

        return $positions;
    }

    /**
     * @return array{ra: float, dec: float}|null
     */
    private static function position(string $table, string $key, Carbon $moment, GeographicalCoordinates $geo): ?array
    {
        switch ($table) {
            case 'objects':
                $object = DB::table('objects')->where('slug', $key)->first(['ra', 'decl']);

                return $object && is_numeric($object->ra) && is_numeric($object->decl) ? ['ra' => (float) $object->ra * 15.0, 'dec' => (float) $object->decl] : null;
            case 'planets':
                return self::planet((int) $key, $moment, $geo);
            case 'moons':
                $moon = DB::table('moons')->where('id', (int) $key)->first(['name', 'planet_id']);
                if (! $moon) {
                    return null;
                }

                return $moon->planet_id ? self::planet((int) $moon->planet_id, $moment, $geo) : self::moon($moment, $geo);
            case 'lunar_features':
                return self::moon($moment, $geo);
            case 'cometobjects':
                $name        = DB::table('cometobjects')->where('id', (int) $key)->value('name');
                $coordinates = $name ? CometCoordinates::forName((string) $name, $moment->copy(), $geo) : null;

                return $coordinates ? self::degrees($coordinates) : null;
            case 'asteroids':
                $number   = DB::table('asteroids')->where('id', (int) $key)->value('number');
                $elements = $number ? AsteroidCoordinates::orbitalElements((int) $number) : null;
                $target   = $elements ? AsteroidCoordinates::target($elements) : null;

                return $target ? self::degrees(AsteroidCoordinates::calculate($target, $moment->copy(), $geo)) : null;
        }

        return null;
    }

    /**
     * @return array{ra: float, dec: float}|null
     */
    private static function planet(int $id, Carbon $moment, GeographicalCoordinates $geo): ?array
    {
        $slug  = mb_strtolower((string) DB::table('planets')->where('id', $id)->value('slug'));
        $class = TonightsBest::PLANETS[$slug] ?? null;
        if ($class === null) {
            return null;
        }
        $planet = new $class();
        $planet->calculateEquatorialCoordinates($moment->copy(), $geo, 0.0);

        return self::degrees($planet->getEquatorialCoordinatesToday());
    }

    /**
     * @return array{ra: float, dec: float}
     */
    private static function moon(Carbon $moment, GeographicalCoordinates $geo): array
    {
        $moon = new Moon();
        $moon->calculateEquatorialCoordinates($moment->copy(), $geo, 0.0);

        return self::degrees($moon->getEquatorialCoordinatesToday());
    }

    /**
     * @return array{ra: float, dec: float}
     */
    private static function degrees(object $coordinates): array
    {
        return ['ra' => (float) $coordinates->getRA()->getCoordinate() * 15.0, 'dec' => (float) $coordinates->getDeclination()->getCoordinate()];
    }
}
