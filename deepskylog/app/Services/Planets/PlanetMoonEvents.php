<?php

namespace App\Services\Planets;

use App\Models\GreatRedSpotPosition;
use App\Models\Location;
use App\Models\User;
use App\Services\News\SkyPositions;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * The events of the moons of Jupiter and Saturn (PlanetMoons):
 *
 * - a Galilean moon or Titan in front of the planet's disk (transit_start,
 *   transit_end), its shadow on the disk (shadow_start, shadow_end), the moon
 *   in the planet's shadow (eclipse_start, eclipse_end) or behind the disk
 *   (occultation_start, occultation_end): the moments the moon's centre (or
 *   that of its shadow) crosses the limb of the flattened disk. An eclipse
 *   while the moon is behind the disk and an occultation while it is in the
 *   shadow cannot be seen and are left out;
 * - the Great Red Spot on Jupiter's central meridian (grs_transit), from the
 *   newest System II longitude and drift the administrators entered on
 *   /admin/great-red-spot (GreatRedSpotPosition; GRS_LONGITUDE on GRS_EPOCH,
 *   drifting GRS_DRIFT degrees a year, without one): the spot drifts
 *   irregularly, so planets:grs-reminder asks them every month to check it
 *   (each degree off moves the transits by about 1.7 minutes);
 * - Titan at its greatest distance east or west of Saturn
 *   (elongation_east, elongation_west).
 *
 * The positions are taken every STEP seconds and each crossing is refined to
 * a few seconds; against JPL Horizons the Galilean moons are good to about a
 * minute, Titan's slow shadow to a few minutes. night() gives the
 * events of a night that can be seen from a place: the planet at least
 * MIN_ALTITUDE high with the Sun below SUN_ALTITUDE.
 */
class PlanetMoonEvents
{
    public const STEP = 300;

    /**
     * The System II longitude of the Great Red Spot (degrees) on GRS_EPOCH
     * (Sky & Telescope, from the JUPOS trend), used when no measurement was
     * entered on /admin/great-red-spot.
     */
    public const GRS_LONGITUDE = 91.0;

    public const GRS_EPOCH = '2026-06-01';

    /** The drift of the Great Red Spot in System II longitude (degrees a year, about 1.75° a month). */
    public const GRS_DRIFT = 21.0;

    /** The planet has to be this high (degrees) for its events to be seen. */
    public const MIN_ALTITUDE = 10.0;

    /** The Sun has to be this low (degrees): the moons and shadows need a dark enough sky. */
    public const SUN_ALTITUDE = -6.0;

    /** The planets closer to the Sun (degrees) are lost in its glare: no news. */
    public const MIN_ELONGATION = 20.0;

    /**
     * Two shadows on Jupiter for less time (minutes) are no news: in the
     * months Io and Europa are in step they come every three and a half days.
     */
    public const MIN_DOUBLE_SHADOW_MINUTES = 30;

    /** The events of the home page. */
    public const HOMEPAGE_EVENTS = 6;

    /** The moons whose phenomena are followed, per planet. */
    public const MOONS = ['jupiter' => PlanetMoons::JUPITER_MOONS, 'saturn' => ['titan']];

    /** The order of the events at the same moment. */
    private const TYPE_ORDER = [
        'transit_end'     => 0, 'shadow_end' => 1, 'occultation_end' => 2, 'eclipse_end' => 3, 'grs_transit' => 4,
        'elongation_east' => 5, 'elongation_west' => 5, 'transit_start' => 6, 'shadow_start' => 7, 'occultation_start' => 8, 'eclipse_start' => 9,
    ];

    /**
     * The events between two moments (timestamps, UT), sorted by moment.
     *
     * @return list<array{planet: string, moon: ?string, type: string, at: int, distance?: float}>
     */
    public static function between(int $from, int $to): array
    {
        $events = [];
        foreach (['jupiter', 'saturn'] as $planet) {
            $previous = null;
            $samples  = [];
            for ($t = $from - self::STEP; $t <= $to + self::STEP; $t += self::STEP) {
                $sample    = self::sample($planet, $t);
                $samples[] = $sample;
                if ($previous !== null) {
                    array_push($events, ...self::crossings($planet, $previous, $sample));
                }
                $previous = $sample;
            }
            if ($planet === 'jupiter') {
                array_push($events, ...self::grsTransits($samples));
            } else {
                array_push($events, ...self::elongations($samples));
            }
        }

        $events = array_values(array_filter($events, fn (array $event) => $event['at'] >= $from && $event['at'] < $to));
        usort($events, fn (array $a, array $b) => [$a['at'], self::TYPE_ORDER[$a['type']]] <=> [$b['at'], self::TYPE_ORDER[$b['type']]]);

        return $events;
    }

