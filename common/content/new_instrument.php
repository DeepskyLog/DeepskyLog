<?php
// new_instrument.php
// form which allows the user to add a new instrument 


$type = $objUtil->checkGetKey('type');
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  $type = $objInstrument->getInstrumentType($objUtil->checkGetKey('instrumentid'));
$tempInstrumentType= "<select name=\"type\">";
$tempInstrumentType.="<option ".(($type==InstrumentReflector)?        "selected=\"selected\" ":"")."value=\"".InstrumentReflector.        "\">".InstrumentsReflector."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentRefractor)?        "selected=\"selected\" ":"")."value=\"".InstrumentRefractor.        "\">".InstrumentsRefractor."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentCassegrain)?       "selected=\"selected\" ":"")."value=\"".InstrumentCassegrain.       "\">".InstrumentsCassegrain."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentSchmidtCassegrain)?"selected=\"selected\" ":"")."value=\"".InstrumentSchmidtCassegrain."\">".InstrumentsSchmidtCassegrain."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentKutter)?           "selected=\"selected\" ":"")."value=\"".InstrumentKutter.           "\">".InstrumentsKutter."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentMaksutov)?         "selected=\"selected\" ":"")."value=\"".InstrumentMaksutov.         "\">".InstrumentsMaksutov."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentBinoculars)?       "selected=\"selected\" ":"")."value=\"".InstrumentBinoculars.       "\">".InstrumentsBinoculars."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentFinderscope)?      "selected=\"selected\" ":"")."value=\"".InstrumentFinderscope.      "\">".InstrumentsFinderscope."</option>";
$tempInstrumentType.="<option ".(($type==InstrumentOther)?            "selected=\"selected\" ":"")."value=\"".InstrumentRest.             "\">".InstrumentsOther."</option>";
$tempInstrumentType.="</select>";

$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
// the code below looks very strange but it works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$insts=$objInstrument->getSortedInstruments($sort,$_SESSION['deepskylog_id']);
if((isset($_GET['sort']))&&($_GET['previous']==$_GET['sort']))                   // reverse sort when pushed twice
{ if ($_GET['sort'] == "name")
    $insts = array_reverse($insts, true);
  else
  { krsort($insts);
    reset($insts);
  }
  $previous = "";
}
else
  $previous = $sort;
