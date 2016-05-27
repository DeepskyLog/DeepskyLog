<?php
// new_session.php
// allows the user to add a new observing session
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	new_session ();
function new_session() {
	global $baseURL, $loggedUserName, $objSession, $loggedUser, $objObserver, $objLocation, $objPresentations, $objUtil, $objLanguage;

	// Javascript to add a user
	echo " <script type=\"text/javascript\" >";
	echo " function addUser(elm,user)";
	echo " {";
	echo "  var userToAdd = elm.value;";
	echo "  var w = document.forms[\"sessionForm\"].addObserver.selectedIndex;";
	echo "  var selected_text = document.forms[\"sessionForm\"].addObserver.options[w].text;";

	// Remove observer from list of observers
	echo "  var elSel = document.getElementById('addObserver');";
	echo "  var i;";
	echo "  for (i = elSel.length - 1; i>0; i--) {";
	echo "    if (elSel.options[i].selected) {";
	echo "      elSel.remove(i);";
	echo "    }";
	echo "  }";

	// Add to list of deletable observers
	echo "  var newOption = document.createElement('option');";
	echo "  newOption.text = selected_text;";
	echo "  newOption.value = userToAdd;";
	echo "  var elSel = document.getElementById('deleteObserver');";
	echo "  try {";
	echo "    elSel.add(newOption, null);"; // standards compliant; doesn't work in IE
	echo "  }";
	echo "  catch(ex) {";
	echo "    elSel.add(newOption);"; // IE only
	echo "  }	";

	echo "  document.forms[\"sessionForm\"].observers.value = user;";
	echo "  for (i = 1;i < elSel.length;i++) {";
	echo "    document.forms[\"sessionForm\"].observers.value += \"\\n\" + document.forms[\"sessionForm\"].deleteObserver.options[i].text;";
	echo "  }";
	echo "  var div1 = document.createElement('div');";
	// Get template data
	echo "  div1.innerHTML = \"<input type='hidden' name='addedObserver[]' value='\" + userToAdd + \"' />\";";
	// append to our form, so that template data become part of form
	echo "  document.getElementById('newlink').appendChild(div1);";
	echo " }";
	echo "	</script>";

	// Javascript to delete a user
	echo " <script type=\"text/javascript\" >";
	echo " function deleteUser(elm2,user)";
	echo " {";
	echo "  var userToDelete = elm2.value;";
	echo "  var w = document.forms[\"sessionForm\"].deleteObserver.selectedIndex;";
	echo "  var selected_text = document.forms[\"sessionForm\"].deleteObserver.options[w].text;";
	// Remove observer from list of observers
	echo "  var elSel = document.getElementById('deleteObserver');";
	echo "  var i;";
	echo "  for (i = elSel.length - 1; i>0; i--) {";
	echo "    if (elSel.options[i].selected) {";
	echo "      elSel.remove(i);";
	echo "    }";
	echo "  }";

	echo "  var div1 = document.createElement('div');";
	// Get template data
	echo "  div1.innerHTML = \"<input type='hidden' name='deletedObserver[]' value='\" + userToDelete + \"' />\";";
	// append to our form, so that template data become part of form
	echo "  document.getElementById('newlink').appendChild(div1);";

	// Add to list of observers
	echo "  var newOption = document.createElement('option');";
	echo "  newOption.text = selected_text;";
	echo "  newOption.value = userToDelete;";
	echo "  var elSel = document.getElementById('addObserver');";
	echo "  try {";
	echo "    elSel.add(newOption, null);"; // standards compliant; doesn't work in IE
	echo "  }";
	echo "  catch(ex) {";
	echo "    elSel.add(newOption);"; // IE only
	echo "  }	";
	// Make the text
	echo "  document.forms[\"sessionForm\"].observers.value = user;";
	echo "  var elSel = document.getElementById('deleteObserver');";
	echo "  for (i = 1;i < elSel.length;i++) {";
	echo "    document.forms[\"sessionForm\"].observers.value += \"\\n\" + document.forms[\"sessionForm\"].deleteObserver.options[i].text;";
	echo "  }";
	echo " }";
	echo "	</script>";

	echo "<div id=\"main\">";
	// When there are sessions added by another observer, where the observer is co-observer,
	// we should first see a list with sessions.
	// Get the list with sessions for this observer, which are not yet active
	$listWithSessions = $objSession->getListWithInactiveSessions ( $loggedUser );
	if (count ( $listWithSessions ) > 0) {
		echo "<h4>" . LangAddExistingSessionTitle . "</h4>";
		echo "<hr />";
		$objSession->showInactiveSessions ( $loggedUser );
	}

	echo "<h4>" . LangAddSessionTitle . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" id=\"sessionForm\" enctype=\"multipart/form-data\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_session\" />";

	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . LangAddSessionButton . "\" />";
    echo "<br />";
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField1 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"sessionname\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'sessionname' ) ) . stripslashes ( $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'name' ) ) . "\" />";
	echo "<span class=\"help-block\">" . LangAddSessionField1Expl . "</span>";
	echo "</div>";

	// Add the begindate field
	$yesterday = date ( 'Ymd', strtotime ( '-1 day' ) );
	$theYear = substr ( $yesterday, 0, 4 );
	$theMonth = substr ( $yesterday, 4, 2 );
	$theDay = substr ( $yesterday, 6, 2 );
	$theHour = "22";
	$theMinute = "00";

	$contentBeginDate = "<input type=\"number\" min=\"1\" max=\"31\" required class=\"form-control\" maxlength=\"2\" size=\"4\"  name=\"beginday\" id=\"beginday\" value=\"" . $theDay . "\" onkeypress=\"return checkPositiveInteger(event);\" />";
	$contentBeginDate .= "&nbsp;&nbsp;";
	$contentBeginDate .= "<select name=\"beginmonth\" required id=\"beginmonth\" class=\"form-control\">";
	for($i = 1; $i < 13; $i ++)
		$contentBeginDate .= "<option value=\"" . $i . "\"" . (($theMonth == $i) ? " selected=\"selected\"" : "") . ">" . $GLOBALS ['Month' . $i] . "</option>";
	$contentBeginDate .= "</select>";
	$contentBeginDate .= "&nbsp;&nbsp;";
	$contentBeginDate .= "<input type=\"number\" min=\"1500\" max=\"2200\" required class=\"form-control\" maxlength=\"4\" size=\"6\" name=\"beginyear\" id=\"beginyear\" onkeypress=\"return checkPositiveInteger(event);\" value=\"" . $theYear . "\" />";
	$contentBeginTime = "<input type=\"number\" min=\"0\" max=\"23\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"beginhours\" value=\"" . $theHour . "\" />";
	$contentBeginTime .= "&nbsp;&nbsp;";
	$contentBeginTime .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"beginminutes\" value=\"" . $theMinute . "\" />&nbsp;&nbsp;";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField2 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $contentBeginDate . "&nbsp;" . $contentBeginTime;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField2Expl . "</span>";
	echo "</div>";

	// End date field
	$today = date ( 'Ymd', strtotime ( 'today' ) );
	$theYear = substr ( $today, 0, 4 );
	$theMonth = substr ( $today, 4, 2 );
	$theDay = substr ( $today, 6, 2 );
	$theHour = "02";
	$theMinute = "00";

	$contentEndDate = "<input type=\"number\" min=\"1\" max=\"31\" required class=\"form-control\" maxlength=\"2\" size=\"4\"  name=\"endday\" id=\"endday\" value=\"" . $theDay . "\" onkeypress=\"return checkPositiveInteger(event);\" />";
	$contentEndDate .= "&nbsp;&nbsp;";
	$contentEndDate .= "<select name=\"endmonth\" id=\"endmonth\" required class=\"form-control\">";
	for($i = 1; $i < 13; $i ++)
		$contentEndDate .= "<option value=\"" . $i . "\"" . (($theMonth == $i) ? " selected=\"selected\"" : "") . ">" . $GLOBALS ['Month' . $i] . "</option>";
	$contentEndDate .= "</select>";
	$contentEndDate .= "&nbsp;&nbsp;";
	$contentEndDate .= "<input type=\"number\" min=\"1500\" max=\"2200\" required class=\"form-control\" maxlength=\"4\" size=\"6\" name=\"endyear\" id=\"endyear\" onkeypress=\"return checkPositiveInteger(event);\" value=\"" . $theYear . "\" />";
	$contentEndTime = "<input type=\"number\" min=\"0\" max=\"23\" class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"endhours\" value=\"" . $theHour . "\" />";
	$contentEndTime .= "&nbsp;&nbsp;";
	$contentEndTime .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"endminutes\" value=\"" . $theMinute . "\" />&nbsp;&nbsp;";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField3 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $contentEndDate . "&nbsp;" . $contentEndTime;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField3Expl . "</span>";
	echo "</div>";

	// Location of the session
	$sites = $objLocation->getSortedLocationsList ( "name", $loggedUser, 1 );
	// Get the standard location here!
	$theLoc = $objObserver->getObserverProperty ( $loggedUser, "stdlocation" );
	$contentLoc = "<select required class=\"form-control\" name=\"site\">";
	while ( list ( $key, $value ) = each ( $sites ) )
		$contentLoc .= "<option " . (($value [0] == $theLoc) ? "selected=\"selected\"" : '') . " value=\"" . $value [0] . "\">" . $value [1] . "</option>";
	$contentLoc .= "</select>&nbsp;";

	echo "<div class=\"form-group\">
	       <label>" . "<a href=\"" . $baseURL . "index.php?indexAction=add_location\" title=\"" . LangChangeAccountField7Expl . "\" >" . LangAddSessionField4 . "</a>" . "</label>";
	echo "<div class=\"form-inline\">";
	echo $contentLoc;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField4Expl . "</span>";
	echo "</div>";

	// Language
	$theLanguage = $objObserver->getObserverProperty ( $loggedUser, 'observationlanguage' );
	$allLanguages = $objLanguage->getAllLanguages ( $objObserver->getObserverProperty ( $loggedUser, 'language' ) );
	$contentLanguage = "<select name=\"description_language\"  class=\"form-control\">";
	while ( list ( $key, $value ) = each ( $allLanguages ) )
		$contentLanguage .= "<option value=\"" . $key . "\"" . (($theLanguage == $key) ? "selected=\"selected\"" : '') . ">" . $value . "</option>";
	$contentLanguage .= "</select>&nbsp;";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField8 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $contentLanguage;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField8Expl . "</span>";
	echo "</div>";

	// Other observers
	// First add the loggedUser
	$observersCont = "<textarea maxlength=\"5000\" readonly=\"readonly\" class=\"form-control\" id=\"observers\" rows=\"7\" cols=\"50\">";
	$observersCont .= $objObserver->getObserverProperty ( $loggedUser, "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $loggedUser, "name" );
	$observersCont .= "</textarea>";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField9 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $observersCont;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField9Expl . "</span>";
	echo "</div>";

	// Add observer
	$addObserver = "<select style=\"width: 50%\" id=\"addObserver\" name=\"addObserver\" onchange=\"addUser(this,'" . $loggedUserName . "')\" class=\"form-control\">";
	$obs = $objObserver->getPopularObserversByName ();

	$addObserver .= "<option value=\"\">&nbsp;</option>";
	while ( list ( $key, $value ) = each ( $obs ) ) {
		if ($key != $loggedUser) {
			$addObserver .= "<option value=\"" . $key . "\">" . $value . "</option>";
		}
	}
	$addObserver .= "</select>";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField10 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $addObserver;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField10Expl . "</span>";
	echo "</div>";

	// Delete observer
	$deleteObserver = "<select style=\"width: 50%\" id=\"deleteObserver\" name=\"deleteObserver\" onchange=\"deleteUser(this,'" . $loggedUserName . "')\" class=\"form-control\">";

	$deleteObserver .= "<option value=\"\">&nbsp;</option>";
	$deleteObserver .= "</select>";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField11 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $deleteObserver;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField11Expl . "</span>";
	echo "</div>";

	// Hidden field with all the observers
	// First the loggedUser
	echo "<div id=\"newlink\">";
	echo "  <div class=\"observer\">";
	echo "     <input type=\"hidden\" name=\"addedObserver[]\" value=\"" . $loggedUser . "\" />";
	echo "  </div>";
	echo "</div>";

	// Weather
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField5 . "</label>";
	echo "<textarea maxlength=\"500\" name=\"weather\"  class=\"form-control\" cols=\"50\" rows=\"7\">" . "</textarea>";
	echo "<span class=\"help-block\">" . LangAddSessionField5Expl . "</span>";
	echo "</div>";

	// Equipment
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField6 . "</label>";
	echo "<textarea maxlength=\"500\" name=\"equipment\"  class=\"form-control\" cols=\"50\" rows=\"7\">" . "</textarea>";
	echo "<span class=\"help-block\">" . LangAddSessionField6Expl . "</span>";
	echo "</div>";

	// Comments
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField7 . "</label>";
	echo "<textarea name=\"comments\"  class=\"form-control\" maxlength=\"5000\" cols=\"50\" rows=\"7\">" . "</textarea>";
	echo "<span class=\"help-block\">" . LangAddSessionField7Expl . "</span>";
	echo "</div>";

	// Pictures
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField12 . "</label>";
	echo "<div class=\"form\">";
	echo "<input type=\"file\" id=\"picture\" name=\"picture\" data-show-remove=\"false\" accept=\"image/*\" class=\"file-loading\"/>";

	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField12Expl . "</span>";
	echo "</div>";

	echo "<input type=\"submit\" class=\"btn btn-success\" name=\"add\" value=\"" . LangAddSessionButton . "\" />";

	echo "</div></form>";
	echo "</div>";

	echo "<script type=\"text/javascript\">";
	echo "$(document).on(\"ready\", function() {
				$(\"#picture\").fileinput({
					  maxFileCount: 1,
						validateInitialCount: true,
						autoReplace: true,
						showRemove: false,
						showUpload: false,
						removeLabel: '',
						removeIcon: '',
						removeTitle: '',
						layoutTemplates: {actionDelete: ''},
						allowedFileTypes: [\"image\"],
				});
			});";
	echo "</script>";

}
?>
