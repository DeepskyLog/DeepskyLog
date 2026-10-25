<?php

namespace App\Services\News;

use Illuminate\Support\Facades\DB;

/**
 * The astronomical events the news announces (#960): eclipses (Eclipses),
 * close conjunctions of two planets, of the Moon and a planet and of a
 * planet and a Messier object, of the Moon and a bright Messier object,
 * oppositions of the outer planets, greatest elongations of Mercury and
 * Venus, the peaks of the major meteor showers and bright comets
 * (BrightComets).
 *
 * The positions are sampled every hour (SkyPositions); a conjunction is a
 * local minimum of the distance below the threshold of its kind, refined with
 * a parabola through the samples around it. A body closer than
 * MIN_ELONGATION to the Sun is lost in its glare and gives no conjunction.
 */
class AstronomicalEvents
{
    private const STEP = 3600;

    /** Degrees: the largest distance of a conjunction of two planets. */
    public const PLANET_DISTANCE = 1.0;

    /** Degrees: the largest distance of a conjunction of the Moon and a planet. */
    public const MOON_DISTANCE = 1.0;

    /** Degrees: the largest distance of a planet and a Messier object. */
    public const DEEPSKY_DISTANCE = 1.0;

    /**
     * The faintest Messier object the Moon is announced near: MOON_DEEPSKY_NEW
     * at new Moon, brighter as the Moon fills, MOON_DEEPSKY_FULL at full Moon
     * (only the Pleiades).
     */
    public const MOON_DEEPSKY_NEW = 4.0;

    public const MOON_DEEPSKY_FULL = 1.6;

    /** Degrees from the Sun a body needs to be seen in the twilight. */
    public const MIN_ELONGATION = 15.0;

    /** The planets the Moon and the Messier objects are compared with: those seen with the naked eye. */
    private const BRIGHT_PLANETS = ['mercury', 'venus', 'mars', 'jupiter', 'saturn'];

    private const OUTER_PLANETS = ['mars', 'jupiter', 'saturn', 'uranus', 'neptune'];

    /**
     * The major meteor showers: the solar longitude (J2000) of the peak and
     * the zenithal hourly rate, from the meteor shower calendar of the
     * International Meteor Organization.
     */
    public const METEOR_SHOWERS = [
        'quadrantids'   => ['longitude' => 283.15, 'zhr' => 80],
        'lyrids'        => ['longitude' => 32.32, 'zhr' => 18],
        'eta_aquariids' => ['longitude' => 45.5, 'zhr' => 50],
        'perseids'      => ['longitude' => 140.0, 'zhr' => 100],
        'orionids'      => ['longitude' => 208.0, 'zhr' => 20],
        'leonids'       => ['longitude' => 235.27, 'zhr' => 15],
        'geminids'      => ['longitude' => 262.2, 'zhr' => 150],
    ];

    /** @var list<int> */
    private array $times = [];

    /** @var array<string, list<array{lon: float, lat: float}>> */
    private array $positions = [];

