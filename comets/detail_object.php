<?php

// detail_object.php
// show details of one object 

include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$_SESSION['module'] = "comets";

if($_GET['object']) // object defined
{
$_SESSION['object'] = $_GET['object'];

include("../common/head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle . " - " . $_GET['object']);
$head->printMenu();
$head->printMeta("DeepskyLog, " . $_GET['object']);

include("../common/menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

include("../common/menu/login.php"); // LOGIN MENU

if($_SESSION['admin'] == "yes")
{
   include("../common/menu/admin.php"); // ADMINISTRATION MENU
}

include("menu/search.php"); // SEARCH MENU 

if($_SESSION['deepskylog_id']) // LOGGED IN
{
include("menu/change.php"); // CHANGE MENU 
        
include("../common/menu/help.php"); // HELP MENU 

include("../common/menu/out.php"); // LOG OUT MENU 
}
else
{
include("../common/menu/help.php"); // HELP MENU 
include("../common/menu/language.php"); // LANGUAGE MENU
}
include("../common/menu/endmenu.php"); // END MENU

include("content/view_object.php"); // LOCATION DETAILS 

include("../common/tail.php"); // HTML END CODE
}
?>
