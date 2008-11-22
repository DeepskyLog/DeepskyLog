<?php

require_once "../lib/lists.php";
require_once "../lib/observations.php";
require_once "../lib/locations.php";
require_once "../lib/objects.php";


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
