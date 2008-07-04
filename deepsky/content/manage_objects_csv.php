<?php

// new_observation.php
// GUI to add a new observation to the database
// Version 0.3: 2005/04/05, JV

// Code cleanup - removed by David on 20080704
//include_once "../common/control/ra_to_hms.php";
//include_once "../common/control/dec_to_dm.php";
//include_once "../lib/objects.php";
//include_once "../lib/observations.php";
//$observer = $_SESSION['deepskylog_id'];

session_start(); // start session

include_once "../lib/util.php";
$util = new Util();
$util->checkUserInput();


echo("<div id=\"main\">\n");
echo("<h2>");
echo (LangCSVObjectTitle);
echo("</h2>\n");

echo ("<p>");
echo (LangCSVObjectMessage1);
echo ("<br /><br />" . LangCSVObjectMessage2);
echo ("<br /><br />" . LangCSVObjectMessage3);
echo ("<br /><br />" . LangCSVObjectMessage4);
echo ("<br /><br />" . LangCSVObjectMessage5);
echo ("<br /><br />" . LangCSVObjectMessage6."\n");
echo ("<form action=\"deepsky/control/manage_csv_objects.php\" enctype=\"multipart/form-data\" method=\"post\">");
echo ("<input type=\"file\" name=\"csv\"><br />"); 
echo ("<input type=\"submit\" name=\"change\" value=\"".LangCSVObjectButton."\" /></form>")
?>
