<?php

// overview_lenses.php
// generates an overview of all lenses (admin only)
// version 3.2: WDM 11/05/2008

//include_once "../lib/observers.php";
//$observers = new observers;


include_once "../lib/lenses.php";
include_once "../lib/util.php";
include_once "../lib/observations.php";
include_once "../lib/cometobservations.php";

$lenses = new Lenses;
$util = new util;
$util->checkUserInput();
$observations = new observations;
$cometobservations = new CometObservations;


// sort

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
  $sort = "name"; // standard sort on filter name
}

$lns = $lenses->getSortedLenses($sort);

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $lns = array_reverse($lns, true);
  }
  else
  {
    krsort($lns);
    reset($lns);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewLensTitle."</h2>");

// the code below is very strange but works
if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

$link = "common/view_lenses.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

list($min, $max) = $util->printListHeader($lns, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"common/view_lenses.php?sort=name&amp;previous=$previous\">".LangViewLensName."</a></td>
      <td><a href=\"common/view_lenses.php?sort=type&amp;previous=$previous\">".LangViewLensFactor."</a></td>
      <td><a href=\"common/view_lenses.php?sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";



echo "<td></td>";
echo "</tr>";

$count = 0;

while(list ($key, $value) = each($lns))
{
  if($count >= $min && $count < $max) // selection
  {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $name = stripslashes($lenses->getLensName($value));
   $factor = $lenses->getFactor($value);
   $observer = $lenses->getObserverFromLens($value);

   if ($value != "1")
   {
     print("<tr $type>
             <td><a href=\"common/adapt_lens.php?lens=$value\">$name</a></td>\n
             <td>");
		 echo ($factor);
 
     print("</td>\n
             <td>");
		 print ($observer);
     print ("</td>\n");

             echo("<td>");

     // check if there are no observations made with this lens

     $queries = array("lens" => $value);
     $obs = $observations->getObservationFromQuery($queries, "", "1", "False");

// No lenses yet for comet observations!!
//           $queries = array("eyepiece" => $value);
//           $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

     if(!sizeof($obs) > 0) // no observations with lens yet
     {
       echo("<a href=\"common/control/validate_delete_lens.php?lensid=" . $value . "\">" . LangRemove . "</a>");
     }

     echo("</td>\n</tr>");
   }
 }
 $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($lns, $link, $min, $step, "");

  echo "</div></div></body></html>";
?>
