<?php 
// top_observers.php
// generates an overview of all observers and their rank 

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else topobservers();

function topobservers()
{ global $DSOcatalogsLists,$baseURL,$FF,$objObject,$objObservation,$objObserver,$objPresentations,$objUtil,$step;
	$catalog=$objUtil->checkGetKey('catalog','');
	if(!(in_array(stripslashes($catalog),$DSOcatalogsLists)))
	  $catalog="M";
	$rank=$objObservation->getPopularObserversOverviewCatOrList($catalog);
	$link=$baseURL."index.php?indexAction=rank_observers&amp;catalog=".urlencode($catalog);
	echo "<div id=\"main\">";
	echo "<h4>".LangTopObserversTitle."</h4>";
	echo "<hr />";
	$objObserver->showTopObservers($catalog,$rank);
	echo "</div>";
}
?>
