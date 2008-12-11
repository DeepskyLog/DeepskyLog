<?php
if (!function_exists('fnmatch')) {
  function fnmatch($pattern, $string) {
	  return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}
$entryMessage="";
if(!session_id()) session_start();
require_once "lib/setup/databaseInfo.php";
require_once "lib/database.php";
require_once "lib/setup/language.php";
$objDatabase->newlogin();                                                       // TO BE MOVED TO CONSTRUCTOR OF CLASS WHEN ALL CODE CLEANUP IS FINISHED - DAVID 11 NOV 2008
require_once "lib/observers.php";
require_once "lib/setup/vars.php";
require_once "lib/util.php";
require_once "common/control/loginuser.php";
require_once "lib/atlasses.php";
require_once "lib/locations.php";
require_once "lib/instruments.php";
require_once "lib/filters.php";
require_once "lib/lenses.php";
require_once "lib/contrast.php";
require_once "lib/eyepieces.php";
require_once "lib/observations.php";
require_once "lib/lists.php";
require_once "lib/objects.php";
require_once "common/layout/tables.php";
require_once "common/control/ra_to_hms.php";
require_once "common/control/dec_to_dm.php";
include_once "lib/cometobservations.php";
include_once "lib/cometobjects.php";

// pagenumbers
if(array_key_exists('min',$_GET))
   $min=$_GET['min'];
elseif(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*25;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*25;
else
  $min = 0;
	
//listnames
$myList=False;
$listname='';
if(array_key_exists('listname', $_SESSION))
  $listname=$_SESSION['listname'];
$listname_ss = stripslashes($listname);
if(array_key_exists('listname',$_SESSION) && $objList->checkList($_SESSION['listname'])==2)
  $myList=True;

// LCO for viewing observation lists in list, compact or last-own compact
if(array_key_exists('lco', $_GET) && (($_GET['lco']=="L") ||( $_GET['lco']=="C") || ($_GET['lco']=="O"))) // lco = List, Compact or compactlO;
{ $cookietime = time() + 365 * 24 * 60 * 60;            // 1 year
  $_SESSION['lco']=$_GET['lco'];
	setcookie("lco",$_SESSION['lco'],$cookietime, "/");
}
elseif(array_key_exists('lco', $_COOKIE) && (($_COOKIE['lco']=="L") ||( $_COOKIE['lco']=="C") || ($_COOKIE['lco']=="O"))) // lco = List, Compact or compactlO;
  $_SESSION['lco']=$_COOKIE['lco'];
elseif((!array_key_exists('lco',$_SESSION)) || (!(($_SESSION['lco']=="L") ||( $_SESSION['lco']=="C") || ($_SESSION['lco']=="O"))))
{ $cookietime = time() + 365 * 24 * 60 * 60;            // 1 year
	setcookie("lco","L",$cookietime, "/");
  $_SESSION['lco']="L";
}
?>
