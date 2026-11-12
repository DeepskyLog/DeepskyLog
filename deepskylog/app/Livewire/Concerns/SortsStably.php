<?php

namespace App\Livewire\Concerns;

use App\Support\Exports\ExportSorting;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * A stable order for PowerGrid tables of a model with a name: after the
 * column the observer sorts on, the rows are sorted on their name and id.
 * Without it, rows with the same value (all instruments that are active)
 * came back in an order MariaDB chose, so sorting on such a column looked
 * as if it did nothing, and a row could be on two pages or on none.
 * Also exports in the table's order (ExportSorting, before the name and id
 * are added).
 */
trait SortsStably
{
    public function transformQuery(mixed $query): mixed
    {
        $query = ExportSorting::fix($this, parent::transformQuery($query));

        if ($query instanceof EloquentBuilder) {
            $table = $query->getModel()->getTable();
            $query->orderBy("{$table}.name")->orderBy("{$table}.id");
        } elseif ($query instanceof QueryBuilder && is_string($query->from)) {
            $query->orderBy("{$query->from}.name")->orderBy("{$query->from}.id");
        }

        return $query;
    }
}
