<?php

namespace App\Services;

use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\FieldViews\FieldViewBuilder;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Magnitude;
use deepskylog\AstronomyLibrary\Targets\Jupiter;
use deepskylog\AstronomyLibrary\Targets\Mars;
use deepskylog\AstronomyLibrary\Targets\Mercury;
use deepskylog\AstronomyLibrary\Targets\Neptune;
use deepskylog\AstronomyLibrary\Targets\Saturn;
use deepskylog\AstronomyLibrary\Targets\Uranus;
use deepskylog\AstronomyLibrary\Targets\Venus;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The objects worth observing in a night, well placed in the dark part of
 * it: "Tonight's best" of the home page, and the observing lists made for a
 * night (Livewire NightListWizard), which choose the options().
 *
 * An observer with a standard telescope and location gets a personal list
 * from their contrast reserves (user_object_metrics): mostly objects they
 * have not seen with that telescope yet, easy ones and a few challenging
 * ones, and a few they have; the objects of the chosen catalogs and lists
 * first, then the most observed ones (by all observers). When the Moon is
 * up, the sky at each object is brightened with MoonlightSky, its contrast
 * reserve recalculated with that sky and its best moment the darkest one.
 * Guests (and observers without that equipment) get the most observed
 * objects that are well placed at the location of the ephemerides. Both get
 * the planets and the bright comets as well, unless left out. The deep-sky
 * objects are spread over the hours chosen (pick()).
 *
 * The list is cached per observer, equipment, night and options; for an
 * observer until they record a new observation.
 */
class TonightsBest
{
    /** Deep-sky objects must get this high (degrees) during the hours chosen. */
    public const MIN_ALTITUDE = 30.0;

    public const MIN_SOLAR_SYSTEM_ALTITUDE = 10.0;

    /** Comets are diffuse: they need more altitude than planets. */
    public const MIN_COMET_ALTITUDE = 20.0;

    /** The number of deep-sky objects of the home page. */
    public const DEEPSKY_COUNT = 15;

    public const MIN_COUNT = 5;

    public const MAX_COUNT = 100;

    /** At most this many deep-sky objects of one type in a list of DEEPSKY_COUNT, for variety. */
    public const MAX_PER_TYPE = 4;

    public const SOLAR_SYSTEM_COUNT = 5;

    public const MAX_COMETS = 2;

    /** The faintest comet on the list without a telescope to judge by. */
    public const POPULAR_COMET_LIMIT = 10.0;

    public const PERIODS = ['night', 'evening', 'midnight', 'morning', 'custom'];

    /** 'none': only objects not seen yet; 'few': a fifth may have been seen; 'any': seen or not. */
    public const SEEN_OPTIONS = ['none', 'few', 'any'];

    /** 'mixed': a third of the new objects challenging, the rest easy. */
    public const DIFFICULTIES = ['mixed', 'easy', 'challenging'];

    /**
     * Types left out unless they are chosen: stars and double stars (a
     * point would top every list; a chosen double star is taken when the
     * telescope splits it, doubleStars()), point sources and supernovae of
     * the past.
     */
    public const EXCLUDED_TYPES = ['AA1STAR', 'DS', 'QUASR', 'SNOVA'];

    /** Catalog errors, never on a list. */
    private const NONEXISTENT_TYPE = 'NONEX';

    /** The objects are spread over parts of the night of about this many hours, */
    private const SLICE_HOURS = 2.0;

    /** and at most this many parts. */
    private const MAX_SLICES = 5;

    /** Below this many personal deep-sky objects, popular ones fill the list. */
    private const MIN_PERSONAL = 10;

    /** Contrast reserves above which an object is easy, or at least visible (ContrastReserveCalculator). */
    private const EASY_ABOVE = 0.5;

    private const VISIBLE_ABOVE = 0.1;

    /**
     * A double star has no contrast reserve: the verdict of
     * DoubleStarResolvability stands in for it, so a chosen double star is
     * easy or challenging like the other objects.
     */
    private const DOUBLE_STAR_RESERVES = ['easy' => 1.1, 'split' => 0.6, 'difficult' => 0.3];

    /** The objects whose contrast reserve is recalculated under the Moon. */
    private const MOON_POOL = 300;

    private const MOON_POOL_CHALLENGING = 150;

    /** The most observed objects considered for a popular list (and the objects of the chosen sources). */
    private const POPULAR_POOL = 2000;

    /** A sky this bright (magnitudes per square arcsecond) only shows bright popular objects. */
    private const BRIGHT_SKY = 20.0;

    private const BRIGHT_SKY_MAX_MAGNITUDE = 9.0;

    private const CACHE_SECONDS = 3600;

    /** While metrics are being calculated, the list is made again soon. */
    private const PENDING_CACHE_SECONDS = 300;

    public const PLANETS = [
        'mercury' => Mercury::class,
        'venus'   => Venus::class,
        'mars'    => Mars::class,
        'jupiter' => Jupiter::class,
        'saturn'  => Saturn::class,
        'uranus'  => Uranus::class,
        'neptune' => Neptune::class,
    ];

