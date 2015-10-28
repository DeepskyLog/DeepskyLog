<?php
// change_session.php
// allows the user to change an observing session
global $loggedUser;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($sessionid = $objUtil->checkGetKey ( 'sessionid' )))
	throw new Exception ( LangException003 );
elseif (! ($objSession->getSessionPropertyFromId ( $sessionid, 'name' )))
	throw new Exception ( "Session not found in change_session.php, please contact the developers with this message:" . $sessionid );
elseif (strcmp ( $objSession->getSessionPropertyFromId ( $sessionid, 'observerid' ), $loggedUser ) == 0 && isset ( $_GET ['adapt'] ))
	change_session ();
else
	view_session ();
function change_session() {
	global $baseURL, $loggedUserName, $objSession, $loggedUser, $objObserver, $objLocation, $objPresentations, $objUtil, $objLanguage, $instDir;

	echo "	<script type=\"text/javascript\" >";
	echo "	var calBegin = new CalendarPopup();";
	echo "  function SetObsDateBegin(y,m,d)";
	echo "  {";
	echo "    document.getElementById('beginday').value = d;";
	echo "    document.getElementById('beginmonth').value = m;";
	echo "    document.getElementById('beginyear').value = y;";
	echo "	}";
	echo "	</script>";
	echo "	<script type=\"text/javascript\" >";
	echo "	var calEnd = new CalendarPopup();";
	echo "  function SetObsDateEnd(y,m,d)";
	echo "  {";
	echo "    document.getElementById('endday').value = d;";
	echo "    document.getElementById('endmonth').value = m;";
	echo "    document.getElementById('endyear').value = y;";
	echo "	}";
	echo "	</script>";
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
	echo "<h4>" . LangChangeSessionTitle . "</h4>";
	echo "<hr />";
	echo "<form id=\"sessionForm\" enctype=\"multipart/form-data\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_session\" />";
	echo "<input type=\"hidden\" name=\"sessionid\" value=\"" . $objUtil->checkRequestKey ( 'sessionid' ) . "\" />";
	if ($objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), "active" ) == 0) {
		$sessionButton = LangAddSessionButton;
	} else {
		$sessionButton = LangChangeSessionButton;
	}

	echo "<span class=\"pull-right\">";
	echo "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=validate_delete_existingsession&amp;sessionid=" . urlencode ( $_GET ['sessionid'] ) . "\">" . LangRemove . "</a>&nbsp;";
	echo "<input class=\"btn btn-primary\" type=\"submit\" name=\"add\" value=\"" . $sessionButton . "\" />&nbsp;";
	echo "</span><br />";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField1 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"sessionname\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'sessionname' ) ) . stripslashes ( $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'name' ) ) . "\" />";
	echo "<span class=\"help-block\">" . LangAddSessionField1Expl . "</span>";
	echo "</div>";


	// Add the begindate field
	$beginday = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'begindate' );
	$theYear = substr ( $beginday, 0, 4 );
	$theMonth = substr ( $beginday, 5, 2 );
	$theDay = substr ( $beginday, 8, 2 );
	$theHour = substr ( $beginday, 11, 2 );
	$theMinute = substr ( $beginday, 14, 2 );

	$contentBeginDate = "<input type=\"number\" min=\"1\" max=\"31\" required class=\"form-control\" maxlength=\"2\" size=\"4\"  name=\"beginday\" id=\"beginday\" value=\"" . $theDay . "\" onkeypress=\"return checkPositiveInteger(event);\" />";
	$contentBeginDate .= "&nbsp;&nbsp;";
	$contentBeginDate .= "<select required name=\"beginmonth\" id=\"beginmonth\" class=\"form-control\">";
	for($i = 1; $i < 13; $i ++)
		$contentBeginDate .= "<option value=\"" . $i . "\"" . (($theMonth == $i) ? " selected=\"selected\"" : "") . ">" . $GLOBALS ['Month' . $i] . "</option>";
	$contentBeginDate .= "</select>";
	$contentBeginDate .= "&nbsp;&nbsp;";
	$contentBeginDate .= "<input type=\"number\" min=\"1500\" max=\"2200\" required class=\"form-control\" maxlength=\"4\" size=\"6\" name=\"beginyear\" id=\"beginyear\" onkeypress=\"return checkPositiveInteger(event);\" value=\"" . $theYear . "\" />";
	$contentBeginTime = "<input type=\"number\" min=\"0\" max=\"23\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"beginhours\" value=\"" . $theHour . "\" />";
	$contentBeginTime .= "&nbsp;&nbsp;";
	$contentBeginTime .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"beginminutes\" value=\"" . $theMinute . "\" />&nbsp;&nbsp;";

	$contentBeginDateText = "<a href=\"#\" onclick=\"calBegin.showNavigationDropdowns();
	                             calBegin.setReturnFunction('SetObsDateBegin');
															 calBegin.showCalendar('DateAnchor2');
	                             return false;\"
										 name=\"DateAnchor2\"
										 id=\"DateAnchor2\">" . LangAddSessionField2 . "</a>";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField2 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $contentBeginDate . "&nbsp;" . $contentBeginTime;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField2Expl . "</span>";
	echo "</div>";

	// End date field
	// Add the begindate field
	$beginday = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'enddate' );
	$theYear = substr ( $beginday, 0, 4 );
	$theMonth = substr ( $beginday, 5, 2 );
	$theDay = substr ( $beginday, 8, 2 );
	$theHour = substr ( $beginday, 11, 2 );
	$theMinute = substr ( $beginday, 14, 2 );

	$contentEndDate = "<input type=\"number\" min=\"1\" max=\"31\" required class=\"form-control\" maxlength=\"2\" size=\"4\"  name=\"endday\" id=\"endday\" value=\"" . $theDay . "\" onkeypress=\"return checkPositiveInteger(event);\" />";
	$contentEndDate .= "&nbsp;&nbsp;";
	$contentEndDate .= "<select required name=\"endmonth\" id=\"endmonth\" class=\"form-control\">";
	for($i = 1; $i < 13; $i ++)
		$contentEndDate .= "<option value=\"" . $i . "\"" . (($theMonth == $i) ? " selected=\"selected\"" : "") . ">" . $GLOBALS ['Month' . $i] . "</option>";
	$contentEndDate .= "</select>";
	$contentEndDate .= "&nbsp;&nbsp;";
	$contentEndDate .= "<input type=\"number\" min=\"1500\" max=\"2200\" required class=\"form-control\" maxlength=\"4\" size=\"6\" name=\"endyear\" id=\"endyear\" onkeypress=\"return checkPositiveInteger(event);\" value=\"" . $theYear . "\" />";
	$contentEndTime = "<input type=\"number\" min=\"0\" max=\"23\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"endhours\" value=\"" . $theHour . "\" />";
	$contentEndTime .= "&nbsp;&nbsp;";
	$contentEndTime .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" size=\"4\" name=\"endminutes\" value=\"" . $theMinute . "\" />&nbsp;&nbsp;";

	$contentEndDateText = "<a href=\"#\" onclick=\"calEnd.showNavigationDropdowns();
	                             calEnd.setReturnFunction('SetObsDateEnd');
															 calEnd.showCalendar('DateAnchor3');
	                             return false;\"
										 name=\"DateAnchor3\"
										 id=\"DateAnchor3\">" . LangAddSessionField3 . "</a>";

	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField3 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $contentEndDate . "&nbsp;" . $contentEndTime;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField3Expl . "</span>";
	echo "</div>";

	// Location of the session
	$sites = $objLocation->getSortedLocationsList ( "name", $loggedUser );
	// Get the given location here!
	$theLoc = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'locationid' );
	$theLocName = $objLocation->getLocationPropertyFromId ( $theLoc, "name" );
	$found = 1;
	// Check if the number is owned by the loggedUser
	if ($objLocation->getLocationPropertyFromId ( $theLoc, "observer" ) != $loggedUser) {
		$found = 0;
		for($i = 0; $i < count ( $sites ); $i ++) {
			if (strcmp ( $sites [$i] [1], $theLocName ) == 0) {
				$theLoc = $sites [$i] [0];
				$found = 1;
			}
		}
	}
	$contentLoc = "<select class=\"form-control\" name=\"site\">";
	while ( list ( $key, $value ) = each ( $sites ) )
		$contentLoc .= "<option " . (($value [0] == $theLoc) ? "selected=\"selected\"" : '') . " value=\"" . $value [0] . "\">" . $value [1] . "</option>";
	if ($found == 0) {
		$contentLoc .= "<option selected=\"selected\" value=\"" . $theLoc . "\">" . $theLocName . " (" . LangAddLocationSession . ")</option>";
	}
	$contentLoc .= "</select>&nbsp;";

	echo "<div class=\"form-group\">
	       <label>" . "<a href=\"" . $baseURL . "index.php?indexAction=add_site\" title=\"" . LangChangeAccountField7Expl . "\" >" . LangAddSessionField4 . "</a>" . "</label>";
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
	$observersCont = "<textarea readonly=\"readonly\" class=\"form-control\" id=\"observers\" rows=\"7\" cols=\"50\">";
	$observersArray = $objSession->getObservers ( $objUtil->checkRequestKey ( 'sessionid' ) );
	if (! in_array ( $loggedUser, $observersArray )) {
		$observersCont .= $loggedUserName . "\n";
	}
	for($i = 0; $i < count ( $observersArray ); $i ++) {
		$observersCont .= $objObserver->getObserverProperty ( $observersArray [$i] ['observer'], "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $observersArray [$i] ['observer'], "name" ) . "\n";
	}
	$observersCont .= "</textarea>";
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField9 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $observersCont;
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSessionField9Expl . "</span>";
	echo "</div>";

	// Add observer
	$addObserver = "<select id=\"addObserver\" name=\"addObserver\" onchange=\"addUser(this,'" . $loggedUserName . "')\" class=\"form-control\">";
	$obs = $objObserver->getPopularObserversByName ();

	$addObserver .= "<option value=\"\">&nbsp;</option>";
	while ( list ( $key, $value ) = each ( $obs ) ) {
		if ($key != $loggedUser) {
			$foundKey = 0;
			for($i = 0; $i < count ( $observersArray ); $i ++) {
				if (strcmp ( $key, $observersArray [$i] ['observer'] ) == 0) {
					$foundKey = 1;
				}
			}
			if ($foundKey == 0) {
				$addObserver .= "<option value=\"" . $key . "\">" . $value . "</option>";
			}
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
	$deleteObserver = "<select id=\"deleteObserver\" name=\"deleteObserver\" onchange=\"deleteUser(this,'" . $loggedUserName . "')\" class=\"form-control\">";
	$deleteObserver .= "<option value=\"\">&nbsp;</option>";
	for($i = 0; $i < count ( $observersArray ); $i ++) {
		$deleteObserver .= "<option value=\"" . $observersArray [$i] ['observer'] . "\">" . $objObserver->getObserverProperty ( $observersArray [$i] ['observer'], "firstname" ) . " " . $objObserver->getObserverProperty ( $observersArray [$i] ['observer'], "name" ) . "</option>";
	}
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
	for($i = 0; $i < count ( $observersArray ); $i ++) {
		echo "  <div class=\"observer\">";
		echo "     <input type=\"hidden\" name=\"addedObserver[]\" value=\"" . $observersArray [$i] ['observer'] . "\" />";
		echo "  </div>";
	}
	echo "</div>";

	// Weather
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField5 . "</label>";
	echo "<textarea name=\"weather\"  class=\"form-control\" cols=\"50\" rows=\"7\">" . $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'weather' ) . "</textarea>";
	echo "<span class=\"help-block\">" . LangAddSessionField5Expl . "</span>";
	echo "</div>";

	// Equipment
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField6 . "</label>";
	echo "<textarea name=\"equipment\"  class=\"form-control\" cols=\"50\" rows=\"7\">" . $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'equipment' ) . "</textarea>";
	echo "<span class=\"help-block\">" . LangAddSessionField6Expl . "</span>";
	echo "</div>";

	// Comments
	echo "<div class=\"form-group\">
	       <label>" . LangAddSessionField7 . "</label>";
	echo "<textarea name=\"comments\"  class=\"form-control\" cols=\"50\" rows=\"7\">" . $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'comments' ) . "</textarea>";
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

	echo "</div></form>";
	echo "</div>";

	// The javascript for the fileinput plugins
	// Make sure to show the correct image.
	$imaLocation = "";
	if (file_exists ( $instDir . 'deepsky/sessions/' . $objUtil->checkRequestKey ( 'sessionid' ) . ".jpg" )) {
		$imaLocation = $baseURL . "deepsky/sessions/" . $objUtil->checkRequestKey ( 'sessionid' ) . ".jpg";
	}
	echo "<script type=\"text/javascript\">";
	echo "$(document).on(\"ready\", function() {
				$(\"#picture\").fileinput({";
	if ($imaLocation != "") {
		echo "    initialPreview: [
							// Show the correct file.
							'<img src=\"" . $imaLocation . "\" class=\"file-preview-image\">'
						],";
	}
	echo "    maxFileCount: 1,
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
function view_session() {
	global $baseURL, $loggedUserName, $objSession, $loggedUser, $objObserver, $objLocation, $objPresentations, $objUtil, $objLanguage, $instDir;

	echo "<div id=\"main\">";
	echo "<h4>" . stripslashes ( $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'name' ) ) . "</h4>";

	// Add the begindate field
	$beginday = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'begindate' );
	$contentBeginDate = $beginday;
	echo "<table class=\"table\">";
	echo "<tr><td>" . LangAddSessionField2 . "</td><td>" . $contentBeginDate . "</td></tr>";

	// End date field
	// Add the begindate field
	$endday = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'enddate' );
	$contentEndDate = $endday;
	echo "<tr><td>" . LangAddSessionField3 . "</td><td>" . $contentEndDate . "</td></tr>";

	// Location of the session
	$sites = $objLocation->getSortedLocationsList ( "name", $loggedUser );
	// Get the given location here!
	$theLoc = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'locationid' );
	$theLocName = $objLocation->getLocationPropertyFromId ( $theLoc, "name" );

	echo "<tr><td>" . LangAddSessionField4 . "</td><td>" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_location&location=" . $theLoc . "\">" . $theLocName . "</a>" . "</td></tr>";

	// Language
	$theLanguage = $objObserver->getObserverProperty ( $loggedUser, 'observationlanguage' );
	$allLanguages = $objLanguage->getAllLanguages ( $objObserver->getObserverProperty ( $loggedUser, 'language' ) );
	echo "<tr><td>" . LangAddSessionField8 . "</td><td>" . $allLanguages [$theLanguage] . "</td></tr>";

	// Other observers
	$theObserver = $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'observerid' );
	$observersCont = "<a href=\"" . $baseURL . "index.php?indexAction=detail_observer&user=" . $theObserver . "\"> " . $objObserver->getObserverProperty ( $theObserver, "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $theObserver, "name" ) . "</a>";
	$observersArray = $objSession->getObservers ( $objUtil->checkRequestKey ( 'sessionid' ) );

	for($i = 0; $i < count ( $observersArray ); $i ++) {
		$observersCont .= " - ";
		$observersCont .= "<a href=\"" . $baseURL . "index.php?indexAction=detail_observer&user=" . $observersArray [$i] ['observer'] . "\">" . $objObserver->getObserverProperty ( $observersArray [$i] ['observer'], "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $observersArray [$i] ['observer'], "name" ) . "</a>";
	}
	echo "<tr><td>" . LangAddSessionField9 . "</td><td>" . $observersCont . "</td></tr>";

	// Weather
	echo "<tr><td>" . LangAddSessionField5 . "</td><td>" . $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'weather' ) . "</td></tr>";

	// Equipment
	echo "<tr><td>" . LangAddSessionField6 . "</td><td>" . $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'equipment' ) . "</td></tr>";

	// Comments
	echo "<tr><td>" . LangAddSessionField7 . "</td><td>" . $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'comments' ) . "</td></tr>";

	echo "</table>";

	if (strcmp ( $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'observerid' ), $loggedUser ) == 0) {
		$linkToAdapt = "<a class=\"btn btn-success\" href=\"" . $baseURL . 'index.php?indexAction=adapt_session&sessionid=' . $objUtil->checkRequestKey ( 'sessionid' ) . "&adapt=1\">";
		$linkToAdapt .= LangChangeSessionButton . "</a>";
		echo $linkToAdapt;
	}

	echo "<hr />";
	// A link to the picture
	if (file_exists ( $instDir . 'deepsky/sessions/' . $objUtil->checkRequestKey ( 'sessionid' ) . ".jpg" )) {
		echo "<a href=\"" . $baseURL . 'deepsky/sessions/' . $objUtil->checkRequestKey ( 'sessionid' ) . ".jpg\" data-lightbox=\"image-1\" data-title=\"" . stripslashes ( $objSession->getSessionPropertyFromId ( $objUtil->checkRequestKey ( 'sessionid' ), 'name' ) ) . "\" class=\"gallery clearfix\">
	         <img src=\"" . $baseURL . 'deepsky/sessions/' . $objUtil->checkRequestKey ( 'sessionid' ) . "_resized.jpg\" /></a></td>";
	}

	echo "</div></form>";
	echo "</div>";
}

?>
