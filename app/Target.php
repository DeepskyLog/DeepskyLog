<?php

/**
 * Target eloquent model.
 *
 * PHP Version 7
 *
 * @category Targets
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Target eloquent model.
 *
 * @category Targets
 * @package  DeepskyLog
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class Target extends Model
{
    protected $fillable = ['name', 'type'];

    /**
     * Targets have exactly one target type.
     *
     * @return HasOne The eloquent relationship
     */
    public function type()
    {
        return $this->hasOne('App\TargetType', 'id', 'type');
    }

    /**
     * Targets have exactly one or none constellations.
     *
     * @return HasOne The eloquent relationship
     */
    public function constellation()
    {
        return $this->hasOne('App\Constellations', 'id', 'con');
    }

    /**
     * Returns the atlaspage of the target when the code of the atlas is given.
     *
     * @param String $atlasname The code of the atlas
     *
     * @return String The page where the target can be found in the atlas
     */
    public function atlasPage($atlasname)
    {
        return $this->$atlasname;
    }

    /**
     * Returns the declination as a human readable string.
     *
     * @return String The declination
     */
    public function declination()
    {
        $decl = $this->decl;
        $sign = ' ';
        if ($decl < 0) {
            $sign = '-';
            $decl = -$decl;
        }
        $decl_degrees = floor($decl);
        $subminutes = 60 * ($decl - $decl_degrees);
        $decl_minutes = floor($subminutes);
        $subseconds = 60 * ($subminutes - $decl_minutes);
        $decl_seconds = round($subseconds);
        if ($decl_seconds == 60) {
            $decl_seconds = 0;
            $decl_minutes++;
        }
        if ($decl_minutes == 60) {
            $decl_minutes = 0;
            $decl_degrees++;
        }

        return $sign . sprintf('%02d', $decl_degrees) . '°'
            . sprintf('%02d', $decl_minutes) . "'"
            . sprintf('%02d', $decl_seconds) . '"';
    }

    /**
     * Returns the right ascension as a human readable string.
     *
     * @return String The right ascension
     */
    public function ra()
    {
        $ra = $this->ra;
        $ra_hours = floor($ra);
        $subminutes = 60 * ($ra - $ra_hours);
        $ra_minutes = floor($subminutes);
        $ra_seconds = round(60 * ($subminutes - $ra_minutes));
        if ($ra_seconds == 60) {
            $ra_seconds = 0;
            $ra_minutes++;
        }
        if ($ra_minutes == 60) {
            $ra_minutes = 0;
            $ra_hours++;
        }
        if ($ra_hours == 24) {
            $ra_hours = 0;
        }

        return sprintf('%02d', $ra_hours) . 'h'
            . sprintf('%02d', $ra_minutes) . 'm'
            . sprintf('%02d', $ra_seconds) . 's';
    }

    /**
     * Returns the size of the target as a human readable string.
     *
     * @return String The size
     */
    public function size()
    {
        $size = '-';
        if ($this->diam1 != 0.0) {
            if ($this->diam1 >= 40.0) {
                if (round($this->diam1 / 60.0) == ($this->diam1 / 60.0)) {
                    if (($this->diam1 / 60.0) > 30.0) {
                        $size = sprintf("%.0f'", $this->diam1 / 60.0);
                    } else {
                        $size = sprintf("%.1f'", $this->diam1 / 60.0);
                    }
                } else {
                    $size = sprintf("%.1f'", $this->diam1 / 60.0);
                }
                if ($this->diam2 != 0.0) {
                    if (round($this->diam2 / 60.0) == ($this->diam2 / 60.0)) {
                        if (($this->diam2 / 60.0) > 30.0) {
                            $size = $size . sprintf("x%.0f'", $this->diam2 / 60.0);
                        } else {
                            $size = $size . sprintf("x%.1f'", $this->diam2 / 60.0);
                        }
                    } else {
                        $size = $size . sprintf("x%.1f'", $this->diam2 / 60.0);
                    }
                }
            } else {
                $size = sprintf('%.1f"', $this->diam1);
                if ($this->diam2 != 0.0) {
                    $size = $size . sprintf('x%.1f"', $this->diam2);
                }
            }
        }

        return $size;
    }

    /**
     * Returns the Field Of View of the target to be used in the aladin script.
     *
     * @return String The Field Of View
     */
    public function getFOV()
    {
        if (preg_match('/(?i)^AA\d*STAR$/', $this->type)
            || preg_match('/(?i)^PLNNB$/', $this->type)
            || $this->diam1 == 0 && $this->diam2 == 0
        ) {
            $fov = 1;
        } else {
            $fov = 2 * max($this->diam1, $this->diam2) / 3600;
        }

        return $fov;
    }

    /**
     * Returns the ra and dec of the target to be used in the aladin script.
     *
     * @return String The coordinates
     */
    public function raDecToAladin()
    {
        $decl = $this->decl;
        $ra = $this->ra;

        $sign = '';
        if ($decl < 0) {
            $sign = '-';
            $decl = -$decl;
        } else {
            $sign = '+';
        }

        $ra_hours = floor($ra);
        $subminutes = 60 * ($ra - $ra_hours);
        $ra_minutes = floor($subminutes);
        $ra_seconds = round(60 * ($subminutes - $ra_minutes));
        if ($ra_seconds == 60) {
            $ra_seconds = 0;
            $ra_minutes++;
        }
        if ($ra_minutes == 60) {
            $ra_minutes = 0;
            $ra_hours++;
        }
        if ($ra_hours == 24) {
            $ra_hours = 0;
        }

        $decl_degrees = floor($decl);
        $subminutes = 60 * ($decl - $decl_degrees);
        $decl_minutes = floor($subminutes);
        $decl_seconds = round(60 * ($subminutes - $decl_minutes));
        if ($decl_seconds == 60) {
            $decl_seconds = 0;
            $decl_minutes++;
        }
        if ($decl_minutes == 60) {
            $decl_minutes = 0;
            $decl_degrees++;
        }

        return ($ra_hours . ' ' . $ra_minutes . ' ' . $ra_seconds . ' '
            . $sign . $decl_degrees . ' ' . $decl_minutes . ' ' . $decl_seconds);
    }
}
