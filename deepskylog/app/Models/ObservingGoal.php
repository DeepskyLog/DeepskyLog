<?php

namespace App\Models;

use App\Services\ObjectSources;
use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A personal observing goal: see (or draw) target_count objects of a catalog
 * or observing list between starts_on and ends_on, all of them when
 * target_count is null ("every Messier in one season"). With new_only, only
 * objects the observer had not seen (drawn) before starts_on count. The
 * progress is counted from the observations, so it follows every edit; it
 * is shown on the observer page and, for public goals, in the blog. A goal
 * with a challenge_id is an observer's part in an ObservingChallenge: it
 * follows the challenge and is removed by leaving it.
 */
class ObservingGoal extends Model
{
    use ClearsResponseCache;

    /** The goals an observer may have. */
    public const MAX_PER_USER = 20;

    /** The longest period of a goal, in years. */
    public const MAX_YEARS = 10;

    protected $fillable = [
        'user_id', 'challenge_id', 'name', 'catalog', 'observing_list_id', 'source_label', 'target_count',
        'mode', 'new_only', 'starts_on', 'ends_on', 'public',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'new_only' => 'boolean',
        'public' => 'boolean',
        'target_count' => 'integer',
    ];

    private ?array $progress = null;

    /** @var Collection<string, string>|null */
    private ?Collection $firstDates = null;

    /** @var Collection<int, string>|null */
    private ?Collection $sourceObjects = null;

