<?php
if(array_key_exists('source',$_GET)&&($_GET['source']=='observation_query'))
{	unset($_SESSION['QOP']);
	$validQobj=false;
  if(array_key_exists('QobjParams',$_SESSION)&&array_key_exists('source',$_SESSION['QobjParams'])&&($_SESSION['QobjParams']['source']=='observation_query'))
	  $validQobj=true;
	while($validQobj && (list($key,$value) = each($_SESSION['QobjParams'])))
	  if(array_key_exists($key,$_SESSION['QobsParams'])&&($value != $_SESSION['QobsParams'][$key]))
	    $validQobj=false;	 
  if(!$validQobj)
	{ $obj = $objObject->getSeenObjectDetails($objObservation->getObjectsFromObservations($_SESSION['Qobs']),'D');
    $_SESSION['QobjParams']=array('source'=>'observation_query',$_SESSION['QobsParams']);
	  $_SESSION['Qobj']=$obj;
		$_SESSION['QobjSort']='showname';
	  $_SESSION['QobjSortDirection']='asc';
	}
}
elseif(array_key_exists('seen',$_GET))
{
  $min=0;
  $previous = '';
  $prev = '';		
  
  $pageError = false;       
  $minDeclDegreesError = false;    $minDeclMinutesError = false;    $minDeclSecondsError = false;
  $maxDeclDegreesError = false;    $maxDeclMinutesError = false;    $maxDeclSecondsError = false;
  $minRAHoursError = false;        $minRAMinutesError = false;      $minRASecondsError = false;
  $maxRAHoursError = false;        $maxRAMinutesError = false;      $maxRASecondsError = false;
  $minMagError = false;            $maxMagError = false;               
  $minSBError = false;             $maxSBError = false;
  $minSizeError = false;           $maxSizeError = false;
  $minContrastError = false;       $maxContrastError = false; 
  $listError = false;
  
  $name = '';                                 $atlas = '';          $atlasPageNumber = '';
  $catalog = '';        $catNumber = '';
  $type = '';                                 $con = '';		
  $minDecl = '';        $minDeclDegrees = ''; $minDeclMinutes = ''; $minDeclSeconds = '';
  $maxDecl = '';        $maxDeclDegrees = ''; $maxDeclMinutes = ''; $maxDeclSeconds = '';
  $minRA = '';          $minRAHours = '';     $minRAMinutes = '';   $minRASeconds = '';
  $maxRA = '';          $maxRAHours = '';     $maxRAMinutes = '';   $maxRASeconds = '';
  $maxMag = '';       	                      $minMag = '';
  $maxSB = '';                                $minSB = '';
  $minSize = '';        $minSizeC = '';       $size_min_units = ''; 
  $maxSize = '';        $maxSizeC = '';       $size_max_units = ''; 
  $minContrast = '';                          $maxContrast = '';    
  $inList = '';                               $notInList = '';
  
  
  $min=0;   if(array_key_exists('min',$_GET) && $_GET['min'])  $min = $_GET['min'];
  // CATALOG AND / OR NUMBER
  $exact = 0;
  if(array_key_exists('catalog',$_GET) && $_GET['catalog']) $name = $_GET['catalog'];
  if(array_key_exists('catalog',$_GET)) $catalog = $_GET['catalog'];
  if(array_key_exists('catNumber',$_GET)) $catNumber = $_GET['catNumber'];
  if(array_key_exists('atlas',$_GET) && $_GET['atlas'])
    $atlas=$_GET['atlas'];
  elseif(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])
    $atlas=$objAtlas->atlasCodes[$objObserver->getStandardAtlasCode($_SESSION['deepskylog_id'])];
  if(array_key_exists('atlasPageNumber',$_GET)) $atlasPageNumber = $_GET['atlasPageNumber']; else $atlasPageNumber='';
  if(array_key_exists('inList', $_GET)) $inList = $_GET['inList']; else $inList = '';
  if(array_key_exists('notInList', $_GET)) $notInList = $_GET['notInList']; else $notInList = '';
  if(array_key_exists('size_min_units',$_GET)) $size_min_units=$_GET['size_min_units']; else $size_min_units='';
  if(array_key_exists('size_max_units',$_GET)) $size_max_units=$_GET['size_max_units']; else $size_max_units='';
  if(array_key_exists('catNumber',$_GET) && $_GET['catNumber'])
  {
    $name = ucwords(trim($name . " " . trim($_GET['catNumber'])));
    $exact = "1";
  }
  // ATLAS PAGE
  if(array_key_exists('atlasPageNumber',$_GET) && $_GET['atlasPageNumber'])
  {
    if(!is_numeric($_GET['atlasPageNumber']) || ($_GET['atlasPageNumber']<1) || ($_GET['atlasPageNumber']>5000))
      $pageError = true;
    else
      $atlasPageNumber = $_GET['atlasPageNumber'];
  }
  // CONSTELLATION
  if(array_key_exists('con',$_GET) && $_GET['con'])     $con = $_GET['con'];
  // TYPE
  if(array_key_exists('type',$_GET) && $_GET['type'])   $type = $_GET['type'];
  // MINIMUM DECLINATION
  if(array_key_exists('minDeclDegrees',$_GET) && $_GET['minDeclDegrees']!='') 
  {
    $minDeclDegrees = $_GET['minDeclDegrees'];
    if((!is_numeric($_GET['minDeclDegrees'])) || ($_GET['minDeclDegrees']<=-90) || ($_GET['minDeclDegrees']>=90))
      $minDeclDegreesError = True;
    if(array_key_exists('minDeclMinutes',$_GET) && $_GET['minDeclMinutes']!='') 
    {  
      $minDeclMinutes = $_GET['minDeclMinutes']; 
      if((!is_numeric($_GET['minDeclMinutes'])) || ($_GET['minDeclMinutes']<0) || ($_GET['minDeclMinutes']>=60))
        $minDeclMinutesError = true;
    }
    else
    {
      $minDeclMinutes = 0; 
      $_GET['minDeclMinutes']=0; 
    }
    if(array_key_exists('minDeclSeconds',$_GET) && $_GET['minDeclSeconds']!='') 
    {
      $minDeclSeconds = $_GET['minDeclSeconds']; 
      if((!is_numeric($_GET['minDeclSeconds'])) || ($_GET['minDeclSeconds']<0) || ($_GET['minDeclSeconds']>=60))
        $minDeclSecondsError = true;
    }
    else
    {
      $minDeclSeconds = 0;
      $_GET['minDeclSeconds'] = 0;
    }
    if($minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError)
      $errorQuery = true;
    else
      if(substr(trim($_GET['minDeclDegrees']),1,1)=="-")
        $minDecl = $minDeclDegrees - ($_GET['minDeclMinutes'] / 60) - ($_GET['minDeclSeconds'] / 3600);
      else 
        $minDecl = $minDeclDegrees + ($_GET['minDeclMinutes'] / 60) + ($_GET['minDeclSeconds'] / 3600);
  }
  // MAXIMUM DECLINATION 
  if(array_key_exists('maxDeclDegrees',$_GET) && $_GET['maxDeclDegrees']!='') 
  {
    $maxDeclDegrees = $_GET['maxDeclDegrees'];
    if((!is_numeric($_GET['maxDeclDegrees'])) || ($_GET['maxDeclDegrees']<=-90) || ($_GET['maxDeclDegrees']>=90))
      $maxDeclDegreesError = true;
    if(array_key_exists('maxDeclMinutes',$_GET) && $_GET['maxDeclMinutes']!='') 
    {  
      $maxDeclMinutes = $_GET['maxDeclMinutes']; 
      if((!is_numeric($_GET['maxDeclMinutes'])) || ($_GET['maxDeclMinutes']<0) || ($_GET['maxDeclMinutes']>=60))
        $maxDeclMinutesError = true;
    }
    else
    {
      $maxDeclMinutes = 0; 
      $_GET['maxDeclMinutes']=0; 
    }
    if(array_key_exists('maxDeclseconds',$_GET) && $_GET['maxDeclseconds']!='') 
    {
      $maxDeclSeconds = $_GET['maxDeclSeconds']; 
      if((!is_numeric($_GET['maxDeclSeconds'])) || ($_GET['maxDeclSeconds']<0) || ($_GET['maxDeclSeconds']>=60))
        $maxDeclSecondsError = true;
    }
    else
    {
      $maxDeclseconds = 0;
      $_GET['maxDeclSeconds'] = 0;
    }
    if($maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError)
      $errorQuery = true;
    else
      if(substr(trim($_GET['maxDeclDegrees']),1,1)=="-")
        $maxDecl = $maxDeclDegrees - ($_GET['maxDeclMinutes'] / 60) - ($_GET['maxDeclSeconds'] / 3600);
      else 
        $maxDecl = $maxDeclDegrees + ($_GET['maxDeclMinutes'] / 60) + ($_GET['maxDeclSeconds'] / 3600);
  } 
  // MIN RA
  if(array_key_exists('minRAHours',$_GET) && $_GET['minRAHours']!='') 
  {
    $minRAHours = $_GET['minRAHours'];
    if((!is_numeric($_GET['minRAHours'])) || ($_GET['minRAHours']<0) || ($_GET['minRAHours']>24))
    {  $minRAHoursError = True;
  echo 'MinRAHours: ' .$_GET['minRAHours'];
    
  	}if(array_key_exists('minRAMinutes',$_GET) && $_GET['minRAMinutes']!='') 
    {  
      $minRAMinutes = $_GET['minRAMinutes']; 
      if((!is_numeric($_GET['minRAMinutes'])) || ($_GET['minRAMinutes']<0) || ($_GET['minRAMinutes']>=60))
        $minRAMinutesError = true;
    }
    else
    {
      $minRAMinutes = 0; 
      $_GET['minRAMinutes']=0; 
    }
    if(array_key_exists('minRASeconds',$_GET) && $_GET['minRASeconds']!='') 
    {
      if((!is_numeric($_GET['minRASeconds'])) || ($_GET['minRASeconds']<0) || ($_GET['minRASeconds']>=60))
        $minRASecondsError = true;
      else
        $minRASeconds = $_GET['minRASeconds']; 
    }
    else
    {
      $minRASeconds = 0;
      $_GET['minRASeconds'] = 0;
    }
    if(!($minRAHoursError || $minRAMinutesError || $minRASecondsError))
      $minRA = $minRAHours + ($_GET['minRAMinutes'] / 60) + ($_GET['minRASeconds'] / 3600);
  }
  // MAX RA
  if(array_key_exists('maxRAHours',$_GET) && $_GET['maxRAHours']!='') 
  {
    $maxRAHours = $_GET['maxRAHours'];
    if((!is_numeric($_GET['maxRAHours'])) || ($_GET['maxRAHours']<0) || ($_GET['maxRAHours']>24))
      $maxRAHoursError = True;
    if(array_key_exists('maxRAMinutes',$_GET) && $_GET['maxRAMinutes']!='') 
    {  
      $maxRAMinutes = $_GET['maxRAMinutes']; 
      if((!is_numeric($_GET['maxRAMinutes'])) || ($_GET['maxRAMinutes']<0) || ($_GET['maxRAMinutes']>=60))
        $maxRAMinutesError = true;
    }
    else
    {
      $maxRAMinutes = 0; 
      $_GET['maxRAMinutes']=0; 
    }
    if(array_key_exists('maxRASeconds',$_GET) && $_GET['maxRASeconds']!='') 
    {
      $maxRASeconds = $_GET['maxRASeconds']; 
      if((!is_numeric($_GET['maxRASeconds'])) || ($_GET['maxRASeconds']<0) || ($_GET['maxRASeconds']>=60))
        $maxRASecondsError = true;
    }
    else
    {
      $maxRASeconds = 0;
      $_GET['maxRASeconds'] = 0;
    }
    if(!($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError))
      $maxRA = $maxRAHours + ($_GET['maxRAMinutes'] / 60) + ($_GET['maxRASeconds'] / 3600);
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
  if(array_key_exists('minSize',$_GET) && ($_GET['minSize']!=''))
  { if((!is_numeric($_GET['minSize'])) || ($_GET['minSize']<0))
      $minSizeError=True; 
    if(array_key_exists('size_min_units', $_GET) && ($_GET['size_min_units'] == "sec"))
    {
      $size_min_units = 'sec';
      $minSize = $_GET['minSize'];
      $minSizeC = $_GET['minSize'];
    }
    else
    {
      $size_min_units = 'min';
      $minSize = $_GET['minSize'];
      $minSizeC = $_GET['minSize'] * 60;
    }
  }
  // MAXIMUM SIZE
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
  if(!($pageError || $minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError || 
         $maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError || $minRAHoursError || 
         $minRAMinutesError || $minRASecondsError || $maxRAHoursError || $maxRAMinutesError || 
         $maxRASecondsError || $minMagError || $maxMagError || $minSBError || $maxSBError || 
         $minSizeError || $maxSizeError || $minContrastError || $maxContrastError ||$listError))
  {
      $query = array("name"          => $name,
                     "type"          => $type,
                     "constellation"   => $con,             
                     "minmag"          => $minMag,
                     "maxmag"          => $maxMag,
                     "minsubr"         => $minSB,             
                     "maxsubr"         => $maxSB,
                     "minra"           => $minRA,   
                     "maxra"           => $maxRA,
                     "mindecl"         => $minDecl,
                     "maxdecl"         => $maxDecl,
                     "mindiam1"        => $minSizeC,
                     "maxdiam1"        => $maxSizeC, 
                     "minContrast"     => $minContrast,
                     "maxContrast"     => $maxContrast,
                     "inList"          => $inList,
                     "notInList"       => $notInList,
                     "atlas"           => $atlas,
										 "atlasPageNumber" => $atlasPageNumber);
      if(array_key_exists('seen',$_GET) && $_GET['seen'])
        $seenPar = $_GET['seen'];
      else
        $seenPar = "D";
			
    	$validQobj=false;
      if(array_key_exists('QobjParams',$_SESSION))
    	  $validQobj=true;
    	while($validQobj && (list($key,$value) = each($_SESSION['QobjParams'])))
    	  if(array_key_exists($key,$query)&&($value!=$query[$key]))
    	    $validQobj=false;	 
      if(!$validQobj)
    	{ $_SESSION['QobjParams']=$query;
    	  $_SESSION['Qobj']= $objObject->getObjectFromQuery($query, $exact, $seenPar);
    		$_SESSION['QobjSort']='showname';
    	  $_SESSION['QobjSortDirection']='asc';
    	}
			unset($_SESSION['QOP']);
  }
  elseif(array_key_exists('QO',$_SESSION))
  	unset($_SESSION['QO']);
}
?>
