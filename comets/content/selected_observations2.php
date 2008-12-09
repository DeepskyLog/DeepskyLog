<?php

// selected_observations2.php
// generates an overview of selected observations in the database
// version 0.4: 2005/11/05, WDM

include_once "lib/observations.php";
include_once "lib/setup/language.php";
include_once "lib/instruments.php";
include_once "lib/observers.php";
include_once "lib/cometobjects.php";
include_once "lib/util.php";
include_once "lib/setup/databaseInfo.php";

$observations = new CometObservations;
$instruments = new Instruments;
$observers = new Observers;
$objects = new CometObjects;

$util = new util;
$util->checkUserInput();

// TITLE

echo("<div id=\"main\">\n<h2>");
echo(LangSelectedObservationsTitle2);
echo("</h2>\n");

if($_GET['observer'] || $_GET['instrument'] || $_GET['site'] || $_GET['minyear'] || $_GET['maxyear'] || ($_GET['mindiameter'] && $_GET['mindiameterunits']) || ($_GET['maxdiameter'] && $_GET['maxdiameterunits']) || $_GET['minmag'] || $_GET['maxmag'] || $_GET['description'] || $_GET['mindc'] || $_GET['maxdc'] || $_GET['mincoma'] || $_GET['maxcoma'] || $_GET['mintail'] || $_GET['maxtail'] || $_GET['object']) // at least 1 field to search on 
{

   if($_GET['minyear'] && $_GET['minmonth'] && $_GET['minday']) // exact date given
   {
     $mindate = $_GET['minyear'] . sprintf("%02d",$_GET['minmonth']) . sprintf("%02d",$_GET['minday']);
   }
   elseif($_GET['minyear'] && $_GET['minmonth']) // month and year given
   {
     $mindate = $_GET['minyear'] . sprintf("%02d",$_GET['minmonth']) . "00";
   }
   elseif($_GET['minyear']) // only year given
   {
     $mindate = $_GET['minyear'] . "0000";
   }
 
   if($_GET['maxyear'] && $_GET['maxmonth'] && $_GET['maxday']) // exact date given
   {
     $maxdate = $_GET['maxyear'] . sprintf("%02d",$_GET['maxmonth']) . sprintf("%02d",$_GET['maxday']);
   }
   elseif($_GET['maxyear'] && $_GET['maxmonth']) // month and year given 
   {
     $maxdate = $_GET['maxyear'] . sprintf("%02d",$_GET['maxmonth']) . "31";
   }
   elseif($_GET['maxyear']) // only year given
   {
     $maxdate = $_GET['maxyear'] . "1231";
   }

   if($_GET['mindiameter'] && ($_GET['mindiameterunits'] == "inch")) // convert minimum diameter in inches to mm 
   {
      $mindiam = $_GET['mindiameter'] * 25.4;
   }
   else
   {
      $mindiam = $_GET['mindiameter'];
   }

   if($_GET['maxdiameter'] && ($_GET['maxdiameterunits'] == "inch")) // convert maximum diameter in inches to mm
   {
      $maxdiam = $_GET['maxdiameter'] * 25.4;
   }
   else
   {
      $maxdiam = $_GET['maxdiameter'];
   }

   $maxmag = $_GET['maxmag'];
   $minmag = $_GET['minmag'];
   $description = $_GET['description'];
   $object = $_GET['object'];
   $mintail = $_GET['mintail'];
   $maxtail = $_GET['maxtail'];
   $mincoma = $_GET['mincoma'];
   $maxcoma = $_GET['maxcoma'];
   $mindc = $_GET['mindc'];
   $maxdc = $_GET['maxdc'];
   $observer = $_GET['observer'];

	 if(array_key_exists('instrument',$_GET) && $_GET['instrument'] != "")
	 {
      $instrument = $_GET['instrument'];
      $name = $instruments->getInstrumentName($instrument);
      $instrument = $instruments->getId($name, $_SESSION['deepskylog_id']);
	 }
	 else
	 {
	    $instrument = '';
	 }

	 if(array_key_exists('site',$_GET) && $_GET['site'] != "")
	 {
      $site = $_GET['site'];
      $name = $locations->getName($site);
      $site = $locations->getId($name, $_SESSION['deepskylog_id']);
	 }
	 else
	 {
	    $site = '';
	 }

// QUERY

$query = array("object" => $object,
               "observer" => $observer,
               "instrument" => $instrument,
	       "location" => $site,
               "mindate" => $mindate,
               "maxdate" => $maxdate, 
               "maxdiameter" => $maxdiam,
               "mindiameter" => $mindiam,
               "maxmag" => $maxmag,
               "minmag" => $minmag,
               "description" => $description,
               "mintail" => $mintail,
               "maxtail" => $maxtail,
               "mincoma" => $mincoma,
               "maxcoma" => $maxcoma,
               "mindc" => $mindc,
               "maxdc" => $maxdc);

// SORTING

if (isset($_GET['sort']))
{
 $sort = $_GET['sort'];
}
else
{
   $sort = "date";
   $_GET['sort'] = $sort;
}

// minimum

if (isset($_GET['min']))
{
 $min = $_GET['min'];
}
else
{
 $min = 0;
}

if ($observers->getUseLocal($_SESSION['deepskylog_id']))
{
  if ($mindate != "")
  {
    $mindate = $mindate - 1;
  }
  if ($maxdate != "")
  {
    $maxdate = $maxdate + 1;
  }
}


if(isset($cataloguesearch))
{
  if($cataloguesearch == "yes")
  {
    $obs = $observations->getObservationFromQuery($query,$sort,0); // LIKE
  }
  else
  {
    $obs = $observations->getObservationFromQuery($query,$sort); // EXACT MATCH
  }
}
else
{
  $obs = $observations->getObservationFromQuery($query,$sort); // EXACT MATCH
}

   // Dates can changes when we use local time!
   if ($observers->getUseLocal($_SESSION['deepskylog_id']))
   {
     if ($mindate != "" || $maxdate != "")
     {
       if ($mindate != "")
       {
        $mindate = $mindate + 1;
       }
       if ($maxdate != "")
       {
        $maxdate = $maxdate - 1;
       }

       $newkey = 0;

       $new_obs = Array();

       while(list ($key, $value) = each($obs)) // go through observations array
       {
         $newdate = $observations->getLocalDate($value);

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


if(sizeof($obs) > 0)
{
  krsort($obs);
}

// the code below is very strange but works

if(isset($_GET['previous']))
{
 $previous = $_GET['previous'];
}
else
{
 $previous = '';
}

$link = "comets/result_selected_observations.php?object=" . $_GET['object'] . 
                                         "&amp;observer=" . $_GET['observer'] . 
                                         "&amp;instrument=" . $_GET['instrument'] . 
                                         "&amp;site=" . $_GET['site'] . 
                                         "&amp;minyear=" . $_GET['minyear'] . 
                                         "&amp;minmonth=" . $_GET['minmonth'] . 
                                         "&amp;minday=" . $_GET['minday'] . 
                                         "&amp;maxyear=" . $_GET['maxyear'] . 
                                         "&amp;maxmonth=" . $_GET['maxmonth'] . 
                                         "&amp;maxday=" . $_GET['maxday'] . 
                                         "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                         "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                         "&amp;mindiameter=" . $_GET['mindiameter'] .
                                         "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                         "&amp;maxmag=" . $_GET['maxmag'] .
                                         "&amp;minmag=" . $_GET['minmag'] .
                                         "&amp;description=" . $_GET['description'] .
                                         "&amp;sort=" . $sort . 
                                         "&amp;mindc=" . $_GET['mindc'] .
                                         "&amp;maxdc=" . $_GET['maxdc'] .
                                         "&amp;mincoma=" . $_GET['mincoma'] .
                                         "&amp;maxcoma=" . $_GET['maxcoma'] .
                                         "&amp;mintail=" . $_GET['mintail'] .
                                         "&amp;maxtail=" . $_GET['maxtail'] .
                                         "&amp;previous=" . $previous;

$step = 25;

list($min, $max) = $util->printListHeader($obs, $link, $min, $step, "");

if(($sort != '') && $previous == $_GET['sort']) // reverse sort when pushed twice
{
   if(sizeof($obs) > 0)
   {
    $obs = array_reverse($obs, true);
   }
   else
   {
     krsort($obs);
     reset($obs);
   }
   $previous = ""; // reset previous field to sort on
}
else
{
   $previous = $sort;
}


if(sizeof($obs) > 0)
{
$count = 0; // counter for altering table colors
 
if(sizeof($obs) > 0) // ONLY WHEN OBSERVATIONS AVAILABLE
{

// LINKS TO SORT ON OBSERVATION TABLE HEADERS

echo "<table>\n";

echo "<tr class=\"type3\">\n";

// OBJECT NAME

echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] . 
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] . 
        	                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
	                                            "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=objectid&amp;previous=$previous\">" . 
                                                    LangOverviewObservationsHeader1 . "</a></td>\n";

// OBSERVER

echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=observerid&amp;previous=$previous\">" .
                                                    LangOverviewObservationsHeader2 . "</a></td>\n";

// DATE

echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=date&amp;previous=$previous\">" .
                                                    LangOverviewObservationsHeader4 . "</a></td>\n";

// MAGNITUDE
echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=mag&amp;previous=$previous\">" .
                                                    LangNewComet1 . "</a></td>\n";

// INSTRUMENT
echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=inst&amp;previous=$previous\">" .
                                                    LangViewObservationField3 . "</a></td>\n";

// COMA
echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=coma&amp;previous=$previous\">" .
                                                    LangViewObservationField19 . "</a></td>\n";

// DC
echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=dc&amp;previous=$previous\">" .
                                                    LangViewObservationField18b . "</a></td>\n";

// TAIL
echo "<td><a href=\"comets/result_selected_observations.php?object=" . $_GET['object'] .
                                                    "&amp;instrument=" . $_GET['instrument'] .
                                                    "&amp;observer=" . $_GET['observer'] .
                                                    "&amp;site=" . $_GET['site'] .
                                                    "&amp;minyear=" . $_GET['minyear'] .
                                                    "&amp;minmonth=" . $_GET['minmonth'] .
                                                    "&amp;minday=" . $_GET['minday'] .
                                                    "&amp;maxyear=" . $_GET['maxyear'] .
                                                    "&amp;maxmonth=" . $_GET['maxmonth'] .
                                                    "&amp;maxday=" . $_GET['maxday'] .
                                                    "&amp;maxdiameter=" . $_GET['maxdiameter'] .
                                                    "&amp;maxdiameterunits=" . $_GET['maxdiameterunits'] .
                                                    "&amp;mindiameter=" . $_GET['mindiameter'] .
                                                    "&amp;mindiameterunits=" . $_GET['mindiameterunits'] .
                                                    "&amp;maxmag=" . $_GET['maxmag'] .
                                                    "&amp;minmag=" . $_GET['minmag'] .
                                                    "&amp;description=" . $_GET['description'] .
                                                    "&amp;mindc=" . $_GET['mindc'] .
                                                    "&amp;maxdc=" . $_GET['maxdc'] .
                                                    "&amp;mincoma=" . $_GET['mincoma'] .
                                                    "&amp;maxcoma=" . $_GET['maxcoma'] .
                                                    "&amp;mintail=" . $_GET['mintail'] .
                                                    "&amp;maxtail=" . $_GET['maxtail'] .
                                                    "&amp;sort=tail&amp;previous=$previous\">" .
                                                    LangViewObservationField20b . "</a></td>\n<td></td>\n</tr>\n";


while(list ($key, $value) = each($obs)) // go through observations array
{
   if($count >= $min && $count < $max)
   { 
      if ($count % 2)
      {
         $typefield = "class=\"type1\"";
      }
      else
      {
         $typefield = "class=\"type2\"";
      }

      // OBJECT 

      $object = $observations->getObjectId($value);

      // OBSERVER 

      $observer = $observations->getObserverId($value);

      // INSTRUMENT 
 
      $temp = $observations->getInstrumentId($value);
      $instrument = $instruments->getInstrumentName($temp);
      $instrumentsize = $instruments->getDiameter($temp);
      if ($instrument == "Naked eye")
      {
       $instrument = InstrumentsNakedEye;
      }
  
      // MAGNITUDE

      $mag = $observations->getMagnitude($value);

      if ($mag < -90)
      {
       $mag = '';
      }
      else
      {
       $mag = sprintf("%2.01f", $mag);
      }

      // COMA

      $coma = $observations->getComa($value);
      if ($coma < -90)
      {
       $coma = '';
      }
      else
      {
       $coma = $coma."'";
      }

      // DC

      $dc = $observations->getDc($value);

      if ($dc < -90)
      {
       $dc = '';
      }

      // TAIL

      $tail = $observations->getTail($value);
      if ($tail > -90)
      {
       $tail = $tail."'";
      }
      else
      { 
       $tail = '';
      }

      // OUTPUT

      echo("<tr $typefield>\n
            <td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($object) . "\">" . $objects->getName($object) . "</a></td>\n
            <td><a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=" . urlencode($observer) . "\">" . $observers->getFirstName($observer) . "&nbsp;" . $observers->getObserverName($observer) . "</a></td>\n
            <td>");

      if($instrument != InstrumentsNakedEye && $instrument != "")
      {
         $instrument = $instrument. " (" . $instrumentsize . "&nbsp;mm" . ")";
      }



      if ($observers->getUseLocal($_SESSION['deepskylog_id']))
      {
        $date = sscanf($observations->getLocalDate($value), "%4d%2d%2d");
      }
      else
      {
        $date = sscanf($observations->getDate($value), "%4d%2d%2d");
      }

      echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));

      // TIME

      if ($observers->getUseLocal($_SESSION['deepskylog_id']))
      {
        $time = sscanf(sprintf("%04d", $observations->getLocalTime($value)), "%2d%2d");
      }
      else
      {
        $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
      }

      echo("&nbsp;(");

      printf("%02d", $time[0]);

      echo (":");

      printf("%02d", $time[1]);

      $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");

      echo(")");

      echo("</td>\n
            <td>$mag</td>
            <td>$instrument</td>
            <td>$coma</td>
            <td>$dc</td>
            <td>$tail</td>
            <td><a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

      // LINK TO DRAWING (IF AVAILABLE)
 
      $upload_dir = 'cometdrawings';
      $dir = opendir($instDir."comets/".$upload_dir);
 
      while (FALSE !== ($file = readdir($dir)))
      {
        if ("." == $file OR ".." == $file)
        {
          continue; // skip current directory and directory above
        }
        if(fnmatch($value . "_resized.gif", $file) || fnmatch($value . "_resized.jpg", $file) || fnmatch($value. "_resized.png", $file))
        {
          echo("&nbsp;+&nbsp;");
          echo LangDrawing; 
        }
      }

     echo("</a></td>\n</tr>\n");

     }

    $count++; // increase counter
  }

  echo ("</table>\n");
  }

$_SESSION['observation_query'] = $obs;

echo "<p><a href=\"comets/observations.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a></p>";
//echo "<p><a href=\"comets/observations.icq\" target=\"new_window\">".LangExecuteQueryObjectsMessage7."</a></p>";

}
else // NO OBSERVATIONS FOUND 
{
  echo "<p>" . LangObservationNoResults . "</p>"; 
}
echo("<p><a href=\"comets/query_observations.php\">" . LangObservationQueryError2 . "</a></p>");
}
else // no search fields filled in
{
   echo "<p>" . LangObservationQueryError1 . "</p>";
   echo "<p><a href=\"comets/query_observations.php\">" . LangObservationQueryError2 . "</a>";
   echo " " . LangObservationOR . " ";
   echo "<a href=\"".$baseURL."index.php?indexAction=comets_all_observations\">" . LangObservationQueryError3 . "</a></p>";
}
echo("</div>\n</div>\n</body>\n</html>");
?>
