<?php

// subscribe.php
// allows the user to apply for an deepskylog account
include_once "../lib/setup/databaseInfo.php";

// shows account details

session_start();
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

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

include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU
        
include("../common/menu/help.php"); // HELP MENU 

include("menu/language.php"); // LANGUAGE MENU

include("menu/endmenu.php"); // END MENU	

include("content/register.php"); // REGISTER 

include("tail.php"); // HTML END CODE
?>
