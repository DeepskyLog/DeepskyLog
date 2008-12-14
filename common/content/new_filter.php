<?php
// new_filter.php
// allows the user to add a new filter

$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
// the code below looks very strange but it works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$filts=$objFilter->getSortedFilters($sort, $_SESSION['deepskylog_id']);
if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{ if ($_GET['sort'] == "name")
    $filts = array_reverse($filts, true);
  else
  { krsort($filts);
    reset($filts);
  }
  $previous = ""; // reset previous field to sort on
}
else
  $previous = $sort;
$step = 25;
echo "<div id=\"main\">";
echo "<h2>".LangOverviewFilterTitle."</h2>";
$link=$baseURL."index.php?indexAction=add_filter&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;
list($min, $max)=$objUtil->printListHeader($filts, $link, $min, $step, "");
echo "<table>";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_filter&amp;sort=name&amp;previous=$previous\">".LangViewFilterName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_filter&amp;sort=type&amp;previous=$previous\">".LangViewFilterType."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_filter&amp;sort=color&amp;previous=$previous\">".LangViewFilterColor."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_filter&amp;sort=wratten&amp;previous=$previous\">".LangViewFilterWratten."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_filter&amp;sort=schott&amp;previous=$previous\">".LangViewFilterSchott."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
if(count($filts)>0)
{ while(list ($key, $value) = each($filts))
  { $name = stripslashes($objFilter->getFilterName($value));
    $type = $objFilter->getFilterType($value);
    $color = $objFilter->getColor($value);
    $wratten = $objFilter->getWratten($value);
    $schott = $objFilter->getSchott($value);
    echo "<tr class=\"type".(2-($count%2))."\">";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_filter&amp;filter=".urlencode($value)."\">$name</a></td>";
    echo "<td>";
    if($type == FilterOther) {echo(FiltersOther);}
    if($type == FilterBroadBand) {echo(FiltersBroadBand);}
    if($type == FilterNarrowBand) {echo(FiltersNarrowBand);}
    if($type == FilterOIII) {echo(FiltersOIII);}
    if($type == FilterHBeta) {echo(FiltersHBeta);}
    if($type == FilterHAlpha) {echo(FiltersHAlpha);}
    if($type == FilterColor) {echo(FiltersColor);}
    if($type == FilterNeutral) {echo(FiltersNeutral);}
    if($type == FilterCorrective) {echo(FiltersCorrective);}
    echo "</td>";
    echo "<td>";
    echo ($wratten?$wratten:"-");
    echo "</td>";
    echo "<td>";
    echo ($schott?$schott:"-"); 
	  echo "</td>";
    echo "<td>";
    $queries=array("filter"=>$value,"observer"=>$_SESSION['deepskylog_id']);  // check if there are no observations made with this filter
    $obs=$objObservation->getObservationFromQuery($queries,"","1","False");
    //           $queries = array("eyepiece" => $value);                      // No filters yet for comet observations!!
    //           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
    if(!sizeof($obs) > 0) // no observations from location yet
      echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode($value) . "\">" . LangRemove . "</a>";
    echo "</td>";
    echo "</tr>";
    $count++;
	}
}
echo "</table>";
list($min,$max)=$objUtil->printListHeader($filts,$link,$min,$step,"");
echo "</div>";
echo "<h2>";
echo LangAddFilterTitle;
echo "</h2>";
echo "<ol>";
echo "<li value=\"1\">";
echo LangAddFilterExisting;
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td width=\"25%\">";
echo "<form name=\"overviewform\">";		
echo "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$filts=$objFilter->getSortedFilters('name', "", true);
while(list($key, $value) = each($filts))
  echo("<option value=\"".$baseURL."index.php?indexAction=add_filter&amp;filterid=".urlencode($value)."\">" . $objFilter->getFilterName($value) . "</option>\n");
echo "</select>";
echo "</form>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</li>";
echo "</ol>";
echo "<p>";
echo LangAddSiteFieldOr;
echo "</p>";
echo "<ol><li value=\"2\">".LangAddFilterFieldManually."</li></ol>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddFilterField1;
echo "</td>";
echo "<td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\""; 
echo stripslashes($objUtil->checkGetKey('filtername',''));
if($objUtil->checkGetKey('filterid'))
  echo stripslashes($objFilter->getFilterName($_GET['filterid'])); 
echo "\" />";
echo "</td>";
echo "<td class=\"explanation\">";
echo LangAddFilterField1Expl;
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddFilterField2;
echo "</td>";
echo "<td>";
echo "<select name=\"type\">";
$type=$objUtil->checkGetKey('type');
if($objUtil->checkGetKey('filterid'))
  $type=$objFilter->getFilterType($_GET['filterid']);
