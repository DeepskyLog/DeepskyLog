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

use DateTime;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Time;
use deepskylog\AstronomyLibrary\Targets\Moon;
use deepskylog\AstronomyLibrary\Coordinates\EclipticalCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;

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
    private $_geo_coords = null;
    private $_timezone = 'UTC';

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
        $this->jd = Time::getJd(Carbon::instance($date));

        $this->_timezone = $timezone;
        $this->_geo_coords = new GeographicalCoordinates($longitude, $latitude);

        // TODO: Stylsheet for white on yellow background
        // TODO: More popups for yearephemerides?
    }

    /**
     * Calculates the Rise, transit and setting time of the moon for a
     * given location.
     *
     * @return Moon The moon target
     */
    public function calculateMoonRiseTransitSettingTime()
    {
        // Step one : calculate the ra and dec for the moon for today, yesterday
        //            and tomorrow
        $jd = floor($this->jd) - 0.5;

        $radec1 = $this->_calculateMoonCoordinates(
            $jd - 1,
            $this->_geo_coords->getLongitude(),
            $this->_geo_coords->getLatitude()
        );
        $radec2 = $this->_calculateMoonCoordinates(
            $jd,
            $this->_geo_coords->getLongitude(),
            $this->_geo_coords->getLatitude()
        );
        $radec3 = $this->_calculateMoonCoordinates(
            $jd + 1,
            $this->_geo_coords->getLongitude(),
            $this->_geo_coords->getLatitude()
        );
        $equa_yesterday = new EquatorialCoordinates($radec1[0], $radec1[1]);
        $equa_today = new EquatorialCoordinates($radec2[0], $radec2[1]);
        $equa_tomorrow = new EquatorialCoordinates($radec3[0], $radec3[1]);

        $date = Time::fromJd($jd);
        $date->hour = 12;
        $date->timezone($this->_timezone);

        $greenwichSiderialTime = Time::apparentSiderialTimeGreenwich(
            $date
        );
        $deltaT = Time::deltaT($date);

        $target = new Moon();
        $target->setEquatorialCoordinatesYesterday($equa_yesterday);
        $target->setEquatorialCoordinatesToday($equa_today);
        $target->setEquatorialCoordinatesTomorrow($equa_tomorrow);

        // Calculate the ephemerids for the target
        $target->calculateEphemerides(
            $this->_geo_coords,
            $greenwichSiderialTime,
            $deltaT
        );

        return $target;
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

        $ecl = new EclipticalCoordinates($eclLongitude, $eclLatitude);

        // Now we transform from ecliptical to equatorial coordinates
        $equa = $ecl->convertToEquatorial($nutat[3]);

        $moonRa = $equa->getRA()->getCoordinate();
        $moonDecl = $equa->getDeclination()->getCoordinate();

        return [$moonRa, $moonDecl, $pi];
    }
}
