<?php
// overview_locations.php
// generates an overview of all locations (admin only)

$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
$sites = $objLocation->getSortedLocations($sort);
$locs = $objObserver->getListOfLocations();
// the code below looks very strange but it works
if((isset($_GET['previous'])))
 $orig_previous = $_GET['previous'];
else
 $orig_previous = "";
if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{if ($_GET['sort'] == "name")
   $sites = array_reverse($sites, true);
  else
  { krsort($sites);
    reset($sites);
  }
  $previous = ""; 
}
else
  $previous = $sort;

$step = 25;
echo "<div id=\"main\">";
echo "<h2>".LangViewLocationTitle."</h2>";
$link=$baseURL."index.php?indexAction=view_locations&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;
list($min, $max) = $objUtil->printListHeader($sites, $link, $min, $step, "");
echo "<table>";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=name&amp;previous=$previous\">".LangViewLocationLocation."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=region&amp;previous=$previous\">".LangViewLocationProvince."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=country&amp;previous=$previous\">".LangViewLocationCountry."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=longitude&amp;previous=$previous\">".LangViewLocationLongitude."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=latitude&amp;previous=$previous\">".LangViewLocationLatitude."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=timezone&amp;previous=$previous\">".LangAddSiteField6."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=limitingMagnitude&amp;previous=$previous\">".LangViewLocationLimMag."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=skyBackground&amp;previous=$previous\">".LangViewLocationSB."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_locations&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
while(list ($key, $value) = each($sites))
{ if($count >= $min && $count < $max) // selection
  { $sitename = stripslashes($objLocation->getLocationPropertyFromId($value,'name'));
    $region = stripslashes($objLocation->getLocationPropertyFromId($value,'region'));
    $country = $objLocation->getLocationPropertyFromId($value,'country');
    if($objLocation->getLocationPropertyFromId($value'longitude') > 0)
      $longitude = "&nbsp;" . decToString($objLocation->getLocationPropertyFromId($value,'longitude'));
    else
     $longitude = decToString($objLocation->getLocationPropertyFromId($value,'longitude'));
    if($objLocation->getLocationPropertyFromId($value,'latitude') > 0)
      $latitude = "&nbsp;" . decToString($objLocation->getLocationPropertyFromId($value,'latitude'));
    else
      $latitude = decToString($objLocation->getLocationPropertyFromId($value,'latitude'));
    $timezone = $objLocation->getLocationPropertyFromId($value,'timezone');
    $observer = $objLocation->getLocationPropertyFromId($value,'observer');
    $limmag = $objLocation->getLocationPropertyFromId($value,'limitingMagnitude');
    if ($limmag < -900)
      $limmag = "&nbsp;";
    $sb = $objLocation->getLocationPropertyFromId($value,'skyBackground');
    if ($sb < -900)
      $sb = "&nbsp;
    if($value!= "1")
    { echo "<tr class=\"type".(2-($count%2))."\">";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_site&amp;location=".urlencode($value)."\">$sitename</a></td>";
      echo "<td>".$region."</td>";
      echo "<td>".$country."</td>";
      echo "<td>".$longitude."</td>";
			echo "<td>".$latitude."</td>";
			echo "<td>".$timezone."</td>";
			echo "<td>".$limmag."</td>";
			echo "<td>".$sb."</td>";
			echo "<td>".$observer."</td>";
      // check if there are no observations made from this location
      $queries = array("location" => $value);
      $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");
      // $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
      echo "<td>";
      if(!sizeof($obs) > 0 && !in_array($value, $locs)) // && !sizeof($comobs) > 0) // no observations from location yet
        echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_location&amp;locationid=" . urlencode($value) . "\">" . LangRemove . "</a>");
      echo "</td>";
			echo "</tr>";
    }
  }
  $count++;
}
echo "</table>";
list($min, $max) = $objUtil->printNewListHeader($sites, $link, $min, $step, "");
echo "</div>";
?>
