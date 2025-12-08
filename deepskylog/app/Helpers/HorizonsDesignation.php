<?php

namespace App\Helpers;

class HorizonsDesignation
{
    /**
     * Return a canonical designation string for Horizons queries.
     *
     * Strategy:
     * - If a numbered periodic form is present (e.g. "12P" or "12P/Pons-Brooks"),
     *   return the short numbered form ("12P").
     * - Else if a non-periodic form like "C/2023 A1" is present, normalize spacing
     *   and upper-case ("C/2023 A1").
     * - Otherwise return trimmed input.
     */
    public static function canonicalize(?string $in): ?string
    {
        if ($in === null) return null;
        $s = trim((string)$in);
        if ($s === '') return null;

        // If a numbered periodic designation is present, return short form (e.g. 12P)
        if (preg_match('/\b(\d{1,4})\s*[Pp]\b/', $s, $m)) {
            return strtoupper($m[1] . 'P');
        }

        // Match canonical non-periodic like C/2023 A1 or P/2020 V2
        if (preg_match('/\b([PCpc])\s*\/\s*(\d{4})\s*([A-Z0-9-]+)\b/', $s, $m)) {
            $lead = strtoupper($m[1]);
            $year = $m[2];
            $code = strtoupper($m[3]);
            return "{$lead}/{$year} {$code}";
        }

        // If the string contains a slash with a trailing name (e.g. "12P/Pons-Brooks"),
        // attempt to keep the full form but normalize spacing and remove duplicate whitespace.
        if (strpos($s, '/') !== false) {
            $parts = preg_split('/\s*\/\s*/', $s, 2);
            $left = strtoupper(trim($parts[0]));
            $right = isset($parts[1]) ? preg_replace('/\s+/', ' ', trim($parts[1])) : '';
            return $right === '' ? $left : ($left . '/' . $right);
        }

        // Fallback: collapse whitespace and return uppercase for short codes, else trimmed
        $clean = preg_replace('/\s+/', ' ', $s);
        return $clean;
    }
}
