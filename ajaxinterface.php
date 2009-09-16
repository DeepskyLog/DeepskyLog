<?php //ajaxinterface.php accepts ajax requests and dispatches the calls. Upon receiving teh result, an xml response is send back
$inIndex=true;
include 'common/entryexit/preludes.php';                                                                // Includes of all classes and assistance files
$ajaxInstruction=$objUtil->checkGetKey('instruction');
if($ajaxInstruction=="getObjects")
{ $objects=array();
  $objects=$objObject->getObjects($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey('mag',0));
  $objects=array_merge($objects,$objStar->getStars6($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey(('6'),0)));
  $objects=array_merge($objects,$objStar->getStars($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey(('vMag'),0)));
  header("Content-Type:text/xml");
  echo "<?xml version='1.0' encoding=\"ISO-8859-1\"?>";
  echo "<xmlresponse>";
  while(list($key,$value)=each($objects))
  { echo "<object>";
    while(list($objectproperty,$objectpropertyvalue)=each($value))
      echo   "<".$objectproperty.">".htmlspecialchars($objectpropertyvalue)."</".$objectproperty.">";
    echo "</object>";
  }
  echo "</xmlresponse>";
}
if($ajaxInstruction=="getStars6")
{ $objects=array();
  $objects=$objStar->getStars6($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey(('6'),0));
  header("Content-Type:text/xml");
  echo "<?xml version='1.0' encoding=\"ISO-8859-1\"?>";
  echo "<xmlresponse>";
  while(list($key,$value)=each($objects))
  { echo "<object>";
    while(list($objectproperty,$objectpropertyvalue)=each($value))
      echo   "<".$objectproperty.">".htmlspecialchars($objectpropertyvalue)."</".$objectproperty.">";
    echo "</object>";
  }
  echo "</xmlresponse>";
}
if($ajaxInstruction=="getStars")
{ $objects=array();
  $objects=$objStar->getStars($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey(('vMag'),0));
  header("Content-Type:text/xml");
  echo "<?xml version='1.0' encoding=\"ISO-8859-1\"?>";
  echo "<xmlresponse>";
  while(list($key,$value)=each($objects))
  { echo "<object>";
    while(list($objectproperty,$objectpropertyvalue)=each($value))
      echo   "<".$objectproperty.">".htmlspecialchars($objectpropertyvalue)."</".$objectproperty.">";
    echo "</object>";
  }
  echo "</xmlresponse>";
}
?>