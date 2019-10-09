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
use Illuminate\Support\Facades\Auth;

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

    /**
     * Returns the ephemerids for a whole year.
     * The ephemerids are calculated the first and the fifteenth of the month.
     *
     * @return Array the ephemerides for a whole year
     */
    public function getYearEphemerides()
    {
        $cnt = 0;
        for ($i = 1; $i < 13; $i++) {
            for ($j = 1; $j < 16; $j = $j + 14) {
                $datestr = sprintf('%02d', $j) . '/' . sprintf('%02d', $i) . '/'
                    . \Carbon\Carbon::now()->format('Y');

                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $datestr);
                $ephemerides[$cnt]['date'] = $date;

                $location = \App\Location::where(
                    'id', Auth::user()->stdlocation
                )->first();
                $astroCalc = new \App\Libraries\AstroCalc(
                    $date,
                    $location->latitude,
                    $location->longitude,
                    $location->timezone
                );

                $ris_tra_set = $astroCalc->calculateRiseTransitSettingTime(
                    $this->ra,
                    $this->decl,
                    $astroCalc->jd
                );
                $nightephemerides = date_sun_info(
                    $date->getTimestamp(),
                    $location->latitude,
                    $location->longitude
                );
                $ephemerides[$cnt]['max_alt'] = $ris_tra_set[3];
                $ephemerides[$cnt]['transit'] = $ris_tra_set[1];
                $ephemerides[$cnt]['rise'] = $ris_tra_set[0];
                $ephemerides[$cnt]['set'] = $ris_tra_set[2];

                $ephemerides[$cnt]['astronomical_twilight_end'] = is_bool(
                    $nightephemerides["astronomical_twilight_end"]
                ) ? null :
                    $date->copy()
                    ->setTimeFromTimeString(
                        date("H:i", $nightephemerides["astronomical_twilight_end"])
                    )->setTimezone($location->timezone);

                $ephemerides[$cnt]['astronomical_twilight_begin'] = is_bool(
                    $nightephemerides["astronomical_twilight_begin"]
                ) ? null :
                $date->copy()
                    ->setTimeFromTimeString(
                        date("H:i", $nightephemerides["astronomical_twilight_begin"])
                    )->setTimezone($location->timezone);

                $ephemerides[$cnt]['nautical_twilight_end'] = is_bool(
                    $nightephemerides["nautical_twilight_end"]
                ) ? null :$date->copy()
                    ->setTimeFromTimeString(
                        date("H:i", $nightephemerides["nautical_twilight_end"])
                    )->setTimezone($location->timezone);

                $ephemerides[$cnt]['nautical_twilight_begin'] = is_bool(
                    $nightephemerides["nautical_twilight_begin"]
                ) ? null :$date->copy()
                    ->setTimeFromTimeString(
                        date("H:i", $nightephemerides["nautical_twilight_begin"])
                    )->setTimezone($location->timezone);


                if ($ephemerides[$cnt]['astronomical_twilight_end'] > $ephemerides[$cnt]['astronomical_twilight_begin']) {
                    $ephemerides[$cnt]['astronomical_twilight_begin']->addDay();
                }
                if ($ephemerides[$cnt]['nautical_twilight_end'] > $ephemerides[$cnt]['nautical_twilight_begin']) {
                    $ephemerides[$cnt]['nautical_twilight_begin']->addDay();
                }
                $ephemerides[$cnt]['count'] = ($j == 1) ? '' : $i;

                $cnt++;
            }
        }

        // Setting the classes for the different colors
        $cnt = 0;
        foreach ($ephemerides as $ephem) {
            // Green if the max_alt does not change. This means that the
            // altitude is maximal
            if (($ephem['max_alt'] != '-'
                && $ephemerides[($cnt + 1) % 24]['max_alt'] != '-')
                && (($ephem['max_alt'] == $ephemerides[($cnt + 1) % 24]['max_alt'])
                || ($ephem['max_alt'] == $ephemerides[($cnt + 23) % 24]['max_alt']))
            ) {
                $ephemerides[$cnt]['max_alt_color'] = "ephemeridesgreen";
            } else {
                $ephemerides[$cnt]['max_alt_color'] = "";
            }

            // Green if the transit is during astronomical twilight
            // Yellow if the transit is during astronomical twilight
            $time = $ephem['date']->setTimeZone($location->timezone)->copy()
                ->setTimeFromTimeString($ephem['transit']);
            if ($time->format('H') < 12) {
                $time->addDay();
            }

            if ($ephem['max_alt'] != '-') {
                if ($ephem['astronomical_twilight_end'] != null
                    && $time->between(
                        $ephem['astronomical_twilight_begin'],
                        $ephem['astronomical_twilight_end']
                    )
                ) {
                    $ephemerides[$cnt]['transit_color'] = 'ephemeridesgreen';
                } elseif ($ephem['nautical_twilight_end'] != null
                    && $time->between(
                        $ephem['nautical_twilight_begin'],
                        $ephem['nautical_twilight_end']
                    )
                ) {
                    $ephemerides[$cnt]['transit_color'] = 'ephemeridesyellow';
                } else {
                    $ephemerides[$cnt]['transit_color'] = '';
                }
            } else {
                $ephemerides[$cnt]['transit_color'] = '';
            }



            $ephemerides[$cnt]['rise_color'] = "";

            if ($ephem['max_alt'] == '-') {
                $ephemerides[$cnt]['rise_color'] = '';
            } else {
                if ($ephem['rise'] == '-') {
                    if ($ephem['astronomical_twilight_end'] != null) {
                        $ephemerides[$cnt]['rise_color'] = 'ephemeridesgreen';
                    } elseif ($ephem['nautical_twilight_end'] != null) {
                        $ephemerides[$cnt]['rise_color'] = 'ephemeridesyellow';
                    }
                }
                if ($ephem['astronomical_twilight_end'] != null
                    && $this->_checkNightHourMinutePeriodOverlap(
                        $ephem['rise'],
                        $ephem['set'],
                        $ephem['astronomical_twilight_end'],
                        $ephem['astronomical_twilight_begin']
                    )
                ) {
                    $ephemerides[$cnt]['rise_color'] = 'ephemeridesgreen';
                } elseif ($ephem['nautical_twilight_end'] != null
                    && $this->_checkNightHourMinutePeriodOverlap(
                        $ephem['rise'],
                        $ephem['set'],
                        $ephem['nautical_twilight_end'],
                        $ephem['nautical_twilight_begin']
                    )
                ) {
                    $ephemerides[$cnt]['rise_color'] = 'ephemeridesyellow';
                }
            }

            $cnt++;
        }
        return $ephemerides;
    }

     /**
      * Checks if there is an overlap between the two given time periods
      *
      * @param string $firststart  The start of the first time interval.
      * @param string $firstend    The end of the first time interval.
      * @param Carbon $secondstart The start of the second time interval.
      * @param Carbon $secondend   The end of the second time interval.
      *
      * @return bool True if the two time intervals overlap.
      */
    private function _checkNightHourMinutePeriodOverlap(
        $firststart, $firstend, $secondstart, $secondend
    ) {
        $firststartvalue = str_replace(':', '', $firststart);
        $firstendvalue = str_replace(':', '', $firstend);
        $secondstartvalue = $secondstart->format("Hi");
        $secondendvalue = $secondend->format("Hi");
        if ($secondstartvalue < $secondendvalue) {
            return ((($firststartvalue > $secondstartvalue)
                && ($firststartvalue < $secondendvalue))
                || (($firstendvalue > $secondstartvalue)
                && ($firstendvalue < $secondendvalue))
                || (($firststartvalue < $secondend)
                && ($firstendvalue > $secondendvalue))
                || (($firststartvalue < $secondstartvalue)
                && ($firststartvalue > $firstendvalue))
                | (($firstendvalue > $secondendvalue)
                && ($firststartvalue > $firstendvalue)));
        } else {
            return ($firststartvalue > $secondstartvalue)
                || ($firststartvalue < $secondendvalue)
                || ($firstendvalue > $secondstartvalue)
                || ($firstendvalue < $secondendvalue)
                || (($firststartvalue < $secondstartvalue)
                && ($firstendvalue > $secondendvalue)
                && ($firststartvalue > $firstendvalue));
        }
    }
}
