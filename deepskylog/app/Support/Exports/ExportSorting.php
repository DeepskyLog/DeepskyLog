<?php

namespace App\Support\Exports;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PowerComponents\Turbine\DataSource\Processors\Database\Pipelines\Sorting;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

/**
 * PowerGrid's export (Turbine's ExportEngine::prepareDataset()) sorts with
 * orderBy($sortField) on the column's field as such, ignoring its sortUsing()
 * and its data field: an export of a table sorted on a column such as Name
 * (field name_link, sorted with a closure) asked the database for a column
 * name_link and failed with a 500 Server Error. The table itself sorts with
 * Turbine's Sorting pipeline before transformQuery(), which never orders on
 * such a field as such, so finding that order in transformQuery() means an
 * export: it is replaced by the table's own sorting.
 */
class ExportSorting
{
    public static function fix(PowerGridComponent $component, mixed $query): mixed
    {
        $base = match (true) {
            $query instanceof EloquentBuilder => $query->getQuery(),
            $query instanceof QueryBuilder => $query,
            default => null,
        };

        $sortField = $component->state()->sortField;

        if ($base === null || ! is_string($sortField) || $sortField === '' || empty($base->orders)) {
            return $query;
        }

        if ($component->getSortCallback($sortField) === null && $component->resolveSortField($sortField) === $sortField) {
            return $query;
        }

        $ordersOnField = collect($base->orders)
            ->contains(fn ($order) => ($order['type'] ?? 'Basic') !== 'Raw' && ($order['column'] ?? null) === $sortField);

        if (! $ordersOnField) {
            return $query;
        }

        $base->orders = null;
        $base->bindings['order'] = [];

        return (new Sorting($component))->handle($query, fn ($query) => $query);
    }
}
