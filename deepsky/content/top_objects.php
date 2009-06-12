<?php // top_objects.php - generates an overview of all observed objects and their rank 
if((array_key_exists('steps',$_SESSION))&&(array_key_exists("topObj",$_SESSION['steps'])))
  $step=$_SESSION['steps']["topObj"];
if(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*$step;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*$step;
elseif(array_key_exists('min',$_GET))
  $min=$_GET['min'];
else
  $min = 0;
echo"<div id=\"main\">";
$link=$baseURL."index.php?indexAction=rank_objects";
list($min,$max,$content)=$objUtil->printNewListHeader3($_SESSION['Qobj'], $baseURL."index.php?indexAction=rank_objects", $min, $step, "");
$objPresentations->line(array("<h4>".LangTopObjectsTitle."</h4>",$content),"LR",array(70,30),30);
$content=$objUtil->printStepsPerPage3($link,"topObj",$step);
$objPresentations->line(array($content),"R",array(100),20);
echo "<hr />";
$_GET['min']=$min;
$_GET['max']=$max;
if($FF)
{ echo "<script type=\"text/javascript\">";
  echo "theResizeElement='obj_list';";
  echo "theResizeSize=40;";
  echo "</script>";
}
$objObject->showObjects($link, $min, $max,'',2,$step);
echo "<hr />";
echo "</div>";
?>
