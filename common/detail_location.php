<?php

// detail_location.php
// show details of one location 
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/locations.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

$locations = new locations();

if(array_key_exists('location', $_GET) && $_GET['location']) // location defined
{

$_SESSION['location'] = $_GET['location'];

include("head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle . " - " . $locations->getLocationName($_GET['location']));
$head->printMenu();
$head->printMeta("DeepskyLog, " . $locations->getLocationName($_GET['location']));

include("menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

include("menu/login.php");

if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
{
   include("menu/admin.php"); // ADMINISTRATION MENU
}

include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU


if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id']) // LOGGED IN
{
include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU

include("../common/menu/help.php"); // HELP MENU 

include("menu/out.php"); // LOG OUT MENU 
}
else
{
include("../common/menu/help.php"); // HELP MENU 
include("menu/languagemenu.php"); // LANGUAGE MENU
}
include("menu/endmenu.php"); // END MENU

include("content/view_location.php"); // LOCATION DETAILS 

include("tail.php"); // HTML END CODE
}

else
{
        header("Location: index.php"); // GO BACK TO MAIN PAGE
}

?>
