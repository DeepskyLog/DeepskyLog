<?php
// change_site.php
// allows the administrator to change site details 

$latitudestr = $objLocation->getLatitude($_GET['location']);
$latitudedeg = (int)($latitudestr);
$latitudemin = round(((float)($latitudestr) - (int)($latitudestr)) * 60);
$longitudestr = $objLocation->getLongitude($_GET['location']);
$longitudedeg = (int)($longitudestr);
$longitudemin = round(((float)($longitudestr) - (int)($longitudestr)) * 60);
$timezone_identifiers = DateTimeZone::listIdentifiers();
$theTimeZone=$objLocation->getTimezone($_GET['location']);
$tempTimeZoneList="<select name=\"timezone\" class=\"inputfield requiredField\">";
while(list ($key, $value) = each($timezone_identifiers))
  $tempTimeZoneList.="<option value=\"".$value."\"".(($value==$theTimeZone)?" selected":"")."> ".$value."</option>";
$tempTimeZoneList.="</select>";
$lm = $objLocation->getLocationLimitingMagnitude($_GET['location']);
$sb = $objLocation->getSkyBackground($_GET['location']);

echo "<div id=\"main\">";
echo "<h2>".stripslashes($objLocation->getLocationName($_GET['location']))."</h2>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
echo "<table>";
tableFieldnameField(LangAddSiteField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"".stripslashes($objLocation->getLocationName($_GET['location']))."\" />");
tableFieldnameFieldExplanation(LangAddSiteField2,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"".stripslashes($objLocation->getRegion($_GET['location']))."\" />",LangAddSiteField2Expl);
tableFieldnameFieldExplanation(LangAddSiteField3,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"country\" size=\"30\" value=\"".$objLocation->getCountry($_GET['location'])."\" />",LangAddSiteField3Expl);
tableFieldnameFieldExplanation(LangAddSiteField4,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"3\" name=\"latitude\" size=\"3\" value=\"".$latitudedeg."\" />&deg;<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"latitudemin\" size=\"2\" value=\"".$latitudemin . "\" />&#39;",LangAddSiteField4Expl);
tableFieldnameFieldExplanation(LangAddSiteField5,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"".$longitudedeg."\" />&deg;<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" name=\"longitudemin\" size=\"2\" value=\"".$longitudemin."\" />&#39;",LangAddSiteField5Expl);
tableFieldnameField(LangAddSiteField6,$tempTimeZoneList);
tableFieldnameFieldExplanation(LangAddSiteField7,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"".(($lm > -900)?$lm:"")."\">",LangAddSiteField7Expl);
tableFieldnameFieldExplanation(LangAddSiteField8,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"".(($sb > -900)?$sb:"")."\">",LangAddSiteField8Expl);
echo "<tr>";
echo "<td><input type=\"submit\" name=\"change\" value=\"".LangAddSiteButton2."\" /><input type=\"hidden\" name=\"id\" value=\"".$_GET['location']."\"></input></td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

?>
