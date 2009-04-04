<?php // change_account.php - allows the user to view and change his account's details
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
else
{
$sites = $objLocation->getSortedLocations("name", $loggedUser);
$tempLocationList="<select name=\"site\" class=\"inputfield\">";
// If there are locations with the same name, the province should also be shown
$previous = "fskfskf";
for($i=0;$i<count($sites);$i++)
{ $adapt[$i] = 0;
  if($objLocation->getLocationPropertyFromId($sites[$i],'name')==$previous)
  { $adapt[$i]=1;
    $adapt[$i-1]=1;
  }
 $previous=$objLocation->getLocationPropertyFromId($sites[$i],'name');
}
for ($i = 0;$i < count($sites);$i++)
{ if($adapt[$i])
    $sitename = $objLocation->getLocationPropertyFromId($sites[$i],'name')." (".$objLocation->getLocationPropertyFromId($sites[$i],'region').")";
  else
    $sitename = $objLocation->getLocationPropertyFromId($sites[$i],'name');
  $tempLocationList.="<option ".(($objObserver->getObserverProperty($loggedUser,'stdlocation')==$sites[$i])?" selected=\"selected\"":"")." value=\"".$sites[$i]."\">".$sitename."</option>";
}
$tempLocationList.="</select>";
$tempInstrumentList="<select name=\"instrument\" class=\"inputfield\">";
$instr=$objInstrument->getSortedInstruments("name",$loggedUser);
$noStd=false;
while(list($key,$value)=each($instr))
{ $instrumentname=$objInstrument->getInstrumentPropertyFromId($value,'name');
  if($instrumentname=="Naked eye")
    $instrumentname=InstrumentsNakedEye;
  if($objObserver->getObserverProperty($loggedUser,'stdtelescope')=="0")
    $noStd = 1;
	if($objObserver->getObserverProperty($loggedUser,'stdtelescope')==$value)
    $tempInstrumentList.="<option selected=\"selected\" value=\"".$value."\">".$instrumentname."</option>";
  else
    $tempInstrumentList.="<option ".(($noStd&&($value=="1"))?" selected=\"selected\"":"")." value=\"".$value."\">".$instrumentname."</option>";
}
$tempInstrumentList.="</select>";

$theAtlasKey=$objObserver->getObserverProperty($loggedUser,'standardAtlasCode','urano');
$tempAtlasList="<select name=\"atlas\" class=\"inputfield\">";
while(list($key,$value)=each($objAtlas->atlasCodes))
  $tempAtlasList.="<option ".(($key==$theAtlasKey)?"selected=\"selected\"":"")." value=\"$key\">" . $value . "</option>";
$tempAtlasList.="</select>";

$tempLangList="<select name=\"language\" class=\"inputfield\">";
$languages=$objLanguage->getLanguages(); 
while(list($key,$value)=each($languages))
  $tempLangList.="<option value=\"".$key."\"".(($objObserver->getObserverProperty($loggedUser,'language')==$key)?" selected=\"selected\"":"").">".$value."</option>";
$tempLangList.="</select>";

$allLanguages=$objLanguage->getAllLanguages($objObserver->getObserverProperty($loggedUser,'language'));
$tempAllLangList="<select name=\"description_language\" class=\"inputfield\">";
while(list($key,$value)=each($allLanguages))
  $tempAllLangList.="<option value=\"".$key."\"".(($objObserver->getObserverProperty($loggedUser,'observationlanguage') == $key)?" selected=\"selected\"":"").">".$value."</option>";
$tempAllLangList.="</select>";
$_SESSION['alllanguages']=$allLanguages; 
$usedLanguages=$objObserver->getUsedLanguages($loggedUser);
reset($allLanguages);
$tempObsLangList="<table><tr>";
$j=0;
while(list($key,$value)=each($allLanguages))
{ if(!($j++%3))
    $tempObsLangList.= "</tr><tr>";
	$tempObsLangList.="<td><input type=\"checkbox\" ".(in_array($key,$usedLanguages)?"checked=\"true\"":"")." name=\"".$key."\" value=\"".$key."\" />".$value."</td>";
}
$tempObsLangList.="</tr></table>";

// =================================================================================================PAGE OUTPUT
echo "<div id=\"main\">";
echo "<h2>".LangChangeAccountTitle."</h2>";
echo "<hr>";
$upload_dir = 'common/observer_pics';
$dir = opendir($instDir.$upload_dir);
while (FALSE!==($file=readdir($dir)))
{ if(("."==$file)||(".."==$file))                                            // skip current directory and directory above
    continue; 
  if(fnmatch($loggedUser.".gif",$file)||fnmatch($loggedUser.".jpg",$file)||fnmatch($loggedUser.".png",$file))
  { echo "<p align=\"center\">";
	  echo "<img class=\"account\" src=\"".$baseURL.$upload_dir."/".$file."\" alt=\"".$loggedUser."/".$file."\"></img>";
		echo "</p>";
	}
}
echo "<form class=\"content\" action=\"".$baseURL."index.php\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\">";
echo "<table width=\"100%\">";
tableFieldnameFieldExplanation(LangChangeAccountField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkSessionKey('deepskylog_id')."\" />",LangChangeAccountField1Expl);
tableFieldnameFieldExplanation(LangChangeAccountField2,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'email')."\" />",LangChangeAccountField2Expl);
tableFieldnameFieldExplanation(LangChangeAccountField3,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'firstname')."\" />",LangChangeAccountField3Expl);
tableFieldnameFieldExplanation(LangChangeAccountField4,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'name')."\" />",LangChangeAccountField4Expl);
tableFieldnameFieldExplanation(LangChangeAccountField5,"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"\" />",LangChangeAccountField5Expl);
tableFieldnameFieldExplanation(LangChangeAccountField6,"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"\" />",LangChangeAccountField6Expl);
tableFieldnameFieldExplanation(LangChangeAccountField11."&nbsp;*","<input type=\"checkbox\" class=\"inputfield\" name=\"local_time\"".(($objObserver->getObserverProperty($loggedUser,'UT'))?"":"checked")." />",LangChangeAccountField11Expl);
tableFieldnameFieldExplanation(LangChangeAccountField10,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"icq_name\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'icqname')."\" />",LangChangeAccountField10Expl);
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
tableFieldnameFieldExplanation(LangChangeAccountField7,$tempLocationList,"<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeAccountField7Expl."</a>");
tableFieldnameFieldExplanation(LangChangeAccountField8,$tempInstrumentList,"<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>");
tableFieldnameFieldExplanation(LangChangeAccountField9,$tempAtlasList,"");
tableFieldnameFieldExplanation(LangChangeAccountPicture,"<input type=\"file\" name=\"picture\" class=\"inputfield\"/>","");
echo "<tr>";
echo "<td>&nbsp;</td>"; 
echo "<td>&nbsp;</td>";
echo "<td>&nbsp;</td>";
echo "</tr>";
if($languageMenu==1)
  tableFieldnameFieldExplanation(LangChangeAccountLanguage,$tempLangList,LangChangeAccountLanguageExpl);
tableFieldnameFieldExplanation(LangChangeAccountObservationLanguage,$tempAllLangList,LangChangeAccountObservationLanguageExpl);
tableFieldnameFieldExplanation(LangChangeAccountObservationLanguage,$tempObsLangList,LangChangeVisibleLanguagesExpl);
echo "</table>";
echo "<hr>";
echo "<input type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";
echo "</form>";
echo "</div>";
}
?>
