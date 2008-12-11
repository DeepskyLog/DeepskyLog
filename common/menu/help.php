<?php
// help.php

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
if ($_SESSION['lang'] == "nl")
{ echo"<tr>";
	echo "<th valign=\"top\">Help</th>";
	echo "</tr><tr>";
	echo "<td valign=\"top\">";
	echo "<a href=\"http://www.deepskylog.org/wiki/bin/view/Main/DeepskyLogManualNL\" target=\"_blank\">Handleiding</a>";
	echo "</td>";
	echo "</tr>";
}

echo "</table>";
?>
