<?php
// search.php
// menu which allows the user to search the observation database
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	menu_search ();
function menu_search() {
	global $loggedUser, $menuView, $baseURL;
	$theDate = date ( 'Ymd', strtotime ( '-1 year' ) );
	$lastMinYear = substr ( $theDate, 0, 4 );
	$lastMinMonth = substr ( $theDate, 4, 2 );
	$lastMinDay = substr ( $theDate, 6, 2 );
	$link = "";
	reset ( $_GET );
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangSearchMenuTitle . "<b class=\"caret\"></b></a>";
	echo " <ul class=\"dropdown-menu\">";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;myLanguages=true&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay&amp;newobservations=true\">" . LangSearchMenuItem9 . "</a></li>";
	if (($loggedUser) && ($loggedUser != "admin")) { // admin doesn't have own observations
		echo "<li><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;observer=" . urlencode ( $loggedUser ) . "\">" . LangSearchMenuItem1 . "</a></li>";
		echo "<li><a href=\"" . $baseURL . "index.php?indexAction=result_selected_sessions&amp;observer=" . urlencode ( $loggedUser ) . "\">" . LangSearchMenuItem11 . "</a></li>";
	}
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=result_selected_sessions\">" . LangSearchMenuItem12 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=view_filters\">" . LangViewFilters . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=view_lenses\">" . LangViewLenses . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=rank_observers\">" . LangSearchMenuItem6 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=rank_objects\">" . LangSearchMenuItem7 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=view_catalogs\">" . LangSearchMenuItem10 . "</a></li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>