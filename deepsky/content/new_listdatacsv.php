<?php
// new_listdatacsv.php
// add new list data via csv file
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	new_listdatacsv ();
function new_listdatacsv() {
	global $baseURL, $objList, $objPresentations;
	echo "<div id=\"main\">";
	echo "<h4>" . _("Import objects from a CSV file to your list") . "</h4>";
	echo "<hr />";
	if ($objList->checkList ( $_SESSION ['listname'] ) == 2) {
		echo _("This form gives you the possibility to add different objects at once using a CSV file (comma separated value). The form makes it also possible to easily add objects from another database to your DeepskyLog list.") . "<br />"; 
		echo _("The CSV file has to start with the following definition on the first line, the next lines contain the data:") . "<br /><br />"; 
		echo _("Objectname;free fields;These fields will not be taken into account...") . "<br /><br />";
		echo _("NGC 7000;NA Nebula;...") . "<br /><br />";
		echo "<strong>" . _("Watch out!") . "</strong><br /><br />";
		echo _("The objects in the CSV file should be known already by DeepskyLog. When this is not the case, an error message will appear and no objects will be added at all!") . "<br />";
		echo _("The non existing objects should be added manually until no error messages are shown. When everything goes fine, the added objects will be shown in your list.") . "<br />";
		echo _("Double objects will not be duplicated in the list!") . "<br />";
		echo "<hr />";
		echo _("CSV file ") . ":"  . "<br />";
		echo "<form action=\"" . $baseURL . "index.php?indexAction=add_csv_listdata\" enctype=\"multipart/form-data\" method=\"post\">";
		echo "<input type=\"file\" name=\"csv\" /><br />";
		echo "<input type=\"submit\" class=\"btn btn-success\" name=\"change\" value=\"" . _("Import!") . "\" />";
		echo "</form><br />";
	} else
		throw new Exception ( "List is not yours to edit" );
	echo "</div>";
}
?>
