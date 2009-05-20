<?php // top_observers.php - generates an overview of all observers and their rank 

$outputtable = "";   $count=0;   $step = 25;

$sort=$objUtil->checkGetKey('sort','totaal');
$catalog=$objUtil->checkGetKey('catalog','M');
$catalogs=$objObject->getCatalogsAndLists();
if(!(in_array(stripslashes($catalog),$catalogs)))
  $catalog="M";
$objectsInCatalog=$objObject->getNumberOfObjectsInCatalog($catalog);

$rank=$objObservation->getPopularObserversOverviewCatOrList($sort, $catalog);
$link=$baseURL."index.php?indexAction=rank_observers&amp;sort=".$sort."&amp;size=25&amp;catalog=".urlencode($catalog);
echo "<div id=\"main\">";
echo "<table class=\"h2table\">";
echo "<tr>";
echo "<td class=\"h2header\">".LangTopObserversTitle."</td>";
echo "<td align=\"right\">";
list($min, $max) = $objUtil->printNewListHeader($rank, $link, $min, $step, "");
//$max=25;
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td style=\"text-align:center\">".LangTopObserversHeader1."</td>";
echo "<td style=\"text-align:center\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=observer&amp;catalog=".urlencode($catalog)."\">".LangTopObserversHeader2."</a></td>";
echo "<td style=\"text-align:center\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=totaal&amp;catalog="  .urlencode($catalog)."\">".LangTopObserversHeader3."</a></td>";
echo "<td style=\"text-align:center\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=jaar&amp;catalog="    .urlencode($catalog)."\">".LangTopObserversHeader4."</a></td>";
echo "<td style=\"width:125px;\" align=\"center\">";
echo "<select style=\"width:125px;\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\" class=\"inputfield\">";
while(list($key,$value)=each($catalogs))
{ if(!($value))
    $value="-----------";
  if($value==stripslashes($catalog))
    echo("<option selected value=\"".$baseURL."index.php?sort=catalog&amp;indexAction=rank_observers&amp;catalog=".urlencode($value)."\">".$value."</option>\n");
  else
	  echo("<option value=\"".$baseURL."index.php?sort=catalog&amp;indexAction=rank_observers&amp;catalog=".urlencode($value)."\">".$value."</option>\n");
}
echo "</select>";
echo "</td>";
echo "<td style=\"text-align:center\"><a href=\"".$baseURL."index.php?indexAction=rank_observers&amp;sort=objecten&amp;catalog=".urlencode($catalog)."\">".LangTopObserversHeader6."</a></td>";
echo"</tr>";
 
$numberOfObservations = $objObservation->getNumberOfDsObservations();
$numberOfObservationsThisYear = $objObservation->getObservationsLastYear('%');
$numberOfDifferentObjects = $objObservation->getNumberOfDifferentObservedDSObjects();
while(list($key,$value)=each($rank))
{ if(($count>=$min)&&($count<$max))
  { $name = $objObserver->getObserverProperty($key,'name');
    $firstname = $objObserver->getObserverProperty($key,'firstname');
    $outputtable .= "<tr class=\"type".(2-($count%2))."\">";
    $outputtable.="<td style=\"text-align:center\">" . ($count + 1) . "</td><td> <a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=".urlencode($key)."\">$firstname&nbsp;$name</a> </td>";
    if($sort=="totaal") $value2 = $value; else $value2 = $objObservation->getDsObservationsCountFromObserver($key);
    $outputtable .= "<td style=\"text-align:center\"> $value2 &nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf("%.2f", (($value2 / $numberOfObservations) * 100)). "%)</td>";
    if($sort=="jaar") $observationsThisYear = $value; else $observationsThisYear = $objObservation->getObservationsLastYear($key);
    if ($numberOfObservationsThisYear != 0) $percentObservations = ($observationsThisYear / $numberOfObservationsThisYear) * 100; else $percentObservations = 0;
    $outputtable .= "<td style=\"text-align:center\">". $observationsThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)</td>";
    if($sort=="catalog") $objectsCount = $value; else $objectsCount = $objObservation->getObservedCountFromCatalogOrList($key,$catalog);
		$outputtable .= "<td  style=\"text-align:center\"> <a href=\"".$baseURL."index.php?indexAction=view_observer_catalog&amp;catalog=".urlencode($catalog)."&amp;user=".urlencode($key)."\">". $objectsCount . "</a> (" . sprintf("%.2f",(($objectsCount / $objectsInCatalog)*100)) . "%)</td>";
    if($sort=="objecten") $numberOfObjects = $value; else $numberOfObjects = $objObservation->getNumberOfObjects($key);
    $outputtable .= "<td style=\"text-align:center\">". $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100))."%)</td>";
    $outputtable .= "</tr>";
  }
  $count++;
}

$outputtable .= "<tr class=\"type3\" style=\"text-align:center\"><td>".LangTopObservers1."</td><td></td>".
                "<td style=\"text-align:center\">$numberOfObservations</td>" .
	              "<td style=\"text-align:center\">$numberOfObservationsThisYear</td>" .
 							  "<td style=\"text-align:center\">".$objectsInCatalog."</td>" .
							  "<td style=\"text-align:center\">".$numberOfDifferentObjects."</td></tr>";
$outputtable .= "</table>";

echo $outputtable;
list($min, $max) = $objUtil->printNewListHeader($rank, $link, $min, $step, "");
echo "</div>";
?>
