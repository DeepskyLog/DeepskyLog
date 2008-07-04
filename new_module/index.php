<?php

// index.php
// main entrance to deepsky modules of DeepskyLog
// version 0.2, JV 20050203
// version 3.1, DE 20061119


include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

session_start();

$_SESSION['module'] = "NEWMODULE";

include_once "../lib/setup/databaseInfo.php";

include("../common/head.php"); 							 		 					// HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("../common/menu/headmenu.php"); 									// HEAD MENU

menu($title); 																						// SUBTITLE

include("../common/menu/login.php"); 											// LOGIN MENU 

if($_SESSION['admin'] == "yes")
{
   include("../common/menu/admin.php"); 					 				// ADMINISTRATION MENU
}

include("menu/search.php"); 							 								// SEARCH MENU 

if(isset($_COOKIE["deepskylogsec"])) 											// cookie set
{
   include("menu/change.php"); 														// CHANGE MENU
   include("../common/menu/out.php"); 										// LOGOUT MENU
}
else // cookie not set
{
   include("../common/menu/languagemenu.php"); 								// LANGUAGE MENU
}
include("../common/menu/endmenu.php"); 										// END MENU

include("content/overview_observations.php"); 						// WELCOME

include("../common/tail.php"); 														// HTML END CODE
?>
