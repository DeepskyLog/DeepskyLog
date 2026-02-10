<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectsOld extends Model
{
    public $timestamps = false;
    protected $table = 'objects';

    // This model is intended for seeding/legacy access only. Use mysqlOld connection.
    protected $connection = 'mysqlOld';

    /**
     * Format Right Ascension value into hours/minutes/seconds (e.g. 00h42m44.2s).
     * Accepts RA in hours (0..24) or degrees (0..360). If value > 24 we assume degrees and convert.
     * Returns null for empty input.
     */
    public static function formatRa($ra): ?string
    {
        if ($ra === null || $ra === '') {
            return null;
        }
        $v = floatval($ra);
        // If RA looks like degrees (>24) convert to hours
        if ($v > 24) {
            $hours = $v / 15.0;
        } else {
            $hours = $v;
        }

        // Normalize to 0..24
        $hours = fmod($hours, 24.0);
        if ($hours < 0) {
            $hours += 24.0;
        }

        $h = floor($hours);
        $mFloat = ($hours - $h) * 60.0;
        $m = floor($mFloat);
        $s = ($mFloat - $m) * 60.0;

        // Round seconds to 1 decimal and cascade if needed
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
     * Format Declination into degrees/arcminutes/arcseconds (e.g. 41°16'06.5").
     * Returns null for empty input.
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

        // Round seconds to 1 decimal and cascade if needed
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

    public function long_type(): string
    {
        return __(''.TargetType::where('id', $this->type)->first()->type.'');
    }
}
