<?php // new_observation.php - GUI to add new observations from xml file to the database
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".LangXMLTitle."</h4>"),"L",array(),30);
echo "<hr />";
$objPresentations->line(array(LangXMLMessage1),"L",array(),30);
$objPresentations->line(array(LangXMLMessage2),"L",array(),30);
$objPresentations->line(array(LangXMLMessage3),"L",array(),30);
echo "<hr />";
echo "<form action=\"".$baseURL."index.php?indexAction=add_xml_observations\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"file\" name=\"xml\" /><br />"; 
echo "<input type=\"submit\" name=\"change\" value=\"".LangXMLButton."\" />";
echo "</form>";
echo "<hr />";
echo "</div>";
?>
