<?php

// top_observers.php
// generates an overview of all observers and their rank 

include_once "../lib/observers.php";
include_once "../lib/objects.php";
include_once "../lib/observations.php";
include_once "../lib/util.php";


$obs = new Observers;
$observations = new Observations;
$objects = new Objects;
$util = new Util;
$util->checkUserInput();

$step = 25; // number of observers to be shown
if(array_key_exists('min',$_GET)) $min = $_GET['min']; else $min = 0;
if(array_key_exists('sort',$_GET) && $_GET['sort']) $sort=$_GET['sort']; else $sort='totaal';
$catalog="M";
if(array_key_exists('catalogue',$_GET))
  $catalog = $_GET['catalogue'];
$objectsInCatalog = $objects->getNumberOfObjectsInCatalogue($catalog);
$rank = $observations->getPopularObserversOverviewCatOrList($sort, $catalog);
$link = "deepsky/index.php?indexAction=rank_observers&sort=$sort&size=25&catalogue=" . urlencode($catalog);
$count = 0;

echo '<div id=\"main\">';
echo '<table width=\"100%\">';
echo '<tr width=\"100%\">';
echo '<td>';
echo '<h2>' . LangTopObserversTitle . '</h2>';
echo '</td>';
echo '<td align=\"right\">';
list($min, $max) = $util->printNewListHeader($rank, $link, $min, $step, "");
echo '</td>';
echo '</tr>';
echo '</table>';
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td>" . LangTopObserversHeader1 . "</td>";
echo "<td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=observer&catalogue=" . urlencode($catalog) . "\">" . LangTopObserversHeader2 . "</a></td>";
echo "<td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=totaal&catalogue=" . urlencode($catalog) . "\">" . LangTopObserversHeader3 . "</a></td>";
echo "<td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=jaar&catalogue=" . urlencode($catalog) . "\">" . LangTopObserversHeader4 . "</a></td>";
echo "<td width=\"125px\" align=\"center\">";
  echo("<form name=\"overviewform\">\n ");		
  echo("<select style=\"width:125px\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");
  $catalogs = $objects->getCataloguesAndLists();
  while(list($key, $value) = each($catalogs))
  { if($value==$catalog)
      echo("<option selected value=\"" . $baseURL . "deepsky/index.php?sort=catalog&indexAction=rank_observers&catalogue=$value\">$value</option>\n");
    else
	    echo("<option value=\"" . $baseURL . "deepsky/index.php?sort=catalog&indexAction=rank_observers&catalogue=$value\">$value</option>\n");
  }
  echo("</select>\n");
echo("</form>");			
echo("</a></td>");
echo "<td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=objecten&catalogue=" . urlencode($catalog) . "\">" . LangTopObserversHeader6 . "</a></td>";
echo"</tr>";
 
$numberOfObservations = $observations->getNumberOfDsObservations();
$numberOfObservationsThisYear = $observations->getNumberOfObservationsLastYear();
$numberOfDifferentObjects = $observations->getNumberOfDifferentObjects();
$outputtable = ""; // output string 
while(list ($key, $value) = each($rank))
{ if($count >= $min && $count < $max)
  { if ($count % 2) $type = "class=\"type1\""; else $type = "class=\"type2\"";
    $name = $obs->getObserverName($key);
    $firstname = $obs->getFirstName($key);
    $outputtable .= "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"common/detail_observer.php?user=" . urlencode($key) . "\">$firstname&nbsp;$name</a> </td>";
    if($sort=="totaal") $value2 = $value; else $value2 = $observations->getObservationsCountFromObserver($key);
    $outputtable .= "<td> $value2 &nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf("%.2f", (($value2 / $numberOfObservations) * 100)). "%)</td>";
    if($sort=="jaar") $observationsThisYear = $value; else $observationsThisYear = $observations->getObservationsLastYear($key);
    if ($numberOfObservationsThisYear != 0) $percentObservations = ($observationsThisYear / $numberOfObservationsThisYear) * 100; else $percentObservations = 0;
    $outputtable .= "<td>". $observationsThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)</td>";
    if($sort=="catalog") $objectsCount = $value; else $objectsCount = $observations->getObservedCountFromCatalogueOrList($key,$catalog);
		$outputtable .= "<td align=\"center\"> <a href=\"deepsky/index.php?indexAction=view_observer_catalog&catalog=" . urlencode($catalog) . "&user=" . urlencode($key) . "\">". $objectsCount . "</a> (" . sprintf("%.2f",(($objectsCount / $objectsInCatalog)*100)) . "%)</td>";
    if($sort=="objecten") $numberOfObjects = $value; else $numberOfObjects = $observations->getNumberOfObjects($key);
    $outputtable .= "<td>". $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100))."%)</td>";
    $outputtable .= "</tr>";
  }
  $count++;
}

$outputtable .= "<tr class=\"type3\"><td>".LangTopObservers1."</td><td></td>".
                "<td>$numberOfObservations</td>" .
	              "<td>$numberOfObservationsThisYear</td>" .
 							  "<td>" . $objectsInCatalog . "</td>" .
							  "<td>$numberOfDifferentObjects</td></tr>";
$outputtable .= "</table>";

echo $outputtable;
list($min, $max) = $util->printListHeader($rank, $link, $min, $step, "");
echo "</div></body></html>";
?>
