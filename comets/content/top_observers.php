<?php

// top_observers.php
// generates an overview of all observers and their rank 

  include_once "lib/observers.php";
  include_once "lib/cometobservations.php";
  include_once "lib/util.php";

  $obs = new Observers;
  $observations = new CometObservations;
  $util = new Util;
  $util->checkUserInput();

  $testobservations = $observations->getObservations(); // test array if no observaton yet

  $step = 25; // number of observers to be shown

  echo("<div id=\"main\">\n<h2>" . LangTopObserversTitle . "</h2>");

  if(sizeof($testobservations) > 0)
  {
  $rank = $observations->getPopularObservers();

  $link = "comets/rank_observers.php?size=25";

  if(isset($_GET['min']))
  {
    $mini = $_GET['min'];
  }
  else
  {
    $mini = '';
  }
  list($min, $max) = $util->printListHeader($rank, $link, $mini, $step, "");

  $count = 0;


  echo "<table>
         <tr class=\"type3\">
          <td>" . LangTopObserversHeader1 . "</td>
          <td>" . LangTopObserversHeader2 . "</td>
          <td>" . LangTopObserversHeader3 . "</td>
          <td>" . LangTopObserversHeader4 . "</td>
          <td>" . LangTopObserversHeader6 . "</td>
         </tr>";

  $numberOfObservations = $observations->getNumberOfObservations();
  $numberOfObservationsThisYear = $observations->getNumberOfObservationsThisYear();
  $numberOfDifferentObjects = $observations->getNumberOfDifferentObjects();

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

    echo "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=" . urlencode($key) . "\">$firstname&nbsp;$name</a> </td>";

    echo "<td> $value &nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($value / $numberOfObservations) * 100))."%)</td>";

    $observationsThisYear = $observations->getObservationsThisYear($key);
    if ($numberOfObservationsThisYear != 0)
    {
     $percentObservations = ($observationsThisYear / $numberOfObservationsThisYear) * 100;   }
    else
    {
     $percentObservations = 0;
    }
    echo "<td>". $observationsThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)</td>";

    $numberOfObjects = $observations->getNumberOfObjects($key);
    echo "<td>". $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100))."%)</td>";
    echo("</tr>");
   }
   $count++;
  }

  echo "<tr class=\"type3\"><td>".LangTopObservers1."</td><td></td><td>$numberOfObservations</td><td>$numberOfObservationsThisYear</td><td>$numberOfDifferentObjects</td></tr>";

  echo "</table>";
  }

echo "</div></body></html>";
?>
