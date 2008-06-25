<?php

// subscribe.php
// allows the user to apply for an deepskylog account
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

// shows account details

session_start();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

include_once("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include_once("menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

include_once("menu/login.php"); // LOGIN MENU 

include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

include_once("menu/language.php"); // LANGUAGE MENU

include_once("menu/endmenu.php"); // END MENU

include_once("content/confirm.php"); // REGISTER CONFIRMATION 

include_once("tail.php"); // HTML END CODE
?>
