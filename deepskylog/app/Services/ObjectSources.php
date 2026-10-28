<?php

namespace App\Services;

use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The catalogs and observing lists whose objects an observer can count: the
 * ranking and catalog page of /observers and the observing goals. A source is
 * ['key' => 'catalog:m' | 'list:12', 'type' => 'catalog' | 'list', ...] with
 * the catalog code or the list id, and a label.
 */
class ObjectSources
{
    /**
     * The list of catalogs is fresh for an hour, and for a day after that
     * the old list is shown while it is made again after the response.
     */
    private const CACHE_SECONDS = [3600, 86400];

    /**
     * All catalogs, and the lists the viewer owns or subscribed to.
     */
    public static function options(?User $viewer): Collection
    {
        $catalogs = Cache::flexible('observers:catalogs', self::CACHE_SECONDS, fn() => DB::table('objectnames')
            ->whereNotNull('catalog')
            ->where('catalog', '!=', '')
            ->distinct()
            ->orderBy('catalog')
            ->pluck('catalog')
            ->all());

        $catalogSources = collect($catalogs)
            ->map(function ($catalog) {
                $catalog = (string) $catalog;

                return [
                    'key' => 'catalog:' . Str::lower($catalog),
                    'type' => 'catalog',
                    'catalog' => $catalog,
                    'short_label' => $catalog,
                    'label' => $catalog,
                ];
            })
            ->values();

        if (!$viewer) {
            return $catalogSources;
        }

        $ownedLists = $viewer->observingLists()
            ->with('owner:id,name')
            ->get(['observing_lists.id', 'observing_lists.name', 'observing_lists.slug', 'observing_lists.owner_user_id']);
        $subscribedLists = $viewer->subscribedObservingLists()
            ->with('owner:id,name')
            ->get(['observing_lists.id', 'observing_lists.name', 'observing_lists.slug', 'observing_lists.owner_user_id']);

        $listSources = $ownedLists
            ->concat($subscribedLists)
            ->unique('id')
            ->sortBy(fn(ObservingList $list) => Str::lower((string) $list->name))
            ->values()
            ->map(function (ObservingList $list) use ($viewer) {
                $name = html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $ownerName = html_entity_decode((string) optional($list->owner)->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                return [
                    'key' => 'list:' . $list->id,
                    'type' => 'list',
                    'list_id' => (int) $list->id,
                    'list_slug' => (string) $list->slug,
                    'short_label' => $name,
                    'label' => $list->owner_user_id === $viewer->id || $ownerName === '' ? $name : $name . ' (' . $ownerName . ')',
                ];
            });

        return $catalogSources->concat($listSources)->values();
    }

    public static function resolve(string $selectedKey, ?User $viewer, ?Collection $sourceOptions = null): array
    {
        $sourceOptions ??= self::options($viewer);
        $selected = $sourceOptions->firstWhere('key', $selectedKey);

        if (!$selected) {
            $selected = $sourceOptions->firstWhere('key', 'catalog:m') ?? $sourceOptions->first();
        }

        return $selected ?? [
            'key' => 'catalog:m',
            'type' => 'catalog',
            'catalog' => 'M',
            'short_label' => 'M',
            'label' => 'M',
        ];
    }

    public static function objects(array $source): Collection
    {
        if (($source['type'] ?? null) === 'list') {
            $items = DB::table('observing_list_items')
                ->where('observing_list_id', $source['list_id'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['object_name']);

            $objectNames = $items->pluck('object_name')->filter()->unique()->values();
            $slugLookup = DB::table('objects')
                ->whereIn('name', $objectNames->all())
                ->pluck('slug', 'name');

            if ($slugLookup->count() < $objectNames->count()) {
                $missingNames = $objectNames->reject(fn($name) => $slugLookup->has($name))->values();

                if ($missingNames->isNotEmpty()) {
                    $fallbackSlugs = DB::table('objectnames')
                        ->whereIn('objectname', $missingNames->all())
                        ->whereNotNull('slug')
                        ->orderBy('objectname')
                        ->get(['objectname', 'slug'])
                        ->pluck('slug', 'objectname');

                    $slugLookup = $slugLookup->union($fallbackSlugs);
                }
            }

            return $items->map(function ($item) use ($slugLookup) {
                $objectName = (string) $item->object_name;

                return [
                    'object_name' => $objectName,
                    'display_name' => $objectName,
                    'slug' => (string) ($slugLookup[$objectName] ?? Str::slug($objectName, '-')),
                ];
            })->values();
        }

        $catalog = (string) ($source['catalog'] ?? '');
        $rows = DB::table('objectnames as objectnames')
            ->leftJoin('objects as objects', 'objects.name', '=', 'objectnames.objectname')
            ->where('objectnames.catalog', $catalog)
            ->select([
                'objectnames.objectname',
                'objectnames.catalog',
                'objectnames.catindex',
                DB::raw('MAX(COALESCE(objects.slug, objectnames.slug)) as resolved_slug'),
            ])
            ->groupBy('objectnames.objectname', 'objectnames.catalog', 'objectnames.catindex')
            ->get()
            ->map(function ($row) {
                $displayName = trim((string) $row->catalog . ' ' . (string) $row->catindex);

                return [
                    'object_name' => (string) $row->objectname,
                    'display_name' => $displayName !== '' ? $displayName : (string) $row->objectname,
                    'slug' => (string) ($row->resolved_slug ?: Str::slug((string) ($displayName !== '' ? $displayName : $row->objectname), '-')),
                    'sort_key' => self::catalogSortKey((string) $row->catindex, (string) $row->objectname),
                ];
            })
            ->sortBy(fn(array $row) => $row['sort_key'])
            ->values()
            ->map(function (array $row) {
                unset($row['sort_key']);

                return $row;
            });

        return $rows;
    }

    private static function catalogSortKey(string $catalogIndex, string $fallback): string
    {
        $catalogIndex = trim($catalogIndex);
        if ($catalogIndex !== '' && ctype_digit($catalogIndex)) {
            return '0-' . str_pad($catalogIndex, 10, '0', STR_PAD_LEFT);
        }

        return '1-' . Str::lower($catalogIndex !== '' ? $catalogIndex : $fallback);
    }

    /**
     * The source with this key, if the viewer may count it: any catalog, a
     * list they own or subscribed to.
     */
    public static function find(string $key, ?User $viewer): ?array
    {
        return self::options($viewer)->firstWhere('key', $key);
    }
}
