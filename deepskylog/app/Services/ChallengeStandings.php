<?php

namespace App\Services;

use App\Models\ObservingChallenge;
use App\Models\ObservingGoal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The leaderboard and the shared progress of an observing challenge, from
 * the goals of its participants (ObservingGoal::progress()): the
 * participants ranked by the number of objects they saw (drew) in the period,
 * then by who reached the target first, then by who got to their number
 * first; and per object of the source how many participants saw it. The
 * source is read once for all participants.
 *
 * Cached CACHE_MINUTES (a day once the challenge ended), forgotten when
 * someone joins or leaves or the challenge changes; new observations show
 * up when the cache expires.
 */
class ChallengeStandings
{
    public const CACHE_MINUTES = 10;

    /**
     * @return array{rows: list<array{user_id: int, name: string, slug: string, count: int, target: int, reached_on: ?string, last_on: ?string, rank: int}>, objects: list<array{object_name: string, display_name: string, slug: string, seen_by: int}>, total: int, seen: int}
     */
    public static function for(ObservingChallenge $challenge): array
    {
        // The standings of an ended challenge only change when old observations are edited
        $expires = $challenge->status() === 'ended' ? now()->addDay() : now()->addMinutes(self::CACHE_MINUTES);

        return Cache::remember(self::key($challenge), $expires, fn () => self::compute($challenge));
    }

    public static function forget(ObservingChallenge $challenge): void
    {
        Cache::forget(self::key($challenge));
    }

    private static function key(ObservingChallenge $challenge): string
    {
        return 'challenge_standings:' . $challenge->id;
    }

    /**
     * @return array<string, mixed>
     */
    public static function compute(ObservingChallenge $challenge): array
    {
        $source  = $challenge->source();
        $objects = $source === null ? collect() : ObjectSources::objects($source)->unique('object_name')->values();
        $names   = $objects->pluck('object_name')->filter()->values();

        $goals = $challenge->goals()
            ->with('user:id,name,username,slug')
            ->get()
            ->filter(fn (ObservingGoal $goal) => $goal->user !== null)
            ->each(fn (ObservingGoal $goal) => $goal->setRelation('challenge', $challenge)->useSourceObjects($names));

        $seenBy = [];
        $rows   = $goals->map(function (ObservingGoal $goal) use (&$seenBy) {
            $progress = $goal->progress();
            $dates    = $goal->firstObservationDates();
            foreach ($dates->keys() as $name) {
                $seenBy[$name] = ($seenBy[$name] ?? 0) + 1;
            }

            return [
                'user_id' => (int) $goal->user->id,
                'name' => html_entity_decode((string) $goal->user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'slug' => (string) $goal->user->slug,
                'count' => $progress['count'],
                'target' => $progress['target'],
                'reached_on' => $progress['reached_on']?->toDateString(),
                'last_on' => $dates->max(),
            ];
        });

        return [
            'rows' => self::rank($rows),
            'objects' => $objects->map(fn (array $object) => [
                'object_name' => $object['object_name'],
                'display_name' => $object['display_name'],
                'slug' => $object['slug'],
                'seen_by' => $seenBy[mb_strtolower($object['object_name'])] ?? 0,
            ])->all(),
            'total' => $names->count(),
            'seen' => count($seenBy),
        ];
    }

    /**
     * Most objects first; of the same number the one who reached the target
     * first, then the one who got to that number first. The same number and
     * day share a rank.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private static function rank(Collection $rows): array
    {
        $sorted = $rows->sort(function (array $a, array $b) {
            return [$b['count'], $a['reached_on'] ?? '9999', $a['last_on'] ?? '9999', mb_strtolower($a['name'])]
                <=> [$a['count'], $b['reached_on'] ?? '9999', $b['last_on'] ?? '9999', mb_strtolower($b['name'])];
        })->values();

        $ranked   = [];
        $previous = null;
        foreach ($sorted as $index => $row) {
            $tie         = $previous !== null && $previous['count'] === $row['count'] && $previous['reached_on'] === $row['reached_on'] && $previous['last_on'] === $row['last_on'];
            $row['rank'] = $tie ? $previous['rank'] : $index + 1;
            $ranked[]    = $previous = $row;
        }

        return $ranked;
    }
}
