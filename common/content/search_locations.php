<?php

// search_location.php
// allows the user to search a location in the database 

// Code cleanup - removed by David on 20080704
//include_once "../lib/observers.php";

include_once "../lib/locations.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

echo("<div id=\"main\">\n
   <h2>Search location</h2>
   <form action=\"common/site_result.php\" method=\"post\">
   <table>
<tr><td colspan=\"3\">   
<ol>
   <li value=\"1\">".LangSearchLocations1."</li>
</ol>
</td>
</tr>
<tr><td class=\"fieldname\">".LangSearchLocations2."</td><td>");

$locations = new Locations;
 
echo("<select name=\"country\">");
 
      $countries = $locations->getDatabaseCountries();

      while(list ($key, $value) = each($countries))
      {
      echo("<option>$value</option>\n");
      }
print("
   </select>
   </td>
   <td class=\"explanation\">".LangSearchLocations3."
   </td>
   </tr>
<tr><td colspan=\"3\">
<ol>
   <li value=\"2\">".LangSearchLocations4."</li>
</ol>
   </td>
   </tr>
   <tr>
   <td class=\"fieldname\">".LangSearchLocations5."</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"location_name\" size=\"30\" value=\"\" /></td>
   <td class=\"explanation\">".LangSearchLocations6."
   </td>
   </tr>
   <tr>
   <td></td>
   <td><input type=\"submit\" name=\"search\" value=\"Search\" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div></body></html>");

?>
