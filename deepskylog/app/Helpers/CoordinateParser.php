<?php

namespace App\Helpers;

class CoordinateParser
{
    /**
     * Parse an RA or Dec string into an associative array with decimal degrees and components.
     * If $isRA is true, RA is parsed (hours -> degrees). Otherwise Dec is parsed.
     * Returns ['deg' => float, 'h'|'d' => int, 'm' => int, 's' => float, 'sign' => 1|-1]
     */
    public static function parse(string $str, bool $isRA = false): array
    {
        $tokens = preg_split('/[^0-9+\-.]+/', trim($str));
        $tokens = array_values(array_filter($tokens, fn($t) => $t !== '' && $t !== null));

        $toFloat = fn($v) => is_numeric($v) ? floatval($v) : 0.0;

        if (count($tokens) >= 3) {
            $a = $toFloat($tokens[0]);
            $b = $toFloat($tokens[1]);
            $c = $toFloat($tokens[2]);
            if ($isRA) {
                $hours = $a; $minutes = $b; $seconds = $c;
                $deg = ($hours + $minutes/60.0 + $seconds/3600.0) * 15.0;
                return ['deg' => $deg, 'h' => intval($hours), 'm' => intval($minutes), 's' => $seconds, 'sign' => 1];
            } else {
                $sign = ($a < 0 || str_starts_with($tokens[0], '-')) ? -1 : 1;
                $d = abs($a); $m = $b; $s = $c;
                $deg = $sign * ($d + $m/60.0 + $s/3600.0);
                return ['deg' => $deg, 'd' => intval($d), 'm' => intval($m), 's' => $s, 'sign' => $sign];
            }
        }

        $v = $toFloat($tokens[0] ?? 0.0);
        if ($isRA) {
            if ($v <= 24.0) {
                $deg = $v * 15.0;
                $hours = intval($v);
                $minutes = intval(($v - $hours) * 60);
                $seconds = (($v - $hours) * 60 - $minutes) * 60;
            } else {
                $deg = $v;
                $hoursFloat = $deg / 15.0;
                $hours = intval($hoursFloat);
                $minutes = intval(($hoursFloat - $hours) * 60);
                $seconds = (($hoursFloat - $hours) * 60 - $minutes) * 60;
            }
            return ['deg' => $deg, 'h' => $hours, 'm' => $minutes, 's' => $seconds, 'sign' => 1];
        } else {
            $sign = ($v < 0 || str_starts_with($tokens[0] ?? '', '-')) ? -1 : 1;
            $abs = abs($v);
            $d = intval($abs);
            $m = intval(($abs - $d) * 60);
            $s = (($abs - $d) * 60 - $m) * 60;
            $deg = $sign * $abs;
            return ['deg' => $deg, 'd' => $d, 'm' => $m, 's' => $s, 'sign' => $sign];
        }
    }

    public static function toHMS(array $raParsed): string
    {
        $h = $raParsed['h'] ?? 0;
        $m = $raParsed['m'] ?? 0;
        $s = $raParsed['s'] ?? 0.0;
        return sprintf('%02d %02d %05.2f', $h, $m, $s);
    }

    public static function toDMS(array $decParsed): string
    {
        $sign = ($decParsed['sign'] ?? 1) < 0 ? '-' : '+';
        $d = $decParsed['d'] ?? intval(abs($decParsed['deg'] ?? 0));
        $m = $decParsed['m'] ?? 0;
        $s = $decParsed['s'] ?? 0.0;
        return sprintf('%s%02d %02d %05.2f', $sign, $d, $m, $s);
    }

    public static function toDecimal(array $parsed): float
    {
        return floatval($parsed['deg'] ?? 0.0);
    }
}
