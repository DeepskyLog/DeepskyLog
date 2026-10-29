<?php

namespace App\Services;

use App\Models\DeepskyObject;
use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\Location;
use App\Models\ObjectFindingNote;
use App\Models\Observation;
use App\Models\ObservingGoal;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\FieldViews\FieldViewRenderer;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The beginner path: a guided series of about SIZE showpieces for a new
 * observer, picked from POOL for their telescope (else a STARTER_INSTRUMENTS
 * one) and location: objects that rise high enough there and that the
 * telescope shows easily (contrast reserve, or a double star it splits),
 * spread over the year and over the kinds of objects. It is an observing
 * list with an observing goal of all its objects (beginner_path), so the
 * progress, the observer page and the page at the eyepiece come for free.
 * The path page shows each object with the month it is best seen, a hop
 * from the nearest bright star, the community's finding notes and links to
 * the field views and the observation form.
 */
class BeginnerPath
{
    /** The objects of a path. */
    public const SIZE = 30;

    /**
     * The showpieces to choose from, the most rewarding first: Messier and
     * NGC/IC objects, clusters of the southern sky, and bright, wide double
     * stars with colourful or equal pairs.
     */
    public const POOL = [
        'M 45', 'M 42', 'M 31', 'M 13', 'M 44', 'NGC 869', 'NGC 884', 'M 57', 'M 27', 'Beta Cyg',
        'NGC 5139', 'NGC 104', 'M 7', 'M 6', 'M 8', 'M 22', 'M 11', 'M 81', 'M 82', 'Zeta UMa',
        'Gamma And', 'NGC 3372', 'NGC 4755', 'IC 2602', 'M 35', 'M 36', 'M 37', 'M 38', 'M 41', 'M 3',
        'M 5', 'M 15', 'M 92', 'M 4', 'M 17', 'M 20', 'M 51', 'M 104', 'M 33', 'M 32',
        'Alpha CVn', 'Epsilon Lyr', 'NGC 7000', 'NGC 2070', 'NGC 6231', 'NGC 3532', 'Mel 111', 'Cr 399', 'NGC 457', 'M 52',
        'M 103', 'M 39', 'M 34', 'M 47', 'M 46', 'M 67', 'M 93', 'M 25', 'M 23', 'M 2',
        'M 10', 'M 12', 'M 55', 'NGC 6752', 'NGC 6397', 'NGC 362', 'NGC 2808', 'NGC 3201', 'M 16', 'M 1',
        'NGC 6543', 'NGC 3242', 'NGC 7662', 'NGC 6826', 'NGC 7009', 'M 97', 'M 110', 'M 101', 'M 64', 'M 94',
        'M 63', 'M 65', 'M 66', 'M 83', 'M 106', 'NGC 253', 'NGC 5128', 'NGC 55', 'NGC 300', 'NGC 6992',
        'NGC 2516', 'NGC 752', 'IC 4665', 'IC 4756', 'NGC 6633', 'M 48', 'M 50', 'NGC 2244', 'NGC 2264', 'NGC 2451',
        'NGC 3114', 'NGC 2547', 'NGC 3766', 'Mel 20', 'NGC 2232', 'Alpha Cen', 'Alpha Cru', 'Gamma Leo', 'Alpha Gem', 'Eta Cas',
        'Gamma Ari', 'Delta Cep', 'Gamma Del', 'Iota Cnc', 'Beta Sco', 'Alpha Her', 'Beta Mon', 'Gamma Vir', 'Sigma Ori', 'Theta Eri',
        'Delta Ori', 'Alpha Lib', 'Gamma Cru', 'Alpha Cap', 'Epsilon Boo',
    ];

    /** The kinds of POOL objects with at most this many on a path; the others have no limit. */
    public const KIND_LIMITS = ['double' => 6, 'galaxy' => 8];

    /**
     * A contrast reserve above which an object is easy (the categories of
     * ContrastReserveCalculator); objects above MIN_RESERVE only fill a path
     * that has too few easy ones.
     */
    public const EASY_RESERVE = 0.5;

    public const MIN_RESERVE = 0.1;

    /** The least altitude (degrees) an object reaches at the location, so it is not lost in the haze. */
    public const MIN_ALTITUDE = 20.0;

    /**
     * The least apparent size (arcseconds, the size times the best
     * magnification) of an object: a small planetary nebula in binoculars
     * looks like a star.
     */
    public const MIN_APPARENT_SIZE = 1000.0;

