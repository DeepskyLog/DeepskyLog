<?php

// view_location.php
// view information of location 

include_once "../lib/locations.php"; // location table
$locations = new Locations;

include_once "control/dec_to_dm.php"; 
include_once "../lib/util.php";
include_once "../lib/contrast.php";

$util = new Util();
$util->checkUserInput();

if(!$_GET['location']) // no location defined 
{
   header("Location: ../index.php");
}  

echo("<div id=\"main\">\n<h2>".LangViewLocationTitle2."</h2><table width=\"490\">\n
<form action=\"common/adapt_site.php?location=" . $_GET['location'] . "\" method=\"post\">
<tr>\n
<td class=\"fieldname\">\n");

echo(LangViewLocationLocation);

echo("</td>\n<td>\n");

echo(stripslashes($locations->getLocationName($_GET['location'])));

echo("</td></tr>");

echo("<tr><td class=\"fieldname\">");

echo(LangViewLocationProvince);

echo("</td><td>");

echo(stripslashes($locations->getRegion($_GET['location'])));

print("</td></tr>");

echo("<tr><td class=\"fieldname\">");

echo(LangViewLocationCountry);

echo("</td><td>");

echo($locations->getCountry($_GET['location']));

print("</td></tr><tr><td class=\"fieldname\">");

echo(LangViewLocationLongitude);

echo("</td><td>");

echo(decToTrimmedString($locations->getLongitude($_GET['location'])));

print("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangViewLocationLatitude);

echo("</td>
   <td>");

echo(decToTrimmedString($locations->getLatitude($_GET['location'])));

print("</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddSiteField6);

echo("</td>
   <td>");

$timezone = $locations->getTimezone($_GET['location']);

echo($timezone);

echo("</td></tr>");

$lm = $locations->getLocationLimitingMagnitude($_GET['location']);
$sb = $locations->getSkyBackground($_GET['location']);

if ($lm > -900 || $sb > -900)
{
 if ($lm > -900)
 {
   $c = new Contrast();
   $sb = $c->calculateSkyBackgroundFromLimitingMagnitude($lm);
 } else {
   $c = new Contrast();
   $lm = $c->calculateLimitingMagnitudeFromSkyBackground($sb);
 }

 echo("<tr>
      <td class=\"fieldname\">");

 echo(LangAddSiteField7);

 echo("</td>
       <td>");

 echo(sprintf("%.1f", $lm));

 echo("</td></tr>");

 echo("<tr>
      <td class=\"fieldname\">");

 echo(LangAddSiteField8);

 echo("</td>
       <td>");

 echo(sprintf("%.2f", $sb));

 echo("</td></tr>");
}

if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($locations->getObserverFromLocation($_GET['location']) == $_SESSION['deepskylog_id']))
{
  echo("<tr>
         <td class=\"fieldname\"><input type=\"submit\" name=\"change\" value=\"");

  echo (LangAddSiteButton2);

  echo("\" />");
  echo("</td></tr>");
}

echo("<tr>
   <td class=\"fieldname\">");

echo "</td>
   <td>
   </td>
   </tr>
   <tr>
   <td colspan=\"2\"><br></br>
   <a href=\"http://maps.google.com/maps?ll=" . $locations->getLatitude($_GET['location']) . "," . $locations->getLongitude($_GET['location']) . "&spn=4.884785,11.585083&t=h&hl=en\"><img class=\"account\" src=\"common/content/map.php?lat=" . $locations->getLatitude($_GET['location']) . "&long=" . $locations->getLongitude($_GET['location']) . "\" width=\"490\" height=\"245\" title=\"";

echo (LangGooglemaps);

echo "\"></a>
   </td>
   </tr>";

echo "</table></form>

</div></div></body></html>";

?>
