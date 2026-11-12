<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The observation listings (/observations and the lists of an observer or
 * object) as cards or as a compact list, one row per observation, as the
 * old site's "list" view. ?view=list|cards chooses; a logged-in observer's
 * choice is kept in the user_preferences row PREFERENCE, so the listings
 * open the way they last chose. Guests only have the query parameter, which
 * keeps the response cache of their pages right.
 */
class ObservationListView
{
    public const PREFERENCE = 'observations_view';

    public const CARDS = 'cards';

    public const LIST = 'list';

    /**
     * The view of this request: the query parameter (remembered for a
     * logged-in observer), else the observer's last choice, else cards.
     */
    public static function current(Request $request, ?User $user): string
    {
        $asked = $request->query('view');

        if (in_array($asked, [self::CARDS, self::LIST], true)) {
            if ($user !== null && self::preference($user) !== $asked) {
                DB::table('user_preferences')->upsert(
                    [['user_id' => $user->id, 'key' => self::PREFERENCE, 'value' => $asked, 'created_at' => now(), 'updated_at' => now()]],
                    ['user_id', 'key'],
                    ['value', 'updated_at']
                );
            }

            return $asked;
        }

        return ($user !== null ? self::preference($user) : null) ?? self::CARDS;
    }

    private static function preference(User $user): ?string
    {
        $value = DB::table('user_preferences')->where('user_id', $user->id)->where('key', self::PREFERENCE)->value('value');

        return in_array($value, [self::CARDS, self::LIST], true) ? $value : null;
    }

    /**
     * The visibility (1–7) of the deep-sky observations among these, keyed
     * by observation id, in one query.
     *
     * @param  iterable<mixed>  $observations
     * @return Collection<int, int>
     */
    public static function visibilities(iterable $observations): Collection
    {
        $ids = collect($observations instanceof \Illuminate\Contracts\Pagination\Paginator ? $observations->items() : $observations)
            ->filter(fn ($o) => $o instanceof Observation && $o->target_type === Observation::TYPE_DEEPSKY)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table('deepsky_observation_details')
            ->whereIn('observation_id', $ids)
            ->whereNotNull('visibility')
            ->pluck('visibility', 'observation_id')
            ->map(fn ($value) => (int) $value);
    }
}
