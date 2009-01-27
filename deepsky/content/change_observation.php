<?php
// change_observation.php
// allows a user to change his observation 

if (!$_GET['observation'])
	throw new Exception("No observation selected");
echo "<div id=\"main\">";
echo "<h2>" . LangChangeObservationTitle . "</h2>";
echo "<form action=\"" . $baseURL . "index.php?indexAction=validate_change_observation\" method=\"post\" enctype=\"multipart/form-data\">";
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td "."class=\"fieldname\" width=\"100\"".">".LangViewObservationField1."</td>";
echo "<td>"."<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode($objObservation->getDsObservationProperty($_GET['observation'],'objectname')) . "\">" . $objObservation->getDsObservationProperty($_GET['observation'],'objectname') . "</a>"."</td>";
echo "</tr><tr>";
echo "<td "."class=\"fieldname\"".">".LangViewObservationField2."</td>";
echo "<td >"."<a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode($objObservation->getDsObservationProperty($_GET['observation'],'observerid')) . "\">" . $objObserver->getFirstName($objObservation->getDsObservationProperty($_GET['observation'],'observerid')) . "&nbsp;" . $objObserver->getObserverName($objObservation->getDsObservationProperty($_GET['observation'],'observerid')) . "</a>"."</td>";
echo "<tr>";
echo "<td "."class=\"fieldname\"".">".LangViewObservationField5."</td>";
echo "<td>";
if ($objObserver->getUseLocal($_SESSION['deepskylog_id'])) {
	$date = sscanf($objObservation->getDsObservationLocalDate($_GET['observation']), "%4d%2d%2d");
	$timestr = $objObservation->getDsObservationLocalTime($_GET['observation']);
} else {
	$date = sscanf($objObservation->getDateDsObservation($_GET['observation']), "%4d%2d%2d");
	$timestr = $objObservation->getTime($_GET['observation']);
}
if ($timestr >= 0)
	$time = sscanf(sprintf("%04d", $timestr), "%2d%2d");
else {
	$time[0] = -9;
	$time[1] = -9;
}
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" size=\"2\" name=\"day\" value=\"" . $date[2] . "\" />";
echo "&nbsp;&nbsp;";
echo "<select name=\"month\" class=\"inputfield requiredField\">";
echo "<option value=\"\"></option>";
for ($i = 1; $i < 13; $i++)
	echo "<option value=\"" . $i . "\"" . (($date[1] == $i) ? " selected=\"selected\"" : "") . ">" . $GLOBALS['Month' . $i] . "</option>";
echo "</select>";
echo "&nbsp;&nbsp";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"4\" size=\"4\" name=\"year\" value=\"" . $date[0] . "\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo (($objObserver->getUseLocal($_SESSION['deepskylog_id'])) ? LangViewObservationField9lt : LangViewObservationField9);
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"" . (($time[0] >= 0) ? $time[0] : '') . "\" />";
echo "&nbsp;&nbsp";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minutes\" value=\"" . (($time[1] >= 0) ? $time[1] : '') . "\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">"; // LOCATION
echo LangViewObservationField4;
echo "</td>";
echo "<td>";
echo "<select name=\"location\" class=\"inputfield requiredField\" style=\"width:300px\">";
$locs = $objLocation->getSortedLocationsList("name", $_SESSION['deepskylog_id']);
$theLoc = $objObservation->getDsObservationLocationId($_GET['observation']);
while (list ($key, $value) = each($locs))
	echo "<option " . (($value[0] == $theLoc) ? "selected=\"selected\"" : '') . " value=\"" . $value[0] . "\">" . $value[1] . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField3;
echo "</td>";
echo "<td>"; // INSTRUMENTS
echo "<select name=\"instrument\" class=\"inputfield requiredField\" style=\"width:300px\">";
$instr = $objInstrument->getSortedInstrumentsList("name", $_SESSION['deepskylog_id']);
$theInstr = $objObservation->getDsObservationProperty($_GET['observation'],'instrumentid');
while (list ($key, $value) = each($instr))
	echo "<option " . (($theInstr == $key) ? "selected=\"selected\"" : '') . " value=\"" . $key . "\">" . $value . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<td class=\"fieldname\">"; // EYEPIECE
