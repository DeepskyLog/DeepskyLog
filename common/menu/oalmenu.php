<?php 
// oalmenu.php
// dispalys a site counter

menu_aol();

function menu_aol()
{ global $baseURL;
	echo "<div class=\"menuDiv\">";
	echo "<p  class=\"menuHead\">"."OpenAstronomyLog"."</p>";
	echo "<p class=\"centered\">";
	echo "<a class=\"oal\" href=\"http://groups.google.com/group/openastronomylog\" rel=\"external\">";
	echo "<img class=\"oal\" src=\"".$baseURL."styles/images/oallogo_small.jpg\" alt=\"OAL\"/>";
	echo "</a>";
	echo "</p>";
	echo "</div>";
}
?>