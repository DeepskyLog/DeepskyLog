<?php
interface iAstroCalc
{    public function calculateRiseTransitSettingTime($longitude, $latitude, $ra, $dec, $jd);  // Rising, transit and setting time of an object
}
class AstroCalc implements iAstroCalc
{ 
  public  function __construct()                                                // Constructor initialises the public atlasCodes property
  { // Nothing to do in the constructor?
  //  $jd = 2455215.18264;
  //  $latitude = 50 + (50.0 / 60.0) + (59.99 / 3600.0);
  //  $longitude = 4 + (20.0 / 60.0) + (59.0 / 3600.0);
  //  
  //  $moon = $this->calculateMoonRiseTransitSettingTime($jd, $longitude, $latitude);
  //  
  //  print "MOON : \n";
  //  print "Rising  : " . floor($moon[0]) . ":" . floor(($moon[0] - floor($moon[0])) * 60) . "\n";
  //  print "Transit : " . floor($moon[1]) . ":" . floor(($moon[1] - floor($moon[1])) * 60) . "\n";
  //  print "Setting : " . floor($moon[2]) . ":" . floor(($moon[2] - floor($moon[2])) * 60) . "\n";
  //  print "% : " . $moon[3] . "\n";
  }

  // This function calculates the rise, transit and setting time of an object (not the sun of the moon).
  // $longitude is the longitude of the location where you observe... East is positive, west is negative.
  // $latitude is the latitude of the location where you observe... North is positive
  // $starTime should be calculated using the calculateStarTime method
  // The accuracy is a few minutes... When the object does not rise above the horizon, the setting time and
  // the rising time will be NaN. The transit time will be given (the time of the highest point of the object,
  // even when the object is under the horizon).
  // The rising, transit and setting time are given in UT. The should be corrected with the offset in hours of the
  // observing place.
  // An array is returned where [0] is the rising time, [1] the transit time and [2] the setting time.
  public  function calculateRiseTransitSettingTime($longitude, $latitude, $ra, $dec, $starTime, $moon = 0)
  { $ra = $ra * 15.0;
    if ($moon == 1) {
      $h0 = 0.125;
    } else {
      $h0 = -0.5667;
    }
    $longitude = -$longitude;

    $starTime = $starTime * 15.0;
    $Hcap0 = $this->todeg(acos((sin($this->torad($h0)) - sin($this->torad($latitude)) * sin($this->torad($dec)))
        / (cos($this->torad($latitude)) * cos($this->torad($dec)))));

    $m0 = ($ra + $longitude - $starTime) / 360.0;
    $m0 = $m0 - floor($m0);

    $m1 = $m0 - $Hcap0 / 360.0;
    $m1 = $m1 - floor($m1);

    $m2 = $m0 + $Hcap0 / 360;
    $m2 = $m2 - floor($m2);

    $rising = ($m1 * 24.0);
    $transit = $m0 * 24.0;
    $setting = $m2 * 24.0;

    $ris_tra_set[0] = $rising;
    $ris_tra_set[1] = $transit;
    $ris_tra_set[2] = $setting;

    return $ris_tra_set;
  }

  function torad ( $arg )    { return ($arg * (pi() / 180.0)); }     // deg->rad

  function todeg ( $arg )    { return ($arg * (180.0 / pi())); }     // rad->deg

