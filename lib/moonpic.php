<?php
require_once 'lib/moonphase.inc.php';
// moonpic.php
// functions getting moon inmage
function getMoonPic($date, $realTime, $latitude, $longitude, $timezone){
	global $objAstroCalc;
	
	// Show the moon during the observation
	$date = explode("-", $date); 
	$year = $date [0];
	$month = $date [1];
	$day = $date [2];
	$date = $date [0] . "-" . $date [1] . "-" . $date [2];
	
	if ($realTime < 0) {
		$time = "23:59:59";
	} else {
		$time = $realTime;
	}
	$tzone = "GMT";
	$moondata = phase ( strtotime ( $date . ' 23:59:59 ' . $tzone ) );
	$MoonIllum = $moondata [1];
	$MoonAge = $moondata [2];
	// Convert $MoonIllum to percent and round to whole percent.
	$MoonIllum = round ( $MoonIllum, 2 );
	$MoonIllum *= 100;
	$file = "m" . round ( ($MoonAge / SYNMONTH) * 40 ) . ".gif";
	
	// Moon is above the horizon
	if ($realTime < 0) {
		$moon = "<img src=\"/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" />";
	} else {
		// Calculate altitude of the moon for this date, time and location
	
		// Get the julian day of the observation...
		$jd = gregoriantojd ( $month+0, $day+0, $year+0 );
	
		$dateTimeZone = new DateTimeZone ( $timezone );
	
		$datestr = sprintf ( "%02d", $month ) . "/" . sprintf ( "%02d", $day ) . "/" . $year;
		$dateTime = new DateTime ( $datestr, $dateTimeZone );
		// Geeft tijdsverschil terug in seconden
		$timedifference = $dateTimeZone->getOffset ( $dateTime );
		$timedifference = $timedifference / 3600.0;
	
		if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0)
			$timedifference = - $timedifference;
			// Calculate the rise and set time of the moon
			$moonCalc = $objAstroCalc->calculateMoonRiseTransitSettingTime ( $jd, $longitude, $latitude, $timedifference );
	
			// Now we know when the moon rises and sets. We have to convert the time and compare with the time of the observation.
			// $moonCalc[0] = rise
			// $moonCalc[2] = set
			$moonriseArray = sscanf ( $moonCalc [0], "%d:%d" );
			$moonsetArray = sscanf ( $moonCalc [2], "%d:%d" );
			$moonRise = $moonriseArray [0] * 100.0 + $moonriseArray [1];
			$moonSet = $moonsetArray [0] * 100.0 + $moonsetArray [1];
	
			$moonAboveHorizon = true;
			if ($moonRise > $moonSet) {
				if ($time <= $moonRise && $time >= $moonSet) {
					$moonAboveHorizon = false;
				}
			} else {
				if ($time <= $moonRise || $time >= $moonSet) {
					$moonAboveHorizon = false;
				}
			}
	
			$ext = "";
			if (!$moonAboveHorizon) {
				$file = "below.png";
				$ext = " - " . _('under the horizon');
			}
			
			
			$moon = $moon = "<img src=\"/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "% ".$ext."\" alt=\"" . $MoonIllum . "%\" />";
	}	
	
	return $moon;
	
}


?>