    /**
     * The options, completed with the defaults of the home page and limited
     * to valid values.
     *
     * The deep-sky objects are of the types given, else of all types but the
     * EXCLUDED_TYPES; 'count' is their number, the planets and comets
     * (SOLAR_SYSTEM_COUNT at most) come on top. 'exclude' holds deep-sky
     * objects (objects.name) to leave out: those of the earlier nights of a
     * starparty, so each night gets a list of its own.
     *
     * @return array{night: ?string, period: string, from: ?string, until: ?string, types: list<string>,
     *     sources: list<string>, sources_only: bool, seen: string, difficulty: string, count: int,
     *     deepsky: bool, planets: bool, comets: bool, exclude: list<string>}
     */
    public static function options(array $options = []): array
    {
        $time  = fn ($value) => is_string($value) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : null;
        $night = $options['night'] ?? null;

        return [
            'night'        => is_string($night) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $night) && strtotime($night) !== false ? $night : null,
            'period'       => in_array($options['period'] ?? null, self::PERIODS, true) ? $options['period'] : 'night',
            'from'         => $time($options['from'] ?? null),
            'until'        => $time($options['until'] ?? null),
            'types'        => array_values(array_unique(array_filter(array_map('strval', (array) ($options['types'] ?? [])), fn (string $type) => $type !== '' && $type !== self::NONEXISTENT_TYPE))),
            'sources'      => array_values(array_unique(array_filter(array_map('strval', (array) ($options['sources'] ?? [])), fn (string $key) => preg_match('/^(catalog:.+|list:\d+)$/', $key) === 1))),
            'sources_only' => (bool) ($options['sources_only'] ?? false),
            'seen'         => in_array($options['seen'] ?? null, self::SEEN_OPTIONS, true) ? $options['seen'] : 'few',
            'difficulty'   => in_array($options['difficulty'] ?? null, self::DIFFICULTIES, true) ? $options['difficulty'] : 'mixed',
            'count'        => max(self::MIN_COUNT, min(self::MAX_COUNT, (int) ($options['count'] ?? self::DEEPSKY_COUNT))),
            'deepsky'      => (bool) ($options['deepsky'] ?? true),
            'planets'      => (bool) ($options['planets'] ?? true),
            'comets'       => (bool) ($options['comets'] ?? true),
            'exclude'      => array_values(array_unique(array_filter(array_map('strval', (array) ($options['exclude'] ?? [])), fn (string $name) => $name !== ''))),
        ];
    }

    /**
     * The list for the observer (null for a guest), or null when there is no
     * location to calculate it for: the location given (the site of a
     * starparty, which need not be stored), else that of the ephemerides.
     *
     * @param  array<string, mixed>  $options  see options()
     * @return array{mode: string, night: string, location: ?string, instrument: ?string, latitude: float,
     *     dark: ?array{from: string, until: string, twilight: string}, period: ?array{from: string, until: string},
     *     moon: ?array{fraction: float, from: string, until: string}, objects: list<array<string, mixed>>, pending: bool}|null
     */
    public static function for(?User $user, array $options = [], ?Location $location = null): ?array
    {
        $location ??= Location::forEphemerides($user);
        if (! $location || ! $location->hasValidCoordinates()) {
            return null;
        }
        $options    = self::options($options);
        $timezone   = EphemerisDate::timezone($location);
        $night      = $options['night'] ?? EphemerisDate::chosen($timezone) ?? EphemerisDate::today($timezone);
        $instrument = $user ? self::instrument($user, $location) : null;
        $lensId     = $instrument ? $user->standardLensId() : null;

        // v2: a magnitude of 0 is unknown (ContrastReserveCalculator::target())
        $key = 'tonights_best:v2:'.md5(json_encode([
            $user?->id, $instrument?->id, $location->id, (float) $location->latitude, (float) $location->longitude, $lensId, $night, $timezone, $options,
            // a new observation changes what the observer has seen
            $user ? DB::table('observations')->where('observer_username', $user->username)->max('id') : null,
        ]));
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $result = (new self($user, $location, $instrument, $lensId, $night, $timezone, $options))->build();
        Cache::put($key, $result, $result['pending'] ? self::PENDING_CACHE_SECONDS : self::CACHE_SECONDS);

        return $result;
    }

    /**
     * Make an observing list of the observer with the objects of a result of
     * for(), in the order to observe them: the deep-sky objects, planets and
     * comets, under the names the list tables match on (search_index.name),
     * each with its moment. Null when there are none.
     */
    public static function createList(User $owner, array $result, ?string $name = null, bool $public = false): ?ObservingList
    {
        $names = collect($result['objects'] ?? [])->map(fn (array $object) => $object['list_name'] ?? $object['name']);
        if ($names->isEmpty()) {
            return null;
        }
        // the moment to observe each object, when the result knows its time zone
        $timezone = $result['timezone'] ?? null;
        $details  = $timezone === null ? [] : collect($result['objects'])
            ->mapWithKeys(fn (array $object) => [($object['list_name'] ?? $object['name']) => ['at' => $object['timestamp'] ?? null]])
            ->all();

        return ObservingList::createWithObjects($owner, trim((string) $name) ?: self::listName($result), $names, self::listDescription($result), $public, $details, [
            'night'       => $timezone === null ? null : $result['night'],
            'timezone'    => $timezone,
            'location_id' => $result['location_id'] ?? null,
        ]);
    }

    /**
     * The default name of a list made for the night of the result.
     */
    public static function listName(array $result): string
    {
        return __('Night of :date', ['date' => Carbon::parse($result['night'])->locale(app()->getLocale())->isoFormat('LL')]);
    }

    /**
     * What a list made for the night of the result was made for.
     */
    public static function listDescription(array $result): string
    {
        $parts = [__('Made for the night of :date.', ['date' => Carbon::parse($result['night'])->locale(app()->getLocale())->isoFormat('LL')])];
        if ($result['period'] ?? null) {
            $parts[] = __('The objects are well placed between :from and :until.', $result['period']);
        }
        $parts[] = $result['mode'] === 'personal'
            ? __('Chosen for :instrument from :location.', ['instrument' => $result['instrument'], 'location' => $result['location']])
            : __('Chosen for a latitude of :latitude°.', ['latitude' => $result['latitude']]);
        if ($result['moon'] ?? null) {
            $parts[] = __('The Moon is :fraction% illuminated.', ['fraction' => (int) round($result['moon']['fraction'] * 100)]);
        }

        return implode(' ', $parts);
    }

    /**
     * The standard telescope, when the list can be made for it: the
     * location is the observer's standard location and the telescope has an
     * aperture.
     */
    public static function instrument(User $user, Location $location): ?Instrument
    {
        $instrument = $user->standardInstrument;

        return $instrument && $location->exists && (int) $user->stdlocation === (int) $location->id && (float) $instrument->aperture_mm > 0
            ? $instrument
            : null;
    }

    /** The dark hours of the night. */
    private ?NightWindow $dark;

    /** The hours chosen of them. */
    private ?NightWindow $window = null;

    private ?MoonlightSky $moon = null;

    private float $darkSqm;

    /** @var array<string, string> 'table|key' (lower case) => 'seen' or 'seen_other' */
    private array $seen = [];

    /** @var array<string, true> the objects of the chosen catalogs and lists */
    private array $priority = [];

    /** @var array<string, true> the objects to leave out (options 'exclude') */
    private array $excluded = [];

    private bool $pending = false;

    private function __construct(
        private ?User $user,
        private Location $location,
        private ?Instrument $instrument,
        private ?int $lensId,
        private string $night,
        private string $timezone,
        private array $options,
    ) {
        $this->dark     = NightWindow::for((float) $location->latitude, (float) $location->longitude, $night, $timezone);
        $sqm            = $location->getSqm();
        $this->darkSqm  = is_numeric($sqm) && (float) $sqm > 0 ? (float) $sqm : Magnitude::nelmToSqm(FieldViewBuilder::DEFAULT_NELM);
        $this->excluded = array_fill_keys($options['exclude'], true);
    }

    private function build(): array
    {
        $result = [
            'mode'        => $this->instrument ? 'personal' : 'popular',
            'night'       => $this->night,
            'location'    => $this->instrument ? html_entity_decode((string) $this->location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
            'instrument'  => $this->instrument ? html_entity_decode((string) $this->instrument->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
            'latitude'    => round((float) $this->location->latitude),
            'timezone'    => $this->timezone,
            'location_id' => $this->location->id,
            'dark'        => null,
            'period'      => null,
            'moon'        => null,
            'objects'     => [],
            'pending'     => false,
        ];
        if ($this->dark === null) {
            return $result;
        }
        $result['dark'] = ['from' => $this->time($this->dark->start), 'until' => $this->time($this->dark->end), 'twilight' => $this->dark->twilight];

        $this->window = $this->period();
        if ($this->window === null) {
            return $result;
        }
        $result['period'] = ['from' => $this->time($this->window->start), 'until' => $this->time($this->window->end)];

        $this->moon = new MoonlightSky($this->window, (float) $this->location->longitude, (float) ($this->location->elevation ?? 0.0));
        if ($up = $this->moon->upBetween()) {
            $result['moon'] = ['fraction' => round($this->moon->illuminatedFraction(), 2), 'from' => $this->time($up[0]), 'until' => $this->time($up[1])];
        } else {
            $this->moon = null;
        }
        $this->seen     = $this->seenTargets();
        $this->priority = $this->sourceObjects();

        $count   = $this->options['count'];
        $deepsky = $this->instrument && $this->options['deepsky'] ? $this->personalDeepsky() : [];
        if ($this->options['deepsky'] && (count($deepsky) < min($count, self::MIN_PERSONAL) || ($this->pending && count($deepsky) < $count))) {
            $chosen  = array_column($deepsky, 'name');
            $popular = array_filter($this->popularDeepsky(), fn (array $object) => ! in_array($object['name'], $chosen, true));
            if ($this->instrument) {
                // Objects with a contrast reserve were judged already: only the ones still being calculated fill in.
                $judged = DB::table('user_object_metrics as m')
                    ->where(fn (Builder $query) => $this->joinMetricsWhere($query))
                    ->whereIn('m.object_name', array_column($popular, 'name'))
                    ->pluck('m.object_name')
                    ->flip();
                // Double stars were judged by whether the telescope splits them (doubleStars())
                $popular = array_filter($popular, fn (array $object) => ! isset($judged[$object['name']]) && ContrastReserveCalculator::appliesTo($object['type_code']));
            }
            $deepsky = array_merge($deepsky, $this->pick(array_values($popular), $count - count($deepsky), $deepsky));
        }

        $objects = array_merge($deepsky, $this->options['planets'] || $this->options['comets'] ? $this->solarSystem() : []);
        // in the order to observe them
        usort($objects, fn (array $a, array $b) => [$a['timestamp'], $a['name']] <=> [$b['timestamp'], $b['name']]);
        // the moment for the field views
        $result['objects'] = array_map(fn (array $object) => [...$object, 'moment' => Carbon::createFromTimestamp($object['timestamp'], $this->timezone)->format('Y-m-d\TH:i')], $objects);
        $result['pending'] = $this->pending;

        return $result;
    }

    /**
     * The hours chosen of the dark part of the night: all of it, a third of
     * it (evening, around midnight, morning), or from and until a time.
     */
    private function period(): ?NightWindow
    {
        $dark  = $this->dark;
        $third = intdiv($dark->end - $dark->start, 3);

        return match ($this->options['period']) {
            'evening'  => $dark->between($dark->start, $dark->start + $third),
            'midnight' => $dark->between($dark->start + $third, $dark->end - $third),
            'morning'  => $dark->between($dark->end - $third, $dark->end),
            'custom'   => $dark->between($this->moment($this->options['from'], $dark->start), $this->moment($this->options['until'], $dark->end)),
            default    => $dark,
        };
    }

    /**
     * The timestamp of a time of the night: times after noon fall on the
     * evening of the night, earlier times on the next morning.
     */
    private function moment(?string $time, int $default): int
    {
        if ($time === null) {
            return $default;
        }
        $date = (int) substr($time, 0, 2) >= 12 ? $this->night : Carbon::parse($this->night)->addDay()->toDateString();

        return Carbon::parse($date.' '.$time, $this->timezone)->getTimestamp();
    }

    /**
     * The deep-sky objects of the observer's list, from their contrast
     * reserves with the standard telescope; queues the missing ones.
     *
     * @return list<array<string, mixed>>
     */
    private function personalDeepsky(): array
    {
        $candidates = [];
        $rows       = $this->objectQuery()
            ->join('user_object_metrics as m', fn (JoinClause $join) => $this->joinMetrics($join))
            ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->where('m.contrast_reserve', '>', self::VISIBLE_ABOVE)
            ->get(['o.name', 'o.slug', 'o.type', 'o.ra', 'o.decl', 'o.mag', 'o.diam1', 'o.diam2', 't.name as type_name', 'c.name as constellation', 'm.contrast_reserve', 's.observations']);
        foreach ($rows as $row) {
            if (! isset($candidates[$row->name]) && $this->allowed($row->name) && ($object = $this->placed($row))) {
                $object['contrast_reserve'] = (float) $row->contrast_reserve;
                $candidates[$row->name]     = $object;
            }
        }
        foreach ($this->doubleStars() as $name => $object) {
            $candidates[$name] ??= $object;
        }
        $this->queueMissingMetrics();

        $candidates = array_values($candidates);
        if ($this->moon) {
            $candidates = $this->underTheMoon($candidates);
        }
        foreach ($candidates as &$object) {
            $object['category'] = ContrastReserveCalculator::category($object['contrast_reserve']);
        }
        unset($object);

        // The objects of the chosen sources first, then the ones observed
        // most by all observers (the showpieces).
        usort($candidates, self::byPriority(...));
        $count      = $this->options['count'];
        $difficulty = $this->options['difficulty'];
        $maxSeen    = $this->maxSeen();
        $new        = array_values(array_filter($candidates, fn (array $object) => $this->options['seen'] === 'any' || $object['status'] !== 'seen'));
        $seen       = array_values(array_filter($candidates, fn (array $object) => $object['status'] === 'seen' && $object['contrast_reserve'] > self::EASY_ABOVE));
        $easy       = array_values(array_filter($new, fn (array $object) => $object['contrast_reserve'] > self::EASY_ABOVE));
        $hard       = array_values(array_filter($new, fn (array $object) => $object['contrast_reserve'] <= self::EASY_ABOVE));
        $allowed    = match ($difficulty) {
            'easy'        => $easy,
            'challenging' => $hard,
            default       => $new,
        };

        $newCount  = $count - $maxSeen;
        $hardCount = $difficulty === 'mixed' ? (int) round($newCount / 3) : 0;
        $picked    = $this->pick($difficulty === 'challenging' ? $hard : $easy, $newCount - $hardCount, []);
        $picked    = array_merge($picked, $this->pick($hard, $hardCount, $picked));
        $picked    = array_merge($picked, $this->pick($allowed, $newCount - count($picked), $picked));
        $picked    = array_merge($picked, $this->pick($seen, $maxSeen, $picked));

        // Too few seen objects: more new ones.
        return array_merge($picked, $this->pick($allowed, $count - count($picked), $picked));
    }

    /**
     * The most observed deep-sky objects that are well placed, for guests
     * and observers without a standard telescope, and the objects of the
     * chosen sources.
     *
     * @return list<array<string, mixed>>
     */
    private function popularDeepsky(): array
    {
        $columns = ['o.name', 'o.slug', 'o.type', 'o.ra', 'o.decl', 'o.mag', 't.name as type_name', 'c.name as constellation', 's.observations'];
        $rows    = $this->options['sources_only'] ? collect() : $this->objectQuery()
            ->join('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->orderByDesc('s.observations')
            ->orderBy('o.name')
            ->limit(self::POPULAR_POOL)
            ->get($columns);
        foreach (array_chunk(array_slice(array_keys($this->priority), 0, self::POPULAR_POOL), 500) as $names) {
            $rows = $rows->concat($this->objectQuery()
                ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
                ->whereIn('o.name', $names)
                ->get($columns));
        }

        $candidates = [];
        foreach ($rows as $row) {
            if (isset($candidates[$row->name]) || ! $this->allowed($row->name) || ! ($object = $this->placed($row))) {
                continue;
            }
            if ($this->moon) {
                $object = $this->darkestMoment($object);
                $bright = (float) $row->mag > 0 && (float) $row->mag <= self::BRIGHT_SKY_MAX_MAGNITUDE;
                if ($object === null || ($object['sqm'] < self::BRIGHT_SKY && ! $bright)) {
                    continue;
                }
            }
            $candidates[$row->name] = $object;
        }

        $candidates = array_values($candidates);
        usort($candidates, self::byPriority(...));
        $count   = $this->options['count'];
        $maxSeen = $this->maxSeen();
        $new     = array_values(array_filter($candidates, fn (array $object) => $this->options['seen'] === 'any' || $object['status'] !== 'seen'));
        $seen    = array_values(array_filter($candidates, fn (array $object) => $object['status'] === 'seen'));
        $picked  = $this->pick($new, $count - $maxSeen, []);
        $picked  = array_merge($picked, $this->pick($seen, $maxSeen, $picked));

        return array_merge($picked, $this->pick($new, $count - count($picked), $picked));
    }

    /**
     * How many objects the observer has seen may be on the list (with 'any'
     * they are new ones).
     */
    private function maxSeen(): int
    {
        return $this->options['seen'] === 'few' && $this->user ? max(1, (int) round($this->options['count'] / 5)) : 0;
    }

    /**
     * Whether the object may be on the list: not one to leave out, and with
     * sources_only, only the objects of the chosen sources.
     */
    private function allowed(string $name): bool
    {
        return ! isset($this->excluded[$name]) && (! $this->options['sources_only'] || isset($this->priority[$name]));
    }

    /**
     * The objects that can get high enough at the latitude, of the chosen
     * types, with their type and constellation.
     */
    private function objectQuery(): Builder
    {
        $latitude = (float) $this->location->latitude;

        return DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->when(
                $this->options['types'] !== [],
                fn (Builder $query) => $query->whereIn('o.type', $this->options['types']),
                fn (Builder $query) => $query->whereNotIn('o.type', [...self::EXCLUDED_TYPES, self::NONEXISTENT_TYPE])
            )
            ->whereBetween('o.decl', [$latitude - (90.0 - self::MIN_ALTITUDE), $latitude + (90.0 - self::MIN_ALTITUDE)]);
    }

    private function joinMetrics(JoinClause $join): JoinClause
    {
        return $this->joinMetricsWhere($join->on('m.object_name', '=', 'o.name'));
    }

    /**
     * The metrics (m) of the observer's equipment.
     */
    private function joinMetricsWhere(Builder $query): Builder
    {
        return $query
            ->where('m.user_id', $this->user->id)
            ->where('m.instrument_id', $this->instrument->id)
            ->where('m.location_id', $this->location->id)
            ->when($this->lensId === null, fn ($query) => $query->whereNull('m.lens_id'), fn ($query) => $query->where('m.lens_id', $this->lensId));
    }

    /**
     * The chosen double stars the standard telescope splits, with the
     * contrast reserve of their verdict (DOUBLE_STAR_RESERVES).
     *
     * @return array<string, array<string, mixed>>
     */
    private function doubleStars(): array
    {
        if (! in_array('DS', $this->options['types'], true)) {
            return [];
        }

        $candidates = [];
        $rows       = $this->objectQuery()
            ->where('o.type', 'DS')
            ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->get(['o.name', 'o.slug', 'o.type', 'o.ra', 'o.decl', 'o.mag', 'o.subr', 'o.pa', 'o.diam1', 'o.diam2', 't.name as type_name', 'c.name as constellation', 's.observations']);
        $magnifications = ContrastReserveCalculator::magnifications($this->instrument, $this->user, ContrastReserveCalculator::lensFactor($this->lensId));
        foreach ($rows as $row) {
            if (isset($candidates[$row->name]) || ! $this->allowed($row->name) || ! ($object = $this->placed($row))) {
                continue;
            }
            $split   = DoubleStarResolvability::forObject($row, $this->instrument, $this->location, $this->user, 1.0, $magnifications);
            $reserve = self::DOUBLE_STAR_RESERVES[$split['verdict'] ?? ''] ?? null;
            if ($reserve !== null) {
                $object['contrast_reserve'] = $reserve;
                $candidates[$row->name]     = $object;
            }
        }

        return $candidates;
    }

    /**
     * Queue the metrics of the objects that are well placed in the hours
     * chosen but have none for this equipment yet.
     */
    private function queueMissingMetrics(): void
    {
        $missing = $this->objectQuery()
            ->where('o.type', '<>', 'DS')
            ->leftJoin('user_object_metrics as m', fn (JoinClause $join) => $this->joinMetrics($join))
            ->whereNull('m.id')
            ->get(['o.name', 'o.ra', 'o.decl'])
            ->filter(fn ($row) => $this->allowed($row->name) && $this->window->highest((float) $row->ra * 15.0, (float) $row->decl)['altitude'] >= self::MIN_ALTITUDE)
            ->pluck('name');
        if ($missing->isEmpty()) {
            return;
        }

        $token         = ObjectMetricsQueue::queueMissing($this->user->id, $this->instrument->id, $this->location->id, $this->lensId, $missing);
        $this->pending = ObjectMetricsQueue::pending($token);
    }

    /**
     * The objects (objects.name) of the chosen catalogs and observing lists;
     * only lists the observer may see.
     *
     * @return array<string, true>
     */
    private function sourceObjects(): array
    {
        $catalogs = [];
        $lists    = [];
        foreach ($this->options['sources'] as $key) {
            [$type, $value] = explode(':', $key, 2);
            if ($type === 'catalog') {
                $catalogs[] = $value;
            } else {
                $lists[] = (int) $value;
            }
        }

        $names = collect();
        if ($catalogs !== []) {
            $names = $names->concat(DB::table('objectnames')->whereIn('catalog', $catalogs)->distinct()->pluck('objectname'));
        }
        if ($lists !== []) {
            $visible = DB::table('observing_lists')
                ->whereIn('id', $lists)
                ->where(fn (Builder $query) => $query->where('public', true)->when($this->user, fn (Builder $query) => $query->orWhere('owner_user_id', $this->user->id)))
                ->pluck('id');
            $names = $names->concat(DB::table('observing_list_items')->whereIn('observing_list_id', $visible)->distinct()->pluck('object_name'));
        }

        return $names->mapWithKeys(fn ($name) => [(string) $name => true])->all();
    }

    /**
     * The entry of a deep-sky object at its highest moment of the hours
     * chosen, or null when it does not get high enough.
     */
    private function placed(object $row): ?array
    {
        $ra      = (float) $row->ra * 15.0;
        $highest = $this->window->highest($ra, (float) $row->decl);
        if ($highest['altitude'] < self::MIN_ALTITUDE) {
            return null;
        }

        return [
            'kind'             => 'deepsky',
            'name'             => (string) $row->name,
            'slug'             => (string) ($row->slug ?: Str::slug((string) $row->name)),
            'type'             => $row->type_name ?: (string) $row->type,
            'type_code'        => (string) $row->type,
            'constellation'    => $row->constellation,
            'magnitude'        => is_numeric($row->mag) && (float) $row->mag > 0 && (float) $row->mag < 99 ? round((float) $row->mag, 1) : null,
            'raw_magnitude'    => $row->mag,
            'ra'               => $ra,
            'decl'             => (float) $row->decl,
            'diam1'            => $row->diam1 ?? null,
            'diam2'            => $row->diam2 ?? null,
            'altitude'         => (int) round($highest['altitude']),
            'timestamp'        => $highest['timestamp'],
            'time'             => $this->time($highest['timestamp']),
            'sqm'              => null,
            'contrast_reserve' => null,
            'category'         => null,
            'observations'     => (int) ($row->observations ?? 0),
            'priority'         => isset($this->priority[$row->name]),
            'status'           => $this->status('objects', (string) $row->name),
        ];
    }

    /**
     * The candidates with the Moon up: of the best ones, the contrast reserve
     * at their darkest moment, recalculated for the sky the Moon lights.
     * Objects that are no longer visible drop out.
     *
     * @param  list<array<string, mixed>>  $candidates
     * @return list<array<string, mixed>>
     */
    private function underTheMoon(array $candidates): array
    {
        usort($candidates, self::byPriority(...));
        $pool = array_slice($candidates, 0, self::MOON_POOL);
        $hard = array_values(array_filter(array_slice($candidates, self::MOON_POOL), fn (array $object) => $object['contrast_reserve'] <= self::EASY_ABOVE));
        $pool = array_merge($pool, array_slice($hard, 0, self::MOON_POOL_CHALLENGING));

        $magnifications = ContrastReserveCalculator::magnifications($this->instrument, $this->user, ContrastReserveCalculator::lensFactor($this->lensId));
        $visible        = [];
        foreach ($pool as $object) {
            $object = $this->darkestMoment($object);
            if ($object === null) {
                continue;
            }
            if ($object['sqm'] < $this->darkSqm - 0.05 && ContrastReserveCalculator::appliesTo($object['type_code'])) {
                $reserve = ContrastReserveCalculator::calculate(
                    ContrastReserveCalculator::target($object['diam1'], $object['diam2'], $object['raw_magnitude']),
                    $this->instrument,
                    $this->location,
                    $this->user,
                    1.0,
                    $magnifications,
                    $object['sqm'],
                    $object['altitude']
                )['contrast_reserve'];
                if ($reserve === null) {
                    continue;
                }
                $object['contrast_reserve'] = $reserve;
            }
            if ($object['contrast_reserve'] > self::VISIBLE_ABOVE) {
                $visible[] = $object;
            }
        }

        return $visible;
    }

    /**
     * The objects of the chosen sources first, then the most observed
     * objects, then the highest contrast reserve.
     */
    private static function byPriority(array $a, array $b): int
    {
        return [$b['priority'], $b['observations'], $b['contrast_reserve']] <=> [$a['priority'], $a['observations'], $a['contrast_reserve']];
    }

    /**
     * The object at the moment its sky is darkest while it is high enough
     * (the higher of equally dark moments), with the sky brightness then.
     */
    private function darkestMoment(array $object): ?array
    {
        $best = $this->moon->darkestMoment($object['ra'], $object['decl'], $this->darkSqm, self::MIN_ALTITUDE);
        if ($best === null) {
            return null;
        }

        return [...$object, 'timestamp' => $best['timestamp'], 'time' => $this->time($best['timestamp']), 'sqm' => $best['sqm'], 'altitude' => (int) round($best['altitude'])];
    }

    /**
     * Up to $count objects of the list, leaving out the ones already chosen
     * and the types that have their share already: MAX_PER_TYPE of
     * DEEPSKY_COUNT objects, no limit when only one type was chosen or for
     * the objects of the chosen sources.
     *
     * The objects are spread over the hours chosen: each next one comes from
     * the part of the night (slice()) with the fewest objects so far, the
     * first of the list there. Without this, the most observed objects that
     * stand highest at the start of the dark hours would fill the list.
     *
     * @param  list<array<string, mixed>>  $objects
     * @param  list<array<string, mixed>>  $chosen
     * @return list<array<string, mixed>>
     */
    private function pick(array $objects, int $count, array $chosen): array
    {
        $perType = count($this->options['types']) === 1
            ? PHP_INT_MAX
            : max(self::MAX_PER_TYPE, (int) ceil($this->options['count'] * self::MAX_PER_TYPE / self::DEEPSKY_COUNT));
        $names  = array_flip(array_column($chosen, 'name'));
        $types  = array_count_values(array_column($chosen, 'type_code'));
        $filled = array_fill(0, $this->slices(), 0);
        foreach ($chosen as $object) {
            $filled[$this->slice($object['timestamp'])]++;
        }

        // The objects of each part of the night, in the order of the list.
        $queues = [];
        foreach ($objects as $object) {
            if (! isset($names[$object['name']])) {
                $queues[$this->slice($object['timestamp'])][] = $object;
            }
        }

        $picked = [];
        while (count($picked) < $count && $queues !== []) {
            $slice = null;
            foreach (array_keys($queues) as $candidate) {
                if ($slice === null || $filled[$candidate] < $filled[$slice]) {
                    $slice = $candidate;
                }
            }
            $object = array_shift($queues[$slice]);
            if ($queues[$slice] === []) {
                unset($queues[$slice]);
            }
            // the objects of the chosen sources do not count for the variety
            if (isset($names[$object['name']]) || (! ($object['priority'] ?? false) && ($types[$object['type_code']] ?? 0) >= $perType)) {
                continue;
            }
            $picked[]                    = $object;
            $names[$object['name']]      = true;
            $types[$object['type_code']] = ($types[$object['type_code']] ?? 0) + 1;
            $filled[$slice]++;
        }

        return $picked;
    }

    /**
     * The number of parts the hours chosen are cut in to spread the objects:
     * about SLICE_HOURS each, at most MAX_SLICES.
     */
    private function slices(): int
    {
        $hours = ($this->window->end - $this->window->start) / 3600.0;

        return max(1, min(self::MAX_SLICES, (int) round($hours / self::SLICE_HOURS)));
    }

    /** The part of the hours chosen a moment (timestamp) falls in. */
    private function slice(int $timestamp): int
    {
        $slices = $this->slices();
        $length = max(1, $this->window->end - $this->window->start) / $slices;

        return max(0, min($slices - 1, (int) floor(($timestamp - $this->window->start) / $length)));
    }

    /**
     * The planets and the bright comets above MIN_SOLAR_SYSTEM_ALTITUDE
     * (MIN_COMET_ALTITUDE) in the hours chosen, of the kinds chosen (with
     * sources_only, only the ones of the chosen lists): the brightest comets
     * first, then the planets from bright to faint.
     *
     * @return list<array<string, mixed>>
     */
    private function solarSystem(): array
    {
        $cometLimit = self::POPULAR_COMET_LIMIT;
        if ($this->instrument) {
            $nelm = $this->location->getNelm((float) ($this->user->fstOffset ?? 0));
            $nelm = is_numeric($nelm) && $nelm > 0 ? (float) $nelm : FieldViewBuilder::DEFAULT_NELM;
            // a comet is diffuse: some magnitudes brighter than the faintest star
            $cometLimit = TelescopicLimitingMagnitude::calculate($nelm, (float) $this->instrument->aperture_mm, null, (float) $this->instrument->obstruction_perc, $this->instrument->hasMirrors()) - 3.0;
        }

        $comets  = [];
        $planets = [];
        foreach (self::bodies($this->night) as $body) {
            if (! $this->options[$body['kind'] === 'comet' ? 'comets' : 'planets'] || ! $this->allowed($body['list_name'])) {
                continue;
            }
            if ($body['kind'] === 'comet' && ($body['magnitude'] === null || $body['magnitude'] > $cometLimit)) {
                continue;
            }
            $highest = $this->window->highest($body['ra'], $body['decl']);
            if ($highest['altitude'] < ($body['kind'] === 'comet' ? self::MIN_COMET_ALTITUDE : self::MIN_SOLAR_SYSTEM_ALTITUDE)) {
                continue;
            }
            $object = [
                'kind'             => $body['kind'],
                'name'             => $body['name'],
                'list_name'        => $body['list_name'],
                'slug'             => $body['slug'],
                'type'             => $body['kind'] === 'comet' ? 'Comet' : 'Planet',
                'type_code'        => $body['kind'],
                'constellation'    => null,
                'magnitude'        => $body['magnitude'] === null ? null : round($body['magnitude'], 1),
                'altitude'         => (int) round($highest['altitude']),
                'timestamp'        => $highest['timestamp'],
                'time'             => $this->time($highest['timestamp']),
                'sqm'              => null,
                'contrast_reserve' => null,
                'category'         => null,
                'status'           => $this->status($body['kind'] === 'comet' ? 'cometobjects' : 'planets', (string) $body['key']),
            ];
            if ($body['kind'] === 'comet') {
                $comets[] = $object;
            } else {
                $planets[] = $object;
            }
        }
        $byMagnitude = fn (array $a, array $b) => ($a['magnitude'] ?? 99) <=> ($b['magnitude'] ?? 99);
        usort($comets, $byMagnitude);
        usort($planets, $byMagnitude);

        $chosen = array_slice($comets, 0, self::MAX_COMETS);
        $chosen = array_merge($chosen, array_slice($planets, 0, self::SOLAR_SYSTEM_COUNT - count($chosen)));

        return array_merge($chosen, array_slice($comets, self::MAX_COMETS, self::SOLAR_SYSTEM_COUNT - count($chosen)));
    }

    /**
     * The planets and the comets brighter than magnitude 13 with their
     * positions (degrees) and magnitudes at midnight (UT) of the night, the
     * same for every location and cached for the night.
     *
     * list_name is the name an observing list holds it under.
     *
     * @return list<array{kind: string, name: string, list_name: string, slug: string, key: string, ra: float, decl: float, magnitude: ?float}>
     */
    public static function bodies(string $night): array
    {
        return Cache::remember('tonights_best:bodies:v2:'.$night, 6 * 3600, function () use ($night) {
            $date   = Carbon::parse($night, 'UTC')->addDay()->startOfDay();
            $geo    = new GeographicalCoordinates(0.0, 0.0);
            $bodies = [];

            $slugs = DB::table('planets')->pluck('slug', 'name')->mapWithKeys(fn ($slug, $name) => [mb_strtolower((string) $slug) => [(string) $name, (string) $slug]]);
            foreach (self::PLANETS as $slug => $class) {
                try {
                    $planet = new $class();
                    $planet->calculateEquatorialCoordinates($date->copy(), $geo, 0.0);
                    $coordinates       = $planet->getEquatorialCoordinatesToday();
                    [$name, $pageSlug] = $slugs[$slug] ?? [ucfirst($slug), $slug];
                    $bodies[]          = [
                        'kind'      => 'planet',
                        'name'      => $name,
                        'list_name' => $name,
                        'slug'      => $pageSlug,
                        'key'       => $pageSlug,
                        'ra'        => (float) $coordinates->getRA()->getCoordinate() * 15.0,
                        'decl'      => (float) $coordinates->getDeclination()->getCoordinate(),
                        'magnitude' => (float) $planet->magnitude($date->copy()),
                    ];
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            foreach (DB::table('cometobjects')->get(['id', 'name', 'slug']) as $comet) {
                try {
                    $elements = CometCoordinates::orbitalElements($comet->name);
                    $target   = $elements ? CometCoordinates::target($elements) : null;
                    if (! $target) {
                        continue;
                    }
                    $magnitude = CometCoordinates::magnitude($target, $date);
                    if ($magnitude === null || $magnitude > 13.0) {
                        continue;
                    }
                    $coordinates = CometCoordinates::calculate($target, $date, $geo);
                    $bodies[]    = [
                        'kind'      => 'comet',
                        'name'      => html_entity_decode((string) $comet->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'list_name' => (string) $comet->name,
                        'slug'      => (string) ($comet->slug ?: Str::slug((string) $comet->name)),
                        'key'       => (string) $comet->id,
                        'ra'        => (float) $coordinates->getRA()->getCoordinate() * 15.0,
                        'decl'      => (float) $coordinates->getDeclination()->getCoordinate(),
                        'magnitude' => $magnitude,
                    ];
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return $bodies;
        });
    }

    /**
     * What the observer has seen, per target: with the standard telescope
     * ('seen'), or only with other instruments ('seen_other'; without a
     * telescope every observation counts as 'seen'). Observations of objects
     * that were not seen do not count.
     *
     * @return array<string, string>
     */
    private function seenTargets(): array
    {
        if (! $this->user) {
            return [];
        }
        $instrumentId = $this->instrument?->id;
        $rows         = DB::table('observations')
            ->where('observer_username', $this->user->username)
            ->whereIn('target_table', ['objects', 'cometobjects', 'planets'])
            ->whereNotExists(function (Builder $details) {
                $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN);
            })
            ->groupBy('target_table', 'target_key')
            ->selectRaw('target_table, target_key, MAX(CASE WHEN instrument_id = ? THEN 1 ELSE 0 END) as with_instrument', [$instrumentId ?? 0])
            ->get();

        $seen = [];
        foreach ($rows as $row) {
            $seen[$row->target_table.'|'.mb_strtolower((string) $row->target_key)] = $instrumentId === null || $row->with_instrument ? 'seen' : 'seen_other';
        }

        return $seen;
    }

    /**
     * 'seen', 'seen_other' or 'new' for an observer, null for a guest.
     */
    private function status(string $table, string $key): ?string
    {
        if (! $this->user) {
            return null;
        }

        return $this->seen[$table.'|'.mb_strtolower($key)] ?? 'new';
    }

    private function time(int $timestamp): string
    {
        return Carbon::createFromTimestamp($timestamp, $this->timezone)->format('H:i');
    }
}
