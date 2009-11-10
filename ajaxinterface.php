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
if($ajaxInstruction=="getObjectsMagnitude")
{ $objects=array();
  $objects=$objObject->getObjectsMag($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey('frommag',0),$objUtil->checkGetKey('tomag',10));
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
if($ajaxInstruction=="getStarsMagnitude")
{ $objects=array();
  $objects=$objStar->getStarsMagnitude($objUtil->checkGetKey('lLhr',0),$objUtil->checkGetKey('rLhr',0),$objUtil->checkGetKey('dDdeg',0),$objUtil->checkGetKey('uDdeg',0),$objUtil->checkGetKey(('frommag'),0),$objUtil->checkGetKey(('tomag'),0));
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
if($ajaxInstruction=="getConstellationBoundries")
{ $boundries=array();
  $boundries=$objConstellation->getAllBoundries();
  header("Content-Type:text/xml");
  echo "<?xml version='1.0' encoding=\"ISO-8859-1\"?>";
  echo "<xmlresponse>";
  while(list($key,$value)=each($boundries))
  { echo "<boundry>";
    while(list($boundryproperty,$boundrypropertyvalue)=each($value))
      echo   "<".$boundryproperty.">".htmlspecialchars($boundrypropertyvalue)."</".$boundryproperty.">";
    echo "</boundry>";
  }
  echo "</xmlresponse>";
}
?>