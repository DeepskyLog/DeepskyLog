<?php

namespace App\Livewire\Concerns;

use App\Support\Exports\ExportSorting;

/**
 * Exports a PowerGrid table in the order the table shows, also when it is
 * sorted on a column with sortUsing() or another data field (see
 * ExportSorting). Tables with SortsStably get this through that trait.
 */
trait ExportsInTableOrder
{
    public function transformQuery(mixed $query): mixed
    {
        return ExportSorting::fix($this, parent::transformQuery($query));
    }
}
