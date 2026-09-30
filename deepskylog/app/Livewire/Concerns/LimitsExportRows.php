<?php

namespace App\Livewire\Concerns;

use PowerComponents\LivewirePowerGrid\Traits\WithExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use WireUi\Traits\WireUiActions;

/**
 * PowerGrid's CSV/XLS export, refused when the table holds more rows than one
 * web request can load. Use instead of WithExport on public tables.
 */
trait LimitsExportRows
{
    use WireUiActions;
    use WithExport {
        exportToCsv as protected powerGridExportToCsv;
        exportToXLS as protected powerGridExportToXLS;
    }

    /** Most rows one CSV/XLS export may hold. */
    protected function maxExportRows(): int
    {
        return 5000;
    }

    public function exportToCsv(bool $selected = false): BinaryFileResponse|bool
    {
        if ($this->exportTooLarge($selected)) {
            return false;
        }

        return $this->powerGridExportToCsv($selected);
    }

    public function exportToXLS(bool $selected = false): BinaryFileResponse|bool
    {
        if ($this->exportTooLarge($selected)) {
            return false;
        }

        return $this->powerGridExportToXLS($selected);
    }

    /**
     * Whether the export would hold more rows than maxExportRows(); if so,
     * tell the user to narrow the table down. total() counts the rows left
     * after the table's search and filters, as the export does.
     */
    protected function exportTooLarge(bool $selected): bool
    {
        $rows = $selected ? count($this->checkboxValues) : (int) $this->total();
        $max = $this->maxExportRows();

        if ($rows <= $max) {
            return false;
        }

        $this->notification()->error(
            __('Too many rows to export'),
            __('This table has :count rows, but at most :max rows can be exported. Please narrow down your search.', [
                'count' => number_format($rows),
                'max' => number_format($max),
            ])
        );

        return true;
    }
}
