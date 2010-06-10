<?php
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{ if (!function_exists('fnmatch'))                                              // definition of the php fnmatch function for Windows environments
  { function fnmatch($pattern, $string) 
  	{ return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
	  }
	}
	$entryMessage="";
	$resizeElement="";
	$resizeSize=0;
	$loadAtlasPage=0;
	if(!session_id()) session_start();
	require_once "lib/setup/databaseInfo.php";
	require_once "lib/database.php";
	require_once "lib/util.php";
	require_once "lib/setup/language.php";
	require_once "lib/observers.php";
  require_once "lib/observerqueries.php";
	require_once "lib/setup/vars.php";
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
  require_once "lib/astrocalc.php";
	require_once "lib/stars.php";
	include_once "lib/cometobservations.php";
	include_once "lib/cometobjects.php";
  include_once 'lib/presentation.php';
  include_once 'lib/constellations.php';
  include_once 'lib/formlayouts.php';
  include_once 'lib/reportlayouts.php';
  include_once 'lib/catalogs.php';
  include_once "lib/moonphase.inc.php";
  include_once "lib/astrocalc.php";
  
	if(strpos(($browser=$objUtil->checkArrayKey($_SERVER,'HTTP_USER_AGENT','')),'Firefox')===false)
	  $FF=false;
	else
	  $FF=true;
  if(strpos(($browser=$objUtil->checkArrayKey($_SERVER,'HTTP_USER_AGENT','')),'MSIE')===false)
	  $MSIE=false;
	else
	  $MSIE=true;
	  
	  
  $today=date('Ymd',strtotime('today'));
  $thisYear=date("Y");
  $thisMonth=date("n");
  $thisDay=date("j");
  if (array_key_exists('globalMonth',$_SESSION) && $_SESSION['globalMonth']) {
  } else {
    $_SESSION['globalYear']=$thisYear;
    $_SESSION['globalMonth']=$thisMonth;
    $_SESSION['globalDay']=$thisDay;
  }
  if(array_key_exists('changeMonth',$_GET) && $_GET['changeMonth'])
  { $_SESSION['globalMonth'] = $_GET['changeMonth'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('changeYear',$_GET) && $_GET['changeYear'])
  { $_SESSION['globalYear'] = $_GET['changeYear'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
  if(array_key_exists('changeDay',$_GET) && $_GET['changeDay'])
  { $_SESSION['globalDay'] = $_GET['changeDay'];
    if(array_key_exists('Qobj',$_SESSION))
      $_SESSION['Qobj']=$objObject->getObjectRisSetTrans($_SESSION['Qobj']);
  }
	  
}
function Nz($arg)
{ if($arg) return $arg;
  else     return ""; 
}
function Nz0($arg)
{ if($arg) return $arg;
  else     return 0; 
}
function Nzx($arg,$default="")
{ if($arg) return $arg;
  else     return $default; 
}
?>
