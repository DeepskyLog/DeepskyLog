<?php
// new_instrument.php
// allows the user to add a new instrument
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
throw new Exception ( LangException002 );
else
	new_instrument ();
function new_instrument() {
	global $baseURL, $loggedUserName, $objInstrument, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewInstrumentsTitle . " " . $loggedUserName . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_instrument\" />";
	echo "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"add\" value=\"" . LangAddInstrumentAdd . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objInstrument->showInstrumentsObserver ();
}
?>
