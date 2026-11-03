<?php

namespace App\Services\Planets;

/**
 * The albedo features of Mars a telescope of 15–30 cm shows near
 * opposition, with the classical names of the ALPO and BAA maps: their
 * centre in areographic latitude and west longitude and their size as an
 * angular radius (degrees, rounded: the features are irregular and change
 * with the dust), and whether they are dark or bright.
 */
class MarsFeatures
{
    /** @var array<string, array{name: string, lat: float, lon: float, radius: float, bright: bool}> */
    public const FEATURES = [
        'syrtis-major'       => ['name' => 'Syrtis Major', 'lat' => 10.0, 'lon' => 290.0, 'radius' => 11.0, 'bright' => false],
        'hellas'             => ['name' => 'Hellas', 'lat' => -42.0, 'lon' => 290.0, 'radius' => 14.0, 'bright' => true],
        'sinus-sabaeus'      => ['name' => 'Sinus Sabaeus', 'lat' => -8.0, 'lon' => 335.0, 'radius' => 9.0, 'bright' => false],
        'sinus-meridiani'    => ['name' => 'Sinus Meridiani', 'lat' => -4.0, 'lon' => 0.0, 'radius' => 7.0, 'bright' => false],
        'mare-serpentis'     => ['name' => 'Mare Serpentis', 'lat' => -28.0, 'lon' => 320.0, 'radius' => 7.0, 'bright' => false],
        'margaritifer-sinus' => ['name' => 'Margaritifer Sinus', 'lat' => -10.0, 'lon' => 25.0, 'radius' => 6.0, 'bright' => false],
        'mare-acidalium'     => ['name' => 'Mare Acidalium', 'lat' => 45.0, 'lon' => 30.0, 'radius' => 12.0, 'bright' => false],
        'mare-erythraeum'    => ['name' => 'Mare Erythraeum', 'lat' => -25.0, 'lon' => 40.0, 'radius' => 13.0, 'bright' => false],
        'argyre'             => ['name' => 'Argyre', 'lat' => -48.0, 'lon' => 43.0, 'radius' => 8.0, 'bright' => true],
        'aurorae-sinus'      => ['name' => 'Aurorae Sinus', 'lat' => -15.0, 'lon' => 50.0, 'radius' => 6.0, 'bright' => false],
        'solis-lacus'        => ['name' => 'Solis Lacus', 'lat' => -27.0, 'lon' => 88.0, 'radius' => 6.0, 'bright' => false],
        'tharsis'            => ['name' => 'Tharsis', 'lat' => 5.0, 'lon' => 110.0, 'radius' => 12.0, 'bright' => true],
        'olympus-mons'       => ['name' => 'Olympus Mons (Nix Olympica)', 'lat' => 18.0, 'lon' => 133.0, 'radius' => 4.0, 'bright' => true],
        'mare-sirenum'       => ['name' => 'Mare Sirenum', 'lat' => -30.0, 'lon' => 155.0, 'radius' => 11.0, 'bright' => false],
        'propontis'          => ['name' => 'Propontis', 'lat' => 42.0, 'lon' => 185.0, 'radius' => 6.0, 'bright' => false],
        'trivium-charontis'  => ['name' => 'Trivium Charontis', 'lat' => 20.0, 'lon' => 198.0, 'radius' => 4.0, 'bright' => false],
        'elysium'            => ['name' => 'Elysium', 'lat' => 25.0, 'lon' => 212.0, 'radius' => 9.0, 'bright' => true],
        'mare-cimmerium'     => ['name' => 'Mare Cimmerium', 'lat' => -22.0, 'lon' => 215.0, 'radius' => 12.0, 'bright' => false],
        'mare-tyrrhenum'     => ['name' => 'Mare Tyrrhenum', 'lat' => -20.0, 'lon' => 252.0, 'radius' => 11.0, 'bright' => false],
        'utopia'             => ['name' => 'Utopia', 'lat' => 48.0, 'lon' => 250.0, 'radius' => 12.0, 'bright' => false],
    ];

    /** A feature further from the centre of the disk than this (degrees) is too foreshortened to name. */
    public const MAX_CENTRE_DISTANCE = 65.0;

    /** A feature within this of the central meridian (degrees of longitude) is "on the central meridian". */
    public const ON_MERIDIAN = 15.0;

