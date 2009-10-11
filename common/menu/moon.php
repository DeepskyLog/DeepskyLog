<?php // moon.php - menu which shows the moon phase
echo "<div class=\"menuDiv\">";
echo "<p  class=\"menuHead\">";
echo LangMoonMenuTitle."</p>";
if($menuMoon=="collapsed") {
  // Only show the current moon phase
  include_once "lib/moonphase.inc.php";

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
  echo "<span class=\"menuText\">".LangMoonMenuActualMoon."</span>&nbsp;"."<img src=\"".$baseURL."/lib/moonpics/" . $file . "\" style=\"vertical-align:top;\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" /><br />";
  
}
echo "</div>";
?>
