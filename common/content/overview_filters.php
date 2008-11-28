<?php
// overview_filters.php
// generates an overview of all filters (admin only)

if(isset($_GET['sort']))
  $sort = $_GET['sort']; // field to sort on
else
  $sort = "name"; // standard sort on filter name
$filts = $objFilter->getSortedFilters($sort);
if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
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
$step = 25;
echo "<div id=\"main\">";
echo "<h2>".LangOverviewFilterTitle."</h2>");
// the code below is very strange but works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$link=$baseURL."indexAction=view_filters?sort=".$sort."&amp;previous=".$orig_previous;
if(!$min) $min=$objUtil->checkGetKey('min',0);
list($min,$max)=$util->printListHeader($filts,$link,$min,$step,"");
echo "<table>";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=name&amp;previous=$previous\">".LangViewFilterName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=type&amp;previous=$previous\">".LangViewFilterType."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=color&amp;previous=$previous\">".LangViewFilterColor."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=wratten&amp;previous=$previous\">".LangViewFilterWratten."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=schott&amp;previous=$previous\">".LangViewFilterSchott."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
while(list($key,$value)=each($filts))
{ if($count >= $min && $count < $max) // selection
  { $name = stripslashes($objFilter->getName($value));
    $type = $objFilter->getType($value);
    $color = $objFilter->getColor($value);
    $wratten = $objFilter->getWratten($value);
    $schott = $objFilter->getSchott($value);
    $observer = $objFilter->getObserver($value);
    if ($value != "1")
    { echo "<tr class=\"type".(2-($count%2))."\">";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_filter&amp;filter=".urlencode($value)."\">$name</a></td>";
      echo "<td>";
      if($type == FilterOther) {echo(FiltersOther);}
      if($type == FilterBroadBand) {echo(FiltersBroadBand);}
      if($type == FilterNarrowBand) {echo(FiltersNarrowBand);}
      if($type == FilterOIII) {echo(FiltersOIII);}
      if($type == FilterHBeta) {echo(FiltersHBeta);}
      if($type == FilterHAlpha) {echo(FiltersHAlpha);}
      if($type == FilterColor) {echo(FiltersColor);}
      if($type == FilterNeutral) {echo(FiltersNeutral);}
      if($type == FilterCorrective) {echo(FiltersCorrective);}
      echo "</td>";
      echo "<td>";
      if ($color == 0)
        echo ("-");
      else
      { if($color == FilterColorLightRed) {echo(FiltersColorLightRed);}
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
      echo "</td>";
      echo "<td>";
      echo ($wratten)?$wratten:"-";
      echo "</td>";
      echo "<td>";
      echo ($schott)$schott:"-";
      echo "</td>";
      echo "<td>";
		  echo $observer;
      echo "</td>";
      echo("<td>";
     $queries=array("filter"=>$value);                                          // check if there are no observations made with this filter
     $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");
     // No filters yet for comet observations!!
     //           $queries = array("eyepiece" => $value);
     //           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
     if(!sizeof($obs) > 0) // no observations with filter yet
       echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode($value) . "\">" . LangRemove . "</a>");
     echo "</td>";
     echo "</tr>";
   }
 }
 $count++;
}
echo "</table>";
list($min, $max)=$util->printListHeader($filts,$link,$min,$step,"");
echo "</div>";
?>