    /** A double star closer than this needs steady air: not for a beginner. */
    public const MIN_SEPARATION_ARCSEC = 2.0;

    /** The sky of a location without an SQM or limiting magnitude: a suburban sky. */
    public const DEFAULT_SQM = 20.0;

    /**
     * Instruments to choose from when the observer has none: as
     * EquipmentGain::plannedSpec().
     */
    public const STARTER_INSTRUMENTS = [
        'binoculars' => ['binoculars' => true, 'aperture' => 50, 'magnification' => 10],
        'refractor'  => ['binoculars' => false, 'aperture' => 80, 'focal_length' => 400],
        'reflector'  => ['binoculars' => false, 'aperture' => 130, 'focal_length' => 650],
        'dobsonian'  => ['binoculars' => false, 'aperture' => 200, 'focal_length' => 1200],
    ];

    public const DEFAULT_STARTER = 'reflector';

    /** The months a path runs. */
    public const MONTHS = 12;

    /** The home page invites observers with fewer observations than this. */
    public const INVITE_BELOW_OBSERVATIONS = 25;

    /** The stars a hop starts from: brighter than this, with a Bayer letter. */
    public const HOP_MAGNITUDE = 4.0;

    /** A hop from a star farther than this does not help. */
    public const HOP_MAX_DEG = 12.0;

    /** The finding notes shown per object. */
    public const FINDING_NOTES = 2;

    /** The local hour of the evening the best month of an object is chosen for. */
    private const EVENING_HOUR = 22;

    private const GALAXY_TYPES = ['GALXY', 'GALCL', 'GACAN', 'GXADN', 'GXAGC'];

    private const SUPERSCRIPTS = ['1' => '¹', '2' => '²', '3' => '³', '4' => '⁴', '5' => '⁵', '6' => '⁶', '7' => '⁷', '8' => '⁸', '9' => '⁹'];

    /** The direction from the star to the object, as a translation key. */
    private const COMPASS = ['north of', 'north-east of', 'east of', 'south-east of', 'south of', 'south-west of', 'west of', 'north-west of'];

    /**
     * The observer's current beginner path: their newest beginner goal whose
     * list still exists.
     */
    public static function current(User $user): ?ObservingGoal
    {
        $goal = ObservingGoal::where('user_id', $user->id)
            ->where('beginner_path', true)
            ->whereNotNull('observing_list_id')
            ->with('observingList')
            ->orderByDesc('id')
            ->first();

        return $goal?->observingList ? $goal->setRelation('user', $user) : null;
    }

    /**
     * The instrument a path is picked for: one of the observer's active
     * instruments ('instrument:<id>'), a starter instrument ('starter:<key>'),
     * else their standard telescope, else the DEFAULT_STARTER.
     */
    public static function instrument(User $user, ?string $choice = null): Instrument
    {
        $own = RevisitObjects::instruments($user);
        if ($choice !== null && str_starts_with($choice, 'starter:') && isset(self::STARTER_INSTRUMENTS[substr($choice, 8)])) {
            return self::starter(substr($choice, 8));
        }
        $chosen = $choice !== null && str_starts_with($choice, 'instrument:') ? $own->firstWhere('id', (int) substr($choice, 11)) : null;

        return $chosen ?? $own->firstWhere('id', (int) $user->stdtelescope) ?? self::starter(self::DEFAULT_STARTER);
    }

    /** The default choice of instrument(): the standard telescope, else the DEFAULT_STARTER. */
    public static function defaultChoice(User $user): string
    {
        $standard = RevisitObjects::instruments($user)->firstWhere('id', (int) $user->stdtelescope);

        return $standard ? 'instrument:' . $standard->id : 'starter:' . self::DEFAULT_STARTER;
    }

    public static function starter(string $key): Instrument
    {
        // without a make, as fullName() needs one
        return EquipmentGain::plannedInstrument(EquipmentGain::plannedSpec(self::STARTER_INSTRUMENTS[$key]))
            ->setRelation('instrument_make', (new InstrumentMake())->forceFill(['name' => '']));
    }

