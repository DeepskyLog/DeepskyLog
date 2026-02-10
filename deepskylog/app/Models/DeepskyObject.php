<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeepskyObject extends Model
{
    // Use default connection at runtime
    protected $table = 'objects';

    public $timestamps = false;

    /**
     * Instance helper: formatted RA
     */
    public function formattedRa(): ?string
    {
        return static::formatRa($this->ra ?? null);
    }

    /**
     * Instance helper: formatted Dec
     */
    public function formattedDec(): ?string
    {
        return static::formatDec($this->decl ?? null);
    }

    /**
     * Static formatter for Right Ascension.
     */
    public static function formatRa($ra): ?string
    {
        if ($ra === null || $ra === '') {
            return null;
        }
        $v = floatval($ra);
        if ($v > 24) {
            $hours = $v / 15.0;
        } else {
            $hours = $v;
        }
        $hours = fmod($hours, 24.0);
        if ($hours < 0) {
            $hours += 24.0;
        }
        $h = floor($hours);
        $mFloat = ($hours - $h) * 60.0;
        $m = floor($mFloat);
        $s = ($mFloat - $m) * 60.0;
        $s = round($s, 1);
        if ($s >= 60.0) {
            $s -= 60.0;
            $m += 1;
        }
        if ($m >= 60) {
            $m -= 60;
            $h += 1;
        }
        if ($h >= 24) {
            $h = $h % 24;
        }
        return sprintf('%02dh%02dm%04.1fs', $h, $m, $s);
    }

    /**
     * Static formatter for Declination.
     */
    public static function formatDec($dec): ?string
    {
        if ($dec === null || $dec === '') {
            return null;
        }
        $v = floatval($dec);
        $sign = ($v < 0) ? '-' : '';
        $abs = abs($v);
        $deg = floor($abs);
        $mFloat = ($abs - $deg) * 60.0;
        $min = floor($mFloat);
        $sec = ($mFloat - $min) * 60.0;
        $sec = round($sec, 1);
        if ($sec >= 60.0) {
            $sec -= 60.0;
            $min += 1;
        }
        if ($min >= 60) {
            $min -= 60;
            $deg += 1;
        }
        return sprintf('%s%d°%02d\'%04.1f"', $sign, $deg, $min, $sec);
    }

    /**
     * Parse RA in various formats to decimal degrees.
     * Accepts: hours (0..24) as number or string, HH MM SS, HH:MM:SS, or degrees.
     * Returns decimal degrees (0..360) or null on failure.
     */
    public static function raToDecimal($ra): ?float
    {
        if ($ra === null || $ra === '') return null;
        $s = trim((string)$ra);
        // If numeric, accept either hours (<=24) or degrees (>24)
        if (is_numeric($s)) {
            $v = floatval($s);
            if ($v <= 24.0) {
                return $v * 15.0;
            }
            return $v;
        }

        // Accept HH:MM:SS or HH MM SS or HhMmSs formats
        if (preg_match('/^(\d{1,2})[:h\s](\d{1,2})[:m\s](\d+(?:\.\d+)?)/i', $s, $m)) {
            $h = floatval($m[1]);
            $min = floatval($m[2]);
            $sec = floatval($m[3]);
            $hours = $h + $min / 60.0 + $sec / 3600.0;
            return fmod($hours * 15.0, 360.0);
        }

        // Fallback: try space separated H M S
        if (preg_match('/^(\d{1,2})\s+(\d{1,2})\s+(\d+(?:\.\d+)?)/', $s, $m)) {
            $h = floatval($m[1]);
            $min = floatval($m[2]);
            $sec = floatval($m[3]);
            $hours = $h + $min / 60.0 + $sec / 3600.0;
            return fmod($hours * 15.0, 360.0);
        }

        return null;
    }

    /**
     * Parse Declination in various formats to decimal degrees.
     * Accepts: decimal degrees or D M S (with optional sign), returns degrees.
     */
    public static function decToDecimal($dec): ?float
    {
        if ($dec === null || $dec === '') return null;
        $s = trim((string)$dec);
        if (is_numeric($s)) return floatval($s);

        // Match ±DD:MM:SS or D M S
        if (preg_match('/^([\+\-]?\d{1,3})[:°\s](\d{1,2})[:\'"\s](\d+(?:\.\d+)?)/', $s, $m)) {
            $sign = strpos($m[1], '-') !== false ? -1 : 1;
            $deg = abs(floatval($m[1]));
            $min = floatval($m[2]);
            $sec = floatval($m[3]);
            return $sign * ($deg + $min / 60.0 + $sec / 3600.0);
        }

        // Fallback: try space separated with optional sign
        if (preg_match('/^([\+\-]?\d{1,3})\s+(\d{1,2})\s+(\d+(?:\.\d+)?)/', $s, $m)) {
            $sign = strpos($m[1], '-') !== false ? -1 : 1;
            $deg = abs(floatval($m[1]));
            $min = floatval($m[2]);
            $sec = floatval($m[3]);
            return $sign * ($deg + $min / 60.0 + $sec / 3600.0);
        }

        return null;
    }
}
