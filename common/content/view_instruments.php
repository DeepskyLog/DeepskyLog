<?php
// new_instrument.php
// allows the user to add a new instrument
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
throw new Exception(_("You need to be logged in to change your locations or equipment."));
else
	new_instrument ();
function new_instrument() {
	global $baseURL, $loggedUserName, $objInstrument, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . _("Instruments of") . " " . $loggedUserName . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_instrument\" />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . _("Add instrument") . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objInstrument->showInstrumentsObserver ();
}
?>
