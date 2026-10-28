<?php

namespace App\Livewire\Concerns;

use Closure;

/**
 * Sorting for PowerGrid columns whose cell can be empty: the rows with an empty
 * cell come last whichever way the column is sorted, instead of first when it
 * is sorted ascending.
 */
trait SortsEmptyLast
{
    /**
     * A sortUsing() callback that sorts on $column with the empty cells last.
     * $emptyWhen is the SQL condition for an empty cell, by default NULL or an
     * empty string; pass one for a number column, where MariaDB reads '' as 0.
     */
    protected function sortEmptyLast(string $column, ?string $emptyWhen = null, array $bindings = []): Closure
    {
        $emptyWhen ??= "{$column} IS NULL OR {$column} = ''";

        return function ($query, string $direction) use ($column, $emptyWhen, $bindings) {
            $query->orderByRaw("CASE WHEN {$emptyWhen} THEN 1 ELSE 0 END", $bindings)
                ->orderBy($column, $direction);
        };
    }
}
