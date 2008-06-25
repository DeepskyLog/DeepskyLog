<?php

// add_site.php
// allows the user to add a new site 
include_once "../lib/setup/databaseInfo.php";
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

include("menu/login.php");

if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") && ($_SESSION['module'] != "deepsky"))
{
   include("menu/admin.php"); // ADMINISTRATION MENU
}

include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU

include("../common/menu/help.php"); // HELP MENU 

if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") && ($_SESSION['module'] == "deepsky"))
{
  include("../deepsky/menu/location.php");
  include("../deepsky/menu/instrument.php");
  include("menu/admin.php"); // ADMINISTRATION MENU
}

include("menu/out.php"); // LOGOUT MENU

include("menu/endmenu.php"); // END MENU

include("content/new_site.php"); // NEW SITE 

include("tail.php"); // HTML END CODE
?>
