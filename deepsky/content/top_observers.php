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
	// Cached for an hour, as the counts in showTopObservers()
	$rank=SimpleCache::remember('rank_observers:rank',3600,function() use ($objObservation) {
	  return $objObservation->getPopularObserversOverviewCatOrListAllInfo();
	});
	$link=$baseURL."index.php?indexAction=rank_observers&amp;catalog=".urlencode($catalog);
	echo "<div id=\"main\">";
	echo "<h4>"._("Most active observers")."</h4>";
	echo "<hr />";
	$objObserver->showTopObservers($catalog,$rank);
	echo "</div>";
}
?>
