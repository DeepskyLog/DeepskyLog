<?php
// change.php
// menu which allows the user to add or change things in the database
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($loggedUser))
	throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
elseif (! ($objUtil->checkAdminOrUserID ( $loggedUser )))
	throw new Exception(_("You need to be logged in to execute these operations."));
else
	menu_change ();
function menu_change() {
	global $baseURL, $loggedUser, $menuAddChange, $objUtil;
	if ($loggedUser) {
		reset ( $_GET );
		echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a class=\"tour1 tour3\" href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . _("Add") . "<b class=\"caret\"></b></a>";
		echo " <ul class=\"dropdown-menu\">";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&newObservationQuickPick=Nieuwe waarneming\">" . _("Observation") . "</a></li>";
		echo "  <li class=\"disabled\">─────────────────</li>";
		echo "  <li><a data-toggle=\"modal\" data-target=\"#addList\">" . _("Observing list") . "</a></li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_session\">" . _("Sessions") . "</a></li>";
		echo "  <li class=\"disabled\">─────────────────</li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_instrument\">" . _("Instruments") . "</a></li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_location\">" . _("Locations") . "</a></li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_eyepiece\">" . _("Eyepieces") . "</a></li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_filter\">" . _("Filters") . "</a></li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_lens\">" . _("Lenses") . "</a></li>";
		echo "  <li class=\"disabled\">─────────────────</li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=add_object\">" . _("Object") . "</a></li>";
		echo " </ul>";
		echo "</li>
			  </ul>";
	}
}
?>