$step = 25;
echo "<div id=\"main\">";
echo "<h2>".LangOverviewInstrumentsTitle."</h2>";
$link=$baseURL."index.php?indexAction=add_instrument&amp;sort=".$sort."&amp;previous=".$orig_previous;
list($min, $max) = $objUtil->printListHeader($insts, $link, $min, $step, "");
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=name&amp;previous=$previous\">".LangOverviewInstrumentsName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=diameter&amp;previous=$previous\">".LangOverviewInstrumentsDiameter."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=fd&amp;previous=$previous\">".LangOverviewInstrumentsFD."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=fixedMagnification&amp;previous=$previous\">".LangOverviewInstrumentsFixedMagnification."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=type&amp;previous=$previous\">".LangOverviewInstrumentsType."</a></td>";
echo "<td>".LangChangeAccountField8."</td>";
echo "<td></td>";
echo "</tr>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
$count = 0;
if(count($insts)>0)
{ while(list($key,$value)=each($insts))
  { $name = $objInstrument->getInstrumentName($value);
    $diameter = round($objInstrument->getDiameter($value), 0);
    $fd=round($objInstrument->getFd($value), 1);
    if($fd=="0")
      $fd = "-";
    $type = $objInstrument->getInstrumentType($value);
    $fixedMagnification = $objInstrument->getFixedMagnification($value);
    echo "<tr class=\"type".(2-($count%2))."\">";
		if ($name == "Naked eye")
      echo "<td><a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($value)."\">".InstrumentsNakedEye."</a></td>";
    else
      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_instrument&amp;instrument=".urlencode($value)."\">".$name."</a></td>";
    echo "<td align=\"center\">$diameter</td>\n";
    echo "<td align=\"center\">$fd</td>";
		echo "<td align=\"center\">";
    if($fixedMagnification>0)
      echo($fixedMagnification);
    else
      echo("-");
		echo "</td>";
		echo "<td>";
    if($type == InstrumentReflector) {echo(InstrumentsReflector);}
    if($type == InstrumentFinderscope) {echo(InstrumentsFinderscope);}
    if($type == InstrumentRefractor) {echo(InstrumentsRefractor);}
    if($type == InstrumentRest) {echo(InstrumentsOther);}
    if($type == InstrumentBinoculars) {echo(InstrumentsBinoculars);}
    if($type == InstrumentCassegrain) {echo(InstrumentsCassegrain);}
    if($type == InstrumentSchmidtCassegrain) {echo(InstrumentsSchmidtCassegrain);}
    if($type == InstrumentKutter) {echo(InstrumentsKutter);}
    if($type == InstrumentMaksutov) {echo(InstrumentsMaksutov);}
    echo "</td>";
		echo "<td align=\"center\">";
		// Radio button for the standard instrument
    if($value==$objObserver->getStandardTelescope($_SESSION['deepskylog_id']))
	    echo("<input type=\"radio\" name=\"stdtelescope\" value=\"". $value ."\" checked>&nbsp;<br>");
	  else
			echo("<input type=\"radio\" name=\"stdtelescope\" value=\"". $value ."\">&nbsp;<br>");
    echo "</td>";
		echo "<td>";
    $queries=array("instrument"=>$value,"observer"=>$_SESSION['deepskylog_id']);
    $obs=$objObservation->getObservationFromQuery($queries, "D", "1");
    $obscom = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
    if((count($obs)==0)&&(count($obscom)==0)) // no observations with instrument yet
      echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_instrument&amp;instrumentid=" . urlencode($value) . "\">" . LangRemove . "</a>");
    else echo "&nbsp;";
		echo "</td>";
		echo "</tr>";
    $count++;    
  }
}
echo "</table>";
echo "<input type=\"hidden\" name=\"adaption\" value=\"1\">";
echo "<input type=\"submit\" name=\"adapt\" value=\"" . LangAddInstrumentStdTelescope . "\" />";
echo "</form>";
list($min,$max)=$objUtil->printNewListHeader($insts,$link,$min,$step,"");
echo "</div>";
echo "<hr />";
echo "<h2>".LangAddInstrumentTitle."</h2>";
echo "<ol>";
echo "<li value=\"1\">";
echo LangAddInstrumentExisting;
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td width=\"25%\">";
echo "<form name=\"overviewform\">";
echo "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
echo "<option selected value=\"".$baseURL."index.php?indexAction=add_instrument\"> &nbsp; </option>";
$insts=$objInstrument->getSortedInstruments('name',"",true);
while(list($key,$value)=each($insts))
  echo "<option value=\"".$baseURL."index.php?indexAction=add_instrument&amp;instrumentid=".urlencode($value)."\">" . $objInstrument->getInstrumentName($value) . "</option>";
echo "</select>";
echo "</form>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</li>";
echo "</ol>";
echo "<p>".LangAddSiteFieldOr."</p>";
echo "<ol>";
echo "<li value=\"2\">".LangAddInstrumentManually."</li>";
echo "</ol>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";

echo "<table>";
tableFieldnameFieldExplanation(LangAddInstrumentField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\"  value=\"".stripslashes($objUtil->checkGetKey('instrumentname')).stripslashes($objInstrument->getInstrumentName($objUtil->checkGetKey('instrumentid')))."\" />",
                               "");

tableFieldnameFieldExplanation(LangAddInstrumentField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"diameter\" size=\"10\" value=\"".stripslashes($objUtil->checkGetKey('diameter')).stripslashes($objInstrument->getDiameter($objUtil->checkGetKey('instrumentid')))."\" />".
                               "<select name=\"diameterunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>",
                               "");
tableFieldnameFieldExplanation(LangAddInstrumentField5,$tempInstrumentType,"");
tableFieldnameFieldExplanation(LangAddInstrumentField4,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"focallength\" size=\"10\"  value=\"".stripslashes($objUtil->checkGetKey('focallength')).stripslashes($objInstrument->getInstrumentFocalLength($objUtil->checkGetKey('instrumentid')))."\" />".
                               "<select name=\"focallengthunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>".
                               "&nbsp;<span style=\"font-style:normal\">".LangAddInstrumentOr."&nbsp;".
                               LangAddInstrumentField3."</span>&nbsp;".
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"fd\" size=\"10\" value=\"".stripslashes($objUtil->checkGetKey('fd')).stripslashes($objInstrument->getFd($objUtil->checkGetKey('instrumentid')))."\" />",
                               "");

tableFieldnameFieldExplanation(LangAddInstrumentField6,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"fixedMagnification\" size=\"5\" value=\"".($objUtil->checkGetKey('fixedMagnification')).stripslashes($objInstrument->getFixedMagnification($objUtil->checkGetKey('instrumentid')))."\" />",
                               LangAddInstrumentField6Expl);
echo "</table>";
echo "<hr />";
echo "<input type=\"submit\" name=\"add\" value=\"".LangAddInstrumentAdd."\" />";
echo "</form>";
echo "</div>";
?>
