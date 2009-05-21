<?php // top_observers.php - generates an overview of all observers and their rank 
$step = 25;
		$sort=$objUtil->checkGetKey('sort','totaal');
		$catalog=$objUtil->checkGetKey('catalog','M');
		$catalogs=$objObject->getCatalogsAndLists();
		if(!(in_array(stripslashes($catalog),$catalogs)))
		  $catalog="M";
    $rank=$objObservation->getPopularObserversOverviewCatOrList($sort, $catalog);
		  $link=$baseURL."index.php?indexAction=rank_observers&amp;sort=".$sort."&amp;size=25&amp;catalog=".urlencode($catalog);
echo "<div id=\"main\">";
list($min, $max,$content)=$objUtil->printNewListHeader3($rank, $link, $min, $step, "");
$objPresentations->line(array("<h5>".LangTopObserversTitle."</h5>",$content),"LR",array(50,50),50);
echo "<hr />";
$objObserver->showTopObservers($catalog,$rank,$sort,$min,$max);
echo "</div>";
?>