    /**
     * The longitude of the Great Red Spot at a moment, from the newest
     * measurement entered by the administrators (GreatRedSpotPosition), else
     * from the GRS_* constants.
     */
    public static function grsLongitude(int $timestamp): float
    {
        $grs   = GreatRedSpotPosition::current();
        $years = ($timestamp - $grs['epoch']) / (365.25 * 86400);

        return SkyPositions::wrap($grs['longitude'] + $grs['drift'] * $years);
    }

    /**
     * The events of the night that starts on the evening of $night (Y-m-d in
     * $timezone) that can be seen from the place, and per planet when it can
     * be observed then (high enough in a dark enough sky) and when it is
     * highest. Cached a day per place and night.
     *
     * @return array{night: string, timezone: string, planets: array<string, array{from: ?int, until: ?int, best: ?int, best_altitude: ?float}>, events: list<array<string, mixed>>}
     */
    public static function night(float $latitude, float $longitude, string $night, string $timezone): array
    {
        $key = 'planet_moon_events:night:v1:' . md5(json_encode([round($latitude, 2), round($longitude, 2), $night, $timezone, GreatRedSpotPosition::current()]));

        return Cache::remember($key, 86400, fn () => self::computeNight($latitude, $longitude, $night, $timezone));
    }

    public static function nightAt(Location $location, string $night, string $timezone): array
    {
        return self::night((float) $location->latitude, (float) $location->longitude, $night, $timezone);
    }

    /**
     * The coming nights from $night on.
     *
     * @return list<array<string, mixed>> night()
     */
    public static function upcoming(Location $location, string $night, string $timezone, int $nights): array
    {
        $date = Carbon::parse($night);

        return array_map(fn (int $i) => self::nightAt($location, $date->copy()->addDays($i)->toDateString(), $timezone), range(0, $nights - 1));
    }

    /**
     * The events still to come tonight at the observer's location (else
     * Location::forEphemerides()) for the home page, or null when there are
     * none that can be seen. Before noon the night of the ephemeris date is
     * the one that just ended: when its events are over, those of the coming
     * night are shown.
     *
     * @return array{night: array<string, mixed>, events: list<array<string, mixed>>}|null
     */
    public static function forHomePage(?User $user): ?array
    {
        $location = Location::forEphemerides($user);
        if (! $location || ! $location->hasValidCoordinates()) {
            return null;
        }
        $timezone = EphemerisDate::timezone($location);
        $date     = Carbon::parse(EphemerisDate::current($timezone));
        $now      = now()->getTimestamp();
        foreach ([$date, $date->copy()->addDay()] as $day) {
            $night  = self::nightAt($location, $day->toDateString(), $timezone);
            $events = array_values(array_filter($night['events'], fn (array $event) => $event['at'] >= $now));
            if ($events !== []) {
                return ['night' => $night, 'events' => array_slice($events, 0, self::HOMEPAGE_EVENTS)];
            }
            // The night of the ephemeris date has not started yet: no need to look at the next one.
            if (Carbon::parse($day->toDateString() . ' 12:00', $timezone)->getTimestamp() > $now) {
                break;
            }
        }

        return null;
    }

