<?php

$entryMessage='';
if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="add_observation")
{ if(array_key_exists('number',$_POST)&&(!$_POST['number']))
    $_GET['indexAction']="query_objects";
  elseif(array_key_exists('number',$_POST)&&(!($_GET['object']=$GLOBALS['objObject']->getExactDsObject('',$GLOBALS['objUtil']->checkPostKey('catalogue'), $GLOBALS['objUtil']->checkPostKey('number')))))
  { $entryMessage.="No corresponding object found for ".$GLOBALS['objUtil']->checkPostKey('catalogue')." ".$GLOBALS['objUtil']->checkPostKey('number');
	  $_GET['indexAction']="query_objects";
   }
  else
	{ $GLOBALS['objUtil']->checkPostKey('year',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsYear'));;
	  $_POST['year']=$GLOBALS['objUtil']->checkPostKey('year',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsYear')); 
    $_POST['month']=$GLOBALS['objUtil']->checkPostKey('month',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsMonth')); 
    $_POST['day']=$GLOBALS['objUtil']->checkPostKey('day',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsDay'));
    $_POST['instrument']=$GLOBALS['objUtil']->checkPostKey('instrument',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsInstrument')); 
    $_POST['site']=$GLOBALS['objUtil']->checkPostKey('site',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLocation')); 
    $_POST['limit']=$GLOBALS['objUtil']->checkPostKey('limit',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLimit')); 
    $_POST['sqm']=$GLOBALS['objUtil']->checkPostKey('sqm',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsSQM')); 
    $_POST['seeing']=$GLOBALS['objUtil']->checkPostKey('seeing',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsSeeing')); 
    $_POST['description_language']=$GLOBALS['objUtil']->checkPostKey('description_language',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLanguage'));
		$_POST['timestamp']=time();
		$_SESSION['addObs']=$_POST['timestamp'];
	} 
}
elseif(array_key_exists('newObservation',$_GET))                                // From quickpick
{ if(array_key_exists('object',$_GET)&&(!($_GET['object']=$GLOBALS['objObject']->getExactDsObject($_GET['object'],'', ''))))
  { $entryMessage.="No corresponding object found.";
	  $_GET['indexAction']="query_objects";
   }
  else
	{ $GLOBALS['objUtil']->checkPostKey('year',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsYear'));;
	  $_POST['year']=$GLOBALS['objUtil']->checkPostKey('year',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsYear')); 
    $_POST['month']=$GLOBALS['objUtil']->checkPostKey('month',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsMonth')); 
    $_POST['day']=$GLOBALS['objUtil']->checkPostKey('day',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsDay'));
    $_POST['instrument']=$GLOBALS['objUtil']->checkPostKey('instrument',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsInstrument')); 
    $_POST['site']=$GLOBALS['objUtil']->checkPostKey('site',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLocation')); 
    $_POST['limit']=$GLOBALS['objUtil']->checkPostKey('limit',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLimit')); 
    $_POST['sqm']=$GLOBALS['objUtil']->checkPostKey('sqm',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsSQM')); 
    $_POST['seeing']=$GLOBALS['objUtil']->checkPostKey('seeing',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsSeeing')); 
    $_POST['description_language']=$GLOBALS['objUtil']->checkPostKey('description_language',$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLanguage'));
		$_POST['timestamp']=time();
		$_SESSION['addObs']=$_POST['timestamp'];
	} 
}
elseif(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="clear_observation")
{ $_POST['year']='';                                                             // empty the fields of the new observation form
  $_POST['month']=''; 
  $_POST['day']='';
  $_POST['instrument']=''; 
  $_POST['site']=''; 
  $_POST['limit']=''; 
  $_POST['sqm']=''; 
  $_POST['seeing']=''; 
  $_POST['description_language']='';
	$_GET['indexAction']="add_observation";
}
if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_observation")
  include_once $instDir."/deepsky/control/validate_observation.php";
if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_change_observation")
  include_once $instDir."/deepsky/control/validate_change_observation.php";
if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_object")
  include_once $instDir."/deepsky/control/validate_object.php";
if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_delete_observation")
  include_once $instDir."/deepsky/control/validate_delete_observation.php";
// ============================================================================ LIST COMMANDS
if(array_key_exists('addObjectToList',$_GET)&&$_GET['addObjectToList']&&array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&$myList)
{ $objList->addObjectToList($_GET['addObjectToList'],$GLOBALS['objUtil']->checkGetKey('showname',$_GET['addObjectToList']));
  $entryMessage.=LangListQueryObjectsMessage8."<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=".urlencode($_GET['addObjectToList']) . "\">".$_GET['showname']."</a>".LangListQueryObjectsMessage6."<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
}
if(array_key_exists('addObservationToList',$_GET) && $_GET['addObservationToList'] && $myList)
{ $objList->addObservationToList($_GET['addObservationToList']);
  $entryMessage.=LangListQueryObjectsMessage16.LangListQueryObjectsMessage6."<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">".$listname_ss."</a>.";
}
if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
{ $objList->removeObjectFromList($_GET['removeObjectFromList']);
  $entryMessage.=LangListQueryObjectsMessage8."<a href=\"deepsky/index.php?indexAction=detail_object&object=".urlencode($_GET['removeObjectFromList'])."\">".$_GET['removeObjectFromList']."</a>".LangListQueryObjectsMessage7."<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">".$listname_ss."</a>.";
}

if(array_key_exists('addAllObjectsFromPageToList',$_GET) && $_GET['addAllObjectsFromPageToList'] && $myList)
{ $count=$min;
	while(($count<($min+25))&&($count<count($_SESSION['Qobj'])))
	{ $objList->addObjectToList($_SESSION['Qobj'][$count]['objectname'],$_SESSION['Qobj'][$count]['showname']);
		$count++;
  }
	$entryMessage = LangListQueryObjectsMessage9 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
}
if(array_key_exists('addAllObjectsFromQueryToList',$_GET)&&$_GET['addAllObjectsFromQueryToList']&&$myList)
{ $count=0;
	while($count<count($_SESSION['Qobj']))
	{ $objList->addObjectToList($_SESSION['Qobj'][$count]['objectname'],$_SESSION['Qobj'][$count]['showname']);
		$count++;
  }
	$entryMessage = LangListQueryObjectsMessage9 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
}
if(array_key_exists('editListObjectDescription',$_GET)&&$_GET['editListObjectDescription']
 &&array_key_exists('object',$_GET)&&$_GET['object']&&array_key_exists('description',$_GET))
{ $objList->setListObjectDescription($_GET['object'],$_GET['description']);
}
// ============================================================================ ADMIN COMMANDS
if(array_key_exists('admin', $_SESSION)&&$_SESSION['admin']=="yes")
{ if(array_key_exists("newaction",$_GET))
	{ if($_GET['newaction']=="NewName")
	  { $objObject->newName($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
		  $_GET['object'] = trim($_GET['newcatalogue'] . " " . ucwords(trim($_GET['newnumber'])));
    }	
  	if($_GET['newaction']=="NewAltName")
	    $objObject->newAltName($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
  	if($_GET['newaction']=="RemoveAltNameName")
	    $objObject->removeAltName($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
  	if($_GET['newaction']=="NewPartOf")
	    $objObject->newPartOf($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
  	if($_GET['newaction']=="RemovePartOf")
	    $objObject->removePartOf($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
  	if($_GET['newaction']=="RemoveAndReplaceObjectBy")
	  { $objObject->removeAndReplaceObjectBy($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
		  $_GET['object'] = trim($_GET['newcatalog'] . " " . ucwords(trim($_GET['newnumber'])));
	  }			
  	if($_GET['newaction']=="LangObjectSetRA")
	    $objObject->setRA($_GET['object'], $_GET['newnumber']);
  	if($_GET['newaction']=="LangObjectSetDECL")
	    $objObject->setDeclination($_GET['object'], $_GET['newnumber']);
  	if($_GET['newaction']=="LangObjectSetCon")
	    $objObject->setConstellation($_GET['object'], $_GET['newnumber']);
  	if($_GET['newaction']=="LangObjectSetType")
	    $objObject->setDsObjectType($_GET['object'], $_GET['newnumber']);
  	if($_GET['newaction']=="LangObjectSetMag")
	    $objObject->setMagnitude($_GET['object'], $_GET['newnumber']);
   	if($_GET['newaction']=="LangObjectSetSUBR")
	    $objObject->setSurfaceBrightness($_GET['object'], $_GET['newnumber']);
   	if($_GET['newaction']=="LangObjectSetDiam1")
		  $objObject->setDiam1($_GET['object'], $_GET['newnumber']);
   	if($_GET['newaction']=="LangObjectSetDiam2")
		  $objObject->setDiam2($_GET['object'], $_GET['newnumber']);
   	if($_GET['newaction']=="LangObjectSetPA")
		  $objObject->setPositionAngle($_GET['object'], $_GET['newnumber']);
	}
}
?>
