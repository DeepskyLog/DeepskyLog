<?php
	$inIndex=true;

  require_once "lib/setup/databaseInfo.php";
	require_once "lib/database.php";
	require_once "lib/util.php";
	require_once "lib/setup/language.php";
	require_once "lib/observers.php";
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
	include_once "lib/cometobservations.php";
	include_once "lib/cometobjects.php";
  include_once 'lib/presentation.php';                                                               // functions etc. concerning layout
	  
  $lLhr=$objUtil->checkGetKey('lLhr');
  $dDdeg=$objUtil->checkGetKey('dDdeg');
  $rLhr=$objUtil->checkGetKey('rLhr');
  $uDdeg=$objUtil->checkGetKey('uDdeg');
  $objects=$objUtil->checkGetKey('objects');
  $mag=$objUtil->checkGetKey('mag');
  if($lLhr<$rLhr)
  { $sql="SELECT * FROM stars WHERE (RA2000<".$lLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.") AND (vMag<=".$mag.") ORDER BY vMag;";
    $objects=$objDatabase->selectRecordsetArray($sql);  
    $sql="SELECT * FROM stars WHERE (RA2000>".$rLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.") AND (vMag<=".$mag.") ORDER BY vMag;";
    $objects=array_merge($objects,$objDatabase->selectRecordsetArray($sql));  
    $sql="SELECT * FROM objects WHERE (ra<".$lLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND (mag<=".$mag.") ORDER BY mag;";
    $objects=array_merge($objects,$objDatabase->selectRecordsetArray($sql));  
    $sql="SELECT * FROM objects WHERE (ra>".$rLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND (mag<=".$mag.") ORDER BY mag;";
    $objects=array_merge($objects,$objDatabase->selectRecordsetArray($sql));  
  }
  else
  { $sql="SELECT * FROM stars WHERE (RA2000<".$lLhr.") AND (RA2000>".$rLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.") AND (vMag<=".$mag.") ORDER BY vMag;";
    $objects=$objDatabase->selectRecordsetArray($sql);
    $sql="SELECT * FROM objects WHERE (ra<".$lLhr.") AND (ra>".$rLhr.") AND (decl>".$dDdeg.") AND (decl<".$uDdeg.") AND (mag<=".$mag.") ORDER BY mag;";
    $objects=array_merge($objects,$objDatabase->selectRecordsetArray($sql));  
  }
  header("Content-Type:text/xml");
  echo "<?xml version='1.0' encoding=\"ISO-8859-1\"?>";
  echo "<xmlresponse>";
  while(list($key,$value)=each($objects))
  { echo "<object>";
    while(list($objectproperty,$objectpropertyvalue)=each($value))
      echo   "<".$objectproperty.">".urlencode($objectpropertyvalue)."</".$objectproperty.">";
    echo "</object>";
  }
  echo "</xmlresponse>";
?>