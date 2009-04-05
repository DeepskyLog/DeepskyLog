<?php
// new_observation.php
// GUI to add a new observation to the database

echo "<div id=\"main\">";
echo "<h2>";
echo LangCSVObjectTitle;
echo "</h2>";
echo "<p>";
echo LangCSVObjectMessage1;
echo "<br /><br />" . LangCSVObjectMessage2;
echo "<br /><br />" . LangCSVObjectMessage3;
echo "<br /><br />" . LangCSVObjectMessage4;
echo "<br /><br />" . LangCSVObjectMessage5;
echo "<br /><br />" . LangCSVObjectMessage6;
echo "<form action=\"".$baseURL."index.php?indexAction=manage_csv_objects\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"file\" name=\"csv\"><br />"; 
echo "<input type=\"submit\" name=\"change\" value=\"".LangCSVObjectButton."\" />";
echo "</form>";
echo "</div>";
?>
