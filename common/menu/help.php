<?php
// help.php
// displays the help menu (only in Dutch)
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	help ();
function help() {
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Help<b class=\"caret\"></b></a>";
	echo " <ul class=\"dropdown-menu\">";
	if ($_SESSION ['lang'] == "nl") {
		echo "  <li><a href=\"http://redmine.deepskylog.org/projects/deepskylog/wiki/DeepskylogManualNL39\" rel=\"external\">Handleiding</a></li>";
	}
	echo "  <li>" . LangMailtoLink . "</li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>
