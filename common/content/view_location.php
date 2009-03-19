<?php // view_location.php - view information of location 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($locationid=$objUtil->checkGetKey('location'))) throw new Exception(LangException011b);
else
{
$name=stripslashes($objLocation->getLocationPropertyFromId($locationid,'name'));
$timezone = $objLocation->getLocationPropertyFromId($locationid,'timezone');
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewLocationProvince,stripslashes($objLocation->getLocationPropertyFromId($locationid,'region')));
tableFieldnameField(LangViewLocationCountry,$objLocation->getLocationPropertyFromId($locationid,'country'));
tableFieldnameField(LangViewLocationLongitude,$objPresentations->decToTrimmedString($objLocation->getLocationPropertyFromId($locationid,'longitude')));
tableFieldnameField(LangViewLocationLatitude,$objPresentations->decToTrimmedString($objLocation->getLocationPropertyFromId($locationid,'latitude')));
tableFieldnameField(LangAddSiteField6,$timezone);
$lm = $objLocation->getLocationPropertyFromId($locationid,'limitingMagnitude');
$sb = $objLocation->getLocationPropertyFromId($locationid,'skyBackground');
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
echo "<a href=\"http://maps.google.com/maps?ll=" . $objLocation->getLocationPropertyFromId($locationid,'latitude') . "," . $objLocation->getLocationPropertyFromId($locationid,'longitude') . "&spn=4.884785,11.585083&t=h&hl=en\"><img class=\"account\" src=\"".$baseURL."common/content/map.php?lat=" . $objLocation->getLocationPropertyFromId($locationid,'latitude') . "&long=" . $objLocation->getLocationPropertyFromId($locationid,'longitude') . "\" width=\"490\" height=\"245\" title=\"".LangGooglemaps."\"></a>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
}
?>
