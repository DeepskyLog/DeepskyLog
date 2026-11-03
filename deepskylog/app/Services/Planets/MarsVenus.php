<?php

namespace App\Services\Planets;

use App\Models\Location;
use App\Services\News\SkyPositions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Mars and Venus in a night and in the coming months (/mars-venus): when
 * each can be observed (window()), the central meridian of Mars hour by hour
 * with the features turned towards us (meridianHours()), the moments a
 * chosen feature crosses the central meridian in the coming nights
 * (featureTransits()), the disks of the coming weeks (weeks()) and the next
 * opposition of Mars and the next elongations, dichotomy and conjunctions
 * of Venus (events()), all from PlanetDisk and SkyPositions.
 */
class MarsVenus
{
    /** The lowest altitude at which a planet counts as observable (degrees). */
    public const MIN_ALTITUDE = 10.0;

    /**
     * The Sun at most this high: Mars in a dark sky as Jupiter and Saturn,
     * Venus from sunset (it is seen best in a bright sky, often in daylight).
     */
    public const SUN_ALTITUDE = ['mars' => -6.0, 'venus' => 0.0];

    /**
     * The nights in which featureTransits() looks for a feature: at the same
     * hour Mars turns about 10° less each night, so a feature comes back to
     * the central meridian at the same time after some 37 nights.
     */
    public const FEATURE_NIGHTS = 40;

    /** The transits featureTransits() gives at most. */
    public const MAX_FEATURE_TRANSITS = 12;

    /** The rows of weeks(). */
    public const WEEKS = 16;

    /** How far events() looks ahead (days): more than the 780 days between two oppositions of Mars. */
    public const EVENTS_DAYS = 800;

    /** The sidereal rotation of Mars (degrees per day). */
    private const MARS_ROTATION = 350.89200025;

    /**
     * When each planet is at least MIN_ALTITUDE high with the Sun below
     * SUN_ALTITUDE in the night of $night (noon to noon): the first and last
     * moment, and the moment it is highest.
     *
     * @return array{night: string, timezone: string, planets: array<string, array{from: ?int, until: ?int, best: ?int, best_altitude: ?int}>}
     */
    public static function night(float $latitude, float $longitude, string $night, string $timezone): array
    {
        $key = 'mars_venus:night:v1:' . md5(json_encode([round($latitude, 2), round($longitude, 2), $night, $timezone]));

        return Cache::remember($key, 86400, function () use ($latitude, $longitude, $night, $timezone) {
            $noon    = Carbon::parse($night . ' 12:00', $timezone)->getTimestamp();
            $planets = [];
            foreach (PlanetDisk::PLANETS as $planet) {
                // Venus moves more than a degree a day: its position is taken every hour.
                $positions = [];
                for ($hour = 0; $hour <= 24; $hour++) {
                    $positions[$hour] = self::equatorial($planet, $noon + $hour * 3600);
                }
                $from         = $until = $best = null;
                $bestAltitude = -90.0;
                for ($t = $noon; $t <= $noon + 86400; $t += 600) {
                    $position = $positions[intdiv($t - $noon, 3600)];
                    if (self::sunAltitude($t, $latitude, $longitude) > self::SUN_ALTITUDE[$planet]) {
                        continue;
                    }
                    $altitude = SkyPositions::altitude($position['ra'], $position['dec'], $latitude, $longitude, $t);
                    if ($altitude < self::MIN_ALTITUDE) {
                        continue;
                    }
                    $from ??= $t;
                    $until = $t;
                    if ($altitude > $bestAltitude) {
                        $bestAltitude = $altitude;
                        $best         = $t;
                    }
                }
                $planets[$planet] = ['from' => $from, 'until' => $until, 'best' => $best, 'best_altitude' => $best === null ? null : (int) round($bestAltitude)];
            }

            return ['night' => $night, 'timezone' => $timezone, 'planets' => $planets];
        });
    }

    public static function nightAt(Location $location, string $night, string $timezone): array
    {
        return self::night((float) $location->latitude, (float) $location->longitude, $night, $timezone);
    }

    /**
     * The central meridian of Mars every whole hour Mars can be observed in
     * the night, with the features on it.
     *
     * @param  array<string, mixed>  $night  night()
     * @return list<array{at: int, cm: float, features: list<string>}>
     */
    public static function meridianHours(array $night): array
    {
        $window = $night['planets']['mars'];
        if ($window['from'] === null) {
            return [];
        }
        $hours = [];
        for ($t = (int) ceil($window['from'] / 3600) * 3600; $t <= $window['until']; $t += 3600) {
            $disk    = PlanetDisk::at('mars', $t);
            $hours[] = [
                'at'       => $t,
                'cm'       => $disk['cm'],
                'features' => array_column(array_filter(MarsFeatures::facing($disk), fn (array $feature) => $feature['on_meridian']), 'name'),
            ];
        }

        return $hours;
    }

