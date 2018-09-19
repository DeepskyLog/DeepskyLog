<?php
// new_filter.php
// allows the user to add a new filter
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception(_("You need to be logged in to change your locations or equipment."));
else
	view_filters ();
function view_filters() {
	global $baseURL, $loggedUserName, $objFilter, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . sprintf(_("Filters of %s"), $loggedUserName) . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_filter\" />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . _("Add filter") . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objFilter->showFiltersObserver ();
}
?>
