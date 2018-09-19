<?php
// new_eyepiece.php
// allows the user to add a new eyepiece
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception(_("You need to be logged in to change your locations or equipment."));
else
	new_eyepiece ();
function new_eyepiece() {
	global $baseURL, $loggedUserName, $objEyepiece, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . sprintf(_("Eyepieces of %s"), $loggedUserName) . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_eyepiece\" />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . _("Add eyepiece") . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objEyepiece->showEyepiecesObserver ();
}
?>
