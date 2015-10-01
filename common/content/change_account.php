<?php
// change_account.php
// allows the user to view and change his account's details

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangException001);
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(LangException012);
else change_account();

function change_account()
{ global $baseURL,$instDir,$languageMenu,$loggedUser,
         $objAtlas,$objInstrument,$objLanguage,$objLocation,$objObserver,$objPresentations,$objUtil;
  $sites = $objLocation->getSortedLocations("name", $loggedUser);
	$tempLocationList="<select name=\"site\" class=\"inputfield form-control\">";
	$tempLocationList.= "<option value=\"0\">-----</option>";
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
	for($i=0;$i<count($sites);$i++)
	{ $sitename = $objLocation->getLocationPropertyFromId($sites[$i],'name');
	  $tempLocationList.="<option ".(($objObserver->getObserverProperty($loggedUser,'stdlocation')==$sites[$i])?" selected=\"selected\"":"")." value=\"".$sites[$i]."\">".$sitename."</option>";
	}
	$tempLocationList.="</select>";
	$tempInstrumentList="<select name=\"instrument\" class=\"inputfield form-control\">";
	$tempInstrumentList.= "<option value=\"0\">-----</option>";
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
	$tempAtlasList="<select name=\"atlas\" class=\"inputfield form-control\">";
	while(list($key,$value)=each($objAtlas->atlasCodes))
	  $tempAtlasList.="<option ".(($key==$theAtlasKey)?"selected=\"selected\"":"")." value=\"$key\">" . $value . "</option>";
	$tempAtlasList.="</select>";

	$tempLangList="<select name=\"language\" class=\"inputfield form-control\">";
	$languages=$objLanguage->getLanguages();
	while(list($key,$value)=each($languages))
	  $tempLangList.="<option value=\"".$key."\"".(($objObserver->getObserverProperty($loggedUser,'language')==$key)?" selected=\"selected\"":"").">".$value."</option>";
	$tempLangList.="</select>";

	$allLanguages=$objLanguage->getAllLanguages($objObserver->getObserverProperty($loggedUser,'language'));
	$tempAllLangList="<select name=\"description_language\" class=\"inputfield form-control\">";
	while(list($key,$value)=each($allLanguages))
	  $tempAllLangList.="<option value=\"".$key."\"".(($objObserver->getObserverProperty($loggedUser,'observationlanguage') == $key)?" selected=\"selected\"":"").">".$value."</option>";
	$tempAllLangList.="</select>";
	$_SESSION['alllanguages']=$allLanguages;
	$usedLanguages=$objObserver->getUsedLanguages($loggedUser);

	// =================================================================================================PAGE OUTPUT

	echo "<div>";
	echo "<form class=\"form-horizontal\" role=\"form\" action=\"".$baseURL."index.php\" enctype=\"multipart/form-data\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\" />";

	echo "<h4>" . LangChangeAccountTitle . "</h4>";
	$content="<input class=\"pull-right btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";
	echo $content;

	echo "<br />";
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField1 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"text\" required class=\"inputfield form-control requiredField\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkSessionKey('deepskylog_id')."\" />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField1Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField2 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"email\" required class=\"inputfield form-control requiredField\" maxlength=\"80\" name=\"email\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'email')."\" />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField2Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField3 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"text\" required class=\"inputfield form-control requiredField\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'firstname')."\" />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField3Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField4 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"text\" required class=\"inputfield form-control requiredField\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'name')."\" />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField4Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField5 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"password\" required class=\"inputfield form-control requiredField\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"\" />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField5Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField6 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"password\" required class=\"inputfield form-control requiredField\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"\" />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField6Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountSendMail . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"checkbox\" class=\"inputfield\" name=\"send_mail\"".(($objObserver->getObserverProperty($loggedUser,'sendMail'))?"checked":"")." />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountSendMailExpl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField11 . "</label>";
	echo "<div class=\"col-sm-6\">
			<input type=\"checkbox\" class=\"inputfield\" name=\"local_time\"".(($objObserver->getObserverProperty($loggedUser,'UT'))?"":"checked")." />";
	echo "</div><p class=\"form-control-static\">" .
        LangChangeAccountField11Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField10 . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">
			<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"icq_name\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'icqname')."\" />";
	echo "</div><p class=\"form-control-static\">" .
			LangChangeAccountField10Expl . "</p></div>";

	echo "<br />";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField7 . "</label>";
	echo "<div class=\"col-sm-6\">" . $tempLocationList;
	echo "</div><p class=\"form-control-static\">" .
			"<a href=\"".$baseURL."index.php?indexAction=add_site\">".LangChangeAccountField7Expl."</a>" . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField8 . "</label>";
	echo "<div class=\"col-sm-6\">" . $tempInstrumentList;
	echo "</div><p class=\"form-control-static\">" .
			 "<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>" . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField9 . "</label>";
	echo "<div class=\"col-sm-6\">" . $tempAtlasList;
	echo "</div></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField12 . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
	    "<input type=\"number\" min=\"-5.0\" max=\"5.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"4\" name=\"fstOffset\" size=\"4\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'fstOffset')."\" />";
	echo "</div><p class=\"form-control-static\">" .
			  LangChangeAccountField12Expl . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountPicture . "</label>";
	echo "<div class=\"col-sm-6\"><p class=\"form-control-static\">" .
	    "<input type=\"file\" name=\"picture\" class=\"inputfield tour6\"/>";
	echo "</p></div></div>";


	echo profilefovmagnitude;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profilefovmagnitudeselect . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
			   " <input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"overviewFoV\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'overviewFoV')."\" />".
	       " / <input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"lookupFoV\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'lookupFoV')."\" />".
	       " / <input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"detailFoV\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'detailFoV')."\" />";
	echo "</div></div>";

	echo profiledsosmagnitude;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profiledsosmagnitudeselect . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
        " <input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"overviewdsos\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'overviewdsos')."\" />".
	      " / <input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"lookupdsos\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'lookupdsos')."\" />".
	      " / <input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"detaildsos\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'detaildsos')."\" />";
	echo "</div></div>";

	echo profilestarsmagnitude;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profilestarsmagnitudeselect . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
        "<input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"overviewstars\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'overviewstars')."\" />".
        " / <input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"lookupstars\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'lookupstars')."\" />".
        " / <input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"detailstars\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'detailstars')."\" />";
	echo "</div></div>";

	echo profilephotosizes;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profilephotosizesselect . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
			  "<input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"photosize1\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'photosize1')."\" />".
	      " / <input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" maxlength=\"5\" name=\"photosize2\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'photosize2')."\" />";
	echo "</div></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . AtlasPageFont . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
         "<input type=\"number\" min=\"6\" max=\"9\" class=\"inputfield centered form-control\" maxlength=\"1\" name=\"atlaspagefont\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'atlaspagefont')."\" />";
	echo "</div></div>";

	echo "<br />";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountCopyright . "</label>";
	echo "<div class=\"col-sm-6\">" .
         "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"128\" name=\"copyright\" size=\"40\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'copyright')."\" />";
	echo "</div></div>";

	if($languageMenu==1) {
	  echo "<div class=\"form-group\">";
	  echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountLanguage . "</label>";
	  echo "<div class=\"col-sm-6\">" .
           $tempLangList;
	  echo "</div><p class=\"form-control-static\">" . LangChangeAccountLanguageExpl . "</p></div>";
	}
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountObservationLanguage . "</label>";
	echo "<div class=\"col-sm-6\">" .
			$tempAllLangList;
	echo "</div><p class=\"form-control-static\">" . LangChangeAccountObservationLanguageExpl . "</p></div>";
	reset($allLanguages);

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeVisibleLanguagesExpl . "</label>";
	echo "<div class=\"col-sm-6\">";
	echo "<table class=\"table table-condensed table-bordered\">";

	$j = 0;
	echo "<tr>";
	while((list($key,$value)=each($allLanguages)))
	{ echo "<td><label class=\"checkbox-inline\"><input type=\"checkbox\" ".(in_array($key,$usedLanguages)?"checked=\"checked\"":"")." name=\"".$key."\" value=\"".$key."\" />".$value."</label></td>";
	  if (($j + 1) % 3 == 0) {
	  	echo "</tr><tr>";
	  }
	  $j++;
	}
	for ($i = $j % 3;$i < 3;$i++) {
		echo "<td></td>";
	}
	echo "</tr></table></div></div>";

	$content="<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";
	echo $content;

	echo "</div></form>";
	$upload_dir = 'common/observer_pics';
	$dir = opendir($instDir.$upload_dir);
	while (FALSE!==($file=readdir($dir)))
	{ if(("."==$file)||(".."==$file))                                            // skip current directory and directory above
	    continue;
	  if(fnmatch($loggedUser.".gif",$file)||fnmatch($loggedUser.".jpg",$file)||fnmatch($loggedUser.".png",$file))
	  { echo "<div class=\"row text-center\">";
		  echo "<img class=\"img-thumbnail account\" src=\"".$baseURL.$upload_dir."/".$file."\" alt=\"".$loggedUser."/".$file."\"></img>";
			echo "</div>";
		}
	}
	echo "<p>&nbsp;</p>";
	echo "</div>";
}
?>