    /**
     * The events between two moments (timestamps, UT), sorted by date. The
     * key of an event names it, so the news item made from it is found again
     * the next day.
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    public function between(int $from, int $to): array
    {
        // A day on each side, so minima at the edges are recognised.
        for ($t = $from - 86400; $t <= $to + 86400; $t += self::STEP) {
            $this->times[] = $t;
        }
        $this->sample();

        $events = [];
        foreach (Eclipses::between($from, $to) as $eclipse) {
            $events[] = $eclipse + ['key' => $eclipse['kind'] . ':' . gmdate('Y-m-d', $eclipse['at'])];
        }
        array_push($events, ...BrightComets::between($from, $to));
        array_push(
            $events,
            ...$this->planetConjunctions(),
            ...$this->moonConjunctions(),
            ...$this->deepskyConjunctions(),
            ...$this->moonDeepskyConjunctions(),
            ...$this->oppositions(),
            ...$this->elongations(),
            ...$this->meteorShowers(),
        );

        $events = array_values(array_filter($events, fn ($event) => $event['at'] >= $from && $event['at'] < $to));
        usort($events, fn ($a, $b) => [$a['at'], $a['key']] <=> [$b['at'], $b['key']]);

        return $events;
    }

    private function sample(): void
    {
        foreach ($this->times as $t) {
            $sun                       = SkyPositions::sun($t);
            $this->positions['sun'][]  = $sun;
            $this->positions['moon'][] = SkyPositions::moon($t);
            foreach (array_keys(SkyPositions::PLANETS) as $planet) {
                $this->positions[$planet][] = SkyPositions::planet($planet, $t);
            }
        }
    }

    /**
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function planetConjunctions(): array
    {
        $events  = [];
        $planets = array_keys(SkyPositions::PLANETS);
        foreach ($planets as $i => $a) {
            foreach (array_slice($planets, $i + 1) as $b) {
                // Two faint planets together are no news.
                if (! in_array($a, self::BRIGHT_PLANETS, true) && ! in_array($b, self::BRIGHT_PLANETS, true)) {
                    continue;
                }
                $distances = $this->distances($this->positions[$a], $this->positions[$b]);
                foreach ($this->minima($distances, self::PLANET_DISTANCE) as [$at, $separation]) {
                    $elongation = $this->elongation($a, $at);
                    if (abs($elongation) < self::MIN_ELONGATION) {
                        continue;
                    }
                    $events[] = [
                        'key'  => 'planet_conjunction:' . $a . ':' . $b . ':' . gmdate('Y-m-d', $at),
                        'kind' => 'planet_conjunction',
                        'at'   => $at,
                        'data' => [
                            'bodies'     => [$a, $b],
                            'separation' => round($separation, 2),
                            'elongation' => round($elongation, 1),
                            'magnitudes' => [$a => SkyPositions::planetMagnitude($a, $at), $b => SkyPositions::planetMagnitude($b, $at)],
                        ],
                    ];
                }
            }
        }

        return $events;
    }

    /**
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function moonConjunctions(): array
    {
        $events = [];
        foreach (self::BRIGHT_PLANETS as $planet) {
            $distances = $this->distances($this->positions['moon'], $this->positions[$planet]);
            foreach ($this->minima($distances, self::MOON_DISTANCE) as [$at, $separation]) {
                $elongation = $this->elongation($planet, $at);
                if (abs($elongation) < self::MIN_ELONGATION) {
                    continue;
                }
                $moon = SkyPositions::moon($at);
                // Seen from somewhere on the Earth the Moon covers the planet
                // when the geocentric distance is below the Moon's parallax
                // plus its radius.
                $reach = rad2deg(asin(SkyPositions::EARTH_RADIUS_KM / $moon['distance'])) + 358473400.0 / $moon['distance'] / 3600.0;

                $events[] = [
                    'key'  => 'moon_conjunction:' . $planet . ':' . gmdate('Y-m-d', $at),
                    'kind' => 'moon_conjunction',
                    'at'   => $at,
                    'data' => [
                        'bodies'            => ['moon', $planet],
                        'separation'        => round($separation, 2),
                        'elongation'        => round($elongation, 1),
                        'magnitudes'        => [$planet => SkyPositions::planetMagnitude($planet, $at)],
                        'moon_illumination' => round(SkyPositions::moonIllumination($at), 2),
                        'occultation'       => $separation < $reach,
                    ],
                ];
            }
        }

        return $events;
    }

    /**
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function deepskyConjunctions(): array
    {
        $events = [];
        foreach ($this->messierObjects() as $object) {
            $position = SkyPositions::eclipticalOfDate((float) $object->ra * 15.0, (float) $object->decl, $this->times[0]);
            // The planets stay within 9 degrees of the ecliptic.
            if (abs($position['lat']) > 9.0 + self::DEEPSKY_DISTANCE) {
                continue;
            }
            $fixed = array_map(fn (int $t) => SkyPositions::eclipticalOfDate((float) $object->ra * 15.0, (float) $object->decl, $t), $this->times);
            foreach (self::BRIGHT_PLANETS as $planet) {
                $distances = $this->distances($this->positions[$planet], $fixed);
                foreach ($this->minima($distances, self::DEEPSKY_DISTANCE) as [$at, $separation]) {
                    $elongation = $this->elongation($planet, $at);
                    if (abs($elongation) < self::MIN_ELONGATION) {
                        continue;
                    }
                    $events[] = [
                        'key'  => 'deepsky_conjunction:' . $planet . ':' . $object->slug . ':' . gmdate('Y-m-d', $at),
                        'kind' => 'deepsky_conjunction',
                        'at'   => $at,
                        'data' => [
                            'bodies'     => [$planet],
                            'object'     => $this->objectData($object),
                            'separation' => round($separation, 2),
                            'elongation' => round($elongation, 1),
                            'magnitudes' => [$planet => SkyPositions::planetMagnitude($planet, $at)],
                        ],
                    ];
                }
            }
        }

        return $events;
    }

    /**
     * The Moon within MOON_DISTANCE of a bright Messier object: the fuller
     * the Moon, the brighter the object has to be (moonDeepskyLimit()).
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function moonDeepskyConjunctions(): array
    {
        $events = [];
        foreach ($this->messierObjects() as $object) {
            $magnitude = (float) $object->mag;
            if ($magnitude <= 0 || $magnitude > self::MOON_DEEPSKY_NEW) {
                continue;
            }
            $position = SkyPositions::eclipticalOfDate((float) $object->ra * 15.0, (float) $object->decl, $this->times[0]);
            // The Moon stays within 5.3 degrees of the ecliptic.
            if (abs($position['lat']) > 5.3 + self::MOON_DISTANCE) {
                continue;
            }
            $fixed     = array_map(fn (int $t) => SkyPositions::eclipticalOfDate((float) $object->ra * 15.0, (float) $object->decl, $t), $this->times);
            $distances = $this->distances($this->positions['moon'], $fixed);
            foreach ($this->minima($distances, self::MOON_DISTANCE) as [$at, $separation]) {
                $illumination = SkyPositions::moonIllumination($at);
                $sun          = SkyPositions::sun($at);
                $moon         = SkyPositions::moon($at);
                $elongation   = SkyPositions::separation($sun['lon'], $sun['lat'], $moon['lon'], $moon['lat']);
                if ($magnitude > self::moonDeepskyLimit($illumination) || $elongation < self::MIN_ELONGATION) {
                    continue;
                }
                $reach = rad2deg(asin(SkyPositions::EARTH_RADIUS_KM / $moon['distance'])) + 358473400.0 / $moon['distance'] / 3600.0;

                $events[] = [
                    'key'  => 'moon_deepsky_conjunction:' . $object->slug . ':' . gmdate('Y-m-d', $at),
                    'kind' => 'moon_deepsky_conjunction',
                    'at'   => $at,
                    'data' => [
                        'bodies'            => ['moon'],
                        'object'            => $this->objectData($object),
                        'separation'        => round($separation, 2),
                        'elongation'        => round(SkyPositions::wrap180($moon['lon'] - $sun['lon']) >= 0 ? $elongation : -$elongation, 1),
                        'magnitudes'        => [],
                        'moon_illumination' => round($illumination, 2),
                        'occultation'       => $separation < $reach,
                    ],
                ];
            }
        }

        return $events;
    }

    /** The faintest Messier object announced near the Moon at an illuminated fraction. */
    public static function moonDeepskyLimit(float $illumination): float
    {
        return self::MOON_DEEPSKY_NEW - (self::MOON_DEEPSKY_NEW - self::MOON_DEEPSKY_FULL) * $illumination;
    }

