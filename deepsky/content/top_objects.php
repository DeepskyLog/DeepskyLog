<?php // top_objects.php - generates an overview of all observed objects and their rank 
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
$step=25;
echo"<div id=\"main\">";
echo "<div style=\"position:relative;width:100%;height:40px;\">";
echo "<div style=\"float:left;width:65%;height:20px;padding-top:10px;\">";
echo "<h6>".LangTopObjectsTitle."</h6>";
echo "</div>";
$link=$baseURL."index.php?indexAction=rank_objects";
echo "<div style=\"float:right;width:35%;height:20px;text-align:right;padding-top:10px;\">";
list($min,$max)=$objUtil->printNewListHeader2($_SESSION['Qobj'], $baseURL."index.php?indexAction=rank_objects", $min, $step, "");
echo "</div>";
echo "</div>";
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
$resizeElement='obj_list';
$resizeSize=40;
echo "</div>";
?>
