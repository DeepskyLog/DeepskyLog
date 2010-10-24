<?php
// manage_objects_csv.php
// manage objects from csv import, only for admins

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else manage_objects_csv();

function manage_objects_csv()
{ global $baseURL,
         $objPresentations;
	echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".LangCSVObjectTitle."</h4>"),"L");
	echo "<hr />";
	$objPresentations->line(array(LangCSVObjectMessage1),"L");
	$objPresentations->line(array(LangCSVObjectMessage1b),"L");
	$objPresentations->line(array(LangCSVObjectMessage2),"L");
	$objPresentations->line(array(LangCSVObjectMessage3),"L");
	$objPresentations->line(array(LangCSVObjectMessage4),"L");
	$objPresentations->line(array(LangCSVObjectMessage5),"L");
	echo "<hr />";
	$objPresentations->line(array(LangCSVObjectMessage6),"L");
	echo "<form action=\"".$baseURL."index.php?indexAction=manage_csv_objects\" enctype=\"multipart/form-data\" method=\"post\">";
	$objPresentations->line(array("<input type=\"file\" name=\"csv\" />"),"L"); 
	$objPresentations->line(array("<input type=\"submit\" name=\"change\" value=\"".LangCSVObjectButton."\" />"),"L");
	echo "</form>";
	echo "</div>";
}
?>
