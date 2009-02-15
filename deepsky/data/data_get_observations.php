<?php
$MaxCnt=$objObservation->getMaxObservation();
//=========================================================================================== LOOKING FOR SPECIFIC OBJECT, OR LOOKING FOR SOME OTHER CHARACTERISTIC ============================================================
if(array_key_exists('number',$_GET) && $_GET['number'])
{ $objectarray = $objObject->getLikeDsObject("",$_GET['catalog'], $_GET['number']);
	if(count($objectarray)==1)
	  $object=$objectarray[0];
}
else 
  $object=$GLOBALS['objUtil']->checkGetKey('object');

//200811151634B: dit wordt momenteel niet verwerkt, kan er met CONVERT_TZ(dt,from_tz,to_tz) in het sql statement gewerkt worden?
$mindate=$objUtil->checkGetKey('mindate');
$maxdate=$objUtil->checkGetKey('maxdate');
if($loggedUser && (!($objObserver->getObserverProperty($loggedUser,'UT'))))
{ if ($mindate != "")
    $mindate = $mindate - 1;
  if ($maxdate != "")
    $maxdate = $maxdate + 1;
}
//200811151634End

$selectedLanguages=Array();
if($objUtil->checkGetKey('myLanguages','false')=='true')
  while(list($key,$value)=each($allLanguages))
    if(array_key_exists($key,$_GET)) 
      $selectedLanguages[]=$key;
if((!count($selectedLanguages))&&$objUtil->checkGetKey('myLanguages'))
{ reset($allLanguages);
  while(list($key,$value)=each($allLanguages))
    if(($loggedUser&&in_array($key,$usedLanguages))
    ||((!$loggedUser)&&($key==$_SESSION['lang'])))
      $selectedLanguages[]=$key;
}
$query = array("object"           => $object,
               "catalog"          => $GLOBALS['objUtil']->checkGetKey('catalog'),
               "number"           => $GLOBALS['objUtil']->checkGetKey('number'),
               "observer"         => $GLOBALS['objUtil']->checkGetKey('observer'),
               "instrument"       => $GLOBALS['objUtil']->checkGetKey('instrument'),
               "location"         => $GLOBALS['objUtil']->checkGetKey('site'),
               "mindate"          => $GLOBALS['objUtil']->checkGetDate('minyear','minmonth','minday'),
               "maxdate"          => $GLOBALS['objUtil']->checkGetDate('maxyear','maxmonth','maxday'), 
               "maxdiameter"      => ($GLOBALS['objUtil']->checkGetKey('maxdiameter')?($GLOBALS['objUtil']->checkGetKey('maxdiameterunits')=="inch"?$_GET['maxdiameter']*25.4:$_GET['maxdiameter']):''),
               "mindiameter"      => ($GLOBALS['objUtil']->checkGetKey('mindiameter')?($GLOBALS['objUtil']->checkGetKey('mindiameterunits')=="inch"?$_GET['mindiameter']*25.4:$_GET['mindiameter']):''),
               "type"             => $GLOBALS['objUtil']->checkGetKey('type'),
               "con"              => $GLOBALS['objUtil']->checkGetKey('con'),
               "maxmag"           => $GLOBALS['objUtil']->checkGetKey('maxmag'),
               "minmag"           => $GLOBALS['objUtil']->checkGetKey('minmag'),
               "maxsb"            => $GLOBALS['objUtil']->checkGetKey('maxsb'),
               "minsb"            => $GLOBALS['objUtil']->checkGetKey('minsb'),
               "maxdecl"          => $GLOBALS['objUtil']->checkGetTimeOrDegrees('maxDeclDegrees','maxDeclMinutes','maxDeclSeconds'),
               "mindecl"          => $GLOBALS['objUtil']->checkGetTimeOrDegrees('minDeclDegrees','minDeclMinutes','minDeclSeconds'),
						   "minLat"           => $GLOBALS['objUtil']->checkGetTimeOrDegrees('minLatDegrees','minLatMinutes','minLatSeconds'),
						   "maxLat"           => $GLOBALS['objUtil']->checkGetTimeOrDegrees('maxLatDegrees','maxLatMinutes','maxLatSeconds'),
               "atlas"            => $GLOBALS['objUtil']->checkGetKey('atlas'),
							 "atlasPageNumber"  => $GLOBALS['objUtil']->checkGetKey('atlasPageNumber'),
               "minra"            => $GLOBALS['objUtil']->checkGetTimeOrDegrees('minRAhours','minRAminutes','minRAseconds'),
               "maxra"            => $GLOBALS['objUtil']->checkGetTimeOrDegrees('maxRAhours','maxRAminutes','maxRAseconds'),
               "mindiam1"         => ($GLOBALS['objUtil']->checkGetKey('minsize')?($GLOBALS['objUtil']->checkGetKey('size_min_units')=="min"?$_GET['minsize']*60:$_GET['minsize']):''),
               "maxdiam1"         => ($GLOBALS['objUtil']->checkGetKey('maxsize')?($GLOBALS['objUtil']->checkGetKey('size_max_units')=="min"?$_GET['maxsize']*60:$_GET['maxsize']):''),
               "description"      => $GLOBALS['objUtil']->checkGetKey('description'),
               "minvisibility"    => $GLOBALS['objUtil']->checkGetKey('minvisibility'),
               "maxvisibility"    => $GLOBALS['objUtil']->checkGetKey('maxvisibility'),
               "minlimmag"        => $GLOBALS['objUtil']->checkGetKey('minlimmag'),
               "maxlimmag"        => $GLOBALS['objUtil']->checkGetKey('maxlimmag'),
               "minseeing"        => $GLOBALS['objUtil']->checkGetKey('minseeing'),
               "maxseeing"        => $GLOBALS['objUtil']->checkGetKey('maxseeing'),
							 "hasDrawing"       => $objUtil->checkGetKey('drawings','off'),            
               "languages"        => $selectedLanguages);
