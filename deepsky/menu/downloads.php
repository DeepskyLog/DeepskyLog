<?php
// search.php
// menu which allows the user to search the observation database
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	menu_downloads ();
function menu_downloads() {
	global $loggedUser, $menuDownloads, $baseURL;
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangDownloadsMenuTitle . "<b class=\"caret\"></b></span></a>";
	echo " <ul class=\"dropdown-menu\">";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=downloadAstroImageCatalogs\">" . LangSearchMenuItem14 . "</a></li>";
	echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=view_atlaspages\">" . LangSearchMenuItem13 . "</a></li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>