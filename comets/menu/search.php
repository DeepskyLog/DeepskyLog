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
	       <a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangSearchMenuTitle . "<b class=\"caret\"></b></a>";

	echo " <ul class=\"dropdown-menu\">";
	if ($loggedUser) {
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;user=" . urlencode ( $loggedUser ) . "\">" . LangSearchMenuItem1 . "</a></li>";
	}
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_query_observations\" >" . LangSearchMenuItem3 . "</a></li>";
	echo "  <li class=\"disabled\">─────────────────</li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_view_objects\" >" . LangSearchMenuItem4 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_query_objects\" >" . LangSearchMenuItem5 . "</a></li>";
	echo "  <li class=\"disabled\">─────────────────</li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_rank_observers\" >" . LangSearchMenuItem6 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=comets_rank_objects\" >" . LangSearchMenuItem7 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=statistics\">" . LangStatistics . "</a></li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>
