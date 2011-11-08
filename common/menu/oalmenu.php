<?php 
// oalmenu.php
// dispalys the OAL logo

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_aol();

function menu_aol()
{ global $baseURL;
	echo "<div class=\"menuDiv\">";
	echo "<p  class=\"menuHead\">"."OpenAstronomyLog"."</p>";
	echo "<p class=\"centered\">";
	echo "<a class=\"oal\" href=\"http://groups.google.com/group/openastronomylog\" rel=\"external\">";
	echo "<img class=\"oal\" src=\"".$baseURL."styles/images/oallogo_small.jpg\" alt=\"OAL\"/>";
	echo "</a>";
	echo "</p>";
    echo "<a href=\"https://plus.google.com/105963409869875462537/?prsrc=3\" style=\"text-decoration: none; color: #333;\"><div style=\"display: inline-block;\"><span style=\"float: left; font: bold 13px/16px arial,sans-serif; margin-right: 4px;\">DeepskyLog</span><span style=\"float: left; font: 13px/16px arial,sans-serif; margin-right: 11px;\">on</span><div style=\"float: left;\"><img src=\"https://ssl.gstatic.com/images/icons/gplus-16.png\" width=\"16\" height=\"16\" style=\"border: 0;\"/></div><div style=\"clear: both\"></div></div></a>";
	echo "</div>";
}
?>