echo "<option ".(($type==FilterOther)?"selected=\"selected\" ":"")."value=\"".FilterOther."\">".FiltersOther."</option>";
echo "<option ".(($type==FilterBroadBand)?"selected=\"selected\" ":"")."value=\"".FilterBroadBand."\">".FiltersBroadBand."</option>";
echo "<option ".(($type==FilterNarrowBand)?"selected=\"selected\" ":"")."value=\"".FilterNarrowBand."\">".FiltersNarrowBand."</option>";
echo "<option ".(($type==FilterOIII)?"selected=\"selected\" ":"")."value=\"".FilterOIII."\">".FiltersOIII."</option>";
echo "<option ".(($type==FilterHBeta)?"selected=\"selected\" ":"")."value=\"".FilterHBeta."\">".FiltersHBeta."</option>";
echo "<option ".(($type==FilterHAlpha)?"selected=\"selected\" ":"")."value=\"".FilterHAlpha."\">".FiltersHAlpha."</option>";
echo "<option ".(($type==FilterColor)?"selected=\"selected\" ":"")."value=\"".FilterColor."\">".FiltersColor."</option>";
echo "<option ".(($type==FilterNeutral)?"selected=\"selected\" ":"")."value=\"".FilterNeutral."\">".FiltersNeutral."</option>";
echo "<option ".(($type==FilterCorrective)?"selected=\"selected\" ":"")."value=\"".FilterCorrective."\">".FiltersCorrective."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddFilterField3; 
echo "</td>";
echo "<td>";
if(array_key_exists('color',$_GET) && $_GET['color'])
  $color = $_GET['color'];
if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
  $color = $objFilter->getColor($_GET['filterid']);
echo "<select name=\"color\">";
echo "<option value=\"\">&nbsp;</option>";
echo "<option ".(($color==FilterColorLightRed)?" option selected=\"selected\" ":"")."value=\"".FilterColorLightRed."\">".FiltersColorLightRed."</option>";
echo "<option ".(($color==FilterColorRed)?" option selected=\"selected\" ":"")."value=\"".FilterColorRed."\">".FiltersColorRed."</option>";
echo "<option ".(($color==FilterColorDeepRed)?" option selected=\"selected\" ":"")."value=\"".FilterColorDeepRed."\">".FiltersColorDeepRed."</option>";
echo "<option ".(($color==FilterColorOrange)?" option selected=\"selected\" ":"")."value=\"".FilterColorOrange."\">".FiltersColorOrange."</option>";
echo "<option ".(($color==FilterColorLightYellow)?" option selected=\"selected\" ":"")."value=\"".FilterColorLightYellow."\">".FiltersColorLightYellow."</option>";
echo "<option ".(($color==FilterColorDeepYellow)?" option selected=\"selected\" ":"")."value=\"".FilterColorDeepYellow."\">".FiltersColorDeepYellow."</option>";
echo "<option ".(($color==FilterColorYellow)?" option selected=\"selected\" ":"")."value=\"".FilterColorYellow."\">".FiltersColorYellow."</option>";
echo "<option ".(($color==FilterColorYellowGreen)?" option selected=\"selected\" ":"")."value=\"".FilterColorYellowGreen."\">".FiltersColorYellowGreen."</option>";
echo "<option ".(($color==FilterColorLightGreen)?" option selected=\"selected\" ":"")."value=\"".FilterColorLightGreen."\">".FiltersColorLightGreen."</option>";
echo "<option ".(($color==FilterColorGreen)?" option selected=\"selected\" ":"")."value=\"".FilterColorGreen."\">".FiltersColorGreen."</option>";
echo "<option ".(($color==FilterColorMediumBlue)?" option selected=\"selected\" ":"")."value=\"".FilterColorMediumBlue."\">".FiltersColorMediumBlue."</option>";
echo "<option ".(($color==FilterColorPaleBlue)?" option selected=\"selected\" ":"")."value=\"".FilterColorPaleBlue."\">".FiltersColorPaleBlue."</option>";
echo "<option ".(($color==FilterColorBlue)?" option selected=\"selected\" ":"")."value=\"".FilterColorBlue."\">".FiltersColorBlue."</option>";
echo "<option ".(($color==FilterColorDeepBlue)?" option selected=\"selected\" ":"")."value=\"".FilterColorDeepBlue."\">".FiltersColorDeepBlue."</option>";
echo "<option ".(($color==FilterColorDeepViolet)?" option selected=\"selected\" ":"")."value=\"".FilterColorDeepViolet."\">".FiltersColorDeepViolet."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddFilterField4;
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"";
if(array_key_exists('wratten',$_GET) && $_GET['wratten'])
  echo stripslashes($_GET['wratten']);
if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
  echo stripslashes($objFilter->getWratten($_GET['filterid'])); 
echo "\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddFilterField5;
echo "</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"";
if(array_key_exists('schott',$_GET) && $_GET['schott'])
  echo stripslashes($_GET['schott']); 
if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
  echo stripslashes($objFilter->getSchott($_GET['filterid'])); 
echo "\" />";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"add\" value=\"".LangAddFilterButton."\" /></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
