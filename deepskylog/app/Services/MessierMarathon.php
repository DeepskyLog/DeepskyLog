<?php

namespace App\Services;

use App\Models\Instrument;
use App\Models\Location;
use App\Models\ObservingList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The Messier marathon planner: the nights of a season in which the Messier
 * objects can be seen from a location in one night, and the order to see
 * them in. In spring (March and April) the Sun stands in the part of the sky
 * without Messier objects, between M 30 and M 74, so all can be seen; in
 * autumn (October and November) it stands in Libra, which hides only M 83
 * and some of the objects low in Scorpius and Ophiuchus.
 *
 * The night runs from the end of nautical twilight in the evening to its
 * begin in the morning (the Sun 12° below the horizon, NightWindow). An
 * object can be seen while it stands at least $minAltitude high; the moments
 * it crosses that altitude follow from the hour angle, as in
 * SeasonalVisibility. An object only that high in the brighter twilight
 * (until the Sun is 6° below the horizon), as M 30 in the morning from most
 * places, is seen at the darkest moment it is that high, marked as twilight.
 *
 * The order, with $minutes per object and $gap minutes to move to the next
 * one, comes from NightPlanner: the one that
 * misses the fewest objects, each moved towards the moment it stands highest.
 *
 * A night near the new Moon is one whose Moon light, the illuminated
 * fraction times the part of the night the Moon is up, is at most
 * MAX_MOON_LIGHT. The best nights see the most objects with the least Moon.
 *
 * With an instrument (useInstrument()), a faint object has to stand higher:
 * the atmosphere dims it less there. Each object then counts from the
 * lowest altitude at which its contrast reserve with the instrument under
 * the dark sky of the location is above VISIBLE_ABOVE (lowestAltitude()),
 * so binoculars see the faint galaxies of Virgo higher up than a telescope,
 * or not at all ('faint').
 */
class MessierMarathon
{
    /** The last number of the catalog (M 111 and on are not Messier's). */
    public const LAST_NUMBER = 110;

    /** The default altitude an object has to reach. */
    public const MIN_ALTITUDE = 5;

    /** The default minutes spent on each object. */
    public const MINUTES_PER_OBJECT = 4;

    /** The first and last night (month-day) of each season. */
    public const SEASONS = [
        'spring' => ['03-01', '04-30'],
        'autumn' => ['10-01', '11-30'],
    ];

    /** The nights near the new Moon have at most this much Moon light. */
    public const MAX_MOON_LIGHT = 0.05;

    /** The number of nights marked as best. */
    public const BEST_NIGHTS = 3;

    /**
     * The contrast reserve above which an object counts as seen with the
     * instrument: at least difficult (EquipmentGain::VISIBLE_ABOVE).
     */
    public const VISIBLE_ABOVE = 0.1;

    /** How long the Moon of the nights of a season is kept. */
    private const CACHE_SECONDS = 7 * 86400;

    /** How long the lowest altitudes of the objects with an instrument are kept. */
    private const ALTITUDE_CACHE_SECONDS = 86400;

    /** @var list<array{number: int, name: string, list_name: string, slug: string, type: string, type_code: string, constellation: ?string, mag: ?float, diam1: ?float, diam2: ?float, ra: float, dec: float, min_altitude?: float}> */
    private array $objects;

