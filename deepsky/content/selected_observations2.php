<?php

// selected_observations2.php
// generates an overview of selected observations in the database
// version 0.4: 2005/11/05, WDM

// Code cleanup - removed by David on 20080704
//include_once "../lib/locations.php";
//$locations = new Locations;
//include_once "../lib/instruments.php";
//$instruments = new Instruments;




if (!function_exists('fnmatch')) 
{
  function fnmatch($pattern, $string)
	{
    return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}

include_once "../lib/lists.php";
include_once "../lib/observations.php";
include_once "../lib/setup/language.php";
include_once "../lib/observers.php";
include_once "../lib/objects.php";
include_once "../lib/util.php";
include_once "../lib/setup/databaseInfo.php";

$observations = new Observations;
$observers = new Observers;
$objects = new Objects;
$util = new util;
$util->checkUserInput();
$list = new Lists;
$myList = False;
if(array_key_exists('listname',$_SESSION) && $list->checkList($_SESSION['listname'])==2)
  $myList=True;
if(array_key_exists('addObservationToList',$_GET) && $_GET['addObservationToList'] && $myList)
{
  $listobservationname = $_GET['addObservationToList'];
	$list->addObservationToList($listobservationname);
  echo LangListQueryObjectsMessage16 . LangListQueryObjectsMessage6 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $_SESSION['listname'] . "</a>.";
	echo "<HR>";
}
elseif(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
{
  $listobjectname = $_GET['removeObjectFromList'];
	$list->removeObjectFromList($listobjectname);
  echo LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($listobjectname) . "\">" . $listobjectname . "</a>" . LangListQueryObjectsMessage7 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $_SESSION['listname'] . "</a>.";
	echo "<HR>";
}
 // minimum

if(array_key_exists('min',$_GET))
   $min=$_GET['min'];
 elseif(array_key_exists('multiplepagenr',$_GET))
    $min = ($_GET['multiplepagenr']-1)*25;
 elseif(array_key_exists('multiplepagenr',$_POST))
    $min = ($_POST['multiplepagenr']-1)*25;
 else
    $min = 0;
$object = '';
$cataloguesearch = ''; // variable to check if only catalogue has been filled in

if(array_key_exists('object', $_GET) && ($_GET['object']))
{  
	$object = $_GET['object'];
  if(($object!='* ') && ((!array_key_exists('catalogue',$_GET)) || (array_key_exists('catalogue',$_GET) && $_GET['catalogue']=="") || ($_GET['number']!='')))
	{
    // SEEN
    $seen = "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
    $seenDetails = $objects->getSeen($_GET['object']);
    if(substr($seenDetails,0,1)=="X") // object has been seen already
    {
      $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
    }
    if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
    {
      if (substr($seenDetails,0,1)=="Y") // object has been seen by the observer logged in
        $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
    }
    echo("<div id=\"main\"><h2>");
    echo (LangViewObjectTitle . "&nbsp;-&nbsp;" . stripslashes($_GET['object']));
    echo "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen;
    echo("</h2>");
  	echo "<table width=\"100%\"><tr>";
  	echo("<td width=\"25%\" align=\"left\">");
    echo("<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['object']) . "\">" . LangViewObjectViewNearbyObject . " " . $_GET['object']);
  	echo("</td><td width=\"25%\" align=\"center\">");
    if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
      echo("<a href=\"deepsky/index.php?indexAction=add_observation&object=" . urlencode($_GET['object']) . "\">" . LangViewObjectAddObservation . $_GET['object'] . "</a>");
  	echo("</td>");
  	if($myList)
  	{
      echo("<td width=\"25%\" align=\"center\">");
      if($list->checkObjectInMyActiveList($_GET['object']))
        echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($_GET['object']) . "&amp;removeObjectFromList=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>");
      else
        echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($_GET['object']) . "&amp;addObjectToList=" . urlencode($_GET['object']) . "&amp;showname=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>");
  	  echo("</td>");
  	}	
  	echo("</tr>");
  	echo("</table>");
	  $objects->showObject($object);
	}
}	
if(array_key_exists('seen', $_GET) && $_GET['seen'])
  $seenpar=$_GET['seen'];
else
  $seenpar="D";	 

$objectarray=array();
if(array_key_exists('number',$_GET) && $_GET['number'])
  $objectarray = $objects->getExactDsObject("",$_GET['catalogue'], $_GET['number']);

if($objectarray && ($objectarray!=''))
{
  $object=$objectarray[0];
}
elseif(array_key_exists('catalogue',$_GET) && ($_GET['catalogue']))
{
  $object = $_GET['catalogue'] . " ";
  $cataloguesearch = "yes";
}  
 
if(array_key_exists('catalogue',$_GET))
  $catalogue = $_GET['catalogue']; // field to sort on 
else
  $catalogue = '';

// TITLE
echo"<table width=\"100%\">";
echo"<td>";
echo("<div id=\"main\">\n<h2>");
$theDate = date('Ymd', strtotime('-1 year')) ;
if(array_key_exists('minyear',$_GET) && ($_GET['minyear'] == substr($theDate,0,4)) &&
   array_key_exists('minmonth',$_GET) && ($_GET['minmonth'] == substr($theDate,4,2)) &&
   array_key_exists('minday',$_GET) && ($_GET['minday'] == substr($theDate,6,2)))
  echo (LangSelectedObservationsTitle3); 
elseif ($catalogue=="*")
  echo (LangOverviewObservationsTitle); 
elseif($object)
  echo (LangSelectedObservationsTitle . $object);
else
  echo(LangSelectedObservationsTitle2);
	


