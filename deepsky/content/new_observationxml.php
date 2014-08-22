<?php 
// new_observationxml.php
// GUI to add new observations from xml file to the database

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else new_observationxml();

function new_observationxml()
{ global $baseURL,
         $objPresentations;
	echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".LangXMLTitle."</h4>"),"L",array(),30);
	echo "<hr />";
	print LangXMLMessage1 . "<br />";
	print LangXMLMessage2 . "<br />";
	print LangXMLMessage3 . "<br />";
	echo "<hr />";
	echo "<form action=\"".$baseURL."index.php?indexAction=add_xml_observations\" enctype=\"multipart/form-data\" method=\"post\"><div>";
	echo "<input type=\"file\" name=\"xml\" /><br />"; 
	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"change\" value=\"".LangXMLButton."\" />";
	echo "</div></form>";
	echo "<hr />";
	echo "</div>";
}?>