    /**
     * The objects of a new path, in the order to observe them: from the
     * current month on, the objects best seen in each month, the easiest
     * first.
     *
     * @return list<string>
     */
    public static function pick(User $user, Instrument $instrument, Location $location, ?Carbon $today = null): array
    {
        $today ??= Carbon::today();
        $seasons        = SeasonalVisibility::for($location, $today->year, $today->month, EphemerisDate::timezone($location));
        $seen           = self::seenObjects($user);
        $sqm            = is_numeric($location->getSqm()) ? null : self::DEFAULT_SQM;
        $lens           = ContrastReserveCalculator::lensFactor($instrument->lens_id);
        $magnifications = ContrastReserveCalculator::magnifications($instrument, $user, $lens);

        $candidates = [];
        foreach (self::poolRows() as $rank => $row) {
            if (isset($seen[mb_strtolower($row->name)]) || $seasons->highestAltitude((float) $row->decl) < self::MIN_ALTITUDE) {
                continue;
            }
            $tier  = self::tier($row, $instrument, $location, $user, $lens, $magnifications, $sqm);
            $month = $tier === null ? null : self::bestMonth($seasons, (float) $row->ra * 15.0, (float) $row->decl);
            if ($month !== null) {
                $candidates[] = ['name' => $row->name, 'rank' => $rank, 'tier' => $tier, 'month' => $month, 'kind' => self::kind($row->type)];
            }
        }

        $chosen = self::spread($candidates, $today->month);
        usort($chosen, fn (array $a, array $b) => [($a['month'] - $today->month + 12) % 12, $a['tier'], $a['rank']]
            <=> [($b['month'] - $today->month + 12) % 12, $b['tier'], $b['rank']]);

        return array_column($chosen, 'name');
    }

    /**
     * Takes SIZE candidates, the easy ones first: in turns over the months
     * from the current one, the most rewarding candidate of each month, so
     * every season has objects, within KIND_LIMITS.
     *
     * @param  list<array{name: string, rank: int, tier: int, month: int, kind: string}>  $candidates
     * @return list<array{name: string, rank: int, tier: int, month: int, kind: string}>
     */
    private static function spread(array $candidates, int $currentMonth): array
    {
        $chosen = [];
        $kinds  = [];
        foreach ([0, 1] as $tier) {
            $byMonth = [];
            foreach ($candidates as $candidate) {
                if ($candidate['tier'] === $tier) {
                    $byMonth[$candidate['month']][] = $candidate;
                }
            }
            while (count($chosen) < self::SIZE && $byMonth !== []) {
                for ($offset = 0; $offset < 12 && count($chosen) < self::SIZE; $offset++) {
                    $month = ($currentMonth - 1 + $offset) % 12 + 1;
                    while (! empty($byMonth[$month])) {
                        $candidate = array_shift($byMonth[$month]);
                        $limit     = self::KIND_LIMITS[$candidate['kind']] ?? null;
                        if ($limit === null || ($kinds[$candidate['kind']] ?? 0) < $limit) {
                            $chosen[]                  = $candidate;
                            $kinds[$candidate['kind']] = ($kinds[$candidate['kind']] ?? 0) + 1;
                            break;
                        }
                    }
                    if (empty($byMonth[$month])) {
                        unset($byMonth[$month]);
                    }
                }
            }
        }

        return $chosen;
    }

    /**
     * 0 for an object the instrument shows easily, 1 for one it shows, null
     * for one it does not show (or a double star it does not split, or that
     * needs steady air).
     */
    private static function tier(object $row, Instrument $instrument, Location $location, User $user, float $lens, array $magnifications, ?float $sqm): ?int
    {
        if ($row->type === 'DS') {
            if ((float) $row->diam1 < self::MIN_SEPARATION_ARCSEC) {
                return null;
            }
            $split = DoubleStarResolvability::forObject($row, $instrument, $location, $user, $lens, $magnifications);

            if ($split === null || $split['magnification_too_low'] || $split['not_visible']) {
                return null;
            }

            return match ($split['verdict']) {
                'easy'  => 0,
                'split' => 1,
                default => null,
            };
        }

        $altitude = ContrastReserveCalculator::highestAltitude($row->decl, $location);
        $result   = ContrastReserveCalculator::calculate(ContrastReserveCalculator::target($row->diam1, $row->diam2, $row->mag), $instrument, $location, $user, $lens, $magnifications, $sqm, $altitude);
        $reserve  = $result['contrast_reserve'];

        return match (true) {
            ! is_numeric($reserve)                                                         => null,
            (float) $row->diam1 * (int) $result['magnification'] < self::MIN_APPARENT_SIZE => null,
            $reserve > self::EASY_RESERVE                                                  => 0,
            $reserve > self::MIN_RESERVE                                                   => 1,
            default                                                                        => null,
        };
    }

