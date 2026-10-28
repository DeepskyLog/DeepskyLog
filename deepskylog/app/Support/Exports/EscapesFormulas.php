<?php

namespace App\Support\Exports;

use App\Support\SpreadsheetCell;
use Generator;

/**
 * PowerGrid's CSV/XLSX export with the cells a spreadsheet would read as a
 * formula escaped (see SpreadsheetCell). PowerGrid escapes them itself, but
 * also a number such as "-12.5", which a declination column then exports as
 * text: its apostrophe is taken off again for numbers.
 */
trait EscapesFormulas
{
    public function streamRows(iterable $data, array $columns, bool $stripTags): Generator
    {
        foreach (parent::streamRows($data, $columns, $stripTags) as $values) {
            yield array_map(
                fn ($value) => SpreadsheetCell::escape(SpreadsheetCell::unescape((string) $value)),
                $values
            );
        }
    }

    public function exportHeaders(array $columns): array
    {
        return SpreadsheetCell::escapeRow(parent::exportHeaders($columns));
    }
}