    /**
     * @return array<string, mixed>
     */
    private function objectData(object $object): array
    {
        return [
            'name'     => $object->name,
            'slug'     => $object->slug,
            'type'     => $object->type,
            'mag'      => (float) $object->mag   > 0 && (float) $object->mag < 99 ? round((float) $object->mag, 1) : null,
            'diameter' => (float) $object->diam1 > 0 ? round((float) $object->diam1 / 60.0, 1) : null,
            'ra'       => round((float) $object->ra * 15.0, 4),
            'decl'     => round((float) $object->decl, 4),
        ];
    }

    /**
     * The outer planets at opposition: the moment their longitude is 180
     * degrees from the Sun's.
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function oppositions(): array
    {
        $events = [];
        foreach (self::OUTER_PLANETS as $planet) {
            $previous = null;
            foreach ($this->times as $i => $t) {
                $difference = SkyPositions::wrap180($this->positions[$planet][$i]['lon'] - $this->positions['sun'][$i]['lon'] - 180.0);
                // The Sun moves faster than an outer planet, so the difference decreases.
                if ($previous !== null && $previous > 0 && $difference <= 0 && $previous - $difference < 10) {
                    $at       = (int) round($t - self::STEP * $difference / ($difference - $previous));
                    $position = SkyPositions::planet($planet, $at);
                    $events[] = [
                        'key'  => 'opposition:' . $planet . ':' . gmdate('Y-m-d', $at),
                        'kind' => 'opposition',
                        'at'   => $at,
                        'data' => [
                            'bodies'    => [$planet],
                            'magnitude' => SkyPositions::planetMagnitude($planet, $at),
                            'distance'  => round($position['distance'], 3),
                            'decl'      => round(SkyPositions::toEquatorial($position['lon'], $position['lat'], $at)['dec'], 1),
                        ],
                    ];
                }
                $previous = $difference;
            }
        }

        return $events;
    }

    /**
     * Mercury and Venus at their greatest distance from the Sun: east of it
     * in the evening sky, west of it in the morning sky.
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function elongations(): array
    {
        $events = [];
        foreach (['mercury', 'venus'] as $planet) {
            $elongations = array_map(fn ($i) => -abs($this->elongationAt($planet, $i)), array_keys($this->times));
            foreach ($this->minima($elongations, 0.0) as [$at, $negative]) {
                $east     = $this->elongation($planet, $at) > 0;
                $events[] = [
                    'key'  => 'greatest_elongation:' . $planet . ':' . gmdate('Y-m-d', $at),
                    'kind' => 'greatest_elongation',
                    'at'   => $at,
                    'data' => [
                        'bodies'     => [$planet],
                        'side'       => $east ? 'east' : 'west',
                        'elongation' => round(-$negative, 1),
                        'magnitude'  => SkyPositions::planetMagnitude($planet, $at),
                    ],
                ];
            }
        }

        return $events;
    }

    /**
     * The peaks of the major meteor showers: the moment the Sun reaches the
     * solar longitude of the peak.
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function meteorShowers(): array
    {
        $events = [];
        foreach (self::METEOR_SHOWERS as $shower => $peak) {
            $previous = null;
            foreach ($this->times as $i => $t) {
                // The longitude of the Sun referred to the equinox of J2000.
                $century    = (SkyPositions::jd($t) - 2451545.0) / 36525.0;
                $difference = SkyPositions::wrap180($this->positions['sun'][$i]['lon'] - 1.396971 * $century - $peak['longitude']);
                if ($previous !== null && $previous < 0 && $difference >= 0) {
                    $at       = (int) round($t - self::STEP * $difference / ($difference - $previous));
                    $events[] = [
                        'key'  => 'meteor_shower:' . $shower . ':' . gmdate('Y', $at),
                        'kind' => 'meteor_shower',
                        'at'   => $at,
                        'data' => [
                            'shower'            => $shower,
                            'zhr'               => $peak['zhr'],
                            'moon_illumination' => round(SkyPositions::moonIllumination($at), 2),
                        ],
                    ];
                }
                $previous = $difference;
            }
        }

        return $events;
    }

    /**
     * @param  list<array{lon: float, lat: float}>  $a
     * @param  list<array{lon: float, lat: float}>  $b
     * @return list<float>
     */
    private function distances(array $a, array $b): array
    {
        $distances = [];
        foreach ($a as $i => $position) {
            $distances[] = SkyPositions::separation($position['lon'], $position['lat'], $b[$i]['lon'], $b[$i]['lat']);
        }

        return $distances;
    }

