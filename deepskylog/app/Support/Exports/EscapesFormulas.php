<?php

namespace App\Support\Exports;

use App\Support\SpreadsheetCell;
use Illuminate\Support\Collection;

/**
 * PowerGrid's CSV/XLSX export with the cells a spreadsheet would read as a
 * formula escaped (see SpreadsheetCell).
 */
trait EscapesFormulas
{
    public function prepare(Collection $data, array $columns, bool $stripTags): array
    {
        $prepared = parent::prepare($data, $columns, $stripTags);

        $prepared['headers'] = SpreadsheetCell::escapeRow((array) $prepared['headers']);
        $prepared['rows'] = collect($prepared['rows'])
            ->map(fn ($row) => SpreadsheetCell::escapeRow((array) $row))
            ->all();

        return $prepared;
    }
}
