<?php

namespace App\Services;

use App\Jobs\ComputeTripPlan;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\StarParties\StarPartyNights;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The trip planner, "What could I see from…?": for a place the observer
 * picks on a map (not one of their locations: La Palma, Namibia, a
 * starparty abroad), the nights of the trip and one of their instruments,
 * the deep-sky objects that are new for them there. An object is listed
 * when it is well placed in the dark hours of at least one night of the
 * trip (at least MIN_HOURS above MIN_ALTITUDE, as SeasonalVisibility), the
 * instrument shows it there (a contrast reserve above
 * EquipmentGain::VISIBLE_ABOVE under the sky of the trip, dimmed by the
 * extinction at its highest altitude in those nights), and at home (one of
 * their locations, by default the standard one) it either never stands
 * MIN_ALTITUDE high (LOW; behind the local horizon at its transit counts
 * too) or none of their active instruments shows it (FAINTER). Objects the
 * observer has seen are left out when the result is shown (objects()), the
 * rest are ranked by the number of observations in DeepskyLog.
 *
 * The site is an unsaved Location, as EquipmentGain's "What if I bought…"
 * uses an unsaved Instrument; its time zone and sky are proposed from the
 * map (guessTimezone(), suggestSqm()). A run calculates the contrast
 * reserves of all deep-sky objects that rise there, so it is queued
 * (ComputeTripPlan) and cached like EquipmentGain. createLists() makes an
 * observing list with times per night with NightPlanner (plan()).
 */
class TripPlanner
{
    /** Well placed: at least MIN_HOURS this high in the dark hours of a night of the trip. */
    public const MIN_ALTITUDE = TonightsBest::MIN_ALTITUDE;

    public const MIN_HOURS = SeasonalVisibility::MIN_HOURS;

    /** The nights of a trip, at most. */
    public const MAX_NIGHTS = 14;

    /** The sky of the site when the sky-brightness map has no cell there: a dark site. */
    public const DEFAULT_SQM = 21.5;

    /** The time zone of the nearest location within this many degrees, else one from the longitude. */
    private const TIMEZONE_RADIUS_DEG = 3.0;

    /** The objects shown and planned, of the most observed. */
    public const MAX_OBJECTS = 200;

    public const MINUTES_PER_OBJECT = ObservingListTimes::MINUTES_PER_OBJECT;

    /** Never MIN_ALTITUDE high at home. */
    public const LOW = 'low';

    /** High enough at home, but none of the observer's instruments shows it there. */
    public const FAINTER = 'fainter';

    public const CACHE_DAYS = 7;

    /** As EquipmentGain::MAX_ATTEMPTS. */
    public const MAX_ATTEMPTS = 3;

    /** Part of the cache key: raise it when the cached result changes shape or calculation. */
    private const RESULT_VERSION = 1;

    private ?string $key = null;

    /**
     * @param  array{latitude: float, longitude: float, sqm: float, timezone: string, from: string, until: string, name: string}  $trip
     */
    private function __construct(
        public readonly User $user,
        public readonly ?Instrument $instrument,
        public readonly ?Location $home,
        public readonly Location $site,
        public readonly array $trip
    ) {
    }

    /**
     * For a trip of tripSpec(), with one of the observer's active
     * instruments (else their standard telescope), compared with home (one
     * of their locations, EquipmentGain::location()).
     */
    public static function for(User $user, array $trip, ?int $instrumentId = null, ?int $homeId = null): self
    {
        $trip        = self::tripSpec($trip);
        $instruments = RevisitObjects::instruments($user);
        $instrument  = $instruments->firstWhere('id', $instrumentId)
            ?? $instruments->firstWhere('id', (int) $user->stdtelescope)
            ?? $instruments->first();

        return new self($user, $instrument, EquipmentGain::location($user, $homeId), self::site($trip), $trip);
    }

