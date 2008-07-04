<?php

// overview_instruments.php
// generates an overview of all instruments (admin only)
// version 3.2: WDM 22/01/2008

include_once "../lib/instruments.php";
include_once "../lib/observations.php";
include_once "../lib/cometobservations.php";
include_once "../lib/observers.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$instruments = new Instruments;
$observations = new Observations;
$cometobservations = new CometObservations;
$observers = new Observers;

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
   $sort = "name";
}

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

$telescopes = $instruments->getSortedInstruments($sort);
$insts = $observers->getListOfInstruments();

// the code below is very strange but works
if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

if((isset($_GET['sort'])) && (isset($_GET['previous'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] != "")
  {
    $telescopes = array_reverse($telescopes, true);
  }
  else
  {
    krsort($telescopes);
    reset($telescopes);
  }
  $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewInstrumentsTitle."</h2>");

$link = "common/view_instruments.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($telescopes, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"common/view_instruments.php?sort=name&amp;previous=$previous\">".LangOverviewInstrumentsName."</a></td>
      <td><a href=\"common/view_instruments.php?sort=diameter&amp;previous=$previous\">".LangOverviewInstrumentsDiameter."</a></td>
      <td><a href=\"common/view_instruments.php?sort=fd&amp;previous=$previous\">".LangOverviewInstrumentsFD."</a></td>
      <td><a href=\"common/view_instruments.php?sort=fixedMagnification&amp;previous=$previous\">".LangOverviewInstrumentsFixedMagnification."</a></td>";

echo "<td><a href=\"common/view_instruments.php?sort=type&amp;previous=$previous\">".LangOverviewInstrumentsType."</a></td>
      <td><a href=\"common/view_instruments.php?sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";

echo "</tr>";

$count = 0;

while(list ($key, $value) = each($telescopes))
{
   if($count >= $min && $count < $max) // selection
   {
   if ($count % 2)
   {
    $typefield = "class=\"type1\"";
   }
   else
   {
    $typefield = "class=\"type2\"";
   }

   $name = $instruments->getInstrumentName($value);
   $diameter = round($instruments->getDiameter($value), 0);
   $fd = round($instruments->getFd($value), 1);
   if ($fd == "0")
   {
    $fd = "-";
   }
   $type = $instruments->getType($value);
   $fixedMagnification = $instruments->getFixedMagnification($value);
   if ($fixedMagnification == "0")
   {
    $fixedMagnification = "-";
   }
   $observer = $instruments->getObserver($value);

   print("<tr $typefield>
           <td><a href=\"common/adapt_instrument.php?instrument=$value\">".$name."</a></td>\n
           <td>$diameter</td>\n
           <td>$fd</td>\n
           <td>$fixedMagnification</td>\n
           <td>");

   if($type == InstrumentReflector) {echo(InstrumentsReflector);}
   if($type == InstrumentFinderscope) {echo(InstrumentsFinderscope);}
   if($type == InstrumentRefractor) {echo(InstrumentsRefractor);}
   if($type == InstrumentRest) {echo(InstrumentsOther);}
   if($type == InstrumentBinoculars) {echo(InstrumentsBinoculars);}
   if($type == InstrumentCassegrain) {echo(InstrumentsCassegrain);}
   if($type == InstrumentSchmidtCassegrain) {echo(InstrumentsSchmidtCassegrain);}
   if($type == InstrumentKutter) {echo(InstrumentsKutter);}
   if($type == InstrumentMaksutov) {echo(InstrumentsMaksutov);}

   echo("</td>\n
         <td>$observer</td>
         <td>\n");

   $queries = array("instrument" => $value);
   $obs = $observations->getObservationFromQuery($queries, "", "1", "False");
   $obscom = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

   if(!sizeof($obs) > 0 && !sizeof($obscom) > 0 && !array_search($value, $insts) && $value != "1") // no observations with instrument yet
   {
      echo("<a href=\"common/control/validate_delete_instrument.php?instrumentid=" . $value . "\">" . LangRemove . "</a>");
   }

   echo("</td>\n</tr>");
   }
   $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($telescopes, $link, $min, $step, "");

  echo "</div></div></body></html>";

?>
