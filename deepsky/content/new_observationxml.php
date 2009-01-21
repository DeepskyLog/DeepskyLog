<?php
// new_observation.php
// GUI to add new observations from xml file to the database

echo "<div id=\"main\">";
echo "<h2>";
echo LangXMLTitle;
echo "</h2>";
echo "<p>";
echo LangXMLMessage1;
echo "<form action=\"".$baseURL."index.php?indexAction=add_xml_observations\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"file\" name=\"xml\"><br />"; 
echo "<input type=\"submit\" name=\"change\" value=\"".LangXMLButton."\" />";
echo "</form>";
?>
