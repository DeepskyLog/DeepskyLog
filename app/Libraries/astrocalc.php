<?php
/**
 * Procedures for calculating astronomical timing etc.
 *
 * PHP Version 7
 *
 * @category Utils/Astronomy
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace App\Libraries;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use deepskylog\AstronomyLibrary\Time;

/**
 * Procedures for calculating astronomical timing etc.
 *
 * @category Utils/Astronomy
 * @author   Deepsky Developers <developers@deepskylog.be>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */
class astrocalc
{
    public $jd;
    protected $timedifference;
    protected $longitude;
    protected $latitude;

    /**
     * Constructor initialises the public astroCalc property.
     *
     * @param DateTime $date      the date (as dd/mm/yyyy)
     * @param float    $latitude  the latitude of the location
     * @param float    $longitude The longitude of the location. East is positive,
     *                            west is negative.
     * @param string   $timezone  The timezone of the location, e.g.
     *                            "Europe/Brussels"
     */
    public function __construct(
        DateTime $date,
        $latitude,
        $longitude,
        $timezone
    ) {
        $day = $date->format('d');
        $month = $date->format('m');
        $year = $date->format('Y');
        $this->jd = Time::getJd(Carbon::instance($date));
        $dateTimeZone = new DateTimeZone($timezone);
        $datestr = sprintf('%02d/%02d/%d', $month, $day, $year);
        $dateTime = new DateTime($datestr, $dateTimeZone);

        // Returns timedifference in seconds
        $timedifference = $dateTimeZone->getOffset($dateTime);
        $timedifference /= 3600.0;

        if (strncmp($timezone, 'Etc/GMT', 7) === 0) {
            $timedifference = -$timedifference;
        }

        $this->timedifference = $timedifference;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    /**
     * This function calculates the rise, transit and setting time of an object (not
     * the sun of the moon). The accuracy is a few minutes... When the object does
     * not rise above the horizon, the setting time and the rising time will be NaN.
     * The transit time will be given (the time of the highest point of the object,
     * even when the object is under the horizon).
     * The rising, transit and setting time are given in UT. They should be corrected
     * with the offset in hours of the observing place.
     *
     * @param float $ra  The right ascension of the ephemerids
     * @param float $dec The declination of the object
     * @param float $jd  The julian date for the ephemerids
     *
     * @return array [0] is the rising time, [1] the transit time and [2] the setting
     *               time
     */
    public function calculateRiseTransitSettingTime($ra, $dec, $jd)
    {
        return $this->_calculateRiseTransitSettingTimeCommon(
            $jd,
            $ra,
            $ra,
            $ra,
            $dec,
            $dec,
            $dec,
            -99.99
        );
    }

    /**
     * This function does the calculations for the rise and setting of moon and stars.
     *
     * @param float $jd              The julian date
     * @param float $ra1             the right ascension of the object yesterday
     * @param float $ra2             the right ascension of the object today
     * @param float $ra3             the right ascension of the object tomorrow
     * @param float $dec1            the declination of the object yesterday
     * @param float $dec2            the declination of the object today
     * @param float $dec3            the declination of the object tomorrow
     * @param float $moonHorParallax the horizontal parallax of the moon
     *
     * @return array Rise time, transit time, setting time and transit altitude
     */
    private function _calculateRiseTransitSettingTimeCommon(
        $jd,
        $ra1,
        $ra2,
        $ra3,
        $dec1,
        $dec2,
        $dec3,
        $moonHorParallax
    ) {
        // Step 1 : Calculate the apparent siderial time at Greenwich at 0h UT.
        $jd = floor($jd) - 0.5;

        $T = ($jd - 2451545.0) / 36525.0;

        $theta0 = 100.46061837 + 36000.770053608 * $T + 0.000387933 * $T * $T
            - $T * $T * $T / 38710000.0;

        if ($theta0 < 0.0) {
            $a = -floor($theta0 / 360.0) + 1;
            $theta0 += $a * 360.0;
        }
        if ($theta0 > 360.0) {
            $a = floor($theta0 / 360.0);
            $theta0 -= $a * 360.0;
        }
        $theta0 /= 15.0;

        $nutat = Time::nutation($jd);

        $theta0 += (($nutat[0] *
            cos(deg2rad($nutat[3])) / 15.0) / 3600.0);

        // STEP 2 : Calculating the rise, transit and set time of the object
        $ra1 *= 15.0;
        $ra2 *= 15.0;
        $ra3 *= 15.0;

        // Tests when the object passes ra 24 and goes back to 0
        if ($ra3 - $ra2 < -50.0) {
            $ra3 += 360.0;
        } elseif ($ra2 - $ra1 < -50.0) {
            $ra3 += 360.0;
            $ra2 += 360.0;
        } elseif ($ra2 - $ra3 < -50.0) {
            $ra1 += 360.0;
            $ra2 += 360.0;
        } elseif ($ra1 - $ra2 < -50.0) {
            $ra1 += 360.0;
        }
        $longitude = -$this->longitude;

        if ($moonHorParallax === -99.99) {
            $h0 = -0.5667;
        } else {
            $h0 = 0.7275 * $moonHorParallax - 0.566667;
        }

        $Hcap0 = rad2deg(
            acos(
                (sin(deg2rad($h0)) - sin(deg2rad($this->latitude))
                    * sin(deg2rad($dec2)))
                / (cos(deg2rad($this->latitude))
                * cos(deg2rad($dec2)))
            )
        );

        $m0 = ($ra2 + $longitude - $theta0 * 15.0) / 360.0;
        $m0 -= floor($m0);

        if (is_nan($Hcap0)) {
            $m1 = 99;
            $m2 = 99;
        } else {
            $m1 = $m0 - $Hcap0 / 360.0;
            $m1 -= floor($m1);
            $m2 = $m0 + $Hcap0 / 360.0;
            $m2 -= floor($m2);
        }

        // STEP 3 : Extra calculation to work for moving bodies...
        // 3.1 : transit time
        $theta = $theta0 * 15.0 + 360.985647 * $m0;
        $theta /= 360.0;
        $theta -= floor($theta);
        $theta *= 360.0;

        // Use delta t from laravel-astronomy-library.
        // We use delta t for the given date.
        $n = $m0 + Time::deltaT(Time::fromJd($jd)) / 86400;

        $a = $ra2 - $ra1;
        $b = $ra3 - $ra2;
        $c = $b - $a;
        $alphaInterpol = $ra2 + $n / 2.0 * ($a + $b + $n * $c);
        $H = $theta - $longitude - $alphaInterpol;
        $deltaM = -$H / 360.0;

        $m0 = ($deltaM + $m0) * 24.0;

        if (! is_nan($Hcap0)) {
            // 3.2 : rise time
            $theta = $theta0 * 15.0 + 360.985647 * $m1;
            $theta /= 360.0;
            $theta -= floor($theta);
            $theta *= 360.0;

            $n = $m1 + 56 / 86400;

            $a = $ra2 - $ra1;
            $b = $ra3 - $ra2;
            $c = $b - $a;
            $alphaInterpol = $ra2 + $n / 2.0 * ($a + $b + $n * $c);

            $a = $dec2 - $dec1;
            $b = $dec3 - $dec2;
            $c = $b - $a;
            $deltaInterpol = $dec2 + $n / 2.0 * ($a + $b + $n * $c);

            $H = $theta - $longitude - $alphaInterpol;
            $h = rad2deg(
                asin(
                    sin(deg2rad($this->latitude)) * sin(deg2rad($deltaInterpol))
                    + cos(deg2rad($this->latitude)) * cos(deg2rad($deltaInterpol))
                    * cos(deg2rad($H))
                )
            );
            $deltaM = ($h - $h0)
                / (360.0 * cos(deg2rad($deltaInterpol))
                * cos(deg2rad($this->latitude))
                * sin(deg2rad($H)));

            $m1 = ($deltaM + $m1) * 24.0;

            // 3.3 : set time
            $theta = $theta0 * 15.0 + 360.985647 * $m2;
            $theta /= 360.0;
            $theta -= floor($theta);
            $theta *= 360.0;

            $n = $m2 + 56 / 86400;

            $a = $ra2 - $ra1;
            $b = $ra3 - $ra2;
            $c = $b - $a;
            $alphaInterpol = $ra2 + $n / 2.0 * ($a + $b + $n * $c);

            $a = $dec2 - $dec1;
            $b = $dec3 - $dec2;
            $c = $b - $a;
            $deltaInterpol = $dec2 + $n / 2.0 * ($a + $b + $n * $c);

            $H = $theta - $longitude - $alphaInterpol;
            $h = rad2deg(
                asin(
                    sin(deg2rad($this->latitude)) * sin(deg2rad($deltaInterpol))
                    + cos(deg2rad($this->latitude)) * cos(deg2rad($deltaInterpol))
                    * cos(deg2rad($H))
                )
            );
            $deltaM = ($h - $h0)
                / (360.0 * cos(deg2rad($deltaInterpol))
                * cos(deg2rad($this->latitude)) * sin(deg2rad($H)));

            $m2 = ($deltaM + $m2) * 24.0;
        }
        $ris_tra_set = [$m1, $m0, $m2];

        if ($ris_tra_set[0] > 48 || $ris_tra_set[0] < -24) {
            $ris_tra_set[0] = '-';
        } else {
            $ris_tra_set[0] += $this->timedifference;
            if ($ris_tra_set[0] < 0) {
                $ris_tra_set[0] += 24;
            }
            if ($ris_tra_set[0] > 24) {
                $ris_tra_set[0] -= 24;
            }
            $minutes = round(($ris_tra_set[0] - floor($ris_tra_set[0])) * 60);
            if ($minutes >= 60) {
                $minutes = 0;
                $toAdd = 1;
            } else {
                $toAdd = 0;
            }
            if ($minutes < 10) {
                $minutes = '0'.$minutes;
            }
            if ((floor($ris_tra_set[0]) + $toAdd) % 24 < 10) {
                $ris_tra_set[0] = '0'.(floor($ris_tra_set[0]) + $toAdd) % 24
                    .':'.$minutes;
            } else {
                $ris_tra_set[0] = floor($ris_tra_set[0]) + $toAdd.':'.$minutes;
            }
        }

        $transit = $ris_tra_set[1];
        if ($ris_tra_set[1] > 48 || $ris_tra_set[1] < -24) {
            $ris_tra_set[1] = '-';
        } else {
            $ris_tra_set[1] += $this->timedifference;
            if ($ris_tra_set[1] < 0) {
                $ris_tra_set[1] += 24;
            }
            if ($ris_tra_set[1] > 24) {
                $ris_tra_set[1] -= 24;
            }
            $minutes = round(($ris_tra_set[1] - floor($ris_tra_set[1])) * 60);
            if ($minutes >= 60) {
                $minutes = 0;
                $toAdd = 1;
            } else {
                $toAdd = 0;
            }
            if ($minutes < 10) {
                $minutes = '0'.$minutes;
            }
            if ((floor($ris_tra_set[1]) + $toAdd) % 24 < 10) {
                $ris_tra_set[1] = '0'.(floor($ris_tra_set[1]) + $toAdd) % 24
                    .':'.$minutes;
            } else {
                $ris_tra_set[1] = floor($ris_tra_set[1]) + $toAdd.':'.$minutes;
            }
        }

        if ($ris_tra_set[2] > 48 || $ris_tra_set[2] < -24) {
            $ris_tra_set[2] = '-';
        } else {
            $ris_tra_set[2] += $this->timedifference;
            if ($ris_tra_set[2] < 0) {
                $ris_tra_set[2] += 24;
            }
            if ($ris_tra_set[2] > 24) {
                $ris_tra_set[2] -= 24;
            }
            $minutes = round(($ris_tra_set[2] - floor($ris_tra_set[2])) * 60);
            if ($minutes >= 60) {
                $minutes = 0;
                $toAdd = 1;
            } else {
                $toAdd = 0;
            }
            if ($minutes < 10) {
                $minutes = '0'.$minutes;
            }
            if ((floor($ris_tra_set[2]) + $toAdd) % 24 < 10) {
                $ris_tra_set[2] = '0'.(floor($ris_tra_set[2]) + $toAdd) % 24
                    .':'.$minutes;
            } else {
                $ris_tra_set[2] = floor($ris_tra_set[2]) + $toAdd.':'.$minutes;
            }
        }
        $ris_tra_set[4] = 0;
        $ra2 /= 15;

        date_default_timezone_set('UTC');
        $temptime = jdtogregorian($jd + 1);
        $temppos = strpos($temptime, '/');
        $tempmonth = substr($temptime, 0, $temppos);
        $temptime = substr($temptime, $temppos + 1);
        $temppos = strpos($temptime, '/');
        $tempday = substr($temptime, 0, $temppos);
        $tempyear = substr($temptime, $temppos + 1);

        $timestr = $tempyear.'-'.$tempmonth.'-'.$tempday;

        $sun_info = date_sun_info(
            strtotime($timestr),
            $this->latitude,
            -$longitude
        );
        $astrobegin = date('H:i', $sun_info['astronomical_twilight_begin']);
        sscanf($astrobegin, '%d:%d', $hour, $minute);
        $astrobegin = ($hour + $minute / 60.0);

        $astroend = date('H:i', $sun_info['astronomical_twilight_end']);
        sscanf($astroend, '%d:%d', $hour, $minute);
        $astroend = ($hour + $minute / 60.0);

        $nautbegin = date('H:i', $sun_info['nautical_twilight_begin']);
        sscanf($nautbegin, '%d:%d', $hour, $minute);
        $nautbegin = ($hour + $minute / 60.0);

        $nautend = date('H:i', $sun_info['nautical_twilight_end']);
        sscanf($nautend, '%d:%d', $hour, $minute);
        $nautend = ($hour + $minute / 60.0);

        if ($transit > 0) {
            $transit = $transit % 24.0 + ($transit - floor($transit));
        } else {
            $toAdd = floor(-$transit / 24.0) + 1.0;
            $transit += 24.0 * $toAdd;
        }
        if ($astroend > 0 && $astrobegin > 0) {
            $tocompare = -999;
            if ($astrobegin > 12) {
                $toCheck = $astrobegin;
            } else {
                $toCheck = $astrobegin + 24;
            }
            if (($transit + 24 < $astroend + 24) && ($transit + 24 > $toCheck)) {
                // The transit is during the day
                // Check the rise time for $astroend and for $astrobegin
                $theta0w = $theta0 + ($astrobegin * 1.00273790935);
                if ($theta0w > 0) {
                    $theta0w = $theta0w % 24.0 + ($theta0w - floor($theta0w));
                } else {
                    $toAdd = floor(-$theta0w / 24.0) + 1;
                    $theta0w += 24.0 * $toAdd;
                }
                $H = ($theta0w - $longitude / 15 - $ra2) * 15.0;
                if ($H > 0) {
                    $H = $H % 360.0 + ($H - floor($H));
                } else {
                    $toAdd = floor(-$H / 360.0) + 1;
                    $H += 360.0 * $toAdd;
                }

                $tocompare = rad2deg(
                    asin(
                        sin(deg2rad($this->latitude)) * sin(deg2rad($dec2))
                            + cos(deg2rad($this->latitude)) * cos(deg2rad($dec2))
                            * cos(deg2rad($H))
                    )
                );

                $transit = $astroend;
            }

            $theta0 += ($transit * 1.00273790935);
            if ($theta0 > 0) {
                $theta0 = $theta0 % 24.0 + ($theta0 - floor($theta0));
            } else {
                $toAdd = floor(-$theta0 / 24.0) + 1;
                $theta0 += 24.0 * $toAdd;
            }
            $H = ($theta0 - $longitude / 15 - $ra2) * 15.0;
            if ($H > 0) {
                $H = $H % 360.0 + ($H - floor($H));
            } else {
                $toAdd = floor(-$H / 360.0) + 1;
                $H += 360.0 * $toAdd;
            }

            $ris_tra_set[3] = rad2deg(
                asin(
                    sin(deg2rad($this->latitude)) * sin(deg2rad($dec2))
                    + cos(deg2rad($this->latitude)) * cos(deg2rad($dec2))
                    * cos(deg2rad($H))
                )
            );
            if ($tocompare !== -999) {
                if ($tocompare > $ris_tra_set[3]) {
                    $ris_tra_set[3] = $tocompare;
                    $ris_tra_set[4] = $astrobegin;
                } else {
                    $ris_tra_set[4] = $astroend;
                }
            } else {
                $ris_tra_set[4] = $transit;
            }

            $minutes = round(($ris_tra_set[3] - floor($ris_tra_set[3])) * 60);
            if ($minutes >= 60) {
                $minutes = 0;
                $toAdd = 1;
            } else {
                $toAdd = 0;
            }
            if ($minutes < 10) {
                $minutes = '0'.$minutes;
            }
            if ($ris_tra_set[3] < 0) {
                $ris_tra_set[3] = '-';
            } else {
                if ((floor($ris_tra_set[3]) + $toAdd) % 24 < 10) {
                    $ris_tra_set[3] = '0'.(floor($ris_tra_set[3]) + $toAdd) % 24
                        .'&deg;'.$minutes."'";
                } else {
                    $ris_tra_set[3] = floor($ris_tra_set[3]) + $toAdd.'&deg;'
                        .$minutes."'";
                }
            }

            if ($ris_tra_set[4] > 24
                || $ris_tra_set[4] < 0 || $ris_tra_set[3] === '-'
            ) {
                $ris_tra_set[4] = '-';
            } else {
                $ris_tra_set[4] += $this->timedifference;
                if ($ris_tra_set[4] < 0) {
                    $ris_tra_set[4] += 24;
                }
                if ($ris_tra_set[4] > 24) {
                    $ris_tra_set[4] -= 24;
                }
                $minutes = round(($ris_tra_set[4] - floor($ris_tra_set[4])) * 60);
                if ($minutes >= 60) {
                    $minutes = 0;
                    $toAdd = 1;
                } else {
                    $toAdd = 0;
                }
                if ($minutes < 10) {
                    $minutes = '0'.$minutes;
                }
                if ((floor($ris_tra_set[4]) + $toAdd) % 24 < 10) {
                    $ris_tra_set[4] = '0'.(floor($ris_tra_set[4]) + $toAdd) % 24
                        .':'.$minutes;
                } else {
                    $ris_tra_set[4] = floor($ris_tra_set[4]) + $toAdd
                        .':'.$minutes;
                }
            }
        } else {
            $ris_tra_set[3] = '-';
            $ris_tra_set[4] = '-';
        }

        // if no astro twilight, or no best astro time for object
        if (! (($astroend > 0 && $astrobegin > 0))) {
            if ($nautend > 0 && $nautbegin > 0) {
                $tocompare = -999;
                if ($nautbegin > 12) {
                    $toCheck = $nautbegin;
                } else {
                    $toCheck = $nautbegin + 24;
                }
                if (($transit + 24 < $nautend + 24) && ($transit + 24 > $toCheck)) {
                    // The transit is during the day
                    // Check the rise time for $nautend and for $nautbegin
                    $theta0w = $theta0 + ($nautbegin * 1.00273790935);
                    if ($theta0w > 0) {
                        $theta0w = $theta0w % 24.0 + ($theta0w - floor($theta0w));
                    } else {
                        $toAdd = floor(-$theta0w / 24.0) + 1;
                        $theta0w = $theta0w + 24.0 * $toAdd;
                    }
                    $H = ($theta0w - $longitude / 15 - $ra2) * 15.0;
                    if ($H > 0) {
                        $H = $H % 360.0 + ($H - floor($H));
                    } else {
                        $toAdd = floor(-$H / 360.0) + 1;
                        $H += 360.0 * $toAdd;
                    }

                    $tocompare = rad2deg(
                        asin(
                            sin(deg2rad($this->latitude)) * sin(deg2rad($dec2))
                            + cos(deg2rad($this->latitude)) * cos(deg2rad($dec2))
                            * cos(deg2rad($H))
                        )
                    );

                    $transit = $nautend;
                }

                $theta0 += ($transit * 1.00273790935);
                if ($theta0 > 0) {
                    $theta0 = $theta0 % 24.0 + ($theta0 - floor($theta0));
                } else {
                    $toAdd = floor(-$theta0 / 24.0) + 1;
                    $theta0 += 24.0 * $toAdd;
                }
                $H = ($theta0 - $longitude / 15 - $ra2) * 15.0;
                if ($H > 0) {
                    $H = $H % 360.0 + ($H - floor($H));
                } else {
                    $toAdd = floor(-$H / 360.0) + 1;
                    $H += 360.0 * $toAdd;
                }

                $ris_tra_set[3] = rad2deg(
                    asin(
                        sin(deg2rad($this->latitude)) * sin(deg2rad($dec2))
                        + cos(deg2rad($this->latitude)) * cos(deg2rad($dec2))
                        * cos(deg2rad($H))
                    )
                );

                if ($tocompare !== -999) {
                    if ($tocompare > $ris_tra_set[3]) {
                        $ris_tra_set[3] = $tocompare;
                        $ris_tra_set[4] = $nautbegin;
                    } else {
                        $ris_tra_set[4] = $nautend;
                    }
                } else {
                    $ris_tra_set[4] = $transit;
                }

                $minutes = round(($ris_tra_set[3] - floor($ris_tra_set[3])) * 60);
                if ($minutes >= 60) {
                    $minutes = 0;
                    $toAdd = 1;
                } else {
                    $toAdd = 0;
                }
                if ($minutes < 10) {
                    $minutes = '0'.$minutes;
                }
                if ($ris_tra_set[3] < 0) {
                    $ris_tra_set[3] = '-';
                } else {
                    if ((floor($ris_tra_set[3]) + $toAdd) % 24 < 10) {
                        $ris_tra_set[3] = '0'.(floor($ris_tra_set[3]) + $toAdd) % 24
                            .'&deg;'.$minutes."'";
                    } else {
                        $ris_tra_set[3] = floor($ris_tra_set[3]) + $toAdd.'&deg;'
                            .$minutes."'";
                    }
                }

                if ($ris_tra_set[4] > 24
                    || $ris_tra_set[4] < 0 || $ris_tra_set[3] === '-'
                ) {
                    $ris_tra_set[4] = '-';
                } else {
                    $ris_tra_set[4] += $this->timedifference;
                    if ($ris_tra_set[4] < 0) {
                        $ris_tra_set[4] += 24;
                    }
                    if ($ris_tra_set[4] > 24) {
                        $ris_tra_set[4] -= 24;
                    }
                    $minutes = round(
                        ($ris_tra_set[4] - floor($ris_tra_set[4])) * 60
                    );
                    if ($minutes >= 60) {
                        $minutes = 0;
                        $toAdd = 1;
                    } else {
                        $toAdd = 0;
                    }
                    if ($minutes < 10) {
                        $minutes = '0'.$minutes;
                    }
                    if ((floor($ris_tra_set[4]) + $toAdd) % 24 < 10.0) {
                        $ris_tra_set[4] = '0'.(floor($ris_tra_set[4]) + $toAdd) % 24
                            .':'.$minutes;
                    } else {
                        $ris_tra_set[4] = floor($ris_tra_set[4]) + $toAdd
                            .':'.$minutes;
                    }
                }
            } else {
                $ris_tra_set[3] = '-';
                $ris_tra_set[4] = '-';
            }
            if ($ris_tra_set[3] !== '-') {
                $ris_tra_set[3] = '('.$ris_tra_set[3].')';
            }
        }

        return $ris_tra_set;
    }

    /**
     * Calculates the Rise, transit and setting time of the moon for a
     * given location.
     *
     * @return array The rise, transit and setting of the moon
     */
    public function calculateMoonRiseTransitSettingTime()
    {
        // Step one : calculate the ra and dec for the moon for today, yesterday
        //            and tomorrow
        $jd = floor($this->jd) - 0.5;

        $radec1 = $this->_calculateMoonCoordinates(
            $jd - 1,
            $this->longitude,
            $this->latitude
        );
        $radec2 = $this->_calculateMoonCoordinates(
            $jd,
            $this->longitude,
            $this->latitude
        );
        $radec3 = $this->_calculateMoonCoordinates(
            $jd + 1,
            $this->longitude,
            $this->latitude
        );

        return $this->_calculateRiseTransitSettingTimeCommon(
            $jd,
            $radec1[0],
            $radec2[0],
            $radec3[0],
            $radec1[1],
            $radec2[1],
            $radec3[1],
            $this->timedifference
        );
    }

    /**
     * Calculates the moon coordinates for a given date.
     *
     * @param float $jd The julian day
     *
     * @return array the ra and dec of the moon
     */
    private function _calculateMoonCoordinates($jd)
    {
        $T = ($jd - 2451545.0) / 36525.0;

        /* Moon's mean longitude */
        $L_accent = 218.3164591 + 481267.88134236 * $T - 0.0013268 * pow($T, 2) +
              pow($T, 3) / 538841.0 - pow($T, 4) / 65194000.0;

        $L_accent -= floor($L_accent / 360.0) * 360.0;

        /* Mean elongation of the moon */
        $D = 297.8502042 + 445267.1115168 * $T - 0.0016300 * pow($T, 2) +
              pow($T, 3) / 545868.0 - pow($T, 4) / 113065000.0;

        $D -= floor($D / 360.0) * 360.0;

        /* Sun's mean anomaly */
        $M = 357.5291092 + 35999.0502909 * $T - 0.0001536 * pow($T, 2)
            + pow($T, 3) / 24490000.0;

        $M -= floor($M / 360.0) * 360.0;

        /* Moon's mean anomaly */
        $M_accent = 134.9634114 + 477198.8676313 * $T + 0.0089970 * pow($T, 2) +
              pow($T, 3) / 69699.0 - pow($T, 4) / 14712000.0;

        $M_accent -= floor($M_accent / 360.0) * 360.0;

        /*Moon's argument of latitude */
        $F = 93.2720993 + 483202.0175273 * $T - 0.0034029 * pow($T, 2) -
              pow($T, 3) / 3526000.0 + pow($T, 4) / 863310000.0;

        $F -= floor($F / 360.0) * 360.0;

        $A1 = 119.75 + 131.849 * $T;
        $A1 -= floor($A1 / 360.0) * 360.0;

        $A2 = 53.09 + 479264.290 * $T;
        $A2 -= floor($A2 / 360.0) * 360.0;

        $A3 = 313.45 + 481266.484 * $T;
        $A3 -= floor($A3 / 360.0) * 360.0;

        $E = 1 - 0.002516 * $T - 0.0000074 * pow($T, 2);

        $L = 6288774.0 * sin(deg2rad($M_accent))
            + 1274027.0 * sin(deg2rad(2 * $D - $M_accent))
            + 658314.0 * sin(deg2rad(2 * $D))
            + 213618.0 * sin(deg2rad(2 * $M_accent))
            - 185116.0 * sin(deg2rad($M)) * $E
            - 114332.0 * sin(deg2rad(2 * $F))
            + 58793.0 * sin(deg2rad(2 * $D - 2 * $M_accent))
            + 57066.0 * sin(deg2rad(2 * $D - $M - $M_accent)) * $E
            + 53322.0 * sin(deg2rad(2 * $D + $M_accent))
            + 45758.0 * sin(deg2rad(2 * $D - $M)) * $E
            - 40923.0 * sin(deg2rad($M - $M_accent)) * $E
            - 34720.0 * sin(deg2rad($D))
            - 30383 * sin(deg2rad($M + $M_accent)) * $E
            + 15327 * sin(deg2rad(2 * $D - 2 * $F))
            - 12528 * sin(deg2rad($M_accent + 2 * $F))
            + 10980 * sin(deg2rad($M_accent - 2 * $F))
            + 10675 * sin(deg2rad(4 * $D - $M_accent))
            + 10034 * sin(deg2rad(3 * $M_accent))
            + 8548 * sin(deg2rad(4 * $D - 2 * $M_accent))
            - 7888 * sin(deg2rad(2 * $D + $M - $M_accent)) * $E
            - 6766 * sin(deg2rad(2 * $D + $M)) * $E
            - 5163 * sin(deg2rad($D - $M_accent))
            + 4987 * sin(deg2rad($D + $M)) * $E
            + 4036 * sin(deg2rad(2 * $D - $M + $M_accent)) * $E
            + 3994 * sin(deg2rad(2 * $D + 2 * $M_accent))
            + 3861 * sin(deg2rad(4 * $D))
            + 3665 * sin(deg2rad(2 * $D - 3 * $M_accent))
            - 2689 * sin(deg2rad($M - 2 * $M_accent)) * $E
            - 2602 * sin(deg2rad(2 * $D - $M_accent + 2 * $F))
            + 2390 * sin(deg2rad(2 * $D - $M - 2 * $M_accent)) * $E
            - 2348 * sin(deg2rad($D + $M_accent))
            + 2236 * sin(deg2rad(2 * $D - 2 * $M)) * pow($E, 2)
            - 2120 * sin(deg2rad($M + 2 * $M_accent)) * $E
            - 2069 * sin(deg2rad(2 * $M)) * pow($E, 2)
            + 2048 * sin(deg2rad(2 * $D - 2 * $M - $M_accent)) * pow($E, 2)
            - 1773 * sin(deg2rad(2 * $D + $M_accent - 2 * $F))
            - 1595 * sin(deg2rad(2 * $D + 2 * $F))
            + 1215 * sin(deg2rad(4 * $D - $M - $M_accent)) * $E
            - 1110 * sin(deg2rad(2 * $M_accent + 2 * $F))
            - 892 * sin(deg2rad(3 * $D - $M_accent))
            - 810 * sin(deg2rad(2 * $D + $M + $M_accent)) * $E
            + 759 * sin(deg2rad(4 * $D - $M - 2 * $M_accent)) * $E
            - 713 * sin(deg2rad(2 * $M - $M_accent)) * pow($E, 2)
            - 700 * sin(deg2rad(2 * $D + 2 * $M - $M_accent)) * pow($E, 2)
            + 691 * sin(deg2rad(2 * $D + $M - 2 * $M_accent)) * $E
            + 596 * sin(deg2rad(2 * $D - $M - 2 * $F)) * $E
            + 549 * sin(deg2rad(4 * $D + $M_accent))
            + 537 * sin(deg2rad(4 * $M_accent))
            + 520 * sin(deg2rad(4 * $D - $M)) * $E
            - 487 * sin(deg2rad($D - 2 * $M_accent))
            - 399 * sin(deg2rad(2 * $D + $M - 2 * $F)) * $E
            - 381 * sin(deg2rad(2 * $M_accent - 2 * $F))
            + 351 * sin(deg2rad($D + $M + $M_accent)) * $E
            - 340 * sin(deg2rad(3 * $D - 2 * $M_accent))
            + 330 * sin(deg2rad(4 * $D - 3 * $M_accent))
            + 327 * sin(deg2rad(2 * $D - $M + 2 * $M_accent)) * $E
            - 323 * sin(deg2rad(2 * $M + $M_accent)) * pow($E, 2)
            + 299 * sin(deg2rad($D + $M - $M_accent)) * $E
            + 294 * sin(deg2rad(2 * $D + 3 * $M_accent));

        $L += 3958 * sin(deg2rad($A1))
                + 1962 * sin(deg2rad($L_accent - $F))
                + 318 * sin(deg2rad($A2));

        $eclLongitude = $L_accent + $L / 1000000.0;

        $B = 5128122.0 * sin(deg2rad($F))
             + 280602.0 * sin(deg2rad($M_accent + $F))
             + 277693.0 * sin(deg2rad($M_accent - $F))
             + 173237.0 * sin(deg2rad(2 * $D - $F))
             + 55413.0 * sin(deg2rad(2 * $D - $M_accent + $F))
             + 46271.0 * sin(deg2rad(2 * $D - $M_accent - $F))
             + 32573 * sin(deg2rad(2 * $D + $F))
             + 17198 * sin(deg2rad(2 * $M_accent + $F))
             + 9266 * sin(deg2rad(2 * $D + $M_accent - $F))
             + 8822 * sin(deg2rad(2 * $M_accent - $F))
             + 8216 * sin(deg2rad(2 * $D - $M - $F)) * $E
             + 4324 * sin(deg2rad(2 * $D - 2 * $M_accent - $F))
             + 4200 * sin(deg2rad(2 * $D + $M_accent + $F))
             - 3359 * sin(deg2rad(2 * $D + $M - $F)) * $E
             + 2463 * sin(deg2rad(2 * $D - $M - $M_accent + $F)) * $E
             + 2211 * sin(deg2rad(2 * $D - $M + $F)) * $E
             + 2065 * sin(deg2rad(2 * $D - $M - $M_accent - $F)) * $E
             - 1870 * sin(deg2rad($M - $M_accent - $F)) * $E
             + 1828 * sin(deg2rad(4 * $D - $M_accent - $F))
             - 1794 * sin(deg2rad($M + $F)) * $E
             - 1749 * sin(deg2rad(3 * $F))
             - 1565 * sin(deg2rad($M - $M_accent + $F)) * $E
             - 1491 * sin(deg2rad($D + $F))
             - 1475 * sin(deg2rad($M + $M_accent + $F)) * $E
             - 1410 * sin(deg2rad($M + $M_accent - $F)) * $E
             - 1344 * sin(deg2rad($M - $F)) * $E
             - 1335 * sin(deg2rad($D - $F))
             + 1107 * sin(deg2rad(3 * $M_accent + $F))
             + 1021 * sin(deg2rad(4 * $D - $F))
             + 833 * sin(deg2rad(4 * $D - $M_accent + $F))
             + 777 * sin(deg2rad($M_accent - 3 * $F))
             + 671 * sin(deg2rad(4 * $D - 2 * $M_accent + $F))
             + 607 * sin(deg2rad(2 * $D - 3 * $F))
             + 596 * sin(deg2rad(2 * $D + 2 * $M_accent - $F))
             + 491 * sin(deg2rad(2 * $D - $M + $M_accent - $F)) * $E
             - 451 * sin(deg2rad(2 * $D - 2 * $M_accent + $F))
             + 439 * sin(deg2rad(3 * $M_accent - $F))
             + 422 * sin(deg2rad(2 * $D + 2 * $M_accent + $F))
             + 421 * sin(deg2rad(2 * $D - 3 * $M_accent - $F))
             - 366 * sin(deg2rad(2 * $D + $M - $M_accent + $F)) * $E
             - 351 * sin(deg2rad(2 * $D + $M + $F)) * $E
             + 331 * sin(deg2rad(4 * $D + $F))
             + 315 * sin(deg2rad(2 * $D - $M + $M_accent + $F)) * $E
             + 302 * sin(deg2rad(2 * $D - 2 * $M - $F)) * pow($E, 2)
             - 283 * sin(deg2rad($M_accent + 3 * $F))
             - 229 * sin(deg2rad(2 * $D + $M + $M_accent - $F)) * $E
             + 223 * sin(deg2rad($D + $M - $F)) * $E
             + 223 * sin(deg2rad($D + $M + $F)) * $E
             - 220 * sin(deg2rad($M - 2 * $M_accent - $F)) * $E
             - 220 * sin(deg2rad(2 * $D + $M - $M_accent - $F)) * $E
             - 185 * sin(deg2rad($D + $M_accent + $F))
             + 181 * sin(deg2rad(2 * $D - $M - 2 * $M_accent - $F)) * $E
             - 177 * sin(deg2rad($M + 2 * $M_accent + $F)) * $E
             + 176 * sin(deg2rad(4 * $D - 2 * $M_accent - $F))
             + 166 * sin(deg2rad(4 * $D - $M - $M_accent - $F)) * $E
             - 164 * sin(deg2rad($D + $M_accent - $F))
             + 132 * sin(deg2rad(4 * $D + $M_accent - $F))
             - 119 * sin(deg2rad($D - $M_accent - $F))
             + 115 * sin(deg2rad(4 * $D - $M - $F)) * $E
             + 107 * sin(deg2rad(2 * $D - 2 * $M + $F)) * pow($E, 2);

        $B -= 2235 * sin(deg2rad($L_accent))
                + 382 * sin(deg2rad($A3))
                + 175 * sin(deg2rad($A1 - $F))
                + 175 * sin(deg2rad($A1 + $F))
                + 127 * sin(deg2rad($L_accent - $M_accent))
                - 115 * sin(deg2rad($L_accent + $M_accent));

        $eclLatitude = $B / 1000000.0;

        $R = -20905355.0 * cos(deg2rad($M_accent))
             - 3699111.0 * cos(deg2rad(2 * $D - $M_accent))
             - 2955968.0 * cos(deg2rad(2 * $D))
             - 569925.0 * cos(deg2rad(2 * $M_accent))
             + 48888.0 * cos(deg2rad($M)) * $E
             - 3149.0 * cos(deg2rad(2 * $F))
             + 246158.0 * cos(deg2rad(2 * $D - 2 * $M_accent))
             - 152138.0 * cos(deg2rad(2 * $D - $M - $M_accent)) * $E
             - 170733.0 * cos(deg2rad(2 * $D + $M_accent))
             - 204586.0 * cos(deg2rad(2 * $D - $M)) * $E
             - 129620.0 * cos(deg2rad($M - $M_accent)) * $E
             + 108743.0 * cos(deg2rad($D))
             + 104755.0 * cos(deg2rad($M + $M_accent)) * $E
             + 10321.0 * cos(deg2rad(2 * $D - 2 * $F))
             + 79661.0 * cos(deg2rad($M_accent - 2 * $F))
             - 34782.0 * cos(deg2rad(4 * $D - $M_accent))
             - 23210.0 * cos(deg2rad(3 * $M_accent))
             - 21636.0 * cos(deg2rad(4 * $D - 2 * $M_accent))
             + 24208.0 * cos(deg2rad(2 * $D + $M - $M_accent)) * $E
             + 30824.0 * cos(deg2rad(2 * $D + $M)) * $E
             - 8379.0 * cos(deg2rad($D - $M_accent))
             - 16675.0 * cos(deg2rad($D + $M)) * $E
             - 12831.0 * cos(deg2rad(2 * $D - $M + $M_accent)) * $E
             - 10445.0 * cos(deg2rad(2 * $D + 2 * $M_accent))
             - 11650.0 * cos(deg2rad(4 * $D))
             + 14403.0 * cos(deg2rad(2 * $D - 3 * $M_accent))
             - 7003.0 * cos(deg2rad($M - 2 * $M_accent)) * $E
             + 10056.0 * cos(deg2rad(2 * $D - $M - 2 * $M_accent)) * $E
             + 6322.0 * cos(deg2rad($D + $M_accent))
             - 9884.0 * cos(deg2rad(2 * $D - 2 * $M)) * pow($E, 2)
             + 5751.0 * cos(deg2rad($M + 2 * $M_accent)) * $E
             - 4950.0 * cos(deg2rad(2 * $D - 2 * $M - $M_accent)) * pow($E, 2)
             + 4130.0 * cos(deg2rad(2 * $D + $M_accent - 2 * $F))
             - 3958.0 * cos(deg2rad(4 * $D - $M - $M_accent)) * $E
             + 3258.0 * cos(deg2rad(3 * $D - $M_accent))
             + 2616.0 * cos(deg2rad(2 * $D + $M + $M_accent)) * $E
             - 1897.0 * cos(deg2rad(4 * $D - $M - 2 * $M_accent)) * $E
             - 2117.0 * cos(deg2rad(2 * $M - $M_accent)) * pow($E, 2)
             + 2354.0 * cos(deg2rad(2 * $D + 2 * $M - $M_accent)) * pow($E, 2)
             - 1423.0 * cos(deg2rad(4 * $D + $M_accent))
             - 1117.0 * cos(deg2rad(4 * $M_accent))
             - 1571.0 * cos(deg2rad(4 * $D - $M)) * $E
             - 1739.0 * cos(deg2rad($D - 2 * $M_accent))
             - 4421.0 * cos(deg2rad(2 * $M_accent - 2 * $F))
             + 1165.0 * cos(deg2rad(2 * $M + $M_accent)) * pow($E, 2)
             + 8752.0 * cos(deg2rad(2 * $D - $M_accent - 2 * $F));

        $moonR = 385000.56 + $R / 1000.0;

        $pi = rad2deg(asin(6378.14 / $moonR));

        $nutat = Time::nutation($jd);

        $eclLongitude += $nutat[0] / 3600.0;

        $ecl = [$eclLongitude, $eclLatitude];

        // Now we transform from ecliptical to equatorial coordinates
        $equa = $this->_convertFromEclipticalToEquatorialCoordinates(
            $ecl,
            $nutat[3]
        );

        $moonRa = $equa[0] / 15;
        $moonDecl = $equa[1];

        return [$moonRa, $moonDecl, $pi];
    }

    /**
     * Converts from ecliptical coordinates to equatorial coordinates.
     *
     * @param array $coords       The ecliptical coordinates
     * @param float $nutObliquity The nutation in Obliquity
     *
     * @return array The equatorial coordinates
     */
    private function _convertFromEclipticalToEquatorialCoordinates(
        $coords,
        $nutObliquity
    ) {
        $ra = rad2deg(
            atan2(
                sin(deg2rad($coords[0])) *
                cos(deg2rad($nutObliquity)) - tan(deg2rad($coords[1])) *
                sin(deg2rad($nutObliquity)),
                cos(deg2rad($coords[0]))
            )
        );

        $decl = rad2deg(
            asin(
                sin(deg2rad($coords[1])) * cos(deg2rad($nutObliquity))
                + cos(deg2rad($coords[1])) * sin(deg2rad($nutObliquity)) *
                sin(deg2rad($coords[0]))
            )
        );

        if ($ra < 0.0) {
            $ra += 360.0;
        }

        return [$ra, $decl];
    }
}
