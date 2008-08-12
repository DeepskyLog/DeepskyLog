<?php

// adapt_lens.php
// let the administrator/user change a filter
// version 3.2 : WDM, 21/01/2008
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";
include_once "../lib/lenses.php";
$util = new Util();
$util->checkUserInput();
$lns = new Lenses();

session_start();

if(!$_SESSION['module'])
  $_SESSION['module'] = $modules[0];

// ADMINISTRATOR LOGGED IN or USER is the owner of the lens.
if((array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
   ($_SESSION['deepskylog_id'] == $lns->getObserver($_GET['lens']))) 
{
  include("head.php"); // HTML head
  $head = new head();
  $head->printHeader($browsertitle);
  $head->printMenu();
  $head->printMeta("DeepskyLog");
  include("menu/headmenu.php"); // HEAD MENU
  menu($title); // SUBTITLE
  include("menu/login.php");
  include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU
  include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU
  include("../common/menu/help.php"); // HELP MENU 
  include("menu/out.php"); // LOG OUT MENU 
  include("menu/endmenu.php"); // END MENU
  include("content/change_lens.php"); // ADJUSTABLE FILTER DETAILS 
  include("tail.php"); // HTML END CODE
}
else
  header("Location: index.php"); // GO BACK TO MAIN PAGE

?>