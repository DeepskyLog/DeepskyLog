<?php

// query_objects.php
// query the objects table 

$_SESSION['module'] = "comets";

include_once "lib/setup/databaseInfo.php";
include("../common/head.php"); // HTML head
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

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
include("../common/menu/languagemenu.php"); // LANGUAGE MENU
}
include("../common/menu/endmenu.php"); // END MENU

include("content/setup_objects_query.php"); // QUERY SETUP OBJECTS TABLE 

include("../common/tail.php"); // HTML END CODE
?>
