<?php // new_site.php - allows the user to add a new site
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
$sort=$objUtil->checkGetKey('sort','name');
$locationid=$objUtil->checkGetKey('locationid');
$timezone_identifiers = DateTimeZone::listIdentifiers();
$tempTimeZoneList="<select name=\"timezone\" class=\"inputfield requiredField\">";
while(list($key,$value)=each($timezone_identifiers))
{ if($locationid)
	  $tempTimeZoneList.="<option value=\"$value\"".(($value==$objLocation->getLocationPropertyFromId($locationid,'timezone'))?" selected=\"selected\"":"").">$value</option>";
	else
	  $tempTimeZoneList.="<option value=\"".$value."\"".(($value=="UTC")?" selected":"").">".$value."</option>";
}
$tempTimeZoneList.="</select>";
$tempCountryList="<select name=\"country\" class=\"inputfield requiredField\">";
$countries = $objLocation->getCountries();
$tempCountryList.="<option value=\"\"></option>";
while(list($key,$value)=each($countries))
{ $sites=$objLocation->getSortedLocations($sort, $_SESSION['deepskylog_id']);
  $locs =$objObserver->getListOfLocations();
  if($objUtil->checkGetKey('country')==$value)
	  $tempCountryList.="<option selected=\"selected\" value=\"".$value."\">".$value."</option>";
	elseif($locationid&&($objLocation->getLocationPropertyFromId($locationid,'country')==$value))
	  $tempCountryList.="<option selected=\"selected\" value=\"".$value."\">".$value."</option>";
	else
    $tempCountryList.="<option value=\"".$value."\">".$value."</option>";
}
$tempCountryList.="</select>";
$latitudedeg='';
$latitudemin='';
$longitudedeg='';
$longitudemin='';
if(($latitude=$objUtil->checkGetKey('latitude'))||$locationid)
{ if($latitude)
    $latitudestr=$objPresentations->decToString($_GET['latitude'], 1);
  else
	  $latitudestr=$objPresentations->decToString($objLocation->getLocationPropertyFromId($_GET['locationid'],'latitude'), 1);
  $latarray = explode("&deg;", $latitudestr);
  $latitudedeg = $latarray[0];
  $latitudemin = substr($latarray[1],0,-1);
}
if(array_key_exists('longitude',$_GET) && $_GET['longitude'] || array_key_exists('locationid',$_GET) && $_GET['locationid'])
{ if (array_key_exists('longitude',$_GET))
      $longitudestr = $objPresentations->decToString($_GET['longitude'], 1);
  else
    $longitudestr = $objPresentations->decToString($objLocation->getLocationPropertyFromId($_GET['locationid'],'longitude'), 1);
  $longarray = explode("&deg;", $longitudestr);
  $longitudedeg = $longarray[0];
  $longitudemin = substr($longarray[1],0,-1);
}
echo "<div id=\"main\">";
$objLocation->showLocationsObserver();
$sites = $objLocation->getSortedLocations('name');
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
$content1b= "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
while(list($key,$value)=each($sites))
  $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_site&amp;locationid=".urlencode($value)."\" ".(($value==$objUtil->checkGetKey('locationid'))?' selected=\"selected\" ':'').">" . $objLocation->getLocationPropertyFromId($value,'name') . "</option>";
$content1b.= "</select>";
$objPresentations->line(array("<h5>".LangAddSiteTitle."</h5>"),"L",array(),50);
echo "<hr />";
$objPresentations->line(array("","<a href=\"".$baseURL."index.php?indexAction=search_sites\">".LangAddSiteFieldSearchDatabase."</a>",
                              "<input type=\"submit\" name=\"add\" value=\"".LangAddSiteButton."\" />&nbsp;"),
                        "RLR",array(25,40,35),'',array("fieldname"));
$objPresentations->line(array(LangAddSiteExisting,
                              $content1b),
                        "RLR",array(25,40,35),'',array("fieldname"));                              
$objPresentations->line(array(LangAddSiteFieldOr." ".LangAddSiteFieldManually),"R",array(25),'',array("fieldname"));
$objPresentations->line(array(LangAddSiteField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('sitename')).stripslashes($objLocation->getLocationPropertyFromId($objUtil->checkGetKey('locationid'),'name'))."\" />",
                               ''),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('region')).stripslashes($objLocation->getLocationPropertyFromId($objUtil->checkGetKey('locationid'),'region'))."\" />",
                               LangAddSiteField2Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField3,$tempCountryList,''),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField4,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"3\" name=\"latitude\" size=\"4\" value=\"".
                                (((array_key_exists('latitude',$_GET) && $_GET['latitude']) || (array_key_exists('locationid',$_GET) && $_GET['locationid']))?$latitudedeg:"").
                                "\" />&deg;&nbsp;".
                                "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"latitudemin\" size=\"4\"	value=\"".
                                (((array_key_exists('latitude',$_GET) && $_GET['latitude']) || (array_key_exists('locationid',$_GET) && $_GET['locationid']))?$latitudemin:"").
                                "\" />&#39;",
                                LangAddSiteField4Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField5,
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"".
                               (((array_key_exists('longitude',$_GET) && $_GET['longitude']) || (array_key_exists('locationid',$_GET) && $_GET['locationid']))?$longitudedeg:"").
                               "\" />&deg;&nbsp;".
                               "<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\"	name=\"longitudemin\" size=\"4\" value=\"".
                               (((array_key_exists('longitude',$_GET) && $_GET['longitude']) || (array_key_exists('locationid',$_GET) && $_GET['locationid']))?$longitudemin:"").
                               "\" />&#39;",
                               LangAddSiteField5Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField6,$tempTimeZoneList,''),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField7,
                               "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"".(($objLocation->getLocationPropertyFromId($objUtil->checkGetKey('locationid'),'limitingMagnitude')>-900)?$objLocation->getLocationPropertyFromId($objUtil->checkGetKey('locationid'),'limitingMagnitude'):"")."\" />",
                               LangAddSiteField7Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
$objPresentations->line(array(LangAddSiteField8,
                               "<input type=\"text\" class=\"inputfield centered\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"".(($objLocation->getLocationPropertyFromId($objUtil->checkGetKey('locationid'),'skyBackground')>-900)?$objLocation->getLocationPropertyFromId($objUtil->checkGetKey('locationid'),'skyBackground'):"")."\" />",
                               LangAddSiteField8Expl),
                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
echo "<hr />";
echo "</form>";
echo "</div>";
}
?>
