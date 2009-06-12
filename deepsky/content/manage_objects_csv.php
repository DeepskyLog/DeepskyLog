<?php
// new_observation.php
// GUI to add a new observation to the database

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
?>
