<?php

// overview_observations.php
// generates an overview of all observations in the database
// version 0.4, WDM 20052011
// version 3.1, DE 20061205

include_once "../lib/cometobservations.php";
include_once "../lib/cometobjects.php";
include_once "../lib/setup/language.php";
include_once "../lib/instruments.php";
include_once "../lib/observers.php";
include_once "../lib/util.php";
include_once "../lib/setup/databaseInfo.php";

$observations = new CometObservations;
$objects = new CometObjects;
$instruments = new Instruments;
$observers = new Observers;
$util = new util;
$util->checkUserInput();

if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
{
  $sort = $_GET['sort'];
  $obs = $observations->getSortedObservations($sort);
}
else
{
   $sort = "date"; // standard sort on date
   $obs = $observations->getSortedObservations($sort);
   if(sizeof($obs) > 0)
   {
    krsort($obs);
   }
}

// save $obs as a session variable

$_SESSION['obs'] = $obs;
$_SESSION['observation_query'] = $obs;

echo("<div id=\"main\">\n<h2>");

echo (LangOverviewObservationsTitle); // page title

echo("</h2>\n");

if(isset($_GET['previous']))
{
 $previous = $_GET['previous'];
}
else
{
 $previous = 'date';
}

$count = 0; // counter for altering table colors
$link = "comets/all_observations.php?sort=".$sort."&amp;previous=".$previous;


if(isset($_GET['sort']) && isset($_GET['previous']) && ($_GET['previous'] == $_GET['sort'])) // reverse sort when pushed twice
{
   if(sizeof($obs) > 0)
   {
    krsort($obs);
   }
   $previous = ""; // reset previous field to sort on
}
else
{
   $previous = $sort;
}
if (isset($_GET['min']))
{
  $min=$_GET['min'];
}
else
{
  $min=0;
} 
list($min, $max) = $util->printListHeader($obs, $link, $min, 25, "");

if(sizeof($obs) > 0)
{
// OBJECT TABLE HEADERS

echo "<table>\n
      <tr class=\"type3\">\n
      <td><a href=\"comets/all_observations.php?sort=objectname&amp;previous=$previous\">" . LangOverviewObservationsHeader1 . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=observerid&amp;previous=$previous\">" . LangOverviewObservationsHeader2 . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=date&amp;previous=$previous\">" . LangOverviewObservationsHeader4 . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=mag&amp;previous=$previous\">" . LangNewComet1 . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=inst&amp;previous=$previous\">" . LangViewObservationField3 . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=coma&amp;previous=$previous\">" . LangViewObservationField19 . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=dc&amp;previous=$previous\">" . LangViewObservationField18b . "</a></td>\n
      <td><a href=\"comets/all_observations.php?sort=tail&amp;previous=$previous\">" . LangViewObservationField20b . "</a></td>\n
      <td></td>\n
      </tr>\n";

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

      // DATE

      if ($observers->getUseLocal($_SESSION['deepskylog_id']))
      {
       $date = sscanf($observations->getLocalDate($value), "%4d%2d%2d");
      }
      else
      {
       $date = sscanf($observations->getDate($value), "%4d%2d%2d");
      }

      // TIME
      if ($observers->getUseLocal($_SESSION['deepskylog_id']))
      {
       $time = sscanf(sprintf("%04d", $observations->getLocalTime($value)), "%2d%2d");
      }
      else
      {
       $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
      }

      // INSTRUMENT 
 
      $temp = $observations->getInstrumentId($value);
      $instrument = $instruments->getName($temp);
      $instrumentsize = round($instruments->getDiameter($temp), 0);
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
       $mag = sprintf("%01.1f", $mag);
       if($observations->getMagnitudeWeakerThan($value) == "1")
       {
         $mag = "[" . $mag;
       }
       if($observations->getMagnitudeUncertain($value) == "1")
       {
         $mag = $mag . ":";
       }
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
      if ($tail < -90)
      {
       $tail = '';
      }
      else
      {
       $tail = $tail."'";
      }

      // OUTPUT

      echo("<tr $typefield>\n
            <td><a href=\"comets/detail_object.php?object=" . $object . "\">" . $objects->getName($object) . "</a></td>\n
            <td><a href=\"common/detail_observer.php?user=" . $observer . "\">" . $observers->getFirstName($observer) . "&nbsp;" . $observers->getName($observer) . 
            "</a></td>\n<td>");

      echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));

      echo ("&nbsp;(");

      printf("%02d", $time[0]);

      echo (":");

      printf("%02d", $time[1]);

      if($instrument != InstrumentsNakedEye && $instrumentsize != "0" && $instrumentsize != "1")
      {
         $instrument = $instrument. "(" . $instrumentsize . "&nbsp;mm" . ")";
      }

      echo(")</td>\n
            <td>$mag</td>
            <td>$instrument</td>
            <td>$coma</td>
            <td>$dc</td>
            <td>$tail</td>
            <td><a href=\"comets/detail_observation.php?observation=" . $value . "\">details");

      // LINK TO DRAWING (IF AVAILABLE)

$upload_dir = 'cometdrawings';
$dir = opendir($upload_dir);

while (FALSE !== ($file = readdir($dir)))
{
   if ("." == $file OR ".." == $file)
   {
   continue; // skip current directory and directory above
   }
   if(fnmatch($value . "_resized.gif", $file) || fnmatch($value . "_resized.jpg",
$file) || fnmatch($value. "_resized.png", $file))
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

list($min, $max) = $util->printListHeader($obs, $link, $min, 25, "");

echo("</div>\n</body>\n</html>");

?>
