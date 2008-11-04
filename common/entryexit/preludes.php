<?php

session_start();

require_once "../lib/setup/databaseInfo.php";
require_once "../lib/setup/vars.php";
require_once "../lib/setup/language.php";
require_once "../lib/util.php";
require_once "../lib/atlasses.php";
require_once "../lib/objects.php";
require_once "../lib/observers.php";
require_once "../lib/lists.php";
require_once "../lib/observations.php";
require_once "../lib/instruments.php";
require_once "../lib/locations.php";

//listnames
$listname    = '';
if(array_key_exists('listname', $_SESSION))
  $listname=$_SESSION['listname'];
$listname_ss = stripslashes($listname);

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