    /**
     * The rare events the news announces between two moments: two or more
     * shadows on Jupiter at the same time (double_shadow_transit) and the
     * shadow of Titan on Saturn (titan_shadow_transit), only possible in the
     * years around Saturn's equinox. In the format of AstronomicalEvents.
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    public static function newsEvents(int $from, int $to): array
    {
        // A day on each side, so the shadows already on the disk at the edges are found.
        $shadows = [];
        foreach (self::between($from - 86400, $to + 86400) as $event) {
            if ($event['type'] === 'shadow_start') {
                $shadows[] = ['planet' => $event['planet'], 'moon' => $event['moon'], 'start' => $event['at'], 'end' => null];
            } elseif ($event['type'] === 'shadow_end') {
                foreach ($shadows as &$shadow) {
                    if ($shadow['moon'] === $event['moon'] && $shadow['end'] === null) {
                        $shadow['end'] = $event['at'];
                    }
                }
                unset($shadow);
            }
        }
        $shadows = array_values(array_filter($shadows, fn (array $shadow) => $shadow['end'] !== null));

        $events = [];
        foreach ($shadows as $shadow) {
            if ($shadow['planet'] === 'saturn') {
                $events[] = self::newsEvent('titan_shadow_transit', 'saturn', [$shadow], $shadow['start'], $shadow['end']);
            }
        }

        // The moments at which the number of shadows on Jupiter changes
        $jupiter = array_values(array_filter($shadows, fn (array $shadow) => $shadow['planet'] === 'jupiter'));
        $moments = array_unique(array_merge(array_column($jupiter, 'start'), array_column($jupiter, 'end')));
        sort($moments);
        $current = null;
        foreach ($moments as $moment) {
            $on = array_values(array_filter($jupiter, fn (array $shadow) => $shadow['start'] <= $moment && $shadow['end'] > $moment));
            if (count($on) >= 2) {
                $current ??= ['start' => $moment, 'shadows' => []];
                foreach ($on as $shadow) {
                    $current['shadows'][$shadow['moon']] = $shadow;
                }
            } elseif ($current !== null) {
                if ($moment - $current['start'] >= self::MIN_DOUBLE_SHADOW_MINUTES * 60) {
                    $events[] = self::newsEvent('double_shadow_transit', 'jupiter', array_values($current['shadows']), $current['start'], $moment);
                }
                $current = null;
            }
        }

        return array_values(array_filter($events, function (array $event) use ($from, $to) {
            if ($event['at'] < $from || $event['at'] >= $to) {
                return false;
            }
            $sun    = SkyPositions::sun($event['at']);
            $planet = SkyPositions::planet($event['data']['planet'], $event['at']);

            return SkyPositions::separation($sun['lon'], $sun['lat'], $planet['lon'], $planet['lat']) >= self::MIN_ELONGATION;
        }));
    }

    /**
     * When a news event of newsEvents() can be seen from a place: the part of
     * it with the planet at least MIN_ALTITUDE high and the Sun below
     * SUN_ALTITUDE, and the planet's highest altitude then, or null.
     *
     * @param  array<string, mixed>  $data
     * @return array{from: int, until: int, altitude: float}|null
     */
    public static function visibleDuring(array $data, float $latitude, float $longitude): ?array
    {
        $from     = $until = null;
        $altitude = -90.0;
        for ($t = (int) $data['start']; $t <= (int) $data['end']; $t += 120) {
            $planet = self::altitude($data['planet'], $t, $latitude, $longitude);
            if ($planet < self::MIN_ALTITUDE || self::sunAltitude($t, $latitude, $longitude) > self::SUN_ALTITUDE) {
                continue;
            }
            $from ??= $t;
            $until    = $t;
            $altitude = max($altitude, $planet);
        }

        return $from === null ? null : ['from' => $from, 'until' => $until, 'altitude' => round($altitude, 0)];
    }

    /**
     * The name of a moon in the language of the request.
     */
    public static function moonName(string $moon): string
    {
        return match ($moon) {
            'io'       => __('Io'),
            'europa'   => __('Europa'),
            'ganymede' => __('Ganymede'),
            'callisto' => __('Callisto'),
            'tethys'   => __('Tethys'),
            'dione'    => __('Dione'),
            'rhea'     => __('Rhea'),
            'titan'    => __('Titan'),
            'iapetus'  => __('Iapetus'),
            default    => ucfirst($moon),
        };
    }

    /**
     * What happens at an event, in the language of the request.
     *
     * @param  array{planet: string, moon: ?string, type: string}  $event
     */
    public static function label(array $event): string
    {
        $moon   = $event['moon']   === null ? '' : self::moonName($event['moon']);
        $planet = $event['planet'] === 'jupiter' ? __('Jupiter') : __('Saturn');

        return match ($event['type']) {
            'transit_start'     => __(':moon starts its transit across :planet', ['moon' => $moon, 'planet' => $planet]),
            'transit_end'       => __(':moon ends its transit across :planet', ['moon' => $moon, 'planet' => $planet]),
            'shadow_start'      => __('The shadow of :moon appears on :planet', ['moon' => $moon, 'planet' => $planet]),
            'shadow_end'        => __('The shadow of :moon leaves :planet', ['moon' => $moon, 'planet' => $planet]),
            'eclipse_start'     => __(':moon disappears in the shadow of :planet', ['moon' => $moon, 'planet' => $planet]),
            'eclipse_end'       => __(':moon reappears from the shadow of :planet', ['moon' => $moon, 'planet' => $planet]),
            'occultation_start' => __(':moon disappears behind :planet', ['moon' => $moon, 'planet' => $planet]),
            'occultation_end'   => __(':moon reappears from behind :planet', ['moon' => $moon, 'planet' => $planet]),
            'grs_transit'       => __('The Great Red Spot on the central meridian of Jupiter'),
            'elongation_east'   => __(':moon at its greatest distance east of :planet', ['moon' => $moon, 'planet' => $planet]),
            'elongation_west'   => __(':moon at its greatest distance west of :planet', ['moon' => $moon, 'planet' => $planet]),
            default             => $event['type'],
        };
    }

