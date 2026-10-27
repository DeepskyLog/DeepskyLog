<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use App\Services\ChallengeStandings;
use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A site-wide observing challenge ("Hickson month", "Arp galaxies this
 * spring"), set by an administrator or database expert: see (or draw)
 * target_count objects (null = all) of a catalog or public observing list
 * between starts_on and ends_on. An observer who joins gets an ObservingGoal
 * linked to it (challenge_id), which counts their progress, is shown on their
 * observer page and in their blog, and follows the challenge when it is
 * edited; ChallengeStandings ranks the participants. The challenge is
 * announced by a news item (kind 'challenge', key 'challenge:{id}').
 */
class ObservingChallenge extends Model
{
    use ClearsResponseCache;
    use Sluggable;

    /** The news item is published this many days before the start. */
    public const ANNOUNCE_DAYS_BEFORE = 7;

    /** The running challenges on the home page. */
    public const HOMEPAGE = 3;

    protected $fillable = [
        'name', 'description', 'catalog', 'observing_list_id', 'source_label', 'target_count',
        'mode', 'new_only', 'starts_on', 'ends_on', 'hidden', 'user_id',
    ];

    protected $attributes = [
        'mode' => 'observed',
        'new_only' => false,
        'hidden' => false,
    ];

    protected $casts = [
        'description' => PurifiedHtml::class,
        'starts_on' => 'date',
        'ends_on' => 'date',
        'new_only' => 'boolean',
        'hidden' => 'boolean',
        'target_count' => 'integer',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'method' => fn (string $string, string $separator) => Str::slug(
                    html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $separator
                ) ?: 'challenge',
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function observingList(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class);
    }

    /** The goals of the participants. */
    public function goals(): HasMany
    {
        return $this->hasMany(ObservingGoal::class, 'challenge_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('hidden', false);
    }

    /** The column: $this->hidden is the model's list of attributes hidden from arrays. */
    public function isHidden(): bool
    {
        return (bool) $this->getAttribute('hidden');
    }

    public function drawings(): bool
    {
        return $this->mode === 'drawn';
    }

    /** 'upcoming', 'active' or 'ended'. */
    public function status(): string
    {
        $today = Carbon::today();

        return match (true) {
            $this->starts_on->gt($today) => 'upcoming',
            $this->ends_on->lt($today) => 'ended',
            default => 'active',
        };
    }

    public function canBeJoined(): bool
    {
        return ! $this->isHidden() && $this->status() !== 'ended' && $this->source() !== null;
    }

    /**
     * The source of ObjectSources, as ObservingGoal::source(); null when the
     * observing list was deleted.
     */
    public function source(): ?array
    {
        return $this->goalFor(null)->source();
    }

    /** What has to be done: "See 20 objects of Hickson", in the reader's language. */
    public function task(): string
    {
        return $this->goalFor(null)->title();
    }

    /**
     * The attributes of the goal of a participant, which follow the challenge.
     *
     * @return array<string, mixed>
     */
    public function goalAttributes(): array
    {
        return [
            'name' => $this->name,
            'catalog' => $this->catalog,
            'observing_list_id' => $this->observing_list_id,
            'source_label' => $this->source_label,
            'target_count' => $this->target_count,
            'mode' => $this->mode,
            'new_only' => $this->new_only,
            'starts_on' => $this->starts_on,
            'ends_on' => $this->ends_on,
            'public' => true,
        ];
    }

    /**
     * The goal of the observer in this challenge: the one they have when
     * they joined, else one that is not saved (to show what they would have),
     * without a name when there is no observer.
     */
    public function goalFor(?User $observer): ObservingGoal
    {
        if ($observer !== null && $this->exists) {
            $goal = $this->goals()->where('user_id', $observer->id)->first();
            if ($goal !== null) {
                return $goal->setRelation('user', $observer)->setRelation('challenge', $this);
            }
        }

        $goal = new ObservingGoal(['challenge_id' => $this->id, 'user_id' => $observer?->id] + $this->goalAttributes());
        if ($observer === null) {
            $goal->name = null;
        } else {
            $goal->setRelation('user', $observer);
        }

        return $goal->setRelation('challenge', $this);
    }

    public function hasJoined(?User $observer): bool
    {
        return $observer !== null && $this->goals()->where('user_id', $observer->id)->exists();
    }

    public function join(User $observer): ObservingGoal
    {
        $goal = $this->goals()->firstOrCreate(['user_id' => $observer->id], $this->goalAttributes());
        ChallengeStandings::forget($this);

        return $goal;
    }

    public function leave(User $observer): void
    {
        $this->goals()->where('user_id', $observer->id)->get()->each->delete();
        ChallengeStandings::forget($this);
    }

    /**
     * Save the challenge, make the goals of the participants follow it and
     * announce it in the news.
     */
    public function saveAndAnnounce(): void
    {
        $this->save();
        $this->goals()->update($this->goalAttributes());
        ChallengeStandings::forget($this);
        $this->announce();
    }

    /**
     * The challenges for the home page: the running ones, and the coming ones
     * the viewer joined or that start within ANNOUNCE_DAYS_BEFORE (announced
     * in the news). Those the viewer joined first, then the running ones,
     * each with the viewer's goal and place on the leaderboard (null when
     * they did not join or it has not started).
     *
     * @return Collection<int, array{challenge: self, goal: ?ObservingGoal, rank: ?int}>
     */
    public static function forHomePage(?User $viewer): Collection
    {
        $today      = Carbon::today();
        $challenges = self::visible()
            ->where('ends_on', '>=', $today->toDateString())
            ->withCount('goals')
            ->orderBy('ends_on')
            ->get();
        $goals = $viewer !== null && $challenges->isNotEmpty()
            ? ObservingGoal::where('user_id', $viewer->id)->whereIn('challenge_id', $challenges->modelKeys())->get()->keyBy('challenge_id')
            : collect();
        $announced = $today->copy()->addDays(self::ANNOUNCE_DAYS_BEFORE);

        return $challenges
            ->filter(fn (self $challenge) => $challenge->status() === 'active' || $goals->has($challenge->id) || $challenge->starts_on->lte($announced))
            ->sortBy(fn (self $challenge) => [$goals->has($challenge->id) ? 0 : 1, $challenge->status() === 'active' ? 0 : 1])
            ->take(self::HOMEPAGE)
            ->map(function (self $challenge) use ($goals, $viewer) {
                $goal = $goals->get($challenge->id)?->setRelation('user', $viewer)->setRelation('challenge', $challenge);

                return [
                    'challenge' => $challenge,
                    'goal' => $goal,
                    'rank' => $goal !== null && $challenge->status() === 'active' ? $challenge->rankOf($viewer) : null,
                ];
            })
            ->values();
    }

    /** The place of the observer on the leaderboard, null when they did not join. */
    public function rankOf(User $observer): ?int
    {
        return collect(ChallengeStandings::for($this)['rows'])->firstWhere('user_id', $observer->id)['rank'] ?? null;
    }

    /**
     * The trophies of the observer: the challenges whose target they
     * reached, newest first, a gold one for the winners (first place when the
     * challenge has ended, ties included).
     *
     * @return Collection<int, array{challenge: self, reached_on: Carbon, winner: bool}>
     */
    public static function trophiesFor(User $observer): Collection
    {
        return ObservingGoal::where('user_id', $observer->id)
            ->whereNotNull('challenge_id')
            ->with('challenge')
            ->get()
            ->filter(fn (ObservingGoal $goal) => $goal->challenge !== null && ! $goal->challenge->isHidden())
            ->map(function (ObservingGoal $goal) use ($observer) {
                $reachedOn = $goal->setRelation('user', $observer)->progress()['reached_on'];
                if ($reachedOn === null) {
                    return null;
                }
                $challenge = $goal->challenge;

                return [
                    'challenge' => $challenge,
                    'reached_on' => $reachedOn,
                    'winner' => $challenge->status() === 'ended' && $challenge->rankOf($observer) === 1,
                ];
            })
            ->filter()
            ->sortByDesc(fn (array $trophy) => $trophy['reached_on']->toDateString())
            ->values();
    }

    /** The news item announcing the challenge. */
    public function newsItem(): ?NewsItem
    {
        return $this->exists ? NewsItem::where('event_key', 'challenge:' . $this->id)->first() : null;
    }

    /**
     * Create or update the news item: published ANNOUNCE_DAYS_BEFORE the
     * start (at once when that has passed), shown on the home page until the
     * end, hidden with the challenge.
     */
    public function announce(): NewsItem
    {
        $item = $this->newsItem() ?? new NewsItem(['kind' => 'challenge', 'event_key' => 'challenge:' . $this->id]);

        $announceAt = $this->starts_on->copy()->subDays(self::ANNOUNCE_DAYS_BEFORE);
        $published  = $item->exists && $item->published_at->lte(now());

        $item->fill([
            'event_at' => $this->starts_on->copy()->startOfDay(),
            'data' => ['challenge_id' => $this->id, 'slug' => $this->slug, 'name' => $this->name],
            'user_id' => $this->user_id,
            'hidden' => $this->isHidden(),
            'published_at' => $published ? $item->published_at : ($announceAt->gt(now()) ? $announceAt : now()),
            'expires_at' => $this->ends_on->copy()->addDay()->startOfDay(),
        ]);
        $item->save();

        return $item;
    }

    protected static function booted(): void
    {
        static::deleting(function (self $challenge) {
            $challenge->newsItem()?->delete();
            $challenge->goals()->delete();
            ChallengeStandings::forget($challenge);
        });
    }
}
