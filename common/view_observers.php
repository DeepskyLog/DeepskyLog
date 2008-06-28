<?php

// view_observers.php
// shows account details and allows user to change them
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) // LOGGED IN AS ADMINISTRATOR
{
  include_once("head.php"); // HTML head
  // $_SESSION['module'] = "comets";
  $head = new head();
  $head->printHeader($browsertitle);
  $head->printMenu();
  $head->printMeta("DeepskyLog");
	include_once("menu/headmenu.php"); // HEAD MENU
	menu($title); // SUBTITLE
  include_once("menu/login.php");
	include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU
	include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU 
  include("../common/menu/help.php"); // HELP MENU 
  include_once("menu/admin.php"); // ADMINISTRATION MENU
	include_once("menu/out.php"); // LOG OUT MENU 
  include_once("menu/endmenu.php"); // END MENU	
	include_once("content/overview_observers.php"); // SHOW OVERVIEW OBSERVERS 
  include_once("tail.php"); // HTML END CODE
}
else
{
	header("Location: ../index.php"); // GO BACK TO MAIN PAGE
}

?>