    /**
     * @param  list<array{planet: string, moon: string, start: int, end: int}>  $shadows
     * @return array{key: string, kind: string, at: int, data: array<string, mixed>}
     */
    private static function newsEvent(string $kind, string $planet, array $shadows, int $start, int $end): array
    {
        $moons = array_column($shadows, 'moon');

        return [
            'key'  => $kind . ':' . implode(':', $moons) . ':' . gmdate('Y-m-d', $start),
            'kind' => $kind,
            'at'   => $start,
            'data' => [
                'bodies'  => [$planet],
                'planet'  => $planet,
                'moons'   => $moons,
                'start'   => $start,
                'end'     => $end,
                'shadows' => array_map(fn (array $shadow) => ['moon' => $shadow['moon'], 'start' => $shadow['start'], 'end' => $shadow['end']], $shadows),
            ],
        ];
    }

    /**
     * The values of the moons at a moment: in the disk (below 0) or not, in
     * front (z < 0) or behind, seen from the Earth and from the Sun, and X
     * seen from the Earth; Jupiter's central meridian.
     *
     * @return array{t: int, moons: array<string, array{earth: float, earth_z: float, sun: float, sun_z: float, x: float}>, cm2?: float, distance: float}
     */
    private static function sample(string $planet, int $t): array
    {
        $positions = $planet === 'jupiter' ? PlanetMoons::jupiter($t) : PlanetMoons::saturn($t);
        $moons     = [];
        foreach (self::MOONS[$planet] as $moon) {
            $earth        = $positions['earth'][$moon];
            $sun          = $positions['sun'][$moon];
            $moons[$moon] = [
                'earth'   => PlanetMoons::disk($planet, $earth['x'], $earth['y']),
                'earth_z' => $earth['z'],
                'sun'     => PlanetMoons::disk($planet, $sun['x'], $sun['y']),
                'sun_z'   => $sun['z'],
                'x'       => $earth['x'],
            ];
        }

        return ['t' => $t, 'moons' => $moons, 'distance' => $positions['distance']] + (isset($positions['cm2']) ? ['cm2' => $positions['cm2']] : []);
    }

    /**
     * The crossings of the limb between two samples.
     *
     * @return list<array{planet: string, moon: string, type: string, at: int}>
     */
    private static function crossings(string $planet, array $a, array $b): array
    {
        $events = [];
        foreach (self::MOONS[$planet] as $moon) {
            foreach (['earth', 'sun'] as $view) {
                $before = $a['moons'][$moon][$view];
                $after  = $b['moons'][$moon][$view];
                if (($before < 0) === ($after < 0)) {
                    continue;
                }
                // Bisection to a few seconds
                [$lo, $hi] = [$a['t'], $b['t']];
                while ($hi - $lo > 4) {
                    $mid = intdiv($lo + $hi, 2);
                    if ((self::sample($planet, $mid)['moons'][$moon][$view] < 0) === ($before < 0)) {
                        $lo = $mid;
                    } else {
                        $hi = $mid;
                    }
                }
                $at     = intdiv($lo + $hi, 2);
                $state  = self::sample($planet, $at)['moons'][$moon];
                $front  = $state[$view . '_z'] < 0;
                $starts = $after               < 0;
                if ($view === 'earth') {
                    // A moon behind the disk while in the shadow: nothing to see
                    if (! $front && $state['sun'] < 0 && $state['sun_z'] > 0) {
                        continue;
                    }
                    $type = $front ? ($starts ? 'transit_start' : 'transit_end') : ($starts ? 'occultation_start' : 'occultation_end');
                } else {
                    // A moon in the shadow while behind the disk: nothing to see
                    if (! $front && $state['earth'] < 0 && $state['earth_z'] > 0) {
                        continue;
                    }
                    $type = $front ? ($starts ? 'shadow_start' : 'shadow_end') : ($starts ? 'eclipse_start' : 'eclipse_end');
                }
                $events[] = ['planet' => $planet, 'moon' => $moon, 'type' => $type, 'at' => $at];
            }
        }

        return $events;
    }

