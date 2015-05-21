<?php
// new_eyepiece.php
// allows the user to add a new eyepiece
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	new_eyepiece ();
function new_eyepiece() {
	global $baseURL, $loggedUserName, $objEyepiece, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewEyepieceTitle . " " . $loggedUserName . "</h4>";
	echo "<hr />";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_eyepiece\" />";
	echo "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"add\" value=\"" . LangAddEyepieceButton . "\" />&nbsp;";
	echo "</div>";
	echo "</form>";
	$objEyepiece->showEyepiecesObserver ();
}
?>