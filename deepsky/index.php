<?php
// index.php
// main entrance to deepsky modules of DeepskyLog
session_start();


if((!array_key_exists('module',$_SESSION)) ||
   (array_key_exists('module',$_SESSION) && ($_SESSION['module'] != "deepsky")))
{
  $_SESSION['module'] = "deepsky";
  $cookietime = time() + 365 * 24 * 60 * 60;     // 1 year
  setcookie("module","deepsky", $cookietime, "/");
}
include_once "../lib/util.php";
$util = new Util();
$util->checkUserInput();
include("../common/head.php"); // HTML head
$head = new head();
$head->printHeader($browsertitle);
$head->printMenu();
$head->printMeta("DeepskyLog");
include("../common/menu/headmenu.php"); 	 	     // HEAD MENU
menu($title); 															 		 // SUBTITLE
include("../common/menu/login.php");             // login menu if not logged in
include("menu/search.php"); 								 		 // SEARCH MENU 
if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
{
   include("menu/change.php"); 							 		 // CHANGE MENU
	 include("menu/location.php");
	 include("menu/instrument.php");
   include("../common/menu/help.php"); 			 		 // HELP MENU
   if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
     include("../common/menu/admin.php"); 		 		 // ADMINISTRATION MENU
   include("../common/menu/out.php"); 			 		 // LOGOUT MENU
}
else 																						 // cookie not set
{
   include("../common/menu/help.php"); 			 		 // HELP MENU
   include("../common/menu/language.php"); 	 		 // LANGUAGE MENU
}
include("../common/menu/endmenu.php"); 			 		 
if(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'adapt_observation') && 
   array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  include("content/change_observation.php"); 
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'add_csv') &&
       array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  include("content/new_observationcsv.php");  
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'import_csv_list') &&
       array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!="") && $_SESSION['listname'])
  include("content/new_listdatacsv.php");  
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'manage_csv_object') &&
       array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
  include("content/manage_objects_csv.php");  
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'add_object') &&
       array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  include("content/new_object.php"); 
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'add_observation') &&
       array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  include("content/new_observation.php"); 
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'detail_object'))
  include("content/view_object.php");  
elseif(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'detail_observation'))
  include("content/view_observation.php"); 
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'rank_observers'))
  include("content/top_observers.php");
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'result_query_objects'))
  include("content/execute_query_objects.php"); 
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'result_selected_observations'))
  include("content/selected_observations2.php");  
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'query_observations'))
  include("content/setup_observations_query.php");  
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'query_objects'))
  include("content/setup_objects_query.php");  
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'rank_objects'))
  include("content/top_objects.php");  
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'view_image'))
  include("content/show_image.php"); 
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'listaction'))
  include("content/tolist.php");  
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'quickpick'))
{
  include_once "../lib/objects.php";
  $objects = new Objects;
  $temp = $objects->getExactObject($_GET['object']);
  if($temp)
  {
	  $_GET['object'] = $temp[0];
    if(array_key_exists('searchObservations', $_GET))
      include("content/selected_observations2.php");  
    elseif(array_key_exists('newObservation', $_GET))
      include("content/new_observation.php");   
    else
      include("content/view_object.php");  
  }
  else
  {
	  $_SID=time();
		$_GET['SID']=$_SID;
	  $_GET['catNumber']=ucwords(trim($_GET['object']));
    include('content/setup_objects_query.php');  	
  //	echo("<div id=\"main\">\n<h2>");
  //  echo LangSelectedObjectsTitle; // page title
  //  echo("</h2>\n");
  //  echo("<p color=\"black\">" . LangExecuteQueryObjectsMessage2);
  //  echo("<p><a href=\"deepsky/index.php?indexAction=query_objects\">");
  //  echo(LangExecuteQueryObjectsMessage2a . "</a>");
  }
}
elseif (array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == 'view_observer_catalog'))
  include("content/details_observer_catalog.php"); 
else
{
  $_GET['catalogue']="*";
  include("content/selected_observations2.php"); 	 // WELCOME
}
include("../common/tail.php"); 									 // HTML END CODE
?>