  // This method returns the star time of a given julian day.
  private function calculateStarTime($jd)
  {
    $jd = floor($jd - 0.5) + 0.5;

    $T = ($jd - 2451545.0) / 36525.0;
    $starTime = 280.46061837 + 360.98564736629 * ($jd - 2451545.0)
          + 0.000387933 * pow($T, 2) - pow($T, 3) / 38710000.0;

    if($starTime < 0.0)
    {
      $a = - floor($starTime / 360.0) + 1;
      $starTime = $starTime + $a * 360.0;
    }
    if($starTime > 360.0)
    {
      $a = floor($starTime / 360.0);
      $starTime = $starTime - $a * 360.0;
    }
    $starTime = $starTime / 15.0;

    // Now we calculate the nutation
    // THIS IS ONLY NEEDED FOR MORE ACCURACY
    /* D stands for mean elongation of the moon from the sun. */
    $D = 297.85036 + 445267.111480 * $T - 0.0019142 * pow($T, 2) + pow($T, 3)
          / 189474.0;
    $D = $D - floor($D / 360.0) * 360;
    /* M stands for mean anomaly of the sun */
    $M = 357.52772 + 35999.050340 * $T - 0.0001603 * pow($T, 2) - pow($T, 3) /
          300000.0;
    $M = $M - floor($M / 360.0) * 360;

    /* M_accent stands for mean anomaly of the moon */
    $M_accent = 134.96298 + 477198.867398 * $T + 0.0086972 * pow($T, 2) +
          pow($T, 3) / 56250.0;
    $M_accent = $M_accent - floor($M_accent / 360.0) * 360;

    /* F stands for the moon's argument of latitude */
    $F = 93.27191 + 483202.017538 * $T - 0.0036825 * pow($T, 2) + pow($T, 3) /
          327270.0;
    $F = $F - floor($F / 360.0) * 360;

    /* Omega stands for the longitude of the ascending node of the moon's
     mean orbit on the ecliptic, measured from the mean equinox of the date
     */
    $omega = 125.04452 - 1934.136261 * $T + 0.0020708 * pow($T, 2) + pow($T,
          3) / 450000.0;
    $omega = $omega - floor($omega / 360.0) * 360;
    $L = 280.4665 + 36000.7698 * $T;
    $L_accent = 218.3165 + 481267.8813 * $T;

    /* The nutation in longitude has an accuracy of 0.5 seconds of arc */
    $nutLongitude = -17.2 * sin($this->torad($omega)) - 1.32 * sin($this->torad(2*$L)) -0.23
          * sin($this->torad(2 * $L_accent)) + 0.21 * sin($this->torad(2 * $omega));

    $U = $T / 100.0;
    /* For the obliquity, we have an accuracy of 0.01 arcseconds after
     1000 years. (A.D. 1000 - 3000). The accuracy is still a few seconds of
     arc 10000 years after or before 2000 A.D. */
    $nutObliquity = (84381.448 - 4680.93 * $U
          - 1.55 * pow($U, 2)
          + 1999.25 * pow($U, 3)
          - 51.38 * pow($U, 4)
          - 249.67 * pow($U, 5)
          - 39.05 * pow($U, 6)
          + 7.12 * pow($U, 7)
          + 27.87 * pow($U, 8)
          + 5.79 * pow($U, 9)
          + 2.45 * pow($U, 10)) / 3600.0;

    $starTime = $starTime + (($nutLongitude *
    cos($this->torad($nutObliquity)) / 15.0) / 3600.0);

    return $starTime;
  }

  // Calculates the Rise, transit and setting time of the moon for a given location. 
  // $longitude is the longitude of the location where you observe... East is positive, west is negative.
  // $latitude is the latitude of the location where you observe... North is positive

