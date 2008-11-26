<?php
// adapt_observation.php
// allows a user to change his observation 

if(!$_GET['observation']) 
  throw new Exception("No observation selected");
echo "<div id=\"main\">";
echo "<h2>".LangChangeObservationTitle."</h2>";
echo "<form action=\"".$baseURL."index.php?indexAction=validate_change_observation\" method=\"post\" enctype=\"multipart/form-data\">";
tableNew("width=\"490\"");
tableNewRow();
tableFormatCell("class=\"fieldname\" width=\"100\"",LangViewObservationField1);
tableCell("<a href=\"deepsky/index?indexAction=detail_object&amp;object=".$objObservation->getObjectId($_GET['observation'])."\">".$objObservation->getObjectId($_GET['observation'])."</a>");
tableNextRow();
tableFormatCell("class=\"fieldname\"",LangViewObservationField2);
tableFormatCell('',"<a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=".$objObservation->getObserverId($_GET['observation'])."\">".$objObserver->getFirstName($objObservation->getObserverId($_GET['observation'])) . "&nbsp;" . $objObserver->getObserverName($objObservation->getObserverId($_GET['observation']))."</a>");
tableNewRow();
tableFormatCell("class=\"fieldname\"",LangViewObservationField5);
echo "<td>";
if($objObserver->getUseLocal($_SESSION['deepskylog_id']))
{ $date = sscanf($observations->getDsObservationLocalDate($_GET['observation']), "%4d%2d%2d");
  $timestr = $observations->getDsObservationLocalTime($_GET['observation']);
}
else
{ $date = sscanf($objObservation->getDateDsObservation($_GET['observation']),"%4d%2d%2d");
  $timestr = $objObservation->getTime($_GET['observation']);
}
if ($timestr >= 0)
  $time = sscanf(sprintf("%04d", $timestr), "%2d%2d");
else
{ $time[0] = -9;
  $time[1] = -9;
}
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"day\" value=\"".$date[2]."\" />";
echo "&nbsp;&nbsp;";
echo "<select name=\"month\">";
echo "<option value=\"\"></option>";
for($i=1;$i<13;$i++)
  echo "<option value=\"".$i."\"".(($date[1]==$i)?" selected=\"selected\"":"").">" . $GLOBALS['Month'.$i] . "</option>";  
echo "</select>";
echo "&nbsp;&nbsp";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"year\" value=\"".$date[0]."\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo (($objObserver->getUseLocal($_SESSION['deepskylog_id']))?LangViewObservationField9lt:LangViewObservationField9);
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"".(($time[0]>=0)?$time[0]:'')."\" />";
echo "&nbsp;&nbsp";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minutes\" value=\"".(($time[1]>=0)?$time[1]:'')."\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";                                                // LOCATION
echo LangViewObservationField4;
echo "</td>";
echo "<td>";
echo "<select name=\"location\">";
$locs=$objLocation->getSortedLocationsList("name", $_SESSION['deepskylog_id']);
$theLoc=$objObservation->getDsObservationLocationId($_GET['observation']);
while(list($key,$value)=each($locs))
  echo "<option ".(($value[0]==$theLoc)?"selected=\"selected\"":'')." value=\"".$value[0]."\">".$value[1]."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField3;
echo "</td>";
echo "<td>";                                                                    // INSTRUMENTS
echo "<select name=\"instrument\">";
$instr=$objInstrument->getSortedInstrumentsList("name", $_SESSION['deepskylog_id'],false,InstrumentsNakedEye);
$theInstr=$objObservation->getDsObservationInstrumentId($_GET['observation']);
while(list($key,$value)=each($instr))
	echo "<option ".(($theInstr==$value[0])?"selected=\"selected\"":'')." value=\"".$value[0]."\">".$value[1]."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<td class=\"fieldname\">";                                                // EYEPIECE
echo LangViewObservationField30;
echo "&nbsp;";
echo "</td>";
echo "<td>";
echo "<select name=\"eyepiece\">";
echo "<option value=\"\"></option>";
$eyeps=$objEyepiece->getSortedEyepiecesList("name", $_SESSION['deepskylog_id'], false);
$theEyepiece=$objObservation->getDsObservationEyepieceId($_GET['observation']);
while(list($key,$value)=each($eyeps))
  echo "<option value=\"".$value."\"".(($theEyepiece==$value)?" selected=\"selected\" ":'').">".$GLOBALS['objEyepiece']->getEyepieceName($value)."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField31;                                                // FILTER