    /**
     * The moments a feature of MarsFeatures crosses the central meridian
     * while Mars can be observed, in FEATURE_NIGHTS nights from $night on.
     *
     * @return list<array{night: string, at: int, altitude: int}>
     */
    public static function featureTransits(Location $location, string $feature, string $night, string $timezone): array
    {
        $key = 'mars_venus:transits:v1:' . md5(json_encode([round((float) $location->latitude, 2), round((float) $location->longitude, 2), $feature, $night, $timezone]));

        return Cache::remember($key, 86400, fn () => self::computeFeatureTransits($location, $feature, $night, $timezone));
    }

    /**
     * @return list<array{night: string, at: int, altitude: int}>
     */
    private static function computeFeatureTransits(Location $location, string $feature, string $night, string $timezone): array
    {
        $longitude = MarsFeatures::FEATURES[$feature]['lon'];
        $date      = Carbon::parse($night);
        $transits  = [];
        for ($i = 0; $i < self::FEATURE_NIGHTS && count($transits) < self::MAX_FEATURE_TRANSITS; $i++) {
            $day    = $date->copy()->addDays($i)->toDateString();
            $window = self::nightAt($location, $day, $timezone)['planets']['mars'];
            if ($window['from'] === null) {
                continue;
            }
            // The central meridian moves about 14.6° an hour: two corrections give the moment to a minute.
            $t = $window['from'];
            for ($step = 0; $step < 3; $step++) {
                $ahead = SkyPositions::wrap($longitude - PlanetDisk::at('mars', $t)['cm']);
                if ($step > 0 && $ahead > 180.0) {
                    $ahead -= 360.0;
                }
                $t += (int) round($ahead / self::MARS_ROTATION * 86400);
            }
            if ($t < $window['from'] || $t > $window['until']) {
                continue;
            }
            $position   = self::equatorial('mars', $t);
            $transits[] = ['night' => $day, 'at' => $t, 'altitude' => (int) round(SkyPositions::altitude($position['ra'], $position['dec'], (float) $location->latitude, (float) $location->longitude, $t))];
        }

        return $transits;
    }

    /**
     * Both disks at 0h UT of every week from $date on.
     *
     * @return list<array{date: string, mars: array<string, mixed>, venus: array<string, mixed>}>
     */
    public static function weeks(string $date): array
    {
        return Cache::remember('mars_venus:weeks:v1:' . $date, 86400, function () use ($date) {
            $start = Carbon::parse($date, 'UTC')->startOfDay();
            $rows  = [];
            for ($i = 0; $i < self::WEEKS; $i++) {
                $t      = $start->copy()->addWeeks($i)->getTimestamp();
                $rows[] = ['date' => gmdate('Y-m-d', $t), 'mars' => PlanetDisk::at('mars', $t), 'venus' => PlanetDisk::at('venus', $t)];
            }

            return $rows;
        });
    }

