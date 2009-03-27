<?php
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{ if($includeFile=='deepsky/content/top_objects.php')
	{ $_GET['source']='top_objects';
	  require_once 'deepsky/data/data_get_objects.php';
	}
	if($includeFile=='deepsky/content/setup_objects_query.php')
	{ $pageError = false;       
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
	  require_once 'deepsky/data/data_get_objects.php'; 
	  if(array_key_exists('Qobj',$_SESSION)&&(count($_SESSION['Qobj'])>1))
	    $includeFile="deepsky/content/execute_query_objects.php";
	  elseif(count($_SESSION['Qobj'])==1) // ========================================= 1 object found
	  { $_GET['object']=$_SESSION['Qobj'][0]['objectname'];
	    $includeFile="deepsky/content/view_object.php";
	  }
	  else
	  { if($objUtil->checkGetKey('source')=='setup_objects_query')
	  	  $entryMessage=LangExecuteQueryObjectsMessage2;
	  }
	}   
	if($includeFile=='deepsky/content/tolist.php')
	{ $_GET['source']='tolist';
	  require_once 'deepsky/data/data_get_objects.php';
	}
	if($includeFile=='deepsky/content/view_object.php')
	{ if(!($objUtil->checkGetKey('object'))) 
	    throw new Exception(LangException016);
	  if(!($_GET['object']=$objObject->getDsObjectName($_GET['object'])))
	    throw new Exception(LangException016b);
	  $_GET['source']='objects_nearby';
	  $_GET['zoom']=$objUtil->checkGetKey('zoom',30);	
	  include "deepsky/data/data_get_objects.php";	
	}
	if($includeFile=='deepsky/content/selected_observations2.php')
	{  require_once 'deepsky/data/data_get_observations.php';
	}
}
?>