    /**
     * The local minima of a series sampled every STEP that are at most
     * $threshold, as [moment, value], refined with a parabola through the
     * samples around them. A threshold of 0 keeps every minimum.
     *
     * @param  list<float>  $values
     * @return list<array{0: int, 1: float}>
     */
    private function minima(array $values, float $threshold): array
    {
        $minima = [];
        for ($i = 1; $i < count($values) - 1; $i++) {
            [$before, $value, $after] = [$values[$i - 1], $values[$i], $values[$i + 1]];
            if ($value > $before || $value >= $after) {
                continue;
            }
            // The vertex of the parabola through the three samples
            $curvature = $before + $after - 2 * $value;
            $offset    = $curvature > 0 ? ($before - $after) / (2 * $curvature) : 0.0;

            $minimum = $value - ($before - $after) * $offset / 4;
            if ($threshold > 0 && $minimum > $threshold) {
                continue;
            }
            $minima[] = [(int) round($this->times[$i] + $offset * self::STEP), $minimum];
        }

        return $minima;
    }

    /**
     * The distance (degrees) of a planet from the Sun at a moment, positive
     * east of the Sun (evening sky), negative west of it (morning sky).
     */
    private function elongation(string $planet, int $at): float
    {
        $sun      = SkyPositions::sun($at);
        $position = SkyPositions::planet($planet, $at);
        $distance = SkyPositions::separation($sun['lon'], $sun['lat'], $position['lon'], $position['lat']);

        return SkyPositions::wrap180($position['lon'] - $sun['lon']) >= 0 ? $distance : -$distance;
    }

    private function elongationAt(string $planet, int $i): float
    {
        $sun      = $this->positions['sun'][$i];
        $position = $this->positions[$planet][$i];
        $distance = SkyPositions::separation($sun['lon'], $sun['lat'], $position['lon'], $position['lat']);

        return SkyPositions::wrap180($position['lon'] - $sun['lon']) >= 0 ? $distance : -$distance;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function messierObjects()
    {
        return DB::table('objectnames')
            ->join('objects', 'objects.name', '=', 'objectnames.objectname')
            ->where('objectnames.catalog', 'M')
            ->select('objects.name', 'objects.slug', 'objects.type', 'objects.ra', 'objects.decl', 'objects.mag', 'objects.diam1')
            ->distinct()
            ->get();
    }
}