    /**
     * A trip as stored in the cache key and the job: the place (degrees),
     * its sky (SQM), time zone, the evenings of the first and last night
     * (at most MAX_NIGHTS) and the name the observer gave it.
     *
     * @return array{latitude: float, longitude: float, sqm: float, timezone: string, from: string, until: string, name: string}
     */
    public static function tripSpec(array $trip): array
    {
        $latitude  = round(max(-90.0, min(90.0, (float) ($trip['latitude'] ?? 0))), 4);
        $longitude = round(max(-180.0, min(180.0, (float) ($trip['longitude'] ?? 0))), 4);
        $sqm       = is_numeric($trip['sqm'] ?? null) ? round(max(16.0, min(22.5, (float) $trip['sqm'])), 2) : self::DEFAULT_SQM;
        $timezone  = (string) ($trip['timezone'] ?? '');
        if (! in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC), true)) {
            $timezone = self::guessTimezone($latitude, $longitude);
        }
        $date  = fn ($value) => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) !== false ? Carbon::parse($value) : null;
        $from  = $date($trip['from'] ?? null)  ?? Carbon::today();
        $until = $date($trip['until'] ?? null) ?? $from->copy();
        if ($until->lt($from)) {
            $until = $from->copy();
        }
        if ($from->diffInDays($until) >= self::MAX_NIGHTS) {
            $until = $from->copy()->addDays(self::MAX_NIGHTS - 1);
        }

        return [
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'sqm'       => $sqm,
            'timezone'  => $timezone,
            'from'      => $from->toDateString(),
            'until'     => $until->toDateString(),
            'name'      => mb_substr(trim((string) ($trip['name'] ?? '')), 0, 100),
        ];
    }

    /**
     * The site of the trip as a location that is not stored.
     */
    private static function site(array $trip): Location
    {
        $site = new Location();
        $site->forceFill([
            'name'              => $trip['name'],
            'latitude'          => $trip['latitude'],
            'longitude'         => $trip['longitude'],
            'elevation'         => 0,
            'timezone'          => $trip['timezone'],
            'skyBackground'     => $trip['sqm'],
            'limitingMagnitude' => null,
            'horizon'           => null,
        ]);

        return $site;
    }

    /**
     * The time zone of the nearest location in DeepskyLog with one (within
     * TIMEZONE_RADIUS_DEG), else the zone of the longitude (Etc/GMT-1).
     */
    public static function guessTimezone(float $latitude, float $longitude): string
    {
        $radius   = self::TIMEZONE_RADIUS_DEG;
        $wide     = $radius / max(0.2, cos(deg2rad($latitude)));
        $timezone = Location::whereBetween('latitude', [$latitude - $radius, $latitude + $radius])
            ->whereBetween('longitude', [$longitude - $wide, $longitude + $wide])
            ->whereNotNull('timezone')
            ->whereNotIn('timezone', ['', 'UTC'])
            ->orderByRaw('(latitude - ?) * (latitude - ?) + (longitude - ?) * (longitude - ?)', [$latitude, $latitude, $longitude, $longitude])
            ->value('timezone');
        if (is_string($timezone) && in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC), true)) {
            return $timezone;
        }

        // the signs of the Etc zones are the other way round
        $offset = (int) round($longitude / 15.0);

        return $offset === 0 ? 'UTC' : 'Etc/GMT'.($offset > 0 ? '-' : '+').abs($offset);
    }

    /**
     * The sky brightness of the cell of the sky-brightness map at the place,
     * else DEFAULT_SQM.
     */
    public static function suggestSqm(float $latitude, float $longitude): float
    {
        foreach (SkyBrightnessMap::cells() as $cell) {
            if ($latitude >= $cell['south'] && $latitude < $cell['north'] && $longitude >= $cell['west'] && $longitude < $cell['east']) {
                return round((float) $cell['sky'], 2);
            }
        }

        return self::DEFAULT_SQM;
    }

    /**
     * The evenings of the nights of the trip (Y-m-d).
     *
     * @return list<string>
     */
    public function nights(): array
    {
        $nights = [];
        for ($night = Carbon::parse($this->trip['from']); $night->lte(Carbon::parse($this->trip['until'])); $night->addDay()) {
            $nights[] = $night->toDateString();
        }

        return $nights;
    }

    /**
     * The dark hours and the Moon of each night at the site (StarPartyNights).
     *
     * @return list<array{night: string, dark: ?array, moon: ?array, moonless_minutes: int}>
     */
    public function nightSummaries(): array
    {
        return array_map(fn (string $night) => StarPartyNights::at($this->trip['latitude'], $this->trip['longitude'], 0.0, $this->trip['timezone'], $night), $this->nights());
    }

    /**
     * The dark hours of each night of the trip that gets dark, by evening.
     *
     * @return array<string, NightWindow>
     */
    private function windows(): array
    {
        $windows = [];
        foreach ($this->nights() as $night) {
            if ($window = NightWindow::for($this->trip['latitude'], $this->trip['longitude'], $night, $this->trip['timezone'])) {
                $windows[$night] = $window;
            }
        }

        return $windows;
    }

    /**
     * Why nothing can be calculated, as a translation key; null when it can.
     */
    public function unavailable(): ?string
    {
        if (! $this->instrument) {
            return 'Add an instrument to see what you could see on your trip.';
        }
        if (! $this->home) {
            return 'Add a location to see which objects you can see.';
        }
        if (! is_numeric($this->home->getSqm())) {
            return 'Give the location a sky brightness (SQM) or limiting magnitude to see which objects you can see.';
        }
        if ($this->windows() === []) {
            return 'It does not get dark there in these nights.';
        }

        return null;
    }

    /**
     * The cached result, or null when it is not calculated yet.
     *
     * @return array{objects: list<array{name: string, ra: float, dec: float, contrast_reserve: float, category: string, magnification: ?int, reason: string, home_altitude: ?int, before: ?float, nights: list<string>}>, compared: list<string>, sqm: float, home_sqm: float}|null
     */
    public function result(): ?array
    {
        return Cache::get($this->key());
    }

    /**
     * Queues the calculation of a result not cached yet; false when it is
     * already waiting or has failed().
     */
    public function queue(): bool
    {
        if ($this->failed() || ! Cache::add($this->key().':pending', true, now()->addHour())) {
            return false;
        }
        Cache::add($this->key().':attempts', 0, now()->addHour());
        Cache::increment($this->key().':attempts');
        ComputeTripPlan::dispatch($this->user->id, $this->trip, (int) $this->instrument->id, (int) $this->home->id);

        return true;
    }

    public function pending(): bool
    {
        return Cache::has($this->key().':pending');
    }

    public function forgetPending(): void
    {
        Cache::forget($this->key().':pending');
    }

    /**
     * Whether MAX_ATTEMPTS calculations ended without a result (for an hour,
     * or until retry()).
     */
    public function failed(): bool
    {
        return ! Cache::has($this->key())
            && ! $this->pending()
            && (int) Cache::get($this->key().':attempts', 0) >= self::MAX_ATTEMPTS;
    }

    public function retry(): void
    {
        Cache::forget($this->key().':attempts');
    }

    /**
     * Calculates and caches the result.
     */
    public function compute(): array
    {
        $result = $this->calculate();
        Cache::put($this->key(), $result, now()->addDays(self::CACHE_DAYS));
        Cache::forget($this->key().':attempts');

        return $result;
    }

    /**
     * The objects of the result for $reason (null for both) the observer
     * has not seen yet, the most observed first, at most $limit, with their
     * slug, type, constellation, magnitude and number of observations.
     *
     * @return array{total: int, objects: list<array<string, mixed>>}
     */
    public function objects(array $result, ?string $reason = null, int $limit = self::MAX_OBJECTS): array
    {
        $seen     = EquipmentGain::seenObjectNames($this->user);
        $matching = array_values(array_filter($result['objects'], fn (array $object) => ! isset($seen[mb_strtolower($object['name'])])
            && ($reason === null || $object['reason'] === $reason)));
        $chosen = array_slice($matching, 0, $limit);

        $rows = DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->whereIn('o.name', array_column($chosen, 'name'))
            ->get(['o.name', 'o.slug', 'o.type', 'o.mag', 't.name as type_name', 'c.name as constellation', 's.observations'])
            ->keyBy('name');

        $objects = [];
        foreach ($chosen as $object) {
            $row = $rows[$object['name']] ?? null;
            if (! $row) {
                continue;
            }
            $objects[] = [
                ...$object,
                'slug'          => (string) $row->slug,
                'type'          => $row->type_name ?: (string) $row->type,
                'constellation' => $row->constellation,
                'magnitude'     => is_numeric($row->mag) && (float) $row->mag < 99 ? round((float) $row->mag, 1) : null,
                'observations'  => (int) ($row->observations ?? 0),
            ];
        }

        return ['total' => count($matching), 'objects' => $objects];
    }

    /**
     * The objects shown, spread over the nights of the trip (or only
     * $night) with a time each: an object goes to the night it is well
     * placed in with the most time left (each night holds its dark hours
     * divided by $minutes), the most observed first; NightPlanner orders
     * each night, and an object that does not fit is tried in the other
     * nights.
     *
     * @return array{nights: array<string, list<array{name: string, at: int, from: int, until: int}>>, unplanned: list<string>}
     */
    public function plan(array $result, ?string $reason = null, ?string $night = null, int $minutes = self::MINUTES_PER_OBJECT): array
    {
        $minutes = max(1, $minutes);
        $windows = $this->windows();
        if ($night !== null) {
            $windows = array_intersect_key($windows, [$night => true]);
        }
        $objects = $this->objects($result, $reason)['objects'];
        $points  = array_map(fn (array $object) => ['ra' => $object['ra'], 'dec' => $object['dec']], $objects);
        $planner = new NightPlanner($this->trip['latitude'], self::MIN_ALTITUDE, $minutes);

        $visibility = [];
        $capacity   = [];
        $assigned   = [];
        foreach ($windows as $date => $window) {
            $visibility[$date] = $planner->visibility($window, $points);
            $capacity[$date]   = intdiv($window->end - $window->start, $minutes * 60);
            $assigned[$date]   = [];
        }

        $unplanned = [];
        foreach (array_keys($objects) as $key) {
            $best = null;
            foreach ($windows as $date => $window) {
                $room = $capacity[$date] - count($assigned[$date]);
                if ($room > 0 && $visibility[$date][$key]['intervals'] !== [] && ($best === null || $room > $best[1])) {
                    $best = [$date, $room];
                }
            }
            if ($best === null) {
                $unplanned[] = $key;
            } else {
                $assigned[$best[0]][] = $key;
            }
        }

        $schedule = fn (string $date, array $keys) => $planner->schedule(
            $windows[$date],
            array_intersect_key($visibility[$date], array_flip($keys)),
            array_intersect_key($points, array_flip($keys))
        );
        $schedules = [];
        foreach ($assigned as $date => $keys) {
            $schedules[$date] = $schedule($date, $keys);
            foreach ($schedules[$date]['missed'] as $key) {
                $assigned[$date] = array_values(array_diff($assigned[$date], [$key]));
                $unplanned[]     = $key;
            }
        }

        // what did not fit, in the other nights it is well placed in
        $left = [];
        foreach ($unplanned as $key) {
            foreach ($windows as $date => $window) {
                if (in_array($key, $assigned[$date], true) || $visibility[$date][$key]['intervals'] === [] || count($assigned[$date]) >= $capacity[$date]) {
                    continue;
                }
                $tried = $schedule($date, [...$assigned[$date], $key]);
                if ($tried['missed'] === []) {
                    $assigned[$date][] = $key;
                    $schedules[$date]  = $tried;

                    continue 2;
                }
            }
            $left[] = $objects[$key]['name'];
        }

        $nights = [];
        foreach ($schedules as $date => $planned) {
            foreach ($planned['order'] as [$key, $at, [$from, $until]]) {
                $nights[$date][] = ['name' => $objects[$key]['name'], 'at' => $at, 'from' => $from, 'until' => $until];
            }
        }
        ksort($nights);

        return ['nights' => $nights, 'unplanned' => $left];
    }

    /**
     * An observing list with times for each night of the plan (or only
     * $night) that holds objects.
     *
     * @return list<ObservingList>
     */
    public function createLists(array $result, ?string $reason = null, ?string $night = null, int $minutes = self::MINUTES_PER_OBJECT, ?string $name = null, bool $public = false): array
    {
        $plan  = $this->plan($result, $reason, $night, $minutes);
        $lists = [];
        foreach ($plan['nights'] as $date => $objects) {
            $details = [];
            foreach ($objects as $object) {
                $details[$object['name']] = ['at' => $object['at'], 'from' => $object['from'], 'until' => $object['until']];
            }
            $formatted = Carbon::parse($date)->locale(app()->getLocale())->isoFormat('LL');
            $listName  = trim((string) $name) !== ''
                ? (count($plan['nights']) > 1 ? __(':list, night of :date', ['list' => trim((string) $name), 'date' => $formatted]) : trim((string) $name))
                : __(':list, night of :date', ['list' => $this->placeName(), 'date' => $formatted]);
            $lists[] = ObservingList::createWithObjects(
                $this->user,
                $listName,
                array_column($objects, 'name'),
                __('Objects you could see from :place with your :instrument that you cannot see at :home, with their times for the night of :date.', [
                    'place'      => $this->placeName(),
                    'instrument' => $this->instrumentName(),
                    'home'       => $this->homeName(),
                    'date'       => $formatted,
                ]),
                $public,
                $details,
                ['night' => $date, 'timezone' => $this->trip['timezone'], 'location_id' => null],
            );
        }

        return $lists;
    }

    /**
     * The name the observer gave the place, else its coordinates.
     */
    public function placeName(): string
    {
        if ($this->trip['name'] !== '') {
            return $this->trip['name'];
        }

        return sprintf(
            '%s° %s, %s° %s',
            number_format(abs($this->trip['latitude']), 2),
            $this->trip['latitude'] < 0 ? __('S') : __('N'),
            number_format(abs($this->trip['longitude']), 2),
            $this->trip['longitude'] < 0 ? __('W') : __('E'),
        );
    }

    public function instrumentName(): string
    {
        return $this->instrument ? html_entity_decode((string) $this->instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    }

    public function homeName(): string
    {
        return $this->home ? html_entity_decode((string) $this->home->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    }

    /**
     * The objects well placed in the trip's nights that the instrument shows
     * there and that are new compared with home, the most observed first.
     */
    private function calculate(): array
    {
        $windows    = $this->windows();
        $candidates = $this->candidates();
        $points     = $candidates->map(fn ($object) => ['ra' => (float) $object->ra * 15.0, 'dec' => (float) $object->decl])->all();

        // the nights each object is well placed in
        $planner = new NightPlanner($this->trip['latitude'], self::MIN_ALTITUDE, 1);
        $nights  = [];
        foreach ($windows as $date => $window) {
            foreach ($planner->visibility($window, $points) as $key => $visibility) {
                $seconds = array_sum(array_map(fn (array $interval) => $interval[1] - $interval[0], $visibility['intervals']));
                if ($seconds >= self::MIN_HOURS * 3600) {
                    $nights[$key][] = $date;
                }
            }
        }

        $side = fn (Instrument $instrument) => [
            'instrument'     => $instrument,
            'lens'           => $lens = ContrastReserveCalculator::lensFactor($instrument->lens_id),
            'magnifications' => ContrastReserveCalculator::magnifications($instrument, $this->user, $lens),
            'label'          => html_entity_decode((string) $instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ];
        $trip      = $side($this->instrument);
        $homeSides = RevisitObjects::instruments($this->user)->map(fn (Instrument $instrument) => $side($instrument))->all();
        $offset    = DetectionThreshold::offsetFor($this->user);
        $horizon   = LocalHorizon::forLocation($this->home);
        $homeLat   = (float) $this->home->latitude;

        $objects = [];
        foreach ($candidates as $key => $object) {
            if (! isset($nights[$key])) {
                continue;
            }
            $point = $points[$key];
            // the highest altitude in the dark hours of the nights it is well placed in
            $altitude = max(array_map(fn (string $date) => $windows[$date]->highest($point['ra'], $point['dec'])['altitude'], $nights[$key]));
            $target   = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);
            $there    = $this->reserve($target, $trip, $this->site, $altitude);
            if ($there['contrast_reserve'] === null || $there['contrast_reserve'] + $offset <= EquipmentGain::VISIBLE_ABOVE) {
                continue;
            }

            $homeAltitude = ContrastReserveCalculator::highestAltitude($object->decl, $this->home);
            $low          = $homeAltitude === null || $homeAltitude < self::MIN_ALTITUDE
                                                   || ($horizon && ! $horizon->isClear($homeLat, 0.0, (float) $object->decl, self::MIN_ALTITUDE));
            $before = null;
            if (! $low) {
                // the best of the instruments at home, stopping at the first that shows it
                foreach ($homeSides as $homeSide) {
                    $reserve = $this->reserve($target, $homeSide, $this->home, $homeAltitude)['contrast_reserve'];
                    if ($reserve !== null && ($before === null || $reserve + $offset > $before)) {
                        $before = $reserve + $offset;
                    }
                    if ($before !== null && $before > EquipmentGain::VISIBLE_ABOVE) {
                        continue 2;
                    }
                }
            }

            $objects[] = [
                'name'             => (string) $object->name,
                'ra'               => round($point['ra'], 5),
                'dec'              => round($point['dec'], 5),
                'contrast_reserve' => round($there['contrast_reserve'] + $offset, 2),
                'category'         => ContrastReserveCalculator::category($there['contrast_reserve'] + $offset),
                'magnification'    => $there['magnification'],
                'reason'           => $low ? self::LOW : self::FAINTER,
                'home_altitude'    => $homeAltitude === null ? null : (int) round($homeAltitude),
                'before'           => $before       === null ? null : round($before, 2),
                'nights'           => $nights[$key],
                'observations'     => (int) ($object->observations ?? 0),
            ];
        }

        usort($objects, fn (array $a, array $b) => [$b['observations'], $b['contrast_reserve'], $a['name']] <=> [$a['observations'], $a['contrast_reserve'], $b['name']]);

        return [
            'objects'  => array_map(fn (array $object) => array_diff_key($object, ['observations' => true]), $objects),
            'compared' => array_column($homeSides, 'label'),
            'sqm'      => $this->trip['sqm'],
            'home_sqm' => (float) $this->home->getSqm(),
        ];
    }

    private function reserve($target, array $side, Location $location, ?float $altitude): array
    {
        return ContrastReserveCalculator::calculate($target, $side['instrument'], $location, $this->user, $side['lens'], $side['magnifications'], null, $altitude);
    }

    /**
     * The deep-sky objects with a magnitude that reach MIN_ALTITUDE at the
     * site, not of EquipmentGain::EXCLUDED_TYPES.
     */
    private function candidates()
    {
        $latitude = $this->trip['latitude'];

        return DB::table('objects as o')
            ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->whereNotIn('o.type', EquipmentGain::EXCLUDED_TYPES)
            ->whereNotNull('o.mag')
            ->where('o.mag', '<', 99)
            ->whereNotNull('o.ra')
            ->whereBetween('o.decl', [$latitude - (90.0 - self::MIN_ALTITUDE), $latitude + (90.0 - self::MIN_ALTITUDE)])
            ->get(['o.name', 'o.ra', 'o.decl', 'o.mag', 'o.diam1', 'o.diam2', 's.observations'])
            ->unique('name')
            ->values();
    }

    /**
     * Changes with everything the result depends on: the trip, the
     * instruments, the eyepieces and the home location.
     */
    private function key(): string
    {
        return $this->key ??= $this->makeKey();
    }

    private function makeKey(): string
    {
        $instruments = Instrument::where('user_id', $this->user->id)
            ->where('active', true)
            ->orderBy('id')
            ->get(['id', 'aperture_mm', 'focal_length_mm', 'fixedMagnification', 'field_of_view_deg', 'lens_id'])
            ->map(fn (Instrument $instrument) => array_values($instrument->getAttributes()))
            ->all();
        $eyepieces = collect(ContrastReserveCalculator::eyepieces($this->user))
            ->map(fn ($eyepiece) => [$eyepiece->id, $eyepiece->focal_length_mm, $eyepiece->field_stop_mm, $eyepiece->apparentFOV])
            ->all();
        $home = $this->home ? [$this->home->id, $this->home->latitude, $this->home->getSqm(), $this->home->horizon] : null;
        // the name does not change the result
        $trip = array_diff_key($this->trip, ['name' => true]);

        return 'trip_planner:v'.self::RESULT_VERSION.':'.$this->user->id.':'.(int) $this->instrument?->id.':'
            .md5(json_encode([$trip, $instruments, $eyepieces, $home, DetectionThreshold::offsetFor($this->user)]));
    }
}
