<?php
// new_filter.php
// allows the user to add a new filter
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	view_filters ();
function view_filters() {
	global $baseURL, $loggedUserName, $objFilter, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewFilterTitle . " " . $loggedUserName . "</h4>"; 
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_filter\" />";
	echo "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"add\" value=\"" . LangAddFilterButton . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objFilter->showFiltersObserver ();
}
?>