    /**
     * The month an object is best seen in the evening: of the months it is
     * well placed in (SeasonalVisibility), the one closest to the month it
     * crosses the meridian at EVENING_HOUR; null when it is never well
     * placed.
     */
    public static function bestMonth(SeasonalVisibility $seasons, float $raDeg, float $decDeg): ?int
    {
        $months = $seasons->wellPlacedMonths($raDeg, $decDeg);
        if ($months === []) {
            return null;
        }
        $evening  = self::eveningMonth($raDeg);
        $distance = fn (int $month) => min(abs($month - $evening), 12 - abs($month - $evening));
        usort($months, fn (int $a, int $b) => [$distance($a), $a] <=> [$distance($b), $b]);

        return $months[0];
    }

    /**
     * The month (1–12) in which the right ascension crosses the meridian at
     * EVENING_HOUR local mean time, from the right ascension of the Sun in
     * the middle of each month.
     */
    public static function eveningMonth(float $raDeg): int
    {
        $best           = 1;
        $bestDifference = 360.0;
        for ($month = 1; $month <= 12; $month++) {
            $day        = 30.44       * ($month - 1) + 15;
            $sun        = 360.0       * ($day - 80.0) / 365.25;
            $meridian   = $sun + 15.0 * (self::EVENING_HOUR - 12);
            $difference = abs(fmod(fmod($raDeg - $meridian, 360.0) + 540.0, 360.0) - 180.0);
            if ($difference < $bestDifference) {
                [$best, $bestDifference] = [$month, $difference];
            }
        }

        return $best;
    }

    private static function kind(?string $type): string
    {
        return match (true) {
            $type === 'DS'                            => 'double',
            in_array($type, self::GALAXY_TYPES, true) => 'galaxy',
            default                                   => 'other',
        };
    }

    /**
     * The POOL objects in the database, in the order of POOL.
     *
     * @return list<object>
     */
    private static function poolRows(): array
    {
        $rows = DB::table('objects')
            ->whereIn('name', self::POOL)
            ->whereNotNull('ra')
            ->whereNotNull('decl')
            ->get(['name', 'type', 'ra', 'decl', 'mag', 'subr', 'diam1', 'diam2', 'pa'])
            ->keyBy(fn ($row) => mb_strtolower($row->name));

        return array_values(array_filter(array_map(fn (string $name) => $rows[mb_strtolower($name)] ?? null, self::POOL)));
    }

    /**
     * The deep-sky objects the observer has seen, by name in lower case.
     *
     * @return array<string, true>
     */
    private static function seenObjects(User $user): array
    {
        return DB::table('observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $user->username)
            ->where('observations.target_type', 'deepsky')
            ->where(fn ($query) => $query->whereNull('details.visibility')
                ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN))
            ->distinct()
            ->pluck('observations.target_name')
            ->mapWithKeys(fn ($name) => [mb_strtolower((string) $name) => true])
            ->all();
    }

    /**
     * Makes a new path for the observer, replacing the one they had: the
     * observing list of pick() and a goal of all its objects from today for
     * MONTHS months. Null when no object suits the instrument and location.
     */
    public static function start(User $user, Instrument $instrument, Location $location, ?Carbon $today = null): ?ObservingGoal
    {
        $today ??= Carbon::today();
        $names = self::pick($user, $instrument, $location, $today);
        if ($names === []) {
            return null;
        }

        self::remove($user);
        $description = __('Your beginner path: :count showpieces picked for your :instrument at :location, in the order the seasons bring them.', [
            'count'      => count($names),
            'instrument' => self::instrumentName($instrument),
            'location'   => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ]);
        $list = ObservingList::createWithObjects($user, __('Beginner path'), $names, $description);

        return ObservingGoal::create([
            'user_id'           => $user->id,
            'beginner_path'     => true,
            'name'              => __('Beginner path'),
            'observing_list_id' => $list->id,
            'source_label'      => $list->name,
            'target_count'      => null,
            'mode'              => 'observed',
            'new_only'          => false,
            'starts_on'         => $today->toDateString(),
            'ends_on'           => $today->copy()->addMonths(self::MONTHS)->toDateString(),
            'public'            => false,
        ])->setRelation('user', $user);
    }

    /**
     * Removes the observer's beginner paths: the goals, and the lists made
     * for them unless others commented on, liked or subscribed to them.
     */
    public static function remove(User $user): void
    {
        $goals = ObservingGoal::where('user_id', $user->id)->where('beginner_path', true)->get();
        foreach ($goals as $goal) {
            $list = $goal->observing_list_id ? ObservingList::where('owner_user_id', $user->id)->find($goal->observing_list_id) : null;
            $goal->delete();
            if ($list && ! $list->comments()->exists() && ! $list->likes()->exists() && ! $list->subscriptions()->exists()) {
                $list->items()->delete();
                $list->delete();
            }
        }
    }

