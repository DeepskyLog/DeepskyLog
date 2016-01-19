<?php
// locations.php
// allows the user to add a new site
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	locations ();
function locations() {
	global $baseURL, $loggedUser, $loggedUserName, $sites, $objLocation, $objObserver, $objPresentations, $objUtil;
	$sort = $objUtil->checkRequestKey ( 'sort', 'name' );
	$sites = $objLocation->getSortedLocations ( $sort, $loggedUser );
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewSiteTitle . " " . $loggedUserName . "</h4>";
	echo "<hr />";
 	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
 	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_location\" />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right tour4\" name=\"add\" value=\"" . LangAddSiteButton . "\" />&nbsp;";
	echo "</form>";
	$objLocation->showLocationsObserver ();
	echo "<br /><br />";

	echo "</div></form>";
	echo "</div>";
}
?>
