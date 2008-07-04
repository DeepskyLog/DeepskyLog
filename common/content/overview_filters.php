<?php

// overview_filters.php
// generates an overview of all filters (admin only)
// version 3.2: WDM 22/01/2008

include_once "../lib/filters.php";
include_once "../lib/util.php";
include_once "../lib/observations.php";
include_once "../lib/observers.php";
include_once "../lib/cometobservations.php";

$filters = new Filters;
$util = new util;
$util->checkUserInput();
$observations = new observations;
$cometobservations = new CometObservations;

$observers = new observers;

// sort

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
  $sort = "name"; // standard sort on filter name
}

$filts = $filters->getSortedFilters($sort);

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $filts = array_reverse($filts, true);
  }
  else
  {
    krsort($filts);
    reset($filts);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewFilterTitle."</h2>");

// the code below is very strange but works
if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

$link = "common/view_filters.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

list($min, $max) = $util->printListHeader($filts, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"common/view_filters.php?sort=name&amp;previous=$previous\">".LangViewFilterName."</a></td>
      <td><a href=\"common/view_filters.php?sort=type&amp;previous=$previous\">".LangViewFilterType."</a></td>
      <td><a href=\"common/view_filters.php?sort=color&amp;previous=$previous\">".LangViewFilterColor."</a></td>
      <td><a href=\"common/view_filters.php?sort=wratten&amp;previous=$previous\">".LangViewFilterWratten."</a></td>
      <td><a href=\"common/view_filters.php?sort=schott&amp;previous=$previous\">".LangViewFilterSchott."</a></td>
      <td><a href=\"common/view_filters.php?sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";


echo "<td></td>";
echo "</tr>";

$count = 0;

while(list ($key, $value) = each($filts))
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

   $name = stripslashes($filters->getFilterName($value));
   $type = $filters->getType($value);
   $color = $filters->getColor($value);
   $wratten = $filters->getWratten($value);
   $schott = $filters->getSchott($value);
   $observer = $filters->getObserver($value);

   if ($value != "1")
   {
     print("<tr $type>
             <td><a href=\"common/adapt_filter.php?filter=$value\">$name</a></td>\n
             <td>");
     if($type == FilterOther) {echo(FiltersOther);}
     if($type == FilterBroadBand) {echo(FiltersBroadBand);}
     if($type == FilterNarrowBand) {echo(FiltersNarrowBand);}
     if($type == FilterOIII) {echo(FiltersOIII);}
     if($type == FilterHBeta) {echo(FiltersHBeta);}
     if($type == FilterHAlpha) {echo(FiltersHAlpha);}
     if($type == FilterColor) {echo(FiltersColor);}
     if($type == FilterNeutral) {echo(FiltersNeutral);}
     if($type == FilterCorrective) {echo(FiltersCorrective);}
 
     print("</td>\n
             <td>");
     if ($color == 0)
     {
       echo ("-");
     }
     else
     {
       if($color == FilterColorLightRed) {echo(FiltersColorLightRed);}
       if($color == FilterColorRed) {echo(FiltersColorRed);}
       if($color == FilterColorDeepRed) {echo(FiltersColorDeepRed);}
       if($color == FilterColorOrange) {echo(FiltersColorOrange);}
       if($color == FilterColorLightYellow) {echo(FiltersColorLightYellow);}
       if($color == FilterColorDeepYellow) {echo(FiltersColorDeepYellow);}
       if($color == FilterColorYellow) {echo(FiltersColorYellow);}
       if($color == FilterColorYellowGreen) {echo(FiltersColorYellowGreen);}
       if($color == FilterColorLightGreen) {echo(FiltersColorLightGreen);}
       if($color == FilterColorGreen) {echo(FiltersColorGreen);}
       if($color == FilterColorMediumBlue) {echo(FiltersColorMediumBlue);}
       if($color == FilterColorPaleBlue) {echo(FiltersColorPaleBlue);}
       if($color == FilterColorBlue) {echo(FiltersColorBlue);}
       if($color == FilterColorDeepBlue) {echo(FiltersColorDeepBlue);}
       if($color == FilterColorDeepViolet) {echo(FiltersColorDeepViolet);}
     }
     print("</td>\n
             <td>");
     if ($wratten == "")
     {
       echo "-";
     }
     else
     {
       echo $wratten;
     }
     print("</td>\n
             <td>");
     if ($schott == "")
     {
       echo "-";
     }
     else
     {
       echo $schott;
     }
     print ("</td>\n");
             echo("<td>");
		 print ($observer);
     print ("</td>\n");

             echo("<td>");
     // check if there are no observations made with this filter

     $queries = array("filter" => $value);
     $obs = $observations->getObservationFromQuery($queries, "", "1", "False");

// No filters yet for comet observations!!
//           $queries = array("eyepiece" => $value);
//           $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

     if(!sizeof($obs) > 0) // no observations with filter yet
     {
       echo("<a href=\"common/control/validate_delete_filter.php?filterid=" . $value . "\">" . LangRemove . "</a>");
     }

     echo("</td>\n</tr>");
   }
 }
 $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($filts, $link, $min, $step, "");

  echo "</div></div></body></html>";
?>
