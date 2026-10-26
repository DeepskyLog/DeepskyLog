<?php

namespace App\Services;

use App\Jobs\ComputeObserverStats;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\ObservationLike;
use App\Models\ObservingGoal;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The year in review of an observer: what they observed in a finished
 * calendar year (observed_on), with the year before to compare. Only finished
 * years are shown (isShown()); the page, the blog, the observer's RSS feed and
 * the message of observers:send-year-review link to it.
 *
 * summary() holds only what every viewer sees and is cached per observer and
 * year; the goals, which may be private, come from goals().
 */
class ObserverYear
{
    /** The catalogs whose progress the review shows, in this order, when it grew. */
    public const CATALOGS = ['M', 'Caldwell', 'H400', 'H400-II', 'NGC', 'IC', 'Arp', 'Hickson'];

    /** The objects first seen in the year that are listed. */
    public const NEW_OBJECTS_SHOWN = 12;

    /** The drawings shown: sketches of the week/month first, then the most liked. */
    public const DRAWINGS_SHOWN = 12;

    /** The instruments and locations listed, most used first. */
    public const EQUIPMENT_SHOWN = 3;

    /**
     * The summary is fresh for a day; for a week after that the old one is
     * shown while it is made again after the response. A finished year rarely
     * changes: only observations entered late.
     */
    private const CACHE_SECONDS = [86400, 7 * 86400];

    /** Magnitudes outside this range are placeholders (99.9, 0) of the objects table. */
    private const MAGNITUDE_RANGE = [-2, 25];

    /** @var Collection<int, Observation>|null */
    private ?Collection $rows = null;

    public function __construct(private readonly User $observer, public readonly int $year)
    {
    }

    /**
     * Whether a year can be reviewed: only finished years.
     */
    public static function isShown(int $year): bool
    {
        return $year < (int) now()->year;
    }