    /**
     * The next opposition of Mars (with its closest approach and the size of
     * the disk then) and the next greatest elongations, dichotomy and
     * conjunctions of Venus, in time order, from $date on (UT).
     *
     * @return array{mars: list<array{kind: string, at: int, diameter?: float, magnitude?: ?float, elongation?: float}>, venus: list<array{kind: string, at: int, diameter?: float, magnitude?: ?float, elongation?: float}>}
     */
    public static function events(string $date): array
    {
        return Cache::remember('mars_venus:events:v1:' . $date, 7 * 86400, function () use ($date) {
            $from = Carbon::parse($date, 'UTC')->startOfDay()->getTimestamp();

            return ['mars' => self::marsEvents($from), 'venus' => self::venusEvents($from)];
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function marsEvents(int $from): array
    {
        // Zero at opposition, ±180 at conjunction
        $fromOpposition = function (int $t): float {
            $mars = SkyPositions::planet('mars', $t);

            return SkyPositions::wrap180($mars['lon'] - SkyPositions::sun($t)['lon'] - 180.0);
        };

        $step     = 4 * 86400;
        $previous = $fromOpposition($from);
        for ($t = $from + $step; $t <= $from + self::EVENTS_DAYS * 86400; $t += $step) {
            $current = $fromOpposition($t);
            if (abs($current) < 30.0 && abs($previous) < 30.0 && ($previous > 0) !== ($current > 0)) {
                $opposition = self::bisect($fromOpposition, $t - $step, $t);
                // The closest approach lies within a few weeks of the opposition.
                $closest = self::minimum(fn (int $t) => SkyPositions::planet('mars', $t)['distance'], $opposition - 30 * 86400, $opposition + 30 * 86400);
                $disk    = PlanetDisk::at('mars', $closest);

                return [
                    ['kind' => 'opposition', 'at' => $opposition, 'magnitude' => PlanetDisk::at('mars', $opposition)['magnitude']],
                    ['kind' => 'closest_approach', 'at' => $closest, 'diameter' => round($disk['diameter'], 1), 'distance' => round($disk['distance'], 3)],
                ];
            }
            $previous = $current;
        }

        return [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function venusEvents(int $from): array
    {
        $elongation = function (int $t): float {
            $venus = SkyPositions::planet('venus', $t);
            $sun   = SkyPositions::sun($t);

            return SkyPositions::wrap180($venus['lon'] - $sun['lon']);
        };
        $halfLit = function (int $t): float {
            $venus = SkyPositions::planet('venus', $t);
            $earth = SkyPositions::sun($t)['distance'];

            return (($venus['r'] + $venus['distance']) ** 2 - $earth ** 2) / (4 * $venus['r'] * $venus['distance']) - 0.5;
        };

        $step      = 2 * 86400;
        $events    = [];
        $last      = $from + self::EVENTS_DAYS * 86400;
        [$e0, $e1] = [$elongation($from), $elongation($from + $step)];
        $k0        = $halfLit($from);
        for ($t = $from + 2 * $step; $t <= $last && count($events) < 6; $t += $step) {
            $e2 = $elongation($t);
            $k1 = $halfLit($t - $step);
            // A greatest elongation: the largest distance east or west of the Sun
            if (abs($e1) > 30.0 && abs($e1) >= abs($e0) && abs($e1) >= abs($e2)) {
                $at       = self::minimum(fn (int $t) => -abs($elongation($t)), $t - 2 * $step, $t);
                $events[] = ['kind' => $e1 > 0 ? 'greatest_elongation_east' : 'greatest_elongation_west', 'at' => $at, 'elongation' => round(abs(PlanetDisk::at('venus', $at)['elongation']), 1)];
            }
            // A conjunction: the elongation changes sign close to the Sun
            if (abs($e1) < 15.0 && abs($e2) < 15.0 && ($e1 > 0) !== ($e2 > 0)) {
                $at       = self::bisect($elongation, $t - $step, $t);
                $events[] = ['kind' => SkyPositions::planet('venus', $at)['distance'] < 1.0 ? 'inferior_conjunction' : 'superior_conjunction', 'at' => $at];
            }
            // Dichotomy: half the disk lit, in theory (the Schröter effect makes it come days early in the evening, late in the morning)
            if (($k0 > 0) !== ($k1 > 0)) {
                $at       = self::bisect($halfLit, $t - 2 * $step, $t - $step);
                $events[] = ['kind' => 'dichotomy', 'at' => $at, 'elongation' => round(abs(PlanetDisk::at('venus', $at)['elongation']), 1)];
            }
            [$e0, $e1, $k0] = [$e1, $e2, $k1];
        }
        usort($events, fn (array $a, array $b) => $a['at'] <=> $b['at']);

        return array_map(fn (array $event) => $event + ['diameter' => round(PlanetDisk::at('venus', $event['at'])['diameter'], 1)], $events);
    }

    /** The moment between $a and $b at which $f changes sign, to the minute. */
    private static function bisect(callable $f, int $a, int $b): int
    {
        $fa = $f($a);
        while ($b - $a > 60) {
            $m  = intdiv($a + $b, 2);
            $fm = $f($m);
            if (($fm > 0) === ($fa > 0)) {
                [$a, $fa] = [$m, $fm];
            } else {
                $b = $m;
            }
        }

        return intdiv($a + $b, 2);
    }

    /** The moment between $a and $b at which $f is smallest (golden section search, to ten minutes). */
    private static function minimum(callable $f, int $a, int $b): int
    {
        $ratio     = (sqrt(5) - 1) / 2;
        $c         = (int) round($b - $ratio * ($b - $a));
        $d         = (int) round($a + $ratio * ($b - $a));
        [$fc, $fd] = [$f($c), $f($d)];
        while ($b - $a > 600) {
            if ($fc < $fd) {
                [$b, $d, $fd] = [$d, $c, $fc];
                $c            = (int) round($b - $ratio * ($b - $a));
                $fc           = $f($c);
            } else {
                [$a, $c, $fc] = [$c, $d, $fd];
                $d            = (int) round($a + $ratio * ($b - $a));
                $fd           = $f($d);
            }
        }

        return intdiv($a + $b, 2);
    }

    /**
     * @return array{ra: float, dec: float}
     */
    private static function equatorial(string $planet, int $t): array
    {
        $position = SkyPositions::planet($planet, $t);

        return SkyPositions::toEquatorial($position['lon'], $position['lat'], $t);
    }

    private static function sunAltitude(int $t, float $latitude, float $longitude): float
    {
        $sun        = SkyPositions::sun($t);
        $equatorial = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);

        return SkyPositions::altitude($equatorial['ra'], $equatorial['dec'], $latitude, $longitude, $t);
    }
}
