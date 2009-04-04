<?php // new_filter.php  allows the user to add a new filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
$sort=$objUtil->checkGetKey('sort','name');
$filts=$objFilter->getSortedFilters($sort, $loggedUser);
echo "<div id=\"main\">";
if(count($filts)>0)
{ if(!$min) $min=$objUtil->checkGetKey('min',0);
  $orig_previous=$objUtil->checkGetKey('previous','');
  if((isset($_GET['sort']))&&($orig_previous==$_GET['sort'])) // reverse sort when pushed twice
  { if($_GET['sort']=="name")
      $filts = array_reverse($filts, true);
    else
    { krsort($filts);
      reset($filts);
    }
    $previous = ""; // reset previous field to sort on
  }
  else
    $previous = $sort;
  $link=$baseURL."index.php?indexAction=add_filter&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;
   echo "<h2>".LangOverviewFilterTitle."</h2>";
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
  while(list($key, $value)=each($filts))
  { $filterProperties=$objFilter->getFilterPropertiesFromId($value);
    echo "<tr class=\"type".(2-($count%2))."\">";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_filter&amp;filter=".urlencode($value)."\">".stripslashes($filterProperties['name'])."</a></td>";
    echo "<td>".$objFilter->getEchoType($filterProperties['type'])."</td>";
    echo "<td>".$objFilter->getEchoColor($filterProperties['color'])."</td>";
    echo "<td>".($filterProperties['wratten']?$filterProperties['wratten']:"-")."</td>";
    echo "<td>".($filterProperties['schott']?$filterProperties['schott']:"-")."</td>";
    echo "<td>";
    if(!($obsCnt=$objFilter->getFilterUsedFromId($value)))
      echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode($value) . "\">" . LangRemove . "</a>";
    else
      echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;filter=".$value."&amp;exactinstrumentlocation=true\">".$obsCnt.' '.LangGeneralObservations."</a>";
    echo "</td>";
    echo "</tr>";
    $count++;
	}
  echo "</table>";
  echo "<hr />";
}
echo "<h2>".LangAddFilterTitle."</h2>";
echo "<ol>";
echo "<li value=\"1\">".LangAddFilterExisting;
echo "<form name=\"overviewform\">";		
echo "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$filts=$objFilter->getSortedFilters('name', "");
while(list($key, $value) = each($filts))
  echo("<option value=\"".$baseURL."index.php?indexAction=add_filter&amp;filterid=".urlencode($value)."\">" . $objFilter->getFilterPropertyFromId($value,'name') . "</option>\n");
echo "</select>";
echo "</form>";
echo "</li>";
echo "</ol>";
echo "<p>".LangAddSiteFieldOr."</p>";
echo "<ol><li value=\"2\">".LangAddFilterFieldManually."</li></ol>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddFilterField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('filtername','')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'name'))."\">",
                               LangAddFilterField1Expl);
tableFieldnameFieldExplanation(LangAddFilterField2,$objFilter->getEchoListType($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'type')),LangAddFilterField2); 
tableFieldnameFieldExplanation(LangAddFilterField3,$objFilter->getEchoListColor($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'color')),LangAddFilterField3); 
tableFieldnameFieldExplanation(LangAddFilterField4,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('wratten')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'wratten'))."\" />",
                               LangAddFilterField4);
tableFieldnameFieldExplanation(LangAddFilterField5,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('schott')).stripslashes($objFilter->getFilterPropertyFromId($objUtil->checkGetKey('filterid'),'schott'))."\" />",
                               LangAddFilterField5);
echo "</table>";
echo "<hr />";
echo "<input type=\"submit\" name=\"add\" value=\"".LangAddFilterButton."\" />";
echo "</form>";
echo "</div>";
}
?>
