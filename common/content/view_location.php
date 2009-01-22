<?php // view_location.php - view information of location 
if(!$objUtil->checkGetKey('location'))
  throw new Exception("No location specified.");
if(!($name=stripslashes($objLocation->getLocationPropertyFromId($_GET['location'],'name'))))
  throw new Exception("Location not found");
$timezone = $objLocation->getLocationPropertyFromId($_GET['location'],'timezone');
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewLocationProvince,stripslashes($objLocation->getLocationPropertyFromId($_GET['location'],'region')));
tableFieldnameField(LangViewLocationCountry,$objLocation->getLocationPropertyFromId($_GET['location'],'country'));
tableFieldnameField(LangViewLocationLongitude,decToTrimmedString($objLocation->getLocationPropertyFromId($_GET['location'],'longitude')));
tableFieldnameField(LangViewLocationLatitude,decToTrimmedString($objLocation->getLocationPropertyFromId($_GET['location'],'latitude')));
tableFieldnameField(LangAddSiteField6,$timezone);
$lm = $objLocation->getLocationPropertyFromId($_GET['location'],'limitingMagnitude');
$sb = $objLocation->getLocationPropertyFromId($_GET['location'],'skyBackground');
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
echo "<a href=\"http://maps.google.com/maps?ll=" . $objLocation->getLocationPropertyFromId($_GET['location'],'latitude') . "," . $objLocation->getLocationPropertyFromId($_GET['location'],'longitude') . "&spn=4.884785,11.585083&t=h&hl=en\"><img class=\"account\" src=\"".$baseURL."common/content/map.php?lat=" . $objLocation->getLocationPropertyFromId($_GET['location'],'latitude') . "&long=" . $objLocation->getLocationPropertyFromId($_GET['location'],'longitude') . "\" width=\"490\" height=\"245\" title=\"".LangGooglemaps."\"></a>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
?>
