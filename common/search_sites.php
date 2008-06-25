<?php

// search_sites.php
// allows the user to search the database for locations 
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!array_key_exists('module', $_SESSION))
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id']) // LOGGED IN
{

include("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

	include("menu/headmenu.php"); // HEAD MENU

	menu($title); // SUBTITLE

        include("menu/login.php");

        if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
        {
           include("menu/admin.php"); // ADMINISTRATION MENU
        }

        include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

        include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU

        include("../common/menu/help.php"); // HELP MENU 

	include("menu/out.php"); // LOG OUT MENU 

        include("menu/endmenu.php"); // END MENU	

	include("content/search_locations.php"); // SEARCH LOCATIONS 

        include("tail.php"); // HTML END CODE
}

else
{

	header("Location: index.php"); // GO BACK TO MAIN PAGE

}

?>
