<?php

// detail_observer.php
// show details of one observer
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('user', $_GET) && $_GET['user']) // user defined
{

$_SESSION['user'] = $_GET['user'];

include("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

include_once("menu/login.php"); // LOG IN MENU

if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
{
   include_once("menu/admin.php"); // ADMINISTRATION MENU
}

include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])
{
   include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU
   include("../common/menu/help.php"); // HELP MENU 
   include_once("menu/out.php"); // LOG OUT MENU 
}
else
{
   include("../common/menu/help.php"); // HELP MENU 
   include_once("menu/languagemennu.php"); // LANGUAGE MENU
}
include_once("menu/endmenu.php"); // END MENU

include_once("content/view_observer.php"); // USER DETAILS 

include_once("tail.php"); // HTML END CODE
}

?>
