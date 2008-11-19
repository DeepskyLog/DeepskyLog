<?php
// new_observation.php
// GUI to add a new observation to the database

echo "<div id=\"main\">";
if(array_key_exists('object',$_GET)&&$_GET['object'])
{ $seen = $GLOBALS['objObject']->getDSOSeen($_GET['object']);
  echo "<h2>";
  echo LangNewObservationTitle . "&nbsp;" . $_GET['object'];
  echo "&nbsp;:&nbsp;".$seen;
  echo "</h2>";
  echo "<table width=\"100%\">";
	echo "<tr>";
  echo "<td width=\"25%\" align=\"left\">";
  if(substr($GLOBALS['objObject']->getSeen($_GET['object']),0,1)!="-")
    echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($_GET['object']) . "\">" . LangViewObjectObservations . " " . $_GET['object'];
  echo "</td>";
	echo "<td width=\"25%\" align=\"center\">";
  if(array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id'])
    echo("<a href=\"deepsky/index.php?indexAction=add_observation&object=" . urlencode($_GET['object']) . "\">" . LangViewObjectAddObservation . $_GET['object'] . "</a>");
  echo "</td>";
  if($myList)
  { echo "<td width=\"25%\" align=\"center\">";
    if($list->checkObjectInMyActiveList($_GET['object']))
      echo "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "&amp;removeObjectFromList=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>";
    else
      echo "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "&amp;addObjectToList=" . urlencode($_GET['object']) . "&amp;showname=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>";
    echo "</td>";
  }
  echo "</tr>";
  echo "</table>";
  $GLOBALS['objObject']->showObject($_GET['object']);
  echo "<ol>";
	echo "<li value=\"3\">" . LangNewObservationSubtitle3 . "</li>";
	echo "</ol>";
  echo "<p><p/>";
	echo "<form action=\"deepsky/index.php?indexAction=validate_observation&amp;object=".urlencode($_GET['object'])."\" method=\"post\" enctype=\"multipart/form-data\">";
	echo "<input type=\"hidden\" name=\"object\" value=\"".$_GET['object']."\">";
  echo "<table id=\"content\">";
  echo "<tr>";                                                                  // LOCATION
  echo "<tr><td class=\"fieldname\" align=\"right\">" . LangViewObservationField4 . "&nbsp;*</td>";
	echo "<td><select name=\"site\" style=\"width: 147px\">";
  $sites=$GLOBALS['objLocation']->getSortedLocationsList("name", $_SESSION['deepskylog_id']);
  $_POST['site']=$GLOBALS['objUtil']->checkPostKey('site',$GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id']));
  for ($i = 0;$i < count($sites);$i++)
    if(array_key_exists('newObsLocation', $_SESSION) && ($_SESSION['newObsLocation'] == $sites[$i][0])) // location equals session location
      echo "<option selected=\"selected\" value=\"".$sites[$i][0]."\">".$sites[$i][1]."</option>";
    else
      echo "<option value=\"".$sites[$i][0]."\">".$sites[$i][1]."</option>";
  echo "</select></td><td class=\"explanation\"><a href=\"common/add_site.php\">" . LangChangeAccountField7Expl ."</a>";
	echo "</td>";
  echo "</tr>";
  echo "<tr>";                                                                  //DATE  / TIME
  echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField5 . "&nbsp;*";
	echo "</td>";
  echo "<td>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"3\" name=\"day\" value=\"".$GLOBALS['objUtil']->checkPostKey('day')."\" />";
  echo "&nbsp;&nbsp;";
	echo "<select name=\"month\">";
  echo "<option value=\"\"></option>";
	for($i=1;$i<13;$i++)
    echo "<option value=\"".$i."\"".(($GLOBALS['objUtil']->checkPostKey('month')==$i)?" selected=\"selected\"":"").">" . $GLOBALS['Month'.$i] . "</option>";
  echo "</select>";
	echo "&nbsp;&nbsp";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"year\" value=\"".$GLOBALS['objUtil']->checkPostKey('year')."\" />";
  echo "</td>";
	echo "<td class=\"explanation\">".LangViewObservationField10."</td>";
  echo "<td class=\"fieldname\" align=\"right\">";
	echo (($observer->getUseLocal($_SESSION['deepskylog_id']))?LangViewObservationField9lt:LangViewObservationField9);
  echo  "</td>";
	echo "<td>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"".$GLOBALS['objUtil']->checkPostKey('hours')."\"";
	echo "&nbsp;&nbsp;";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minutes\" value=\"".$GLOBALS['objUtil']->checkPostKey('minutes')."\"";
  echo "</td>";
	echo "<td class=\"explanation\">";
	echo LangViewObservationField11;
	echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class=\"fieldname\" align=\"right\">";                              // LIMITING MAG
	echo LangViewObservationField7;
	echo "</td>";
	echo "<td>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"limit\" size=\"3\" value=\"".($GLOBALS['objUtil']->checkPostKey('limit')?sprintf("%1.1f", $GLOBALS['objUtil']->checkPostKey('limit')):'')."\" />";
  echo "&nbsp;&nbsp;";
	echo LangViewObservationField34;                                              // SQM
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"sqm\" size=\"4\" value=\"" .($GLOBALS['objUtil']->checkPostKey('sqm')?sprintf("%2.1f", $GLOBALS['objUtil']->checkPostKey('sqm')):'')."\" />";
  echo "</td>";
	echo "<td></td>";
  echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField6;                                               // SEEING
	echo "</td>";
  echo "<td>";
	echo "<select name=\"seeing\" style=\"width: 147px\">";
  echo "<option value=\"-1\"></option>";
	for($i=1;$i<6;$i++)
    echo "<option value=\"".$i."\"".(($GLOBALS['objUtil']->checkPostKey('seeing',0)==$i)?" selected=\"selected\"":'').">".$GLOBALS['Seeing'.$i]."</option>";
  echo "</select>&nbsp;";
	echo "</td>";
  echo "<td>";
	echo "</td>";
  echo "</tr>";
  echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "</tr>";
  echo "<tr>";
  echo "<td class=\"fieldname\" align=\"right\">";                              // INSTRUMENT
	echo LangViewObservationField3."&nbsp;*";
	echo "</td>";
  echo "<td>";
	echo "<select name=\"instrument\" style=\"width: 250px\">";
  echo "<option value=\"\"></option>";
  $instr=$GLOBALS['objInstrument']->getSortedInstrumentsList("name",$_SESSION['deepskylog_id'],false,InstrumentsNakedEye);
  while(list($key,$value)=each($instr))
		echo "<option ".(($GLOBALS['objUtil']->checkPostKey('instrument',0)==$value[0])?"selected=\"selected\"":(($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id'])==$value[0])?"selected=\"selected\"":''))." value=\"".$value[0]."\">".$value[1]."</option>";
  echo "</select>";
	echo "</td>";
  echo "<td class=\"explanation\">";
	echo "<a href=\"common/add_instrument.php\">" . LangChangeAccountField8Expl . "</a>";
	echo "</td>";
  echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField31 . "&nbsp;";
	echo "</td>";
  echo "<td> <select name=\"filter\" style=\"width: 147px\">";                  // FILTER
  echo "<option value=\"\"></option>";
  $filts = $GLOBALS['objFilter']->getSortedFiltersList("name", $_SESSION['deepskylog_id'],false);
  while(list($key,$value)=each($filts))
    echo "<option value=\"".$value."\"".(($GLOBALS['objUtil']->checkPostKey('filter')==$value)?" selected=\"selected\" ":'').">".$GLOBALS['objFilter']->getFilterName($value)."</option>";
  echo "</select>";
	echo "</td>";
  echo "<td class=\"explanation\">";
	echo "<a href=\"common/add_filter.php\">" . LangViewObservationField31Expl . "</a>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
  echo "<td class=\"fieldname\" align=\"right\">";
	echo LangViewObservationField30."&nbsp;";
	echo "</td>";
  echo "<td> <select name=\"eyepiece\" style=\"width: 147px\">";                // EYEPIECE
  echo "<option value=\"\"></option>";
  $eyeps = $GLOBALS['objEyepiece']->getSortedEyepiecesList("focalLength",$_SESSION['deepskylog_id'],false);
  while(list($key,$value)=each($eyeps))
    echo "<option value=\"".$value."\"".(($GLOBALS['objUtil']->checkPostKey('eyepiece')==$value)?" selected=\"selected\" ":'').">".$GLOBALS['objEyepiece']->getEyepieceName($value)."</option>";
  echo "</select>";
	echo "</td>";
  echo "<td class=\"explanation\">";
	echo "<a href=\"common/add_eyepiece.php\">".LangViewObservationField30Expl."</a>";
  echo "</td>";
  echo "<td class=\"fieldname\" align=\"right\">".LangViewObservationField32."&nbsp;";
	echo "</td>";
  echo "<td> <select name=\"lens\" style=\"width: 147px\">";                 // LENS
  echo "<option value=\"\"></option>";
  $lns = $GLOBALS['objLens']->getSortedLensesList("name",$_SESSION['deepskylog_id'],false);
  while(list ($key, $value) = each($lns))
    echo "<option value=\"".$value."\"".(($GLOBALS['objUtil']->checkPostKey('lens')==$value)?" selected=\"selected\" ":'').">".$GLOBALS['objLens']->getLensName($value)."</option>";
  echo "</select>";
	echo "</td>";
  echo "<td class=\"explanation\">";
	echo "<a href=\"common/add_lens.php\">" . LangViewObservationField32Expl . "</a>";
  echo "</td>";
  echo "</tr>";
  echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "</tr>";
  // VISIBILITY / DRAWING
  echo("<tr>");
  // Visibility of observations
  echo("<td class=\"fieldname\" align=\"right\">" . LangViewObservationField22 . "</td>
	           <td><select name=\"visibility\"><option value=\"0\"></option>");
  // Very simple, prominent object
  echo("<option value=\"1\">".LangVisibility1."</option>");
  // Object easily percepted with direct vision
  echo("<option value=\"2\">".LangVisibility2."</option>");
  // Object perceptable with direct vision
  echo("<option value=\"3\">".LangVisibility3."</option>");
  // Averted vision required to percept object
  echo("<option value=\"4\">".LangVisibility4."</option>");
  // Object barely perceptable with averted vision
  echo("<option value=\"5\">".LangVisibility5."</option>");
  // Perception of object is very questionable
  echo("<option value=\"6\">".LangVisibility6."</option>");
  // Object definitely not seen
  echo("<option value=\"7\">".LangVisibility7."</option>");
  echo("</select></td>");
  echo("<td></td>");
  //DRAWING
  echo("<td class=\"fieldname\" align=\"right\">".LangViewObservationField12."</td>");
  echo("<td colspan=\"2\"><input type=\"file\" name=\"drawing\" /></td>");
  echo("</tr>");

  // Small/Large diameter
  echo("<tr>");
  // Visibility of observations
  echo("<td class=\"fieldname\" align=\"right\">" . LangViewObservationField33 . "</td>
            <td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"largeDiam\" size=\"5\"> 
             x <input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"smallDiam\" size=\"5\"> " 
            . "<select name=\"size_units\"> <option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>
                                     <option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>
      </select></td>");
  echo("</tr>");

  // DESCRIPTION
  echo("<tr>");
  echo("<td class=\"fieldname\" align=\"right\">" . LangViewObservationField8 . "&nbsp;*");
  echo("<br>");
  echo("<a href=\"http://www.deepsky.be/beschrijfobjecten.php\" target=\"new_window\">" . LangViewObservationFieldHelpDescription . "</a></td>");
  echo("<td width=\"100%\" colspan=\"5\">");
  echo("<textarea name=\"description\" class=\"description\">");
  // keep description after wrong observation
  if(array_key_exists('newObsDescription', $_SESSION) && ($_SESSION['newObsDescription'] != ""))
  echo $_SESSION['newObsDescription'];
  echo("</textarea>");
  echo("</td>");
  echo("</tr>");

  echo("<tr>");
  echo("<td></td>");
  echo("<td>");
  echo("<input type=\"submit\" name=\"addobservation\" value=\"".LangViewObservationButton1."\" />&nbsp;");
  echo("<input type=\"submit\" name=\"clearfields\" value=\"".LangViewObservationButton2."\" /></td>");
  echo("<td align=\"right\">");
  // Language of observation
  if(array_key_exists('newObsLanguage', $_SESSION) && array_key_exists('newObsSavedata', $_SESSION) && ($_SESSION['newObsSavedata'] == "yes"))
    $current_language = $_SESSION['newObsLanguage'];
  else
    $current_language = $obs->getObservationLanguage($_SESSION['deepskylog_id']);
  echo("<td class=\"fieldname\" align=\"right\">" . LangViewObservationField29 . "&nbsp;*</td><td>");
  $language = new Language();
  $allLanguages = $language->getAllLanguages($obs->getLanguage($_SESSION['deepskylog_id']));
  echo("<select name=\"description_language\" style=\"width: 147px\">");
  while(list ($key, $value) = each($allLanguages))
    if($current_language == $key)
      print("<option value=\"".$key."\" selected=\"selected\">".$value."</option>\n");
    else
      print("<option value=\"".$key."\">".$value."</option>\n");
  echo("</select></td>");
  echo("</tr>");
  echo("</table>");
  echo("<input type=\"hidden\" name=\"observedobject\" value=\"" . $_GET['object'] . "\"></form>");
}
else // no object found or not pushed on search button yet
{ echo "<h2>";
  echo (LangNewObservationTitle);
  echo "</h2>";
  // upper form
  echo "<form action=\"deepsky/index.php?indexAction=add_observation\" method=\"post\">";
  echo "<ol>";
	echo "<li value=\"1\">" . LangNewObservationSubtitle1a.LangNewObservationSubtitle1abis;
  echo "<a href=\"deepsky/index.php?indexAction=add_csv\">" . LangNewObservationSubtitle1b . "</a>";
  echo "</li>";
	echo "</ol>";
  echo "<table width=\"100%\" id=\"content\">";
  // OBJECT NAME
  echo "<tr>";
  echo "<td class=\"fieldname\">";
  echo LangQueryObjectsField1;
  echo "</td>";
	echo "<td colspan=\"2\">";
  echo "<select name=\"catalogue\">";
  echo "<option value=\"\"></option>";
  $catalogs = $GLOBALS['objObject']->getCatalogues();
  while(list($key, $value) = each($catalogs))
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
  // end upper form
  //OBSOLETE? $_SESSION['backlink'] = "new_observation.php";
}
?>