    /** The instrument the objects have to be seen with; null for the altitude alone. */
    private ?Instrument $instrument = null;

    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly float $elevation,
        public readonly string $timezone,
        public readonly float $minAltitude = self::MIN_ALTITUDE,
        public readonly int $minutes = self::MINUTES_PER_OBJECT,
        public readonly int $gap = 0,
    ) {
        $this->objects = self::objects();
    }

    public static function forLocation(Location $location, string $timezone, float $minAltitude = self::MIN_ALTITUDE, int $minutes = self::MINUTES_PER_OBJECT, int $gap = 0, ?Instrument $instrument = null, ?User $user = null): self
    {
        $marathon = new self((float) $location->latitude, (float) $location->longitude, (float) ($location->elevation ?? 0), $timezone, $minAltitude, $minutes, $gap);

        return $instrument ? $marathon->useInstrument($instrument, $location, $user) : $marathon;
    }

    /**
     * Plan for the instrument at the location: each object counts from the
     * lowest altitude at which it can be seen with it (lowestAltitude()),
     * with the magnifications of the observer's eyepieces and the lens of
     * the instrument. Cached a day per instrument, sky and magnifications.
     */
    public function useInstrument(Instrument $instrument, Location $location, ?User $user = null): self
    {
        $this->instrument = $instrument;
        $lensFactor       = ContrastReserveCalculator::lensFactor($instrument->lens_id);
        $magnifications   = ContrastReserveCalculator::magnifications($instrument, $user, $lensFactor);
        $fields           = ContrastReserveCalculator::fields($instrument, $magnifications, $lensFactor);

        $key = 'messier_marathon:altitudes:'.md5(json_encode([
            $instrument->only(['aperture_mm', 'instrument_type_id', 'obstruction_perc', 'fixedMagnification', 'field_of_view_deg']),
            $fields, array_keys($magnifications), $location->getSqm(), $location->getNelm((float) ($user?->fstOffset ?? 0)), $this->minAltitude, array_column($this->objects, 'list_name'),
        ]));
        $altitudes = Cache::remember($key, self::ALTITUDE_CACHE_SECONDS, fn () => array_map(
            fn (array $object) => $this->lowestAltitude($object, $instrument, $location, $user, $lensFactor, $magnifications),
            $this->objects,
        ));
        foreach ($altitudes as $index => $altitude) {
            if ($altitude > $this->minAltitude) {
                $this->objects[$index]['min_altitude'] = $altitude;
            }
        }

        return $this;
    }

    /**
     * The name of the instrument the objects have to be seen with.
     */
    public function instrumentName(): ?string
    {
        return $this->instrument ? html_entity_decode($this->instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
    }

    /**
     * The lowest altitude (degrees, to 0.1°) at which the object can be seen
     * with the instrument: at least $minAltitude, higher when the extinction
     * there brings its contrast reserve (a point source's for an object
     * without a size, such as M 40) down to VISIBLE_ABOVE; INF when it is not
     * seen even at the zenith. $minAltitude when its contrast reserve is
     * unknown (no magnitude, a location without a sky brightness).
     *
     * @param  array<int, list<\App\Models\Eyepiece>>  $magnifications
     */
    public function lowestAltitude(array $object, Instrument $instrument, Location $location, ?User $user, float $lensFactor, array $magnifications): float
    {
        $reserve = function (float $altitude) use ($object, $instrument, $location, $user, $lensFactor, $magnifications): ?float {
            if ($object['mag'] === null) {
                return null;
            }
            $result = ['contrast_reserve' => null];
            if (ContrastReserveCalculator::appliesTo($object['type_code'])) {
                $target = ContrastReserveCalculator::target($object['diam1'], $object['diam2'], $object['mag']);
                $result = ContrastReserveCalculator::calculate($target, $instrument, $location, $user, $lensFactor, $magnifications, null, $altitude);
            }
            if ($result['contrast_reserve'] === null) {
                $result = ContrastReserveCalculator::pointSource($object['mag'], $instrument, $location, $user, $lensFactor, $magnifications, $altitude);
            }

            return $result['contrast_reserve'];
        };

        $low   = max(0.5, $this->minAltitude);
        $atLow = $reserve($low);
        if ($atLow === null || $atLow > self::VISIBLE_ABOVE) {
            return $this->minAltitude;
        }
        $atZenith = $reserve(90.0);
        if ($atZenith === null || $atZenith <= self::VISIBLE_ABOVE) {
            return $atZenith === null ? $this->minAltitude : INF;
        }

        // The contrast reserve grows with the altitude: halve the interval
        // between an altitude it is too low at and one it is high enough at.
        $high = 90.0;
        while ($high - $low > 0.1) {
            $middle = ($low + $high) / 2;
            if ($reserve($middle) > self::VISIBLE_ABOVE) {
                $high = $middle;
            } else {
                $low = $middle;
            }
        }

        return ceil($high * 10) / 10;
    }

    /**
     * The Messier objects M 1 to M 110, by number, under the name the list
     * tables match on (objects.name) with their position in degrees, type
     * and constellation. An object is found by its name in the M catalog, or
     * else by its name.
     *
     * @return list<array{number: int, name: string, list_name: string, slug: string, type: string, type_code: string, constellation: ?string, mag: ?float, diam1: ?float, diam2: ?float, ra: float, dec: float}>
     */
    public static function objects(): array
    {
        return Cache::remember('messier_marathon:objects:v2', 86400, function () {
            $numbers = range(1, self::LAST_NUMBER);
            $names   = DB::table('objectnames')
                ->where('catalog', 'M')
                ->whereIn('catindex', array_map('strval', $numbers))
                ->pluck('objectname', 'catindex')
                ->mapWithKeys(fn ($name, $number) => [(int) $number => (string) $name])
                ->all();
            foreach ($numbers as $number) {
                $names[$number] ??= 'M '.$number;
            }

            $objects = DB::table('objects as o')
                ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
                ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
                ->whereIn('o.name', array_values($names))
                ->get(['o.name', 'o.slug', 'o.type', 'o.ra', 'o.decl', 'o.mag', 'o.diam1', 'o.diam2', 't.name as type_name', 'c.name as constellation'])
                ->filter(fn ($row) => is_numeric($row->ra) && is_numeric($row->decl))
                ->keyBy(fn ($row) => mb_strtolower((string) $row->name));

            $result = [];
            ksort($names);
            foreach ($names as $number => $name) {
                $row = $objects[mb_strtolower($name)] ?? null;
                if ($row === null) {
                    continue;
                }
                $result[] = [
                    'number'        => $number,
                    'name'          => 'M '.$number,
                    'list_name'     => (string) $row->name,
                    'slug'          => (string) $row->slug,
                    'type'          => (string) ($row->type_name ?: $row->type),
                    'type_code'     => (string) $row->type,
                    'constellation' => $row->constellation,
                    'mag'           => is_numeric($row->mag)   && (float) $row->mag < 99 && (float) $row->mag != 0.0 ? (float) $row->mag : null,
                    'diam1'         => is_numeric($row->diam1) && (float) $row->diam1 > 0 ? (float) $row->diam1 : null,
                    'diam2'         => is_numeric($row->diam2) && (float) $row->diam2 > 0 ? (float) $row->diam2 : null,
                    'ra'            => (float) $row->ra * 15.0,
                    'dec'           => (float) $row->decl,
                ];
            }

            return $result;
        });
    }

    /**
     * The season the date (Y-m-d) falls in or that comes next, and the ones
     * after it, in order.
     *
     * @return list<array{key: string, year: int, season: string}>
     */
    public static function seasons(string $date, int $count = 3): array
    {
        $year    = (int) substr($date, 0, 4);
        $seasons = [];
        while (count($seasons) < $count) {
            foreach (self::SEASONS as $season => [, $last]) {
                if (count($seasons) < $count && ($year > (int) substr($date, 0, 4) || substr($date, 5) <= $last)) {
                    $seasons[] = ['key' => $year.'-'.$season, 'year' => $year, 'season' => $season];
                }
            }
            $year++;
        }

        return $seasons;
    }

    public function total(): int
    {
        return count($this->objects);
    }

    /**
     * Every night of the season ('spring' or 'autumn'): the window (timestamps; null when it does
     * not get dark enough), the objects high enough in it, the objects the
     * order sees in it and in the twilight, and the Moon (illuminated
     * fraction, when it is up, the Moon light). The best nights near the new
     * Moon, the ones that see the most objects, the fewest of them in the
     * twilight, with the least Moon light, are marked. Nights before $from
     * (Y-m-d) are left out.
     *
     * @return list<array{night: string, start: ?int, end: ?int, possible: int, scheduled: int, twilight: int, moon: ?array{fraction: float, from: ?int, until: ?int, light: float}, near_new_moon: bool, best: bool}>
     */
    public function nights(int $year, string $season = 'spring', ?string $from = null): array
    {
        $moons  = $this->moons($year, $season);
        $nights = [];
        foreach ($this->seasonNights($year, $season) as $night) {
            if ($from !== null && $night < $from) {
                continue;
            }
            $result = $this->night($night);
            if ($result === null) {
                $nights[] = ['night' => $night, 'start' => null, 'end' => null, 'possible' => 0, 'scheduled' => 0, 'twilight' => 0, 'moon' => null, 'near_new_moon' => false, 'best' => false];

                continue;
            }

            $twilight = count(array_filter($result['order'], fn (array $item) => $item['twilight']));
            $moon     = $moons[$night] ?? null;
            $nights[] = [
                'night'         => $night,
                'start'         => $result['start'],
                'end'           => $result['end'],
                'possible'      => $result['possible'],
                'scheduled'     => count($result['order']) - $twilight,
                'twilight'      => $twilight,
                'moon'          => $moon,
                'near_new_moon' => $moon !== null && $moon['light'] <= self::MAX_MOON_LIGHT,
                'best'          => false,
            ];
        }

        $ranked = array_keys(array_filter($nights, fn (array $n) => $n['near_new_moon'] && $n['scheduled'] > 0));
        $rank   = fn (array $n) => [-($n['scheduled'] + $n['twilight']), $n['twilight'], $n['moon']['light']];
        usort($ranked, fn (int $a, int $b) => $rank($nights[$a]) <=> $rank($nights[$b]));
        foreach (array_slice($ranked, 0, self::BEST_NIGHTS) as $index) {
            $nights[$index]['best'] = true;
        }

        return $nights;
    }

    /**
     * The order to see the objects in during the night (Y-m-d): for each the
     * moment to see it, the window it stands high enough in, its altitude
     * then and whether that is in the twilight. The objects it misses are
     * either never high enough in the night ('never'), high enough but not
     * to be seen with the instrument ('faint') or not reached in time
     * ('time', with their windows). Null when it does not get dark enough.
     *
     * @return array{night: string, start: int, end: int, objects: list<array>, missed: list<array>}|null
     */
    public function plan(string $night): ?array
    {
        $result = $this->night($night);
        if ($result === null) {
            return null;
        }

        $objects = [];
        foreach ($result['order'] as $item) {
            $object    = $this->objects[$item['index']];
            $objects[] = $object + [
                'at'       => $item['at'],
                'from'     => $item['from'],
                'until'    => $item['until'],
                'twilight' => $item['twilight'],
                'altitude' => $result['window']->altitude($object['ra'], $object['dec'], $item['at']),
            ];
        }

        $missed = [];
        foreach ($result['missed'] as $index) {
            $missed[] = $this->objects[$index] + [
                'reason' => match (true) {
                    in_array($index, $result['faint'], true)          => 'faint',
                    $result['visibility'][$index]['intervals'] === [] => 'never',
                    default                                           => 'time',
                },
                'intervals' => $result['visibility'][$index]['intervals'],
            ];
        }

        return ['night' => $night, 'start' => $result['start'], 'end' => $result['end'], 'objects' => $objects, 'missed' => $missed];
    }

    /**
     * Make an observing list of the observer with the objects of a plan(), in
     * its order, each with the time to see it and its window, and the
     * objects it misses at the end with the reason as note. The location is
     * named when it is the observer's own, else its latitude.
     */
    public function createList(User $owner, array $plan, Location $location, ?string $name = null, bool $public = false): ObservingList
    {
        $place = (int) $location->user_id === (int) $owner->id ? html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;

        $details = [];
        foreach ($plan['objects'] as $object) {
            $details[$object['list_name']] = [
                'note'  => $object['twilight'] ? __('In the twilight.') : null,
                'at'    => $object['at'],
                'from'  => $object['from'],
                'until' => $object['until'],
            ];
        }
        foreach ($plan['missed'] as $object) {
            $details[$object['list_name']] = ['note' => match ($object['reason']) {
                'never' => __('Not high enough in this night.'),
                'faint' => __('Too faint for :instrument at the altitudes it reaches in this night.', ['instrument' => $this->instrumentName()]),
                default => __('Not reached in time (high enough from :from to :until).', ['from' => $this->time($object['intervals'][0][0]), 'until' => $this->time(end($object['intervals'])[1])]),
            }];
        }

        $replace = [
            'date'     => $this->date($plan['night']),
            'location' => $place,
            'latitude' => (int) round($this->latitude),
            'minutes'  => $this->minutes + $this->gap,
            'altitude' => (int) round($this->minAltitude),
        ];
        $description = $place !== null
            ? __('Messier marathon in the night of :date from :location: the objects in the order to see them, :minutes minutes each, with the time to see each one and when it stands at least :altitude° high.', $replace)
            : __('Messier marathon in the night of :date at a latitude of :latitude°: the objects in the order to see them, :minutes minutes each, with the time to see each one and when it stands at least :altitude° high.', $replace);
        if ($this->instrument) {
            $description .= ' '.__('A faint object counts only while it stands high enough to be seen with :instrument.', ['instrument' => $this->instrumentName()]);
        }

        $names = array_merge(array_column($plan['objects'], 'list_name'), array_column($plan['missed'], 'list_name'));

        return ObservingList::createWithObjects($owner, trim((string) $name) ?: $this->listName($plan['night']), $names, $description, $public, $details, [
            'night'       => $plan['night'],
            'timezone'    => $this->timezone,
            'location_id' => $location->id,
        ]);
    }

    /**
     * The default name of the list of the night (Y-m-d).
     */
    public function listName(string $night): string
    {
        return __('Messier marathon :date', ['date' => $this->date($night)]);
    }

    private function date(string $night): string
    {
        return Carbon::parse($night)->locale(app()->getLocale())->isoFormat('LL');
    }

    private function time(int $timestamp): string
    {
        return Carbon::createFromTimestamp($timestamp, $this->timezone)->format('H:i');
    }

    /**
     * The dark window of the night: between the ends of nautical twilight.
     */
    public function window(string $night): ?NightWindow
    {
        return NightWindow::for($this->latitude, $this->longitude, $night, $this->timezone, ['nautical']);
    }

    /**
     * The order of the night: the objects high enough in the dark window in
     * the order of schedule(), the objects only high enough in the twilight
     * at the moment closest to the dark one, by time; and the objects missed.
     *
     * The objects missed that would be high enough without the instrument
     * are 'faint'.
     *
     * @return array{start: int, end: int, window: NightWindow, possible: int, order: list<array{index: int, at: int, from: int, until: int, twilight: bool}>, missed: list<int>, faint: list<int>, visibility: list<array>}|null
     */
    private function night(string $night): ?array
    {
        $dark = $this->window($night);
        if ($dark === null) {
            return null;
        }
        $twilight = NightWindow::for($this->latitude, $this->longitude, $night, $this->timezone, ['civil']) ?? $dark;

        $planner    = new NightPlanner($this->latitude, $this->minAltitude, $this->minutes + $this->gap);
        $visibility = $planner->visibility($twilight, $this->objects);
        $inDark     = array_map(fn (array $v) => [
            'intervals' => array_values(array_filter(array_map(fn (array $i) => [max($i[0], $dark->start), min($i[1], $dark->end)], $v['intervals']), fn (array $i) => $i[1] > $i[0])),
            'transits'  => $v['transits'],
        ], $visibility);
        $schedule = $planner->schedule($dark, $inDark, $this->objects);

        $order   = array_map(fn (array $item) => ['index' => $item[0], 'at' => $item[1], 'from' => $item[2][0], 'until' => $item[2][1], 'twilight' => false], $schedule['order']);
        $missed  = [];
        $evening = [];
        $morning = [];
        foreach ($schedule['missed'] as $index) {
            if ($inDark[$index]['intervals'] !== [] || $visibility[$index]['intervals'] === []) {
                $missed[] = $index;

                continue;
            }
            // Every interval lies in the evening or the morning twilight.
            $closest = null;
            foreach ($visibility[$index]['intervals'] as [$from, $until]) {
                $at       = $until <= $dark->start ? $until : $from;
                $distance = abs($at - ($until <= $dark->start ? $dark->start : $dark->end));
                if ($closest === null || $distance < $closest['distance']) {
                    $closest = ['index' => $index, 'at' => $at, 'from' => $from, 'until' => $until, 'twilight' => true, 'distance' => $distance];
                }
            }
            unset($closest['distance']);
            if ($closest['at'] <= $dark->start) {
                $evening[] = $closest;
            } else {
                $morning[] = $closest;
            }
        }

        // The twilight objects a slot apart: in the evening back from the
        // first object of the dark, the one that sets last first; in the
        // morning on from the last, the one that rises first first.
        $slot = ($this->minutes + $this->gap) * 60;
        usort($evening, fn (array $a, array $b) => $b['at'] <=> $a['at']);
        $next = $order[0]['at'] ?? PHP_INT_MAX;
        foreach ($evening as $item) {
            $item['at'] = min($item['at'], $next - $slot);
            if ($item['at'] < $item['from']) {
                $missed[] = $item['index'];

                continue;
            }
            $order[] = $item;
            $next    = $item['at'];
        }
        usort($morning, fn (array $a, array $b) => $a['at'] <=> $b['at']);
        $previous = $order === [] ? PHP_INT_MIN : max(array_column($order, 'at'));
        foreach ($morning as $item) {
            $item['at'] = max($item['at'], $previous + $slot);
            if ($item['at'] > $item['until']) {
                $missed[] = $item['index'];

                continue;
            }
            $order[]  = $item;
            $previous = $item['at'];
        }
        sort($missed);
        usort($order, fn (array $a, array $b) => $a['at'] <=> $b['at']);

        $raised = array_filter($this->objects, fn (array $object, int $index) => isset($object['min_altitude']) && $visibility[$index]['intervals'] === [], ARRAY_FILTER_USE_BOTH);
        $faint  = $raised === [] ? [] : array_keys(array_filter(
            $planner->visibility($twilight, array_map(fn (array $object) => array_diff_key($object, ['min_altitude' => true]), $raised)),
            fn (array $v) => $v['intervals'] !== [],
        ));

        return [
            'start'      => $dark->start,
            'end'        => $dark->end,
            'window'     => $dark,
            'possible'   => count(array_filter($inDark, fn (array $v) => $v['intervals'] !== [])),
            'order'      => $order,
            'missed'     => $missed,
            'faint'      => $faint,
            'visibility' => $visibility,
        ];
    }

    /**
     * @return list<string>
     */
    private function seasonNights(int $year, string $season): array
    {
        [$first, $last] = self::SEASONS[$season];
        $nights         = [];
        for ($date = strtotime($year.'-'.$first.' 12:00 UTC'); $date <= strtotime($year.'-'.$last.' 12:00 UTC'); $date += 86400) {
            $nights[] = gmdate('Y-m-d', $date);
        }

        return $nights;
    }

    /**
     * The Moon in each night of the season, which does not depend on the
     * options: cached per place and season.
     *
     * @return array<string, array{fraction: float, from: ?int, until: ?int, light: float}>
     */
    private function moons(int $year, string $season): array
    {
        $key = 'messier_marathon:moon:'.implode('|', [round($this->latitude, 2), round($this->longitude, 2), $this->timezone, $year, $season]);

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($year, $season) {
            $moons = [];
            foreach ($this->seasonNights($year, $season) as $night) {
                $window = $this->window($night);
                if ($window === null) {
                    continue;
                }
                $sky      = new MoonlightSky($window, $this->longitude, $this->elevation);
                $up       = $sky->upBetween();
                $fraction = $sky->illuminatedFraction();

                $moons[$night] = [
                    'fraction' => $fraction,
                    'from'     => $up[0] ?? null,
                    'until'    => $up[1] ?? null,
                    'light'    => $up ? $fraction * ($up[1] - $up[0]) / max(1, $window->end - $window->start) : 0.0,
                ];
            }

            return $moons;
        });
    }
}