if(((array_key_exists('number',$_GET) && $_GET['number']) || 
    (array_key_exists('catalog',$_GET) && $_GET['catalog'])) && ($object==''))
{
   echo("</h2>\n");
	 echo "<p>" . LangObservationQueryError1 . "</p>";
   echo "<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>";
   echo " " . LangObservationOR . " ";
   echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=*\">" . LangObservationQueryError3 . "</a>";
}
elseif($object ||
       (array_key_exists('observer',$_GET) && $_GET['observer']) || 
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
	     (array_key_exists('catalogue',$_GET) && $_GET['catalogue']) || 
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
	     (array_key_exists('description',$_GET) && $_GET['description']) ||
			 (array_key_exists('minvisibility',$_GET) && $_GET['minvisibility']!="") || 
	     (array_key_exists('maxvisibility',$_GET) && $_GET['maxvisibility']!="") || 
	     (array_key_exists('minlimmag',$_GET) && $_GET['minlimmag']!="") || 
	     (array_key_exists('maxlimmag',$_GET) && $_GET['maxlimmag']!="") || 
	     (array_key_exists('minseeing',$_GET) && $_GET['minseeing']!="") || 
	     (array_key_exists('maxseeing',$_GET) && $_GET['maxseeing']!="")) // at least 1 field to search on 
{
   if(array_key_exists('observer',$_GET))	    $observer = $_GET['observer'];
	 else $observer = '';
   if(array_key_exists('number',$_GET))       $number = $_GET['number'];
	 else $number = '';
	 if(array_key_exists('minyear',$_GET) && array_key_exists('minmonth',$_GET) && array_key_exists('minday',$_GET) && $_GET['minyear'] && $_GET['minmonth'] && $_GET['minday']) // exact date given
   {
     $minyear = $_GET['minyear'];
		 $minmonth = $_GET['minmonth'];
		 $minday = $_GET['minday'];
		 $mindate = $minyear . sprintf("%02d",$minmonth) . sprintf("%02d",$minday);
   }
   elseif(array_key_exists('minyear',$_GET) && array_key_exists('minmonth',$_GET) && $_GET['minyear'] && $_GET['minmonth']) // month and year given
   {
     $minyear = $_GET['minyear'];
		 $minmonth = $_GET['minmonth'];
		 $minday = '';
     $mindate = $minyear . sprintf("%02d",$minmonth) . "00";
   }
   elseif(array_key_exists('minyear',$_GET) && $_GET['minyear']) // only year given
   {
     $minyear = $_GET['minyear'];
		 $minmonth = '';
		 $minday = '';
     $mindate = $minyear . "0000";
   }
   elseif(array_key_exists('minmonth',$_GET) && $_GET['minmonth'] && array_key_exists('minday',$_GET) && $_GET['minday'])
   {
     $minyear = '';
		 $minmonth = $_GET['minmonth'];
		 $minday = $_GET['minday'];
	   $mindate = sprintf("%02d",$_GET['minmonth']) . sprintf("%02d",$_GET['minday']);
   }
	 elseif(array_key_exists('minmonth',$_GET) && $_GET['minmonth'])
   {
     $minyear = '';
		 $minmonth = $_GET['minmonth'];
		 $minday = '';
	   $mindate = sprintf("%02d",$_GET['minmonth']) . "00";
   }
	 else
	 {
     $minyear = '';
		 $minmonth = '';
		 $minday = '';
     $mindate = '';
	 }
	  
   if(array_key_exists('maxyear',$_GET) && array_key_exists('maxmonth',$_GET) && array_key_exists('maxday',$_GET) && $_GET['maxyear'] && $_GET['maxmonth'] && $_GET['maxday']) // exact date given
   {
     $maxyear = $_GET['maxyear'];
		 $maxmonth = $_GET['maxmonth'];
		 $maxday = $_GET['maxday'];
     $maxdate = $_GET['maxyear'] . sprintf("%02d",$_GET['maxmonth']) . sprintf("%02d",$_GET['maxday']);
   }
   elseif(array_key_exists('maxyear',$_GET) && array_key_exists('maxmonth',$_GET) && $_GET['maxyear'] && $_GET['maxmonth']) // month and year given 
   {
     $maxyear = $_GET['maxyear'];
		 $maxmonth = $_GET['maxmonth'];
		 $maxday = '';
     $maxdate = $_GET['maxyear'] . sprintf("%02d",$_GET['maxmonth']) . "31";
   }
   elseif(array_key_exists('maxyear',$_GET) && $_GET['maxyear']) // only year given
   {
     $maxyear = $_GET['maxyear'];
		 $maxmonth = '';
		 $maxday = '';
     $maxdate = $_GET['maxyear'] . "1231";
   }
   elseif(array_key_exists('maxmonth',$_GET) && $_GET['maxmonth'] && array_key_exists('maxday',$_GET) && $_GET['maxday'])
   {
     $maxyear = '';
		 $maxmonth = $_GET['maxmonth'];
		 $maxday = $_GET['maxday'];
	   $maxdate = sprintf("%02d",$_GET['maxmonth']) . sprintf("%02d",$_GET['maxday']);
   }
	 elseif(array_key_exists('maxmonth',$_GET) && $_GET['maxmonth'])
   {
     $maxyear = '';
		 $maxmonth = $_GET['maxmonth'];
		 $maxday = '';
	   $maxdate = sprintf("%02d",$_GET['maxmonth']) . "31";
   }
   else
	 {
     $maxyear = '';
		 $maxmonth = '';
		 $maxday = '';
     $maxdate = '';
	 }
	 
	 
   if(array_key_exists('mindiameter',$_GET))
   {
	    if (array_key_exists('mindiameterunits',$_GET) && ($_GET['mindiameterunits'] == "inch")) // convert minimum diameter in inches to mm 
      {   
			   $mindiameter = $_GET['mindiameter'] * 25.4;
				 $mindiameterunits = $_GET['mindiameterunits'];
      }
      else
      {
         $mindiameter = $_GET['mindiameter'];
				 $mindiameterunits = '';
      }
   }
	 else
	 {
	 $mindiameter = '';
	 $mindiameterunits = '';
	 }
   
	 if(array_key_exists('maxdiameter',$_GET))
   {
	    if (array_key_exists('maxdiameterunits', $_GET) && ($_GET['maxdiameterunits'] == "inch")) // convert maximum diameter in inches to mm
      {
			   $maxdiameter = $_GET['maxdiameter'] * 25.4;
	       $maxdiameterunits = $_GET['maxdiameterunits'];
      }
      else
      {
         $maxdiameter = $_GET['maxdiameter'];
	       $maxdiameterunits = $_GET['maxdiameterunits'];
      }
   }
	 else
	 {
	 $maxdiameter = '';
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
   {
	    $minDeclDegrees = $_GET['minDeclDegrees'];
			$minDeclMinutes = $_GET['minDeclMinutes'];
			$minDeclSeconds = $_GET['minDeclSeconds'];
      $mindecl = $_GET['minDeclDegrees'] - ($_GET['minDeclMinutes'] / 60) - ($_GET['minDeclSeconds'] / 3600);
   }
   elseif(array_key_exists('minDeclDegrees',$_GET) && array_key_exists('minDeclMinutes',$_GET) && array_key_exists('minDeclSeconds',$_GET) &&  ($_GET['minDeclDegrees'] > 0))
   {
	    $minDeclDegrees = $_GET['minDeclDegrees'];
			$minDeclMinutes = $_GET['minDeclMinutes'];
			$minDeclSeconds = $_GET['minDeclSeconds'];
      $mindecl = $_GET['minDeclDegrees'] + ($_GET['minDeclMinutes'] / 60) + ($_GET['minDeclSeconds'] / 3600);
   }
   elseif(array_key_exists('minDeclDegrees',$_GET) && array_key_exists('minDeclMinutes',$_GET) && array_key_exists('minDeclSeconds',$_GET) && ($_GET['minDeclDegrees'] == "0"))
   {
	    $minDeclDegrees = $_GET['minDeclDegrees'];
			$minDeclMinutes = $_GET['minDeclMinutes'];
			$minDeclSeconds = $_GET['minDeclSeconds'];
      $mindecl = 0 + ($_GET['minDeclMinutes'] / 60) + ($_GET['minDeclSeconds'] / 3600);
   }
   else
   {
	    $minDeclDegrees = '';
			$minDeclMinutes = '';
			$minDeclSeconds = '';
      $mindecl = '';
   }
   // MINIMUM Latitude
   if(array_key_exists('minLatDegrees',$_GET) && array_key_exists('minLatMinutes',$_GET) && array_key_exists('minLatSeconds',$_GET) && ($_GET['minLatDegrees'] < 0 || strcmp($_GET['minLatDegrees'], "-0") == 0))
   {
	    $minLatDegrees = $_GET['minLatDegrees'];
			$minLatMinutes = $_GET['minLatMinutes'];
			$minLatSeconds = $_GET['minLatSeconds'];
      $minLat = $_GET['minLatDegrees'] - ($_GET['minLatMinutes'] / 60) - ($_GET['minLatSeconds'] / 3600);
   }
   elseif(array_key_exists('minLatDegrees',$_GET) && array_key_exists('minLatMinutes',$_GET) && array_key_exists('minLatSeconds',$_GET) &&  ($_GET['minLatDegrees'] > 0))
   {
	    $minLatDegrees = $_GET['minLatDegrees'];
			$minLatMinutes = $_GET['minLatMinutes'];
			$minLatSeconds = $_GET['minLatSeconds'];
      $minLat = $_GET['minLatDegrees'] + ($_GET['minLatMinutes'] / 60) + ($_GET['minLatSeconds'] / 3600);
   }
   elseif(array_key_exists('minLatDegrees',$_GET) && array_key_exists('minLatMinutes',$_GET) && array_key_exists('minLatSeconds',$_GET) && ($_GET['minLatDegrees'] == "0"))
   {
	    $minLatDegrees = $_GET['minLatDegrees'];
			$minLatMinutes = $_GET['minLatMinutes'];
			$minLatSeconds = $_GET['minLatSeconds'];
      $minLat = 0 + ($_GET['minLatMinutes'] / 60) + ($_GET['minLatSeconds'] / 3600);
   }
   else
   {
	    $minLatDegrees = '';
			$minLatMinutes = '';
			$minLatSeconds = '';
      $minLat = '';
   }

   // MAXIMUM DECLINATION
   if(array_key_exists('maxDeclDegrees',$_GET) && array_key_exists('maxDeclMinutes',$_GET) && array_key_exists('maxDeclSeconds',$_GET) && ($_GET['maxDeclDegrees'] < 0 || strcmp($_GET['maxDeclDegrees'], "-0") == 0))
   {
	    $maxDeclDegrees = $_GET['maxDeclDegrees'];
			$maxDeclMinutes = $_GET['maxDeclMinutes'];
			$maxDeclSeconds = $_GET['maxDeclSeconds'];
      $maxdecl = $_GET['maxDeclDegrees'] - ($_GET['maxDeclMinutes'] / 60) - ($_GET['maxDeclSeconds'] / 3600);
   }
   elseif(array_key_exists('maxDeclDegrees',$_GET) && array_key_exists('maxDeclMinutes',$_GET) && array_key_exists('maxDeclSeconds',$_GET) && ($_GET['maxDeclDegrees'] > 0))
   {
	    $maxDeclDegrees = $_GET['maxDeclDegrees'];
			$maxDeclMinutes = $_GET['maxDeclMinutes'];
			$maxDeclSeconds = $_GET['maxDeclSeconds'];
      $maxdecl = $_GET['maxDeclDegrees'] + ($_GET['maxDeclMinutes'] / 60) + ($_GET['maxDeclSeconds'] / 3600);
   }
   elseif(array_key_exists('maxDeclDegrees',$_GET) && array_key_exists('maxDeclMinutes',$_GET) && array_key_exists('maxDeclSeconds',$_GET) && ($_GET['maxDeclDegrees'] == "0"))
   {
	    $maxDeclDegrees = $_GET['maxDeclDegrees'];
			$maxDeclMinutes = $_GET['maxDeclMinutes'];
			$maxDeclSeconds = $_GET['maxDeclSeconds'];
      $maxdecl = 0 + ($_GET['maxDeclMinutes'] / 60) + ($_GET['maxDeclSeconds'] / 3600);
   }
   else
   {
	    $maxDeclDegrees = '';
			$maxDeclMinutes = '';
			$maxDeclSeconds = '';
      $maxdecl = "";
   }
   // MAXIMUM Latitude
   if(array_key_exists('maxLatDegrees',$_GET) && array_key_exists('maxLatMinutes',$_GET) && array_key_exists('maxLatSeconds',$_GET) && ($_GET['maxLatDegrees'] < 0 || strcmp($_GET['maxLatDegrees'], "-0") == 0))
   {
	    $maxLatDegrees = $_GET['maxLatDegrees'];
			$maxLatMinutes = $_GET['maxLatMinutes'];
			$maxLatSeconds = $_GET['maxLatSeconds'];
      $maxLat = $_GET['maxLatDegrees'] - ($_GET['maxLatMinutes'] / 60) - ($_GET['maxLatSeconds'] / 3600);
   }
   elseif(array_key_exists('maxLatDegrees',$_GET) && array_key_exists('maxLatMinutes',$_GET) && array_key_exists('maxLatSeconds',$_GET) && ($_GET['maxLatDegrees'] > 0))
   {
	    $maxLatDegrees = $_GET['maxLatDegrees'];
			$maxLatMinutes = $_GET['maxLatMinutes'];
			$maxLatSeconds = $_GET['maxLatSeconds'];
      $maxLat = $_GET['maxLatDegrees'] + ($_GET['maxLatMinutes'] / 60) + ($_GET['maxLatSeconds'] / 3600);
   }
   elseif(array_key_exists('maxLatDegrees',$_GET) && array_key_exists('maxLatMinutes',$_GET) && array_key_exists('maxLatSeconds',$_GET) && ($_GET['maxLatDegrees'] == "0"))
   {
	    $maxLatDegrees = $_GET['maxLatDegrees'];
			$maxLatMinutes = $_GET['maxLatMinutes'];
			$maxLatSeconds = $_GET['maxLatSeconds'];
      $maxLat = 0 + ($_GET['maxLatMinutes'] / 60) + ($_GET['maxLatSeconds'] / 3600);
   }
   else
   {
	    $maxLatDegrees = '';
			$maxLatMinutes = '';
			$maxLatSeconds = '';
      $maxLat = "";
   }

   // MAXIMUM RA
   if(array_key_exists('maxRAhours',$_GET) && array_key_exists('maxRAminutes',$_GET) && array_key_exists('maxRAseconds',$_GET) && ($_GET['maxRAhours'] > 0  || $_GET['maxRAminutes'] > 0 || $_GET['maxRAseconds'] > 0))
   {
	    $maxRAhours = $_GET['maxRAhours'];
			$maxRAminutes = $_GET['maxRAminutes'];
			$maxRAseconds = $_GET['maxRAseconds'];
      $maxra = $_GET['maxRAhours'] + ($_GET['maxRAminutes'] / 60) + ($_GET['maxRAseconds'] / 3600);
   }
   elseif(array_key_exists('maxRAhours',$_GET) && ($_GET['maxRAhours'] == "0"))
   {
	    $maxRAhours = 0;
			$maxRAminutes = 0;
			$maxRAseconds = 0;
      $maxra = 0;
   }
   else
   {
	    $maxRAhours = '';
			$maxRAminutes = '';
			$maxRAseconds = '';
      $maxra = '';
   }

   // MINIMUM RA
   if(array_key_exists('minRAhours',$_GET) && array_key_exists('maxRAminutes',$_GET) && array_key_exists('maxRAseconds',$_GET) && ($_GET['minRAhours'] > 0  || $_GET['maxRAminutes'] > 0 || $_GET['maxRAseconds'] > 0))
   {
	    $minRAhours = $_GET['minRAhours'];
			$minRAminutes = $_GET['minRAminutes'];
			$minRAseconds = $_GET['minRAseconds']; 
      $minra = $_GET['minRAhours'] + ($_GET['minRAminutes'] / 60) + ($_GET['minRAseconds'] / 3600);
   }
   elseif(array_key_exists('minRAhours',$_GET) && array_key_exists('maxRAminutes',$_GET) && array_key_exists('maxRAseconds',$_GET) && ($_GET['minRAhours'] == "0"))
   {
	    $minRAhours = $_GET['minRAhours'];
			$minRAminutes = $_GET['minRAminutes'];
			$minRAseconds = $_GET['minRAseconds']; 
      $minra = 0 + ($_GET['minRAminutes'] / 60) + ($_GET['minRAseconds'] / 3600);
   }
   else
   {
	    $minRAhours = '';
			$minRAminutes = '';
			$minRAseconds = ''; 
      $minra = '';
   }

   // MINIMUM SIZE

   if(array_key_exists('minsize',$_GET) && ($_GET['minsize']))
   {
      if(array_key_exists('size_min_units',$_GET) && ($_GET['size_min_units'] == "min"))
      {
         $minsize = $_GET['minsize'] * 60;
         $size_min_units = $_GET['size_min_units'];
			}
      elseif(array_key_exists('size_min_units',$_GET) && ($_GET['size_min_units'] == "sec"))
      {
         $minsize = $_GET['minsize'];
         $size_min_units = $_GET['size_min_units'];
      }
      else
      {
         $size_min_units = '';
         $minsize = '';
      }
   }
   else
   {
      $size_min_units = '';
      $minsize = '';
   }

   // MAXIMUM SIZE
   if(array_key_exists('maxsize',$_GET) && ($_GET['maxsize']))
   {
      if(array_key_exists('size_max_units',$_GET) && ($_GET['size_max_units'] == "min"))
      {
         $maxsize = $_GET['maxsize'] * 60;
         $size_max_units = $_GET['size_max_units'];
			}
      elseif(array_key_exists('size_max_units',$_GET) && ($_GET['size_max_units'] == "sec"))
      {
         $maxsize = $_GET['maxsize'];
         $size_max_units = $_GET['size_max_units'];
      }
      else
      {
         $maxsize = '';
         $size_max_units = '';
      }
   }
   else
   {
      $maxsize = '';
      $size_max_units = '';
   }


   // SEARCH ON ATLAS PAGE NUMBER
   if(array_key_exists('atlas',$_GET) && array_key_exists('page',$_GET) && ($_GET['atlas'] == "msa" && $_GET['page'])) // millenium star atlas
   {
      $atlas = $_GET['atlas'];
			$page = $_GET['page'];
      if(array_key_exists('page', $_GET) && ($_GET['page'] < 517)) // first book
      {
         $pagenumber = $_GET['page'] . "/I";
      }
      elseif(array_key_exists('page', $_GET) && ($_GET['page'] < 1033)) // second book
      {
         $pagenumber = $_GET['page'] . "/II";
      }
      else // third book
      {
         $pagenumber = $_GET['page'] . "/III";
      }
   }
   elseif(array_key_exists('atlas',$_GET) && array_key_exists('page',$_GET)) // other atlases
   {
      $atlas = $_GET['atlas'];
			$page = $_GET['page'];
      $pagenumber = $_GET['page'];
   }
	 else
	 {  
	    $atlas = '';
			$page = '';
	    $pagenumber = '';
	 }

   // OBSERVATIONS TABLE HEADERS

	 if(array_key_exists('type',$_GET))
	 {
      $type = $_GET['type'];
	 }
	 else
	 {
	    $type = '';
	 }

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

   if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && $observers->getUseLocal($_SESSION['deepskylog_id']))
     if ($mindate != "")
      $mindate = $mindate - 1;
     if ($maxdate != "")
      $maxdate = $maxdate + 1;

   if(array_key_exists('alllanguages', $_SESSION))
     $allLanguages = $_SESSION['alllanguages'];
   else
     $allLanguages = Array();
   $usedLanguages = Array();
   while(list ($key, $value) = each($allLanguages))
     if(array_key_exists($key, $_GET) || array_key_exists($key, $_SESSION))
     {
       $_SESSION[$key] = $key;
       $usedLanguages[] = $key;
     }

   // QUERY
   $query = array("object" => $object,
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

   // SORTING
   if(array_key_exists('sort',$_GET))      
	   $sort = $_GET['sort']; // field to sort on 
   else
   {
      $sort = "id";
      $_GET['sort'] = $sort;
   }
   if($cataloguesearch == "yes")
      $obs = $observations->getObservationFromQuery($query,$sort,0,false,$seenpar); // LIKE
   else
      $obs = $observations->getObservationFromQuery($query,$sort,1,true,$seenpar); // EXACT MATCH

   $query = array("object" => $object,
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
                  "maxseeing" => $maxseeing);
	 if($cataloguesearch == "yes")
      $allobs = $observations->getObservationFromQuery($query,"",0, false, $seenpar); // LIKE
   else
      $allobs = $observations->getObservationFromQuery($query,"",1, true, $seenpar); // EXACT MATCH

  // Dates can change when we use local time!
  if (array_key_exists('deepskylog_id',$_SESSION) && ($_SESSION['deepskylog_id']) && $observers->getUseLocal($_SESSION['deepskylog_id']))
  {
    if ($mindate != "" || $maxdate != "")
    {
      if ($mindate != "")
        $mindate = $mindate + 1;
      if ($maxdate != "")
        $maxdate = $maxdate - 1;
      $newkey = 0;
      $new_obs = Array();
      while(list ($key, $value) = each($obs)) // go through observations array
      {
        $newdate = $observations->getDsObservationLocalDate($value);

        if ($mindate != "" && $maxdate != "") 
        {
          if (($newdate >= $mindate) && ($newdate <= $maxdate)) 
          {
            $new_obs[$newkey] = $value;
            $newkey++;
          }
        }
        else if ($maxdate != "") 
        {
          if ($newdate <= $maxdate)
          {
            $new_obs[$newkey] = $value;
            $newkey++;
          }
        }
        else if ($mindate != "")
        {
          if ($newdate >= $mindate)
          {
            $new_obs[$newkey] = $value;
            $newkey++;
          }
        }
      }
      $obs = $new_obs;
    }
  }
      
   // Check if only the observations with a drawing should be shown
   if(array_key_exists('drawings',$_GET) && $_GET['drawings'])
   {
	    $drawingslist[] = false;
			if ($handle = opendir('drawings/'))
      {
         while (false !== ($file = readdir($handle)))
         {
            $file = preg_replace("/.jpg/", "", $file);
            $file = preg_replace("/_resized/", "", $file);
            if ($file != "." && $file != "..")
               $drawingslist[] = $file;
         }
         closedir($handle);
      }
			
			if($drawings)
      {
				 $drawingslist = array_unique($drawingslist);
			
         for ($i = 0;$i < count($obs);$i++)
           if ($test = array_search($obs[$i], $drawingslist))
             $new_obs[] = $obs[$i];
         $obs = $new_obs;
      }
			else
			  $obs = false;
   }

   // natural sort of object names

   if (array_key_exists('sort',$_GET) && ($_GET['sort'] == "objectname"))
   {
      while(list ($key, $value) = each($obs)) // go through observations array
        $obsname[$value] = $observations->getObjectId($value);
      natcasesort($obsname);
      reset($obsname);
      $count = 0;
      while(list ($key, $value) = each($obsname)) // go through observations array
      { $obs2[$count] = $key;
        $count++;
      }
      $obs = $obs2;
   }

   if(sizeof($obs) > 0)
     krsort($obs);


  if(array_key_exists('previous',$_GET) && $_GET['previous']) // field to sort on given as a parameter in the url
    $prev = $_GET['previous'];
  else
    $prev = '';

   if(array_key_exists('previous',$_GET) && ($_GET['previous'] == $_GET['sort'])) // reverse sort when pushed twice
   { if ($_GET['sort'] != "")
       $obs = array_reverse($obs, true);
     else
     { krsort($obs);
       reset($obs);
     }
     $previous = ""; // reset previous field to sort on
   }
   else
     $previous = $sort;
   $step = 25;

   $link = "deepsky/index.php?indexAction=result_selected_observations&catalogue=" . urlencode($catalogue) . 
                                         "&amp;observer=" . urlencode($observer) . 
                                         "&amp;object=" . urlencode($object) . 
                                         "&amp;lco=" . $_SESSION['lco'] . 
                                         "&amp;number=" . urlencode($number) . 
                                         "&amp;instrument=" . urlencode($instrument) . 
                                         "&amp;site=" . urlencode($site) . 
                                         "&amp;minyear=" . $minyear . 
                                         "&amp;minmonth=" . $minmonth . 
                                         "&amp;minday=" . $minday . 
                                         "&amp;maxyear=" . $maxyear . 
                                         "&amp;maxmonth=" . $maxmonth . 
                                         "&amp;maxday=" . $maxday . 
                                         "&amp;maxdiameter=" . $maxdiameter .
                                         "&amp;maxdiameterunits=" . $maxdiameterunits .
                                         "&amp;mindiameter=" . $mindiameter .
                                         "&amp;mindiameterunits=" . $mindiameterunits .
                                         "&amp;type=" . $type .
                                         "&amp;con=" . $con .
              	                         "&amp;minLatDegrees=" . $minLatDegrees .
		                                     "&amp;minLatMinutes=" . $minLatMinutes .
		                                     "&amp;minLatSeconds=" . $minLatSeconds .
	                                       "&amp;maxLatDegrees=" . $maxLatDegrees .
			                                   "&amp;maxLatMinutes=" . $maxLatMinutes .
			                                   "&amp;maxLatSeconds=" . $maxLatSeconds .
									                       "&amp;maxLat=" . $maxLat .
                                         "&amp;minmag=" . $minmag .
                                         "&amp;maxsb=" . $maxsb .
                                         "&amp;minsb=" . $minsb .
                                         "&amp;minRAhours=" . $minRAhours .
                                         "&amp;minRAminutes=" . $minRAminutes .
                                         "&amp;minRAseconds=" . $minRAseconds .
                                         "&amp;maxRAhours=" . $maxRAhours .
                                         "&amp;maxRAminutes=" . $maxRAminutes .
                                         "&amp;maxRAseconds=" . $maxRAseconds .
                                         "&amp;maxDeclDegrees=" . $maxDeclDegrees .
                                         "&amp;maxDeclMinutes=" . $maxDeclMinutes .
                                         "&amp;maxDeclSeconds=" . $maxDeclSeconds .
                                         "&amp;minDeclDegrees=" . $minDeclDegrees .
                                         "&amp;minDeclMinutes=" . $minDeclMinutes .
                                         "&amp;minDeclSeconds=" . $minDeclSeconds .
                                         "&amp;minsize=" . $minsize . 
                                         "&amp;size_min_units=" . $size_min_units . 
                                         "&amp;maxsize=" . $maxsize . 
                                         "&amp;size_max_units=" . $size_max_units .
                                         "&amp;atlas=" . $atlas .
                                         "&amp;page=" . $page .
                                         "&amp;description=" . $description .
                                         "&amp;sort=" . $sort . 
                                         "&amp;drawings=" . $drawings .
					                               "&amp;minvisibility=" . $minvisibility .
					                               "&amp;maxvisibility=" . $maxvisibility . 
                                         "&amp;previous=" . $prev . 
																				 "&amp;seen=" . $seenpar;

   $total = count($allobs);

   if (count($obs) == $total)
     $total = "";
	 
   if(count($obs)>0)
	 {
  	 if($_SESSION['lco']!="L")
  	   echo(" - <a href=\"". $link . "&amp;lco=L" . "&amp;min=" . $min . "\" title=\"" . LangOverviewObservationTitle . "\">" . 
  		       LangOverviewObservations . "</a>");
  	 if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
       if($_SESSION['lco']!="C")
         echo(" - <a href=\"". $link . "&amp;lco=C" . "&amp;min=" . $min . "\" title=\"" . LangCompactObservationsTitle . "\">" . 
  			        LangCompactObservations . "</a>");
  	 if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
       if($_SESSION['lco']!="O")
  		   echo(" - <a href=\"". $link . "&amp;lco=O" . "&amp;min=" . $min . "\" title=\"" . LangCompactObservationsLOTitle . "\">" . 
  			        LangCompactObservationsLO . "</a>");
	 }
	 echo "</h2>";
	 echo"</td>";
	 echo"<td align=\"right\">";	 
   list($min, $max) = $util->printNewListHeader($obs, $link, $min, $step, $total);
	 echo"</td>";
	 echo"</table>";
	 
	 if($_SESSION['lco']=="O")
     echo "<p align=\"right\">" .  LangOverviewObservationsHeader5a;
	 
   if(sizeof($obs) > 0)
   {
      $count = 0; // counter for altering table colors
      if(sizeof($obs) > 0) // ONLY WHEN OBSERVATIONS AVAILABLE
      {
        // LINKS TO SORT ON OBSERVATION TABLE HEADERS
        echo "<table width=\"100%\">\n";
        echo "<tr width=\"100%\" class=\"type3\">\n";
        // OBJECT NAME
        echo "<td><a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=" . urlencode($catalogue) .
                                                    "&amp;instrument=" . urlencode($instrument) .
                                                    "&amp;object=" . urlencode($object) . 
                                                    "&amp;lco=" . $_SESSION['lco'] . 
                                                    "&amp;number=" . urlencode($number) .
                                                    "&amp;observer=" . urlencode($observer) .
                                                    "&amp;site=" . urlencode($site) .
                                                    "&amp;minyear=" . $minyear .
                                                    "&amp;minmonth=" . $minmonth .
                                                    "&amp;minday=" . $minday .
                                                    "&amp;maxyear=" . $maxyear . 
                                                    "&amp;maxmonth=" . $maxmonth .
                                                    "&amp;maxday=" . $maxday . 
        	                                          "&amp;maxdiameter=" . $maxdiameter .
                                                    "&amp;maxdiameterunits=" . $maxdiameterunits .
	                                                  "&amp;mindiameter=" . $mindiameter .
                                                    "&amp;mindiameterunits=" . $mindiameterunits .
                                                    "&amp;type=" . $type .
                                                    "&amp;con=" . $con .
                                                    "&amp;minLatDegrees=" . $minLatDegrees .
		                                                "&amp;minLatMinutes=" . $minLatMinutes .
		                                                "&amp;minLatSeconds=" . $minLatSeconds .
	                                                  "&amp;maxLatDegrees=" . $maxLatDegrees .
			                                              "&amp;maxLatMinutes=" . $maxLatMinutes .
			                                              "&amp;maxLatSeconds=" . $maxLatSeconds .
                                                    "&amp;maxmag=" . $maxmag .
                                                    "&amp;minmag=" . $minmag .
                                                    "&amp;maxsb=" . $maxsb .
                                                    "&amp;minsb=" . $minsb .
                                                    "&amp;minRAhours=" . $minRAhours .
                                                    "&amp;minRAminutes=" . $minRAminutes .
                                                    "&amp;minRAseconds=" . $minRAseconds .
                                                    "&amp;maxRAhours=" . $maxRAhours .
                                                    "&amp;maxRAminutes=" . $maxRAminutes .
                                                    "&amp;maxRAseconds=" . $maxRAseconds .
                                                    "&amp;maxDeclDegrees=" . $maxDeclDegrees .
                                                    "&amp;maxDeclMinutes=" . $maxDeclMinutes .
                                                    "&amp;maxDeclSeconds=" . $maxDeclSeconds .
                                                    "&amp;minDeclDegrees=" . $minDeclDegrees .
                                                    "&amp;minDeclMinutes=" . $minDeclMinutes .
                                                    "&amp;minDeclSeconds=" . $minDeclSeconds .
                                                    "&amp;minsize=" . $minsize .
                                                    "&amp;size_min_units=" . $size_min_units .
                                                    "&amp;maxsize=" . $maxsize .
                                                    "&amp;size_max_units=" . $size_max_units .
                                                    "&amp;atlas=" . $atlas .
                                                    "&amp;page=" . $page .
                                                    "&amp;description=" . $description .
                                                    "&amp;drawings=" . $drawings .
	                                                  "&amp;minvisibility=" . $minvisibility .
           	                                        "&amp;maxvisibility=" . $maxvisibility .
																				            "&amp;seen=" . $seenpar . 
                                                    "&amp;sort=objectname&amp;previous=$previous\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObservationsHeader1) . "\">" . 
                                                    LangOverviewObservationsHeader1 . "</a></td>\n";
       
			  echo "<td><a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=" . urlencode($catalogue) .
                                                    "&amp;instrument=" . urlencode($instrument) .
                                                    "&amp;object=" . urlencode($object) . 
                                                    "&amp;lco=" . $_SESSION['lco'] . 
                                                    "&amp;number=" . urlencode($number) .
                                                    "&amp;observer=" . urlencode($observer) .
                                                    "&amp;site=" . urlencode($site) .
                                                    "&amp;minyear=" . $minyear .
                                                    "&amp;minmonth=" . $minmonth .
                                                    "&amp;minday=" . $minday .
                                                    "&amp;maxyear=" . $maxyear . 
                                                    "&amp;maxmonth=" . $maxmonth .
                                                    "&amp;maxday=" . $maxday . 
        	                                          "&amp;maxdiameter=" . $maxdiameter .
                                                    "&amp;maxdiameterunits=" . $maxdiameterunits .
	                                                  "&amp;mindiameter=" . $mindiameter .
                                                    "&amp;mindiameterunits=" . $mindiameterunits .
                                                    "&amp;type=" . $type .
                                                    "&amp;con=" . $con .
                                                    "&amp;minLatDegrees=" . $minLatDegrees .
		                                                "&amp;minLatMinutes=" . $minLatMinutes .
		                                                "&amp;minLatSeconds=" . $minLatSeconds .
	                                                  "&amp;maxLatDegrees=" . $maxLatDegrees .
			                                              "&amp;maxLatMinutes=" . $maxLatMinutes .
			                                              "&amp;maxLatSeconds=" . $maxLatSeconds .
                                                    "&amp;maxmag=" . $maxmag .
                                                    "&amp;minmag=" . $minmag .
                                                    "&amp;maxsb=" . $maxsb .
                                                    "&amp;minsb=" . $minsb .
                                                    "&amp;minRAhours=" . $minRAhours .
                                                    "&amp;minRAminutes=" . $minRAminutes .
                                                    "&amp;minRAseconds=" . $minRAseconds .
                                                    "&amp;maxRAhours=" . $maxRAhours .
                                                    "&amp;maxRAminutes=" . $maxRAminutes .
                                                    "&amp;maxRAseconds=" . $maxRAseconds .
                                                    "&amp;maxDeclDegrees=" . $maxDeclDegrees .
                                                    "&amp;maxDeclMinutes=" . $maxDeclMinutes .
                                                    "&amp;maxDeclSeconds=" . $maxDeclSeconds .
                                                    "&amp;minDeclDegrees=" . $minDeclDegrees .
                                                    "&amp;minDeclMinutes=" . $minDeclMinutes .
                                                    "&amp;minDeclSeconds=" . $minDeclSeconds .
                                                    "&amp;minsize=" . $minsize .
                                                    "&amp;size_min_units=" . $size_min_units .
                                                    "&amp;maxsize=" . $maxsize .
                                                    "&amp;size_max_units=" . $size_max_units .
                                                    "&amp;atlas=" . $atlas .
                                                    "&amp;page=" . $page .
                                                    "&amp;description=" . $description .
                                                    "&amp;drawings=" . $drawings .
	                                                  "&amp;minvisibility=" . $minvisibility .
           	                                        "&amp;maxvisibility=" . $maxvisibility .
																				            "&amp;seen=" . $seenpar . 
                                                    "&amp;sort=objects.con&amp;previous=$previous\" title=\"" . LangSortOn . mb_strtolower(LangViewObservationField1b) . "\">" . 
                                                    LangViewObservationField1b . "</a></td>\n";

         // OBSERVER

         echo "<td><a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=" . urlencode($catalogue) .
                                                    "&amp;instrument=" . urlencode($instrument) .
                                                    "&amp;object=" . urlencode($object) . 
                                                    "&amp;lco=" . $_SESSION['lco'] . 
                                                    "&amp;number=" . urlencode($number) .
                                                    "&amp;observer=" . urlencode($observer) .
                                                    "&amp;site=" . urlencode($site) .
                                                    "&amp;minyear=" . $minyear .
                                                    "&amp;minmonth=" . $minmonth .
                                                    "&amp;minday=" . $minday .
                                                    "&amp;maxyear=" . $maxyear .
                                                    "&amp;maxmonth=" . $maxmonth .
                                                    "&amp;maxday=" . $maxday .
                                                    "&amp;maxdiameter=" . $maxdiameter .
                                                    "&amp;maxdiameterunits=" . $maxdiameterunits .
                                                    "&amp;mindiameter=" . $mindiameter .
                                                    "&amp;mindiameterunits=" . $mindiameterunits .
                                                    "&amp;type=" . $type .
                                                    "&amp;con=" . $con .
                                                    "&amp;minLatDegrees=" . $minLatDegrees .
		                                                "&amp;minLatMinutes=" . $minLatMinutes .
		                                                "&amp;minLatSeconds=" . $minLatSeconds .
	                                                  "&amp;maxLatDegrees=" . $maxLatDegrees .
			                                              "&amp;maxLatMinutes=" . $maxLatMinutes .
			                                              "&amp;maxLatSeconds=" . $maxLatSeconds .
                                                    "&amp;maxmag=" . $maxmag .
                                                    "&amp;minmag=" . $minmag .
                                                    "&amp;maxsb=" . $maxsb .
                                                    "&amp;minsb=" . $minsb .
                                                    "&amp;minRAhours=" . $minRAhours .
                                                    "&amp;minRAminutes=" . $minRAminutes .
                                                    "&amp;minRAseconds=" . $minRAseconds .
                                                    "&amp;maxRAhours=" . $maxRAhours .
                                                    "&amp;maxRAminutes=" . $maxRAminutes .
                                                    "&amp;maxRAseconds=" . $maxRAseconds .
                                                    "&amp;maxDeclDegrees=" . $maxDeclDegrees .
                                                    "&amp;maxDeclMinutes=" . $maxDeclMinutes .
                                                    "&amp;maxDeclSeconds=" . $maxDeclSeconds .
                                                    "&amp;minDeclDegrees=" . $minDeclDegrees .
                                                    "&amp;minDeclMinutes=" . $minDeclMinutes .
                                                    "&amp;minDeclSeconds=" . $minDeclSeconds .
                                                    "&amp;minsize=" . $minsize .
                                                    "&amp;size_min_units=" . $size_min_units .
                                                    "&amp;maxsize=" . $maxsize .
                                                    "&amp;size_max_units=" . $size_max_units .
                                                    "&amp;description=" . $description .
                                                    "&amp;atlas=" . $atlas .
                                                    "&amp;page=" . $page .
                                                    "&amp;drawings=" . $drawings .
       	                                            "&amp;minvisibility=" . $minvisibility .
              	                                    "&amp;maxvisibility=" . $maxvisibility .
																				            "&amp;seen=" . $seenpar . 
                                                    "&amp;sort=observerid&amp;previous=$previous\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObservationsHeader2) . "\">" .
                                                    LangOverviewObservationsHeader2 . "</a></td>\n";

         // INSTRUMENT

         echo "<td><a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=" . urlencode($catalogue) .
                                                    "&amp;instrument=" . urlencode($instrument) .
                                                    "&amp;number=" . urlencode($number) .
                                                    "&amp;object=" . urlencode($object) . 
                                                    "&amp;lco=" . $_SESSION['lco'] . 
                                                    "&amp;observer=" . urlencode($observer) .
                                                    "&amp;site=" . urlencode($site) .
                                                    "&amp;minyear=" . $minyear .
                                                    "&amp;minmonth=" . $minmonth .
                                                    "&amp;minday=" . $minday .
                                                    "&amp;maxyear=" . $maxyear .
                                                    "&amp;maxmonth=" . $maxmonth .
                                                    "&amp;maxday=" . $maxday .
                                                    "&amp;maxdiameter=" . $maxdiameter .
                                                    "&amp;maxdiameterunits=" . $maxdiameterunits .
                                                    "&amp;mindiameter=" . $mindiameter .
                                                    "&amp;mindiameterunits=" . $mindiameterunits .
                                                    "&amp;type=" . $type .
                                                    "&amp;con=" . $con .
                                                    "&amp;minLatDegrees=" . $minLatDegrees .
		                                                "&amp;minLatMinutes=" . $minLatMinutes .
		                                                "&amp;minLatSeconds=" . $minLatSeconds .
	                                                  "&amp;maxLatDegrees=" . $maxLatDegrees .
			                                              "&amp;maxLatMinutes=" . $maxLatMinutes .
			                                              "&amp;maxLatSeconds=" . $maxLatSeconds .
                                                    "&amp;maxmag=" . $maxmag .
                                                    "&amp;minmag=" . $minmag .
                                                    "&amp;maxsb=" . $maxsb .
                                                    "&amp;minsb=" . $minsb .
                                                    "&amp;minRAhours=" . $minRAhours .
                                                    "&amp;minRAminutes=" . $minRAminutes .
                                                    "&amp;minRAseconds=" . $minRAseconds .
                                                    "&amp;maxRAhours=" . $maxRAhours .
                                                    "&amp;maxRAminutes=" . $maxRAminutes .
                                                    "&amp;maxRAseconds=" . $maxRAseconds .
                                                    "&amp;maxDeclDegrees=" . $maxDeclDegrees .
                                                    "&amp;maxDeclMinutes=" . $maxDeclMinutes .
                                                    "&amp;maxDeclSeconds=" . $maxDeclSeconds .
                                                    "&amp;minDeclDegrees=" . $minDeclDegrees .
                                                    "&amp;minDeclMinutes=" . $minDeclMinutes .
                                                    "&amp;minDeclSeconds=" . $minDeclSeconds .
                                                    "&amp;minsize=" . $minsize .
                                                    "&amp;size_min_units=" . $size_min_units .
                                                    "&amp;maxsize=" . $maxsize .
                                                    "&amp;size_max_units=" . $size_max_units .
                                                    "&amp;description=" . $description .
                                                    "&amp;atlas=" . $atlas .
                                                    "&amp;page=" . $page .
                                                    "&amp;drawings=" . $drawings .
	                                            "&amp;minvisibility=" . $minvisibility .
           	                                    "&amp;maxvisibility=" . $maxvisibility .
																				            "&amp;seen=" . $seenpar . 
                                                    "&amp;sort=instrumentid&amp;previous=$previous\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObservationsHeader3) . "\">" .
                                                    LangOverviewObservationsHeader3 . "</a></td>\n";

         // DATE

         echo "<td><a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=" . urlencode($catalogue) .
                                                    "&amp;instrument=" . urlencode($instrument) .
                                                    "&amp;number=" . urlencode($number) .
                                                    "&amp;object=" . urlencode($object) . 
                                                    "&amp;lco=" . $_SESSION['lco'] . 
                                                    "&amp;observer=" . urlencode($observer) .
                                                    "&amp;site=" . urlencode($site) .
                                                    "&amp;minyear=" . $minyear .
                                                    "&amp;minmonth=" . $minmonth .
                                                    "&amp;minday=" . $minday .
                                                    "&amp;maxyear=" . $maxyear .
                                                    "&amp;maxmonth=" . $maxmonth .
                                                    "&amp;maxday=" . $maxday .
                                                    "&amp;maxdiameter=" . $maxdiameter .
                                                    "&amp;maxdiameterunits=" . $maxdiameterunits .
                                                    "&amp;mindiameter=" . $mindiameter .
                                                    "&amp;mindiameterunits=" . $mindiameterunits .
                                                    "&amp;type=" . $type .
                                                    "&amp;con=" . $con .
                                                    "&amp;minLatDegrees=" . $minLatDegrees .
		                                                "&amp;minLatMinutes=" . $minLatMinutes .
		                                                "&amp;minLatSeconds=" . $minLatSeconds .
	                                                  "&amp;maxLatDegrees=" . $maxLatDegrees .
			                                              "&amp;maxLatMinutes=" . $maxLatMinutes .
			                                              "&amp;maxLatSeconds=" . $maxLatSeconds .
                                                    "&amp;maxmag=" . $maxmag .
                                                    "&amp;minmag=" . $minmag .
                                                    "&amp;maxsb=" . $maxsb .
                                                    "&amp;minsb=" . $minsb .
                                                    "&amp;minRAhours=" . $minRAhours . 
                                                    "&amp;minRAminutes=" . $minRAminutes . 
                                                    "&amp;minRAseconds=" . $minRAseconds . 
                                                    "&amp;maxRAhours=" . $maxRAhours . 
                                                    "&amp;maxRAminutes=" . $maxRAminutes . 
                                                    "&amp;maxRAseconds=" . $maxRAseconds .
                                                    "&amp;maxDeclDegrees=" . $maxDeclDegrees .
                                                    "&amp;maxDeclMinutes=" . $maxDeclMinutes .
                                                    "&amp;maxDeclSeconds=" . $maxDeclSeconds .
                                                    "&amp;minDeclDegrees=" . $minDeclDegrees .
                                                    "&amp;minDeclMinutes=" . $minDeclMinutes .
                                                    "&amp;minDeclSeconds=" . $minDeclSeconds .
                                                    "&amp;minsize=" . $minsize .
                                                    "&amp;size_min_units=" . $size_min_units .
                                                    "&amp;maxsize=" . $maxsize .
                                                    "&amp;size_max_units=" . $size_max_units .
                                                    "&amp;description=" . $description .
                                                    "&amp;atlas=" . $atlas .
                                                    "&amp;page=" . $page .
                                                    "&amp;drawings=" . $drawings .
	                                                  "&amp;minvisibility=" . $minvisibility .
           	                                        "&amp;maxvisibility=" . $maxvisibility .
																				            "&amp;seen=" . $seenpar . 
                                                    "&amp;sort=date&amp;previous=$previous\" title=\"" . LangSortOn . mb_strtolower(LangOverviewObservationsHeader4) . "\">" .
                                                    LangOverviewObservationsHeader4 . "</a></td>";
				 if($_SESSION['lco']!="O")
				   echo("<td></td>\n");
				 else
				   echo("<td width=\"15%\">" . LangOverviewObservationsHeader8 . "</td>\n".
                 "<td width=\"15%\">" . LangOverviewObservationsHeader9 . "</td>\n".
                 "<td width=\"15%\">" . LangOverviewObservationsHeader5. "</td>\n");
         echo "</tr>\n";
         while(list ($key, $value) = each($obs)) // go through observations array
         {
            if($count >= $min && $count < $max)
            { 
						  if($_SESSION['lco']=="L")
                $observations->showOverviewObservation($value, $count, $link . "&amp;min=" . $min, $myList);
							elseif($_SESSION['lco']=="C")
                $observations->showCompactObservation($value, $link . "&amp;min=" . $min, $myList);
							elseif($_SESSION['lco']=="O")
                $observations->showCompactObservationLO($value, $link . "&amp;min=" . $min, $myList);
            }
            $count++; // increase counter
         }
         echo ("</table>\n");
      }
			
      list($min, $max) = $util->printNewListHeader($obs, $link, $min, $step, $total);

      $_SESSION['observation_query'] = $obs;
      echo "<p><a href=\"deepsky/observations.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a> - ";
      echo "<a href=\"deepsky/observations.csv\" target=\"new_window\">".LangExecuteQueryObjectsMessage5."</a> - ";
      echo "<a href=\"deepsky/index.php?indexAction=query_objects&amp;source=observation_query\">".LangExecuteQueryObjectsMessage9."</a> - ";

   }
   else // NO OBSERVATIONS FOUND 
   {
      echo("</h2>\n");
			echo LangObservationNoResults; 
			echo "<p>";
   }
   echo("<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>");
}
else // no search fields filled in
{
   echo("</h2>\n");
	 echo "<p>" . LangObservationQueryError1 . "</p>";
   echo "<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>";
   echo " " . LangObservationOR . " ";
   echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=*\">" . LangObservationQueryError3 . "</a>";
}
echo("</div>\n</div>\n</body>\n</html>");
?>
