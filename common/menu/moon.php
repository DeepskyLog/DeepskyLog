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

  // Convert $MoonIllum to percent and round to whole percent.
  $MoonIllum = round( $MoonIllum, 2 );
  $MoonIllum *= 100;

  $file = "m" . round(($MoonAge / SYNMONTH) * 40) . ".gif";
  print "<img src=\"".$baseURL."/lib/moonpics/" . $file . "\" title = " . $MoonIllum . "% />";

  print "&nbsp;" . LangMoonMenuNewMoon . " : ";

  $phases = array();
  $phases = phasehunt();
  print date("j M Y", $phases[4]) . "\n";
}
echo "</div>";
?>
