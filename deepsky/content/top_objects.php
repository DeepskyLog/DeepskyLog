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
{ echo "<script type=\"text/javascript\">";
  echo "theResizeElement='obj_list';";
  echo "theResizeSize=40;";
  echo "</script>";
}
$objObject->showObjects($link, $min, $max,'',2);
echo "</div>";
?>
