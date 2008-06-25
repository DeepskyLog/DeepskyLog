<?php

// view_locations.php
// shows all locations in database 
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) // LOGGED IN AS ADMINISTRATOR
{

include("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

	include("menu/headmenu.php"); // HEAD MENU

	menu($title); // SUBTITLE

        include("menu/login.php");
  
        include("menu/admin.php"); // ADMINISTRATION MENU

        include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

        include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU

        include("../common/menu/help.php"); // HELP MENU 

	include("menu/out.php"); // LOG OUT MENU 
include("menu/endmenu.php"); // END MENU	

	include("content/overview_eyepieces.php"); // SHOW OVERVIEW EYEPIECES 
include("tail.php"); // HTML END CODE
}

else
{

	header("Location: ../index.php"); // GO BACK TO MAIN PAGE

}

?>
