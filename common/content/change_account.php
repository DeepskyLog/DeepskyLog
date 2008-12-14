<?php
// change_account.php
// allows the user to view and change his account's details

echo "<div id=\"main\">";
echo "<h2>".LangChangeAccountTitle."</h2>";
$upload_dir = 'common/observer_pics';
$dir = opendir($upload_dir);
while (FALSE !== ($file = readdir($dir)))
{ if ("." == $file OR ".." == $file)                                            // skip current directory and directory above
    continue; 
  if(fnmatch($_SESSION['deepskylog_id']. ".gif", $file) || fnmatch($_SESSION['deepskylog_id']. ".jpg",$file) || fnmatch($_SESSION['deepskylog_id']. ".png", $file))
  { echo "<p align=\"center\">";
	  echo "<img class=\"account\" src=\"".$baseURL."$upload_dir" . "/" . "$file\" alt=\"" . $_SESSION['deepskylog_id'] . "\"></img>";
		echo "</p>";
	}
}
echo "<form class=\"content\" action=\"".$baseURL."index.php\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"common_control_validate_account\">";
echo "<table width=\"100%\">";
echo "<tr>";
tableFieldnameFieldExplanation(LangChangeAccountField1,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkSessionKey('deepskylog_id')."\" />",LangChangeAccountField1Expl);
tableFieldnameFieldExplanation(LangChangeAccountField2,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objObserver->getEmail($objUtil->checkSessionKey('deepskylog_id'))."\" />",LangChangeAccountField2Expl);
tableFieldnameFieldExplanation(LangChangeAccountField3,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objObserver->getFirstName($objUtil->checkSessionKey('deepskylog_id'))."\" />",LangChangeAccountField3Expl);
tableFieldnameFieldExplanation(LangChangeAccountField4,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objObserver->getObserverName($objUtil->checkSessionKey('deepskylog_id'))."\" />",LangChangeAccountField4Expl);
tableFieldnameFieldExplanation(LangChangeAccountField5,"<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"\" />",LangChangeAccountField5Expl);
tableFieldnameFieldExplanation(LangChangeAccountField6,"<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"\" />",LangChangeAccountField6Expl);
tableFieldnameFieldExplanation(LangChangeAccountField11."&nbsp;*","<input type=\"checkbox\" class=\"inputfield\" name=\"local_time\"".(($objObserver->getUseLocal($_SESSION['deepskylog_id']))?" checked":"")." />",LangChangeAccountField11Expl);
tableFieldnameFieldExplanation(LangChangeAccountField10,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"icq_name\" size=\"5\" value=\"".$objObserver->getIcqName($_SESSION['deepskylog_id'])."\" />",LangChangeAccountField10Expl);
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
$tempList="<select name=\"site\">";                                                  // SITE
$sites = $objLocation->getSortedLocations("name", $_SESSION['deepskylog_id']);
// If there are locations with the same name, the province should alse 
// be shown
$previous = "fskfskf";
for($i=0;$i<count($sites);$i++)
{ $adapt[$i] = 0;
  if($objLocation->getLocationName($sites[$i])==$previous)
  { $adapt[$i]=1;
    $adapt[$i-1]=1;
  }
 $previous=$objLocation->getLocationName($sites[$i]);
}
for ($i = 0;$i < count($sites);$i++)
{ if($adapt[$i])
    $sitename = $objLocation->getLocationName($sites[$i])." (".$objLocation->getRegion($sites[$i]).")";
  else
    $sitename = $objLocation->getLocationName($sites[$i]);
  $tempList.="<option ".(($objObserver->getStandardLocation($_SESSION['deepskylog_id'])==$sites[$i])?" selected=\"selected\"":"")." value=\"".$sites[$i]."\">".$sitename."</option>";
}
$tempList.="</select>";
tableFieldnameFieldExplanation(LangChangeAccountField7,$tempList,"<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeAccountField7Expl."</a>");
$tempList="<select name=\"instrument\">";
$instr=$objInstrument->getSortedInstruments("name",$_SESSION['deepskylog_id']);
$noStd=false;
while(list($key,$value)=each($instr))
{ $instrumentname=$objInstrument->getInstrumentName($value);
  if($instrumentname=="Naked eye")
    $instrumentname=InstrumentsNakedEye;
  if($objObserver->getStandardTelescope($_SESSION['deepskylog_id'])=="0")
    $noStd = 1;
	if($objObserver->getStandardTelescope($_SESSION['deepskylog_id'])==$value)
    $tempList.="<option selected=\"selected\" value=\"".$value."\">".$instrumentname."</option>";
  else
    $tempList.="<option ".(($noStd&&($value=="1"))?" selected=\"selected\"":"")." value=\"".$value."\">".$instrumentname."</option>";
}
$tempList.="</select>";
tableFieldnameFieldExplanation(LangChangeAccountField8,$tempList,"<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>");
$theKey=$objObserver->getStandardAtlasCode($_SESSION['deepskylog_id']);
$tempList="<select name=\"atlas\">";
while(list($key,$value)=each($objAtlas->atlasCodes))
  $tempList.="<option ".(($key==$theKey)?"selected=\"selected\"":"")." value=\"$key\">" . $value . "</option>";
$tempList.="</select>";
tableFieldnameFieldExplanation(LangChangeAccountField9,$tempList,"");
tableFieldnameFieldExplanation(LangChangeAccountPicture,"<input type=\"file\" name=\"picture\" />","");
echo "<tr>";
echo "<td>&nbsp;</td>"; 
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"fieldname\">";
if($languageMenu==1)
{ $tempList="<select name=\"language\">";
	$languages=$objLanguage->getLanguages(); 
  while(list($key,$value)=each($languages))
    $tempList.="<option value=\"".$key."\"".(($objObserver->getLanguage($_SESSION['deepskylog_id'])==$key)?" selected=\"selected\"":"").">".$value."</option>";
  $tempList.="</select>";
  tableFieldnameFieldExplanation(LangChangeAccountLanguage,$tempList,LangChangeAccountLanguageExpl);
}
$allLanguages=$objLanguage->getAllLanguages($objObserver->getLanguage($_SESSION['deepskylog_id']));
$tempList="<select name=\"description_language\">";
while(list ($key, $value) = each($allLanguages))
  $tempList.="<option value=\"".$key."\"".(($objObserver->getObservationLanguage($_SESSION['deepskylog_id']) == $key)?" selected=\"selected\"":"").">".$value."</option>";
$tempList.="</select>";
tableFieldnameFieldExplanation(LangChangeAccountObservationLanguage,$tempList,LangChangeAccountObservationLanguageExpl);
$_SESSION['alllanguages']=$allLanguages; 
$usedLanguages=$objObserver->getUsedLanguages($_SESSION['deepskylog_id']);
reset($allLanguages);
$tempList='';
while(list($key,$value)=each($allLanguages))
  $tempList.="<input type=\"checkbox\" ".(in_array($key,$usedLanguages)?"checked=\"true\"":"")." name=\"".$key."\" value=\"".$key."\" />".$value."<br />";
tableFieldnameFieldExplanation(LangChangeAccountObservationLanguage,$tempList,LangChangeVisibleLanguagesExpl);
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td>";
echo "<input type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" /></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