    /** @var Collection<int, string>|null */
    private ?Collection $countable = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function observingList(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ObservingChallenge::class, 'challenge_id');
    }

    /**
     * Use these names as the objects of the source, so the goals of all
     * participants of a challenge read the source once.
     *
     * @param  Collection<int, string>  $names
     */
    public function useSourceObjects(Collection $names): static
    {
        $this->sourceObjects = $names;

        return $this;
    }

    public function drawings(): bool
    {
        return $this->mode === 'drawn';
    }

    /**
     * The source of ObjectSources the goal counts the objects of, null when
     * its observing list was deleted.
     */
    public function source(): ?array
    {
        if ($this->catalog !== null) {
            return ['key' => 'catalog:' . mb_strtolower($this->catalog), 'type' => 'catalog', 'catalog' => $this->catalog];
        }

        return $this->observing_list_id !== null
            ? ['key' => 'list:' . $this->observing_list_id, 'type' => 'list', 'list_id' => (int) $this->observing_list_id]
            : null;
    }

    /**
     * The name of the goal: the one the observer gave, else one made from
     * the target and the source.
     */
    public function title(): string
    {
        if (trim((string) $this->name) !== '') {
            return (string) $this->name;
        }

        $source = html_entity_decode($this->source_label, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title  = match (true) {
            $this->target_count === null && $this->drawings() => __('Draw all objects of :source', ['source' => $source]),
            $this->target_count === null => __('See all objects of :source', ['source' => $source]),
            $this->drawings() => __('Draw :count objects of :source', ['count' => $this->target_count, 'source' => $source]),
            default => __('See :count objects of :source', ['count' => $this->target_count, 'source' => $source]),
        };

        return $this->new_only ? $title . ' ' . __('(new objects only)') : $title;
    }

    /**
     * How far the observer got: 'count' objects of 'target' (the objects of
     * the source that count, 'total', when the goal is all of them or more
     * than the source has), the date the target was reached, and the status:
     * 'reached', 'upcoming', 'active' or 'ended' (not reached in time).
     *
     * @return array{count: int, target: int, total: int, reached_on: ?Carbon, status: string, days_left: int}
     */
    public function progress(): array
    {
        if ($this->progress !== null) {
            return $this->progress;
        }

        $firstDates = $this->firstObservationDates();
        $total      = $this->countableObjects()->count();
        $target     = min($this->target_count ?? $total, $total);
        $count      = $firstDates->count();
        $reachedOn  = $target > 0 && $count >= $target ? Carbon::parse($firstDates->sort()->values()->get($target - 1)) : null;
        $today      = Carbon::today();

        $status = match (true) {
            $reachedOn !== null => 'reached',
            $this->starts_on->gt($today) => 'upcoming',
            $this->ends_on->lt($today) => 'ended',
            default => 'active',
        };

        return $this->progress = [
            'count' => $count,
            'target' => $target,
            'total' => $total,
            'reached_on' => $reachedOn,
            'status' => $status,
            'days_left' => $status === 'active' ? (int) $today->diffInDays($this->ends_on) + 1 : 0,
        ];
    }

    /**
     * The canonical names of the objects of the source, without those seen
     * (drawn) before the period for a goal of new objects only.
     *
     * @return Collection<int, string>
     */
    public function countableObjects(): Collection
    {
        return $this->countable ??= $this->findCountableObjects();
    }

    private function findCountableObjects(): Collection
    {
        $source = $this->source();
        if ($source === null) {
            return collect();
        }

        $names = $this->sourceObjects ?? ObjectSources::objects($source)->pluck('object_name')->filter()->unique()->values();
        if (! $this->new_only || $names->isEmpty()) {
            return $names;
        }

        $before = $names->chunk(1000)->flatMap(fn (Collection $chunk) => $this->observations($chunk)
            ->where('observations.observed_on', '<', $this->starts_on->toDateString())
            ->distinct()
            ->pluck('observations.target_name')
            ->map(fn ($name) => mb_strtolower((string) $name)))
            ->flip();

        return $names->reject(fn (string $name) => $before->has(mb_strtolower($name)))->values();
    }

    /**
     * The date each countable object was first seen (drawn) in the period,
     * by object name in lower case.
     *
     * @return Collection<string, string>
     */
    public function firstObservationDates(): Collection
    {
        if ($this->firstDates !== null) {
            return $this->firstDates;
        }

        $names = $this->countableObjects();

        return $this->firstDates = $names->chunk(1000)->flatMap(fn (Collection $chunk) => $this->observations($chunk)
            ->whereBetween('observations.observed_on', [$this->starts_on->toDateString(), $this->ends_on->toDateString()])
            ->groupBy('observations.target_name')
            ->selectRaw('observations.target_name, MIN(observations.observed_on) AS first_on')
            ->pluck('first_on', 'observations.target_name')
            ->mapWithKeys(fn ($date, $name) => [mb_strtolower((string) $name) => Carbon::parse($date)->toDateString()]));
    }

    /**
     * The deep-sky observations of the observer in which one of these objects
     * was seen (and drawn, for a goal of drawings).
     */
    private function observations(Collection $names): \Illuminate\Database\Query\Builder
    {
        return DB::table('observations')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->where('observations.observer_username', $this->loadMissing('user')->user->username)
            ->where('observations.target_type', 'deepsky')
            ->where(fn ($query) => $query->whereNull('details.visibility')
                ->orWhere('details.visibility', '!=', Observation::VISIBILITY_NOT_SEEN))
            ->when($this->drawings(), fn ($query) => $query->where('observations.has_drawing', true))
            ->whereIn('observations.target_name', $names->values()->all());
    }

    /**
     * The goals of an observer the viewer may see: all for the observer, the
     * public ones for everybody else. Running goals first, then the coming
     * ones, then the ones that ended, newest first.
     *
     * @return Collection<int, self>
     */
    public static function shownFor(User $observer, ?User $viewer, bool $publicOnly = false): Collection
    {
        $goals = self::query()
            ->where('user_id', $observer->id)
            ->when($publicOnly || ! $viewer?->is($observer), fn ($query) => $query->where('public', true))
            // The goals of a hidden challenge are hidden with it
            ->where(fn ($query) => $query->whereNull('challenge_id')->orWhereHas('challenge', fn ($challenge) => $challenge->where('hidden', false)))
            ->with(['observingList:id,slug', 'challenge:id,slug'])
            ->orderByDesc('ends_on')
            ->orderByDesc('id')
            ->get()
            ->each(fn (self $goal) => $goal->setRelation('user', $observer));

        $order = ['active' => 0, 'upcoming' => 1, 'reached' => 2, 'ended' => 3];

        return $goals->sortBy(fn (self $goal) => $order[$goal->progress()['status']])->values();
    }
}
