<?php

// detail_eyepiece.php
// show details of one eyepiece 
include_once "../lib/setup/databaseInfo.php";
include_once "../lib/util.php";

session_start();

$util = new Util();
$util->checkUserInput();

if(!$_SESSION['module'])
{
   $_SESSION['module'] = $modules[0];
}

if(array_key_exists('eyepiece', $_GET) && ($_GET['eyepiece'])) // instrument defined
{
   include_once("head.php"); // HTML head

   $head = new head();
   $head->printHeader($browsertitle);
   $head->printMenu();
   $head->printMeta("DeepskyLog");

   include_once("menu/headmenu.php"); // HEAD MENU

   menu($title); // SUBTITLE

   include_once("menu/login.php");

   if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
   {
      include_once("menu/admin.php"); // ADMINISTRATION MENU
   }

   include_once("../".$_SESSION['module']."/menu/search.php"); // SEARCH MENU


   if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id']) // LOGGED IN
   {
      include_once("../".$_SESSION['module']."/menu/change.php"); // CHANGE MENU

      include("../common/menu/help.php"); // HELP MENU 

      include_once("menu/out.php"); // LOG OUT MENU 
   }
   else
   {
      include("../common/menu/help.php"); // HELP MENU 
      include_once("menu/languagemenu.php"); // LANGUAGE MENU 
   }

   include_once("menu/endmenu.php"); // END MENU

   include_once("content/view_eyepiece.php"); // LOCATION DETAILS 

   include_once("tail.php"); // HTML END CODE
}
?>
