<?php

namespace App\Services\Clubs;

use App\Models\Club;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Which members of a club have seen which objects of an observing list the
 * club shares: an observation of the member with the list item's name as
 * target_name (as ClubRanking and the observing goals count a list),
 * not-seen observations left out, at any time.
 *
 * Cached CACHE_MINUTES per club, list, members and items; a new
 * observation shows up when the cache expires.
 */
class ClubListProgress
{
    public const CACHE_MINUTES = 10;

    /** The objects shown per page. */
    public const PER_PAGE = 100;

    /** The filters of the objects: all, seen by nobody, not seen by the viewer. */
    public const FILTERS = ['all', 'nobody', 'not_me'];

    /**
     * @return array{
     *     members: Collection<int, array{id: int, name: string, slug: string, seen: int}>,
     *     objects: Collection<int, array{name: string, slug: ?string, added_by: ?string, seen_by: list<int>}>,
     *     total: int,
     *     seen_by_anyone: int,
     * }
     */
    public static function for(Club $club, ObservingList $list): array
    {
        $members = $club->members()->orderBy('users.name')->get(['users.id', 'users.name', 'users.username', 'users.slug']);
        $items   = DB::table('observing_list_items')
            ->where('observing_list_id', $list->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['object_name', 'added_by_user_id', 'updated_at']);

        $key = 'club_list_progress:'.$club->id.':'.$list->id.':'
            .md5($members->pluck('id')->implode(',').'|'.$items->count().'|'.$items->max('updated_at'));
        $seen = Cache::remember($key, now()->addMinutes(self::CACHE_MINUTES), fn () => self::seen($members, $items->pluck('object_name')));

        $userIds = $members->mapWithKeys(fn (User $member) => [mb_strtolower((string) $member->username) => (int) $member->id]);
        $adders  = User::whereIn('id', $items->pluck('added_by_user_id')->filter()->unique())->pluck('name', 'id');
        $slugs   = DB::table('objects')->whereIn('name', $items->pluck('object_name')->unique()->all())->pluck('slug', 'name')
            ->mapWithKeys(fn ($slug, $name) => [mb_strtolower((string) $name) => (string) $slug]);

        $objects = $items->unique(fn ($item) => mb_strtolower((string) $item->object_name))->map(function ($item) use ($seen, $userIds, $adders, $slugs) {
            $name = mb_strtolower((string) $item->object_name);

            return [
                'name'     => (string) $item->object_name,
                'slug'     => $slugs[$name] ?? null,
                'added_by' => isset($adders[$item->added_by_user_id]) ? html_entity_decode((string) $adders[$item->added_by_user_id], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
                'seen_by'  => collect($seen[$name] ?? [])->map(fn (string $username) => $userIds[$username] ?? null)->filter()->values()->all(),
            ];
        })->values();

        $counts = $objects->pluck('seen_by')->flatten()->countBy();

        return [
            'members' => $members->map(fn (User $member) => [
                'id'   => (int) $member->id,
                'name' => html_entity_decode((string) $member->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'slug' => (string) ($member->slug ?: $member->username),
                'seen' => (int) ($counts[$member->id] ?? 0),
            ])->sort(fn (array $a, array $b) => [$b['seen'], Str::lower($a['name'])] <=> [$a['seen'], Str::lower($b['name'])])->values(),
            'objects'        => $objects,
            'total'          => $objects->count(),
            'seen_by_anyone' => $objects->filter(fn (array $object) => $object['seen_by'] !== [])->count(),
        ];
    }

    /**
     * The objects of the result the filter keeps.
     *
     * @param  Collection<int, array{seen_by: list<int>}>  $objects
     */
    public static function filter(Collection $objects, string $filter, ?User $viewer): Collection
    {
        return match ($filter) {
            'nobody' => $objects->filter(fn (array $object) => $object['seen_by'] === [])->values(),
            'not_me' => $objects->filter(fn (array $object) => $viewer === null || ! in_array((int) $viewer->id, $object['seen_by'], true))->values(),
            default  => $objects,
        };
    }

    /**
     * Per object name (lower case) the usernames (lower case) of the members
     * who saw it.
     *
     * @param  Collection<int, User>  $members
     * @param  Collection<int, string>  $names
     * @return array<string, list<string>>
     */
    private static function seen(Collection $members, Collection $names): array
    {
        $usernames = $members->pluck('username')->filter()->values()->all();
        $names     = $names->filter()->unique()->values();
        if ($usernames === [] || $names->isEmpty()) {
            return [];
        }

        $seen = [];
        foreach ($names->chunk(1000) as $chunk) {
            $rows = DB::table('observations')
                ->whereIn('observations.observer_username', $usernames)
                ->whereIn('observations.target_name', $chunk->all())
                ->whereNotExists(fn (Builder $details) => $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN))
                ->distinct()
                ->get(['observations.target_name', 'observations.observer_username']);
            foreach ($rows as $row) {
                $name     = mb_strtolower((string) $row->target_name);
                $username = mb_strtolower((string) $row->observer_username);
                if (! in_array($username, $seen[$name] ?? [], true)) {
                    $seen[$name][] = $username;
                }
            }
        }

        return $seen;
    }
}
