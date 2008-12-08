<?php

// detail_observation.php
// show details of one observation 

$_SESSION['module'] = "comets";

if($_GET['observation']) // observation defined
{

$_SESSION['observation'] = $_GET['observation'];

include("../common/head.php"); // HTML head

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

include("content/view_observation.php"); // LOCATION DETAILS 

include("../common/tail.php"); // HTML END CODE
}

?>
