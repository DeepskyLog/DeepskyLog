<?php

namespace App\Services\Observations;

use App\Models\CometObject;
use App\Models\Constellation;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\Target;
use App\Models\TargetType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * What the observation and sketch cards (observation-deepsky,
 * observation-comet, sketch-deepsky, sketch-comet) show besides the
 * observation itself: observer, object, constellation, equipment, likes and
 * the observations of the same observer on the same day (for the index in
 * the detail URL).
 *
 * A page that renders cards calls preload() with its observations, which
 * loads all of this in one grouped query per kind; the cards then read it
 * from here. Whatever was not preloaded is queried on first use and kept, so
 * a card outside a preloaded page still works, with one query per value
 * instead of one per card. Scoped: it lives for one request (or queue job).
 */
class ObservationCardData
{
    /** @var array<string, User|null> keyed by lowercased username */
    private array $users = [];

    /** @var array<string, Target|null> keyed by object name */
    private array $targets = [];

    /** @var array<string, array<int|string, Model|null>> keyed by model class, then id */
    private array $models = [];

    /** @var array<int, array{count: int, liked: bool}> keyed by observation id */
    private array $likes = [];

    /** @var array<string, Collection<int, Observation>> keyed by "username|Y-m-d" */
    private array $sameDay = [];

    /** @var Collection<int, Constellation>|null */
    private ?Collection $constellations = null;

    /** @var Collection<int, string>|null */
    private ?Collection $targetTypes = null;

    public static function instance(): self
    {
        return app(self::class);
    }

    /**
     * Load everything the cards of these observations need. Items that are
     * not an App\Models\Observation (legacy-shaped rows) are skipped.
     *
     * @param  iterable<mixed>  $observations
     */
    public function preload(iterable $observations): self
    {
        $observations = collect($observations instanceof \Illuminate\Contracts\Pagination\Paginator ? $observations->items() : $observations)
            ->filter(fn ($o) => $o instanceof Observation)
            ->values();
        if ($observations->isEmpty()) {
            return $this;
        }

        $this->preloadUsers($observations->pluck('observer_username'));
        // All names, not only deep-sky: sketch-deepsky also shows the other sketches
        // (/drawings shows comets with it) and looks their names up too.
        $this->preloadTargets($observations->pluck('target_name'));
        $this->preloadModels(CometObject::class, $observations->where('target_type', Observation::TYPE_COMET)
            ->pluck('target_key')->filter(fn ($key) => is_numeric($key)));
        $this->preloadModels(Location::class, $observations->pluck('location_id'));
        $this->preloadModels(Instrument::class, $observations->pluck('instrument_id'));
        $this->preloadModels(Eyepiece::class, $observations->pluck('eyepiece_id'));
        $this->preloadModels(Filter::class, $observations->pluck('filter_id'));
        $this->preloadModels(Lens::class, $observations->pluck('lens_id'));
        $this->preloadLikes($observations->pluck('id'));
        $this->preloadSameDay($observations);
        Observation::preloadMagnifications($observations);

        return $this;
    }

    public function user(?string $username): ?User
    {
        $key = mb_strtolower(trim((string) $username));
        if ($key === '') {
            return null;
        }
        if (! array_key_exists($key, $this->users)) {
            $this->users[$key] = User::where('username', $key)->first();
        }

        return $this->users[$key];
    }

    public function target(?string $name): ?Target
    {
        $name = (string) $name;
        if ($name === '') {
            return null;
        }
        if (! array_key_exists($name, $this->targets)) {
            $this->targets[$name] = Target::where('name', $name)->first();
        }

        return $this->targets[$name];
    }

    public function comet(mixed $id): ?CometObject
    {
        return $this->model(CometObject::class, $id);
    }

    public function location(mixed $id): ?Location
    {
        return $this->model(Location::class, $id);
    }

    public function instrument(mixed $id): ?Instrument
    {
        return $this->model(Instrument::class, $id);
    }

    public function eyepiece(mixed $id): ?Eyepiece
    {
        return $this->model(Eyepiece::class, $id);
    }

    public function filter(mixed $id): ?Filter
    {
        return $this->model(Filter::class, $id);
    }

    public function lens(mixed $id): ?Lens
    {
        return $this->model(Lens::class, $id);
    }

    /** Name of a constellation, from the (small) constellations table. */
    public function constellationName(mixed $id): string
    {
        $this->constellations ??= Constellation::all()->keyBy('id');

        return (string) ($this->constellations->get($id)?->name ?? '');
    }

    /** Untranslated name of an object type (target_types.type), or null. */
    public function targetTypeName(mixed $id): ?string
    {
        $this->targetTypes ??= TargetType::pluck('type', 'id');

        return $this->targetTypes->get($id);
    }

    /**
     * Number of likes of an observation, and whether the logged-in user is
     * one of them.
     *
     * @return array{count: int, liked: bool}
     */
    public function likes(mixed $observationId): array
    {
        $id = (int) $observationId;
        if ($id <= 0) {
            return ['count' => 0, 'liked' => false];
        }
        if (! isset($this->likes[$id])) {
            $this->preloadLikes(collect([$id]));
        }

        return $this->likes[$id];
    }

