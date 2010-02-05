<?php // moon.php - menu which shows the moon phase
echo "<div class=\"menuDiv\">";
reset($_GET);
$link="";
while(list($key,$value)=each($_GET))
  if($key!="menuMoon")
    $link.="&amp;".$key."=".urlencode($value);
reset($_GET);
echo "<p  class=\"menuHead\">";
if($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation')) {
  if($menuMoon=="collapsed")
    echo "<a href=\"".$baseURL."index.php?menuMoon=expanded".$link."\" title=\"".LangMenuExpand."\">+</a> ";
  else
    echo "<a href=\"".$baseURL."index.php?menuMoon=collapsed".$link."\" title=\"".LangMenuCollapse."\">-</a> ";
}
echo LangMoonMenuTitle."</p>";

// Only show the current moon phase
include_once "lib/moonphase.inc.php";
include_once "lib/astrocalc.php";
  
$today=date('Ymd',strtotime('today'));
$theYear=substr($today,0,4);
$theMonth=substr($today,4,2);
$theDay=substr($today,6,2);
$theHour="";
$theMinute="";
$date = $theYear . "-". $theMonth . "-" . $theDay;
$time = "12:19:00";
$tzone = "GMT";
  
$moondata = phase(strtotime($date . ' ' . $time . ' ' . $tzone));

$MoonIllum  = $moondata[1];
$MoonAge    = $moondata[2];
$nextNewMoonText=LangMoonMenuNewMoon." : ";
$phases = array();
$phases = phasehunt();
$nextNewMoonText.=date("j M", $phases[4]);
  
// Convert $MoonIllum to percent and round to whole percent.
$MoonIllum = round( $MoonIllum, 2 );
$MoonIllum *= 100;

$file = "m" . round(($MoonAge / SYNMONTH) * 40) . ".gif";
echo "<span class=\"menuText\">".$nextNewMoonText."</span><br />";
echo "<span class=\"menuText\">".LangMoonMenuActualMoon."</span>&nbsp;"."<img src=\"".$baseURL."/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" /><br />";

if($menuMoon!="collapsed") {
  // 1) Check if logged in
  if($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation')) {
    // 2) Get the julian day of today...
    $jd = gregoriantojd($theMonth, $theDay, $theYear);
    
    // 3) Get the standard location of the observer
    $longitude = $objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'), 'longitude');
    $latitude = $objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'), 'latitude');

    if ($longitude > -199) {
      $timezone=$objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'),'timezone');

      $dateTimeZone=new DateTimeZone($timezone);
      $datestr=sprintf("%02d",$date[1])."/".sprintf("%02d",$date[2])."/".$date[0];
      $dateTime = new DateTime($datestr, $dateTimeZone);
      // Geeft tijdsverschil terug in seconden
      $timedifference = $dateTimeZone->getOffset($dateTime);
      $timedifference = $timedifference / 3600.0;
      if (strncmp($timezone, "Etc/GMT", 7) == 0) {
        $timedifference = -$timedifference;
      }

      // Calculate the rise and set time of the moon
      $moon = $objAstroCalc->calculateMoonRiseTransitSettingTime($jd, $longitude, $latitude);
      echo "<span class=\"menuText\">".LangMoonRise." : ";
      if ($moon[0] > 24.0) {
        $moonNew = ($objAstroCalc->calculateMoonRiseTransitSettingTime($jd + 1, $longitude, $latitude));
        $moon[0] = $moonNew[0];
      }
      if ($moon[0] > 24.0) {
        echo "-<br />";
      } else {
        $moon[0] = $moon[0] + $timedifference;
        if ($moon[0] < 0) {
          $moon[0] = $moon[0] + 24;
        }
        if ($moon[0] > 24) {
          $moon[0] = $moon[0] - 24;
        }
        $minutes = round(($moon[0] - floor($moon[0])) * 60);
        if ($minutes < 10) {
          $minutes = "0" . $minutes;
        }
        echo floor($moon[0]) . ":" . $minutes . "<br />";
      }
      echo LangMoonSet." : " ;
      if ($moon[2] > 24.0) {
        $moonNew = ($objAstroCalc->calculateMoonRiseTransitSettingTime($jd + 1, $longitude, $latitude));
        $moon[2] = $moonNew[2];
      }
      if ($moon[2] > 24.0) {
        echo "-<br />";
      } else {
        $moon[2] = $moon[2] + $timedifference;

        $minutes = round(($moon[2] - floor($moon[2])) * 60);
        if ($minutes < 10) {
          $minutes = "0" . $minutes;
        }
        echo floor($moon[2]) . ":" . $minutes;
      }
    
      // SUNRISE and SET, TWILIGHT...
      date_default_timezone_set ("UTC");
      $timestr = $theYear . "-" . $theMonth . "-" . $theDay;

      $sun_info = date_sun_info(strtotime($timestr), $latitude, $longitude);
    
      $srise = $sun_info["sunrise"] + $timedifference * 60 * 60;
      if ($srise > 1) {
        $srise = date("H:i", $srise);
      } else {
        $srise = "-";
      }
      
      $sset = $sun_info["sunset"] + $timedifference * 60 * 60;
      if ($sset > 1) {
        $sset = date("H:i", $sset);
      } else {
        $sset = "-";
      }

      $nautb = $sun_info["nautical_twilight_begin"] + $timedifference * 60 * 60;
      if ($nautb > 1) {
        $nautb = date("H:i", $nautb);
      } else {
        $nautb = "-";
      }

      $naute = $sun_info["nautical_twilight_end"] + $timedifference * 60 * 60;
      if ($naute > 1) {
        $naute = date("H:i", $naute);
      } else {
        $naute = "-";
      }
      
      $astrob = $sun_info["astronomical_twilight_begin"] + $timedifference * 60 * 60;
      if ($astrob > 1) {
        $astrob = date("H:i", $astrob);
      } else {
        $astrob = "-";
      }

      $astroe = $sun_info["astronomical_twilight_end"] + $timedifference * 60 * 60;
      if ($astroe > 1) {
        $astroe = date("H:i", $astroe);
      } else {
        $astroe = "-";
      }

      print("<br />" . LangMoonSun . " : " . $srise . " - " . $sset);
      print("<br />" . LangMoonTwilight . " : ");
      print("<br />&nbsp;&nbsp;" . LangMoonNaut . " : " . $nautb . " - " . $naute);
      print("<br />&nbsp;&nbsp;" . LangMoonAstro . " : " . $astrob . " - " . $astroe);
    }
    echo "</span><br />";
  }
}
echo "</div>";
?>