    /**
     * The name of an instrument of instrument(): its full name, or a
     * description of a starter instrument.
     */
    public static function instrumentName(Instrument $instrument): string
    {
        if ($instrument->exists) {
            return html_entity_decode((string) $instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $aperture = (int) round((float) $instrument->aperture_mm);

        return $instrument->isBinocular()
            ? __(':magnificationx:aperture binoculars', ['magnification' => (int) $instrument->fixedMagnification, 'aperture' => $aperture])
            : __(':aperture mm telescope', ['aperture' => $aperture]);
    }

    /**
     * The objects of a path in its order, each with its details, the months
     * it is well placed at the location, whether it is now, the date it was
     * first seen in the path's period, a hop from a bright star and the most
     * liked finding notes.
     *
     * @return list<array<string, mixed>>
     */
    public static function steps(ObservingGoal $goal, ?Location $location, ?Carbon $today = null): array
    {
        $today ??= Carbon::today();
        $names = DB::table('observing_list_items')
            ->where('observing_list_id', $goal->observing_list_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('object_name')
            ->all();
        $objects = DeepskyObject::whereIn('name', $names)->get()->keyBy(fn ($object) => mb_strtolower($object->name));
        $done    = $goal->firstObservationDates();
        $notes   = self::findingNotes($names);
        $seasons = $location ? SeasonalVisibility::for($location, $today->year, $today->month, EphemerisDate::timezone($location)) : null;

        $steps = [];
        foreach ($names as $index => $name) {
            $object  = $objects[mb_strtolower($name)] ?? null;
            $months  = $object && $seasons ? $seasons->wellPlacedMonths((float) $object->ra * 15.0, (float) $object->decl) : [];
            $steps[] = [
                'number'  => $index + 1,
                'name'    => $object?->name ?? $name,
                'slug'    => $object?->slug,
                'details' => AtTheEyepiece::details($object),
                'months'  => $months,
                'best'    => $object && $seasons ? self::bestMonth($seasons, (float) $object->ra * 15.0, (float) $object->decl) : null,
                'now'     => in_array($today->month, $months, true),
                'done_on' => $done[mb_strtolower($name)] ?? null,
                'hop'     => $object && $object->type !== 'DS' ? self::hop((float) $object->ra * 15.0, (float) $object->decl, (float) $object->diam1 / 7200.0) : null,
                'notes'   => $notes[mb_strtolower($name)] ?? collect(),
            ];
        }

        return $steps;
    }

    /**
     * The months as ranges of short month names in the app's language, a
     * range over the new year as one ("Nov – Feb").
     *
     * @param  list<int>  $months
     */
    public static function monthRanges(array $months): string
    {
        $months = array_values(array_unique($months));
        sort($months);
        if ($months === []) {
            return '';
        }
        $name = fn (int $month) => Carbon::create(2000, $month, 1)->locale(app()->getLocale())->isoFormat('MMM');
        if (count($months) === 12) {
            return __('all year');
        }

        // start a range at a month whose previous month is not in it
        $in     = array_flip($months);
        $starts = array_values(array_filter($months, fn (int $month) => ! isset($in[($month + 10) % 12 + 1])));
        $ranges = [];
        foreach ($starts as $start) {
            $end = $start;
            while (isset($in[$end % 12 + 1])) {
                $end = $end % 12 + 1;
            }
            $ranges[] = $start === $end ? $name($start) : $name($start) . ' – ' . $name($end);
        }

        return implode(', ', $ranges);
    }

    /**
     * The step to do next: the first not done that is well placed this
     * month, else the first not done; null when all are done.
     *
     * @param  list<array<string, mixed>>  $steps
     */
    public static function next(array $steps): ?array
    {
        $open = array_values(array_filter($steps, fn (array $step) => $step['done_on'] === null));

        return collect($open)->first(fn (array $step) => $step['now']) ?? ($open[0] ?? null);
    }

    /**
     * The FINDING_NOTES most liked notes of each object, by name in lower
     * case, in one query.
     *
     * @param  list<string>  $names
     * @return array<string, Collection<int, ObjectFindingNote>>
     */
    private static function findingNotes(array $names): array
    {
        return ObjectFindingNote::whereIn('object_name', $names)
            ->with('user:id,name,slug')
            ->orderByDesc('likes_count')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (ObjectFindingNote $note) => mb_strtolower((string) $note->object_name))
            ->map(fn (Collection $notes) => $notes->take(self::FINDING_NOTES)->values())
            ->all();
    }

    /**
     * Where to start looking: the nearest star brighter than HOP_MAGNITUDE
     * with a Bayer letter, as its letter and constellation, the distance in
     * degrees and the direction from the star to the object ('north of',
     * 'north-east of', …, a translation key; null when the star lies within
     * $radiusDeg, in the object); null when none is within HOP_MAX_DEG.
     *
     * @return array{star: string, distance: float, direction: ?string}|null
     */
    public static function hop(float $raDeg, float $decDeg, float $radiusDeg = 0.0): ?array
    {
        $best = null;
        foreach (self::brightStars() as $star) {
            $distance = self::separation($raDeg, $decDeg, $star['ra'], $star['decl']);
            if ($distance > 0.1 && $distance <= self::HOP_MAX_DEG && ($best === null || $distance < $best[1])) {
                $best = [$star, $distance];
            }
        }
        if ($best === null) {
            return null;
        }
        [$star, $distance] = $best;

        return [
            'star'      => $star['label'],
            'distance'  => round($distance, 1),
            'direction' => $distance <= $radiusDeg ? null : self::COMPASS[(int) round(self::positionAngle($star['ra'], $star['decl'], $raDeg, $decDeg) / 45.0) % 8],
        ];
    }

    /**
     * The stars hop() starts from, cached: there are a few hundred.
     *
     * @return list<array{label: string, ra: float, decl: float}>
     */
    private static function brightStars(): array
    {
        return Cache::remember('beginner_path:bright_stars', now()->addMonth(), fn () => DB::table('stars')
            ->where('vmag', '<', self::HOP_MAGNITUDE)
            ->whereNotNull('bayer')
            ->whereNotNull('constellation_code')
            ->orderBy('vmag')
            ->get(['bayer', 'bayer_suffix', 'constellation_code', 'ra_degrees', 'decl_degrees'])
            ->unique(fn ($row) => $row->bayer . $row->bayer_suffix . $row->constellation_code)
            ->map(fn ($row) => [
                'label' => (FieldViewRenderer::GREEK_LETTERS[ucfirst(strtolower((string) $row->bayer))] ?? $row->bayer)
                    . strtr((string) $row->bayer_suffix, self::SUPERSCRIPTS) . ' ' . $row->constellation_code,
                'ra'   => (float) $row->ra_degrees,
                'decl' => (float) $row->decl_degrees,
            ])
            ->values()
            ->all());
    }

    private static function separation(float $ra1, float $dec1, float $ra2, float $dec2): float
    {
        $cos = sin(deg2rad($dec1)) * sin(deg2rad($dec2)) + cos(deg2rad($dec1)) * cos(deg2rad($dec2)) * cos(deg2rad($ra1 - $ra2));

        return rad2deg(acos(max(-1.0, min(1.0, $cos))));
    }

    /** The position angle (degrees from north through east) of the second point seen from the first. */
    private static function positionAngle(float $ra1, float $dec1, float $ra2, float $dec2): float
    {
        $dRa   = deg2rad($ra2 - $ra1);
        $angle = atan2(sin($dRa), cos(deg2rad($dec1)) * tan(deg2rad($dec2)) - sin(deg2rad($dec1)) * cos($dRa));

        return fmod(rad2deg($angle) + 360.0, 360.0);
    }

    /**
     * The home page box: the next step of a running path with its progress,
     * an invitation for an observer with few observations who has no path,
     * else null.
     *
     * @return array{goal: ?ObservingGoal, next: ?array}|null
     */
    public static function forHomePage(User $user): ?array
    {
        $goal = self::current($user);
        if ($goal) {
            if (! in_array($goal->progress()['status'], ['active', 'upcoming'], true)) {
                return null;
            }

            return ['goal' => $goal, 'next' => self::next(self::steps($goal, EquipmentGain::location($user, null)))];
        }

        $hadPath = ObservingGoal::where('user_id', $user->id)->where('beginner_path', true)->exists();
        if ($hadPath || DB::table('observations')->where('observer_username', $user->username)->count() >= self::INVITE_BELOW_OBSERVATIONS) {
            return null;
        }

        return ['goal' => null, 'next' => null];
    }
}
