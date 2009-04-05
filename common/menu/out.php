<?php
// out.php
// menu which allows the user to logout from deepskylog

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">";
echo LangLogoutMenuTitle;
echo "</th>";
echo "</tr>";
echo "<tr align=\"left\">";
echo "<td>";
echo "<a href=\"".$baseURL."index.php?indexAction=logout\" class=\"mainlevel\">".LangLogoutMenuItem1."</a>";
echo "</td>";
echo "</tr>";
echo "</table>";
?>
