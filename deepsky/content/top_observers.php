<?php // top_observers.php - generates an overview of all observers and their rank 
if((array_key_exists('steps',$_SESSION))&&(array_key_exists("topObservers",$_SESSION['steps'])))
  $step=$_SESSION['steps']["topObservers"];
if(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*$step;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*$step;
elseif(array_key_exists('min',$_GET))
  $min=$_GET['min'];
else
  $min = 0;
$sort=$objUtil->checkGetKey('sort','totaal');
$catalog=$objUtil->checkGetKey('catalog','M');
$catalogs=$objObject->getCatalogsAndLists();
if(!(in_array(stripslashes($catalog),$catalogs)))
  $catalog="M";
$rank=$objObservation->getPopularObserversOverviewCatOrList($sort, $catalog);
$link=$baseURL."index.php?indexAction=rank_observers&amp;sort=".$sort."&amp;size=25&amp;catalog=".urlencode($catalog);
echo "<div id=\"main\">";
list($min, $max,$content)=$objUtil->printNewListHeader3($rank, $link, $min, $step, "");
$objPresentations->line(array("<h4>".LangTopObserversTitle."</h4>",$content),"LR",array(50,50),30);
$content1="";
$content2=$objUtil->printStepsPerPage3($link,"topObservers",$step);
$objPresentations->line(array($content1,$content2),"LR",array(70,30),25);
echo "<hr />";
$objObserver->showTopObservers($catalog,$rank,$sort,$min,$max);
echo "</div>";
?>
