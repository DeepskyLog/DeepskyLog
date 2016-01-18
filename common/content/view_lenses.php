<?php
// view_lenses.php
// Allows the observer to see a list of his or her lenses.
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	view_lenses ();
function view_lenses() {
	global $baseURL, $loggedUserName, $objLens;

	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewLensTitle . " " . $loggedUserName . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_lens\" />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . LangAddLensButton . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objLens->showLensesObserver ();
}
?>
