<?php
// new_observationcsv.php
// add a new observation list via csv to the database - entry page

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else new_observationcsv();

function new_observationcsv()
{ global $baseURL,
         $objPresentations;
	echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".LangCSVTitle."</h4>"),"L",array(),30);
	echo "<hr />";
	echo LangCSVMessage1;
	echo "<br /><br />" . LangCSVMessage2;
	echo "<br /><br />" . LangCSVMessage3;
	echo "<br /><br />" . LangCSVMessage4;
	echo "<br /><br />" . LangCSVMessage5;
	echo "<br /><br />" . LangCSVMessage6;
	echo "<form action=\"".$baseURL."index.php?indexAction=add_csv_observations\" enctype=\"multipart/form-data\" method=\"post\"><div>";
	echo "<input type=\"file\" name=\"csv\" /><br />"; 
	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangCSVButton."\" />";
	echo "<br />";
	echo "<br />";
	echo "</div></form>";
	echo "</div>";
}
?>
