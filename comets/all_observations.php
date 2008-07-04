<?php

// all_observations.php
// shows all observations in the database 
// version 0.2, JV 20050203

include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$_SESSION['module'] = "comets";

include_once "../lib/setup/databaseInfo.php";

include("../common/head.php"); // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("../common/menu/headmenu.php"); // HEAD MENU

menu($title); // SUBTITLE

// always execute as it contains an include to lib/setup/vars.php

include("../common/menu/login.php"); // LOGIN MENU

if($_SESSION['deepskylog_id'] && $_SESSION['admin'] == "yes") // LOGGED IN AS ADMINISTRATOR
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
else // NOT LOGGED IN
{
        include("../common/menu/help.php"); // HELP MENU
        include("../common/menu/languagemenu.php"); // LANGUAGE MENU
}

include("../common/menu/endmenu.php"); // END MENU

include("content/overview_observations.php"); // SHOW OVERVIEW OBSERVATIONS 

include("../common/tail.php"); // HTML END CODE

?>