    /**
     * The moments the Great Red Spot crosses Jupiter's central meridian.
     *
     * @param  list<array<string, mixed>>  $samples
     * @return list<array{planet: string, moon: null, type: string, at: int}>
     */
    private static function grsTransits(array $samples): array
    {
        $events   = [];
        $previous = null;
        foreach ($samples as $sample) {
            $difference = SkyPositions::wrap180($sample['cm2'] - self::grsLongitude($sample['t']));
            if ($previous !== null && $previous[1] < 0 && $difference >= 0 && $difference - $previous[1] < 90) {
                $events[] = [
                    'planet' => 'jupiter',
                    'moon'   => null,
                    'type'   => 'grs_transit',
                    'at'     => (int) round($previous[0] + ($sample['t'] - $previous[0]) * -$previous[1] / ($difference - $previous[1])),
                ];
            }
            $previous = [$sample['t'], $difference];
        }

        return $events;
    }

    /**
     * Titan at its greatest distance east (X smallest) or west (X largest) of
     * Saturn, refined with a parabola, with that distance in arcseconds.
     *
     * @param  list<array<string, mixed>>  $samples
     * @return list<array{planet: string, moon: string, type: string, at: int, distance: float}>
     */
    private static function elongations(array $samples): array
    {
        $events = [];
        for ($i = 1; $i < count($samples) - 1; $i++) {
            [$before, $value, $after] = array_map(fn (int $j) => $samples[$j]['moons']['titan']['x'], [$i - 1, $i, $i + 1]);
            $west                     = $value > $before && $value >= $after;
            $east                     = $value < $before && $value <= $after;
            if (! $west && ! $east) {
                continue;
            }
            $curvature = $before + $after - 2 * $value;
            $offset    = $curvature != 0.0 ? ($before - $after) / (2 * $curvature) : 0.0;
            $extreme   = $value - ($before - $after) * $offset  / 4;
            $events[]  = [
                'planet'   => 'saturn',
                'moon'     => 'titan',
                'type'     => $west ? 'elongation_west' : 'elongation_east',
                'at'       => (int) round($samples[$i]['t'] + $offset * self::STEP),
                'distance' => round(abs($extreme) * PlanetMoons::SEMIDIAMETER['saturn'] / $samples[$i]['distance'], 0),
            ];
        }

        return $events;
    }

    private static function computeNight(float $latitude, float $longitude, string $night, string $timezone): array
    {
        $noon    = Carbon::parse($night . ' 12:00', $timezone)->getTimestamp();
        $planets = [];
        // The planets move about a tenth of a degree a day: their position at midnight serves the whole night.
        $positions = [];
        foreach (['jupiter', 'saturn'] as $planet) {
            $positions[$planet] = self::equatorial($planet, $noon + 43200);
            $from               = $until = $best = null;
            $bestAltitude       = -90.0;
            for ($t = $noon; $t <= $noon + 86400; $t += 600) {
                $altitude = SkyPositions::altitude($positions[$planet]['ra'], $positions[$planet]['dec'], $latitude, $longitude, $t);
                if ($altitude < self::MIN_ALTITUDE || self::sunAltitude($t, $latitude, $longitude) > self::SUN_ALTITUDE) {
                    continue;
                }
                $from ??= $t;
                $until = $t;
                if ($altitude > $bestAltitude) {
                    $bestAltitude = $altitude;
                    $best         = $t;
                }
            }
            $planets[$planet] = ['from' => $from, 'until' => $until, 'best' => $best, 'best_altitude' => $best === null ? null : round($bestAltitude, 0)];
        }

        $events = [];
        if ($planets['jupiter']['from'] !== null || $planets['saturn']['from'] !== null) {
            foreach (self::between($noon, $noon + 86400) as $event) {
                $position = $positions[$event['planet']];
                $altitude = SkyPositions::altitude($position['ra'], $position['dec'], $latitude, $longitude, $event['at']);
                if ($altitude < self::MIN_ALTITUDE || self::sunAltitude($event['at'], $latitude, $longitude) > self::SUN_ALTITUDE) {
                    continue;
                }
                $events[] = $event + ['altitude' => round($altitude, 0)];
            }
        }

        return ['night' => $night, 'timezone' => $timezone, 'planets' => $planets, 'events' => $events];
    }

    /**
     * @return array{ra: float, dec: float}
     */
    private static function equatorial(string $planet, int $t): array
    {
        $position = SkyPositions::planet($planet, $t);

        return SkyPositions::toEquatorial($position['lon'], $position['lat'], $t);
    }

    private static function altitude(string $planet, int $t, float $latitude, float $longitude): float
    {
        $equatorial = self::equatorial($planet, $t);

        return SkyPositions::altitude($equatorial['ra'], $equatorial['dec'], $latitude, $longitude, $t);
    }

    private static function sunAltitude(int $t, float $latitude, float $longitude): float
    {
        $sun        = SkyPositions::sun($t);
        $equatorial = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);

        return SkyPositions::altitude($equatorial['ra'], $equatorial['dec'], $latitude, $longitude, $t);
    }
}
