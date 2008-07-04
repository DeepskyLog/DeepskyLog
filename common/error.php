<?php

// error.php
// displays error message 

// Code cleanup - removed by David on 20080704
//include_once "../lib/observers.php";
//$obs = new Observers;


include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

session_start();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}


include("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("menu/headmenu.php"); // HEAD MENU
menu($title); // SUBTITLE
include("menu/login.php"); // LOGIN MENU

if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
{
  include("menu/admin.php"); // ADMINISTRATION MENU
}
include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']))
{   
  include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU
  include("../common/menu/help.php"); // HELP MENU
  include("menu/out.php"); // LOG OUT MENU 
}
else
{
  include("../common/menu/help.php"); // HELP MENU
  include("menu/languagemenu.php"); // LOG OUT MENU
}
include("menu/endmenu.php"); // END MENU

// PRINT ERROR MESSAGE AS CONTENT
echo("<div id=\"main\">\n
      <h2>" . LangErrorTitle . "</h2>\n
      <p>");          
  echo($_SESSION['message']);
  echo("</p>\n");
echo("</div>\n");

include("tail.php"); // HTML END CODE
?>
