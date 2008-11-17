<?php
// top_objects.php
// generates an overview of all observed objects and their rank 

$step=25;
if(!array_key_exists('TO',$_SESSION))
{ $rank = $objObservation->getPopularObservations();
  $_SESSION['TO'] = $objObject->getSeenObjectDetails($rank);
}
$link = "deepsky/index.php?indexAction=rank_objects";
echo"<div id=\"main\">";
echo"<table width=\"100%\">";
echo"<tr>";
echo"<td>";
echo"<h2>" . LangTopObjectsTitle . "</h2>";
echo"</td>";
echo"<td align=\"right\">";
list($min, $max) = $objUtil->printNewListHeader($_SESSION['TO'], $link, $min, $step, "");
echo"</td>";
echo"</tr>";
echo"</table>";
$objObject->showObjects($link, 'TO', $min, $max, $myList, '', 1);
list($min, $max) = $objUtil->printNewListHeader($_SESSION['TO'], $link, $min, $step, "");
echo "</div></body></html>";

?>
