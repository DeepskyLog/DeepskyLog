<?php  
// change_session.php
// allows the user to change an observing session
global $loggedUser;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($sessionid=$objUtil->checkGetKey('sessionid'))) throw new Exception(LangException003);
elseif(!($objSession->getSessionPropertyFromId($sessionid,'name'))) throw new Exception("Session not found in change_session.php, please contact the developers with this message:".$sessionid);
elseif(strcmp($objSession->getSessionPropertyFromId($sessionid, 'observerid'),$loggedUser) != 0)  throw new Exception("Session can only be viewed by the owner of the session");
else change_session();

function change_session()
{ global $baseURL,$loggedUserName,$objSession,$loggedUser,$objObserver,
         $objLocation,$objPresentations,$objUtil,$objLanguage;

	echo "	<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/CalendarPopupCC.js\"></script>";
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
         
  $objPresentations->line(array("<h4>".LangChangeSessionTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
	echo "<hr />";
	echo "<form id=\"sessionForm\" action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_session\" />";
  echo "<input type=\"hidden\" name=\"sessionid\" value=\"". $objUtil->checkRequestKey('sessionid') . "\" />";
  if ($objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'), "active") == 0) {
    $sessionButton = LangAddSessionButton;
  } else {
    $sessionButton = LangChangeSessionButton;
  }
  $objPresentations->line(array("", "<a href=\"".$baseURL."index.php?indexAction=validate_delete_existingsession&amp;sessionid=" . urlencode($_GET['sessionid']) . "\">" . LangRemove . "</a>", "<input type=\"submit\" name=\"add\" value=\"".$sessionButton."\" />&nbsp;"),
	                        "LRR",array(60, 20,20),'',array("fieldname"));                              
	$objPresentations->line(array(LangAddSessionField1,
	                              "<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"sessionname\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('sessionname')).stripslashes($objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'name'))."\" />",
	                              LangAddSessionField1Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));

  // Add the begindate field
  $beginday= $objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'begindate');
  $theYear=substr($beginday,0,4);
	$theMonth=substr($beginday,5,2);
	$theDay=substr($beginday,8,2);
	$theHour=substr($beginday,11,2);
  $theMinute=substr($beginday,14,2);

  $contentBeginDate ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" size=\"3\"  name=\"beginday\" id=\"beginday\" value=\"".$theDay."\" onkeypress=\"return checkPositiveInteger(event);\" />";
  $contentBeginDate.="&nbsp;&nbsp;";
	$contentBeginDate.="<select name=\"beginmonth\" id=\"beginmonth\" class=\"inputfield requiredField centered\">";
	for($i= 1;$i<13;$i++)
		$contentBeginDate.="<option value=\"".$i."\"".(($theMonth==$i)?" selected=\"selected\"" : "").">".$GLOBALS['Month'.$i]."</option>";
	$contentBeginDate.="</select>";
	$contentBeginDate.="&nbsp;&nbsp;";
	$contentBeginDate.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"4\" size=\"4\" name=\"beginyear\" id=\"beginyear\" onkeypress=\"return checkPositiveInteger(event);\" value=\"".$theYear."\" />";
	$contentBeginTime ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" size=\"2\" name=\"beginhours\" value=\"".$theHour."\" />";
	$contentBeginTime.="&nbsp;&nbsp;";
	$contentBeginTime.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" size=\"2\" name=\"beginminutes\" value=\"".$theMinute."\" />&nbsp;&nbsp;";

	$contentBeginDateText = "<a href=\"#\" onclick=\"calBegin.showNavigationDropdowns();
	                             calBegin.setReturnFunction('SetObsDateBegin');
															 calBegin.showCalendar('DateAnchor2');
	                             return false;\" 
										 name=\"DateAnchor2\" 
										 id=\"DateAnchor2\">" . LangAddSessionField2 . "</a>"; 

  $objPresentations->line(array($contentBeginDateText,
	                              $contentBeginDate . "&nbsp;" . $contentBeginTime, LangAddSessionField2Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));

  // End date field
  // Add the begindate field
  $beginday= $objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'enddate');
  $theYear=substr($beginday,0,4);
	$theMonth=substr($beginday,5,2);
	$theDay=substr($beginday,8,2);
	$theHour=substr($beginday,11,2);
  $theMinute=substr($beginday,14,2);

  $contentEndDate ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" size=\"3\"  name=\"endday\" id=\"endday\" value=\"".$theDay."\" onkeypress=\"return checkPositiveInteger(event);\" />";
  $contentEndDate.="&nbsp;&nbsp;";
	$contentEndDate.="<select name=\"endmonth\" id=\"endmonth\" class=\"inputfield requiredField centered\">";
	for($i= 1;$i<13;$i++)
		$contentEndDate.="<option value=\"".$i."\"".(($theMonth==$i)?" selected=\"selected\"" : "").">".$GLOBALS['Month'.$i]."</option>";
	$contentEndDate.="</select>";
	$contentEndDate.="&nbsp;&nbsp;";
	$contentEndDate.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"4\" size=\"4\" name=\"endyear\" id=\"endyear\" onkeypress=\"return checkPositiveInteger(event);\" value=\"".$theYear."\" />";
	$contentEndTime ="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" size=\"2\" name=\"endhours\" value=\"".$theHour."\" />";
	$contentEndTime.="&nbsp;&nbsp;";
	$contentEndTime.="<input type=\"text\" class=\"inputfield requiredField centered\" maxlength=\"2\" size=\"2\" name=\"endminutes\" value=\"".$theMinute."\" />&nbsp;&nbsp;";

	$contentEndDateText = "<a href=\"#\" onclick=\"calEnd.showNavigationDropdowns();
	                             calEnd.setReturnFunction('SetObsDateEnd');
															 calEnd.showCalendar('DateAnchor3');
	                             return false;\" 
										 name=\"DateAnchor3\" 
										 id=\"DateAnchor3\">" . LangAddSessionField3 . "</a>"; 

  $objPresentations->line(array($contentEndDateText,
	                              $contentEndDate . "&nbsp;" . $contentEndTime, LangAddSessionField3Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));

  // Location of the session
  $sites = $objLocation->getSortedLocationsList("name", $loggedUser,1);
  // Get the given location here!
  $theLoc = $objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'locationid');
  $theLocName = $objLocation->getLocationPropertyFromId($theLoc, "name");
  $found = 1;
  // Check if the number is owned by the loggedUser
  if ($objLocation->getLocationPropertyFromId($theLoc, "observer") != $loggedUser) {
    $found = 0;
    for ($i=0;$i<count($sites);$i++) {
      if (strcmp($sites[$i][1], $theLocName) == 0) {
        $theLoc = $sites[$i][0];
        $found = 1;
      }
    }
  }
  $contentLoc="<select class=\"inputfield requiredField\" name=\"site\">";
	while(list($key,$value)=each($sites))
		$contentLoc.="<option ".(($value[0]==$theLoc)?"selected=\"selected\"":'')." value=\"".$value[0]."\">".$value[1]."</option>";
  if ($found == 0) {
    $contentLoc.="<option selected=\"selected\" value=\"".$theLoc."\">".$theLocName." (" . LangAddLocationSession . ")</option>";
  }
  $contentLoc.="</select>&nbsp;";

	$objPresentations->line(array("<a href=\"".$baseURL."index.php?indexAction=add_site\" title=\"".LangChangeAccountField7Expl."\" >".LangAddSessionField4."</a>",$contentLoc,LangAddSessionField4Expl),
		                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
	
  // Language
  $theLanguage=$objObserver->getObserverProperty($loggedUser,'observationlanguage');
	$allLanguages = $objLanguage->getAllLanguages($objObserver->getObserverProperty($loggedUser,'language'));
	$contentLanguage="<select name=\"description_language\"  class=\"inputfield requiredField\">";
	while (list ($key, $value) = each($allLanguages))
		$contentLanguage.= "<option value=\"".$key."\"".(($theLanguage==$key)?"selected=\"selected\"":'').">".$value."</option>";
	$contentLanguage.="</select>&nbsp;";
	                              
  $objPresentations->line(array(LangAddSessionField8,
	                               $contentLanguage,
	                               LangAddSessionField8Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
	
  // Other observers
  $observersCont = "<textarea readonly=\"readonly\" class=\"messageAreaSmall\" id=\"observers\" rows=\"1\" cols=\"1\">";
	$observersArray = $objSession->getObservers($objUtil->checkRequestKey('sessionid')); 
  if (!in_array($loggedUser, $observersArray)) {
    $observersCont .= $loggedUserName . "\n";
  }
  for ($i=0;$i<count($observersArray);$i++) {
    $observersCont .= $objObserver->getObserverProperty($observersArray[$i]['observer'], "firstname") . "&nbsp;" . 
                      $objObserver->getObserverProperty($observersArray[$i]['observer'], "name") . "\n";
  }
  $observersCont .= "</textarea>";
  $objPresentations->line(array(LangAddSessionField9,
	                               $observersCont,
	                               LangAddSessionField9Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));

  // Add observer
	$addObserver = "<select id=\"addObserver\" name=\"addObserver\" onchange=\"addUser(this,'" . $loggedUserName . "')\" class=\"inputfield\">";
	$obs = $objObserver->getPopularObserversByName();

	$addObserver .= "<option value=\"\">&nbsp;</option>";
	while(list($key, $value) = each($obs)) {
	  if ($key != $loggedUser) {
	    $foundKey = 0;
	    for ($i=0;$i<count($observersArray);$i++) {
	      if (strcmp($key, $observersArray[$i]['observer']) == 0) {
	        $foundKey = 1;
	      }
	    }
	    if ($foundKey == 0) {
	      $addObserver .= "<option value=\"".$key."\">".$value."</option>";
	    }
	  }
	}
	$addObserver .= "</select>";

  $objPresentations->line(array(LangAddSessionField10,
	                               $addObserver,
	                               LangAddSessionField10Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
	
  // Delete observer
	$deleteObserver = "<select id=\"deleteObserver\" name=\"deleteObserver\" onchange=\"deleteUser(this,'" . $loggedUserName . "')\" class=\"inputfield\">";
	$deleteObserver .= "<option value=\"\">&nbsp;</option>";
	for ($i=0;$i<count($observersArray);$i++) {
	  $deleteObserver .= "<option value=\"".$observersArray[$i]['observer']."\">".
	          $objObserver->getObserverProperty($observersArray[$i]['observer'], "firstname") . " " . 
	          $objObserver->getObserverProperty($observersArray[$i]['observer'], "name")."</option>";
	}
	$deleteObserver .= "</select>";

  $objPresentations->line(array(LangAddSessionField11,
	                               $deleteObserver,
	                               LangAddSessionField11Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));

	// Hidden field with all the observers
	// First the loggedUser
	echo "<div id=\"newlink\">";  
  echo "  <div class=\"observer\">";  
  echo "     <input type=\"hidden\" name=\"addedObserver[]\" value=\"" . $loggedUser . "\" />";  
  echo "  </div>";  
	for ($i=0;$i<count($observersArray);$i++) {
    echo "  <div class=\"observer\">";  
    echo "     <input type=\"hidden\" name=\"addedObserver[]\" value=\"" . $observersArray[$i]['observer'] . "\" />";  
    echo "  </div>";  
	}
  echo "</div>";

  // Weather
	$objPresentations->line(array(LangAddSessionField5,
	                              "<textarea name=\"weather\"  class=\"messageAreaSmall inputfield\" cols=\"1\" rows=\"1\">" . 
	                              $objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'weather') . 
	                              "</textarea>",
	                              LangAddSessionField5Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));

  // Equipment
	$objPresentations->line(array(LangAddSessionField6,
	                              "<textarea name=\"equipment\"  class=\"messageAreaSmall inputfield\" cols=\"1\" rows=\"1\">" . 
	                              $objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'equipment') . 
	                              "</textarea>",
	                              LangAddSessionField6Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));
	
  // Comments
	$objPresentations->line(array(LangAddSessionField7,
	                              "<textarea name=\"comments\"  class=\"messageAreaSmall inputfield\" cols=\"1\" rows=\"1\">" . 
	                              $objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'comments') . 
	                              "</textarea>",
	                              LangAddSessionField7Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));

  echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
