<?php

include("../common/head.php"); // HTML head
$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");
include("../common/menu/headmenu.php"); 	 	        // HEAD MENU
menu($title); 															 		    // SUBTITLE
include("../common/menu/login.php");                // login menu if not logged in
include("menu/search.php"); 								 		    // SEARCH MENU 
if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
{
   include("menu/change.php"); 							 		     // CHANGE MENU
	 include("menu/location.php");
	 include("menu/instrument.php");
   include("../common/menu/help.php"); 			 		     // HELP MENU
   if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
     include("../common/menu/admin.php"); 		 		   // ADMINISTRATION MENU
   include("../common/menu/out.php"); 			 		     // LOGOUT MENU
}
else 																						     // cookie not set
{
   include("../common/menu/help.php"); 			 		     // HELP MENU
   include("../common/menu/languagemenu.php"); 	 		 // LANGUAGE MENU
}
include("../common/menu/endmenu.php"); 	

?>