    /**
     * The observations of an observer on a day, in the order of the detail
     * URL's index (time, then id), with only the columns needed to pick one.
     *
     * @return Collection<int, Observation>
     */
    public function sameDay(string $username, string $date): Collection
    {
        $key = $username.'|'.$date;
        if (! isset($this->sameDay[$key])) {
            $this->loadSameDay([$username => [$date]]);
        }

        return $this->sameDay[$key];
    }

    /** Drop the kept observations per day, after an observation changed. */
    public function forgetSameDay(): void
    {
        $this->sameDay = [];
    }

    private function model(string $class, mixed $id): ?Model
    {
        if ($id === null || $id === '' || (is_numeric($id) && (int) $id <= 0)) {
            return null;
        }
        $id = is_numeric($id) ? (int) $id : $id;
        if (! array_key_exists($id, $this->models[$class] ?? [])) {
            $this->models[$class][$id] = $class::find($id);
        }

        return $this->models[$class][$id];
    }

    private function preloadUsers(Collection $usernames): void
    {
        $missing = $usernames->filter()->map(fn ($name) => (string) $name)->unique()
            ->reject(fn ($name) => array_key_exists(mb_strtolower($name), $this->users))->values();
        if ($missing->isEmpty()) {
            return;
        }
        // Matched as stored, so the username index is used; a name that
        // differs only in case is looked up by user() when needed.
        $found = User::whereIn('username', $missing)->get();
        foreach ($found as $user) {
            $this->users[mb_strtolower($user->username)] = $user;
        }
    }

    private function preloadTargets(Collection $names): void
    {
        $missing = $names->filter()->map(fn ($name) => (string) $name)->unique()
            ->reject(fn ($name) => array_key_exists($name, $this->targets))->values();
        if ($missing->isEmpty()) {
            return;
        }
        $found = Target::whereIn('name', $missing)->get()->keyBy('name');
        foreach ($missing as $name) {
            $this->targets[$name] = $found->get($name);
        }
    }

    private function preloadModels(string $class, Collection $ids): void
    {
        $missing = $ids->filter(fn ($id) => is_numeric($id) && (int) $id > 0)->map(fn ($id) => (int) $id)->unique()
            ->reject(fn ($id) => array_key_exists($id, $this->models[$class] ?? []))->values();
        if ($missing->isEmpty()) {
            return;
        }
        $found = $class::whereIn('id', $missing)->get()->keyBy('id');
        foreach ($missing as $id) {
            $this->models[$class][$id] = $found->get($id);
        }
    }

    private function preloadLikes(Collection $ids): void
    {
        $missing = $ids->map(fn ($id) => (int) $id)->filter()->unique()
            ->reject(fn ($id) => isset($this->likes[$id]))->values();
        if ($missing->isEmpty()) {
            return;
        }
        $userId = Auth::id();
        $rows = ObservationLike::where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->whereIn('observation_id', $missing)
            ->groupBy('observation_id')
            ->selectRaw('observation_id, COUNT(*) AS likes_count')
            ->selectRaw('SUM(CASE WHEN user_id = ? THEN 1 ELSE 0 END) AS liked_by_me', [$userId ?? 0])
            ->get()
            ->keyBy('observation_id');
        foreach ($missing as $id) {
            $row = $rows->get($id);
            $this->likes[$id] = [
                'count' => (int) ($row->likes_count ?? 0),
                'liked' => $userId !== null && (int) ($row->liked_by_me ?? 0) > 0,
            ];
        }
    }

    private function preloadSameDay(Collection $observations): void
    {
        $days = [];
        foreach ($observations as $observation) {
            if ($observation->observer_username && $observation->observed_on) {
                $days[$observation->observer_username][] = $observation->observed_on->format('Y-m-d');
            }
        }
        $this->loadSameDay($days);
    }

    /**
     * @param  array<string, array<int, string>>  $days  dates per username
     */
    private function loadSameDay(array $days): void
    {
        foreach ($days as $username => $dates) {
            $days[$username] = array_values(array_filter(
                array_unique($dates),
                fn ($date) => ! isset($this->sameDay[$username.'|'.$date])
            ));
        }
        $days = array_filter($days);
        if ($days === []) {
            return;
        }

        // One query for all days, per observer on the (observer_username, observed_on) index.
        $rows = Observation::query()
            ->where(function ($query) use ($days) {
                foreach ($days as $username => $dates) {
                    $query->orWhere(fn ($q) => $q->where('observer_username', $username)->whereIn('observed_on', $dates));
                }
            })
            ->orderBy('observed_time')
            ->orderBy('id')
            ->get(['id', 'observer_username', 'observed_on', 'observed_time', 'target_name'])
            // Lowercased, as MySQL compares the username case-insensitively.
            ->groupBy(fn ($row) => mb_strtolower($row->observer_username).'|'.$row->observed_on->format('Y-m-d'));

        foreach ($days as $username => $dates) {
            foreach ($dates as $date) {
                $this->sameDay[$username.'|'.$date] = $rows->get(mb_strtolower($username).'|'.$date, collect())->values();
            }
        }
    }
}
