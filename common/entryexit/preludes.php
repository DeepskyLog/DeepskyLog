<?php
if (!function_exists('fnmatch')) {                                              // definition of the php fnmatch function for Windows environments
  function fnmatch($pattern, $string) {
	  return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}
$entryMessage="";
if(!session_id()) session_start();
require_once "lib/setup/databaseInfo.php";
require_once "lib/database.php";
require_once "lib/util.php";
require_once "lib/setup/language.php";
require_once "lib/observers.php";
require_once "lib/setup/vars.php";
require_once "common/control/loginuser.php";
require_once "lib/setup/"."$language";
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
require_once "common/control/ra_to_hms.php";
require_once "common/control/dec_to_dm.php";
include_once "lib/cometobservations.php";
include_once "lib/cometobjects.php";

if(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox')===false)
  $FF=false;
else
  $FF=true;

?>
