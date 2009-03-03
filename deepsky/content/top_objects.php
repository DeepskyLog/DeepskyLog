<?php
// top_objects.php
// generates an overview of all observed objects and their rank 

$step=25;
echo"<div id=\"main\">";
tablePageTitle(LangTopObjectsTitle, $baseURL."index.php?indexAction=rank_objects", $_SESSION['Qobj'], $min, $max);
$objObject->showObjects($baseURL."index.php?indexAction=rank_objects", $min, $max, '', 2);

	echo "<hr />";
	$_GET['min']=$min;
	$_GET['max']=$max;
	if($FF)
	  $objObject->showObjects($link, $min, $max,'',2);
  else
	{ $_SESSION['ifrm']="deepsky/content/ifrm_objects.php";
		echo "<iframe name=\"obj_list\" id=\"obj_list\" src=\"".$baseURL."ifrm_holder.php?link=".urlencode($link)."&amp;min=".$min."&amp;max=".$max."&amp;ownShow=&amp;showRank=0\" frameborder=\"0\" width=\"100%\" style=\"heigth:100px\">";
	  $objObject->showObjects($link, $min, $max,'',2);
		echo "</iframe>";
	}	
	echo "<script>resizeElement('obj_list',350);</script>";
	echo "<hr />";


list($min,$max)=$objUtil->printNewListHeader($_SESSION['Qobj'], $baseURL."index.php?indexAction=rank_objects", $min, $step, "");
echo "</div>";

?>
