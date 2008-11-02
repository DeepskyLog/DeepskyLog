<?php

// getLocation.php
// prints the locations looked up into the database 
// version 0.2: JV, 20050130

// Code cleanup - removed by David on 20080704
//include_once "../lib/observers.php";

include_once "../common/control/dec_to_dm.php";
include_once "../lib/locations.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$locations = new Locations;

echo("<div id=\"main\">
   \n<h2>".LangGetLocation1."</h2>");

  $count = 0;
 
  $result = $locations->getLocationsFromDatabase($_POST['location_name'],$_POST['country']);
  
  if ($result != "" && $_POST['location_name'] != "") // found
  {

  echo("<div class=\"results\">".LangGetLocation2."</div>");
  echo "<p><table>
         <tr class=\"type3\">
          <td>".LangGetLocation3."</td>
          <td>".LangGetLocation4."</td>
          <td>".LangGetLocation5."</td>
          <td>".LangGetLocation6."</td>
          <td>".LangGetLocation7."</td>
         </tr>";
 
  while(list ($key, $value) = each($result))
  {
     $vars = explode("\t", $value);

   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }
 
   echo "<tr $type><td> <a href=\"common/add_site.php?sitename=$vars[0]&amp;longitude=$vars[1]&amp;latitude=$vars[2]&amp;region=$vars[4]&amp;country=$vars[3]\">$vars[0]</a> </td><td>". decToString($vars[1], 1) ."</td><td>". decToString($vars[2], 1) ."</td><td> $vars[4] </td><td> $vars[3] </td></tr>";

   $count++;
  }
  echo "</table></p>";
  }
  else
  {
  echo("<p>" . LangGetLocation8 . "</p>");
  }

echo ("</div>
</div></body></html>");

?>
