<?php

// change_object.php
// allows administrators to change comet objects 
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

session_start();

$_SESSION['module'] = "comets";

include("../common/head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("../common/menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

include("../common/menu/login.php");

if($_SESSION['admin'] == "yes")
{
   include("../common/menu/admin.php"); // ADMINISTRATION MENU
}

include("menu/search.php"); // SEARCH MENU 

include("menu/change.php"); // CHANGE MENU

include("../common/menu/help.php"); // HELP MENU

include("../common/menu/out.php"); // LOGOUT MENU

include("../common/menu/endmenu.php"); // END MENU

include("content/adapt_object.php"); // CHANGE OBJECT 

include("../common/tail.php"); // HTML END CODE
?>
