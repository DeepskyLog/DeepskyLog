<?php
// change.php
// menu which allows the user to add or change things in the database

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
elseif(!($objUtil->checkAdminOrUserID($loggedUser))) throw new Exception(_("You need to be logged in to execute these operations."));
else comets_menu_change();

function comets_menu_change()
{ global $baseURL,$loggedUser;
	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	       <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . _("Add") ."<b class=\"caret\"></b></a>";
	echo " <ul class=\"dropdown-menu\">";
	if($loggedUser!="admin")
	{ echo "  <li><a href=\"".$baseURL."index.php?indexAction=comets_add_observation\">"._("Observation")."</a></li>";
		echo "  <li class=\"disabled\">─────────────────</li>";
	}
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=add_instrument\">"._("Instruments")."</a></li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=add_location\">"._("Locations")."</a></li>";
	echo "  <li class=\"disabled\">─────────────────</li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=comets_add_object\" >"._("Object")."</a></li>";
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>
