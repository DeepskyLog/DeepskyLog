<?php
// manage_objects_csv.php
// manage objects from csv import, only for admins
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception(_("You need to be logged in to change your locations or equipment."));
elseif ($_SESSION ['admin'] != "yes")
	throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
else
	manage_objects_csv ();
function manage_objects_csv() {
	global $baseURL, $objPresentations;
	echo "<div id=\"main\">";
	echo "<h4>" . _("Manage objects from CSV file") . "</h4>"; 
	echo "<hr />";
	echo _("This form gives you the possibility to manage several objects using one single csv file (comma separated value).") . "<br />";
	echo _("This way you can easily and quickly introduce several objects, alternative names, etc.") . "<br />";
	echo _("The csv file must adhere to the following syntax if the instructions concern object naming:") . "<br /><br />";
	echo _("Instruction;Object;Catalog;Catalogindex;") . "<br /><br />";
	echo _("or if the file contains data") . "<br /><br />";
	echo _("Instruction;Object;;Data") . "<br />";
	echo "<hr />";
	echo _("CSV file "); 
	echo "<form action=\"" . $baseURL . "index.php?indexAction=manage_csv_objects\" enctype=\"multipart/form-data\" method=\"post\">";
	echo "<input type=\"file\" name=\"csv\" /><br />"; 
	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"" . _("Import!") . "\" /><br />"; 
	echo "</form><br />";
	echo "</div>";
}
?>
