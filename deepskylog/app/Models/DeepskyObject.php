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
        if ($hours < 0) { $hours += 24.0; }
        $h = floor($hours);
        $mFloat = ($hours - $h) * 60.0;
        $m = floor($mFloat);
        $s = ($mFloat - $m) * 60.0;
        $s = round($s, 1);
        if ($s >= 60.0) { $s -= 60.0; $m += 1; }
        if ($m >= 60) { $m -= 60; $h += 1; }
        if ($h >= 24) { $h = $h % 24; }
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
        if ($sec >= 60.0) { $sec -= 60.0; $min += 1; }
        if ($min >= 60) { $min -= 60; $deg += 1; }
        return sprintf('%s%d°%02d\'%04.1f"', $sign, $deg, $min, $sec);
    }
}
