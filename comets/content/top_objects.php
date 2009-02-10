<?php

// top_objects.php
// generates an overview of all observed objects and their rank 

  include_once "lib/cometobjects.php";
  include_once "lib/cometobservations.php";
  include_once "lib/util.php";

  $obs = new CometObjects;
  $observations = new CometObservations;
  $object = new CometObjects;
  $util = new Utils;
 
  $testobservations = $observations->getObservations(); // test if no observations yet

  if(isset($_GET['number']))
  {
     $step = $_GET['number'];
  }
  else
  {
     $step = 25; // default number of objects to be shown
  }

  echo("<div id=\"main\">\n<h2>" . LangTopObjectsTitle . "</h2>");
  
  if(sizeof($testobservations) > 0)
  {
  $rank = $observations->getPopularObservations();

  $link = $baseURL."index.php?indexAction=comets_rank_objects&amp;size=25";

  if (isset($_GET['min']))
  {
    $mini = $_GET['min'];
  }
  else
  {
    $mini = '';
  }
  list($min, $max) = $util->printNewListHeader($rank, $link, $mini, $step, "");

  $count = 0;

  echo "<table>
         <tr class=\"type3\">
          <td>" . LangTopObjectsHeader1 . "</td>
          <td>" . LangTopObjectsHeader2 . "</td>
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

    echo "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($key) . "\">".$object->getName($key)."</a> </td>";

    echo "<td> $value </td>";
   
    echo("</tr>");
   }
   $count++;
  }
  echo "</table>";
  }

echo "</div></body></html>";

?>
