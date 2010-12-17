<?php  
// new_session.php
// allows the user to add a new observing session

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else new_session();

function new_session()
{ global $baseURL,$loggedUserName,$objSession,$loggedUser,$objObserver,
         $objLocation,$objPresentations,$objUtil,$objLanguage;

  // TODO : When there are sessions added by another observer, where the observer is co-observer, then we should first see a list with sessions. 
  // It should be possible to click on the seesion, and all information should be filled out (or maybe it should be possible to just accept this session).

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
	echo " function addUser(elm)";
	echo " {";
	// Value (=key) = elm.value
	// TODO : Always make a new list, with all the observers -> Also when removing
	echo "  var w = document.forms[\"sessionForm\"].addObserver.selectedIndex;";
  echo "  var selected_text = document.forms[\"sessionForm\"].addObserver.options[w].text;";
  echo "  document.forms[\"sessionForm\"].observers.value += \"\\n\" + selected_text;";
	// Remove observer from list of observers
	echo "  var elSel = document.getElementById('addObserver');";
  echo "  var i;";
  echo "  for (i = elSel.length - 1; i>=0; i--) {";
  echo "    if (elSel.options[i].selected) {";
  echo "      elSel.remove(i);";
  echo "    }";
  echo "  }";
	
	// Add to list of deletable observers
	echo "  var newOption = document.createElement('option');";
	echo "  newOption.text = selected_text;";
	echo "  newOption.value = elm.value;";
	echo "  var elSel = document.getElementById('deleteObserver');";
	echo "  try {";
	echo "    elSel.add(newOption, null);"; // standards compliant; doesn't work in IE
  echo "  }";
  echo "  catch(ex) {";
  echo "    elSel.add(newOption);"; // IE only
  echo "  }	";
	echo " }";
  echo "	</script>";
  
	// Javascript to delete a user
	// TODO : Check : First user in the list can not be deleted????
	echo " <script type=\"text/javascript\" >";
	echo " function deleteUser(elm)";
	echo " {";
	// Value (=key) = elm.value
	echo "  var w = document.forms[\"sessionForm\"].deleteObserver.selectedIndex;";
  echo "  var selected_text = document.forms[\"sessionForm\"].deleteObserver.options[w].text;";
	echo "  document.forms[\"sessionForm\"].observers.value += \"Deleted : \" + selected_text;";
	// Remove observer from list of observers
	echo "  var elSel = document.getElementById('deleteObserver');";
  echo "  var i;";
  echo "  for (i = elSel.length - 1; i>=0; i--) {";
  echo "    if (elSel.options[i].selected) {";
  echo "      elSel.remove(i);";
  echo "    }";
  echo "  }";
	
	// Add to list of observers
	echo "  var newOption = document.createElement('option');";
	echo "  newOption.text = selected_text;";
	echo "  newOption.value = elm.value;";
	echo "  var elSel = document.getElementById('addObserver');";
	echo "  try {";
	echo "    elSel.add(newOption, null);"; // standards compliant; doesn't work in IE
  echo "  }";
  echo "  catch(ex) {";
  echo "    elSel.add(newOption);"; // IE only
  echo "  }	";
	echo " }";
  echo "	</script>";
  
  echo "<div id=\"main\">";  
	$objPresentations->line(array("<h4>".LangAddSessionTitle."&nbsp;<span class=\"requiredField\">".LangRequiredFields."</span>"."</h4>"),"L",array(),30);
	echo "<hr />";
	echo "<form id=\"sessionForm\" action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_session\" />";

	$objPresentations->line(array("", "<input type=\"submit\" name=\"add\" value=\"".LangAddSessionButton."\" />&nbsp;"),
	                        "LR",array(80,20),'',array("fieldname"));                              
	$objPresentations->line(array(LangAddSessionField1,
	                              "<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"sessionname\" size=\"30\" value=\"".stripslashes($objUtil->checkRequestKey('sessionname')).stripslashes($objSession->getSessionPropertyFromId($objUtil->checkRequestKey('sessionid'),'name'))."\" />",
	                              LangAddSessionField1Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));

  // Add the begindate field
  $yesterday=date('Ymd',strtotime('-1 day'));
	$theYear=substr($yesterday,0,4);
	$theMonth=substr($yesterday,4,2);
	$theDay=substr($yesterday,6,2);
	$theHour="22";
  $theMinute="00";

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
  $today=date('Ymd',strtotime('today'));
	$theYear=substr($today,0,4);
	$theMonth=substr($today,4,2);
	$theDay=substr($today,6,2);
	$theHour="02";
  $theMinute="00";

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
  // Get the standard location here!
  $theLoc=$objObserver->getObserverProperty($loggedUser, "stdlocation");
	$contentLoc="<select class=\"inputfield requiredField\" name=\"site\">";
	while(list($key,$value)=each($sites))
		$contentLoc.="<option ".(($value[0]==$theLoc)?"selected=\"selected\"":'')." value=\"".$value[0]."\">".$value[1]."</option>";
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
  // First add the loggedUser
  $observersCont = "<textarea readonly=\"readonly\" class=\"messageAreaSmall\" id=\"observers\" rows=\"1\" cols=\"1\">";
  $observersCont .= $objObserver->getObserverProperty($loggedUser, "firstname") . "&nbsp;" . $objObserver->getObserverProperty($loggedUser, "name");
  $observersCont .= "</textarea>";
  
  $objPresentations->line(array(LangAddSessionField9,
	                               $observersCont,
	                               LangAddSessionField9Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));

  // Add observer
	$addObserver = "<select id=\"addObserver\" name=\"addObserver\" onchange=\"addUser(this)\" class=\"inputfield\">";
	$obs = $objObserver->getPopularObserversByName();

	while(list($key, $value) = each($obs)) {
	  if ($key != $loggedUser) {
	    $addObserver .= "<option value=\"".$key."\">".$value."</option>";
	  }
	}
	$addObserver .= "</select>";

  $objPresentations->line(array(LangAddSessionField10,
	                               $addObserver,
	                               LangAddSessionField10Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
	
  // Delete observer
	$deleteObserver = "<select id=\"deleteObserver\" name=\"deleteObserver\" onchange=\"deleteUser(this)\" class=\"inputfield\">";

  $deleteObserver .= "<option value=\"\"></option>";
	$deleteObserver .= "</select>";

  $objPresentations->line(array(LangAddSessionField11,
	                               $deleteObserver,
	                               LangAddSessionField11Expl),
	                        "RLL",array(25,40,35),'',array("fieldname","fieldvalue","fieldexplanation"));
	                               
  // Weather
	$objPresentations->line(array(LangAddSessionField5,
	                              "<textarea name=\"weather\"  class=\"messageAreaSmall inputfield\" cols=\"1\" rows=\"1\">" . "</textarea>",
	                              LangAddSessionField5Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));

  // Equipment
	$objPresentations->line(array(LangAddSessionField6,
	                              "<textarea name=\"equipment\"  class=\"messageAreaSmall inputfield\" cols=\"1\" rows=\"1\">" . "</textarea>",
	                              LangAddSessionField6Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));
	
  // Comments
	$objPresentations->line(array(LangAddSessionField7,
	                              "<textarea name=\"comments\"  class=\"messageAreaSmall inputfield\" cols=\"1\" rows=\"1\">" . "</textarea>",
	                              LangAddSessionField7Expl),
	                        "RLL",array(25,40,35),136,array("fieldname","fieldvalue","fieldexplanation"));

  echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
