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
list($min, $max)=$objUtil->printNewListHeader($filts, $link, $min, $step, "");
echo "<table width=\"100%\">";
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
    echo "<td>".$objFilter->getEchoType($type)."</td>";
    echo "<td>".$objFilter->getEchoColor($color)."</td>";
    echo "<td>".($wratten?$wratten:"-")."</td>";
    echo "<td>".($schott?$schott:"-")."</td>";
    echo "<td>";
    $queries=array("filter"=>$value,"observer"=>$_SESSION['deepskylog_id']);  // check if there are no observations made with this filter
    $obs=$objObservation->getObservationFromQuery($queries,"D","1");
    //           $queries = array("eyepiece" => $value);                      // No filters yet for comet observations!!
    //           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
    if(!sizeof($obs)>0) // no observations with filter yet
      echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode($value) . "\">" . LangRemove . "</a>";
    echo "</td>";
    echo "</tr>";
    $count++;
	}
}
echo "</table>";
list($min,$max)=$objUtil->printNewListHeader($filts,$link,$min,$step,"");
echo "</div>";
echo "<hr />";
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
tableFieldnameFieldExplanation(LangAddFilterField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('filtername','')).stripslashes($objFilter->getFilterName($objUtil->checkGetKey('filterid')))."\">",
                               LangAddFilterField1Expl);
tableFieldnameFieldExplanation(LangAddFilterField2,$objFilter->getEchoListType($objFilter->getFilterType($objUtil->checkGetKey('filterid'))),LangAddFilterField2); 
tableFieldnameFieldExplanation(LangAddFilterField3,$objFilter->getEchoListColor($objFilter->getColor($objUtil->checkGetKey('filterid'))),LangAddFilterField3); 
tableFieldnameFieldExplanation(LangAddFilterField4,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('wratten')).stripslashes($objFilter->getWratten($objUtil->checkGetKey('filterid')))."\" />",
                               LangAddFilterField4);
tableFieldnameFieldExplanation(LangAddFilterField5,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('schott')).stripslashes($objFilter->getSchott($objUtil->checkGetKey('filterid')))."\" />",
                               LangAddFilterField5);
echo "</table>";
echo "<hr />";
echo "<input type=\"submit\" name=\"add\" value=\"".LangAddFilterButton."\" />";
echo "</form>";
echo "</div>";
?>
