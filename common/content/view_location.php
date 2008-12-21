<?php
// view_location.php
// view information of location 

if(!$objUtil->checkGetKey('location'))
  throw("No location specified.");
if(!($name=stripslashes($objLocation->getLocationName($_GET['location']))))
  throw("Location not found");
  
$timezone = $objLocation->getTimezone($_GET['location']);
  
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewLocationProvince,stripslashes($objLocation->getRegion($_GET['location'])));
tableFieldnameField(LangViewLocationCountry,$objLocation->getCountry($_GET['location']));
tableFieldnameField(LangViewLocationLongitude,decToTrimmedString($objLocation->getLongitude($_GET['location'])));
tableFieldnameField(LangViewLocationLatitude,decToTrimmedString($objLocation->getLatitude($_GET['location'])));
tableFieldnameField(LangAddSiteField6,$timezone);
$lm = $objLocation->getLocationLimitingMagnitude($_GET['location']);
$sb = $objLocation->getSkyBackground($_GET['location']);
if(($lm>-900)||($sb>-900))
{ if ($lm>-900)
    $sb=$objContrast->calculateSkyBackgroundFromLimitingMagnitude($lm);
  else 
    $lm=$objContrast->calculateLimitingMagnitudeFromSkyBackground($sb);
  tableFieldnameField(LangAddSiteField7,sprintf("%.1f", $lm));
  tableFieldnameField(LangAddSiteField8,sprintf("%.2f", $sb));
}
echo "<tr>";
echo "<td colspan=\"2\"><br></br>";
echo "<a href=\"http://maps.google.com/maps?ll=" . $objLocation->getLatitude($_GET['location']) . "," . $objLocation->getLongitude($_GET['location']) . "&spn=4.884785,11.585083&t=h&hl=en\"><img class=\"account\" src=\"".$baseURL."common/content/map.php?lat=" . $objLocation->getLatitude($_GET['location']) . "&long=" . $objLocation->getLongitude($_GET['location']) . "\" width=\"490\" height=\"245\" title=\"".LangGooglemaps."\"></a>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
?>
