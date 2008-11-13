<?php

$object='';
$cataloguesearch = ''; 
$objectarray=array();
if(array_key_exists('object', $_GET) && ($_GET['object']))
  $object = $_GET['object'];
elseif(array_key_exists('number',$_GET) && $_GET['number'])
{ $objectarray = $objObject->getLikeDsObject("",$_GET['catalogue'], $_GET['number']);
	if(count($objectarray)==1)
	  $object=$objectarray[0];
}
//else
//  $cataloguesearch=true;
$theDate = date('Ymd', strtotime('-1 year')) ;



//=========================================================================================== LOOKING FOR SPECIFIC OBJECT, OR LOOKING FOR SOME OTHER CHARACTERISTIC ============================================================
if($object ||
       (array_key_exists('catalogue',$_GET) && $_GET['catalogue']) || 
       (array_key_exists('number',$_GET) && $_GET['number']) || 
       (array_key_exists('instrument',$_GET) && $_GET['instrument']) || 
			 (array_key_exists('site',$_GET) && $_GET['site']) || 
			 (array_key_exists('minyear',$_GET) && $_GET['minyear']) || 
       (array_key_exists('maxyear',$_GET) && $_GET['maxyear']) || 
			 (array_key_exists('minmonth',$_GET) && $_GET['minmonth']) || 
       (array_key_exists('maxmonth',$_GET) && $_GET['maxmonth']) || 
       ((array_key_exists('mindiameter',$_GET) && array_key_exists('mindiameterunits',$_GET) 
			    && $_GET['mindiameter']!="" && $_GET['mindiameterunits'])) || 
	     ((array_key_exists('maxdiameter',$_GET) && array_key_exists('maxdiameterunits',$_GET)
			    && $_GET['maxdiameter']!="" && $_GET['maxdiameterunits'])) || 
	     (array_key_exists('con',$_GET) && $_GET['con']) || 
	     (array_key_exists('type',$_GET) && $_GET['type']) || 
	     (array_key_exists('observer',$_GET) && $_GET['observer']) || 
	     (array_key_exists('minmag',$_GET) && $_GET['minmag']!="") || 
	     (array_key_exists('maxmag',$_GET) && $_GET['maxmag']!="") || 
	     (array_key_exists('maxsb',$_GET) && $_GET['maxsb']!="") || 
	     (array_key_exists('minsb',$_GET) && $_GET['minsb']!="") || 
	     ((array_key_exists('minRAhours',$_GET) && $_GET['minRAhours']!="") && 
	      (array_key_exists('minRAminutes',$_GET) && $_GET['minRAminutes']!="") && 
	      (array_key_exists('minRAseconds',$_GET) && $_GET['minRAseconds']!="")) || 
	     ((array_key_exists('minDeclDegrees',$_GET) && $_GET['minDeclDegrees']!="") && 
	      (array_key_exists('minDeclMinutes',$_GET) && $_GET['minDeclMinutes']!="") && 
	      (array_key_exists('minDeclSeconds',$_GET) && $_GET['minDeclSeconds']!="")) || 
	     ((array_key_exists('minLatDegrees',$_GET) && $_GET['minLatDegrees']!="") && 
	      (array_key_exists('minLatMinutes',$_GET) && $_GET['minLatMinutes']!="") && 
	      (array_key_exists('minLatSeconds',$_GET) && $_GET['minLatSeconds']!="")) || 
	     ((array_key_exists('maxRAhours',$_GET) && $_GET['maxRAhours']!="") && 
	      (array_key_exists('maxRAminutes',$_GET) && $_GET['maxRAminutes']!="") && 
	      (array_key_exists('maxRAseconds',$_GET) && $_GET['maxRAseconds']!="")) || 
	     ((array_key_exists('maxDeclDegrees',$_GET) && $_GET['maxDeclDegrees']!="") && 
	      (array_key_exists('maxDeclMinutes',$_GET) && $_GET['maxDeclMinutes']!="") && 
	      (array_key_exists('maxDeclSeconds',$_GET) && $_GET['maxDeclSeconds']!="")) || 
	     ((array_key_exists('maxLatDegrees',$_GET) && $_GET['maxLatDegrees']!="") && 
	      (array_key_exists('maxLatMinutes',$_GET) && $_GET['maxLatMinutes']!="") && 
	      (array_key_exists('maxLatSeconds',$_GET) && $_GET['maxLatSeconds']!="")) || 
	     ((array_key_exists('atlas',$_GET) && $_GET['atlas'] && 
	       array_key_exists('page',$_GET) && $_GET['page'])) || 
	     ((array_key_exists('minsize',$_GET) && array_key_exists('size_min_units',$_GET)
			    && $_GET['minsize']!=""  && $_GET['size_min_units'])) || 
	     ((array_key_exists('maxsize',$_GET) && array_key_exists('size_max_units',$_GET)
			    && $_GET['maxsize']!=""  && $_GET['size_max_units'])) || 
	     (array_key_exists('description',$_GET) && $_GET['description']) ||
			 (array_key_exists('minvisibility',$_GET) && $_GET['minvisibility']!="") || 
	     (array_key_exists('maxvisibility',$_GET) && $_GET['maxvisibility']!="") || 
	     (array_key_exists('minlimmag',$_GET) && $_GET['minlimmag']!="") || 
	     (array_key_exists('maxlimmag',$_GET) && $_GET['maxlimmag']!="") || 
	     (array_key_exists('minseeing',$_GET) && $_GET['minseeing']!="") || 
	     (array_key_exists('maxseeing',$_GET) && $_GET['maxseeing']!="")) // at least 1 field to search on 
{ $catalogue = '';
  if(!$object && array_key_exists('catalogue',$_GET) && ($_GET['catalogue']))
  { $catalogue = $_GET['catalogue']; 
  }  
  if(array_key_exists('number',$_GET) && ($_GET['number']!=''))
	  $number = $_GET['number'];
  if(array_key_exists('observer',$_GET))	    
	  $observer = $_GET['observer'];
	else 
	  $observer = '';
  if(array_key_exists('number',$_GET))       
	  $number = $_GET['number'];
	else 
	  $number = '';
	if(array_key_exists('minyear',$_GET) && array_key_exists('minmonth',$_GET) && array_key_exists('minday',$_GET) && $_GET['minyear'] && $_GET['minmonth'] && $_GET['minday']) // exact date given
  { $minyear = $_GET['minyear'];
	  $minmonth = $_GET['minmonth'];
	  $minday = $_GET['minday'];
	  $mindate = $minyear . sprintf("%02d",$minmonth) . sprintf("%02d",$minday);
  }
  elseif(array_key_exists('minyear',$_GET) && array_key_exists('minmonth',$_GET) && $_GET['minyear'] && $_GET['minmonth']) // month and year given
  { $minyear = $_GET['minyear'];
	  $minmonth = $_GET['minmonth'];
	  $minday = '';
    $mindate = $minyear . sprintf("%02d",$minmonth) . "00";
  }
  elseif(array_key_exists('minyear',$_GET) && $_GET['minyear']) // only year given
  { $minyear = $_GET['minyear'];
	  $minmonth = '';
	  $minday = '';
    $mindate = $minyear . "0000";
  }
  elseif(array_key_exists('minmonth',$_GET) && $_GET['minmonth'] && array_key_exists('minday',$_GET) && $_GET['minday'])
  { $minyear = '';
	  $minmonth = $_GET['minmonth'];
	  $minday = $_GET['minday'];
	   $mindate = sprintf("%02d",$_GET['minmonth']) . sprintf("%02d",$_GET['minday']);
  }
	elseif(array_key_exists('minmonth',$_GET) && $_GET['minmonth'])
  { $minyear = '';
	  $minmonth = $_GET['minmonth'];
		$minday = '';
	  $mindate = sprintf("%02d",$_GET['minmonth']) . "00";
  }
	else
	{ $minyear = '';
	  $minmonth = '';
		$minday = '';
    $mindate = '';
	}	  
  if(array_key_exists('maxyear',$_GET) && array_key_exists('maxmonth',$_GET) && array_key_exists('maxday',$_GET) && $_GET['maxyear'] && $_GET['maxmonth'] && $_GET['maxday']) // exact date given
  { $maxyear = $_GET['maxyear'];
	  $maxmonth = $_GET['maxmonth'];
		$maxday = $_GET['maxday'];
    $maxdate = $_GET['maxyear'] . sprintf("%02d",$_GET['maxmonth']) . sprintf("%02d",$_GET['maxday']);
  }
  elseif(array_key_exists('maxyear',$_GET) && array_key_exists('maxmonth',$_GET) && $_GET['maxyear'] && $_GET['maxmonth']) // month and year given 
  { $maxyear = $_GET['maxyear'];
	  $maxmonth = $_GET['maxmonth'];
		$maxday = '';
    $maxdate = $_GET['maxyear'] . sprintf("%02d",$_GET['maxmonth']) . "31";
  }
  elseif(array_key_exists('maxyear',$_GET) && $_GET['maxyear']) // only year given
  { $maxyear = $_GET['maxyear'];
	  $maxmonth = '';
		$maxday = '';
    $maxdate = $_GET['maxyear'] . "1231";
  }
  elseif(array_key_exists('maxmonth',$_GET) && $_GET['maxmonth'] && array_key_exists('maxday',$_GET) && $_GET['maxday'])
  { $maxyear = '';
	  $maxmonth = $_GET['maxmonth'];
		$maxday = $_GET['maxday'];
	  $maxdate = sprintf("%02d",$_GET['maxmonth']) . sprintf("%02d",$_GET['maxday']);
  }
	elseif(array_key_exists('maxmonth',$_GET) && $_GET['maxmonth'])
  { $maxyear = '';
	  $maxmonth = $_GET['maxmonth'];
		$maxday = '';
	  $maxdate = sprintf("%02d",$_GET['maxmonth']) . "31";
  }
  else
	{ $maxyear = '';
	  $maxmonth = '';
		$maxday = '';
    $maxdate = '';
	}
  if(array_key_exists('mindiameter',$_GET))
  { if (array_key_exists('mindiameterunits',$_GET) && ($_GET['mindiameterunits'] == "inch")) // convert minimum diameter in inches to mm 
    {  $mindiameter = $_GET['mindiameter'] * 25.4;
  		 $mindiameterunits = $_GET['mindiameterunits'];
    }
    else
    { $mindiameter = $_GET['mindiameter'];
		  $mindiameterunits = '';
    }
  }
	else
	{ $mindiameter = '';
	  $mindiameterunits = '';
	}
  if(array_key_exists('maxdiameter',$_GET))
  { if (array_key_exists('maxdiameterunits', $_GET) && ($_GET['maxdiameterunits'] == "inch")) // convert maximum diameter in inches to mm
    { $maxdiameter = $_GET['maxdiameter'] * 25.4;
	    $maxdiameterunits = $_GET['maxdiameterunits'];
    }
    else
    { $maxdiameter = $_GET['maxdiameter'];
	    $maxdiameterunits = $_GET['maxdiameterunits'];
    }
  }
	else
	{ $maxdiameter = '';
	  $maxdiameterunits = '';
	}
  if(array_key_exists('type',$_GET))         $type = $_GET['type']; else $type = '';
	if(array_key_exists('con',$_GET))          $con = $_GET['con']; else $con = '';
	if(array_key_exists('maxmag',$_GET))       $maxmag = $_GET['maxmag']; else $maxmag = '';
	if(array_key_exists('minmag',$_GET))       $minmag = $_GET['minmag']; else $minmag = '';
	if(array_key_exists('maxsb',$_GET))        $maxsb = $_GET['maxsb']; else $maxsb = '';
	if(array_key_exists('minsb',$_GET))        $minsb = $_GET['minsb']; else $minsb = '';
	if(array_key_exists('description',$_GET))  $description = $_GET['description']; else $description = '';
  // MINIMUM DECLINATION
  if(array_key_exists('minDeclDegrees',$_GET) && array_key_exists('minDeclMinutes',$_GET) && array_key_exists('minDeclSeconds',$_GET) && ($_GET['minDeclDegrees'] < 0 || strcmp($_GET['minDeclDegrees'], "-0") == 0))
  { $minDeclDegrees = $_GET['minDeclDegrees'];
 	  $minDeclMinutes = $_GET['minDeclMinutes'];
	  $minDeclSeconds = $_GET['minDeclSeconds'];
    $mindecl = $_GET['minDeclDegrees'] - ($_GET['minDeclMinutes'] / 60) - ($_GET['minDeclSeconds'] / 3600);
  }
  elseif(array_key_exists('minDeclDegrees',$_GET) && array_key_exists('minDeclMinutes',$_GET) && array_key_exists('minDeclSeconds',$_GET) &&  ($_GET['minDeclDegrees'] > 0))
  { $minDeclDegrees = $_GET['minDeclDegrees'];
	  $minDeclMinutes = $_GET['minDeclMinutes'];
		$minDeclSeconds = $_GET['minDeclSeconds'];
    $mindecl = $_GET['minDeclDegrees'] + ($_GET['minDeclMinutes'] / 60) + ($_GET['minDeclSeconds'] / 3600);
  }
  elseif(array_key_exists('minDeclDegrees',$_GET) && array_key_exists('minDeclMinutes',$_GET) && array_key_exists('minDeclSeconds',$_GET) && ($_GET['minDeclDegrees'] == "0"))
  { $minDeclDegrees = $_GET['minDeclDegrees'];
	  $minDeclMinutes = $_GET['minDeclMinutes'];
		$minDeclSeconds = $_GET['minDeclSeconds'];
    $mindecl = 0 + ($_GET['minDeclMinutes'] / 60) + ($_GET['minDeclSeconds'] / 3600);
  }
  else
  { $minDeclDegrees = '';
	  $minDeclMinutes = '';
		$minDeclSeconds = '';
    $mindecl = '';
  }
  // MINIMUM Latitude
  if(array_key_exists('minLatDegrees',$_GET) && array_key_exists('minLatMinutes',$_GET) && array_key_exists('minLatSeconds',$_GET) && ($_GET['minLatDegrees'] < 0 || strcmp($_GET['minLatDegrees'], "-0") == 0))
  { $minLatDegrees = $_GET['minLatDegrees'];
	  $minLatMinutes = $_GET['minLatMinutes'];
		$minLatSeconds = $_GET['minLatSeconds'];
    $minLat = $_GET['minLatDegrees'] - ($_GET['minLatMinutes'] / 60) - ($_GET['minLatSeconds'] / 3600);
  }
  elseif(array_key_exists('minLatDegrees',$_GET) && array_key_exists('minLatMinutes',$_GET) && array_key_exists('minLatSeconds',$_GET) &&  ($_GET['minLatDegrees'] > 0))
  { $minLatDegrees = $_GET['minLatDegrees'];
	  $minLatMinutes = $_GET['minLatMinutes'];
		$minLatSeconds = $_GET['minLatSeconds'];
    $minLat = $_GET['minLatDegrees'] + ($_GET['minLatMinutes'] / 60) + ($_GET['minLatSeconds'] / 3600);
  }
  elseif(array_key_exists('minLatDegrees',$_GET) && array_key_exists('minLatMinutes',$_GET) && array_key_exists('minLatSeconds',$_GET) && ($_GET['minLatDegrees'] == "0"))
  { $minLatDegrees = $_GET['minLatDegrees'];
 	  $minLatMinutes = $_GET['minLatMinutes'];
	  $minLatSeconds = $_GET['minLatSeconds'];
    $minLat = 0 + ($_GET['minLatMinutes'] / 60) + ($_GET['minLatSeconds'] / 3600);
  }
  else
  { $minLatDegrees = '';
	  $minLatMinutes = '';
		$minLatSeconds = '';
    $minLat = '';
  }
  // MAXIMUM DECLINATION
  if(array_key_exists('maxDeclDegrees',$_GET) && array_key_exists('maxDeclMinutes',$_GET) && array_key_exists('maxDeclSeconds',$_GET) && ($_GET['maxDeclDegrees'] < 0 || strcmp($_GET['maxDeclDegrees'], "-0") == 0))
  { $maxDeclDegrees = $_GET['maxDeclDegrees'];
		$maxDeclMinutes = $_GET['maxDeclMinutes'];
		$maxDeclSeconds = $_GET['maxDeclSeconds'];
    $maxdecl = $_GET['maxDeclDegrees'] - ($_GET['maxDeclMinutes'] / 60) - ($_GET['maxDeclSeconds'] / 3600);
  }
  elseif(array_key_exists('maxDeclDegrees',$_GET) && array_key_exists('maxDeclMinutes',$_GET) && array_key_exists('maxDeclSeconds',$_GET) && ($_GET['maxDeclDegrees'] > 0))
  { $maxDeclDegrees = $_GET['maxDeclDegrees'];
		$maxDeclMinutes = $_GET['maxDeclMinutes'];
		$maxDeclSeconds = $_GET['maxDeclSeconds'];
    $maxdecl = $_GET['maxDeclDegrees'] + ($_GET['maxDeclMinutes'] / 60) + ($_GET['maxDeclSeconds'] / 3600);
  }
  elseif(array_key_exists('maxDeclDegrees',$_GET) && array_key_exists('maxDeclMinutes',$_GET) && array_key_exists('maxDeclSeconds',$_GET) && ($_GET['maxDeclDegrees'] == "0"))
  { $maxDeclDegrees = $_GET['maxDeclDegrees'];
		$maxDeclMinutes = $_GET['maxDeclMinutes'];
		$maxDeclSeconds = $_GET['maxDeclSeconds'];
    $maxdecl = 0 + ($_GET['maxDeclMinutes'] / 60) + ($_GET['maxDeclSeconds'] / 3600);
  }
  else
  { $maxDeclDegrees = '';
		$maxDeclMinutes = '';
		$maxDeclSeconds = '';
    $maxdecl = "";
  }
  // MAXIMUM Latitude
  if(array_key_exists('maxLatDegrees',$_GET) && array_key_exists('maxLatMinutes',$_GET) && array_key_exists('maxLatSeconds',$_GET) && ($_GET['maxLatDegrees'] < 0 || strcmp($_GET['maxLatDegrees'], "-0") == 0))
  { $maxLatDegrees = $_GET['maxLatDegrees'];
		$maxLatMinutes = $_GET['maxLatMinutes'];
		$maxLatSeconds = $_GET['maxLatSeconds'];
    $maxLat = $_GET['maxLatDegrees'] - ($_GET['maxLatMinutes'] / 60) - ($_GET['maxLatSeconds'] / 3600);
  }
  elseif(array_key_exists('maxLatDegrees',$_GET) && array_key_exists('maxLatMinutes',$_GET) && array_key_exists('maxLatSeconds',$_GET) && ($_GET['maxLatDegrees'] > 0))
  { $maxLatDegrees = $_GET['maxLatDegrees'];
		$maxLatMinutes = $_GET['maxLatMinutes'];
		$maxLatSeconds = $_GET['maxLatSeconds'];
    $maxLat = $_GET['maxLatDegrees'] + ($_GET['maxLatMinutes'] / 60) + ($_GET['maxLatSeconds'] / 3600);
  }
  elseif(array_key_exists('maxLatDegrees',$_GET) && array_key_exists('maxLatMinutes',$_GET) && array_key_exists('maxLatSeconds',$_GET) && ($_GET['maxLatDegrees'] == "0"))
  { $maxLatDegrees = $_GET['maxLatDegrees'];
		$maxLatMinutes = $_GET['maxLatMinutes'];
		$maxLatSeconds = $_GET['maxLatSeconds'];
    $maxLat = 0 + ($_GET['maxLatMinutes'] / 60) + ($_GET['maxLatSeconds'] / 3600);
  }
  else
  { $maxLatDegrees = '';
		$maxLatMinutes = '';
		$maxLatSeconds = '';
    $maxLat = '';
  }
  // MAXIMUM RA
  if(array_key_exists('maxRAhours',$_GET) && array_key_exists('maxRAminutes',$_GET) && array_key_exists('maxRAseconds',$_GET) && ($_GET['maxRAhours'] > 0  || $_GET['maxRAminutes'] > 0 || $_GET['maxRAseconds'] > 0))
  { $maxRAhours = $_GET['maxRAhours'];
		$maxRAminutes = $_GET['maxRAminutes'];
		$maxRAseconds = $_GET['maxRAseconds'];
    $maxra = $_GET['maxRAhours'] + ($_GET['maxRAminutes'] / 60) + ($_GET['maxRAseconds'] / 3600);
  }
  elseif(array_key_exists('maxRAhours',$_GET) && ($_GET['maxRAhours'] == "0"))
  { $maxRAhours = 0;
		$maxRAminutes = 0;
		$maxRAseconds = 0;
    $maxra = 0;
  }
  else
  { $maxRAhours = '';
		$maxRAminutes = '';
		$maxRAseconds = '';
    $maxra = '';
  }
  // MINIMUM RA
  if(array_key_exists('minRAhours',$_GET) && array_key_exists('maxRAminutes',$_GET) && array_key_exists('maxRAseconds',$_GET) && ($_GET['minRAhours'] > 0  || $_GET['maxRAminutes'] > 0 || $_GET['maxRAseconds'] > 0))
  { $minRAhours = $_GET['minRAhours'];
		$minRAminutes = $_GET['minRAminutes'];
		$minRAseconds = $_GET['minRAseconds']; 
    $minra = $_GET['minRAhours'] + ($_GET['minRAminutes'] / 60) + ($_GET['minRAseconds'] / 3600);
  }
	elseif(array_key_exists('minRAhours',$_GET) && array_key_exists('maxRAminutes',$_GET) && array_key_exists('maxRAseconds',$_GET) && ($_GET['minRAhours'] == "0"))
  { $minRAhours = $_GET['minRAhours'];
		$minRAminutes = $_GET['minRAminutes'];
		$minRAseconds = $_GET['minRAseconds']; 
    $minra = 0 + ($_GET['minRAminutes'] / 60) + ($_GET['minRAseconds'] / 3600);
  }
  else
  { $minRAhours = '';
		$minRAminutes = '';
		$minRAseconds = ''; 
    $minra = '';
  }
  // MINIMUM SIZE
  if(array_key_exists('minsize',$_GET) && ($_GET['minsize']))
  { if(array_key_exists('size_min_units',$_GET) && ($_GET['size_min_units'] == "min"))
    { $minsize = $_GET['minsize'] * 60;
      $size_min_units = $_GET['size_min_units'];
		}
    elseif(array_key_exists('size_min_units',$_GET) && ($_GET['size_min_units'] == "sec"))
    { $minsize = $_GET['minsize'];
      $size_min_units = $_GET['size_min_units'];
    }
    else
    { $size_min_units = '';
      $minsize = '';
    }
  }
  else
  { $size_min_units = '';
    $minsize = '';
  }
  // MAXIMUM SIZE
  if(array_key_exists('maxsize',$_GET) && ($_GET['maxsize']))
  { if(array_key_exists('size_max_units',$_GET) && ($_GET['size_max_units'] == "min"))
    { $maxsize = $_GET['maxsize'] * 60;
      $size_max_units = $_GET['size_max_units'];
		}
    elseif(array_key_exists('size_max_units',$_GET) && ($_GET['size_max_units'] == "sec"))
    { $maxsize = $_GET['maxsize'];
      $size_max_units = $_GET['size_max_units'];
    }
    else
    { $maxsize = '';
      $size_max_units = '';
    }
  }
  else
  { $maxsize = '';
    $size_max_units = '';
  }
  // SEARCH ON ATLAS PAGE NUMBER
  if(array_key_exists('atlas',$_GET) && array_key_exists('page',$_GET) && ($_GET['atlas'] == "msa" && $_GET['page'])) // millenium star atlas
  { $atlas = $_GET['atlas'];
		$page = $_GET['page'];
    if(array_key_exists('page', $_GET) && ($_GET['page'] < 517)) // first book
      $pagenumber = $_GET['page'] . "/I";
    elseif(array_key_exists('page', $_GET) && ($_GET['page'] < 1033)) // second book
      $pagenumber = $_GET['page'] . "/II";
    else // third book
      $pagenumber = $_GET['page'] . "/III";
   }  
	 elseif(array_key_exists('atlas',$_GET) && array_key_exists('page',$_GET)) // other atlases
   { $atlas = $_GET['atlas'];
		 $page = $_GET['page'];
     $pagenumber = $_GET['page'];
   }
	else
	{ $atlas = '';
		$page = '';
	  $pagenumber = '';
	}
  if(array_key_exists('seen', $_GET) && $_GET['seen'])
    $seenpar=$_GET['seen'];
  else
    $seenpar="D";	 
  // OBSERVATIONS TABLE HEADERS
  if(array_key_exists('type',$_GET))
	  $type = $_GET['type'];
	else
	  $type = '';
	$instrument = '';
  $site='';
  if(array_key_exists('instrument',$_GET) && $_GET['instrument'] != "") $instrument = $_GET['instrument'];
	if(array_key_exists('site',$_GET) && $_GET['site'] != "") $site = $_GET['site'];
	if(array_key_exists('maxlimmag',$_GET)) $maxlimmag = $_GET['maxlimmag']; else $maxlimmag = '';
	if(array_key_exists('minlimmag',$_GET)) $minlimmag = $_GET['minlimmag']; else $minlimmag = '';
	if(array_key_exists('maxseeing',$_GET)) $maxseeing = $_GET['maxseeing']; else $maxseeing = '';
	if(array_key_exists('minseeing',$_GET)) $minseeing = $_GET['minseeing']; else $minseeing = '';
	if(array_key_exists('maxvisibility',$_GET)) $maxvisibility = $_GET['maxvisibility']; else $maxvisibility = '';
	if(array_key_exists('minvisibility',$_GET)) $minvisibility = $_GET['minvisibility']; else $minvisibility = '';
	if(array_key_exists('drawings',$_GET)) $drawings = $_GET['drawings']; else $drawings = '';
  //$mindiam = $mindiameter;
  //$maxdiam = $maxdiameter;
  if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && $objObserver->getUseLocal($_SESSION['deepskylog_id']))
  { if ($mindate != "")
      $mindate = $mindate - 1;
    if ($maxdate != "")
      $maxdate = $maxdate + 1;
  }
	if(array_key_exists('alllanguages', $_SESSION))
    $allLanguages = $_SESSION['alllanguages'];
  else
    $allLanguages = Array();
  $usedLanguages = Array();
  while(list ($key, $value) = each($allLanguages))
    if(array_key_exists($key, $_GET) /*|| array_key_exists($key, $_SESSION)*/)
    { $_SESSION[$key] = $key;
      $usedLanguages[] = $key;
    }
  $query = array("object" => $object,
                 "catalog" => $catalogue,
	               "number" => $number,
                 "observer" => $observer,
                 "instrument" => $instrument,
                 "location" => $site,
                 "mindate" => $mindate,
                 "maxdate" => $maxdate, 
                 "maxdiameter" => $maxdiameter,
                 "mindiameter" => $mindiameter,
                 "type" => $type,
                 "con" => $con,
                 "maxmag" => $maxmag,
                 "minmag" => $minmag,
                 "maxsb" => $maxsb,
                 "minsb" => $minsb,
                 "maxdecl" => $maxdecl,
                 "mindecl" => $mindecl,
								 "minLat" => $minLat,
								 "maxLat" => $maxLat,
                 $atlas => $pagenumber,
                 "minra" => $minra,
                 "maxra" => $maxra,
                 "mindiam1" => $minsize,
                 "maxdiam1" => $maxsize,
                 "description" => $description,
                 "minvisibility" => $minvisibility,
                 "maxvisibility" => $maxvisibility,
                 "minlimmag" => $minlimmag,
                 "maxlimmag" => $maxlimmag,
                 "minseeing" => $minseeing,
                 "maxseeing" => $maxseeing,
                 "languages" => $usedLanguages);


  //============================================ CHECK TO SEE IF OBSERVATIONS ALREADY FETCHED BEFORE, OTHERWISE FETCH DATA FROM DB ===============================
	$validQobs=false;
  if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])>1)&&(count($_SESSION['Qobs'])>0))
	  $validQobs=true;
	while($validQobs && (list($key,$value) = each($_SESSION['QobsParams'])))
	  if(array_key_exists($key,$query) && ($value!=$query[$key]))
	    $validQobs=false;	 
  if(!$validQobs)
	{ $obs = $objObservation->getObservationFromQuery($query, $seenpar);
    $_SESSION['QobsParams']=$query;
	  $_SESSION['Qobs']=$obs;
		$_SESSION['QobsSort']='observationid';
	  $_SESSION['QobsSortDirection']='desc';
		$query['countquery']='true';
    $_SESSION['QobsTotal'] = $objObservation->getObservationFromQuery($query, $seenpar); 
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
			  $_SESSION['Qobs']=array_reverse($sortarray,true);
      }
			$_SESSION['QobsSort']=$_GET['sort'];
			$_SESSION['QobsSortDirection']='desc';
		}
		else
	  { if(count($_SESSION['Qobs'])>1)
		  { while(list($key, $value)=each($_SESSION['Qobs']))
  	      $sortarray[$value[$_GET['sort']].'_'.(99999999-$value['observationid'])]=$value;
			  uksort($sortarray,"strnatcasecmp");
			  $_SESSION['Qobs']=$sortarray;
			}
			$_SESSION['QobsSort']=$_GET['sort'];
			$_SESSION['QobsSortDirection']='asc';
		}
	}
  if($_SESSION['QobsSortDirection']!=$_GET['sortdirection'])
	{ if(count($_SESSION['Qobs'])>1)
		 	$_SESSION['Qobs']=array_reverse($_SESSION['Qobs'],true);
		$_SESSION['QobsSortDirection']=$_GET['sortdirection'];
	}	
	
	
  if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && $objObserver->getUseLocal($_SESSION['deepskylog_id']))
  { if ($mindate != "" || $maxdate != "")
    { if ($mindate != "")
        $mindate = $mindate + 1;
      if ($maxdate != "")
        $maxdate = $maxdate - 1;
      $newkey = 0;
      $new_obs = Array();
      while(list ($key, $value) = each($obs)) // go through observations array
      { $newdate = $objObservation->getDsObservationLocalDate($value);
        if ($mindate != "" && $maxdate != "") 
        { if (($newdate >= $mindate) && ($newdate <= $maxdate)) 
          { $new_obs[$newkey] = $value;
            $newkey++;
          }
        }
        else if ($maxdate != "") 
        { if ($newdate <= $maxdate)
          { $new_obs[$newkey] = $value;
            $newkey++;
          }
        }
        else if ($mindate != "")
        { if ($newdate >= $mindate)
          { $new_obs[$newkey] = $value;
            $newkey++;
          }
        }
      }
      $obs = $new_obs;
    }
  }
     
   // Check if only the observations with a drawing should be shown: THERE SHOULD COME A FIELD IN THE DB SHOWING IF AN OBSERVATION HAS A DRAWING
   if(array_key_exists('drawings',$_GET) && $_GET['drawings'])
   { $drawingslist[] = false;
		 if ($handle = opendir('drawings/'))
     { while (false !== ($file = readdir($handle)))
       { $file = preg_replace("/.jpg/", "", $file);
         $file = preg_replace("/_resized/", "", $file);
         if ($file != "." && $file != "..")
           $drawingslist[] = $file;
       }
       closedir($handle);
     }
		 if($drawings)
     { $drawingslist = array_unique($drawingslist);
			 for ($i = 0;$i < count($obs);$i++)
         if ($test = array_search($obs[$i], $drawingslist))
           $new_obs[] = $obs[$i];
       $obs = $new_obs;
     }
		 else
			 $obs = array();
   }
}
else
  $obs=array(); 
?>
