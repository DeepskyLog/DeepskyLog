<?php
// new_observation.php
// GUI to add a new observation to the database
// Version 0.3: 2005/04/05, JV

echo "<div id=\"main\">";
echo "<h2>";
echo LangCSVTitle;
echo "</h2>";
echo "<p>";
echo LangCSVMessage1;
echo "<br /><br />" . LangCSVMessage2;
echo "<br /><br />" . LangCSVMessage3;
echo "<br /><br />" . LangCSVMessage4;
echo "<br /><br />" . LangCSVMessage5;
echo "<br /><br />" . LangCSVMessage6;
echo "<form action=\"".$baseURL."index.php?indexAction=add_csv_observations\" enctype=\"multipart/form-data\" method=\"post\">";
echo "<input type=\"file\" name=\"csv\" /><br />"; 
echo "<input type=\"submit\" name=\"change\" value=\"".LangCSVButton."\" />";
echo "</form>";
echo "</div>";
?>
