<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait DeferredPagination
{
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

        $total = $query->toBase()->getCountForPagination();

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
}
