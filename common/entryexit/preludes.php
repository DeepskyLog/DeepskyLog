<?php
if (!function_exists('fnmatch')) {
  function fnmatch($pattern, $string) {
	  return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}
$entryMessage="";
if(!session_id()) session_start();
require_once "../lib/setup/databaseInfo.php";
require_once "../lib/database.php";
require_once "../lib/setup/language.php";
$objDatabase->newlogin();                                                       // TO BE MOVED TO CONSTRUCTOR OF CLASS WHEN ALL CODE CLEANUP IS FINISHED - DAVID 11 NOV 2008
require_once "../lib/observers.php";
require_once "../common/loginuser.php";
require_once "../lib/setup/vars.php";
require_once "../lib/util.php";
require_once "../lib/atlasses.php";
require_once "../lib/locations.php";
require_once "../lib/instruments.php";
require_once "../lib/filters.php";
require_once "../lib/lenses.php";
require_once "../lib/eyepieces.php";
require_once "../common/layout/tables.php";
require_once "../common/control/ra_to_hms.php";
require_once "../common/control/dec_to_dm.php";

// pagenumbers
if(array_key_exists('min',$_GET))
   $min=$_GET['min'];
elseif(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*25;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*25;
else
  $min = 0;

?>
