<?php

// new_observation.php
// GUI to add a new observation to the database
// Version 0.3: 2005/04/05, JV

session_start(); // start session

include_once "../lib/objects.php";
include_once "../lib/observations.php";
include_once "../common/control/ra_to_hms.php";
include_once "../common/control/dec_to_dm.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$observer = $_SESSION['deepskylog_id'];

echo("<div id=\"main\">\n");
echo("<h2>");

echo (LangCSVTitle);

echo("</h2>\n");

echo ("<p>");
echo (LangCSVMessage1);
echo ("<br /><br />" . LangCSVMessage2);
echo ("<br /><br />" . LangCSVMessage3);
echo ("<br /><br />" . LangCSVMessage4);
echo ("<br /><br />" . LangCSVMessage5);
echo ("<br /><br />" . LangCSVMessage6."\n");
echo ("<form action=\"deepsky/control/add_csv_observations.php\" enctype=\"multipart/form-data\" method=\"post\">");
echo ("<input type=\"file\" name=\"csv\"><br />"); 
echo ("<input type=\"submit\" name=\"change\" value=\"".LangCSVButton."\" /></form>")
?>
