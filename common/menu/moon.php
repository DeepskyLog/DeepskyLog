<?php // moon.php - menu which shows the moon phase
echo "<div class=\"menuDiv\">";
echo "<p  class=\"menuHead\">";
echo LangMoonMenuTitle."</p>";
if($menuMoon=="collapsed") {
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
  echo "<span class=\"menuText\">".$nextNewMoonText."</span><br /><br />";
  echo "<span class=\"menuText\">".LangMoonMenuActualMoon."</span>&nbsp;"."<img src=\"".$baseURL."/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" /><br />";
  
  // 1) Check if logged in
  if($loggedUser&&$objObserver->getObserverProperty($loggedUser, 'stdLocation')) {
    // 2) Get the julian day of today...
    $jd = gregoriantojd($theMonth, $theDay, $theYear);
    
    // 3) Get the standard location of the observer
    $longitude = $objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'), 'longitude');
    $latitude = $objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser, 'stdLocation'), 'latitude');
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
    $moon[0] = $moon[0] + $timedifference;
    if ($moon[0] > 24.0) {
      echo "-";
    } else {
      $minutes = round(($moon[0] - floor($moon[0])) * 60);
      if ($minutes < 10) {
        $minutes = "0" . $minutes;
      }
      echo floor($moon[0]) . ":" . $minutes . ", ";
    }
    echo LangMoonSet." : " ;
    $moon[2] = $moon[2] + $timedifference;
    if ($moon[2] > 24.0) {
      echo "-";
    } else {
      $minutes = round(($moon[0] - floor($moon[0])) * 60);
      if ($minutes < 10) {
        $minutes = "0" . $minutes;
      }
      echo floor($moon[2]) . ":" . $minutes;
    }
    echo "</span><br />";
  }
}
echo "</div>";
?>
