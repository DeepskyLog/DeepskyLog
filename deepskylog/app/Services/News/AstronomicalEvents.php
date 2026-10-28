<?php

namespace App\Services\News;

use App\Services\FieldViews\FieldViewRenderer;
use App\Services\Planets\PlanetMoonEvents;
use App\Services\WashingtonDoubleStars;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The astronomical events the news announces (#960): eclipses (Eclipses),
 * close conjunctions of two planets, of the Moon and a planet and of a
 * planet and a Messier object, of the Moon and a bright Messier object,
 * lunar occultations of the planets and of the stars brighter than
 * OCCULTATION_STAR_MAGNITUDE, oppositions of the outer planets, greatest
 * elongations of Mercury and Venus, the peaks of the major meteor showers, bright comets
 * (BrightComets) and the shadows of the moons of Jupiter and Saturn
 * (PlanetMoonEvents::newsEvents(): two shadows on Jupiter, Titan's shadow on
 * Saturn).
 *
 * The positions are sampled every hour (SkyPositions); a conjunction is a
 * local minimum of the distance below the threshold of its kind, refined with
 * a parabola through the samples around it. A body closer than
 * MIN_ELONGATION to the Sun is lost in its glare and gives no conjunction.
 * A conjunction of the Moon and a planet close enough for the Moon to cover
 * the planet from somewhere on the Earth is a lunar occultation instead.
 */
class AstronomicalEvents
{
    private const STEP = 3600;

    /** Degrees: the largest distance of a conjunction of two planets. */
    public const PLANET_DISTANCE = 5.0;

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

    /**
     * The faintest star (V) whose occultation by the Moon is announced: the
     * stars of the first magnitude (Aldebaran, Regulus, Spica, Antares). At
     * 3.5 there would be about 120 occultations a year.
     */
    public const OCCULTATION_STAR_MAGNITUDE = 1.5;

    /**
     * The proper names of the bright stars the Moon can cover, by the Bayer
     * designation of the stars table; the others are named by it.
     */
    public const STAR_NAMES = [
        'Alp Tau' => 'Aldebaran', 'Bet Tau' => 'Elnath', 'Eta Tau' => 'Alcyone', 'Alp Leo' => 'Regulus',
        'Alp Vir' => 'Spica', 'Gam Vir' => 'Porrima', 'Alp Sco' => 'Antares', 'Bet Sco' => 'Acrab',
        'Del Sco' => 'Dschubba', 'Sig Sgr' => 'Nunki', 'Del Sgr' => 'Kaus Media', 'Lam Sgr' => 'Kaus Borealis',
        'Zet Sgr' => 'Ascella', 'Mu Gem' => 'Tejat', 'Eta Gem' => 'Propus', 'Del Cap' => 'Deneb Algedi',
    ];

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
        array_push($events, ...PlanetMoonEvents::newsEvents($from, $to));
        array_push(
            $events,
            ...$this->planetConjunctions(),
            ...$this->moonConjunctions(),
            ...$this->deepskyConjunctions(),
            ...$this->moonDeepskyConjunctions(),
            ...$this->lunarOccultations(),
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
                // The Moon covers the planet somewhere: a lunar occultation.
                if ($separation < self::occultationReach($at)) {
                    continue;
                }

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
                    ],
                ];
            }
        }

        return $events;
    }

    /**
     * Degrees: seen from somewhere on the Earth the Moon covers a point when
     * its geocentric distance from the Moon's centre is below the Moon's
     * parallax plus its radius.
     */
    public static function occultationReach(int $at): float
    {
        $moon = SkyPositions::moon($at);

        return rad2deg(asin(SkyPositions::EARTH_RADIUS_KM / $moon['distance'])) + 358473400.0 / $moon['distance'] / 3600.0;
    }

    /**
     * The Moon covering a planet or a star brighter than
     * OCCULTATION_STAR_MAGNITUDE as seen from somewhere on the Earth. The
     * moments seen from the reader's location are LocalCircumstances'.
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    private function lunarOccultations(): array
    {
        $targets = [];
        foreach (array_keys(SkyPositions::PLANETS) as $planet) {
            $targets[] = ['key' => $planet, 'positions' => $this->positions[$planet], 'data' => ['target' => $planet, 'bodies' => ['moon', $planet]]];
        }
        foreach ($this->occultableStars() as $star) {
            $targets[] = [
                'key'       => $star['key'],
                'positions' => array_map(fn (int $t) => SkyPositions::eclipticalOfDate($star['ra'], $star['decl'], $t), $this->times),
                'data'      => ['target' => 'star', 'bodies' => ['moon'], 'star' => array_diff_key($star, ['key' => true])],
            ];
        }

        $events = [];
        foreach ($targets as $target) {
            $distances = $this->distances($this->positions['moon'], $target['positions']);
            // The reach is at most 1.3 degrees, at perigee.
            foreach ($this->minima($distances, 1.3) as [$at, $separation]) {
                if ($separation >= self::occultationReach($at)) {
                    continue;
                }
                $sun        = SkyPositions::sun($at);
                $moon       = SkyPositions::moon($at);
                $elongation = SkyPositions::separation($sun['lon'], $sun['lat'], $moon['lon'], $moon['lat']);
                if ($elongation < self::MIN_ELONGATION) {
                    continue;
                }
                $planet   = $target['data']['target'] === 'star' ? null : $target['data']['target'];
                $events[] = [
                    'key'  => 'lunar_occultation:' . $target['key'] . ':' . gmdate('Y-m-d', $at),
                    'kind' => 'lunar_occultation',
                    'at'   => $at,
                    'data' => $target['data'] + [
                        'separation'        => round($separation, 2),
                        'elongation'        => round(SkyPositions::wrap180($moon['lon'] - $sun['lon']) >= 0 ? $elongation : -$elongation, 1),
                        'magnitudes'        => $planet === null ? [] : [$planet => SkyPositions::planetMagnitude($planet, $at)],
                        'moon_illumination' => round(SkyPositions::moonIllumination($at), 2),
                    ],
                ];
            }
        }

        return $events;
    }

    /**
     * The stars with a Bayer or Flamsteed designation brighter than
     * OCCULTATION_STAR_MAGNITUDE near enough to the ecliptic for the Moon to
     * cover them (5.3 degrees of the Moon's orbit plus its reach), with the
     * object page of the star when DeepskyLog has one.
     *
     * @return list<array{key: string, name: string, designation: string, slug: ?string, ra: float, decl: float, mag: float}>
     */
    private function occultableStars(): array
    {
        $rows = DB::table('stars')
            ->whereNotNull('constellation_code')
            ->where(fn ($q) => $q->whereNotNull('bayer')->orWhereNotNull('flamsteed'))
            ->where('vmag', '<=', self::OCCULTATION_STAR_MAGNITUDE)
            ->orderBy('vmag')
            ->get(['constellation_code', 'bayer', 'bayer_suffix', 'flamsteed', 'ra_degrees', 'decl_degrees', 'vmag']);

        $greek = array_flip(WashingtonDoubleStars::GREEK);
        $stars = [];
        $names = [];
        foreach ($rows as $row) {
            $position = SkyPositions::eclipticalOfDate((float) $row->ra_degrees, (float) $row->decl_degrees, $this->times[0]);
            if (abs($position['lat']) > 5.3 + 1.3) {
                continue;
            }
            $bayer = $row->bayer === null ? null : ucfirst(strtolower((string) $row->bayer));
            $key   = $bayer      === null ? $row->flamsteed . ' ' . $row->constellation_code : $bayer . ($row->bayer_suffix ?? '') . ' ' . $row->constellation_code;
            // The brightest row of a star the table holds twice (Castor)
            if (isset($stars[$key])) {
                continue;
            }
            $letter      = $bayer  === null ? null : (FieldViewRenderer::GREEK_LETTERS[$bayer] ?? null);
            $designation = $letter === null
                ? $row->flamsteed . ' ' . $row->constellation_code
                : $letter . strtr((string) ($row->bayer_suffix ?? ''), ['1' => '¹', '2' => '²', '3' => '³']) . ' ' . $row->constellation_code;

            // The names the objects of DeepskyLog are known by: Alpha Tau, 87 Tau
            if ($bayer !== null && isset($greek[$bayer])) {
                $names[ucfirst($greek[$bayer]) . ($row->bayer_suffix ? '-' . $row->bayer_suffix : '') . ' ' . $row->constellation_code] = $key;
            }
            if ($row->flamsteed !== null) {
                $names[$row->flamsteed . ' ' . $row->constellation_code] = $key;
            }

            $stars[$key] = [
                'key'         => Str::slug($key),
                'name'        => self::STAR_NAMES[$bayer . ' ' . $row->constellation_code] ?? $designation,
                'designation' => $designation,
                'slug'        => null,
                'ra'          => round((float) $row->ra_degrees, 5),
                'decl'        => round((float) $row->decl_degrees, 5),
                'mag'         => round((float) $row->vmag, 1),
            ];
        }

        if ($names !== []) {
            $slugs = DB::table('objectnames')
                ->join('objects', 'objects.name', '=', 'objectnames.objectname')
                ->whereIn('objectnames.altname', array_keys($names))
                ->pluck('objects.slug', 'objectnames.altname');
            foreach ($slugs as $altname => $slug) {
                foreach ($names as $candidate => $key) {
                    // The comparison of the database ignores case.
                    if (strcasecmp($candidate, (string) $altname) === 0 && $stars[$key]['slug'] === null) {
                        $stars[$key]['slug'] = $slug;
                    }
                }
            }
        }

        return array_values($stars);
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
                $reach = self::occultationReach($at);

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
