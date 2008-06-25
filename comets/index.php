<?php

// index.php
// main entrance to deepsky modules of DeepskyLog
// version 0.2, JV 20050203
// version 3.1, DE 20061124

include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

session_start();
if((!array_key_exists('module',$_SESSION)) ||
   (array_key_exists('module',$_SESSION) && ($_SESSION['module'] != "comets")))
{
  $_SESSION['module'] = "comets";
  $cookietime = time() + 365 * 24 * 60 * 60;     // 1 year
  setcookie("module","comets", $cookietime, "/");
}

include_once "../lib/setup/databaseInfo.php";

include("../common/head.php");                 // HTML head

$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");

include("../common/menu/headmenu.php");       // HEAD MENU

menu($title);                                 // SUBTITLE

include("../common/menu/login.php");          // login menu if not logged in

if($_SESSION['admin'] == "yes")
{
   include("../common/menu/admin.php");       // ADMINISTRATION MENU
}

include("menu/search.php");                   // SEARCH MENU 

if(isset($_COOKIE["deepskylogsec"]))          // cookie set
{
   include("menu/change.php");                // CHANGE MENU
   include("../common/menu/help.php");        // HELP MENU
   include("../common/menu/out.php");         // LOGOUT MENU
}
else                                          // cookie not set
{
   include("../common/menu/help.php");        // HELP MENU
   include("../common/menu/language.php");    // LANGUAGE MENU
}
include("../common/menu/endmenu.php");        // END MENU

include("content/overview_observations.php"); // WELCOME

include("../common/tail.php");                // HTML END CODE
?>