  // THIS DOES NOT WORK!!!! OF COURSE NOT! THE MOON MOVES TO FAST TO calculate the position for one moment and then calculate setting and rise time.... TO CHECK!!!
  public function calculateMoonRiseTransitSettingTime($jd, $longitude, $latitude)
  {
    $T = ($jd + 0.5 - 2451545.0) / 36525.0;

    /* Moon's mean longitude */
    $L_accent = 218.3164591 + 481267.88134236 * $T - 0.0013268 * pow($T, 2) +
          pow($T, 3) / 538841.0 - pow($T, 4) / 65194000.0;

    $L_accent = $L_accent - floor($L_accent / 360.0) * 360.0;

    /* Mean elongation of the moon */
    $D = 297.8502042 + 445267.1115168 * $T - 0.0016300 * pow($T, 2) +
          pow($T, 3) / 545868.0 - pow($T, 4) / 113065000.0;

    $D = $D - floor($D / 360.0) * 360.0;

    /* Sun's mean anomaly */
    $M = 357.5291092 + 35999.0502909 * $T - 0.0001536 * pow($T, 2) + pow($T, 3) / 24490000.0;

    $M = $M - floor($M / 360.0) * 360.0;

    /* Moon's mean anomaly */
    $M_accent = 134.9634114 + 477198.8676313 * $T + 0.0089970 * pow($T, 2) +
          pow($T, 3) / 69699.0 - pow($T, 4) / 14712000.0;

    $M_accent = $M_accent - floor($M_accent / 360.0) * 360.0;

    /*Moon's argument of latitude */
    $F = 93.2720993 + 483202.0175273 * $T - 0.0034029 * pow($T, 2) -
          pow($T, 3) / 3526000.0 + pow($T, 4) / 863310000.0;

    $F = $F - floor($F / 360.0) * 360.0;

    $A1 = 119.75 + 131.849 * $T;
    $A1 = $A1 - floor($A1 / 360.0) * 360.0;

    $A2 =  53.09 + 479264.290 * $T;
    $A2 = $A2 - floor($A2 / 360.0) * 360.0;

    $A3 = 313.45 + 481266.484 * $T;
    $A3 = $A3 - floor($A3 / 360.0) * 360.0;

    $E = 1 - 0.002516 * $T - 0.0000074 * pow($T, 2);

    $L = 6288774.0 * sin($this->torad($M_accent))
        +1274027.0 * sin($this->torad(2 * $D - $M_accent))
        +658314.0 * sin($this->torad(2 * $D))
        +213618.0 * sin($this->torad(2 * $M_accent))
        -185116.0 * sin($this->torad($M)) * $E
        -114332.0 * sin($this->torad(2 * $F))
        +58793.0 * sin($this->torad(2 * $D - 2 * $M_accent))
        +57066.0 * sin($this->torad(2 * $D - $M - $M_accent)) * $E
        +53322.0 * sin($this->torad(2 * $D + $M_accent))
        +45758.0 * sin($this->torad(2 * $D - $M)) * $E
        -40923.0 * sin($this->torad($M - $M_accent)) * $E
        -34720.0 * sin($this->torad($D))
        -30383 * sin($this->torad($M + $M_accent)) * $E
        +15327 * sin($this->torad(2 * $D - 2 * $F))
        -12528 * sin($this->torad($M_accent + 2 * $F))
        +10980 * sin($this->torad($M_accent - 2 * $F))
        +10675 * sin($this->torad(4 * $D - $M_accent))
        +10034 * sin($this->torad(3 * $M_accent))
        +8548 * sin($this->torad(4 * $D - 2 * $M_accent))
        -7888 * sin($this->torad(2 * $D + $M - $M_accent)) * $E
        -6766 * sin($this->torad(2 * $D + $M)) * $E
        -5163 * sin($this->torad($D - $M_accent))
        +4987 * sin($this->torad($D + $M)) * $E
        +4036 * sin($this->torad(2 * $D - $M + $M_accent)) * $E
        +3994 * sin($this->torad(2 * $D + 2 * $M_accent))
        +3861 * sin($this->torad(4 * $D))
        +3665 * sin($this->torad(2 * $D - 3 * $M_accent))
        -2689 * sin($this->torad($M - 2 * $M_accent)) * $E
        -2602 * sin($this->torad(2 * $D - $M_accent + 2 * $F))
        +2390 * sin($this->torad(2 * $D - $M - 2 * $M_accent)) * $E
        -2348 * sin($this->torad($D + $M_accent))
        +2236 * sin($this->torad(2 * $D - 2 * $M)) * pow($E, 2)
        -2120 * sin($this->torad($M + 2 * $M_accent)) * $E
        -2069 * sin($this->torad(2 * $M)) * pow($E, 2)
        +2048 * sin($this->torad(2 * $D - 2 * $M - $M_accent)) * pow($E, 2)
        -1773 * sin($this->torad(2 * $D + $M_accent - 2 * $F))
        -1595 * sin($this->torad(2 * $D + 2 * $F))
        +1215 * sin($this->torad(4 * $D - $M - $M_accent)) * $E
        -1110 * sin($this->torad(2 * $M_accent + 2 * $F))
        -892 * sin($this->torad(3 * $D - $M_accent))
        -810 * sin($this->torad(2 * $D + $M + $M_accent)) * $E
        +759 * sin($this->torad(4 * $D - $M - 2 * $M_accent)) * $E
        -713 * sin($this->torad(2 * $M - $M_accent)) * pow($E, 2)
        -700 * sin($this->torad(2 * $D + 2 * $M - $M_accent)) * pow($E, 2)
        +691 * sin($this->torad(2 * $D + $M - 2 * $M_accent)) * $E
        +596 * sin($this->torad(2 * $D - $M - 2 * $F)) * $E
        +549 * sin($this->torad(4 * $D + $M_accent))
        +537 * sin($this->torad(4 * $M_accent))
        +520 * sin($this->torad(4 * $D - $M)) * $E
        -487 * sin($this->torad($D - 2 * $M_accent))
        -399 * sin($this->torad(2 * $D + $M - 2 * $F)) * $E
        -381 * sin($this->torad(2 * $M_accent - 2 * $F))
        +351 * sin($this->torad($D + $M + $M_accent)) * $E
        -340 * sin($this->torad(3 * $D - 2 * $M_accent))
        +330 * sin($this->torad(4 * $D - 3 * $M_accent))
        +327 * sin($this->torad(2 * $D - $M + 2 * $M_accent)) * $E
        -323 * sin($this->torad(2 * $M + $M_accent)) * pow($E, 2)
        +299 * sin($this->torad($D + $M - $M_accent)) * $E
        +294 * sin($this->torad(2 * $D + 3 * $M_accent));

    $L = $L + 3958 * sin($this->torad($A1))
            + 1962 * sin($this->torad($L_accent - $F))
            +  318 * sin($this->torad($A2));

    $eclLongitude = $L_accent + $L / 1000000.0;

    $B = 5128122.0 * sin($this->torad($F))
         +280602.0 * sin($this->torad($M_accent + $F))
         +277693.0 * sin($this->torad($M_accent - $F))
         +173237.0 * sin($this->torad(2 * $D - $F))
         +55413.0 * sin($this->torad(2 * $D - $M_accent + $F))
         +46271.0 * sin($this->torad(2 * $D - $M_accent - $F))
         +32573 * sin($this->torad(2 * $D + $F))
         +17198 * sin($this->torad(2 * $M_accent + $F))
         +9266 * sin($this->torad(2 * $D + $M_accent - $F))
         +8822 * sin($this->torad(2 * $M_accent - $F))
         +8216 * sin($this->torad(2 * $D - $M - $F)) * $E
         +4324 * sin($this->torad(2 * $D - 2 * $M_accent - $F))
         +4200 * sin($this->torad(2 * $D + $M_accent + $F))
         -3359 * sin($this->torad(2 * $D + $M - $F)) * $E
         +2463 * sin($this->torad(2 * $D - $M - $M_accent + $F)) * $E
         +2211 * sin($this->torad(2 * $D - $M + $F)) * $E
         +2065 * sin($this->torad(2 * $D - $M - $M_accent - $F)) * $E
         -1870 * sin($this->torad($M - $M_accent - $F)) * $E
         +1828 * sin($this->torad(4 * $D - $M_accent - $F))
         -1794 * sin($this->torad($M + $F)) * $E
         -1749 * sin($this->torad(3 * $F))
         -1565 * sin($this->torad($M - $M_accent + $F)) * $E
         -1491 * sin($this->torad($D + $F))
         -1475 * sin($this->torad($M + $M_accent + $F)) * $E
         -1410 * sin($this->torad($M + $M_accent - $F)) * $E
         -1344 * sin($this->torad($M - $F)) * $E
         -1335 * sin($this->torad($D - $F))
         +1107 * sin($this->torad(3 * $M_accent + $F))
         +1021 * sin($this->torad(4 * $D - $F))
         +833 * sin($this->torad(4 * $D - $M_accent + $F))
         +777 * sin($this->torad($M_accent - 3 * $F))
         +671 * sin($this->torad(4 * $D - 2 * $M_accent + $F))
         +607 * sin($this->torad(2 * $D - 3 * $F))
         +596 * sin($this->torad(2 * $D + 2 * $M_accent - $F))
         +491 * sin($this->torad(2 * $D - $M + $M_accent - $F)) * $E
         -451 * sin($this->torad(2 * $D - 2 * $M_accent + $F))
         +439 * sin($this->torad(3 * $M_accent - $F))
         +422 * sin($this->torad(2 * $D + 2 * $M_accent + $F))
         +421 * sin($this->torad(2 * $D - 3 * $M_accent - $F))
         -366 * sin($this->torad(2 * $D + $M - $M_accent + $F)) * $E
         -351 * sin($this->torad(2 * $D + $M + $F)) * $E
         +331 * sin($this->torad(4 * $D + $F))
         +315 * sin($this->torad(2 * $D - $M + $M_accent + $F)) * $E
         +302 * sin($this->torad(2 * $D - 2 * $M - $F)) * pow($E, 2)
         -283 * sin($this->torad($M_accent + 3 * $F))
         -229 * sin($this->torad(2 * $D + $M + $M_accent - $F)) * $E
         +223 * sin($this->torad($D + $M - $F)) * $E
         +223 * sin($this->torad($D + $M + $F)) * $E
         -220 * sin($this->torad($M - 2 * $M_accent - $F)) * $E
         -220 * sin($this->torad(2 * $D + $M - $M_accent - $F)) * $E
         -185 * sin($this->torad($D + $M_accent + $F))
         +181 * sin($this->torad(2 * $D - $M - 2 * $M_accent - $F)) * $E
         -177 * sin($this->torad($M + 2 * $M_accent + $F)) * $E
         +176 * sin($this->torad(4 * $D - 2 * $M_accent - $F))
         +166 * sin($this->torad(4 * $D - $M - $M_accent - $F)) * $E
         -164 * sin($this->torad($D + $M_accent - $F))
         +132 * sin($this->torad(4 * $D + $M_accent - $F))
         -119 * sin($this->torad($D - $M_accent - $F))
         +115 * sin($this->torad(4 * $D - $M - $F)) * $E
         +107 * sin($this->torad(2 * $D - 2 * $M + $F)) * pow($E, 2);

    $B = $B - 2235 * sin($this->torad($L_accent))
            +  382 * sin($this->torad($A3))
            +  175 * sin($this->torad($A1 - $F))
            +  175 * sin($this->torad($A1 + $F))
            +  127 * sin($this->torad($L_accent - $M_accent))
            -  115 * sin($this->torad($L_accent + $M_accent));

    $eclLatitude = $B / 1000000.0;

    $R = -20905355.0 * cos($this->torad($M_accent))
         - 3699111.0* cos($this->torad(2 * $D - $M_accent))
         - 2955968.0 * cos($this->torad(2 * $D))
         -  569925.0 * cos($this->torad(2 * $M_accent))
         +   48888.0 * cos($this->torad($M)) * $E
         -    3149.0 * cos($this->torad(2 * $F))
         +  246158.0 * cos($this->torad(2 * $D - 2 * $M_accent))
         -  152138.0 * cos($this->torad(2 * $D - $M - $M_accent)) * $E
         -  170733.0 * cos($this->torad(2 * $D + $M_accent))
         -  204586.0 * cos($this->torad(2 * $D - $M)) * $E
         -  129620.0 * cos($this->torad($M - $M_accent)) * $E
         +  108743.0 * cos($this->torad($D))
         +  104755.0 * cos($this->torad($M + $M_accent)) * $E
         +   10321.0 * cos($this->torad(2 * $D - 2 * $F))
         +   79661.0 * cos($this->torad($M_accent - 2 * $F))
         -   34782.0 * cos($this->torad(4 * $D - $M_accent))
         -   23210.0 * cos($this->torad(3 * $M_accent))
         -   21636.0 * cos($this->torad(4 * $D - 2 * $M_accent))
         +   24208.0 * cos($this->torad(2 * $D + $M - $M_accent)) * $E
         +   30824.0 * cos($this->torad(2 * $D + $M)) * $E
         -    8379.0 * cos($this->torad($D - $M_accent))
         -   16675.0 * cos($this->torad($D + $M)) * $E
         -   12831.0 * cos($this->torad(2 * $D - $M + $M_accent)) * $E
         -   10445.0 * cos($this->torad(2 * $D + 2 * $M_accent))
         -   11650.0 * cos($this->torad(4 * $D))
         +   14403.0 * cos($this->torad(2 * $D - 3 * $M_accent))
         -    7003.0 * cos($this->torad($M - 2 * $M_accent)) * $E
         +   10056.0 * cos($this->torad(2 * $D - $M - 2 * $M_accent)) * $E
         +    6322.0 * cos($this->torad($D + $M_accent))
         -    9884.0 * cos($this->torad(2 * $D - 2 * $M)) * pow($E, 2)
         +    5751.0 * cos($this->torad($M + 2 * $M_accent)) * $E
         -    4950.0 * cos($this->torad(2 * $D - 2 * $M - $M_accent)) * pow($E, 2)
         +    4130.0 * cos($this->torad(2 * $D + $M_accent - 2 * $F))
         -    3958.0 * cos($this->torad(4 * $D - $M - $M_accent)) * $E
         +    3258.0 * cos($this->torad(3 * $D - $M_accent))
         +    2616.0 * cos($this->torad(2 * $D + $M + $M_accent)) * $E
         -    1897.0 * cos($this->torad(4 * $D - $M - 2 * $M_accent)) * $E
         -    2117.0 * cos($this->torad(2 * $M - $M_accent)) * pow($E, 2)
         +    2354.0 * cos($this->torad(2 * $D + 2 * $M - $M_accent)) * pow($E, 2)
         -    1423.0 * cos($this->torad(4 * $D + $M_accent))
         -    1117.0 * cos($this->torad(4 * $M_accent))
         -    1571.0 * cos($this->torad(4 * $D - $M)) * $E
         -    1739.0 * cos($this->torad($D - 2 * $M_accent))
         -    4421.0 * cos($this->torad(2 * $M_accent - 2 * $F))
         +    1165.0 * cos($this->torad(2 * $M + $M_accent)) * pow($E, 2)
         +    8752.0 * cos($this->torad(2 * $D - $M_accent - 2 * $F));

    $moonR = 385000.56 + $R / 1000.0;

    // Now we calculate the nutation
    // THIS IS ONLY NEEDED FOR MORE ACCURACY
    /* D stands for mean elongation of the moon from the sun. */
    $D = 297.85036 + 445267.111480 * $T - 0.0019142 * pow($T, 2) + pow($T, 3)
         / 189474.0;
    $D = $D - floor($D / 360.0) * 360;
  
    /* M stands for mean anomaly of the sun */
    $M = 357.52772 + 35999.050340 * $T - 0.0001603 * pow($T, 2) - pow($T, 3) /
         300000.0;
    $M = $M - floor($M / 360.0) * 360;

    /* M_accent stands for mean anomaly of the moon */
    $M_accent = 134.96298 + 477198.867398 * $T + 0.0086972 * pow($T, 2) +
         pow($T, 3) / 56250.0;
    $M_accent = $M_accent - floor($M_accent / 360.0) * 360;

    /* F stands for the moon's argument of latitude */
    $F = 93.27191 + 483202.017538 * $T - 0.0036825 * pow($T, 2) + pow($T, 3) /
         327270.0;
    $F = $F - floor($F / 360.0) * 360;

    /* Omega stands for the longitude of the ascending node of the moon's
     mean orbit on the ecliptic, measured from the mean equinox of the date
     */
    $omega = 125.04452 - 1934.136261 * $T + 0.0020708 * pow($T, 2) + pow($T,
         3) / 450000.0;
    $omega = $omega - floor($omega / 360.0) * 360;
    $L = 280.4665 + 36000.7698 * $T;
    $L_accent = 218.3165 + 481267.8813 * $T;

    /* The nutation in longitude has an accuracy of 0.5 seconds of arc */
    $nutLongitude = -17.2 * sin($this->torad($omega)) - 1.32 * sin($this->torad(2*$L)) -0.23
         * sin($this->torad(2 * $L_accent)) + 0.21 * sin($this->torad(2 * $omega));

    $U = $T / 100.0;
    /* For the obliquity, we have an accuracy of 0.01 arcseconds after
     1000 years. (A.D. 1000 - 3000). The accuracy is still a few seconds of
     arc 10000 years after or before 2000 A.D. */
    $nutObliquity = (84381.448 - 4680.93 * $U
         - 1.55 * pow($U, 2)
         + 1999.25 * pow($U, 3)
         - 51.38 * pow($U, 4)
         - 249.67 * pow($U, 5)
         - 39.05 * pow($U, 6)
         + 7.12 * pow($U, 7)
         + 27.87 * pow($U, 8)
         + 5.79 * pow($U, 9)
         + 2.45 * pow($U, 10)) / 3600.0;

    $eclLongitude = $eclLongitude + $nutLongitude / 3600.0;

    $ecl[0] = $eclLongitude;
    $ecl[1] = $eclLatitude;

    // Now we transform from ecliptical to equatorial coordinates
    $equa = $this->convertFromEclipticalToEquatorialCoordinates($ecl, $nutObliquity);

    $moonRa = $equa[0] / 15;
    $moonDecl = $equa[1];

    $starTime = $this->calculateStarTime($jd);
    $moon = $this->calculateRiseTransitSettingTime($longitude, $latitude, $moonRa, $moonDecl, $starTime, 1);

    $i = 180 - $D - 6.289 * sin($this->torad($M_accent))
         + 2.100 * sin($this->torad($M))
         - 1.274 * sin($this->torad(2 * $D - $M_accent))
         - 0.658 * sin($this->torad(2 * $D))
         - 0.214 * sin($this->torad(2 * $M_accent))
         - 0.110 * sin($this->torad($D));

    $moonIllum = (1 + cos($this->torad($i))) / 2.0;

    $moon[3] = $moonIllum;

    return $moon;
  }

  private function convertFromEclipticalToEquatorialCoordinates($coords, $nutObliquity)
  {
    $ra = $this->todeg(atan2(sin($this->torad($coords[0])) *
         cos($this->torad($nutObliquity)) - tan($this->torad($coords[1])) *
         sin($this->torad($nutObliquity)) , cos($this->torad($coords[0]))));
    $decl = $this->todeg(asin(sin($this->torad($coords[1])) * cos($this->torad($nutObliquity))
         + cos($this->torad($coords[1])) * sin($this->torad($nutObliquity)) *
         sin($this->torad($coords[0]))));

    if($ra < 0.0) {
      $ra = $ra + 360.0;
    }

    $equa[0] = $ra;
    $equa[1] = $decl;

    return $equa;
  }
}
$objAstroCalc=new AstroCalc;
?>
