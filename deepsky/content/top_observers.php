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

//$testobservations = $observations->getObservations(); // test array if no observaton yet
$testobservations=1; // time consuming

$step = 25; // number of observers to be shown

echo("<div id=\"main\">\n<h2>" . LangTopObserversTitle . "</h2>");

if(array_key_exists('min',$_GET))
{
   $min = $_GET['min'];
}
else
{
   $min = 0;
}
if(array_key_exists('sort',$_GET) && $_GET['sort'])
{
 $sort=$_GET['sort'];
}
else
{
 $sort='totaal';
}
$catalog="M";
if(array_key_exists('catalogue',$_GET))
{
  $catalog = $_GET['catalogue'];
}
$objectsInCatalog = $objects->getNumberOfObjectsInCatalogue($catalog);

if(sizeof($testobservations) > 0)
{
  $rank = $observations->getPopularObserversOverview($sort, $catalog);
  $link = "deepsky/index.php?indexAction=rank_observers&sort=$sort&size=25&catalogue=$catalog";
  list($min, $max) = $util->printListHeader($rank, $link, $min, $step, "");
  $count = 0;
  echo "<table width=\"100%\">";
  echo "<tr class=\"type3\">
          <td>" . LangTopObserversHeader1 . "</td>
          <td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=observer&catalogue=$catalog\">" . LangTopObserversHeader2 . "</a></td>
          <td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=totaal&catalogue=$catalog\">" . LangTopObserversHeader3 . "</a></td>
          <td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=jaar&catalogue=$catalog\">" . LangTopObserversHeader4 . "</a></td>
          <td>";
	echo("<form name=\"overviewform\">\n ");		
	echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");

  $catalogs = $objects->getCatalogues();
  while(list($key, $value) = each($catalogs))
  {
    if($value==$catalog)
		{
		  echo("<option selected value=\"" . $baseURL . "deepsky/index.php?sort=catalog&indexAction=rank_observers&catalogue=$value\">$value</option>\n");
		}
		else
		{
		  echo("<option value=\"" . $baseURL . "deepsky/index.php?sort=catalog&indexAction=rank_observers&catalogue=$value\">$value</option>\n");
		}
  }
  echo("</select>\n");
	echo("</form>");				
  echo("</a></td>");
  echo "<td><a href=\"deepsky/index.php?indexAction=rank_observers&sort=objecten&catalogue=$catalog\">" . LangTopObserversHeader6 . "</a></td>
        </tr>";
   $numberOfObservations = $observations->getNumberOfObservations();
   $numberOfObservationsThisYear = $observations->getNumberOfObservationsLastYear();
   $numberOfDifferentObjects = $observations->getNumberOfDifferentObjects();
	 
   $outputtable = ""; // output string 

   while(list ($key, $value) = each($rank))
   {
      if($count >= $min && $count < $max)
      {
         if ($count % 2)
         {
            $type = "class=\"type1\"";
         }
         else
         {
            $type = "class=\"type2\"";
         }
 
         $name = $obs->getObserverName($key);
         $firstname = $obs->getFirstName($key);

         $outputtable .= "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"common/detail_observer.php?user=" . $key . "\">$firstname&nbsp;$name</a> </td>";
				 if($sort=="totaal")
				 {
				   $value2 = $value;
				 }
				 else
				 {
           $value2 = $observations->getObservationsCountFromObserver($key);
				 }
         $outputtable .= "<td> $value2 &nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf("%.2f", (($value2 / $numberOfObservations) * 100)). "%)</td>";

         if($sort=="jaar")
				 {
				   $observationsThisYear = $value;
				 }
				 else
				 {
				   $observationsThisYear = $observations->getObservationsLastYear($key);
         }
				 if ($numberOfObservationsThisYear != 0)
         {
            $percentObservations = ($observationsThisYear / $numberOfObservationsThisYear) * 100;   
				 }
         else
         {
            $percentObservations = 0;
         }
         $outputtable .= "<td>". $observationsThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)</td>";
         if($sort=="catalog")
				 {
				   $objectsCount = $value;
				 }
				 else
				 {
				   $objectsCount = $observations->getObservedCountFromCatalogue($key,$catalog);
				 }
				 $outputtable .= "<td> <a href=\"deepsky/index.php?indexAction=view_observer_catalog&catalog=$catalog&user=" . $key . "\">". $objectsCount . "</a> (" . sprintf("%.2f",(($objectsCount / $objectsInCatalog)*100)) . "%)</td>";
   
	       if($sort=="objecten")
				 {
				   $numberOfObjects = $value;
				 }
				 else
				 {
           $numberOfObjects = $observations->getNumberOfObjects($key);
				 }
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
}

echo $outputtable;

list($min, $max) = $util->printListHeader($rank, $link, $min, $step, "");

echo "</div></body></html>";
?>