//============================================ CHECK TO SEE IF OBSERVATIONS ALREADY FETCHED BEFORE, OTHERWISE FETCH DATA FROM DB ===============================
$validQobs=false;
if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])>1)&&(count($_SESSION['Qobs'])>0)&&($_SESSION['QobsMaxCnt']==$MaxCnt))
 $validQobs=true;
while($validQobs && (list($key,$value) = each($_SESSION['QobsParams'])))
 if(!array_key_exists($key,$query)||($value!=$query[$key]))
   $validQobs=false;	 
while($validQobs && (list($key,$value) = each($query)))
 if(!array_key_exists($key,$_SESSION['QobsParams'])||($value!=$_SESSION['QobsParams'][$key]))
   $validQobs=false;

if(!$validQobs)
{ $_SESSION['Qobs']=$objObservation->getObservationFromQuery($query, $GLOBALS['objUtil']->checkGetKey('seen','D'));
  $_SESSION['QobsParams']=$query; 
  $_SESSION['QobsSort']='observationid';
  $_SESSION['QobsSortDirection']='desc';
  $query['countquery']='true';
  $_SESSION['QobsTotal']=$objObservation->getObservationFromQuery($query, $GLOBALS['objUtil']->checkGetKey('seen')); 
  $_SESSION['QobsMaxCnt']=$MaxCnt;
  $min=0;
	if($loggedUser && (!($objObserver->getObserverProperty($_SESSION['deepskylog_id'],'UT'))))
  { if(($mindate!="")||($maxdate!=""))
    { if($mindate!="")
        $mindate=$mindate + 1;
      if($maxdate!= "")
        $maxdate=$maxdate - 1;
      $newkey=0;
      $new_obs=Array();
      while(list($key, $value)=each($_SESSION['Qobs'])) // go through observations array
      { $newdate = $objObservation->getDsObservationLocalDate($value['observationid']);
        if ($mindate != "" && $maxdate != "") 
        { if (($newdate >= $mindate) && ($newdate <= $maxdate)) 
            $new_obs[] = $value;
        }
        else if ($maxdate != "") 
        { if ($newdate <= $maxdate)
            $new_obs[] = $value;
        }
        else if ($mindate != "")
          if ($newdate >= $mindate)
            $new_obs[] = $value;
      }
      $obs = $new_obs;
    }
  }
}

//=========================================== CHECK TO SEE IF SORTING IS NECESSARY ===========================================
if(!array_key_exists('sort',$_GET))      
{ $_GET['sort'] = $_SESSION['QobsSort'];
  $_GET['sortdirection']=$_SESSION['QobsSortDirection'];
}
if(!array_key_exists('sortdirection',$_GET))
	$_GET['sortdirection']=$_SESSION['QobsSortDirection'];
if($_SESSION['QobsSort']!=$_GET['sort'])
{ if($_GET['sortdirection']=='desc')
  { if(count($_SESSION['Qobs'])>1)
    { while(list($key, $value)=each($_SESSION['Qobs']))
	      $sortarray[$value[$_GET['sort']].'_'.(99999999-$value['observationid'])]=$value;
	    uksort($sortarray,"strnatcasecmp");
	    $_SESSION['Qobs']=array_values(array_reverse($sortarray,true));
    }
	  $_SESSION['QobsSort']=$_GET['sort'];
	  $_SESSION['QobsSortDirection']='desc';
		$min=0;
  }
  else
  { if(count($_SESSION['Qobs'])>1)
    { while(list($key, $value)=each($_SESSION['Qobs']))
	      $sortarray[$value[$_GET['sort']].'_'.(99999999-$value['observationid'])]=$value;
	    uksort($sortarray,"strnatcasecmp");
	    $_SESSION['Qobs']=array_values($sortarray);
	  }
	  $_SESSION['QobsSort']=$_GET['sort'];
	  $_SESSION['QobsSortDirection']='asc'; 
		$min=0;
  }
}
if($_SESSION['QobsSortDirection']!=$_GET['sortdirection'])
{ if(count($_SESSION['Qobs'])>1)
 	  $_SESSION['Qobs']=array_reverse($_SESSION['Qobs'],true);
  $_SESSION['QobsSortDirection']=$_GET['sortdirection'];
	$min=0;
}	
?>
