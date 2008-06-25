<?php

// result_selected_observations.php
// outputs the result of a query through all observations 
// version 0.3: JV 20051201

$_SESSION['module'] = "comets";

include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

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

	include("content/selected_observations2.php"); // SELECTED OBSERVATIONS 

include("../common/tail.php"); // HTML END CODE
?>
