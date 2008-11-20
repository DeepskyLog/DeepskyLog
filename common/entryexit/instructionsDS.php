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
	{ $_POST['year']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsYear'); 
    $_POST['month']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsMonth'); 
    $_POST['day']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsDay');
    $_POST['instrument']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsInstrument'); 
    $_POST['site']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLocation'); 
    $_POST['limit']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLimit'); 
    $_POST['sqm']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsSQM'); 
    $_POST['seeing']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsSeeing'); 
    $_POST['description_language']=$GLOBALS['objUtil']->checkArrayKey($_SESSION,'newObsLanguage');
	} 
}
if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_observation")
{ if(!array_key_exists('deepskylog_id', $_SESSION)||!$_SESSION['deepskylog_id'])
    throw new Exception("Not logged in");
  if (!$_POST['day'] || !$_POST['month'] || !$_POST['year'] || $_POST['site'] == "1" || !$_POST['instrument'] || !$_POST['description'])
  { if($GLOBALS['objUtil']->checkPostKey('limit'))
      if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})',$_POST['limit'],$matches))     // limiting magnitude like X.X or X,X with X a number between 0 and 9
        $_POST['limit']=$matches[1].".".(($matches[2])?$matches[2]:"0");
  	  else
        $_POST['limit']="";                                                     // clear current magnitude limit
    else
      $_POST['limit']="";
    $entryMessage.="Not all necessary fields are filled in.".LangValidateObservationMessage1;
		$_GET['indexAction']='add_observation';
  }
  else                                                                          // all fields filled in
  { if($_FILES['drawing']['size'] > $maxFileSize)                               // file size of drawing too big
    { $entryMessage.=LangValidateObservationMessage6;
      $entryMessage.="File size of drawing too big";
		  $_GET['indexAction']='add_observation';
    }
    else
    { $date=$_POST['year'].printf("%02d", $_POST['month']).sprintf("%02d",$_POST['day']);
      if($_POST['hours'])
      { if(isset($_POST['minutes']))
          $time=($_POST['hours']*100)+$_POST['minutes'];
        else
          $time=($_POST['hours']*100);
      }
      else
        $time = -9999;
      // add observation to database
		  if($GLOBALS['objUtil']->checkPostKey('limit'))
        if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})',$_POST['limit'],$matches))     // limiting magnitude like X.X or X,X with X a number between 0 and 9
          $_POST['limit']=$matches[1].".".(($matches[2])?$matches[2]:"0");
  	    else
          $_POST['limit']="";                                                     // clear current magnitude limit
      // add observation to database
      $current_observation =  $GLOBALS['objObservation']->addDSObservation($_POST['object'], $_SESSION['deepskylog_id'], $_POST['instrument'], $_POST['site'], $date, $time, nl2br($_POST['description']), $_POST['seeing'], $_POST['limit'], $GLOBALS['objUtil']->checkPostKey('visibility'), $_POST['description_language']);
			if ($_POST['filter'])   $GLOBALS['objObservation']->setFilterId($current_observation, $_POST['filter']);
			if ($_POST['lens'])     $GLOBALS['objObservation']->setLensId($current_observation, $_POST['lens']);
			if ($_POST['eyepiece']) $GLOBALS['objObservation']->setEyepieceId($current_observation, $_POST['eyepiece']);
			if ($GLOBALS['objObserver']->getUseLocal($_SESSION['deepskylog_id']))
                              $GLOBALS['objObservation']->setLocalDateAndTime($current_observation, $date, $time);
      if($_FILES['drawing']['tmp_name'] != "")                                  // drawing to upload
      { $upload_dir = '../drawings';
        $dir = opendir($upload_dir);
        $original_image = $_FILES['drawing']['tmp_name'];
        $destination_image = $upload_dir . "/" . $current_observation . "_resized.jpg";
        $max_width = "490";
        $max_height = "490";
        $resample_quality = "100";
   
        include $instDir."/common/control/resize.php";                          // resize code
        $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);
        move_uploaded_file($_FILES['drawing']['tmp_name'], $upload_dir . "/" . $current_observation . ".jpg");
      }
      $_SESSION['newObsYear'] =       $_POST['year'];                           // save current details for faster submission of multiple observations
      $_SESSION['newObsMonth'] =      $_POST['month'];
      $_SESSION['newObsDay'] =        $_POST['day'];
      $_SESSION['newObsInstrument'] = $_POST['instrument'];
      $_SESSION['newObsLocation'] =   $_POST['site'];
      $_SESSION['newObsLimit'] =      $_POST['limit'];
      $_SESSION['newObsSQM'] =        $_POST['sqm'];
      $_SESSION['newObsSeeing'] =     $_POST['seeing'];
      $_SESSION['newObsLanguage'] =   $_POST['description_language'];
      $_SESSION['newObsSavedata'] =   "yes";
      $_GET['indexAction']="detail_observation";
			$_GET['dalm']='D';
			$_GET['observation']=$current_observation;
			$_GET['new']='yes';
    }  
  }
}
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
