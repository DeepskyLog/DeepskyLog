<?php

namespace App\Services;

use App\Http\Controllers\SessionController;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Services\Observations\DescriptionTranslations;
use App\Services\Observations\ObservationCardData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The blog of an observer (#964), newest first: their sessions as articles
 * with the session's text and their observations of it, the observations
 * outside a session per night, the observing lists they made public, and
 * their drawings chosen as sketch of the week or month.
 *
 * A page holds every entry from its cursor date down to the date where one
 * kind of entry runs out of fetched rows; that date is the cursor of the next
 * page, so a session or a night is never split over two pages.
 */
class ObserverBlog
{
    /**
     * The sessions, nights, lists and sketches fetched per page, of each kind.
     */
    public const PER_KIND = 10;

    /**
     * Observations made before this local hour belong to the night before.
     */
    private const MORNING_ENDS = 12;

    /** @var list<string>|null */
    private ?array $ownSessionDays = null;

    public function __construct(private readonly User $observer)
    {
    }

    /**
     * The entries dated before $before (all, newest first, for null), and the
     * cursor of the next page (null on the last page).
     *
     * Each entry is ['kind' => 'sketch'|'session'|'night'|'list', 'date' =>
     * Carbon, ...]: a sketch has 'sketch_of' ('week' or 'month') and
     * 'observation', a session 'session' and 'observations', a night
     * 'observations', a list 'list'.
     *
     * @return array{entries: Collection<int, array>, next: ?string}
     */
    public function page(?Carbon $before = null): array
    {
        $before = $before?->copy()->startOfDay();

        [$sessions, $sessionBound] = $this->sessions($before);
        [$nights, $nightBound]     = $this->nights($before);
        [$lists, $listBound]       = $this->lists($before);
        [$weeks, $weekBound]       = $this->sketches(SketchOfTheWeek::class, 'week', $before);
        [$months, $monthBound]     = $this->sketches(SketchOfTheMonth::class, 'month', $before);

        // Every entry on or after the highest bound is known.
        $bound = collect([$sessionBound, $nightBound, $listBound, $weekBound, $monthBound])->filter()->max();

        $order   = ['sketch' => 0, 'session' => 1, 'night' => 2, 'list' => 3];
        $entries = $months->concat($weeks)->concat($sessions)->concat($nights)->concat($lists)
            ->filter(fn (array $entry) => $bound === null || $entry['date']->gte($bound))
            ->sort(fn (array $a, array $b) => [$b['date'], $order[$a['kind']]] <=> [$a['date'], $order[$b['kind']]])
            ->values();

        $this->preload($entries);

        return ['entries' => $entries, 'next' => $bound?->toDateString()];
    }

    /**
     * The sessions of the blog: the active sessions of the observer, those
     * they joined and those that hold an observation of theirs. Every observer
     * of an evening often entered a session of their own with the same
     * observations, so another observer's session is left out when the
     * observer has a session of their own that day.
     */
    public function sessionsQuery(): Builder
    {
        $username = $this->observer->username;

        return ObservationSession::query()
            ->where($this->included('observation_sessions'))
            ->where(fn (Builder $query) => $query
                ->where('observerid', $username)
                ->orWhereIn('id', DB::table('sessionObservers')->where('observer', $username)->select('sessionid'))
                ->orWhereIn('id', DB::table('sessionObservations')
                    ->join('observations', 'observations.id', '=', 'sessionObservations.observationid')
                    ->where('observations.observer_username', $username)
                    ->select('sessionObservations.sessionid')));
    }

    /**
     * The constraint that the session in $table is active and not left out
     * for a session of the observer's own on the same day.
     */
    private function included(string $table): \Closure
    {
        $username = $this->observer->username;
        $ownDays  = $this->ownSessionDays();

        return fn ($query) => $query
            ->where($table . '.active', 1)
            ->where(fn ($query) => $query
                ->where($table . '.observerid', $username)
                ->orWhereNotIn(DB::raw('DATE(' . $table . '.begindate)'), $ownDays));
    }

    /**
     * The days on which the observer's own active sessions begin.
     *
     * @return list<string>
     */
    private function ownSessionDays(): array
    {
        return $this->ownSessionDays ??= ObservationSession::query()
            ->where('observerid', $this->observer->username)
            ->where('active', 1)
            ->whereNotNull('begindate')
            ->pluck('begindate')
            ->map(fn (Carbon $begin) => $begin->toDateString())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The session each of these observations is shown in, by observation id.
     * The old site linked some observations to several sessions, also to
     * sessions of other dates: the one whose dates hold the observation wins,
     * then the observer's own, then the oldest.
     *
     * @param  Collection<int, int>  $observationIds
     * @return array<int, int>
     */
    private function chosenSessions(Collection $observationIds): array
    {
        $links = DB::table('sessionObservations')
            ->join('observation_sessions as s', 's.id', '=', 'sessionObservations.sessionid')
            ->join('observations', 'observations.id', '=', 'sessionObservations.observationid')
            ->whereIn('sessionObservations.observationid', $observationIds->all())
            ->where($this->included('s'))
            ->get(['sessionObservations.observationid', 's.id', 's.observerid', 's.begindate', 's.enddate', 'observations.observed_on']);

        return $links->groupBy('observationid')
            ->map(fn (Collection $links) => $links->sortBy(function ($link) {
                $observed = $link->observed_on ? Carbon::parse($link->observed_on)->toDateString() : null;
                $begin    = Carbon::parse($link->begindate)->subDay()->toDateString();
                $end      = Carbon::parse($link->enddate ?? $link->begindate)->addDay()->toDateString();
                $holds    = $observed !== null && $observed >= $begin && $observed <= $end;

                return [$holds ? 0 : 1, $link->observerid === $this->observer->username ? 0 : 1, (int) $link->id];
            })->first()->id)
            ->all();
    }

    /**
     * @return array{0: Collection<int, array>, 1: ?Carbon}
     */
    private function sessions(?Carbon $before): array
    {
        $query = $this->sessionsQuery()
            ->whereNotNull('begindate')
            ->when($before, fn (Builder $query) => $query->where('begindate', '<', $before->toDateTimeString()));

        $sessions = (clone $query)->orderByDesc('begindate')->orderByDesc('id')->limit(self::PER_KIND + 1)->get();
        $bound    = null;
        if ($sessions->count() > self::PER_KIND) {
            // All sessions of the last day, also those beyond the limit.
            $bound    = $sessions->last()->begindate->copy()->startOfDay();
            $sessions = $sessions->concat(
                (clone $query)->where('begindate', '>=', $bound->toDateTimeString())->whereNotIn('id', $sessions->pluck('id'))->get()
            );
        }

        if ($sessions->isEmpty()) {
            return [collect(), $bound];
        }

        $observations = Observation::query()
            ->join('sessionObservations', 'sessionObservations.observationid', '=', 'observations.id')
            ->whereIn('sessionObservations.sessionid', $sessions->pluck('id'))
            ->where('observations.observer_username', $this->observer->username)
            ->select('observations.*', 'sessionObservations.sessionid as blog_session_id')
            ->orderBy('observations.observed_on')
            ->orderBy('observations.observed_time')
            ->orderBy('observations.id')
            ->get();
        $chosen       = $this->chosenSessions($observations->pluck('id')->unique()->values());
        $observations = $observations
            ->filter(fn (Observation $observation) => ($chosen[$observation->id] ?? null) === (int) $observation->blog_session_id)
            ->groupBy('blog_session_id');

        ObservationSession::preloadOtherObservers($sessions);

        $entries = $sessions->map(fn (ObservationSession $session) => [
            'kind'         => 'session',
            'date'         => $session->begindate->copy()->startOfDay(),
            'session'      => $session,
            'observations' => $observations->get($session->id, collect())->values(),
        ]);

        return [$entries, $bound];
    }

    /**
     * The observations outside an active session, per night.
     *
     * @return array{0: Collection<int, array>, 1: ?Carbon}
     */
    private function nights(?Carbon $before): array
    {
        $username = $this->observer->username;
        // The observations in a session of the blog are shown with it.
        $outsideSessions = fn ($query) => $query->whereNotExists(fn ($sub) => $sub
            ->select(DB::raw(1))
            ->from('sessionObservations')
            ->join('observation_sessions', 'observation_sessions.id', '=', 'sessionObservations.sessionid')
            ->whereColumn('sessionObservations.observationid', 'observations.id')
            ->where($this->included('observation_sessions')));

        // A night is the date of its evening, so the observations of the
        // morning after $before still belong to a night before it.
        $dates = DB::table('observations')
            ->where('observer_username', $username)
            ->whereNotNull('observed_on')
            ->when($before, fn ($query) => $query->where('observed_on', '<=', $before->toDateString()))
            ->where($outsideSessions)
            ->distinct()
            ->orderByDesc('observed_on')
            ->limit(self::PER_KIND + 1)
            ->pluck('observed_on')
            ->map(fn ($date) => Carbon::parse($date)->toDateString());

        if ($dates->isEmpty()) {
            return [collect(), null];
        }

        // Without the dates after the last one fetched, the nights before it
        // may miss their evening.
        $bound = null;
        if ($dates->count() > self::PER_KIND) {
            $dates = $dates->take(self::PER_KIND);
            $bound = Carbon::parse($dates->last());
        }

        $observations = Observation::query()
            ->where('observer_username', $username)
            ->whereIn('observed_on', $dates->all())
            ->where($outsideSessions)
            ->orderBy('observed_on')
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get();

        $cards = ObservationCardData::instance()->preload($observations);

        $entries = $observations
            ->groupBy(fn (Observation $observation) => $this->night($observation, $cards->location($observation->location_id)))
            ->map(fn (Collection $observations, string $night) => [
                'kind'         => 'night',
                'date'         => Carbon::parse($night),
                'observations' => $observations->values(),
            ])
            ->filter(fn (array $entry) => $before === null || $entry['date']->lt($before))
            ->values();

        return [$entries, $bound];
    }

    /**
     * The night (date of its evening) of an observation: its local date, the
     * day before for an observation in the morning. Without a time, the date
     * it was stored with.
     */
    public function night(Observation $observation, ?Location $location): string
    {
        $local = $observation->localObservedAt($location);
        if ($local === null) {
            return $observation->observed_on->toDateString();
        }

        return ($local->hour < self::MORNING_ENDS ? $local->copy()->subDay() : $local)->toDateString();
    }

    /**
     * @return array{0: Collection<int, array>, 1: ?Carbon}
     */
    private function lists(?Carbon $before): array
    {
        $query = ObservingList::query()
            ->where('owner_user_id', $this->observer->id)
            ->where('public', true)
            ->whereNotNull('created_at')
            ->when($before, fn (Builder $query) => $query->where('created_at', '<', $before->toDateTimeString()))
            ->withCount('items');

        $lists = (clone $query)->orderByDesc('created_at')->orderByDesc('id')->limit(self::PER_KIND + 1)->get();
        $bound = null;
        if ($lists->count() > self::PER_KIND) {
            $bound = $lists->last()->created_at->copy()->startOfDay();
            $lists = $lists->concat(
                (clone $query)->where('created_at', '>=', $bound->toDateTimeString())->whereNotIn('id', $lists->pluck('id'))->get()
            );
        }

        $entries = $lists->map(fn (ObservingList $list) => [
            'kind' => 'list',
            'date' => $list->created_at->copy()->startOfDay(),
            'list' => $list,
        ]);

        return [$entries, $bound];
    }

    /**
     * The drawings of the observer chosen as sketch of the week or month, on
     * the day they were shown; one chosen for a later day stays hidden until
     * then.
     *
     * @param  class-string<SketchOfTheWeek|SketchOfTheMonth>  $model
     * @return array{0: Collection<int, array>, 1: ?Carbon}
     */
    public function sketches(string $model, string $kind, ?Carbon $before = null): array
    {
        $table = (new $model())->getTable();
        $query = $model::query()
            ->setEagerLoads([])
            // published(), on the joined tables
            ->where($table . '.date', '<=', Carbon::today()->toDateString())
            ->join('observations', 'observations.id', '=', $table . '.observation_id')
            ->where('observations.observer_username', $this->observer->username)
            ->when($before, fn (Builder $query) => $query->where($table . '.date', '<', $before->toDateString()))
            ->select($table . '.*');

        $sketches = (clone $query)->orderByDesc($table . '.date')->orderByDesc($table . '.id')->limit(self::PER_KIND + 1)->get();
        $bound    = null;
        if ($sketches->count() > self::PER_KIND) {
            $bound    = $sketches->last()->date->copy()->startOfDay();
            $sketches = $sketches->concat(
                (clone $query)->where($table . '.date', '>=', $bound->toDateString())->whereNotIn($table . '.id', $sketches->pluck('id'))->get()
            );
        }

        $observations = Observation::whereIn('id', $sketches->pluck('observation_id')->unique())->get()->keyBy('id');
        $entries      = $sketches
            ->filter(fn ($sketch) => $observations->has($sketch->observation_id))
            ->map(fn ($sketch) => [
                'kind'        => 'sketch',
                'date'        => $sketch->date->copy()->startOfDay(),
                'sketch_of'   => $kind,
                'observation' => $observations->get($sketch->observation_id),
            ])
            ->values();

        return [$entries, $bound];
    }

    /**
     * What the entries show, in a few queries: the locations and pictures of
     * the sessions, and the equipment, objects, translations and sketch of the
     * week/month of the observations.
     */
    private function preload(Collection $entries): void
    {
        $sessions  = $entries->where('kind', 'session')->pluck('session');
        $locations = Location::whereIn('id', $sessions->pluck('locationid')->filter()->unique())->get()->keyBy('id');
        foreach ($sessions as $session) {
            $location               = $locations->get($session->locationid);
            $session->location_name = $location?->name;
            $session->preview       = SessionController::resolveSessionImage($session, $location);
        }
        ObservationCardData::instance()->preloadUsernames(
            $sessions->flatMap(fn (ObservationSession $session) => [$session->observerid, ...$session->otherObservers()])->unique()
        );

        $observations = $entries->pluck('observations')->filter()->flatten(1)
            ->concat($entries->pluck('observation')->filter());
        if ($observations->isEmpty()) {
            return;
        }

        ObservationCardData::instance()->preload($observations);
        $ids = $observations->pluck('id')->unique()->values();
        DescriptionTranslations::preload($ids);

        $ofTheWeek  = SketchOfTheWeek::published()->whereIn('observation_id', $ids)->pluck('observation_id')->flip();
        $ofTheMonth = SketchOfTheMonth::published()->whereIn('observation_id', $ids)->pluck('observation_id')->flip();
        foreach ($observations as $observation) {
            $observation->blog_sketch_of = $ofTheMonth->has($observation->id) ? 'month' : ($ofTheWeek->has($observation->id) ? 'week' : null);
        }
    }
}
