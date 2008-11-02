<?php

// adapt_instrument.php
// let the administrator/user change an observation
// version 0.1: JV 20041125
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";
include_once "../lib/instruments.php";
$util = new Util();
$util->checkUserInput();
$ins = new Instruments();

session_start();

if(!$_SESSION['module'])
  $_SESSION['module'] = $modules[0];
	
// ADMINISTRATOR LOGGED IN or USER is the owner of the filter.
if((array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
   ($_SESSION['deepskylog_id'] == $ins->getObserverFromInstrument($_GET['instrument']))) 
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
  if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))    
	  include("menu/admin.php"); // ADMINISTRATION MENU
  include("menu/out.php"); // LOG OUT MENU 
  include("menu/endmenu.php"); // END MENU
  include("content/change_instrument.php"); // ADJUSTABLE FILTER DETAILS 
  include("tail.php"); // HTML END CODE
}
else
  header("Location: index.php"); // GO BACK TO MAIN PAGE

?>

