<?php

 //
 // 2008-10-27
 // File: moonphase.inc.php (http://www.sentry.net/~obsid/moonphase)
 // Calculate information about the phase of the moon at a given time.
 //
 // Based on the Perl module Astro::MoonPhase, version 0.60.
 // http://search.cpan.org/~brett/Astro-MoonPhase-0.60/MoonPhase.pm
 //
 //
 // License:
 //
 // Astro::MoonPhase module is distributed under the public domain,
 // and so is this PHP translation.
 //
 //
 // Credits:
 //
 // The moontool.c Release 2.0:
 // A Moon for the Sun
 // Designed and implemented by John Walker in December 1987,
 // revised and updated in February of 1988.
 //
 // Initial Perl transcription:
 // Raino Pikkarainen, 1998
 // raino.pikkarainen@saunalahti.fi
 //
 // The moontool.c Release 2.4:
 // Major enhancements by Ron Hitchens, 1989
 //
 // Revisions:
 // Brett Hamilton  http://simple.be/
 // Bug fix, 2003
 // Second transcription and bugfixes, 2004
 //
 // Christopher J. Madsen  http://www.cjmweb.net/
 // Added phaselist function, March 2007
 //
 // Translated to PHP by Stephen A. Zarkos <obsid@sentry.net>, 2007
 // Fixed broken phasehunt function, 2008-10-27
 // Added phaselist function, 2008-10-27
 //
 //
 // Documentation:
 //
 // http://search.cpan.org/~brett/Astro-MoonPhase-0.60/MoonPhase.pm
 // http://www.obsid.org/2008/05/calculate-moon-phase-data-with-php.html
 //


 // Error definitions.
 define( 'ERR_UNDEF', -1 );

 // Astronomical constants.
 define( 'EPOCH', 2444238.5 );  // 1980 January 0.0

 // Constants defining the Sun's apparent orbit.
 define( 'ELONGE',  278.833540 ); // ecliptic longitude of the Sun at epoch 1980.0
 define( 'ELONGP',  282.596403 ); // ecliptic longitude of the Sun at perigee
 define( 'ECCENT',  0.016718 ); // eccentricity of Earth's orbit
 define( 'SUNSMAX', 1.495985e8 ); // semi-major axis of Earth's orbit, km
 define( 'SUNANGSIZ', 0.533128 ); // sun's angular size, degrees, at semi-major axis distance

 // Elements of the Moon's orbit, epoch 1980.0.
 define( 'MMLONG',  64.975464 );  // moon's mean longitude at the epoch
 define( 'MMLONGP', 349.383063 ); // mean longitude of the perigee at the epoch
 define( 'MLNODE',  151.950429 ); // mean longitude of the node at the epoch
 define( 'MINC',  5.145396 ); // inclination of the Moon's orbit
 define( 'MECC',  0.054900 ); // eccentricity of the Moon's orbit
 define( 'MANGSIZ', 0.5181 ); // moon's angular size at distance a from Earth
 define( 'MSMAX', 384401.0 ); // semi-major axis of Moon's orbit in km
 define( 'MPARALLAX', 0.9507 ); // parallax at distance a from Earth
 define( 'SYNMONTH',  29.53058868 );  // synodic month (new Moon to new Moon)


 // Handy mathematical functions.
 function sgn ( $arg )    { return (($arg < 0) ? -1 : ($arg > 0 ? 1 : 0)); }  // extract sign
 function fixangle ( $arg ) { return ($arg - 360.0 * (floor($arg / 360.0))); }  // fix angle
 function torad ( $arg )    { return ($arg * (pi() / 180.0)); }     // deg->rad
 function todeg ( $arg )    { return ($arg * (180.0 / pi())); }     // rad->deg
 function dsin ( $arg )   { return (sin(torad($arg))); }        // sin from deg
 function dcos ( $arg )   { return (cos(torad($arg))); }        // cos from deg


 // jtime - convert internal date and time to astronomical Julian
 // time (i.e. Julian date plus day fraction)
 function jtime ( $timestamp )  {
  $julian = ( $timestamp / 86400 ) + 2440587.5; // (seconds / (seconds per day)) + julian date of epoch
  return $julian;
 }



 // jdaytosecs - convert Julian date to a UNIX epoch
 function jdaytosecs ( $jday=0 )  {
  $stamp = ( $jday - 2440587.5 ) * 86400; // (juliandate - jdate of unix epoch) * (seconds per julian day)
  return $stamp;
 }



 // jyear - convert Julian date to year, month, day, which are
 // returned via integer pointers to integers
 function jyear ( $td, &$yy, &$mm, &$dd )  {
  $td += 0.5; // astronomical to civil.
  $z = floor( $td );
  $f = $td - $z;

  if ( $z < 2299161.0 )  {
    $a = $z;
  }
  else  {
    $alpha = floor( ($z - 1867216.25) / 36524.25 );
    $a = $z + 1 + $alpha - floor( $alpha / 4 );
  }

  $b = $a + 1524;
  $c = floor( ($b - 122.1) / 365.25 );
  $d = floor( 365.25 * $c );
  $e = floor( ($b - $d) / 30.6001 );

  $dd = $b - $d - floor( 30.6001 * $e ) + $f;
  $mm = $e < 14 ? $e - 1 : $e - 13;
  $yy = $mm > 2 ? $c - 4716 : $c - 4715;
 }



 //  meanphase  --  Calculates time of the mean new Moon for a given
 //                 base date. This argument K to this function is the
 //                 precomputed synodic month index, given by:
 //
 //                        K = (year - 1900) * 12.3685
 //
 //                 where year is expressed as a year and fractional year.  
 function meanphase ( $sdate, $k )  {

  // Time in Julian centuries from 1900 January 0.5
  $t = ( $sdate - 2415020.0 ) / 36525;
  $t2 = $t * $t;  // Square for frequent use 
  $t3 = $t2 * $t; // Cube for frequent use 

  $nt1 = 2415020.75933 + SYNMONTH * $k
    + 0.0001178 * $t2
    - 0.000000155 * $t3
    + 0.00033 * dsin( 166.56 + 132.87 * $t - 0.009173 * $t2 );

  return ( $nt1 );
 }



 // truephase - given a K value used to determine the mean phase of the
 // new moon, and a phase selector (0.0, 0.25, 0.5, 0.75),
 // obtain the true, corrected phase time.
 function truephase ( $k, $phase )  {
  $apcor = 0;

  $k += $phase;     // add phase to new moon time
  $t = $k / 1236.85;    // time in Julian centuries from 1900 January 0.5
  $t2 = $t * $t;      // square for frequent use
  $t3 = $t2 * $t;     // cube for frequent use

  // mean time of phase
  $pt = 2415020.75933
    + SYNMONTH * $k
    + 0.0001178 * $t2
    - 0.000000155 * $t3
    + 0.00033 * dsin( 166.56 + 132.87 * $t - 0.009173 * $t2 );

  // Sun's mean anomaly
  $m = 359.2242
    + 29.10535608 * $k
    - 0.0000333 * $t2
    - 0.00000347 * $t3;

  // Moon's mean anomaly
  $mprime = 306.0253
    + 385.81691806 * $k
    + 0.0107306 * $t2
    + 0.00001236 * $t3;

  // Moon's argument of latitude
  $f = 21.2964
    + 390.67050646 * $k
    - 0.0016528 * $t2
    - 0.00000239 * $t3;

  if ( ($phase < 0.01) || (abs($phase - 0.5) < 0.01) )  {
    // Corrections for New and Full Moon.
    $pt += ( 0.1734 - 0.000393 * $t ) * dsin( $m )
      + 0.0021 * dsin( 2 * $m  )
      - 0.4068 * dsin( $mprime )
      + 0.0161 * dsin( 2 * $mprime )
      - 0.0004 * dsin( 3 * $mprime )
      + 0.0104 * dsin( 2 * $f )
      - 0.0051 * dsin( $m + $mprime )
      - 0.0074 * dsin( $m - $mprime )
      + 0.0004 * dsin( 2 * $f + $m )
      - 0.0004 * dsin( 2 * $f - $m )
      - 0.0006 * dsin( 2 * $f + $mprime )
      + 0.0010 * dsin( 2 * $f - $mprime )
      + 0.0005 * dsin( $m + 2 * $mprime );
    $apcor = 1;
  }
  elseif ( (abs($phase - 0.25) < 0.01 || (abs($phase - 0.75) < 0.01)) )  {
    $pt += ( 0.1721 - 0.0004 * $t ) * dsin( $m )
      + 0.0021 * dsin( 2 * $m )
      - 0.6280 * dsin( $mprime )
      + 0.0089 * dsin( 2 * $mprime )
      - 0.0004 * dsin( 3 * $mprime )
      + 0.0079 * dsin( 2 * $f )
      - 0.0119 * dsin( $m + $mprime )
      - 0.0047 * dsin( $m - $mprime )
      + 0.0003 * dsin( 2 * $f + $m )
      - 0.0004 * dsin( 2 * $f - $m )
      - 0.0006 * dsin( 2 * $f + $mprime )
      + 0.0021 * dsin( 2 * $f - $mprime )
      + 0.0003 * dsin( $m + 2 * $mprime )
      + 0.0004 * dsin( $m - 2 * $mprime )
      - 0.0003 * dsin( 2 * $m + $mprime );
    if ( $phase < 0.5 )  {
      // First quarter correction.
      $pt += 0.0028 - 0.0004 * dcos( $m ) + 0.0003 * dcos( $mprime );
    }
    else {
      // Last quarter correction.
      $pt += -0.0028 + 0.0004 * dcos( $m ) - 0.0003 * dcos( $mprime );
    }
    $apcor = 1;
  }
  if ( !$apcor )  {
    print "truephase() called with invalid phase selector ($phase).\n";
    exit( ERR_UNDEF );
  }
  return ( $pt );
 }



 // phasehunt - find time of phases of the moon which surround the current
 // date.  Five phases are found, starting and ending with the
 // new moons which bound the current lunation
 function phasehunt ( $time=-1 )  {

  if ( empty($time) || $time == -1 )  {
    $time = time();
  }
  $sdate = jtime( $time );
  $adate = $sdate - 45;
  jyear( $adate, $yy, $mm, $dd );
  $k1 = floor( ($yy + (($mm - 1) * (1.0 / 12.0)) - 1900) * 12.3685 );
  $adate = $nt1 = meanphase( $adate,  $k1 );

  while (1)  {
    $adate += SYNMONTH;
    $k2 = $k1 + 1;
    $nt2 = meanphase( $adate, $k2 );
    if (($nt1 <= $sdate) && ($nt2 > $sdate))  {
      break;
    }
    $nt1 = $nt2;
    $k1 = $k2;
        }                 

  return array (  jdaytosecs( truephase($k1, 0.0) ),
      jdaytosecs( truephase($k1, 0.25) ),
      jdaytosecs( truephase($k1, 0.5) ), 
      jdaytosecs( truephase($k1, 0.75) ),
      jdaytosecs( truephase($k2, 0.0) )
  );
 }



 // phaselist() - Find time of phases of the moon between two dates.
 // Times (in & out) are seconds_since_1970
 function phaselist ( $sdate, $edate )  {
  if ( empty($sdate) || empty($edate) )  {
    return array();
  }

  $sdate = jtime( $sdate );
  $edate = jtime( $edate );

  $phases = array();
  $d = $k = $yy = $mm = 0;

  jyear( $sdate, $yy, $mm, $d );
  $k = floor(($yy + (($mm - 1) * (1.0 / 12.0)) - 1900) * 12.3685) - 2;

  while (1)  {
    ++$k;
    foreach ( array(0.0, 0.25, 0.5, 0.75) as $phase )  {
      $d = truephase( $k, $phase );
      if ( $d >= $edate )  {
        return $phases;
      }
      if ( $d >= $sdate )  {
        if ( empty($phases) )  {
          array_push( $phases, floor(4 * $phase) );
        }
        array_push( $phases, jdaytosecs($d) );
      }
    }
  }  // End while(1)
 }



 // kepler() - solve the equation of Kepler
 function kepler ( $m, $ecc ) {
  $EPSILON = 1e-6;
  $m = torad( $m );
  $e = $m;
  do  {
    $delta = $e - $ecc * sin( $e ) - $m;
    $e -= $delta / ( 1 - $ecc * cos($e) );
  } while ( abs($delta) > $EPSILON );
  return ( $e );
 }



 // phase() - calculate phase of moon as a fraction:
 //
 // The argument is the time for which the phase is requested,
 // expressed as a Julian date and fraction.  Returns the terminator
 // phase angle as a percentage of a full circle (i.e., 0 to 1),
 // and stores into pointer arguments the illuminated fraction of
 // the Moon's disc, the Moon's age in days and fraction, the
 // distance of the Moon from the centre of the Earth, and the
 // angular diameter subtended by the Moon as seen by an observer
 // at the centre of the Earth.
 function phase ( $time=0 )  {
  if ( empty($time) || $time == 0 )  {
    $time = time();
  }
  $pdate = jtime( $time );

  $pphase;  // illuminated fraction
  $mage;    // age of moon in days
  $dist;    // distance in kilometres
  $angdia;  // angular diameter in degrees
  $sudist;  // distance to Sun
  $suangdia;  // sun's angular diameter

//  my ($Day, $N, $M, $Ec, $Lambdasun, $ml, $MM, $MN, $Ev, $Ae, $A3, $MmP,
//     $mEc, $A4, $lP, $V, $lPP, $NP, $y, $x, $Lambdamoon, $BetaM,
//     $MoonAge, $MoonPhase,
//     $MoonDist, $MoonDFrac, $MoonAng, $MoonPar,
//     $F, $SunDist, $SunAng,
//     $mpfrac);

  // Calculation of the Sun's position.
  $Day = $pdate - EPOCH;            // date within epoch
  $N = fixangle( (360 / 365.2422) * $Day );     // mean anomaly of the Sun
  $M = fixangle( $N + ELONGE - ELONGP );        // convert from perigee co-ordinates
                  //   to epoch 1980.0
  $Ec = kepler( $M, ECCENT );         // solve equation of Kepler
  $Ec = sqrt( (1 + ECCENT) / (1 - ECCENT) ) * tan( $Ec / 2 );
  $Ec = 2 * todeg( atan($Ec) );         // true anomaly
  $Lambdasun = fixangle( $Ec + ELONGP );        // Sun's geocentric ecliptic longitude
  # Orbital distance factor.
  $F = ( (1 + ECCENT * cos(torad($Ec))) / (1 - ECCENT * ECCENT) );
  $SunDist = SUNSMAX / $F;          // distance to Sun in km
  $SunAng = $F * SUNANGSIZ;         // Sun's angular size in degrees


  // Calculation of the Moon's position.

  // Moon's mean longitude.
  $ml = fixangle( 13.1763966 * $Day + MMLONG );

  // Moon's mean anomaly.
  $MM = fixangle( $ml - 0.1114041 * $Day - MMLONGP );

  // Moon's ascending node mean longitude.
  $MN = fixangle( MLNODE - 0.0529539 * $Day );

  // Evection.
  $Ev = 1.2739 * sin( torad(2 * ($ml - $Lambdasun) - $MM) );

  // Annual equation.
  $Ae = 0.1858 * sin( torad($M) );

  // Correction term.
  $A3 = 0.37 * sin( torad($M) );

  // Corrected anomaly.
  $MmP = $MM + $Ev - $Ae - $A3;

  // Correction for the equation of the centre.
  $mEc = 6.2886 * sin( torad($MmP) );

  // Another correction term.
  $A4 = 0.214 * sin( torad(2 * $MmP) );

  // Corrected longitude.
  $lP = $ml + $Ev + $mEc - $Ae + $A4;

  // Variation.
  $V = 0.6583 * sin( torad(2 * ($lP - $Lambdasun)) );

  // True longitude.
  $lPP = $lP + $V;

  // Corrected longitude of the node.
  $NP = $MN - 0.16 * sin( torad($M) );

  // Y inclination coordinate.
  $y = sin( torad($lPP - $NP) ) * cos( torad(MINC) );

  // X inclination coordinate.
  $x = cos(torad($lPP - $NP));

  // Ecliptic longitude.
  $Lambdamoon = todeg( atan2($y, $x) );
  $Lambdamoon += $NP;

  // Ecliptic latitude.
  $BetaM = todeg( asin(sin(torad($lPP - $NP)) * sin(torad(MINC))) );


  // Calculation of the phase of the Moon.

  // Age of the Moon in degrees.
  $MoonAge = $lPP - $Lambdasun;

  // Phase of the Moon.
  $MoonPhase = (1 - cos(torad($MoonAge))) / 2;

  // Calculate distance of moon from the centre of the Earth.
  $MoonDist = ( MSMAX * (1 - MECC * MECC)) / (1 + MECC * cos(torad($MmP + $mEc)) );

  // Calculate Moon's angular diameter.
  $MoonDFrac = $MoonDist / MSMAX;
  $MoonAng = MANGSIZ / $MoonDFrac;

  // Calculate Moon's parallax.
  $MoonPar = MPARALLAX / $MoonDFrac;

  $pphase = $MoonPhase;
  $mage = SYNMONTH * ( fixangle($MoonAge) / 360.0 );
  $dist = $MoonDist;
  $angdia = $MoonAng;
  $sudist = $SunDist;
  $suangdia = $SunAng;
  $mpfrac = fixangle($MoonAge) / 360.0;

  return array ( $mpfrac, $pphase, $mage, $dist, $angdia, $sudist, $suangdia );
 }
?>
