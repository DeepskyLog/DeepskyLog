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
	$tempLocationList="<select name=\"site\" style=\"width: 50%\" class=\"inputfield form-control\">";
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
	$tempInstrumentList="<select name=\"instrument\" style=\"width: 50%\" class=\"inputfield form-control\">";
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
	$tempAtlasList="<select name=\"atlas\" style=\"width: 50%\" class=\"inputfield form-control\">";
	while(list($key,$value)=each($objAtlas->atlasCodes))
	  $tempAtlasList.="<option ".(($key==$theAtlasKey)?"selected=\"selected\"":"")." value=\"$key\">" . $value . "</option>";
	$tempAtlasList.="</select>";

	$tempLangList="<select name=\"language\" style=\"width: 50%\" class=\"inputfield form-control\">";
	$languages=$objLanguage->getLanguages();
	while(list($key,$value)=each($languages))
	  $tempLangList.="<option value=\"".$key."\"".(($objObserver->getObserverProperty($loggedUser,'language')==$key)?" selected=\"selected\"":"").">".$value."</option>";
	$tempLangList.="</select>";

	$allLanguages=$objLanguage->getAllLanguages($objObserver->getObserverProperty($loggedUser,'language'));
	$tempAllLangList="<select name=\"description_language\" style=\"width: 50%\" class=\"inputfield form-control\">";
	while(list($key,$value)=each($allLanguages))
	  $tempAllLangList.="<option value=\"".$key."\"".(($objObserver->getObserverProperty($loggedUser,'observationlanguage') == $key)?" selected=\"selected\"":"").">".$value."</option>";
	$tempAllLangList.="</select>";
	$_SESSION['alllanguages']=$allLanguages;
	$usedLanguages=$objObserver->getUsedLanguages($loggedUser);

	// =================================================================================================PAGE OUTPUT

	echo "<div>";
	echo "<form class=\"form-horizontal\" role=\"form\" action=\"".$baseURL."index.php\" enctype=\"multipart/form-data\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\" />";

	echo "<h4>" . LangChangeAccountTitle . LangChangeAccountTitleFor .
                $objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'firstname') . " " .
                $objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'name') . "</h4>";
	$content="<input class=\"pull-right btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";
	echo $content;

	echo "<br />";

  // We make some tabs.
  echo "<ul id=\"tabs\" class=\"nav nav-tabs\" data-tabs=\"tabs\">
          <li class=\"active\"><a href=\"#info\" data-toggle=\"tab\">" . PersonalInfo . "</a></li>
          <li><a href=\"#observingDetails\" data-toggle=\"tab\">" . ObservingDetails . "</a></li>
          <li><a href=\"#atlases\" data-toggle=\"tab\">" . Atlases . "</a></li>
          <li><a href=\"#languages\" data-toggle=\"tab\">" . Languages . "</a></li>
        </ul>";

  echo "<div id=\"my-tab-content\" class=\"tab-content\">";
  echo "<div class=\"tab-pane active\" id=\"info\">";

  echo "<br />";

  $upload_dir = 'common/observer_pics';
	$dir = opendir($instDir.$upload_dir);

  echo "<label class=\"control-label\">" . LangChangeAccountPicture . "</label>
        <input id=\"images\" name=\"image\" type=\"file\" data-show-remove=\"false\" accept=\"image/*\" class=\"file-loading\">";

  // Check existence of avatar for the observer
  $imaLocation = $baseURL."/images/noAvatar.jpg";
  $oldFile = '';
  while (FALSE!==($file=readdir($dir)))
  { if(("."==$file)||(".."==$file))                                            // skip current directory and directory above
    continue;
  	if(fnmatch($loggedUser.".gif",$file)||fnmatch($loggedUser.".jpg",$file)||fnmatch($loggedUser.".png",$file))
  	{
      $oldFile = $upload_dir."/".$file;
  	  $imaLocation = $baseURL.$upload_dir."/".$file;
  	}
  }
  echo "<input id=\"oldFile\" name=\"oldFile\" value=\"" . $oldFile . "\" type=\"hidden\">";

  // The javascript for the fileinput plugins
  echo "<script type=\"text/javascript\">";
  echo "$(document).on(\"ready\", function() {
  			$(\"#images\").fileinput({
  					initialPreview: [
  						// Show the correct file.
  						'<img src=\"" . $imaLocation . "\" class=\"file-preview-image\">'
  					],
            maxFileCount: 1,
            validateInitialCount: true,
  					overwriteInitial: true,
            maxImageWidth: 500,
            resizeImage: true,
            autoReplace: true,
            showRemove: false,
            showUpload: false,
            removeLabel: '',
            removeIcon: '',
            removeTitle: '',
            layoutTemplates: {actionDelete: ''},
            allowedFileTypes: [\"image\"],
  					initialCaption: \"Profile picture\",
  			});
  		});";
  echo "</script>";


  echo "<br /><br />";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField1 . "</label>";
	echo "<div class=\"col-sm-3\">
			    <input type=\"text\" required disabled class=\"inputfield form-control requiredField\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkSessionKey('deepskylog_id')."\" />
        </div>";
  echo "<div class=\"col-sm-3\">
          <button type=\"button\" class=\"btn btn-danger\" data-toggle=\"modal\" data-target=\"#changePassword\">" . LangChangePassword . "</button>
        </div>";
	echo "<p class=\"form-control-static\">" .
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

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField12 . "</label>";
	echo "<div class=\"col-sm-6 form-inline\">" .
	    "<input type=\"number\" min=\"-5.0\" max=\"5.0\" step=\"0.1\" class=\"inputfield centered form-control\" maxlength=\"4\" name=\"fstOffset\" size=\"4\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'fstOffset')."\" />";
	echo "</div><p class=\"form-control-static\">" .
			  LangChangeAccountField12Expl . "</p></div>";

  // The copyright / license settings.
  $copyright = $objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'copyright');
  $ownLicense = true;

  // javascript to disable the copyright field when one of the CC options is selected.
  echo '<script>
          function enableDisableCopyright() {
            var selectBox = document.getElementById("cclicense");
            var selectedValue = selectBox.options[selectBox.selectedIndex].value;
            if (selectedValue == 7) {
              document.getElementById("copyright").disabled=false;
            } else {
              document.getElementById("copyright").disabled=true;
            }
          }
        </script>';

  echo '<div class="form-group">
          <label class="col-sm-2 control-label">' . LangCCLicense . '</label>
          <div class="col-sm-6">
            <select name="cclicense" id="cclicense" onchange="enableDisableCopyright();" class="inputfield form-control">';
  echo '<option value="0"';
  if (strcmp($copyright, "Attribution CC BY") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>Attribution CC BY</option>';

  echo '<option value="1"';
  if (strcmp($copyright, "Attribution-ShareAlike CC BY-SA") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>Attribution-ShareAlike CC BY-SA</option>';

  echo '<option value="2"';
  if (strcmp($copyright, "Attribution-NoDerivs CC BY-ND") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>Attribution-NoDerivs CC BY-ND</option>';

  echo '<option value="3"';
  if (strcmp($copyright, "Attribution-NonCommercial CC BY-NC") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>Attribution-NonCommercial CC BY-NC</option>';

  echo '<option value="4"';
  if (strcmp($copyright, "Attribution-NonCommercial-ShareAlike CC BY-NC-SA") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>Attribution-NonCommercial-ShareAlike CC BY-NC-SA</option>';

  echo '<option value="5"';
  if (strcmp($copyright, "Attribution-NonCommercial-NoDerivs CC BY-NC-ND") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>Attribution-NonCommercial-NoDerivs CC BY-NC-ND</option>';

  echo '<option value="6"';
  if (strcmp($copyright, "") == 0) {
    $ownLicense = false;
    echo ' selected="selected"';
  }
  echo '>' . LangNoLicense . '</option>';

  echo '<option value="7"';
  if ( $ownLicense ) {
    echo ' selected="selected"';
  }
  echo '>' . LangOwnLicense . '</option>';

  echo '    </select>
          </div>
          <p class="form-control-static">' .
            LangSelectLicenseInfo . '
          </p>
        </div>';
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountCopyright . "</label>";
	echo "<div class=\"col-sm-6\">" .
         "<input type=\"text\" id=\"copyright\" class=\"inputfield form-control\" maxlength=\"128\" name=\"copyright\" size=\"40\" value=\"". $copyright ."\" />";
	echo "</div></div>";

	echo "<p>&nbsp;</p>";

  echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";

  echo "</div>";

  echo "<div class=\"tab-pane\" id=\"observingDetails\">";

  echo "<br />";
  echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField7 . "</label>";
	echo "<div class=\"col-sm-6\">" . $tempLocationList;
	echo "</div><p class=\"form-control-static\">" .
			"<a href=\"".$baseURL."index.php?indexAction=add_location\">".LangChangeAccountField7Expl."</a>" . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField8 . "</label>";
	echo "<div class=\"col-sm-6\">" . $tempInstrumentList;
	echo "</div><p class=\"form-control-static\">" .
			 "<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>" . "</p></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField9 . "</label>";
	echo "<div class=\"col-sm-6\">" . $tempAtlasList;
	echo "</div></div>";

	$showInches = $objObserver->getObserverProperty ( $loggedUser, "showInches" );
	$inchSelected = ($showInches == '1')?"selected":"";
	$mmSelected = ($showInches == '0')?"selected":"";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField14 . "</label>";
	echo "<div class=\"col-sm-6\">";
	echo "<select name=\"showInches\" class=\"form-control\"" . $disabled . " >";
	echo "<option ".$inchSelected." value='1'>inch</option>";
	echo "<option ".$mmSelected." value='0'>mm</option>";
	echo "</select>";
	echo "</div></div>";

    echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";

  echo "</div>";

  echo "<div class=\"tab-pane\" id=\"atlases\">";
  echo "<br />";
  echo profilefovmagnitude;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profilefovmagnitudeselect . "</label>";
	echo "<div class=\"col-sm-2 form\">" .
			   "<input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" name=\"overviewFoV\" value=\"".$objObserver->getObserverProperty($loggedUser,'overviewFoV')."\" /></div>".
	       "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" name=\"lookupFoV\" value=\"".$objObserver->getObserverProperty($loggedUser,'lookupFoV')."\" /></div>".
	       "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" name=\"detailFoV\" value=\"".$objObserver->getObserverProperty($loggedUser,'detailFoV')."\" /></div>";
	echo "</div>";

	echo profiledsosmagnitude;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profiledsosmagnitudeselect . "</label>";
	echo "<div class=\"col-sm-2 form\">" .
        " <input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" name=\"overviewdsos\" value=\"".$objObserver->getObserverProperty($loggedUser,'overviewdsos')."\" /></div>".
	      "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" name=\"lookupdsos\" value=\"".$objObserver->getObserverProperty($loggedUser,'lookupdsos')."\" /></div>".
	      "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" name=\"detaildsos\" value=\"".$objObserver->getObserverProperty($loggedUser,'detaildsos')."\" />";
	echo "</div></div>";

	echo profilestarsmagnitude;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profilestarsmagnitudeselect . "</label>";
	echo "<div class=\"col-sm-2 form\">" .
        "<input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" name=\"overviewstars\" value=\"".$objObserver->getObserverProperty($loggedUser,'overviewstars')."\" /></div>".
        "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" name=\"lookupstars\" value=\"".$objObserver->getObserverProperty($loggedUser,'lookupstars')."\" /></div>".
        "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1.00\" max=\"20.0\" step=\"0.1\" class=\"inputfield centered form-control\" name=\"detailstars\" value=\"".$objObserver->getObserverProperty($loggedUser,'detailstars')."\" />";
	echo "</div></div>";

	echo profilephotosizes;
	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . profilephotosizesselect . "</label>";
	echo "<div class=\"col-sm-2 form\">" .
			  "<input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" name=\"photosize1\" value=\"".$objObserver->getObserverProperty($loggedUser,'photosize1')."\" /></div>".
	      "<div class=\"col-sm-2 form\"><input type=\"number\" min=\"1\" max=\"3600\" class=\"inputfield centered form-control\" name=\"photosize2\" value=\"".$objObserver->getObserverProperty($loggedUser,'photosize2')."\" />";
	echo "</div></div>";

	echo "<div class=\"form-group\">";
	echo "<label class=\"col-sm-2 control-label\">" . AtlasPageFont . "</label>";
	echo "<div class=\"col-sm-2 form\">" .
         "<input type=\"number\" min=\"6\" max=\"9\" class=\"inputfield centered form-control\" maxlength=\"1\" name=\"atlaspagefont\" size=\"5\" value=\"".$objObserver->getObserverProperty($loggedUser,'atlaspagefont')."\" />";
	echo "</div></div>";

  echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";

  echo "</div>";

  echo "<div class=\"tab-pane\" id=\"languages\">";
  echo "<br />";
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
	echo "<table class=\"table table-condensed borderless\">";

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

  echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangChangeAccountButton."\" />";

  echo "</div>";

  echo "</div>";

  echo "</div></form>";

  echo "<div class=\"modal fade\" id=\"changePassword\">
        <div class=\"modal-dialog\">
         <div class=\"modal-content\">
          <div class=\"modal-header\">
           <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
           <h4 class=\"modal-title\">" . LangChangePassword . "</h4>
          </div>
          <div class=\"modal-body\">
           <!-- Ask for the name of the list. -->
           <form action=\"".$baseURL."index.php?indexAction=changepassword\" method=\"post\">
             <input type=\"hidden\" name=\"userid\" value=\"" . $loggedUser . "\" />" .
             LangCurrentPassword . "
             <input type=\"password\" name=\"currentPassword\" class=\"strength\" required autofocus data-show-meter=\"false\">" .
             LangNewPassword . "
             <input type=\"password\" name=\"newPassword\" class=\"strength\" required>" .
             LangChangeAccountField6 . "
             <input type=\"password\" name=\"confirmPassword\" class=\"strength\" required data-show-meter=\"false\">
             <br /><br />
            </div>
            <div class=\"modal-footer\">
            <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>
            <input class=\"btn btn-danger\" type=\"submit\" name=\"changePassword\" value=\"" . LangChangePassword . "\" />
		      </form>
          </div>
         </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
       </div><!-- /.modal -->";


}
?>
