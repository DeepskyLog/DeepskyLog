<?php

// view_filter.php
// view information of a filter 

session_start(); // start session

include_once "lib/filters.php"; // location table
include_once "lib/util.php";
include_once "lib/setup/language.php";

$util = new Util();
$util->checkUserInput();

$filters = new Filters; 

if(!$_GET['filter']) // no instrument defined 
{
   header("Location: index.php");
}

$name = $filters->getFilterName($_GET['filter']);

echo("<div id=\"main\">\n<h2>" . $name . "</h2><table width=\"490\">\n
<tr>\n
<td class=\"fieldname\">\n");

echo LangViewFilterName;

echo("</td>\n<td>\n");
echo($name);
echo("</td></tr>");

echo("<tr><td class=\"fieldname\">");


echo("<tr><td class=\"fieldname\">");

echo LangViewFilterType; 

echo("</td><td>");

$type = $filters->getFilterType($_GET['filter']);

if($type == FilterOther) {echo(FiltersOther);}
if($type == FilterBroadBand) {echo(FiltersBroadBand);}
if($type == FilterNarrowBand) {echo(FiltersNarrowBand);}
if($type == FilterOIII) {echo(FiltersOIII);}
if($type == FilterHBeta) {echo(FiltersHBeta);}
if($type == FilterHAlpha) {echo(FiltersHAlpha);}
if($type == FilterColor) {echo(FiltersColor);}
if($type == FilterNeutral) {echo(FiltersNeutral);}
if($type == FilterCorrective) {echo(FiltersCorrective);}

print("</td></tr>");

$color = $filters->getColor($_GET['filter']);
if ($color > 0)
{
  print("<tr><td class=\"fieldname\">");

  echo LangViewFilterColor;

  echo("</td><td>");

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

  print("</td>
   </tr>");
}

$wratten = $filters->getWratten($_GET['filter']);
if ($wratten != "")
{
  echo("<tr><td class=\"fieldname\">");
  echo LangViewFilterWratten;
  echo("</td><td>");
  echo $wratten;

  print("</td>
         </tr>");
}

$schott = $filters->getSchott($_GET['filter']);
if ($schott != "")
{
  echo("<tr><td class=\"fieldname\">");
  echo LangViewFilterSchott;
  echo("</td><td>");
  echo $schott;

  print("</td>
         </tr>");
}

echo ("</table>");

print("</div></div></body></html>");

?>
