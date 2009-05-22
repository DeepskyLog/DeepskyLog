<?php // change_account.php - allows the user to view and change his account's details
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangExcpetion012);
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

// =================================================================================================PAGE OUTPUT
echo "<div id=\"main\">";
echo "<form class=\"content\" action=\"".$baseURL."index.php\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\" />";
$objPresentations->line(array("<h5>".LangChangeAccountTitle."</h5>","<input type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />&nbsp;"),"LR",array(80,20),50);
echo "<hr />";
$line[]=array(LangChangeAccountField1,
              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkSessionKey('deepskylog_id')."\" />",
              LangChangeAccountField1Expl);
$line[]=array(LangChangeAccountField2,
              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'email')."\" />",
              LangChangeAccountField2Expl);
$line[]=array(LangChangeAccountField3,
              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'firstname')."\" />",
              LangChangeAccountField3Expl);
$line[]=array(LangChangeAccountField4,
              "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'name')."\" />",
              LangChangeAccountField4Expl);
$line[]=array(LangChangeAccountField5,
              "<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"\" />",
              LangChangeAccountField5Expl);
$line[]=array(LangChangeAccountField6,
              "<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"\" />",
              LangChangeAccountField6Expl);
$line[]=array(LangChangeAccountField11."&nbsp;*",
              "<input type=\"checkbox\" class=\"inputfield\" name=\"local_time\"".(($objObserver->getObserverProperty($loggedUser,'UT'))?"":"checked")." />",
              LangChangeAccountField11Expl);
$line[]=array(LangChangeAccountField10,
              "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"icq_name\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'icqname')."\" />",
              LangChangeAccountField10Expl);
$line[]=array("");
$line[]=array(LangChangeAccountField7,$tempLocationList,"<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeAccountField7Expl."</a>");
$line[]=array(LangChangeAccountField8,$tempInstrumentList,"<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>");
$line[]=array(LangChangeAccountField9,$tempAtlasList,"");
$line[]=array(LangChangeAccountPicture,"<input type=\"file\" name=\"picture\" class=\"inputfield\"/>","");
$line[]=array("");        
if($languageMenu==1)
  $line[]=array("<span class=\"fieldname\">".LangChangeAccountLanguage."</span>",$tempLangList,LangChangeAccountLanguageExpl);
$line[]=array("<span class=\"fieldname\">".LangChangeAccountObservationLanguage."</span>",$tempAllLangList,LangChangeAccountObservationLanguageExpl);
for($i=0;$i<count($line);$i++)
  $objPresentations->line($line[$i],"RLL",array(20,40,40),'',array("fieldname","fieldvalue","fieldexplanation"));
reset($allLanguages);
$j=0;
$tempObsLangList[]="";
while((list($key,$value)=each($allLanguages))&&($j<3))
{ $tempObsLangList[]="<input type=\"checkbox\" ".(in_array($key,$usedLanguages)?"checked=\"true\"":"")." name=\"".$key."\" value=\"".$key."\" />".$value;
  $j++;
}
$tempObsLangList[]=LangChangeVisibleLanguagesExpl;
$objPresentations->line($tempObsLangList,"RLLLL",array(20,13,13,14,40),'',array("fieldname","fieldvalue","","","fieldexplanation"));
unset($tempObsLangList);
$tempObsLangList[]="";
while((list($key,$value)=each($allLanguages)))
{ $tempObsLangList[]="<input type=\"checkbox\" ".(in_array($key,$usedLanguages)?"checked=\"true\"":"")." name=\"".$key."\" value=\"".$key."\" />".$value;
  $j++;
  if(($j%3)==0)
  { $tempObsLangList[]="";
    $objPresentations->line($tempObsLangList,"RLLLL",array(20,13,13,14,40),'',array("fieldname","fieldvalue","","","fieldexplanation"));
    unset($tempObsLangList);
    $tempObsLangList[]="";
  }
}
echo "</form>";
$upload_dir = 'common/observer_pics';
$dir = opendir($instDir.$upload_dir);
while (FALSE!==($file=readdir($dir)))
{ if(("."==$file)||(".."==$file))                                            // skip current directory and directory above
    continue; 
  if(fnmatch($loggedUser.".gif",$file)||fnmatch($loggedUser.".jpg",$file)||fnmatch($loggedUser.".png",$file))
  { echo "<p align=\"center\">";
	  echo "<img class=\"account\" src=\"".$baseURL.$upload_dir."/".$file."\" alt=\"".$loggedUser."/".$file."\" alt=\"\"></img>";
		echo "</p>";
	}
}
echo "<p>&nbsp;</p>";
echo "</div>";
}
?>