echo "&nbsp;";
echo "</td>";
echo "<td>";
echo "<select name=\"filter\">";
echo "<option value=\"\"></option>";
$filts=$objFilter->getSortedFiltersList("name", $_SESSION['deepskylog_id'], false);
$theFilter=$objObservation->getDsObservationFilterId($_GET['observation']);
while(list ($key, $value) = each($filts)) // go through instrument array
  echo "<option value=\"".$value."\"".(($theFilter==$value)?" selected=\"selected\" ":'').">".$GLOBALS['objFilter']->getFilterName($value)."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField32;                                                // LENS
echo "&nbsp;";
echo "</td>";
echo "<td>";
echo "<select name=\"lens\">";
echo "<option value=\"\"></option>";
$lns=$objLens->getSortedLensesList("name", $_SESSION['deepskylog_id'],false);
$theLens=$objObservation->getDsObservationLensId($_GET['observation']);
while(list($key,$value)=each($lns))
  echo "<option value=\"".$value."\"".(($theLens==$value)?" selected=\"selected\" ":'').">".$GLOBALS['objLens']->getLensName($value)."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
 // SEEING
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField6;
echo "</td>";
echo "<td>";
echo "<select name=\"seeing\" style=\"width: 147px\">";
echo "<option value=\"-1\"></option>";
$theSeeing=$objObservation->getSeeing($_GET['observation']);
for($i=1;$i<6;$i++)
  echo "<option value=\"".$i."\"".(($theSeeing==$i)?" selected=\"selected\"":'').">".$GLOBALS['Seeing'.$i]."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField12;
echo "</td>";
echo "<td>";
echo "<input type=\"file\" name=\"drawing\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField7;
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"limit\" size=\"3\" value=\"".(($objObservation->getLimitingMagnitude($_GET['observation']))?(sprintf("%1.1f", $objObservation->getLimitingMagnitude($_GET['observation']))):'')."\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField22;                                                // Visibility
echo "</td>";
echo "<td>";
echo "<select name=\"visibility\">";
echo "<option value=\"0\"></option>";
$visibility = $objObservation->getVisibility($_GET['observation']);
for($i=1;$i<8;$i++)  
  echo "<option value=\"".$i."\"".(($visibility==$i)?" selected=\"selected\" ":'').">".$GLOBALS['Visibility'.$i]."</option>";
echo "</select>";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "<td class=\"fieldname\">";
echo LangViewObservationField29."&nbsp;*";                                      // Language of observation
echo "</td>";
echo "<td>";
$allLanguages=$objLanguage->getAllLanguages($objObserver->getLanguage($_SESSION['deepskylog_id']));
$theLang=$objObservation->getDsObservationLanguage($_GET['observation']);
echo "<select name=\"description_language\" style=\"width:147px\">";
while(list($key,$value)=each($allLanguages))
  echo "<option value=\"".$key."\" ".(($theLang==$key)?"selected=\"selected\"":'') .">".$value."</option>";
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
echo "<textarea name=\"description\" class=\"description\">".$objUtil->br2nl(html_entity_decode($objObservation->getDescriptionDsObservation($_GET['observation'])))."</textarea>";
echo "</td>";
echo "</tr>";
// ??? echo("</td></tr>"); error ??
echo "<tr>";
echo "<td colspan=\"2\">";
echo "<input type=\"submit\" name=\"changeobservation\" value=\"".LangChangeObservationButton."\" />";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<input type=\"hidden\" name=\"observationid\" value=\"".$_GET['observation']."\"></input>";
echo "</form>";
$upload_dir = 'drawings';                                                       //DRAWING
$dir = opendir($upload_dir);
while(FALSE!==($file=readdir($dir)))
{ if(("."==$file)OR(".."==$file))
    continue; 
  if(fnmatch($_GET['observation']."_resized.gif",$file)||fnmatch($_GET['observation']."_resized.jpg",$file) || fnmatch($_GET['observation']. "_resized.png", $file))
  { echo "<p>";
	  echo "<a href=\"deepsky/".$upload_dir."/".$_GET['observation'].".jpg"."\"><img class=\"account\" src=\"deepsky/$upload_dir"."/"."$file\"></img></a>";
		echo "</p>";
   }
}
echo "</div>";
?>
