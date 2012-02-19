<?php 
// new_site.php
// allows the user to add a new site

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else new_site();

function new_site()
{ global $baseURL,$loggedUser,$loggedUserName,$sites,
         $objLocation,$objObserver,$objPresentations,$objUtil;
  $sort=$objUtil->checkRequestKey('sort','name');
  $sites=$objLocation->getSortedLocations($sort, $loggedUser);
  $locs =$objObserver->getListOfLocations();
  $locationid=$objUtil->checkRequestKey('locationid');
  $tempCountryList="<select name=\"country\" class=\"inputfield requiredField\">";
  $countries = $objLocation->getPreferredCountries();
  while(list($key,$value)=each($countries))
  { if($objUtil->checkRequestKey('country')==$value)
  	  $tempCountryList.="<option selected=\"selected\" value=\"".$value."\">".$value."</option>";
  	elseif($locationid&&($objLocation->getLocationPropertyFromId($locationid,'country')==$value))
    	$tempCountryList.="<option selected=\"selected\" value=\"".$value."\">".$value."</option>";
  	else
  	  $tempCountryList.="<option value=\"".$value."\">".$value."</option>";
  }
  $tempCountryList.="<option value=\"\">-----</option>";
  $countries = $objLocation->getCountries();
  while(list($key,$value)=each($countries))
  { if($objUtil->checkRequestKey('country')==$value)
	    $tempCountryList.="<option selected=\"selected\" value=\"".$value."\">".$value."</option>";
  	elseif($locationid&&($objLocation->getLocationPropertyFromId($locationid,'country')==$value))
	    $tempCountryList.="<option selected=\"selected\" value=\"".$value."\">".$value."</option>";
	  else
      $tempCountryList.="<option value=\"".$value."\">".$value."</option>";
  }
  $tempCountryList.="</select>";
  $latitudedeg=$objUtil->checkRequestKey('latitude');
  $latitudemin=$objUtil->checkRequestKey('latitudemin');
  $longitudedeg=$objUtil->checkRequestKey('longitude');
  $longitudemin=$objUtil->checkRequestKey('longitudemin');
  if($locationid=$objUtil->checkRequestKey('locationid'))
  { $latitudestr = $objLocation->getLocationPropertyFromId($locationid,'latitude');
    $latitudedeg = (int)($latitudestr);
    $latitudemin = round(((float)($latitudestr) - (int)($latitudestr)) * 60);
    $longitudestr = $objLocation->getLocationPropertyFromId($locationid,'longitude');
    $longitudedeg = (int)($longitudestr);
    $longitudemin = round(((float)($longitudestr) - (int)($longitudestr)) * 60);
  }
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>".LangOverviewSiteTitle." ".$loggedUserName."</h4>"),
                          "L",array(100),30);
  echo "<hr />"; 
  $objLocation->showLocationsObserver();
  $sites = $objLocation->getSortedLocations('name');
  echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
  echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
  $content1b= "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
  while(list($key,$value)=each($sites))
    $content1b.= "<option value=\"".$baseURL."index.php?indexAction=add_site&amp;locationid=".urlencode($value)."\" ".(($value==$objUtil->checkRequestKey('locationid'))?" selected=\"selected\" ":'').">" . $objLocation->getLocationPropertyFromId($value,'name') . "</option>";
  $content1b.= "</select>";
  $objPresentations->line(array("<h4>".LangAddSiteTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
  echo "<hr />";
  $objPresentations->line(array("","<a href=\"".$baseURL."index.php?indexAction=search_sites\">".LangAddSiteFieldSearchDatabase."</a>",
                                "<input type=\"submit\" name=\"add\" value=\"".LangAddSiteButton."\" />&nbsp;"),
                          "RLR",array(25,40,35),'',array("fieldname"));
  $objPresentations->line(array(LangAddSiteExisting,
                                $content1b),
                          "RLR",array(25,40,35),'',array("fieldname"));                              
  $objPresentations->line(array(LangAddSiteFieldOr." ".LangAddSiteFieldManually),"R",array(25),'',array("fieldname"));
  $objPresentations->line(array(LangAddSiteField1,
                                 "<input type=\"text\" required class=\"inputfield requiredField\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('sitename')).stripslashes($objLocation->getLocationPropertyFromId($objUtil->checkRequestKey('locationid'),'name'))."\" />",
                                 ''),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  $objPresentations->line(array(LangAddSiteField2,
                                 "<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('region')).stripslashes($objLocation->getLocationPropertyFromId($objUtil->checkRequestKey('locationid'),'region'))."\" />",
                                 LangAddSiteField2Expl),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  $objPresentations->line(array(LangAddSiteField3,$tempCountryList,''),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  $objPresentations->line(array(LangAddSiteField4,
                                 "<input type=\"number\" min=\"-90\" max=\"90\" required class=\"inputfield requiredField centered\" maxlength=\"3\" name=\"latitude\" size=\"4\" value=\"".$latitudedeg.
                                  "\" />&deg;&nbsp;".
                                  "<input type=\"number\" min=\"0\" max=\"59\" required class=\"inputfield requiredField centered\" maxlength=\"2\" name=\"latitudemin\" size=\"4\"	value=\"".abs($latitudemin).
                                  "\" />&#39;",
                                  LangAddSiteField4Expl),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  $objPresentations->line(array(LangAddSiteField5,
                                 "<input type=\"number\" min=\"-180\" max=\"180\" required class=\"inputfield requiredField centered\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"".$longitudedeg.
                                 "\" />&deg;&nbsp;".
                                 "<input type=\"number\" min=\"0\" max=\"59\" required class=\"inputfield requiredField centered\" maxlength=\"2\"	name=\"longitudemin\" size=\"4\" value=\"".abs($longitudemin).
                                 "\" />&#39;",
                                 LangAddSiteField5Expl),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  $objPresentations->line(array(LangAddSiteField7,
                                 "<input type=\"number\" min=\"0\" max=\"9.9\" step=\"0.1\" class=\"inputfield centered\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"".(($objLocation->getLocationPropertyFromId($objUtil->checkRequestKey('locationid'),'limitingMagnitude')>-900)?$objLocation->getLocationPropertyFromId($objUtil->checkRequestKey('locationid'),'limitingMagnitude'):"")."\" />",
                                 LangAddSiteField7Expl),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  $objPresentations->line(array(LangAddSiteField8,
                                 "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" class=\"inputfield centered\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"".(($objLocation->getLocationPropertyFromId($objUtil->checkRequestKey('locationid'),'skyBackground')>-900)?$objLocation->getLocationPropertyFromId($objUtil->checkRequestKey('locationid'),'skyBackground'):"")."\" />",
                                 LangAddSiteField8Expl),
                          "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
  echo "<hr />";
  echo "</div></form>";
  echo "</div>";
}
?>
