<?php

// adapt_site.php
// let the administrator change location details 
// version 0.1: JV 20041126
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";
include_once "../lib/locations.php";

$util = new Util();
$util->checkUserInput();
$loc = new Locations();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || $_SESSION['deepskylog_id'] == $loc->getObserver($_GET['location'])) // ADMINISTRATOR LOGGED IN or USER is the owner of the location.
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

include("content/change_site.php"); // ADJUSTABLE LOCATION DETAILS 

include("tail.php"); // HTML END CODE
}

else
{
        header("Location: ../index.php"); // GO BACK TO MAIN PAGE
}

?>
