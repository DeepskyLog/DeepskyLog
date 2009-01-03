<?php


// new_observation.php
// GUI to add a new observation to the database

echo "<div id=\"main\">";
$object=$objUtil->checkPostKey('object', $objUtil->checkGetKey('object'));
if($object&&($GLOBALS['objUtil']->checkArrayKey($_SESSION,'addObs',0)==$GLOBALS['objUtil']->checkPostKey('timestamp',-1)))
{ $seen = $GLOBALS['objObject']->getDSOSeen($object);
	echo "<h2>";
	echo LangNewObservationTitle . "&nbsp;" . $object;
	echo "&nbsp;:&nbsp;" . $seen;
	echo "</h2>";
	echo "<table width=\"100%\">";
	echo "<tr>";
	echo "<td width=\"25%\" align=\"left\">";
	if (substr($GLOBALS['objObject']->getSeen($object), 0, 1) != "-")
		echo "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "\">" . LangViewObjectObservations . " " . $object;
	echo "</td>";
	echo "<td width=\"25%\" align=\"center\">";
	if (array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])
		echo "<a href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;object=" . urlencode($object) . "\">" . LangViewObjectAddObservation . $object . "</a>";
	echo "</td>";
	if ($myList) {
		echo "<td width=\"25%\" align=\"center\">";
		if ($objList->checkObjectInMyActiveList($object))
			echo "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>";
		else
			echo "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>";
		echo "</td>";
	}
	echo "</tr>";
	echo "</table>";
	$GLOBALS['objObject']->showObject($object);
	echo "<h2>".LangNewObservationSubtitle3."<span class=\"requiredField\">".LangNewObservationSubtitle3A."</span></h2>";
	echo "<p><p/>";
	echo "<form action=\"" . $baseURL . "index.php\" method=\"post\" enctype=\"multipart/form-data\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_observation\">";
	echo "<input type=\"hidden\" name=\"timestamp\" value=\"" . $_POST['timestamp'] . "\">";
	echo "<input type=\"hidden\" name=\"object\" value=\"" . $object . "\">";
	echo "<table id=\"content\">";
	echo "<tr>"; //=================================================================================================================== LOCATION
	echo "<td class=\"fieldname\" align=\"right\" style=\"width:16%\">" . LangViewObservationField4 . "&nbsp;*</td>";
	echo "<td style=\"width:16%\"><select class=\"inputfield requiredField\" style=\"width:300px\" name=\"site\">";
	$sites = $GLOBALS['objLocation']->getSortedLocationsList("name", $_SESSION['deepskylog_id']);
	while (list ($key, $value) = each($sites))
		echo "<option " . (($GLOBALS['objUtil']->checkPostKey('site', 0) == $value[0]) ? "selected=\"selected\"" : (($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']) == $value[0]) ? "selected=\"selected\"" : '')) . " value=\"" . $value[0] . "\">" . $value[1] . "</option>";
	echo "</select></td>";
	echo "<td class=\"explanation\" style=\"width:16%\"><a href=\"" . $baseURL . "index.php?indexAction=add_site\">" . LangChangeAccountField7Expl . "</a>";
	echo "</td>";
	echo "<td  style=\"width:16%\">&nbsp</td>";
	echo "<td  style=\"width:16%\">&nbsp</td>";
	echo "<td  style=\"width:20%\">&nbsp</td>";
	echo "</tr>";
	echo "<tr>"; //=================================================================================================================== DATE  / TIME
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField5 . "&nbsp;*";
	echo "</td>";
	echo "<td>";
	echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" size=\"3\" style=\"text-align:center\" name=\"day\" value=\"" . $GLOBALS['objUtil']->checkPostKey('day') . "\" />";
	echo "&nbsp;&nbsp;";
	echo "<select name=\"month\" style=\"text-align:center\" class=\"inputfield requiredField\">";
	echo "<option value=\"\"></option>";
	for($i= 1;$i<13;$i++)
		echo "<option value=\"".$i."\"".(($GLOBALS['objUtil']->checkPostKey('month')==$i)?" selected=\"selected\"" : "").">".$GLOBALS['Month'.$i]."</option>";
	echo "</select>";
	echo "&nbsp;&nbsp";
	echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"4\" size=\"4\" style=\"text-align:center\" name=\"year\" value=\"" . $GLOBALS['objUtil']->checkPostKey('year') . "\" />";
	echo "</td>";
	echo "<td class=\"explanation\">" . LangViewObservationField10 . "</td>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo (($objObserver->getUseLocal($_SESSION['deepskylog_id'])) ? LangViewObservationField9lt : LangViewObservationField9);
	echo "</td>";
	echo "<td>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" style=\"text-align:center\" name=\"hours\" value=\"" . $GLOBALS['objUtil']->checkPostKey('hours') . "\">";
	echo "&nbsp;&nbsp;";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" style=\"text-align:center\" name=\"minutes\" value=\"" . $GLOBALS['objUtil']->checkPostKey('minutes') . "\">";
	echo "</td>";
	echo "<td class=\"explanation\">";
	echo LangViewObservationField11;
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td class=\"fieldname\" align=\"right\">"; //============================================================================================== LIMITING MAG
	echo LangViewObservationField7;
	echo "</td>";
	echo "<td>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"limit\" size=\"3\" style=\"text-align:center\" value=\"" . ($GLOBALS['objUtil']->checkPostKey('limit') ? sprintf("%1.1f", $GLOBALS['objUtil']->checkPostKey('limit')) : '') . "\" />";
	echo "&nbsp;&nbsp;";
	echo LangViewObservationField34 . "&nbsp;"; // SQM
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sqm\" size=\"4\" style=\"text-align:center\" value=\"" . ($GLOBALS['objUtil']->checkPostKey('sqm') ? sprintf("%2.1f", $GLOBALS['objUtil']->checkPostKey('sqm')) : '') . "\" />";
	echo "</td>";
	echo "<td></td>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField6; //=============================================================================================================== SEEING
	echo "</td>";
	echo "<td>";
	echo "<select name=\"seeing\" style=\"width:300px\" class=\"inputfield\">";
	echo "<option value=\"-1\"></option>";
	for ($i = 1; $i < 6; $i++)
		echo "<option value=\"" . $i . "\"" . (($GLOBALS['objUtil']->checkPostKey('seeing', 0) == $i) ? " selected=\"selected\"" : '') . ">" . $GLOBALS['Seeing' . $i] . "</option>";
	echo "</select>&nbsp;";
	echo "</td>";
	echo "<td>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td class=\"fieldname\" align=\"right\">"; // INSTRUMENT
	echo LangViewObservationField3 . "&nbsp;*";
	echo "</td>";
	echo "<td>";
	echo "<select name=\"instrument\" style=\"width:300px\" class=\"inputfield requiredField\">";
	echo "<option value=\"\"></option>";
	$instr = $GLOBALS['objInstrument']->getSortedInstrumentsList("name", $_SESSION['deepskylog_id'], false);
	while (list ($key, $value) = each($instr))
		echo "<option " . (($GLOBALS['objUtil']->checkPostKey('instrument', 0) == $value[0]) ? "selected=\"selected\"" : (($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id']) == $value[0]) ? "selected=\"selected\"" : '')) . " value=\"" . $value[0] . "\">" . $value[1] . "</option>";
	echo "</select>";
	echo "</td>";
	echo "<td class=\"explanation\">";
	echo "<a href=\"" . $baseURL . "index.php?indexAction=add_instrument\">" . LangChangeAccountField8Expl . "</a>";
	echo "</td>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField31 . "&nbsp;";
	echo "</td>";
	echo "<td> <select name=\"filter\" style=\"width:300px\" class=\"inputfield\">"; //==================================================================== FILTER
	echo "<option value=\"\"></option>";
	$filts = $GLOBALS['objFilter']->getSortedFiltersList("name", $_SESSION['deepskylog_id'], false);
	while (list ($key, $value) = each($filts))
		echo "<option value=\"" . $value . "\"" . (($GLOBALS['objUtil']->checkPostKey('filter') == $value) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['objFilter']->getFilterName($value) . "</option>";
	echo "</select>";
	echo "</td>";
	echo "<td class=\"explanation\">";
	echo "<a href=\"" . $baseURL . "index.php?indexAction=add_filter\">" . LangViewObservationField31Expl . "</a>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField30 . "&nbsp;";
	echo "</td>";
	echo "<td> <select name=\"eyepiece\" style=\"width:300px\" class=\"inputfield\">"; //=================================================================== EYEPIECE
	echo "<option value=\"\"></option>";
	$eyeps = $GLOBALS['objEyepiece']->getSortedEyepiecesList("focalLength", $_SESSION['deepskylog_id'], false);
	while (list ($key, $value) = each($eyeps))
		echo "<option value=\"" . $value . "\"" . (($GLOBALS['objUtil']->checkPostKey('eyepiece') == $value) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['objEyepiece']->getEyepieceName($value) . "</option>";
	echo "</select>";
	echo "</td>";
	echo "<td class=\"explanation\">";
	echo "<a href=\"" . $baseURL . "index.php?indexAction=add_eyepiece\">" . LangViewObservationField30Expl . "</a>";
	echo "</td>";
	echo "<td class=\"fieldname\" align=\"right\">" . LangViewObservationField32 . "&nbsp;";
	echo "</td>";
	echo "<td> <select name=\"lens\" style=\"width:300px\" class=\"inputfield\">"; //========================================================================= LENS
	echo "<option value=\"\"></option>";
	$lns = $GLOBALS['objLens']->getSortedLensesList("name", $_SESSION['deepskylog_id'], false);
	while (list ($key, $value) = each($lns))
		echo "<option value=\"" . $value . "\"" . (($GLOBALS['objUtil']->checkPostKey('lens') == $value) ? " selected=\"selected\" " : '') . ">" . $GLOBALS['objLens']->getLensName($value) . "</option>";
	echo "</select>";
	echo "</td>";
	echo "<td class=\"explanation\">";
	echo "<a href=\"" . $baseURL . "index.php?indexAction=add_lens\">" . LangViewObservationField32Expl . "</a>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "</tr>";
	echo "<tr>";

	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField22;
	echo "</td>";
	echo "<td>"; //====================================================================================================================== Visibility of observations
	echo "<select name=\"visibility\" style=\"width:300px\" class=\"inputfield\"><option value=\"0\"></option>";
	for($i=1;$i<8;$i++)
		echo "<option value=\"".$i."\" ".(($objUtil->checkPostKey('visibility')==$i)?"selected ":"").">".$GLOBALS['Visibility'.$i]."</option>";
	echo "</select>";
	echo "</td>";
	echo "<td> </td>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField12; //====================================================================================================DRAWING
	echo "</td>";
	echo "<td colspan=\"2\">";
	echo "<input type=\"file\" name=\"drawing\" file=\"" . $GLOBALS['objUtil']->checkPostKey('drawing') . "\ class=\"inputfield\" style=\"width:300px\"/>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField33; //============================================================================================================== Estimated diameter
	echo "</td>";
	echo "<td>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"largeDiam\" size=\"5\" style=\"text-align:center\" value=\"" . $GLOBALS['objUtil']->checkPostKey('largeDiam') . "\">";
	echo "&nbsp;x&nbsp;";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"smallDiam\" size=\"5\" style=\"text-align:center\" value=\"" . $GLOBALS['objUtil']->checkPostKey('smallDiam') . "\">";
	echo "&nbsp;";
	echo "<select name=\"size_units\" class=\"inputfield\">";
	echo "<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>";
	echo "<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
	echo "</select>";
	echo "</td>";
	if($GLOBALS['objObject']->getDsoProperty($object,'type')=="GALXY") 
	{ echo "<td>";
		echo "<input type=\"checkbox\" name=\"stellar\" ".($objUtil->checkPostKey("stellar")?"checked ":"")."/>" . LangViewObservationField35;
		echo "</td>";
		echo "<td>";
		echo "<input type=\"checkbox\" name=\"extended\" ".($objUtil->checkPostKey("extended")?"checked ":"")." />" . LangViewObservationField36;
		echo "</td>";
		echo "<td>";
		echo "<input type=\"checkbox\" name=\"resolved\" ".($objUtil->checkPostKey("resolved")?"checked ":"")."/>" . LangViewObservationField37;
		echo "</td>";
		echo "<td>";
		echo "<input type=\"checkbox\" name=\"mottled\" ".($objUtil->checkPostKey("mottled")?"checked ":"")."/>" . LangViewObservationField38;
		echo "</td>";
		echo "</tr>";
	}
	else
	{
		  
	}
	// Some extra fields when we are describing open clusters, or asterisms...
	if(in_array($GLOBALS['objObject']->getDsoProperty($object,'type'),array("ASTER","CLANB","DS","OPNCL","AA1STAR","AA2STAR","AA3STAR","AA4STAR","AA8STAR"))) 
	{ echo "</tr> <tr>";
    echo "<td class=\"fieldname\" align=\"right\">";
		echo LangViewObservationField40;
		echo "</td>";
		echo "<td>";
		echo "<select name=\"characterType\" class=\"inputfield\" style=\"width:300px\"><option value=\"\"></option>";
		echo "<option value=\"A\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'A') ? " selected=\"selected\" " : '') . ">A - ".$ClusterTypeA."</option>";
		echo "<option value=\"B\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'B') ? " selected=\"selected\" " : '') . ">B - ".$ClusterTypeB."</option>";
		echo "<option value=\"C\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'C') ? " selected=\"selected\" " : '') . ">C - ".$ClusterTypeC."</option>";
		echo "<option value=\"D\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'D') ? " selected=\"selected\" " : '') . ">D - ".$ClusterTypeD."</option>";
		echo "<option value=\"E\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'E') ? " selected=\"selected\" " : '') . ">E - ".$ClusterTypeE."</option>";
		echo "<option value=\"F\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'F') ? " selected=\"selected\" " : '') . ">F - ".$ClusterTypeF."</option>";
		echo "<option value=\"G\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'G') ? " selected=\"selected\" " : '') . ">G - ".$ClusterTypeG."</option>";
		echo "<option value=\"H\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'H') ? " selected=\"selected\" " : '') . ">H - ".$ClusterTypeH."</option>";
		echo "<option value=\"I\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'I') ? " selected=\"selected\" " : '') . ">I - ".$ClusterTypeI."</option>";
		echo "<option value=\"X\"" . (($GLOBALS['objUtil']->checkPostKey('characterType') == 'X') ? " selected=\"selected\" " : '') . ">J - ".$ClusterTypeX."</option>";
		echo "</select>";
		echo "</td>";
		echo "<td class=\"explanation\">";
		echo "<a href=\"http://www.deepskylog.org/wiki/bin/view/DeepskyLog/CharacterType" . 
		    $GLOBALS['objObserver']->getLanguage($_SESSION['deepskylog_id']) . "\" target=\"_blank\">" . LangViewObservationField40Expl . "</a>";
		echo "</td>";
			echo "<td>";
		echo "<input type=\"checkbox\" name=\"unusualShape\" />" . LangViewObservationField41;
		echo "</td>";
		echo "<td>";
		echo "<input type=\"checkbox\" name=\"partlyUnresolved\" />" . LangViewObservationField42;
		echo "</td>";
		echo "<td>";
		echo "<input type=\"checkbox\" name=\"colorContrasts\" />" . LangViewObservationField43;
		echo "</td>";
		echo "</tr>";
	}
	echo "<tr>";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField8 . "&nbsp;*"; // DESCRIPTION
	echo "<br />";
	echo "<a href=\"http://www.deepsky.be/beschrijfobjecten.php\" target=\"new_window\">" . LangViewObservationFieldHelpDescription . "</a>";
	echo "</td>";
	echo "<td width=\"100%\" colspan=\"5\">";
	echo "<textarea name=\"description\" class=\"description inputfield requiredField\">";
	echo $GLOBALS['objUtil']->checkPostKey('description');
	echo "</textarea>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td>";
	echo "<td>";
	echo "<input type=\"submit\" name=\"addobservation\" value=\"" . LangViewObservationButton1 . "\" />&nbsp;";
	echo "<input type=\"submit\" name=\"clear_observation\" value=\"" . LangViewObservationButton2 . "\" />";
	echo "</td>";
	echo "<td align=\"right\">";
	echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField29 . "&nbsp;*"; // Language of observation
	echo "</td>";
	echo "<td>";
	$description_language = $GLOBALS['objUtil']->checkPostKey('visibility', $objObserver->getObservationLanguage($_SESSION['deepskylog_id']));
	$allLanguages = $objLanguage->getAllLanguages($objObserver->getLanguage($_SESSION['deepskylog_id']));
	echo "<select name=\"description_language\" >";
	while (list ($key, $value) = each($allLanguages))
		echo "<option value=\"" . $key . "\"" . (($GLOBALS['objUtil']->checkPostKey('description_language') == $key) ? "selected=\"selected\"" : ($GLOBALS['objObserver']->getObservationLanguage($_SESSION['deepskylog_id']) == $key) ? "selected=\"selected\"" : '') . ">" . $value . "</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
} 
else // no object found or not pushed on search button yet
{ echo "<h2>";
	echo (LangNewObservationTitle);
	echo "</h2>";
	
	echo "<ol>";
	echo "<li value=\"1\">" . LangNewObservationSubtitle1a . LangNewObservationSubtitle1abis;
	echo "<a href=\"" . $baseURL . "index.php?indexAction=add_csv\">" . LangNewObservationSubtitle1b . "</a>";
	echo "</li>";
	echo "</ol>";
	echo "<form action=\"" . $baseURL . "index.php?indexAction=add_observation\" method=\"post\">";
	echo "<table width=\"100%\" id=\"content\">";
	// OBJECT NAME
	echo "<tr>";
	echo "<td class=\"fieldname\">";
	echo LangQueryObjectsField1;
	echo "</td>";
	echo "<td colspan=\"2\">";
	echo "<select name=\"catalog\" class=\"inputfield\">";
	echo "<option value=\"\"></option>";
	$catalogs = $GLOBALS['objObject']->getCatalogs();
	while (list ($key, $value) = each($catalogs))
		echo "<option value=\"$value\">$value</option>";
	echo "</select>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"number\" size=\"50\" value=\"\" />";
	echo "</td>";
	echo "<td>";
	echo "<input type=\"submit\" name=\"objectsearch\" value=\"" . LangNewObservationButton1 . "\" />";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}
?>
