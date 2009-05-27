<?php  //instruction.php treats all commands for changing data in the database or setting program parameters
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{	if($objUtil->checkGetKey('indexAction')=="logout")                                                                 // logout
	  require_once $instDir."common/control/logout.php";
	//listnames
	$myList=False;
	$listname='';
	if(array_key_exists('listname', $_SESSION)&&($_SESSION['listname']<>"----------"))
	  $listname=$_SESSION['listname'];
	$listname_ss = stripslashes($listname);
	if(array_key_exists('listname',$_SESSION)&&$objList->checkList($_SESSION['listname'])==2)
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
	// pagenumbers
	if(!array_key_exists('steps',$_SESSION))
	{ if(array_key_exists('steps',$_COOKIE))
	  { $stepsbase=explode(";",$_COOKIE['steps']);
	    while(list($key,$value)=each($stepsbase))
	    { if($value)
	      { $stepsbaseitems=explode(":",$value);
	        $_SESSION['steps'][$stepsbaseitems[0]]=$stepsbaseitems[1];
	      }
	    }
	  }
	}
  if(($objUtil->checkGetKey('indexAction')=="result_selected_observations")&&(array_key_exists('steps',$_SESSION))&&(array_key_exists("selObs".$_SESSION['lco'],$_SESSION['steps'])))
    $step=$_SESSION['steps']["selObs".$_SESSION['lco']];
  else
    $step = 25;
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
  if($stepsType=$objUtil->checkGetKey('stepsCommand'))
	{ $_SESSION['steps'][$stepsType]=$objUtil->checkGetKey('stepsValue',25);
		reset($_SESSION['steps']);
		$stepscookie="";
		while(list($key,$value)=each($_SESSION['steps']))
		  $stepscookie.=$key.":".$value.";";
		$cookietime = time() + 365 * 24 * 60 * 60;            // 1 year
		setcookie("steps",$stepscookie,$cookietime, "/");
		reset($_SESSION['steps']);
	}
	//============================================================================== COMMON INSTRUCTIONS
	while(list($key,$value)=each($modules))                                                                            // change module
	  if($objUtil->checkGetKey('indexAction')=='module'.$value)
	  { $_SESSION['module']=$value;
	    setcookie("module",$value,time()+(365*24*60*60),"/");
	  }
	if($objUtil->checkGetKey('indexAction')=="validate_delete_eyepiece")                                               // delete eyepiece
	{ $entryMessage.=$objEyepiece->validateDeleteEyepiece();
	  if($_SESSION['admin']=='yes')
	    $_GET['indexAction']='view_eyepieces';
	  else
	    $_GET['indexAction']='add_eyepiece';
	}
	if($objUtil->checkGetKey('indexAction')=="validate_delete_filter")                                                 // delete filter
	{ $entryMessage.=$objFilter->validateDeleteFilter();
	  if($_SESSION['admin']=='yes')
	    $_GET['indexAction']='view_filters';
	  else
	    $_GET['indexAction']='add_filter';
	}
	if($objUtil->checkGetKey('indexAction')=="validate_delete_instrument")                                             // delete instrument 
	{ $entryMessage.=$objInstrument->validateDeleteInstrument();
	  if($_SESSION['admin']=='yes')
	    $_GET['indexAction']='view_instruments';
	  else
	    $_GET['indexAction']='add_instrument';
	}
	if($objUtil->checkGetKey('indexAction')=="validate_delete_lens")                                                   // delete lens
	{ $entryMessage.=$objLens->validateDeleteLens();
	  if($_SESSION['admin']=='yes')
	    $_GET['indexAction']='view_lenses';
	  else
	    $_GET['indexAction']="add_lens";
	}
	if($objUtil->checkGetKey('indexAction')=="validate_delete_location")                                               // delete location
	{ $entryMessage.=$objLocation->validateDeleteLocation();
	  if($_SESSION['admin']=='yes')
	    $_GET['indexAction']='view_locations';
	  else
	    $_GET['indexAction']='add_site';
	}  
	if($objUtil->checkGetKey('indexAction')=="validate_account")                                                       // validate account
	{ $objObserver->valideAccount();
	  //$entryMessage is set in the validateAccount() function;
	  //$_GET['indexAction'] is set in the validateAccount() function
	}
	if($objUtil->checkGetKey('indexAction')=="validate_eyepiece")                                                      // validate eyepiece
	{ $entryMessage.=$objEyepiece->validateSaveEyepiece();
	  $_GET['indexAction']='add_eyepiece';
	}
	if($objUtil->checkGetKey('indexAction')=="validate_filter")                                                        // validate filter
	{ $entryMessage.=$objFilter->validateSaveFilter();
	  $_GET['indexAction']='add_filter';
	}
	if($objUtil->checkGetKey('indexAction')=="validate_instrument")                                                    // validate instrument
	{  $entryMessage.=$objInstrument->validateSaveInstrument();
	   $_GET['indexAction']='add_instrument';
	}	
	if($objUtil->checkGetKey('indexAction')=="validate_lens")                                                          // validate lens
	{ $entryMessage.=$objLens->validateSaveLens();
	  $_GET['indexAction']='add_lens';
	}
	if(($objUtil->checkSessionKey('admin')=='yes')&&($objUtil->checkGetKey('indexAction')=="validate_observer"))       // validate observer
	{ $entryMessage.=$objObserver->validateObserver();
	  $_GET['indexAction']='view_observers';
	}
	if(($objUtil->checkSessionKey('admin')=='yes')&&($objUtil->checkGetKey('indexAction')=="validate_delete_observer"))       // validate observer
	{ $entryMessage.=$objObserver->validateDeleteObserver();
	  $_GET['indexAction']='view_observers';
	}
	if($objUtil->checkGetKey('indexAction')=="validate_site")                                                          // validate location
	{ $entryMessage.=$objLocation->validateSaveLocation();
	  $_GET['indexAction']="add_site";
	}
	//============================================================================== DEEPSKY INSTRUCTIONS
	$object=$objUtil->checkPostKey('object',$objUtil->checkGetKey('object'));
	if(($objUtil->checkGetKey('indexAction')=='quickpick') // ========================================================= New Observation From quickpick
	&&($objUtil->checkGetKey('object'))
	&&($objObject->getExactDsObject($_GET['object']))
	&&(array_key_exists('newObservationQuickPick',$_GET)))
	{ $_POST['year']=$objUtil->checkPostKey('year',$objUtil->checkArrayKey($_SESSION,'newObsYear')); 
	  $_POST['month']=$objUtil->checkPostKey('month',$objUtil->checkArrayKey($_SESSION,'newObsMonth')); 
	  $_POST['day']=$objUtil->checkPostKey('day',$objUtil->checkArrayKey($_SESSION,'newObsDay'));
	  $_POST['instrument']=$objUtil->checkPostKey('instrument',$objUtil->checkArrayKey($_SESSION,'newObsInstrument',$objObserver->getObserverProperty($loggedUser,'stdtelescope'))); 
	  $_POST['site']=$objUtil->checkPostKey('site',$objUtil->checkArrayKey($_SESSION,'newObsLocation',$objObserver->getObserverProperty($loggedUser,'stdlocation'))); 
	  $_POST['limit']=$objUtil->checkPostKey('limit',$objUtil->checkArrayKey($_SESSION,'newObsLimit')); 
	  $_POST['sqm']=$objUtil->checkPostKey('sqm',$objUtil->checkArrayKey($_SESSION,'newObsSQM')); 
	  $_POST['seeing']=$objUtil->checkPostKey('seeing',$objUtil->checkArrayKey($_SESSION,'newObsSeeing')); 
	  $_POST['description_language']=$objUtil->checkPostKey('description_language',$objUtil->checkArrayKey($_SESSION,'newObsLanguage'));
		$_POST['timestamp']=time();
		$_SESSION['addObs']=$_POST['timestamp'];
	} 
	if($objUtil->checkGetKey('indexAction')=="add_observation")
	{ if(array_key_exists('number',$_POST)&&(!$_POST['number']))
	    $_GET['indexAction']="query_objects";
	  elseif(array_key_exists('number',$_POST)&&(!($_GET['object']=$GLOBALS['objObject']->getExactDsObject('',$objUtil->checkPostKey('catalog'), $objUtil->checkPostKey('number')))))
	  { $entryMessage.=LangInstructionsNoObjectFound.$objUtil->checkPostKey('catalog')." ".$objUtil->checkPostKey('number');
	    $_GET['indexAction']="query_objects";
	   }
	  else
		{ $_POST['year']=$objUtil->checkPostKey('year',$objUtil->checkArrayKey($_SESSION,'newObsYear')); 
	    $_POST['month']=$objUtil->checkPostKey('month',$objUtil->checkArrayKey($_SESSION,'newObsMonth')); 
	    $_POST['day']=$objUtil->checkPostKey('day',$objUtil->checkArrayKey($_SESSION,'newObsDay'));
	    $_POST['instrument']=$objUtil->checkPostKey('instrument',$objUtil->checkSessionKey('newObsInstrument',$objObserver->getObserverProperty($loggedUser,'stdtelescope'))); 
	    $_POST['site']=$objUtil->checkPostKey('site',$objUtil->checkSessionKey('newObsLocation',$objObserver->getObserverProperty($loggedUser,'stdlocation'))); 
	    $_POST['limit']=$objUtil->checkPostKey('limit',$objUtil->checkSessionKey('newObsLimit',$objLocation->getLocationPropertyFromId('limitingMagnitude',-999))); 
	    $_POST['sqm']=$objUtil->checkPostKey('sqm',$objUtil->checkArrayKey($_SESSION,'newObsSQM')); 
	    $_POST['seeing']=$objUtil->checkPostKey('seeing',$objUtil->checkArrayKey($_SESSION,'newObsSeeing')); 
	    $_POST['description_language']=$objUtil->checkPostKey('description_language',$objUtil->checkArrayKey($_SESSION,'newObsLanguage'));
			$_POST['timestamp']=time();
			$_SESSION['addObs']=$_POST['timestamp'];
		} 
	}
	if(array_key_exists('indexAction',$_POST)&&$_POST['indexAction']=="validate_observation")
	  $objObservation->validateObservation();
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_object")
	  $objObject->validateObject();
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="validate_delete_observation")
	{ $entryMessage.=$objObservation->validateDeleteDSObservation();
		$_GET['indexAction']='default_action';
		unset($_GET['validate_delete_observation']);
	}
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="manage_csv_objects")
	  include_once "deepsky/control/manage_csv_objects.php";
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="add_csv_observations")
	  $entryMessage.=$objObservation->addCSVobservations();
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="add_csv_listdata")
	  include_once "deepsky/control/add_csv_listdata.php";
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="add_xml_observations")
	  include_once "deepsky/control/add_xml_observations.php";
	  
	  
	// ============================================================================ LIST COMMANDS
	if($objUtil->checkGetKey('emptyList')&&$myList)
	{ $objList->emptyList($listname);
	  $entryMessage.=LangToListEmptied.$listname_ss.".";
		unset($_SESSION['QobjParams']);
	  unset($_GET['emptyList']);
	}
	if($objUtil->checkGetKey('ObjectDownInList')&&$myList)
	{ $objList->ObjectDownInList($_GET['ObjectDownInList']);
		unset($_SESSION['QobjParams']);
	  $entryMessage.=LangToListMoved1.$_GET['ObjectDownInList'].LangToListMoved3."<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
	  unset($_GET['ObjectDownInList']);
	}
	if($objUtil->checkGetKey('ObjectUpInList')&&$myList)
	{ $objList->ObjectUpInList($_GET['ObjectUpInList']);
		unset($_SESSION['QobjParams']);
	  $entryMessage.=LangToListMoved1.$_GET['ObjectUpInList'].LangToListMoved2."<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
	  unset($_GET['ObjectUpInList']);
	}
	
	if($objUtil->checkGetKey('ObjectToPlaceInList')&&$myList)
	{ $entryMessage.=$objList->ObjectFromToInList($_GET['ObjectFromPlaceInList'],$_GET['ObjectToPlaceInList']);
		unset($_SESSION['QobjParams']);
	  unset($_GET['ObjectToPlaceInList']);
	}
	if($objUtil->checkGetKey('removePageObjectsFromList')&&$myList)
	{ if(count($_SESSION['Qobj'])>0)
		{ if(array_key_exists('min',$_GET) && $_GET['min'])
	     $min=$_GET['min'];
	    else
	     $min=0;
			$count=$min;
		  while(($count<($min+25))&&($count<count($_SESSION['Qobj'])))
		  {$objList->removeObjectFromList($_SESSION['Qobj'][$count]['objectname'],$_SESSION['Qobj'][$count]['showname']);
			  $count++;
	    }
		  unset($_SESSION['QobjParams']);
	    $entryMessage.=LangToListPageRemoved;
		}
	  unset($_GET['removePageObjectsFromList']);
	}
	if($objUtil->checkGetKey('addList')&&($listnameToAdd=$objUtil->checkGetKey('addlistname')))
	{ unset($_SESSION['QobjParams']);
	  if(array_key_exists("PublicList",$_GET))
	    if(substr($listnameToAdd,0,7)!="Public:")
	      $listnameToAdd="Public: ".$listnameToAdd;  
	  if($objList->checkList($listnameToAdd)!=0)
	    $entryMessage.=LangToListList.stripslashes($listnameToAdd).LangToListExists;
	  else
	  { $objList->addList($listnameToAdd);
	    $_SESSION['listname'] = $listnameToAdd;
	    $listname=$_SESSION['listname'];
	    $listname_ss=stripslashes($listname);
	    $myList=true;
	    $entryMessage.=LangToListList.$listname_ss.LangToListAdded;
	  }                    	
	  unset($_GET['addList']);
	}
	if($objUtil->checkGetKey('renameList')&&($listnameToAdd=$objUtil->checkGetKey('addlistname'))&&$myList)
	{ unset($_SESSION['QobjParams']);
	  $listnameTo=$_GET['addlistname'];
	  if(array_key_exists("PublicList",$_GET))
		  if(substr($listnameTo,0,7)!="Public:")
		    $listnameTo="Public: ".$listnameTo;  
	  if($objList->checkList($listnameTo)!=0)
	     $entryMessage.=LangToListList.stripslashes($listnameTo).LangToListExists;
	  else
	  { $objList->renameList($listname, $listnameTo);
	    $_SESSION['listname']=$listnameTo;
	    $listname=$_SESSION['listname'];
	    $listname_ss=stripslashes($listname);
	    $myList=true;
	    $entryMessage.=LangToListList.$listname_ss.LangToListAdded; 
	  }
	  unset($_GET['renameList']);
	}
	if($objUtil->checkGetKey('removeList')&&$myList)
	{ unset($_SESSION['QobjParams']);
		$objList->removeList($listname);
		$entryMessage.=LangToListRemoved.stripslashes($_SESSION['listname']).".";
		unset($_GET['removeList']);
		$_SESSION['listname']="----------";
		$listname='';
	  $listname_ss='';
	  $myList=False;
	  unset($_GET['removeList']);
	}
	if($objUtil->checkGetKey('activateList')&&$objUtil->checkGetKey('listname'))
	{ $_SESSION['listname']=$_GET['listname'];
	  $listname=$_SESSION['listname'];
	  $listname_ss=stripslashes($listname);
	  $myList=False;
	  if(array_key_exists('listname',$_SESSION)&&$objList->checkList($_SESSION['listname'])==2)
	    $myList=True;
	  if($_GET['listname']<>"----------")
	    $entryMessage.=LangToListList.$listname_ss.LangToListActivation1;
	  unset($_GET['activateList']);
	}
	if($objUtil->checkGetKey('addObjectToList')&&$listname&&$myList)
	{ $objList->addObjectToList($_GET['addObjectToList'],$objUtil->checkGetKey('showname',$_GET['addObjectToList']));
	  $entryMessage.=LangListQueryObjectsMessage8."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['addObjectToList']) . "\">".$_GET['showname']."</a>".LangListQueryObjectsMessage6."<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
	  unset($_GET['addObjectToList']);
	}
	if(array_key_exists('addObservationToList',$_GET) && $_GET['addObservationToList'] && $myList)
	{ $objList->addObservationToList($_GET['addObservationToList']);
	  $entryMessage.=LangListQueryObjectsMessage16.LangListQueryObjectsMessage6."<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
	  unset($_GET['addObservationToList']);
	}
	if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
	{ $objList->removeObjectFromList($_GET['removeObjectFromList']);
	  $entryMessage.=LangListQueryObjectsMessage8."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['removeObjectFromList'])."\">".$_GET['removeObjectFromList']."</a>".LangListQueryObjectsMessage7."<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
	  unset($_GET['removeObjectFromList']);
	}
	
	if(array_key_exists('addAllObjectsFromPageToList',$_GET) && $_GET['addAllObjectsFromPageToList'] && $myList)
	{ $count=$min;
		while(($count<($min+25))&&($count<count($_SESSION['Qobj'])))
		{ $objList->addObjectToList($_SESSION['Qobj'][$count]['objectname'],$_SESSION['Qobj'][$count]['showname']);
			$count++;
	  }
		$entryMessage = LangListQueryObjectsMessage9 . "<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">".$listname_ss."</a>.";
	  unset($_GET['addAllObjectsFromPageToList']);
	}
	if(array_key_exists('addAllObjectsFromQueryToList',$_GET)&&$_GET['addAllObjectsFromQueryToList']&&$myList)
	{ $count=0;
		while($count<count($_SESSION['Qobj']))
		{ $objList->addObjectToList($_SESSION['Qobj'][$count]['objectname'],$_SESSION['Qobj'][$count]['showname']);
			$count++;
	  }
		$entryMessage = LangListQueryObjectsMessage9 . "<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
	  unset($_GET['addAllObjectsFromQueryToList']);
	}
	if(array_key_exists('editListObjectDescription',$_GET)&&$_GET['editListObjectDescription']
	&& array_key_exists('object',$_GET)&&$_GET['object']&&array_key_exists('description',$_GET) && $myList)
	{ $objList->setListObjectDescription($_GET['object'],$_GET['description']);
	  unset($_GET['addAllObjectsFromPageToList']);
	}
	
	
	// =========================================================================== COMET COMMANDS
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="comets_validate_observation")
	  include_once 'comets/control/validate_observation.php';
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="comets_validate_object")
	  include_once 'comets/control/validate_object.php';
	if(array_key_exists('indexAction',$_GET)&&$_GET['indexAction']=="comets_validate_change_object")
	  include_once 'comets/control/validate_change_object.php';
	// ============================================================================ ADMIN COMMANDS
	if(($objUtil->checkSessionKey('admin')=='yes')&&($objUtil->checkGetKey('indexAction')=="change_role"))
	{ if(($_SESSION['admin']=="yes")
	  && ($objUtil->checkGetKey('user')))
	  { $role=$objUtil->checkGetKey('role',2);
	    $objObserver->setObserverProperty($_GET['user'],'role', $role);
	    $entryMessage.="Role is successfully updated!";
	  }
	  $_GET['indexAction']="detail_observer";  
	}
	if(array_key_exists('admin', $_SESSION)&&$_SESSION['admin']=="yes")
	{ if(array_key_exists("newaction",$_GET))
		{ if($_GET['newaction']=="NewName")
		  { $objObject->newName($_GET['object'], $_GET['newcatalog'],$_GET['newnumber']);
			  $_GET['object'] = trim($_GET['newcatalog'] . " " . ucwords(trim($_GET['newnumber'])));
	    }	
	  	if($_GET['newaction']=="NewAltName")
		    $objObject->newAltName($_GET['object'], $_GET['newcatalog'],$_GET['newnumber']);
	  	if($_GET['newaction']=="RemoveAltNameName")
		    $objObject->removeAltName($_GET['object'], $_GET['newcatalog'],$_GET['newnumber']);
	  	if($_GET['newaction']=="NewPartOf")
		    $objObject->newPartOf($_GET['object'], $_GET['newcatalog'],$_GET['newnumber']);
	  	if($_GET['newaction']=="RemovePartOf")
		    $objObject->removePartOf($_GET['object'], $_GET['newcatalog'],$_GET['newnumber']);
	  	if($_GET['newaction']=="RemoveAndReplaceObjectBy")
		  { $objObject->removeAndReplaceObjectBy($_GET['object'], $_GET['newcatalog'],$_GET['newnumber']);
			  $_GET['object'] = trim($_GET['newcatalog'] . " " . ucwords(trim($_GET['newnumber'])));
		  }			
	  	if($_GET['newaction']=="LangObjectSetRA")
	  	{ $objObject->setDsoProperty($_GET['object'],'ra', $_GET['newnumber']);
	  	  $objObject->setDsObjectAtlasPages($_GET['object']);
	  	}
	  	if($_GET['newaction']=="LangObjectSetDECL")
	  	{ $objObject->setDsoProperty($_GET['object'],'decl', $_GET['newnumber']);
	  	  $objObject->setDsObjectAtlasPages($_GET['object']); 
	  	}
	  	if($_GET['newaction']=="LangObjectSetCon")
		    $objObject->setDsoProperty($_GET['object'],'con', $_GET['newnumber']);
	  	if($_GET['newaction']=="LangObjectSetType")
		    $objObject->setDsoProperty($_GET['object'],'tpe', $_GET['newnumber']);
	  	if($_GET['newaction']=="LangObjectSetMag")
	  	{ $objObject->setDsoProperty($_GET['object'],'mag', $_GET['newnumber']);
	  	  $objObject->setDsObjectSBObj($_GET['object']);
	  	}
	   	if($_GET['newaction']=="LangObjectSetSUBR")
		    $objObject->setDsoProperty($_GET['object'],'subr', $_GET['newnumber']);
	   	if($_GET['newaction']=="LangObjectSetDiam1")
	   	{ $objObject->setDsoProperty($_GET['object'],'diam1', $_GET['newnumber']);
	  	  $objObject->setDsObjectSBObj($_GET['object']);
	   	}
	   	if($_GET['newaction']=="LangObjectSetDiam2")
	   	{ $objObject->setDsoProperty($_GET['object'],'diam2', $_GET['newnumber']);
	  	  $objObject->setDsObjectSBObj($_GET['object']);
	   	}
	   	if($_GET['newaction']=="LangObjectSetPA")
			  $objObject->setDsoProperty($_GET['object'],'pa', $_GET['newnumber']);
		}
	}
}?>
