<?php

// overview_locations.php
// generates an overview of all locations (admin only)
// version 0.5: JV 20050212

// problems still to solve
// every page should contain the same number of locations
// this is not the case now as we need to remove the empty location manually

include_once "../common/control/dec_to_dm.php";
include_once "../lib/locations.php";
include_once "../lib/util.php";
include_once "../lib/observations.php";
include_once "../lib/observers.php";
include_once "../lib/cometobservations.php";

$locations = new locations;
$util = new util;
$util->checkUserInput();
$observations = new observations;
$cometobservations = new CometObservations;

$observers = new observers;

// sort

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
  $sort = "name"; // standard sort on location name
}

$sites = $locations->getSortedLocations($sort);
$locs = $observers->getListOfLocations();

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

// the code below looks very strange but it works

if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $sites = array_reverse($sites, true);
  }
  else
  {
    krsort($sites);
    reset($sites);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangViewLocationTitle."</h2>");

$link = "common/view_locations.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($sites, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"common/view_locations.php?sort=name&amp;previous=$previous\">".LangViewLocationLocation."</a></td>
      <td><a href=\"common/view_locations.php?sort=region&amp;previous=$previous\">".LangViewLocationProvince."</a></td>
      <td><a href=\"common/view_locations.php?sort=country&amp;previous=$previous\">".LangViewLocationCountry."</a></td>";

echo "<td><a href=\"common/view_locations.php?sort=longitude&amp;previous=$previous\">".LangViewLocationLongitude."</a></td>";

echo "<td><a href=\"common/view_locations.php?sort=latitude&amp;previous=$previous\">".LangViewLocationLatitude."</a></td>";
echo "<td><a href=\"common/view_locations.php?sort=timezone&amp;previous=$previous\">".LangAddSiteField6."</a></td>";
echo "<td><a href=\"common/view_locations.php?sort=limitingMagnitude&amp;previous=$previous\">".LangViewLocationLimMag."</a></td>";
echo "<td><a href=\"common/view_locations.php?sort=skyBackground&amp;previous=$previous\">".LangViewLocationSB."</a></td>";
echo "<td><a href=\"common/view_locations.php?sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";
echo "</tr>";

$count = 0;

while(list ($key, $value) = each($sites))
{
 if($count >= $min && $count < $max) // selection
 {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $sitename = stripslashes($locations->getLocationName($value));
   $region = stripslashes($locations->getRegion($value));
   $country = $locations->getCountry($value);
   if($locations->getLongitude($value) > 0)
   {
      $longitude = "&nbsp;" . decToString($locations->getLongitude($value));
   }
   else
   {
      $longitude = decToString($locations->getLongitude($value));
   }
   if($locations->getLatitude($value) > 0)
   {
      $latitude = "&nbsp;" . decToString($locations->getLatitude($value));
   }
   else
   {
      $latitude = decToString($locations->getLatitude($value));
   }
   $timezone = $locations->getTimezone($value);
   $observer = $locations->getObserverFromLocation($value);
   $limmag = $locations->getLocationLimitingMagnitude($value);
   if ($limmag < -900)
   {
     $limmag = "&nbsp;";
   }
   $sb = $locations->getSkyBackground($value);
   if ($sb < -900)
   {
     $sb = "&nbsp;";
   }

   if ($value != "1")
   {
    print("<tr $type>
           <td><a href=\"common/adapt_site.php?location=$value\">$sitename</a></td>\n
           <td>$region</td>\n
           <td>$country</td>\n
            <td>");
           echo ($longitude);
           echo("</td><td>");
           echo ($latitude);
           echo("</td><td>");
           echo ($timezone);
           echo("</td><td>");
           echo ($limmag);
           echo("</td><td>");
           echo ($sb);
           echo("</td><td>");
           echo ($observer);
           echo("</td>\n<td>");

           // check if there are no observations made from this location

           $queries = array("location" => $value);
           $obs = $observations->getObservationFromQuery($queries, "", "1", "False");

    //       $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

           if(!sizeof($obs) > 0 && !in_array($value, $locs)) // && !sizeof($comobs) > 0) // no observations from location yet
           {
              echo("<a href=\"common/control/validate_delete_location.php?locationid=" . $value . "\">" . LangRemove . "</a>");
           }

           echo("</td>\n</tr>");

   }
 }
   $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($sites, $link, $min, $step, "");

  echo "</div></div></body></html>";
?>