echo LangViewObservationField30;
echo "&nbsp;";
echo "</td>";
echo "<td>";
echo "<select name=\"eyepiece\" class=\"inputfield\" style=\"width:300px\">";
echo "<option value=\"\"></option>";
$eyeps = $objEyepiece->getSortedEyepieces("name", $_SESSION['deepskylog_id']);
$theEyepiece = $objObservation->getDsObservationProperty($_GET['observation'],'eyepieceid');
while (list ($key, $value) = each($eyeps))
	echo "<option value=\"" . $value . "\"" . (($theEyepiece == $value) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['objEyepiece']->getEyepiecePropertyFromId($value,'name') . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField31; // FILTER
echo "&nbsp;";
echo "</td>";
echo "<td>";
echo "<select name=\"filter\" class=\"inputfield\" style=\"width:300px\">";
echo "<option value=\"\"></option>";
$filts = $objFilter->getSortedFilters("name", $_SESSION['deepskylog_id']);
$theFilter = $objObservation->getDsObservationProperty($_GET['observation'],'filterid');
while (list ($key, $value) = each($filts)) // go through instrument array
	echo "<option value=\"" . $value . "\"" . (($theFilter == $value) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['objFilter']->getFilterPropertyFromId($value,'name') . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField32; // LENS
echo "&nbsp;";
echo "</td>";
echo "<td>";
echo "<select name=\"lens\" class=\"inputfield\" style=\"width:300px\">";
echo "<option value=\"\"></option>";
$lns = $objLens->getSortedLenses("name", $_SESSION['deepskylog_id']);
$theLens = $objObservation->getDsObservationProperty($_GET['observation'],'lensid');
while (list ($key, $value) = each($lns))
	echo "<option value=\"" . $value . "\"" . (($theLens == $value) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['objLens']->getLensPropertyFromId($value,'name') . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
// SEEING
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField6;
echo "</td>";
echo "<td>";
echo "<select name=\"seeing\" style=\"width:300px\" class=\"inputfield\">";
echo "<option value=\"-1\"></option>";
$theSeeing = $objObservation->getSeeing($_GET['observation']);
for ($i = 1; $i < 6; $i++)
	echo "<option value=\"" . $i . "\"" . (($theSeeing == $i) ? " selected=\"selected\"" : '') . ">" . $GLOBALS['Seeing' . $i] . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField12;
echo "</td>";
echo "<td>";
echo "<input type=\"file\" name=\"drawing\" class=\"inputfield\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField7;
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"limit\" size=\"3\" value=\"" . (($objObservation->getLimitingMagnitude($_GET['observation'])) ? (sprintf("%1.1f", $objObservation->getLimitingMagnitude($_GET['observation']))) : '') . "\" />";
echo "&nbsp;".LangViewObservationField34 . "&nbsp;"; // SQM
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sqm\" size=\"4\" style=\"text-align:center\" value=\"";
if ($objObservation->getSQM($_GET['observation']) > 0.0) {
	echo sprintf("%2.1f", $objObservation->getSQM($_GET['observation']));
} else {
	echo "";
}
echo "\" />";
echo "</td>";
echo "</tr>";
echo "<td>";
echo LangViewObservationField33; // Estimated diameter
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"largeDiam\" size=\"5\" style=\"text-align:center\" value=\"";
$min = 0;
if ($largeDiameter=$objObservation->getDsObservationProperty($_GET['observation'],'largeDiameter') > 0.0) {
	if ($largeDiameter > 60.0) {
		$min = 1;
		echo sprintf("%.1f",$largeDiameter / 60.0);
	} else {
		echo sprintf("%.1f", $largeDiameter);
	}
} else {
	echo "";
}
echo "\" />";
echo "&nbsp;x&nbsp;";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"smallDiam\" size=\"5\" style=\"text-align:center\" value=\"";
if ($mallDiameter=$objObservation->getDsObservationProperty($_GET['observation'],'smallDiameter') > 0.0) {
	if ($min == 1) {
		echo sprintf("%.1f", $smallDiameter/ 60.0);
	} else {
		echo sprintf("%.1f", $smallDiameter);
	}
} else {
	echo "";
}
echo "\" />";
echo "&nbsp;";
echo "<select name=\"size_units\" class=\"inputfield\">";
echo "<option value=\"min\"";
if ($min == 1) {
	echo " selected";
}
echo ">" . LangNewObjectSizeUnits1 . "</option>";
echo "<option value=\"sec\"";
if ($min == 0) {
	echo " selected";
}
echo ">" . LangNewObjectSizeUnits2 . "</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
if($GLOBALS['objObject']->getDsoProperty($object,'type')=="GALXY")
{ echo "<tr>";
  echo "<td>";
  echo "</td>";
  echo "<td colspan=\"2\">";
  echo "<input type=\"checkbox\" name=\"stellar\" " . (($objObservation->getDsStellar($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField35;
  echo "&nbsp;&nbsp;&nbsp;";
  echo "<input type=\"checkbox\" name=\"extended\" " . (($objObservation->getDsExtended($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField36;
  echo "&nbsp;&nbsp;&nbsp;";
  echo "<input type=\"checkbox\" name=\"resolved\" " . (($objObservation->getDsResolved($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField37;
  echo "&nbsp;&nbsp;&nbsp;";
  echo "<input type=\"checkbox\" name=\"mottled\" " . (($objObservation->getDsMottled($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField38;
  echo "</td>";
  echo "</tr>";
}
$object = $objObservation->getDsObservationProperty($_GET['observation'],'objectname');
// Some extra fields when we are describing open clusters, or asterisms...
if(in_array($GLOBALS['objObject']->getDsoProperty($object,'type'),array("ASTER" ,"CLANB","DS","OPNCL","AA1STAR","AA2STAR","AA3STAR","AA4STAR","AA8STAR"))) 
{ echo "<tr>";
	echo "<td class=\"fieldname\">";
	echo LangViewObservationField40;
	echo "</td>";
	echo "<td>";
	echo "<select name=\"characterType\" class=\"inputfield\" style=\"width:300px\">";
	echo "<option value=\"\"" . (($objObservation->getDsCharacterType($_GET['observation']) == '') ? " selected=\"selected\" " : '') . "></option>";
	echo "<option value=\"" . "A" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'A') ? " selected=\"selected\" " : '') . ">A - ".$ClusterTypeA."</option>";
	echo "<option value=\"" . "B" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'B') ? " selected=\"selected\" " : '') . ">B - ".$ClusterTypeB."</option>";
	echo "<option value=\"" . "C" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'C') ? " selected=\"selected\" " : '') . ">C - ".$ClusterTypeC."</option>";
	echo "<option value=\"" . "D" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'D') ? " selected=\"selected\" " : '') . ">D - ".$ClusterTypeD."</option>";
	echo "<option value=\"" . "E" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'E') ? " selected=\"selected\" " : '') . ">E - ".$ClusterTypeE."</option>";
	echo "<option value=\"" . "F" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'F') ? " selected=\"selected\" " : '') . ">F - ".$ClusterTypeF."</option>";
	echo "<option value=\"" . "G" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'G') ? " selected=\"selected\" " : '') . ">G - ".$ClusterTypeG."</option>";
	echo "<option value=\"" . "H" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'H') ? " selected=\"selected\" " : '') . ">H - ".$ClusterTypeH."</option>";
	echo "<option value=\"" . "I" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'I') ? " selected=\"selected\" " : '') . ">I - ".$ClusterTypeI."</option>";
	echo "<option value=\"" . "X" . "\"" . (($objObservation->getDsCharacterType($_GET['observation']) == 'X') ? " selected=\"selected\" " : '') . ">X - ".$ClusterTypeX."</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td><td colspan = \"2\">";
	echo "<input type=\"checkbox\" name=\"unusualShape\" " . (($objObservation->getDsUnusualShape($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField41;
	echo "&nbsp;&nbsp;&nbsp;";
	echo "<input type=\"checkbox\" name=\"partlyUnresolved\" " . (($objObservation->getDsPartlyUnresolved($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField42;
	echo "&nbsp;&nbsp;&nbsp;";
	echo "<input type=\"checkbox\" name=\"colorContrasts\" " . (($objObservation->getDsColorContrasts($_GET['observation']) == 1) ? "checked" : '') . "/>" . LangViewObservationField43;
	echo "</td>";
	echo "</tr>";
}
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField22; // Visibility
echo "</td>";
echo "<td>";
echo "<select name=\"visibility\" class=\"inputfield\" style=\"width:300px\">";
echo "<option value=\"0\"></option>";
$visibility = $objObservation->getVisibility($_GET['observation']);
for ($i = 1; $i < 8; $i++)
	echo "<option value=\"" . $i . "\"" . (($visibility == $i) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['Visibility' . $i] . "</option>";
echo "</select>";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField29 . "&nbsp;*"; // Language of observation
echo "</td>";
echo "<td>";
$allLanguages = $objLanguage->getAllLanguages($objObserver->getLanguage($_SESSION['deepskylog_id']));
$theLang = $objObservation->getDsObservationLanguage($_GET['observation']);
echo "<select name=\"description_language\" class=\"inputfield\"  style=\"width:300px\">";
while (list ($key, $value) = each($allLanguages))
	echo "<option value=\"" . $key . "\" " . (($theLang == $key) ? "selected=\"selected\"" : '') . ">" . $value . "</option>";
echo "</select>";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField8;
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan=\"2\">";
echo "<textarea name=\"description\" class=\"description inputfield requiredField\">" . $objUtil->br2nl(html_entity_decode($objObservation->getDescriptionDsObservation($_GET['observation']))) . "</textarea>";
echo "</td>";
echo "</tr>";
// ??? echo("</td></tr>"); error ??
echo "<tr>";
echo "<td colspan=\"2\">";
echo "<input type=\"submit\" name=\"changeobservation\" value=\"" . LangChangeObservationButton . "\" />";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<input type=\"hidden\" name=\"observationid\" value=\"" . $_GET['observation'] . "\"></input>";
echo "</form>";
$upload_dir = 'deepsky/drawings'; //DRAWING
$dir = opendir($upload_dir);
while (FALSE !== ($file = readdir($dir))) {
	if (("." == $file) OR (".." == $file))
		continue;
	if (fnmatch($_GET['observation'] . "_resized.gif", $file) || fnmatch($_GET['observation'] . "_resized.jpg", $file) || fnmatch($_GET['observation'] . "_resized.png", $file)) {
		echo "<p>";
		echo "<a href=\"" . $baseURL . $upload_dir . "/" . $_GET['observation'] . ".jpg" . "\"><img class=\"account\" src=\"" . $baseURL . "deepsky/$upload_dir" . "/" . "$file\"></img></a>";
		echo "</p>";
	}
}
echo "</div>";
?>
