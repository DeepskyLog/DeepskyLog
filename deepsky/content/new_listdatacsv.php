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
include_once "../lib/lists.php";
$list=new Lists;

$util = new Util();
$util->checkUserInput();

$observer = $_SESSION['deepskylog_id'];

echo("<div id=\"main\">\n");
echo("<h2>");
echo (LangCSVListTitle);
echo("</h2>\n");
echo ("<p>");

if($list->checkList($_SESSION['listname'])==2)
{
  echo (LangCSVListMessage1);
  echo ("<br /><br />" . LangCSVListMessage2);
  echo ("<br /><br />" . LangCSVListMessage3);
  echo ("<br /><br />" . LangCSVListMessage7);
  echo ("<br /><br />" . LangCSVListMessage5);
  echo ("<br /><br />" . LangCSVListMessage6."\n");
  echo ("<form action=\"deepsky/control/add_csv_listdata.php\" enctype=\"multipart/form-data\" method=\"post\">");
  echo ("<input type=\"file\" name=\"csv\"><br />"); 
  echo ("<input type=\"submit\" name=\"change\" value=\"".LangCSVListButton."\" /></form>");
}
else
{
}
?>
