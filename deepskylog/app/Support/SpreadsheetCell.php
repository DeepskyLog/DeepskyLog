<?php

namespace App\Support;

/**
 * Text written to a CSV or XLSX file that a spreadsheet would read as a
 * formula (formula or CSV injection): a user's description "=HYPERLINK(...)"
 * would become a live link or command in the observer's spreadsheet.
 */
class SpreadsheetCell
{
    /** First characters that make a spreadsheet read a cell as a formula. */
    private const FORMULA_STARTS = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Prefix text a spreadsheet would read as a formula with an apostrophe,
     * which makes it text. Numbers, and text that is just a number such as
     * "-12.5", are left alone.
     */
    public static function escape(mixed $value): mixed
    {
        if (! is_string($value) || $value === '' || is_numeric($value)
            || ! in_array($value[0], self::FORMULA_STARTS, true)) {
            return $value;
        }

        return "'" . $value;
    }

    /**
     * @param  array<array-key, mixed>  $row
     * @return array<array-key, mixed>
     */
    public static function escapeRow(array $row): array
    {
        return array_map(self::escape(...), $row);
    }

    /**
     * Undo escape() for a file exported by DeepskyLog and imported again.
     */
    public static function unescape(string $value): string
    {
        return strlen($value) > 1 && $value[0] === "'" && in_array($value[1], self::FORMULA_STARTS, true)
            ? substr($value, 1)
            : $value;
    }
}
