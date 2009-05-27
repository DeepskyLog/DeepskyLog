<?php // top_objects.php - generates an overview of all observed objects and their rank 
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
$step=25;
echo"<div id=\"main\">";
$link=$baseURL."index.php?indexAction=rank_objects";
list($min,$max,$content)=$objUtil->printNewListHeader3($_SESSION['Qobj'], $baseURL."index.php?indexAction=rank_objects", $min, $step, "");
$objPresentations->line(array("<h5>".LangTopObjectsTitle."</h5>",$content),"LR",array(70,30),50);
echo "<hr />";
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
