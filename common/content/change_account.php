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
  { echo "<p>";
	  echo "<img class=\"account\" src=\"".$baseURL."$upload_dir" . "/" . "$file\" alt=\"" . $_SESSION['deepskylog_id'] . "\"></img>";
		echo "</p>";
	}
}
echo "<form class=\"content\" action=\"".$baseURL."index.php\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"common_control_validate_account\">";
echo "<table width=\"490\">";
echo "<tr>";
tableFieldnameFieldExplanation(LangChangeAccountField1,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkSessionKey('deepskylog_id')."\" />",LangChangeAccountField1Expl);
tableFieldnameFieldExplanation(LangChangeAccountField2,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objObserver->getEmail($objUtil->checkSessionKey('deepskylog_id'))."\" />",LangChangeAccountField2Expl);
tableFieldnameFieldExplanation(LangChangeAccountField3,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objObserver->getFirstName($objUtil->checkSessionKey('deepskylog_id'))."\" />",LangChangeAccountField3Expl);
tableFieldnameFieldExplanation(LangChangeAccountField4,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objObserver->getObserverName($objUtil->checkSessionKey('deepskylog_id'))."\" />",LangChangeAccountField4Expl);
tableFieldnameFieldExplanation(LangChangeAccountField5,"<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"\" />",LangChangeAccountField5Expl);
tableFieldnameFieldExplanation(LangChangeAccountField6,"<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"\" />",LangChangeAccountField6Expl);
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeAccountField11."&nbsp;*";
echo "</td>";
echo "<td>";                                                                    //LOCAL TIME
echo "<input type=\"checkbox\" class=\"inputfield\" name=\"local_time\"".(($objObserver->getUseLocal($_SESSION['deepskylog_id']))?" checked":"")." />";
echo "</td>";
echo "<td>";
echo LangChangeAccountField11Expl;
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeAccountField10;
echo "</td>";
echo "<td>";                                                                    //ICQ NAME
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"icq_name\" size=\"5\" value=\"".$objObserver->getIcqName($_SESSION['deepskylog_id'])."\" />";
echo "</td>";
echo "<td class=\"explanation\">";
echo LangChangeAccountField10Expl;
echo "</td>";
echo "<td class=\"explanation\">";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeAccountField7;
echo "</td>";
echo "<td>";
echo "<select name=\"site\">";                                                  // SITE
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
  echo "<option ".(($objObserver->getStandardLocation($_SESSION['deepskylog_id'])==$sites[$i])?" selected=\"selected\"":"")." value=\"".$sites[$i]."\">".$sitename."</option>";
}
echo "</select>";
echo "</td>";
echo "<td class=\"explanation\">";
echo "<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeAccountField7Expl."</a>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeAccountField8;
echo"</td>";
echo "<td>";                                                                    //INSTRUMENT
echo "<select name=\"instrument\">";
$instr=$objInstrument->getSortedInstruments("name",$_SESSION['deepskylog_id']);
$noStd=false;
while(list($key,$value)=each($instr))
{ $instrumentname=$objInstrument->getInstrumentName($value);
  if($instrumentname=="Naked eye")
    $instrumentname=InstrumentsNakedEye;
  if($objObserver->getStandardTelescope($_SESSION['deepskylog_id'])=="0")
    $noStd = 1;
	if($objObserver->getStandardTelescope($_SESSION['deepskylog_id'])==$value)
    echo "<option selected=\"selected\" value=\"".$value."\">".$instrumentname."</option>";
  else
    echo "<option ".(($noStd&&($value=="1"))?" selected=\"selected\"":"")." value=\"".$value."\">".$instrumentname."</option>";
}
echo "</select>";
echo "</td>";
echo "<td class=\"explanation\">";
echo "<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeAccountField9;
echo "</td>";
echo "<td>";
echo "<select name=\"atlas\">";
$atlasses=$objAtlas->getSortedAtlasses();
$theKey=$objObserver->getStandardAtlasCode($_SESSION['deepskylog_id']);
while(list($key,$value)=each($atlasses))
  echo"<option ".(($key==$theKey)?"selected=\"selected\"":"")." value=\"$key\">" . $value . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeAccountPicture;
echo "</td>";
echo "<td colspan=\"2\">";
echo "<input type=\"file\" name=\"picture\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>&nbsp;</td>"; 
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
if($languageMenu==1)
{ echo LangChangeAccountLanguage;
  echo "</td>";
  echo "<td>";
	echo "<select name=\"language\">";
	$languages=$objLanguage->getLanguages(); 
  while(list($key,$value)=each($languages))
    echo "<option value=\"".$key."\"".(($objObserver->getLanguage($_SESSION['deepskylog_id'])==$key)?" selected=\"selected\"":"").">".$value."</option>";
  echo "</select>";
  echo "</td>";
	echo "<td class=\"explanation\">";
  echo LangChangeAccountLanguageExpl;
}
echo"</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo LangChangeAccountObservationLanguage;
echo "</td>";
echo "<td>";
$allLanguages=$objLanguage->getAllLanguages($objObserver->getLanguage($_SESSION['deepskylog_id']));
echo "<select name=\"description_language\">";
while(list ($key, $value) = each($allLanguages))
  echo "<option value=\"".$key."\"".(($objObserver->getObservationLanguage($_SESSION['deepskylog_id']) == $key)?" selected=\"selected\"":"").">".$value."</option>";
echo "</select>";
echo "</td>";
echo "<td class=\"explanation\">";
echo LangChangeAccountObservationLanguageExpl;
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangChangeVisibleLanguages;
echo "</td>";
echo "<td>";
$_SESSION['alllanguages']=$allLanguages; 
$usedLanguages=$objObserver->getUsedLanguages($_SESSION['deepskylog_id']);
reset($allLanguages);
while(list($key,$value)=each($allLanguages))
  echo "<input type=\"checkbox\" ".(in_array($key,$usedLanguages)?"checked=\"true\"":"")." name=\"".$key."\" value=\"".$key."\" />".$value."<br />";
echo "</td>";
echo "<td class=\"explanation\">";
echo LangChangeVisibleLanguagesExpl;
echo "</td>";
echo "</tr>";
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
