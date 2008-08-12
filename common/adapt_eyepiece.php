<?php

// adapt_eyepiece.php
// let the administrator/user change eyepiece details 
// version 3.2 : WDM, 16/01/2008
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";
include_once "../lib/eyepieces.php";
$util = new Util();
$util->checkUserInput();
$eps = new Eyepieces();

session_start();

if(!$_SESSION['module'])
  $_SESSION['module'] = $modules[0];

// ADMINISTRATOR LOGGED IN or USER is the owner of the eyepiece.
if((array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
   ($_SESSION['deepskylog_id'] == $eps->getObserver($_GET['eyepiece']))) 
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
  include("content/change_eyepiece.php"); // ADJUSTABLE EYEPIECE DETAILS 
  include("tail.php"); // HTML END CODE
}
else
  header("Location: ../index.php"); // GO BACK TO MAIN PAGE

?>
