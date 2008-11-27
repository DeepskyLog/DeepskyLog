<?php
// search_location.php
// allows the user to search a location in the database 

echo "<div id=\"main\">";
echo "<h2>" . LangSearchLocations0 . "</h2>";
echo "<form action=\"".$baseURL."index.php?indexAction=site_result\" method=\"post\">";
echo "<table>";
echo "<tr>";
echo "<td colspan=\"3\">";
echo "<ol>";
echo "<li value=\"1\">".LangSearchLocations1."</li>";
echo "</ol>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangSearchLocations2."</td>";
echo "<td>";
echo "<select name=\"country\">";
$countries = $objLocation->getDatabaseCountries();
while(list ($key, $value) = each($countries))
  echo "<option>".$value."</option>";
echo "</select>";
echo "</td>";
echo "<td class=\"explanation\">".LangSearchLocations3."</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan=\"3\">";
echo "<ol>";
echo "<li value=\"2\">".LangSearchLocations4."</li>";
echo "</ol>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangSearchLocations5."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"location_name\" size=\"30\" value=\"\" />";
echo "</td>";
echo "<td class=\"explanation\">".LangSearchLocations6."</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td>";
echo "<input type=\"submit\" name=\"search\" value=\"" . LangSearchLocations7 . "\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
