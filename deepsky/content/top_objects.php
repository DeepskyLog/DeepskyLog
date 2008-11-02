<?php

// top_objects.php
// generates an overview of all observed objects and their rank 
// $$ ok

include_once "../lib/objects.php";
$obs = new Objects;
include_once "../lib/observations.php";
$observations = new Observations;
include_once "../lib/util.php";
$util = new Util;
$util->checkUserInput();


$myList = False;
if(array_key_exists('listname',$_SESSION) && ($list->checkList($_SESSION['listname'])==2))
  $myList=True;

 // minimum
if(array_key_exists('min',$_GET))
  $min=$_GET['min'];
elseif(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*25;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*25;
else
  $min = 0;
$step=25;
if(!array_key_exists('TO',$_SESSION))
{ $rank = $observations->getPopularObservations();
  $_SESSION['TO'] = $obs->getSeenObjectDetails($rank);
}
$link = "deepsky/index.php?indexAction=rank_objects";
echo"<div id=\"main\">";
echo"<table width=\"100%\">";
echo"<tr>";
echo"<td>";
echo"<h2>" . LangTopObjectsTitle . "</h2>";
echo"</td>";
echo"<td align=\"right\">";
list($min, $max) = $util->printNewListHeader($_SESSION['TO'], $link, $min, $step, "");
echo"</td>";
echo"</tr>";
echo"</table>";

$count = 0;
/*echo "<table width=\"100%\"
       <tr class=\"type3\">
        <td>" . LangTopObjectsHeader1 . "</td>
        <td>" . LangTopObjectsHeader2 . "</td>
        <td>" . LangTopObjectsHeader3 . "</td>
        <td>" . LangTopObjectsHeader4 . "</td>
        <td>" . LangTopObjectsHeader5 . "</td>
       </tr>";
*/
   $obs->showObjects($link, 'TO', $min, $max, $myList, '', 1);

/*
 while(list ($key, $value) = each($rank))
 {
    if($count >= $min && $count < $max)
    {
       if ($count % 2) $type = "class=\"type1\""; else $type = "class=\"type2\"";
       echo "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($key) . "\">$key</a> </td>";
       $type = $obs->getDsObjectType($key);
       echo "<td>" . $$type . "</td>";
       $con = $obs->getConstellation($key);
       echo "<td>" . $$con . "</td>";
       echo "<td> $value </td>";
       echo("</tr>");
    }
    $count++;
 }
*/
/* echo "</table>";
*/
list($min, $max) = $util->printNewListHeader($_SESSION['TO'], $link, $min, $step, "");
echo "</div></body></html>";

?>
