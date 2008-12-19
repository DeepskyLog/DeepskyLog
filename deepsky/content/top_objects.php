<?php
// top_objects.php
// generates an overview of all observed objects and their rank 

$step=25;
echo"<div id=\"main\">";
tablePageTitle(LangTopObjectsTitle, $baseURL."index.php?indexAction=rank_objects", $_SESSION['Qobj'], $min, $max);
$objObject->showObjects($baseURL."index.php?indexAction=rank_objects", $min, $max, '', 2);
list($min,$max)=$objUtil->printNewListHeader($_SESSION['Qobj'], $baseURL."index.php?indexAction=rank_objects", $min, $step, "");
echo "</div>";

?>
