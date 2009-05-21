<?php // change_site.php - allows the administrator to change site details 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!($locationid=$objUtil->checkGetKey('location'))) throw new Exception(LangException011);
elseif(!($objUtil->checkAdminOrUserID($objLocation->getLocationPropertyFromId($locationid,'observer','')))) throw new Exception(LangExcpetion012);
else
{
$latitudestr = $objLocation->getLocationPropertyFromId($locationid,'latitude');
$latitudedeg = (int)($latitudestr);
$latitudemin = round(((float)($latitudestr) - (int)($latitudestr)) * 60);
$longitudestr = $objLocation->getLocationPropertyFromId($locationid,'longitude');
$longitudedeg = (int)($longitudestr);
$longitudemin = round(((float)($longitudestr) - (int)($longitudestr)) * 60);
$timezone_identifiers = DateTimeZone::listIdentifiers();
$theTimeZone=$objLocation->getLocationPropertyFromId($locationid,'timezone');
$tempTimeZoneList="<select name=\"timezone\" class=\"inputfield requiredField\">";
while(list ($key, $value) = each($timezone_identifiers))
  $tempTimeZoneList.="<option value=\"".$value."\"".(($value==$theTimeZone)?" selected=\"selected\"":"")."> ".$value."</option>";
$tempTimeZoneList.="</select>";
$lm = $objLocation->getLocationPropertyFromId($locationid,'limitingMagnitude');
$sb = $objLocation->getLocationPropertyFromId($locationid,'skyBackground');

echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
$objPresentations->line(array("<h5>".stripslashes($objLocation->getLocationPropertyFromId($locationid,'name'))."</h5>","<input type=\"submit\" name=\"change\" value=\"".LangAddSiteButton2."\" /><input type=\"hidden\" name=\"id\" value=\"".$locationid."\" />&nbsp;"),"LR",array(80,20),50); 
echo "<hr />";
$line[]=array(LangAddSiteField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"".stripslashes($objLocation->getLocationPropertyFromId($locationid,'name'))."\" />");
$line[]=array(LangAddSiteField2,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"".stripslashes($objLocation->getLocationPropertyFromId($locationid,'region'))."\" />",LangAddSiteField2Expl);
$line[]=array(LangAddSiteField3,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"country\" size=\"30\" value=\"".$objLocation->getLocationPropertyFromId($locationid,'country')."\" />",LangAddSiteField3Expl);
$line[]=array(LangAddSiteField4,"<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"3\" name=\"latitude\" size=\"4\" value=\"".$latitudedeg."\" />&deg;<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"latitudemin\" size=\"2\" value=\"".$latitudemin . "\" />&#39;",LangAddSiteField4Expl);
$line[]=array(LangAddSiteField5,"<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"".$longitudedeg."\" />&deg;<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"longitudemin\" size=\"2\" value=\"".$longitudemin."\" />&#39;",LangAddSiteField5Expl);
$line[]=array(LangAddSiteField6,$tempTimeZoneList);
$line[]=array(LangAddSiteField7,"<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"".(($lm > -900)?$lm:"")."\" />",LangAddSiteField7Expl);
$line[]=array(LangAddSiteField8,"<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"".(($sb > -900)?$sb:"")."\" />",LangAddSiteField8Expl);
for($i=0;$i<count($line);$i++)
  $objPresentations->line($line[$i],"RLL",array(20,40,40),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</form>";
echo "</div>";
}
?>
