<?php
// moon.php
// menu which shows the moon phase

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_moon();

function menu_moon()
{ global $baseURL,$dateformat,$loggedUser,$menuMoon,
         $objAstroCalc,$objObserver,$objLocation,$objUtil;
	$theYear=$objUtil->checkSessionKey('globalYear',date("Y"));
	$theMonth=$objUtil->checkSessionKey('globalMonth',date("n"));
	$theDay=$objUtil->checkSessionKey('globalDay',date('j'));
	$theHour="";
	$theMinute="";
	$date = $theYear . "-". $theMonth . "-" . $theDay;
	$time = "23:59:59";
	$tzone = "GMT";
	$dateTimeText0=date($dateformat, mktime(0, 0, 0, $theMonth, $theDay, $theYear));
	$dateTimeText1=date($dateformat, mktime(0, 0, 0, $theMonth, $theDay, $theYear)+(60*60*24));
	if($dateformat=='d-M-Y')
	{ if(substr($dateTimeText0,-8)==substr($dateTimeText1,-8))
	    $dateTimeText0=substr($dateTimeText0,0,2);
	  elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
	    $dateTimeText0=substr($dateTimeText0,0,5);
	}
	elseif($dateformat=="d/m/Y")
	{ if(substr($dateTimeText0,-7)==substr($dateTimeText1,-7))
	    $dateTimeText0=substr($dateTimeText0,0,2);
	  elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
	    $dateTimeText0=substr($dateTimeText0,0,5);
	}
	elseif($dateformat=='d-m-Y')
	{ if(substr($dateTimeText0,-7)==substr($dateTimeText1,-7))
	    $dateTimeText0=substr($dateTimeText0,0,2);
	  elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
	    $dateTimeText0=substr($dateTimeText0,0,5);
	}
	elseif($dateformat=='M-d-Y')
	{ if(substr($dateTimeText0,0,3)==substr($dateTimeText1,0,3))
	  { $dateTimeText0=substr($dateTimeText0,0,6);
	    $dateTimeText1=substr($dateTimeText1,-7);
	  }
	  elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
	    $dateTimeText0=substr($dateTimeText0,0,6);
	}

	$moondata = phase(strtotime($date . ' ' . $time . ' ' . $tzone));
	$MoonIllum  = $moondata[1];
	$MoonAge    = $moondata[2];
	$nextNewMoonText=LangMoonMenuNewMoon.": ";
	$phases = array();
	$phases = phasehunt(strtotime($date));
	$nextNewMoonText.=date("j M", $phases[4]);

	// Convert $MoonIllum to percent and round to whole percent.
	$MoonIllum = round( $MoonIllum, 2 );
	$MoonIllum *= 100;
	// 1) Check if logged in
	if($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation'))
	{ // 2) Get the julian day of today...
	  $jd = gregoriantojd($theMonth, $theDay, $theYear);
	  // 3) Get the standard location of the observer
	  $longitude = $objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'), 'longitude');
	  $latitude = $objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'), 'latitude');
	  if((!($objUtil->checkSessionKey('efemerides'))) || ($_SESSION['efemerides']['base']!=$jd."/".$longitude."/".$latitude))
	  { if ($longitude > -199)
	    { $timezone=$objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'),'timezone');
	      $dateTimeZone=new DateTimeZone($timezone);
	      $datestr=sprintf("%02d",$_SESSION['globalMonth'])."/".sprintf("%02d",$_SESSION['globalDay'])."/".$_SESSION['globalYear'];
	      $dateTime = new DateTime($datestr, $dateTimeZone);
	      // Geeft tijdsverschil terug in seconden
	      $timedifference = $dateTimeZone->getOffset($dateTime);
	      $timedifference = $timedifference / 3600.0;
	      if (strncmp($timezone, "Etc/GMT", 7)==0)
	        $timedifference = -$timedifference;
	      // Calculate the rise and set time of the moon
	      $moon = $objAstroCalc->calculateMoonRiseTransitSettingTime($jd, $longitude, $latitude, $timedifference);
	      // SUNRISE and SET, TWILIGHT...
	      date_default_timezone_set ("UTC");
	      $timestr = $theYear . "-" . $theMonth . "-" . $theDay;
	      $sun_info = date_sun_info(strtotime($timestr), $latitude, $longitude);
	      $srise = $sun_info["sunrise"];
	      if ($srise > 1)
	        $srise = date("H:i", $srise + $timedifference * 60 * 60);
	       else
	        $srise = "-";
	      $sset = $sun_info["sunset"];
	      if ($sset > 1)
	        $sset = date("H:i", $sset + $timedifference * 60 * 60);
	      else
	        $sset = "-";
	      $nautb = $sun_info["nautical_twilight_begin"];
	      if ($nautb > 1)
	        $nautb = date("H:i", $nautb + $timedifference * 60 * 60);
	      else
	        $nautb = "-";
	      $naute = $sun_info["nautical_twilight_end"];
	      if ($naute > 1)
	        $naute = date("H:i", $naute + $timedifference * 60 * 60);
	      else
	        $naute = "-";
	      $astrob = $sun_info["astronomical_twilight_begin"];
	      if ($astrob > 1)
	        $astrob = date("H:i", $astrob + $timedifference * 60 * 60);
	      else
	        $astrob = "-";
	      $astroe = $sun_info["astronomical_twilight_end"];
	      if ($astroe > 1)
	        $astroe = date("H:i", $astroe + $timedifference * 60 * 60);
	      else
	        $astroe = "-";
	      $_SESSION['efemerides']['base']=$jd."/".$longitude."/".$latitude;
	      $_SESSION['efemerides']['astrob']=$astrob;
	      $_SESSION['efemerides']['astroe']=$astroe;
	      $_SESSION['efemerides']['nautb']=$nautb;
	      $_SESSION['efemerides']['naute']=$naute;
	      $_SESSION['efemerides']['srise']=$srise;
	      $_SESSION['efemerides']['sset']=$sset;
	      $_SESSION['efemerides']['moon0']=$moon[0];
	      $_SESSION['efemerides']['moon2']=$moon[2];
	    }
	  }
	}

	echo "<li>";
	reset($_GET);
	$link="";
	while(list($key,$value)=each($_GET))
	  if($key!="menuMoon")
	    $link.="&amp;".$key."=".urlencode($value);
	reset($_GET);
	echo "<p><br /><h4>";
	echo (($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation'))?LangMoonSunMenuTitle:LangMoonMenuTitle)."<br />";
  echo "</h4>";
	echo"<span style=\"font-weight:normal;\">".LangOn." ".$dateTimeText0."&gt;&lt;".$dateTimeText1."</span>";
	echo "</p>";
	if($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation'))
	{ echo "<table class=\"table table-condensed\">";
    if (isset($_SESSION['efemerides'])) {
	    echo "<tr>";
	    echo "<td>".LangMoon."</td>"."<td>".$_SESSION['efemerides']['moon0']."</td>"."<td>".$_SESSION['efemerides']['moon2']."</td>";
	    echo "</tr>";
	    echo "<tr>";
	    echo "<td>".LangMoonSun."</td>"."<td>".$_SESSION['efemerides']['sset']."</td>"."<td>".$_SESSION['efemerides']['srise']."</td>";
	    echo "</tr>";
	    echo "<tr>";
	    echo "<td>".LangMoonNaut."</td>"."<td>".$_SESSION['efemerides']['naute']."</td>"."<td>".$_SESSION['efemerides']['nautb']."</td>";
	    echo "</tr>";
	    echo "<tr>";
	    echo "<td>".LangMoonAstro."</td>"."<td>".$_SESSION['efemerides']['astroe']."</td>"."<td>".$_SESSION['efemerides']['astrob']."</td>";
	    echo "</tr>";
    }
	  echo "</table>";
	}
	$file = "m" . round(($MoonAge / SYNMONTH) * 40) . ".gif";
	echo "<p><img src=\"".$baseURL."/lib/moonpics/" . $file . "\" title=\"" . $MoonIllum . "%\" height=\"100\%\" width=\"100\%\" alt=\"" . $MoonIllum . "%\" /></p>";
	echo $nextNewMoonText."<br />";
	echo "</li>";
}
?>
