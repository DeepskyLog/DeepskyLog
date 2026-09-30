<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

trait DeferredPagination
{
    /**
     * A saved or deleted row can change every cached total of the table
     * (see deferredTotal()), so it moves the version they carry.
     */
    public static function bootDeferredPagination(): void
    {
        $forget = fn ($model) => Cache::forever(self::deferredTotalVersionKey($model->getTable()), (string) hrtime(true));
        static::saved($forget);
        static::deleted($forget);
    }

    /**
     * Paginate using a "deferred join": first select only the primary keys for
     * the requested page, then fetch the full rows for those keys.
     *
     * A plain `select * ... order by id desc limit 10 offset N` on a large
     * table makes MariaDB read and filesort every full row once the offset
     * gets deep (crawlers walking ?page=N). Selecting only the key lets the
     * offset be resolved from the primary key index, and the wide rows are
     * then read for just the handful of ids on the page.
     */
    public function scopeDeferredPaginate(Builder $query, int $perPage = 15, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $page = ($page !== null && $page >= 1) ? $page : Paginator::resolveCurrentPage($pageName);
        $keyName = $query->getModel()->getKeyName();

        $total = self::deferredTotal($query);

        $ids = $total > 0
            ? (clone $query)->select($keyName)->forPage($page, $perPage)->pluck($keyName)
            : collect();

        $rows = $ids->isEmpty()
            ? collect()
            : $query->getModel()->newQuery()->whereIn($keyName, $ids)->get()->keyBy($keyName);

        // Restore the page order from the id query.
        $items = $query->getModel()->newCollection(
            $ids->map(fn ($id) => $rows->get($id))->filter()->values()->all()
        );

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * The number of rows of the query, cached: counting all observations
     * takes 40 ms on every page of a list, the page itself a few. The cached
     * count carries the version of the table, which a saved or deleted row
     * moves; both are read in one cache lookup.
     */
    private static function deferredTotal(Builder $query): int
    {
        $base = $query->toBase();
        $table = $query->getModel()->getTable();
        $key = 'deferred_total:'.$table.':'.md5($base->toSql().'|'.json_encode($base->getBindings()));
        $versionKey = self::deferredTotalVersionKey($table);

        $cached = Cache::many([$versionKey, $key]);
        $version = (string) ($cached[$versionKey] ?? '');
        $entry = $cached[$key];
        if (is_array($entry) && ($entry['version'] ?? null) === $version) {
            return (int) $entry['total'];
        }

        $total = $base->getCountForPagination();
        Cache::put($key, ['version' => $version, 'total' => $total], now()->addHour());

        return $total;
    }

    private static function deferredTotalVersionKey(string $table): string
    {
        return 'deferred_total_version:'.$table;
    }
}
