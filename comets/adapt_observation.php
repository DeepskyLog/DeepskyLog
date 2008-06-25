<?php

// adapt_observations.php
// let the user change an observation
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$_SESSION['module'] = "comets";

if($_SESSION['deepskylog_id']) // LOGGED IN
{

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

include("../common/menu/out.php"); // LOG OUT MENU 

include("../common/menu/endmenu.php"); // END MENU

include("content/change_observation.php"); // ADJUSTABLE OBSERVATION DETAILS 

include("../common/tail.php"); // HTML END CODE
}

else
{

        header("Location: index.php"); // GO BACK TO MAIN PAGE

}

?>
