<?php

// top_objects.php
// generates an overview of all observed objects and their rank 
// $$ ok

include_once "../lib/objects.php";
include_once "../lib/observations.php";
include_once "../lib/util.php";

$obs = new Objects;
$observations = new Observations;
$util = new Util;
$util->checkUserInput();

$testobservations = $observations->getObservations(); // test if no observations yet

if(array_key_exists('number', $_GET) && $_GET['number'])
{
   $step = $_GET['number'];
}
else
{
   $step = 25; // default number of objects to be shown
}

if(array_key_exists('min', $_GET))
{
   $min = $_GET['min'];
}
else
{
   $min = 0; 
}

echo("<div id=\"main\">\n<h2>" . LangTopObjectsTitle . "</h2>");
  
if(sizeof($testobservations) > 0)
{
   $rank = $observations->getPopularObservations();

   $link = "deepsky/index.php?indexAction=rank_objects&size=25";

   list($min, $max) = $util->printListHeader($rank, $link, $min, $step, "");

   $count = 0;

   echo "<table width=\"100%\"
         <tr class=\"type3\">
          <td>" . LangTopObjectsHeader1 . "</td>
          <td>" . LangTopObjectsHeader2 . "</td>
          <td>" . LangTopObjectsHeader3 . "</td>
          <td>" . LangTopObjectsHeader4 . "</td>
          <td>" . LangTopObjectsHeader5 . "</td>
         </tr>";

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

         echo "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"deepsky/index.php?indexAction=detail_object&object=" . $key . "\">$key</a> </td>";
    
		     $type = $obs->getType($key);

         echo "<td>" . $$type . "</td>";

         $con = $obs->getConstellation($key);

         echo "<td>" . $$con . "</td>";
 
         echo "<td> $value </td>";
   
         echo("</tr>");
      }
      $count++;
   }
   echo "</table>";
}

list($min, $max) = $util->printListHeader($rank, $link, $min, $step, "");

echo "</div></body></html>";

?>
