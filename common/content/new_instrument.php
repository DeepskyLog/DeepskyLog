<?php
// new_instrument.php
// form which allows the user to add a new instrument 

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
echo "<form action=\"".$baseURL."index.php?indexAction=validate_instrument\" method=\"post\">";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField1."</td>";
echo "<td><input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"instrumentname\" size=\"30\"  value=\"";
if($objUtil->checkGetKey('instrumentname'))
  echo stripslashes($_GET['instrumentname']);
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  echo stripslashes($objInstrument->getInstrumentName($_GET['instrumentid']));
echo "\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField2."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"diameter\" size=\"10\" value=\"";
if(array_key_exists('diameter',$_GET) && $_GET['diameter'])
  echo stripslashes($_GET['diameter']);
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  echo stripslashes($objInstrument->getDiameter($_GET['instrumentid']));
echo "\" />";
echo "<select name=\"diameterunits\">";
echo "<option>inch</option>";
echo "<option selected=\"selected\">mm</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField5."</td>";
echo "<td>";
echo "<select name=\"type\">";
if(array_key_exists('type',$_GET) && $_GET['type'])
  $type = $_GET['type'];
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  $type = $objInstrument->getInstrumentType($_GET['instrumentid']);
echo "<option ".(($type==InstrumentReflector)?        "selected=\"selected\" ":"")."value=\"".InstrumentReflector.        "\">".InstrumentsReflector."</option>";
echo "<option ".(($type==InstrumentRefractor)?        "selected=\"selected\" ":"")."value=\"".InstrumentRefractor.        "\">".InstrumentsRefractor."</option>";
echo "<option ".(($type==InstrumentCassegrain)?       "selected=\"selected\" ":"")."value=\"".InstrumentCassegrain.       "\">".InstrumentsCassegrain."</option>";
echo "<option ".(($type==InstrumentSchmidtCassegrain)?"selected=\"selected\" ":"")."value=\"".InstrumentSchmidtCassegrain."\">".InstrumentsSchmidtCassegrain."</option>";
echo "<option ".(($type==InstrumentKutter)?           "selected=\"selected\" ":"")."value=\"".InstrumentKutter.           "\">".InstrumentsKutter."</option>";
echo "<option ".(($type==InstrumentMaksutov)?         "selected=\"selected\" ":"")."value=\"".InstrumentMaksutov.         "\">".InstrumentsMaksutov."</option>";
echo "<option ".(($type==InstrumentBinoculars)?       "selected=\"selected\" ":"")."value=\"".InstrumentBinoculars.       "\">".InstrumentsBinoculars."</option>";
echo "<option ".(($type==InstrumentFinderscope)?      "selected=\"selected\" ":"")."value=\"".InstrumentFinderscope.      "\">".InstrumentsFinderscope."</option>";
echo "<option ".(($type==InstrumentOther)?            "selected=\"selected\" ":"")."value=\"".InstrumentRest.             "\">".InstrumentsOther."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField4."</td>";
echo "<td><input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"focallength\" size=\"10\"  value=\"";
if(array_key_exists('focallength',$_GET) && $_GET['focallength'])
  echo stripslashes($_GET['focallength']);
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  echo stripslashes($objInstrument->getInstrumentFocalLength($_GET['instrumentid']));
echo "\" />";
echo "<select name=\"focallengthunits\">";
echo "<option>inch</option>";
echo "<option selected=\"selected\">mm</option>";
echo "</select>";
//echo "</td>";
//echo "</tr>";
//echo "<tr>";
echo "&nbsp;".LangAddInstrumentOr;
//echo "<td></td>";
// echo "</tr>";
// echo "<tr>";
echo "&nbsp;".LangAddInstrumentField3;
echo "&nbsp;<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"fd\" size=\"10\" value=\"";
if(array_key_exists('fd',$_GET) && $_GET['fd'])
  echo stripslashes($_GET['fd']);
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  echo stripslashes($objInstrument->getFd($_GET['instrumentid']));
echo "\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddInstrumentField6."</td>";
echo "<td><input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"fixedMagnification\" size=\"5\" value=\"";
if(array_key_exists('fixedMagnification',$_GET) && $_GET['fixedMagnification']) 
  echo ($_GET['fixedMagnification']);
if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
  echo stripslashes($objInstrument->getFixedMagnification($_GET['instrumentid']));
echo "\" />&nbsp;".LangAddInstrumentField6Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"add\" value=\"".LangAddInstrumentAdd."\" /></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
