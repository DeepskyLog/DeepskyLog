<?php


if($includeFile=='deepsky/content/top_objects.php')
{ $_GET['source']='top_objects';
  require_once 'deepsky/data/data_get_objects.php';
}
if($includeFile=='deepsky/content/selected_observations2.php')
{  require_once 'deepsky/data/data_get_observations.php';
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
}   
if($includeFile='deepsky/content/tolisp.php')
{ $_GET['source']='tolist';
  require_once 'deepsky/data/data_get_objects.php';
}
if($includeFile=='deepsky/content/view_object.php')
{if(!$_GET['object']) // no object defined in url 
  throw new Exception("// no object defined in url, line 6 in view_object.php");
}
if(!($_GET['object']=$objObject->getDsObjectName($_GET['object'])))
{ throw new Exception("// no corresponding object found, line 8 in view_object.php");
  $_GET['source']='objects_nearby';
  $_GET['zoom']=$GLOBALS['objUtil']->checkGetKey('zoom',30);	
  include "deepsky/data/data_get_objects.php";	
}

?>