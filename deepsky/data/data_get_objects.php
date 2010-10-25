<?php

data_get_objects();

function data_get_objects()
{ global $showPartOfs,$listname,
         $objObservation,$objCatalog,$objList,$objObject,$objUtil;
	$showPartOfs=$objUtil->checkGetKey('showPartOfs',$objUtil->checkSessionKey('QobjPO',0));
	// ========================================= filter objects from observation query
	if($objUtil->checkGetKey('source')=='observation_query') 
	{	$validQobj=false;
	  if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='observation_query'))
		  $validQobj=true;
		while($validQobj&&(list($key,$value)=each($_SESSION['QobjParams'])))
		  if((!array_key_exists($key,$_SESSION['QobsParams'])||($value!=$_SESSION['QobsParams'][$key]))&&($key!='source'))
		    $validQobj=false;
		while($validQobj&&(list($key,$value) = each($_SESSION['QobsParams'])))
		  if(!array_key_exists($key,$_SESSION['QobjParams'])||($value!=$_SESSION['QobjParams'][$key]))
		    $validQobj=false;	 
	  if($showPartOfs!=$objUtil->checkSessionKey('QobjPO',0))
	    $validQobj=false;
		if(!$validQobj)
		{ $obj = $objObject->getSeenObjectDetails($objObservation->getObjectsFromObservations($_SESSION['Qobs'],$showPartOfs),'A');
	    $_SESSION['QobjParams']=array_merge(array('source'=>'observation_query'),$_SESSION['QobsParams']);
	    $_SESSION['QobjPO']=$showPartOfs;
	    $_SESSION['Qobj']=$obj;
			$_SESSION['QobjSort']='showname';
		  $_SESSION['QobjSortDirection']='asc';
		}
	}
	// ========================================= get objects from list
	elseif($objUtil->checkGetKey('source')=='tolist')
	{ $validQobj=false;
	  if(array_key_exists('QobjParams',$_SESSION)
	  && array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='tolist')
	  && array_key_exists('list',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['list']==$listname))
		  $validQobj=true;
		if(!$validQobj)
		{ $_SESSION['QobjParams']=array('source'=>'tolist','list'=>$listname);
		  $_SESSION['Qobj']=$objList->getObjectsFromList($_SESSION['listname']);
		  if(!($objUtil->checkGetKey('sort')))
		    $_GET['sort']='objectpositioninlist';
		  if(!($objUtil->checkGetKey('sortdirection')))
		    $_GET['sortdirection']='asc';
		  $_SESSION['QobjSort']='';
		  $_SESSION['QobjSortDirection']='';
		}
	}
	// ========================================= get top objects
	elseif($objUtil->checkGetKey('source')=='top_objects')
	{ $validQobj=false;
	  if(array_key_exists('QobjParams',$_SESSION)
	  && array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='top_objects'))
		  $validQobj=true;
	  if(!$validQobj)
		{ $_SESSION['QobjParams']=array('source'=>'top_objects');
		  $_SESSION['Qobj']=$objObject->getSeenObjectDetails($objObservation->getPopularObservations(),"A");
		  $_SESSION['QobjSort']='objectpositioninlist';
		  $_SESSION['QobjSortDirection']='asc';
		}
	}
	// ========================================= get nearby objects for selected object
	elseif($objUtil->checkGetKey('source')=='objects_nearby')
	{ $validQobj=false;
	  if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='objects_nearby')
		 &&array_key_exists('object',$_GET)        &&array_key_exists('object',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['object']==$_GET['object'])
		 &&array_key_exists('zoom',$_GET)          &&array_key_exists('zoom',  $_SESSION['QobjParams'])&&($_SESSION['QobjParams']['zoom']==$_GET['zoom']))
		  $validQobj=true;
	  if(!$validQobj)
		{ $_SESSION['QobjParams']=array('source'=>'objects_nearby','object'=>$_GET['object'],'zoom'=>$_GET['zoom']);
		  $_SESSION['Qobj']=$objObject->getSeenObjectDetails($objObject->getNearbyObjects($_GET['object'],$_GET['zoom']));
		  $_SESSION['QobjSort']='objectname';
		  $_SESSION['QobjSortDirection']='asc';
		}
	}
	// ========================================= get objects for objects query page
	elseif($objUtil->checkGetKey('source')=='setup_objects_query')
	{ $exact = 0;
	  if(array_key_exists('catalog',$_GET) && $_GET['catalog']) $name = $_GET['catalog'];
	  if(array_key_exists('catalog',$_GET)) $catalog = $_GET['catalog'];
	  if(array_key_exists('catNumber',$_GET)) $catNumber = $_GET['catNumber'];
	  if(array_key_exists('atlas',$_GET) && $_GET['atlas']);
	  $atlas=$objUtil->checkGetKey('atlas',(($loggedUser)?$objAtlas->atlasCodes[$objObserver->getObserverProperty($loggedUser,'standardAtlasCode','urano')]:''));
	  $atlasPageNumber=$objUtil->checkGetKey('atlasPageNumber','');
	  if(array_key_exists('inList', $_GET)) $inList = $_GET['inList']; else $inList = '';
	  if(array_key_exists('notInList', $_GET)) $notInList = $_GET['notInList']; else $notInList = '';
	  if(array_key_exists('size_min_units',$_GET)) $size_min_units=$_GET['size_min_units']; else $size_min_units='';
	  if(array_key_exists('size_max_units',$_GET)) $size_max_units=$_GET['size_max_units']; else $size_max_units='';
	  if(array_key_exists('catNumber',$_GET) && $_GET['catNumber'])
	  { $name = ucwords(trim($name . " " . trim($_GET['catNumber'])));
	    $exact = "1";
	  }
	  // ATLAS PAGE
	  if(array_key_exists('atlasPageNumber',$_GET) && $_GET['atlasPageNumber'])
	  { if(!is_numeric($_GET['atlasPageNumber']) || ($_GET['atlasPageNumber']<1) || ($_GET['atlasPageNumber']>5000))
	      $pageError = true;
	    else
	      $atlasPageNumber = $_GET['atlasPageNumber'];
	  }
	  $con =$objUtil->checkGetKey('con');                                           // CONSTELLATION
	  $conto =$objUtil->checkGetKey('conto',$con);                                           // CONSTELLATION
	  $type=$objUtil->checkGetKey('type');                                          // TYPE
	  $descriptioncontains=$objUtil->checkGetKey('descriptioncontains');
	  $minDecl='';
	  if(($minDeclDegrees=$objUtil->checkGetKey('minDeclDegrees'))!='')             // MINIMUM DECLINATION
	  { if((!is_numeric($minDeclDegrees))||($minDeclDegrees<=-90)||($minDeclDegrees>=90))
	      $minDeclDegreesError = True;
	    $minDeclMinutes=$objUtil->checkGetKey('minDeclMinutes',0);
	    if((!is_numeric($minDeclMinutes))||($minDeclMinutes<0)||($minDeclMinutes>=60))
	      $minDeclMinutesError = true;
	    $minDeclSeconds=$objUtil->checkGetKey('minDeclSeconds',0); 
	    if((!is_numeric($minDeclSeconds))||($minDeclSeconds<0)||($minDeclSeconds>=60))
	      $minDeclSecondsError = true;
	    if(!($errorQuery=($minDeclDegreesError||$minDeclMinutesError||$minDeclSecondsError)))
	      if(substr(trim($_GET['minDeclDegrees']),0,1)=="-")
	        $minDecl=$minDeclDegrees-($minDeclMinutes/60)-($minDeclSeconds/3600);
	      else 
	        $minDecl=$minDeclDegrees+($minDeclMinutes/60)+($minDeclSeconds/3600);
	  }
	  $maxDecl='';
	  if(($maxDeclDegrees=$objUtil->checkGetKey('maxDeclDegrees'))!='')   // MAXIMUM DECLINATION 
	  { if((!is_numeric($maxDeclDegrees))||($maxDeclDegrees<=-90)||($maxDeclDegrees>=90))
	      $maxDeclDegreesError = true;
	    $maxDeclMinutes=$objUtil->checkGetKey('maxDeclMinutes',0);
	    if((!is_numeric($maxDeclMinutes))||($maxDeclMinutes<0)||($maxDeclMinutes>=60))
	      $maxDeclMinutesError = true;
	    $maxDeclSeconds=$objUtil->checkGetKey('maxDeclSeconds',0); 
	    if((!is_numeric($maxDeclSeconds))||($maxDeclSeconds<0)||($maxDeclSeconds>=60))
	      $maxDeclSecondsError = true;
	    if(!($errorQuery=($maxDeclDegreesError||$maxDeclMinutesError||$maxDeclSecondsError)))
	      if(substr(trim($_GET['maxDeclDegrees']),0,1)=="-")
	        $maxDecl=$maxDeclDegrees-($maxDeclMinutes/60)-($maxDeclSeconds/3600);
	      else 
	        $maxDecl=$maxDeclDegrees+($maxDeclMinutes/60)+($maxDeclSeconds/3600);
	  }
	  // MIN RA
	  $minRA='';
	  if(($minRAHours=$objUtil->checkGetKey('minRAHours'))!='') 
	  { if((!is_numeric($_GET['minRAHours'])) || ($_GET['minRAHours']<0) || ($_GET['minRAHours']>24))
	      $minRAHoursError = true;
	  	if(array_key_exists('minRAMinutes',$_GET) && $_GET['minRAMinutes']!='') 
	    { $minRAMinutes = $_GET['minRAMinutes']; 
	      if((!is_numeric($_GET['minRAMinutes'])) || ($_GET['minRAMinutes']<0) || ($_GET['minRAMinutes']>=60))
	        $minRAMinutesError = true;
	    }
	    else
	    { $minRAMinutes = 0; 
	      $_GET['minRAMinutes']=0; 
	    }
	    if(array_key_exists('minRASeconds',$_GET) && $_GET['minRASeconds']!='') 
	    { if((!is_numeric($_GET['minRASeconds'])) || ($_GET['minRASeconds']<0) || ($_GET['minRASeconds']>=60))
	        $minRASecondsError = true;
	      else
	        $minRASeconds = $_GET['minRASeconds']; 
	    }
	    else
	    { $minRASeconds = 0;
	      $_GET['minRASeconds'] = 0;
	    }
	    if(!($minRAHoursError || $minRAMinutesError || $minRASecondsError))
	      $minRA = $minRAHours + ($minRAMinutes/60) + ($minRASeconds/3600);
	  }
	  // MAX RA
	  $maxRA='';
	  if(($maxRAHours=$objUtil->checkGetKey('maxRAHours'))!='') 
	  { if((!is_numeric($_GET['maxRAHours'])) || ($_GET['maxRAHours']<0) || ($_GET['maxRAHours']>24))
	      $maxRAHoursError = true;
	    if(array_key_exists('maxRAMinutes',$_GET) && $_GET['maxRAMinutes']!='') 
	    { $maxRAMinutes = $_GET['maxRAMinutes']; 
	      if((!is_numeric($_GET['maxRAMinutes'])) || ($_GET['maxRAMinutes']<0) || ($_GET['maxRAMinutes']>=60))
	        $maxRAMinutesError = true;
	    }
	    else
	    { $maxRAMinutes = 0; 
	      $_GET['maxRAMinutes']=0; 
	    }
	    if(array_key_exists('maxRASeconds',$_GET) && $_GET['maxRASeconds']!='') 
	    { if((!is_numeric($_GET['maxRASeconds'])) || ($_GET['maxRASeconds']<0) || ($_GET['maxRASeconds']>=60))
	        $maxRASecondsError = true;
	      else
	        $maxRASeconds = $_GET['maxRASeconds']; 
	    }
	    else
	    { $maxRASeconds = 0;
	      $_GET['maxRASeconds'] = 0;
	    }
	    if(!($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError))
	      $maxRA=$maxRAHours+($maxRAMinutes/60)+($maxRASeconds/3600);
	  }
	  // MAGNITUDE BRIGHTER THAN
	  if(array_key_exists('maxMag',$_GET) && $_GET['maxMag']!='') 
	  { $maxMag = $_GET['maxMag'];
	    if((!is_numeric($_GET['maxMag'])) || ($_GET['maxMag']<=-2) || ($_GET['maxMag']>=30))
	      $maxMagError=true;
	  }
	  // MAGNITUDE LESSER THAN
	  if(array_key_exists('minMag',$_GET) && $_GET['minMag']!='')   
	  { $minMag = $_GET['minMag'];
	    if((!is_numeric($_GET['minMag'])) || ($_GET['minMag']<=-2) || ($_GET['minMag']>=30))
	      $minMagError=true;
	  }
	  // SB BRIGHTER THAN
	  if(array_key_exists('maxSB',$_GET) && $_GET['maxSB']!='')  
	  { $maxSB = $_GET['maxSB'];
	    if((!is_numeric($_GET['maxSB'])) || ($_GET['maxSB']<=-2) || ($_GET['maxSB']>=30))
	      $maxSBError=true;
	  }
	  // SB LESSER THAN
	  if(array_key_exists('minSB',$_GET) && $_GET['minSB']!='')
	  { $minSB = $_GET['minSB'];
	    if((!is_numeric($_GET['minSB'])) || ($_GET['minSB']<=-2) || ($_GET['minSB']>=30))
	      $minSBError=true;
	  }
	  // MINIMUM SIZE
	  $minSizeC='';
	  if(array_key_exists('minSize',$_GET) && ($_GET['minSize']!=''))
	  { if((!is_numeric($_GET['minSize'])) || ($_GET['minSize']<0))
	      $minSizeError=True; 
	    if(array_key_exists('size_min_units', $_GET) && ($_GET['size_min_units'] == "sec"))
	    { $size_min_units = 'sec';
	      $minSize = $_GET['minSize'];
	      $minSizeC = $_GET['minSize'];
	    }
	    else
	    { $size_min_units = 'min';
	      $minSize = $_GET['minSize'];
	      $minSizeC = $_GET['minSize'] * 60;
	    }
	  }
	  // MAXIMUM SIZE
	  $maxSizeC='';
	  if(array_key_exists('maxSize',$_GET) && $_GET['maxSize']!='')
	  { if((!is_numeric($_GET['maxSize'])) || ($_GET['maxSize']<0))
	      $maxSizeError=True; 
	    if(array_key_exists('size_max_units', $_GET) && ($_GET['size_max_units'] == "sec"))
	    {
	      $size_max_units = 'sec';
	      $maxSize = $_GET['maxSize'];
	      $maxSizeC = $_GET['maxSize'];
	    }
	    else
	    {
	      $size_max_units = 'min';
	      $maxSize = $_GET['maxSize'];
	      $maxSizeC = $_GET['maxSize'] * 60;
	    }
	  }
	  // MIN CONTRAST
	  if(array_key_exists('minContrast',$_GET) && $_GET['minContrast']!='')	   
	  { $minContrast = $_GET['minContrast'];
	    if(!is_numeric($_GET['minContrast']))
	      $minContrastError=True; 
	  }
	  // MAX CONTRAST
	  if(array_key_exists('maxContrast',$_GET) && $_GET['maxContrast']!='')	   
	  { $maxContrast = $_GET['maxContrast'];
	    if(!is_numeric($_GET['maxContrast']))
	      $maxContrastError=True; 
	  }
	  // DESCRIPTION CONTAINS
	  $descriptioncontains=$_GET['descriptioncontains'];
	  if($minDecl && $maxDecl && ($minDecl<$MaxDecl))
	  {
	    $minDeclError = True;
	    $maxDeclError = True;
	  }
	  if($minRA && $maxRA && ($minRA<$maxRA))
	  {
	    $minRAError = True;
	    $maxRAError = True;
	  }
	  if($maxMag && $minMag && ($maxMag<$minMag))
	  {
	    $maxMagError = True;
	    $minMagError = True;
	  }    
	  if($minSB && $maxSB && ($maxSB<$minSB))
	  {
	    $minSBError=True;
	    $maxSBError=True;
	  }
	  if($minSizeC && $maxSizeC && ($minSizeC>$maxSizeC))
	  {
	    $minSizeError=True;
	    $maxSizeError=True;
	  }
	  if($minContrast && $maxContrast && ($minContrast > $maxContrast))
	  {
	    $minContrastError=True;
	    $maxContrastError=True;
	  }
	  if($inList && $notInList && ($inList==$notInList))
	    $listError = True;
	  // Disable possibility to search for objects with a contrast reserve alone!!!!
	  if(
	     (
	      (int)!
	      (
	       (array_key_exists('con',$_GET) && ($_GET['con']!=""))                       ||
	       (array_key_exists('type',$_GET) && ($_GET['type']!=""))                     || 
	       (array_key_exists('catalog',$_GET) && ($_GET['catalog']!=""))               || 
	       (array_key_exists('catPageNumber',$_GET) && ($_GET['catPageNumber']!=""))   || 
	       (array_key_exists('minMag',$_GET) && ($_GET['minMag']!=""))                 || 
	       (array_key_exists('maxMag',$_GET) && ($_GET['maxMag']!=""))                 || 
	       (array_key_exists('maxSB',$_GET) && ($_GET['maxSB']!=""))                   || 
	       (array_key_exists('minSB',$_GET) && ($_GET['minSB']!=""))                   || 
	       (array_key_exists('minRAhours',$_GET) && ($_GET['minRAhours']!=""))         ||
	       (array_key_exists('minDeclDegrees',$_GET) && ($_GET['minDeclDegrees']!="")) || 
	       (array_key_exists('maxRAhours',$_GET) && ($_GET['maxRAhours']!=""))         || 
	       (array_key_exists('maxDeclDegrees',$_GET) && ($_GET['maxDeclDegrees']!="")) || 
	       (array_key_exists('minSize',$_GET) && ($_GET['minSize']!=""))               || 
	       (array_key_exists('maxSize',$_GET) && ($_GET["maxSize"]!=""))
	     )
	  	 )
	    && 
	     (
	   	(array_key_exists('maxContrast',$_GET) && ($_GET['maxContrast']!=""))        ||
	      (array_key_exists('minContrast',$_GET) && ($_GET['minContrast']!=""))
	  	 )
	   )
	  {
	    $maxContrastError = True;
	  	$minContrastError = True;
	  }
		reset($_GET);
		$excl=array();
		while(list($key,$value)=each($_GET))
		  if(substr($key,0,5)=='excl_')
		    $excl[]=substr($key,5);
	  $excludeexceptseen=$objUtil->checkGetKey('excludeexceptseen','off');
	  if(!($pageError || $minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError || 
	         $maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError || $minRAHoursError || 
	         $minRAMinutesError || $minRASecondsError || $maxRAHoursError || $maxRAMinutesError || 
	         $maxRASecondsError || $minMagError || $maxMagError || $minSBError || $maxSBError || 
	         $minSizeError || $maxSizeError || $minContrastError || $maxContrastError ||$listError))
	  { if(array_key_exists('seen',$_GET) && $_GET['seen'])
	        $seenPar = $_GET['seen'];
	      else
	        $seenPar = "A";
	  	$query = array("name"                  => $name,
	                     "type"                => $type,
	                     "con"                 => $con,             
	                     "conto"               => $conto,             
	                     "minmag"              => $minMag,
	                     "maxmag"              => $maxMag,
	                     "minsubr"             => $minSB,             
	                     "maxsubr"             => $maxSB,
	                     "minra"               => $minRA,   
	                     "maxra"               => $maxRA,
	                     "mindecl"             => $minDecl,
	                     "maxdecl"             => $maxDecl,
	                     "mindiam1"            => $minSizeC,
	                     "maxdiam1"            => $maxSizeC, 
	                     "minContrast"         => $minContrast,
	                     "maxContrast"         => $maxContrast,
	                     "inList"              => $inList,
	                     "notInList"           => $notInList,
	                     "atlas"               => $atlas,
											 "atlasPageNumber"     => $atlasPageNumber,
	                     "excl"                => $excl,
	                     "exclexceptseen"      => $excludeexceptseen,
	                     "descriptioncontains" => $descriptioncontains,
	                     "catalog"             => $objUtil->checkGetKey('catalog'),
	                     "catNumber"           => $objUtil->checkGetKey('catNumber'),
	  	                 "seen"                => $seenPar);
	    	$validQobj=false;
	      if(array_key_exists('QobjParams',$_SESSION)&&(count($_SESSION['QobjParams'])>1)&&array_key_exists('Qobj',$_SESSION)&&(count($_SESSION['Qobj'])>0))
	    	  $validQobj=true;
	    	while($validQobj&&(list($key,$value)=each($_SESSION['QobjParams'])))
	        if((!array_key_exists($key,$query))||($value!=$query[$key]))
	    	    $validQobj=false;	 
	     	while($validQobj&&(list($key,$value)=each($query)))
	        if((!array_key_exists($key,$_SESSION['QobjParams']))||($value!=$_SESSION['QobjParams'][$key]))
	    	    $validQobj=false;
	    	if($showPartOfs!=$objUtil->checkSessionKey('QobjPO',0))
	    	  $validQobj=false;
	    	if(!$validQobj)
	    	{ $_SESSION['QobjParams']=$query;
	    	  $_SESSION['QobjPO']=$showPartOfs;
	    	  $_SESSION['Qobj']= $objObject->getObjectFromQuery($query, $exact, $seenPar, $showPartOfs);
	    		$_SESSION['QobjSort']='name';
	    	  $_SESSION['QobjSortDirection']='asc';
					$min=0;
	    	}
	  }
	  else
	  { $_SESSION['QobjParams']=array();
	    $_SESSION['Qobj']=array();
	    $_SESSION['QobjSort']='';
	    $_SESSION['QobjSortDirection']='';
	  }	
	}
	elseif($objUtil->checkGetKey('source')=='quickpick')   //========================== from quickpick page
	{ $validQobj=false;
	  if(array_key_exists('QobjParams',$_SESSION)
	  && array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='quickpick')
	  && array_key_exists('object',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['object']==$objUtil->checkGetKey('object')))
		  $validQobj=true;
	  if($showPartOfs!=$objUtil->checkSessionKey('QobjPO',0))
	    $validQobj=false;
		if(!$validQobj)
		{ if(!$objUtil->checkGetKey('object'))
			{ //$_SESSION['QobjParams']=array();
	    	$_SESSION['QobjPO']=$showPartOfs;
			  $_SESSION['Qobj']=array();
			  $_SESSION['QobjSort']='';
			  $_SESSION['QobjSortDirection']='';
			}
			else
			{	$_SESSION['QobjParams']=array('source'=>'quickpick','object'=>$objUtil->checkGetKey('object'));
	    	$_SESSION['QobjPO']=$showPartOfs;
			  $_SESSION['Qobj']=$objObject->getObjectFromQuery(array('name'=>$objUtil->checkGetKey('object')),1,"A",$showPartOfs);
		    $_SESSION['QobjSort']='';
		    $_SESSION['QobjSortDirection']='asc';
			}
		} 
	}
	elseif($objUtil->checkGetKey('source')=='add_object10')   //========================== from quickpick page
	{ $validQobj=false;
	  $catandindex=$objCatalog->checkObject($objUtil->checkRequestKey('catalog').' '.$objUtil->checkRequestKey('number'));
	  $firstspace=strpos($catandindex,' ',0);
	  if($firstspace!==FALSE)
	  { $thenewcatalog0=trim(substr($catandindex,0,$firstspace));
	    $theindex=trim(substr($catandindex,$firstspace+1));
	  }
	  else
	  { $thenewcatalog0=$catandindex;
	    $theindex='';
	  }
	  $_REQUEST['catalog']=$thenewcatalog0;
	  $_REQUEST['number']=$theindex;
	  $_GET['object']="%".$_REQUEST['catalog']."%".str_replace(" ","%",$_REQUEST['number'])."%";
	  if(array_key_exists('QobjParams',$_SESSION)
	  && array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='add_object')
	  && array_key_exists('object',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['object']==$objUtil->checkGetKey('object')))
		  $validQobj=true;
	  if($showPartOfs!=$objUtil->checkSessionKey('QobjPO',0))
	    $validQobj=false;
		if(!$validQobj)
		{ if(!$objUtil->checkGetKey('object'))
			{ $_SESSION['QobjParams']=array();
	    	$_SESSION['QobjPO']=$showPartOfs;
			  $_SESSION['Qobj']=array();
			  $_SESSION['QobjSort']='';
			  $_SESSION['QobjSortDirection']='';
			  
			}
			else
			{	$_SESSION['QobjParams']=array('source'=>'add_object','object'=>$objUtil->checkGetKey('object'));
	    	$_SESSION['QobjPO']=$showPartOfs;
			  $_SESSION['Qobj']=$objObject->getObjectFromQuery(array('name'=>$objUtil->checkGetKey('object')),1,"A",$showPartOfs);
		    $_SESSION['QobjSort']='';
		    $_SESSION['QobjSortDirection']='asc';
			}
		} 
	}
	elseif($objUtil->checkGetKey('source')=='add_object20')   //========================== from quickpick page
	{ $catandindex=$objCatalog->checkObject($objUtil->checkRequestKey('catalog').' '.$objUtil->checkRequestKey('number'));
	  $firstspace=strpos($catandindex,' ',0);
	  if($firstspace!==FALSE)
	  { $thenewcatalog0=trim(substr($catandindex,0,$firstspace));
	    $theindex=trim(substr($catandindex,$firstspace+1));
	  }
	  else
	  { $thenewcatalog0=$catandindex;
	    $theindex='';
	  }
	  $_REQUEST['catalog']=$thenewcatalog0;
	  $_REQUEST['number']=$theindex;
	  $_SESSION['QobjParams']=array('source'=>'add_object20');
	  $ra=(abs($objUtil->checkRequestKey('RAhours'))+($objUtil->checkRequestKey('RAminutes')/60)+($objUtil->checkRequestKey('RAseconds')/3600));
	  $decl=((substr(trim($objUtil->checkRequestKey('DeclDegrees')),0,1)=='-')?-1:1)*(abs($objUtil->checkRequestKey('DeclDegrees'))+($objUtil->checkRequestKey('DeclMinutes')/60)+($objUtil->checkRequestKey('DeclSeconds')/3600));
	  $_SESSION['Qobj']=$objObject->getSeenObjectDetails($objObject->getNearbyObjects("",60,$ra,$decl));
		$_SESSION['QobjSort']='objectname';
		$_SESSION['QobjSortDirection']='asc';
	}
	// ========================================= no search specified
	else
	{ $_SESSION['QobjParams']=array();
	  $_SESSION['Qobj']=array();
	  $_SESSION['QobjSort']='';
	  $_SESSION['QobjSortDirection']='';
	}
	
	//=========================================== CHECK TO SEE IF SORTING IS NECESSARY ===========================================
	if(!array_key_exists('sort',$_GET))      
	{ if(!$objUtil->checkGetKey('sort'))
	    $_GET['sort']=$_SESSION['QobjSort'];
	  if(!$objUtil->checkGetKey('sort'))
	    $_GET['sort']='showname';
		$_GET['sortdirection']=$_SESSION['QobjSortDirection'];
	}
	if(!array_key_exists('sortdirection',$_GET))
		$_GET['sortdirection']=$_SESSION['QobjSortDirection'];
	if($_SESSION['QobjSort']!=$_GET['sort'])
	{ if($_GET['sortdirection']=='desc')
	  { if(count($_SESSION['Qobj'])>1)
	    { while(list($key, $value)=each($_SESSION['Qobj']))
	        if($_GET['sort']=='objectcontrast')  
	          if($value[$_GET['sort']]=='-')
	            $sortarray['0 _'.($value['showname'])]=$value;
	          else
	            $sortarray[($value[$_GET['sort']]*100+1000).' _'.($value['showname'])]=$value;
	        else 
	          if($_GET['sort']=='objectdecl')
	            $sortarray[100+(1*$value[$_GET['sort']]).' _'.($value['showname'])]=$value;
	          else
	            $sortarray[($value[$_GET['sort']]).' _'.($value['showname'])]=$value;
	      uksort($sortarray,"strnatcasecmp");
				$sortarray=array_reverse($sortarray);
				$_SESSION['Qobj']=array_values($sortarray);
	    }
		  $_SESSION['QobjSort']=$_GET['sort'];
		  $_SESSION['QobjSortDirection']='desc';
			$min=0;
	  }
	  else
	  { if(count($_SESSION['Qobj'])>1)
	    { while(list($key, $value)=each($_SESSION['Qobj']))
	        if($_GET['sort']=='objectcontrast')  
	          if($value[$_GET['sort']]=='-')
	            $sortarray['0 _'.($value['showname'])]=$value;
	          else
	            $sortarray[($value[$_GET['sort']]*100+1000).' _'.($value['showname'])]=$value;
	        else 
	          if($_GET['sort']=='objectdecl')
	            $sortarray[100+(1*$value[$_GET['sort']]).' _'.($value['showname'])]=$value;
	          else
	            $sortarray[($value[$_GET['sort']]).' _'.($value['showname'])]=$value;            
	      uksort($sortarray,"strnatcasecmp");
	      $_SESSION['Qobj']=array_values($sortarray);
		  }
		  $_SESSION['QobjSort']=$_GET['sort'];
		  $_SESSION['QobjSortDirection']='asc'; 
		  $min=0;
	  }
	}
	if($_SESSION['QobjSortDirection']!=$_GET['sortdirection'])
	{ if(count($_SESSION['Qobj'])>1)
	 	  $_SESSION['Qobj']=array_reverse($_SESSION['Qobj'],false);
	  $_SESSION['QobjSortDirection']=$_GET['sortdirection'];
		$min=0;
	}  
}


?>
