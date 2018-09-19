<?php
// search.php
// menu which allows the user to search the observation database
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	menu_search ();
function menu_search() {
	global $baseURL, $loggedUser;
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . _("View") . "<b class=\"caret\"></b></a>";

	echo " <ul class=\"dropdown-menu\">";
	if ($loggedUser) {
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;user=" . urlencode ( $loggedUser ) . "\">" . _("My observations") . "</a></li>";
	}
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_query_observations\" >" . _("Search observations") . "</a></li>";
	echo "  <li class=\"disabled\">─────────────────</li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_view_objects\" >" . _("All objects") . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_query_objects\" >" . _("Search objects") . "</a></li>";
	echo "  <li class=\"disabled\">─────────────────</li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_rank_observers\" >" . _("Observers") . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_rank_objects\" >" . _("Popular objects") . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=statistics\">" . _("Statistics") . "</a></li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>
