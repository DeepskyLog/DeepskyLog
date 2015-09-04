<?php
// quickpick.php
// allows the user to quiclky enter the name of an object and search it, its observations or make a new observation
global $loggedUser, $inIndex, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	menu_quickpick ();
function menu_quickpick() {
	global $baseURL, $menuSearch, $loggedUser, $loggedUser;
	echo "<li><form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"get\">";
	reset ( $_GET );
	$link = "";
	while ( list ( $key, $value ) = each ( $_GET ) )
		if ($key != "menuSearch")
			$link .= "&amp;" . $key . "=" . urlencode ( $value );
	reset ( $_GET );
	echo "<h4>";
	echo LangSearch . "</h4>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"quickpick\" />";
	echo "<input type=\"hidden\" name=\"titleobjectaction\" value=\"" . LangSearch . "\" />";
	echo "<input type=\"hidden\" name=\"source\"      value=\"quickpick\" />";
	echo "<input type=\"hidden\" name=\"myLanguages\" value=\"true\" />";
	echo "<input type=\"search\" class=\"form-control\" placeholder=\"M 45\" id=\"quickpickobject\" name=\"object\" title=\"" . LangQuickPickHelp . "\" value=\"" . ((array_key_exists ( 'object', $_GET ) && ($_GET ['object'] != '* ')) ? $_GET ['object'] : "") . "\" />";
	echo "<div class=\"form group\"><input class=\"btn btn-default btn-sm\" type=\"submit\" name=\"searchObjectQuickPickQuickPick\" value=\"" . LangQuickPickSearchObject . "\" /></div>";
	echo "<div class=\"form group\"><input class=\"btn btn-default btn-sm\" type=\"submit\" name=\"searchObservationsQuickPick\" value=\"" . LangQuickPickSearchObservations . "\" /></div>";
	if ($loggedUser) {
		echo "<div class=\"form group\"><input class=\"btn btn-default btn-sm\" type=\"submit\" name=\"newObservationQuickPick\" value=\"" . LangQuickPickNewObservation . "\" /></div>";
	}
	echo "</form></li>";
}
?>
