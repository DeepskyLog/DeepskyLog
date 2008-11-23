<?php
// out.php
// menu which allows the user to logout from deepskylog

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">";
echo LangLogoutMenuTitle;
echo "</th>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr align=\"left\">";
echo "<td>";
echo "<a href=\"". $baseURL . "/".$_SESSION['module']."/index.php?indexAction=logout\" class=\"mainlevel\">".LangLogoutMenuItem1."</a>";
echo "</td>";
echo "</tr>";
?>