    /**
     * The features turned towards the Earth, nearest to the centre of the
     * disk first, with their distance from the central meridian (degrees,
     * positive when they have yet to reach it: on the east side).
     *
     * @param  array{cm: float, de: float}  $disk  PlanetDisk::at('mars', …)
     * @return list<array{key: string, name: string, lat: float, lon: float, bright: bool, from_meridian: float, on_meridian: bool}>
     */
    public static function facing(array $disk): array
    {
        $result = [];
        foreach (self::FEATURES as $key => $feature) {
            $fromMeridian = self::fromMeridian($feature['lon'], $disk['cm']);
            $centre       = rad2deg(acos(max(-1.0, min(
                1.0,
                sin(deg2rad($feature['lat'])) * sin(deg2rad($disk['de'])) + cos(deg2rad($feature['lat'])) * cos(deg2rad($disk['de'])) * cos(deg2rad($fromMeridian))
            ))));
            if ($centre > self::MAX_CENTRE_DISTANCE) {
                continue;
            }
            $result[] = ['key'  => $key, 'name' => $feature['name'], 'lat' => $feature['lat'], 'lon' => $feature['lon'], 'bright' => $feature['bright'],
                'from_meridian' => round($fromMeridian, 0), 'on_meridian' => abs($fromMeridian) <= self::ON_MERIDIAN, 'centre' => $centre];
        }
        usort($result, fn (array $a, array $b) => $a['centre'] <=> $b['centre']);

        return array_map(function (array $feature) {
            unset($feature['centre']);

            return $feature;
        }, $result);
    }

    /** The longitude difference of a feature from the central meridian, in [-180, 180). */
    public static function fromMeridian(float $longitude, float $cm): float
    {
        $difference = fmod($longitude - $cm + 540.0, 360.0);

        return ($difference < 0 ? $difference + 360.0 : $difference) - 180.0;
    }

    /**
     * The edge of the polar caps (areographic latitude) by the season Ls, as
     * a telescope roughly shows them: the cap of the spring hemisphere
     * shrinks to its small remnant by midsummer; in autumn and winter the
     * cap is under a cloud hood that looks about as large.
     *
     * @var array<string, array<int, float>>
     */
    private const CAP_EDGE = [
        'north' => [0 => 62.0, 30 => 66.0, 60 => 73.0, 90 => 80.0, 120 => 84.0, 180 => 84.0, 210 => 66.0, 270 => 58.0, 360 => 62.0],
        'south' => [0 => -60.0, 90 => -55.0, 180 => -57.0, 210 => -62.0, 240 => -70.0, 270 => -78.0, 300 => -84.0, 330 => -85.0, 360 => -60.0],
    ];

    /** The latitude of the edge of a polar cap ('north' or 'south') in the season $ls. */
    public static function capEdge(string $pole, float $ls): float
    {
        $points   = self::CAP_EDGE[$pole];
        $previous = null;
        foreach ($points as $at => $edge) {
            if ($previous !== null && $ls <= $at) {
                [$from, $fromEdge] = $previous;

                return $fromEdge + ($edge - $fromEdge) * ($ls - $from) / ($at - $from);
            }
            $previous = [$at, $edge];
        }

        return $points[360];
    }

    /**
     * The season of the northern hemisphere of Mars ('spring', 'summer',
     * 'autumn', 'winter') at the areocentric longitude of the Sun.
     */
    public static function season(float $ls): string
    {
        return ['spring', 'summer', 'autumn', 'winter'][min(3, (int) floor($ls / 90.0))];
    }

    /** Whether the season is that of the global dust storms (Ls 180–330, around the perihelion). */
    public static function dustStormSeason(float $ls): bool
    {
        return $ls >= 180.0 && $ls <= 330.0;
    }

    /** The feature on the central meridian, or the nearest one to it. */
    public static function nearestToMeridian(float $cm): string
    {
        $best = null;
        foreach (self::FEATURES as $feature) {
            if ($feature['radius'] < 6.0) {
                continue;
            }
            $distance = abs(self::fromMeridian($feature['lon'], $cm));
            if ($best === null || $distance < $best[1]) {
                $best = [$feature['name'], $distance];
            }
        }

        return $best[0];
    }
}
