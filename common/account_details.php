<?php

// account_details.php
// version 2.0, JV 20050904
// lets the user change his account details 

include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('deepskylog_id', $_SESSION)) // LOGGED IN
{
   // html head

   include("head.php"); // HTML head
   $head = new head();
   $head->printHeader($browsertitle);
   $head->printMenu();
   $head->printMeta("DeepskyLog");

   // headmenu

   include("menu/headmenu.php"); // HEAD MENU
   menu($title); // SUBTITLE

   // sets language

   include("menu/login.php");

   include_once("../lib/setup/vars.php");

   if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") && ($_SESSION['module'] != "deepsky"))
   {
      include("menu/admin.php"); // ADMINISTRATION MENU
   }

   include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU

   include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU
    
   include("../common/menu/help.php"); // HELP MENU 

   if(array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") && ($_SESSION['module'] == "deepsky"))
   {
	    include("../deepsky/menu/location.php");
	    include("../deepsky/menu/instrument.php");
      include("menu/admin.php"); // ADMINISTRATION MENU
   }
	 
   include("menu/out.php"); // LOG OUT MENU 

   include("menu/endmenu.php"); // END MENU

   include("content/change_account.php"); // USER DETAILS 

   include("tail.php"); // HTML END CODE
  
}
else // NOT LOGGED IN
{
   header("Location: index.php"); // GO BACK TO MAIN PAGE
}

?>
