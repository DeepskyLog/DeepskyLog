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
	echo "<h4>" . LangCSVListTitle . "</h4>";
	echo "<hr />";
	if ($objList->checkList ( $_SESSION ['listname'] ) == 2) {
		echo LangCSVListMessage1 . "<br />"; 
		echo LangCSVListMessage2 . "<br /><br />"; 
		echo LangCSVListMessage3 . "<br /><br />";
		echo LangCSVListMessage7 . "<br /><br />";
		echo "<strong>" . LangCSVListMessage5 . "</strong><br /><br />";
		echo LangCSVListMessage5a . "<br />";
		echo LangCSVListMessage5b . "<br />";
		echo LangCSVListMessage5c . "<br />";
		echo "<hr />";
		echo LangCSVListMessage6 . ":"  . "<br />";
		echo "<form action=\"" . $baseURL . "index.php?indexAction=add_csv_listdata\" enctype=\"multipart/form-data\" method=\"post\">";
		echo "<input type=\"file\" name=\"csv\" /><br />";
		echo "<input type=\"submit\" class=\"btn btn-success\" name=\"change\" value=\"" . LangCSVListButton . "\" />";
		echo "</form><br />";
	} else
		throw new Exception ( "List is not yours to edit" );
	echo "</div>";
}
?>
