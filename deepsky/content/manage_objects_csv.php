<?php
// manage_objects_csv.php
// manage objects from csv import, only for admins
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
elseif ($_SESSION ['admin'] != "yes")
	throw new Exception ( LangException001 );
else
	manage_objects_csv ();
function manage_objects_csv() {
	global $baseURL, $objPresentations;
	echo "<div id=\"main\">";
	echo "<h4>" . LangCSVObjectTitle . "</h4>"; 
	echo "<hr />";
	echo LangCSVObjectMessage1 . "<br />";
	echo LangCSVObjectMessage1b . "<br />";
	echo LangCSVObjectMessage2 . "<br /><br />";
	echo LangCSVObjectMessage3 . "<br /><br />";
	echo LangCSVObjectMessage4 . "<br /><br />";
	echo LangCSVObjectMessage5 . "<br />";
	echo "<hr />";
	echo LangCSVObjectMessage6; 
	echo "<form action=\"" . $baseURL . "index.php?indexAction=manage_csv_objects\" enctype=\"multipart/form-data\" method=\"post\">";
	echo "<input type=\"file\" name=\"csv\" /><br />"; 
	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"" . LangCSVObjectButton . "\" /><br />"; 
	echo "</form><br />";
	echo "</div>";
}
?>
