<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
if ($_SESSION['module'] == "deepsky")
  echo "<th valign=\"top\">".LangMailtoTitle."</th>"; 
echo "</tr>";
echo "<tr>";
echo "<td valign=\"top\" height=\"120\">";
if ($_SESSION['module'] == "deepsky")
  echo LangMailtoLink;
echo "</td>";
echo "</tr>";
echo "</table>"
?>