    /**
     * The finished years in which the observer has observations, newest first.
     *
     * @return Collection<int, int>
     */
    public static function years(User $observer): Collection
    {
        $year = self::yearExpression('observed_on');

        return DB::table('observations')
            ->where('observer_username', $observer->username)
            ->whereNotNull('observed_on')
            ->where('observed_on', '<', now()->startOfYear()->toDateString())
            ->selectRaw($year . ' as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();
    }

    /**
     * The URL of the review of a year.
     */
    public static function url(User $observer, int $year): string
    {
        return route('observer.year', ['observer' => $observer->slug, 'year' => $year]);
    }

    public static function cacheKey(User $observer, int $year): string
    {
        return 'observer_year:' . $observer->id . ':' . $year;
    }

    /**
     * Everything the review shows that does not depend on the viewer.
     *
     * @return array{
     *     totals: array<string, int>,
     *     previous: array<string, int>,
     *     months: array{observations: list<int>, nights: list<int>},
     *     kinds: array<string, int>,
     *     new_objects: array{count: int, shown: list<array{name: string, date: string, url: ?string}>},
     *     best_night: ?array{date: string, objects: int, observations: int, location: ?string, url: string},
     *     faintest: ?array{name: string, magnitude: float, url: string},
     *     rarest: ?array{name: string, observers: int, url: string},
     *     catalogs: list<array{catalog: string, gained: int, seen: int, total: int}>,
     *     instruments: list<array{name: string, count: int}>,
     *     locations: list<array{name: string, count: int}>,
     *     drawing_ids: list<int>,
     *     sketches: array{week: int, month: int},
     *     social: array<string, int>,
     * }
     */
    public function summary(): array
    {
        return Cache::flexible(self::cacheKey($this->observer, $this->year), self::CACHE_SECONDS, fn () => $this->compute());
    }

    /**
     * The goals the viewer may see that ended or were reached in the year.
     *
     * @return Collection<int, ObservingGoal>
     */
    public function goals(?User $viewer): Collection
    {
        return ObservingGoal::shownFor($this->observer, $viewer)
            ->filter(function (ObservingGoal $goal) {
                $reachedOn = $goal->progress()['reached_on'];

                return $reachedOn !== null
                    ? $reachedOn->year     === $this->year
                    : $goal->ends_on->year === $this->year && $goal->progress()['status'] === 'ended';
            })
            ->values();
    }

    private function compute(): array
    {
        $rows = $this->rows();

        return [
            'totals'      => $this->totals($rows, $this->year),
            'previous'    => $this->totals($this->rowsOf($this->year - 1), $this->year - 1),
            'months'      => $this->months($rows),
            'kinds'       => $this->kinds(),
            'new_objects' => $this->newObjects(),
            'best_night'  => $this->bestNight($rows),
            'faintest'    => $this->faintest(),
            'rarest'      => $this->rarest($rows),
            'catalogs'    => $this->catalogs(),
            'instruments' => $this->mostUsed($rows, 'instrument_id', fn ($ids) => Instrument::whereIn('id', $ids)->get()->mapWithKeys(fn (Instrument $i) => [$i->id => $i->fullName()])),
            'locations'   => $this->mostUsed($rows, 'location_id', fn ($ids) => Location::whereIn('id', $ids)->pluck('name', 'id')),
            'drawing_ids' => $this->drawingIds(),
            'sketches'    => [
                'week'  => $this->sketchesOfThe(SketchOfTheWeek::class)->count(),
                'month' => $this->sketchesOfThe(SketchOfTheMonth::class)->count(),
            ],
            'social' => $this->social($rows),
        ];
    }

    /**
     * The observations of the year, with the night each belongs to
     * (year_night) and whether the object was seen (year_seen).
     *
     * @return Collection<int, Observation>
     */
    private function rows(): Collection
    {
        return $this->rows ??= $this->rowsOf($this->year);
    }

    /**
     * @return Collection<int, Observation>
     */
    private function rowsOf(int $year): Collection
    {
        $rows = $this->observations($year)
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->get([
                'observations.id', 'observations.observed_on', 'observations.observed_time', 'observations.legacy_time_int', 'observations.location_id',
                'observations.instrument_id', 'observations.has_drawing', 'observations.target_name',
                'observations.different_object_key', 'details.visibility as year_visibility',
            ]);

        $locations = Location::whereIn('id', $rows->pluck('location_id')->filter()->unique())->get()->keyBy('id');
        $blog      = new ObserverBlog($this->observer);
        foreach ($rows as $row) {
            $row->year_night = $blog->night($row, $locations->get($row->location_id));
            $row->year_seen  = (int) $row->year_visibility !== Observation::VISIBILITY_NOT_SEEN;
        }

        return $rows;
    }

    /**
     * The observer's observations of a year, as an Eloquent query.
     */
    private function observations(int $year): \Illuminate\Database\Eloquent\Builder
    {
        return Observation::query()
            ->where('observations.observer_username', $this->observer->username)
            ->whereBetween('observations.observed_on', [$year . '-01-01', $year . '-12-31']);
    }

    /**
     * @param  Collection<int, Observation>  $rows
     * @return array{observations: int, nights: int, objects: int, drawings: int, sessions: int}
     */
    private function totals(Collection $rows, int $year): array
    {
        return [
            'observations' => $rows->count(),
            'nights'       => $rows->pluck('year_night')->unique()->count(),
            'objects'      => $rows->where('year_seen', true)->pluck('different_object_key')->filter()->unique()->count(),
            'drawings'     => $rows->where('has_drawing', true)->count(),
            'sessions'     => DB::table('observation_sessions')
                ->where('observerid', $this->observer->username)
                ->where('active', 1)
                ->whereBetween('begindate', [$year . '-01-01 00:00:00', $year . '-12-31 23:59:59'])
                ->count(),
        ];
    }

    /**
     * The observations and nights per month. A night that began on the last
     * evening of the year before counts in January.
     *
     * @param  Collection<int, Observation>  $rows
     * @return array{observations: list<int>, nights: list<int>}
     */
    private function months(Collection $rows): array
    {
        $observations = array_fill(0, 12, 0);
        $nights       = array_fill(0, 12, 0);

        foreach ($rows as $row) {
            $observations[$row->observed_on->month - 1]++;
        }
        foreach ($rows->pluck('year_night')->unique() as $night) {
            $date = Carbon::parse($night);
            $nights[$date->year < $this->year ? 0 : $date->month - 1]++;
        }

        return ['observations' => $observations, 'nights' => $nights];
    }

    /**
     * The observations per kind of object, as on the observer page: the
     * deep-sky observations without the double stars.
     *
     * @return array<string, int>
     */
    private function kinds(): array
    {
        $counts = [];
        foreach (ComputeObserverStats::categoryQueries() as $kind => $query) {
            if ($kind === 'all') {
                continue;
            }
            $counts[$kind] = (int) $query()
                ->where('observer_username', $this->observer->username)
                ->whereBetween('observed_on', [$this->year . '-01-01', $this->year . '-12-31'])
                ->count();
        }
        $counts['deepsky'] = max(0, $counts['deepsky'] - $counts['double']);

        return array_filter($counts);
    }

    /**
     * The objects the observer saw for the first time in the year: the number,
     * and the first NEW_OBJECTS_SHOWN with their first observation.
     *
     * @return array{count: int, shown: list<array{name: string, date: string, url: ?string}>}
     */
    private function newObjects(): array
    {
        $firsts = DB::table('observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $this->observer->username)
            ->whereNotNull('observations.different_object_key')
            ->whereNotNull('observations.observed_on')
            ->where(fn ($query) => $query->whereNull('details.visibility')
                ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN))
            ->groupBy('observations.different_object_key')
            ->havingRaw('MIN(observations.observed_on) BETWEEN ? AND ?', [$this->year . '-01-01', $this->year . '-12-31'])
            ->selectRaw('observations.different_object_key AS object_key, MIN(observations.observed_on) AS first_on')
            ->orderBy('first_on')
            ->orderBy('object_key')
            ->get();

        $shownKeys = $firsts->take(self::NEW_OBJECTS_SHOWN);
        $firstOn   = $shownKeys->mapWithKeys(fn ($row) => [$row->object_key => Carbon::parse($row->first_on)->toDateString()]);

        // The first observation of each object shown.
        $observations = $this->observations($this->year)
            ->whereIn('observations.different_object_key', $shownKeys->pluck('object_key')->all())
            ->orderBy('observations.observed_on')
            ->orderBy('observations.observed_time')
            ->orderBy('observations.id')
            ->get()
            ->filter(fn (Observation $observation) => $observation->observed_on->toDateString() === $firstOn->get($observation->different_object_key))
            ->unique('different_object_key');
        ObservationCardData::instance()->preloadDetailUrls($observations);

        $shown = $observations
            ->sortBy(fn (Observation $observation) => [$observation->observed_on->toDateString(), $observation->different_object_key])
            ->map(fn (Observation $observation) => [
                'name' => html_entity_decode((string) $observation->target_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'date' => $observation->observed_on->toDateString(),
                'url'  => $observation->detailUrl($this->observer->slug),
            ])
            ->values()
            ->all();

        return ['count' => $firsts->count(), 'shown' => $shown];
    }

    /**
     * The night with the most different objects seen; the blog page that
     * starts with it.
     *
     * @param  Collection<int, Observation>  $rows
     */
    private function bestNight(Collection $rows): ?array
    {
        $best = $rows->groupBy('year_night')
            ->map(fn (Collection $night, string $date) => [
                'date'         => $date,
                'objects'      => $night->where('year_seen', true)->pluck('different_object_key')->filter()->unique()->count(),
                'observations' => $night->count(),
                'location_id'  => $night->pluck('location_id')->filter()->countBy()->sortDesc()->keys()->first(),
            ])
            ->sortBy(fn (array $night) => [-$night['objects'], -$night['observations'], $night['date']])
            ->first();

        if ($best === null || $best['objects'] === 0) {
            return null;
        }

        $location = $best['location_id'] ? Location::find($best['location_id']) : null;

        return [
            'date'         => $best['date'],
            'objects'      => $best['objects'],
            'observations' => $best['observations'],
            'location'     => $location ? html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
            'url'          => route('observer.blog', [
                'observer' => $this->observer->slug,
                'before'   => Carbon::parse($best['date'])->addDay()->toDateString(),
            ]),
        ];
    }

    /**
     * The deep-sky observations of the year in which the object was seen.
     */
    private function seenDeepsky(): \Illuminate\Database\Query\Builder
    {
        return DB::table('observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $this->observer->username)
            ->whereBetween('observations.observed_on', [$this->year . '-01-01', $this->year . '-12-31'])
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->where(fn ($query) => $query->whereNull('details.visibility')
                ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN));
    }

    /**
     * The faintest deep-sky object seen, by its magnitude in the objects table.
     */
    private function faintest(): ?array
    {
        $object = $this->seenDeepsky()
            ->join('objects', 'objects.name', '=', 'observations.target_name')
            ->whereBetween('objects.mag', self::MAGNITUDE_RANGE)
            ->where('objects.mag', '!=', 0)
            ->orderByDesc('objects.mag')
            ->orderBy('objects.name')
            ->first(['objects.name', 'objects.slug', 'objects.mag']);

        if ($object === null) {
            return null;
        }

        return [
            'name'      => html_entity_decode((string) $object->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'magnitude' => round((float) $object->mag, 1),
            'url'       => route('object.show', $object->slug ?: \Illuminate\Support\Str::slug($object->name)),
        ];
    }

    /**
     * The deep-sky object seen in the year that the fewest observers have seen.
     *
     * @param  Collection<int, Observation>  $rows
     */
    private function rarest(Collection $rows): ?array
    {
        $names = $this->seenDeepsky()->distinct()->pluck('observations.target_name')->filter()->values();
        if ($names->isEmpty()) {
            return null;
        }

        $rarest = $names->chunk(1000)
            ->flatMap(fn (Collection $chunk) => DB::table('object_observation_stats_by_observer')
                ->whereIn('object_name', $chunk->values()->all())
                ->groupBy('object_name')
                ->selectRaw('object_name, COUNT(*) AS observers')
                ->get())
            ->sortBy(fn ($row) => [(int) $row->observers, $row->object_name])
            ->first();

        if ($rarest === null) {
            return null;
        }

        $slug = DB::table('objects')->where('name', $rarest->object_name)->value('slug');

        return [
            'name'      => html_entity_decode((string) $rarest->object_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'observers' => (int) $rarest->observers,
            'url'       => route('object.show', $slug ?: \Illuminate\Support\Str::slug($rarest->object_name)),
        ];
    }

    /**
     * The progress in the CATALOGS that grew in the year: the objects first
     * seen in it, and all seen by its end.
     *
     * @return list<array{catalog: string, gained: int, seen: int, total: int}>
     */
    private function catalogs(): array
    {
        $firstSeen = DB::table('observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $this->observer->username)
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->where('observations.observed_on', '<=', $this->year . '-12-31')
            ->where(fn ($query) => $query->whereNull('details.visibility')
                ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN))
            ->groupBy('observations.target_name')
            ->selectRaw('observations.target_name, MIN(observations.observed_on) AS first_on')
            ->pluck('first_on', 'observations.target_name')
            ->mapWithKeys(fn ($date, $name) => [mb_strtolower((string) $name) => Carbon::parse($date)->toDateString()]);

        if ($firstSeen->isEmpty()) {
            return [];
        }

        $start   = $this->year . '-01-01';
        $members = DB::table('objectnames')
            ->whereIn('catalog', self::CATALOGS)
            ->distinct()
            ->get(['catalog', 'objectname'])
            ->groupBy('catalog');

        $progress = [];
        foreach (self::CATALOGS as $catalog) {
            $names  = $members->get($catalog, collect())->pluck('objectname')->map(fn ($name) => mb_strtolower((string) $name))->unique();
            $dates  = $names->map(fn (string $name) => $firstSeen->get($name))->filter();
            $gained = $dates->filter(fn (string $date) => $date >= $start)->count();
            if ($gained > 0) {
                $progress[] = ['catalog' => $catalog, 'gained' => $gained, 'seen' => $dates->count(), 'total' => $names->count()];
            }
        }

        return $progress;
    }

    /**
     * The EQUIPMENT_SHOWN values of a column used most, with their names.
     *
     * @param  Collection<int, Observation>  $rows
     * @param  \Closure(list<int>): Collection<int, string>  $names
     * @return list<array{name: string, count: int}>
     */
    private function mostUsed(Collection $rows, string $column, \Closure $names): array
    {
        $counts = $rows->pluck($column)->filter()->countBy()->sortDesc()->take(self::EQUIPMENT_SHOWN);
        if ($counts->isEmpty()) {
            return [];
        }
        $labels = $names($counts->keys()->all());

        return $counts
            ->filter(fn (int $count, $id) => $labels->has($id))
            ->map(fn (int $count, $id) => [
                'name'  => html_entity_decode((string) $labels->get($id), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'count' => $count,
            ])
            ->values()
            ->all();
    }

    /**
     * The observer's drawings chosen as sketch of the week or month on a
     * (published) date of the year.
     *
     * @param  class-string<SketchOfTheWeek|SketchOfTheMonth>  $model
     * @return Collection<int, int> the observation ids
     */
    private function sketchesOfThe(string $model): Collection
    {
        $table = (new $model())->getTable();

        return DB::table($table)
            ->join('observations', 'observations.id', '=', $table . '.observation_id')
            ->where('observations.observer_username', $this->observer->username)
            ->whereBetween($table . '.date', [$this->year . '-01-01', min($this->year . '-12-31', Carbon::today()->toDateString())])
            ->orderBy($table . '.date')
            ->pluck($table . '.observation_id')
            ->map(fn ($id) => (int) $id);
    }

    /**
     * The DRAWINGS_SHOWN drawings of the year: those chosen as sketch of the
     * month or week first, then the most liked, then the newest.
     *
     * @return list<int>
     */
    private function drawingIds(): array
    {
        $chosen = $this->sketchesOfThe(SketchOfTheMonth::class)->concat($this->sketchesOfThe(SketchOfTheWeek::class));

        $others = $this->observations($this->year)
            ->where('observations.has_drawing', true)
            ->leftJoin('observation_likes as likes', fn ($join) => $join
                ->on('likes.observation_id', '=', 'observations.id')
                ->where('likes.observation_type', ObservationLike::TYPE_OBSERVATION))
            ->groupBy('observations.id', 'observations.observed_on')
            ->orderByRaw('COUNT(likes.id) DESC')
            ->orderByDesc('observations.observed_on')
            ->orderByDesc('observations.id')
            ->limit(self::DRAWINGS_SHOWN + $chosen->count())
            ->pluck('observations.id')
            ->map(fn ($id) => (int) $id);

        return $chosen->concat($others)->unique()->take(self::DRAWINGS_SHOWN)->values()->all();
    }

    /**
     * What others gave the observer and what the observer shared in the year.
     *
     * @param  Collection<int, Observation>  $rows
     * @return array{likes: int, comments: int, lists: int, star_parties: int}
     */
    private function social(Collection $rows): array
    {
        $ids   = $rows->pluck('id')->all();
        $start = $this->year . '-01-01';
        $end   = $this->year . '-12-31';

        $likes    = 0;
        $comments = 0;
        foreach (array_chunk($ids, 1000) as $chunk) {
            $likes += ObservationLike::where('observation_type', ObservationLike::TYPE_OBSERVATION)
                ->whereIn('observation_id', $chunk)
                ->where('user_id', '!=', $this->observer->id)
                ->count();
            $comments += ObservationComment::where('observation_type', ObservationComment::TYPE_OBSERVATION)
                ->whereIn('observation_id', $chunk)
                ->where('user_id', '!=', $this->observer->id)
                ->count();
        }

        return [
            'likes'    => $likes,
            'comments' => $comments,
            'lists'    => DB::table('observing_lists')
                ->where('owner_user_id', $this->observer->id)
                ->where('public', true)
                ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->count(),
            'star_parties' => DB::table('star_parties')
                ->whereNull('star_parties.cancelled_at')
                ->whereBetween('star_parties.start_date', [$start, $end])
                ->where(fn ($query) => $query
                    ->where('star_parties.user_id', $this->observer->id)
                    ->orWhereIn('star_parties.id', DB::table('star_party_participants')
                        ->where('user_id', $this->observer->id)
                        ->where('status', StarPartyParticipant::GOING)
                        ->select('star_party_id')))
                ->count(),
        ];
    }

    /**
     * The SQL expression for the year of a date column: MySQL has YEAR(),
     * SQLite (the tests) only strftime().
     */
    private static function yearExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }
}
