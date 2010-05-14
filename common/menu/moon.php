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
  
/*$theYear=$_SESSION['globalYear'];
$theMonth=$_SESSION['globalMonth'];
$theDay=$_SESSION['globalDay'];
$theHour="";
$theMinute="";
*/
//temp suggestion to allow trunk to work for some testing by david
$theYear=$objUtil->checkSessionKey('globalYear',date("Y"));
$theMonth=$objUtil->checkSessionKey('globalMonth',date("n"));
$theDay=$objUtil->checkSessionKey('globalDay',date('j'));
$theHour="";
$theMinute="";

$date = $theYear . "-". $theMonth . "-" . $theDay;
$time = "23:59:59";
$tzone = "GMT";
$dateTimeText=date($dateformat, mktime(0, 0, 0, $theMonth, $theDay, $theYear));

$moondata = phase(strtotime($date . ' ' . $time . ' ' . $tzone));

$MoonIllum  = $moondata[1];
$MoonAge    = $moondata[2];
$nextNewMoonText=LangMoonMenuNewMoon." : ";
$phases = array();
$phases = phasehunt(strtotime($date));
$nextNewMoonText.=date("j M", $phases[4]);
  
// Convert $MoonIllum to percent and round to whole percent.
$MoonIllum = round( $MoonIllum, 2 );
$MoonIllum *= 100;

$file = "m" . round(($MoonAge / SYNMONTH) * 40) . ".gif";
echo "<span class=\"menuText\">".$nextNewMoonText."</span><br />";

echo "<span class=\"menuText\">".$dateTimeText."</span>&nbsp;"."<img src=\"".$baseURL."/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" /><br />";

if($menuMoon!="collapsed") {
  // 1) Check if logged in
  if($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation')) {
    // 2) Get the julian day of today...
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
	      if (strncmp($timezone, "Etc/GMT", 7) == 0) {
	        $timedifference = -$timedifference;
	      }
	
	      // Calculate the rise and set time of the moon
	      $moon = $objAstroCalc->calculateMoonRiseTransitSettingTime($jd, $longitude, $latitude, $timedifference);
	
	      // SUNRISE and SET, TWILIGHT...
	      date_default_timezone_set ("UTC");
	      $timestr = $theYear . "-" . $theMonth . "-" . $theDay;
	
	      $sun_info = date_sun_info(strtotime($timestr), $latitude, $longitude);
	    
	      $srise = $sun_info["sunrise"];
	      if ($srise > 1) {
	        $srise = date("H:i", $srise + $timedifference * 60 * 60);
	      } else {
	        $srise = "-";
	      }
	      
	      $sset = $sun_info["sunset"];
	      if ($sset > 1) {
	        $sset = date("H:i", $sset + $timedifference * 60 * 60);
	      } else {
	        $sset = "-";
	      }
	
	      $nautb = $sun_info["nautical_twilight_begin"];
	      if ($nautb > 1) {
	        $nautb = date("H:i", $nautb + $timedifference * 60 * 60);
	      } else {
	        $nautb = "-";
	      }
	
	      $naute = $sun_info["nautical_twilight_end"];
	      if ($naute > 1) {
	        $naute = date("H:i", $naute + $timedifference * 60 * 60);
	      } else {
	        $naute = "-";
	      }
	      
	      $astrob = $sun_info["astronomical_twilight_begin"];
	      if ($astrob > 1) {
	        $astrob = date("H:i", $astrob + $timedifference * 60 * 60);
	      } else {
	        $astrob = "-";
	      }
	
	      $astroe = $sun_info["astronomical_twilight_end"];
	      if ($astroe > 1) {
	        $astroe = date("H:i", $astroe + $timedifference * 60 * 60);
	      } else {
	        $astroe = "-";
	      }
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
	  echo "<span class=\"menuText\">".LangMoonRise." : " . $_SESSION['efemerides']['moon0'] . "<br />";
	  // Setting of the moon
	  echo LangMoonSet." : " . $_SESSION['efemerides']['moon2'] . "<br />";
    echo LangMoonSun . " : " . $_SESSION['efemerides']['srise'] . " - " . $_SESSION['efemerides']['sset'];
    echo "<br />" . LangMoonTwilight . " : ";
    echo "<br />&nbsp;&nbsp;" . LangMoonNaut . " : " .  $_SESSION['efemerides']['nautb'] . " - " . $_SESSION['efemerides']['naute'];
    echo "<br />&nbsp;&nbsp;" . LangMoonAstro . " : " . $_SESSION['efemerides']['astrob'] . " - " . $_SESSION['efemerides']['astroe'];
    echo "</span><br />";
  }
}
echo "</div>";
?>
