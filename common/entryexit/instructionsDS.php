<?php

$entryMessage='';
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
if($entryMessage)
  echo $entryMessage.'<hr />';
?>
