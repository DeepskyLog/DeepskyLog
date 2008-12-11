<?php

// selected_observations.php
// generates an overview of selected observations in the database
// version 0.7: 2005/11/30, JV 

// include statements

include_once "lib/cometobservations.php";
include_once "lib/setup/language.php";
include_once "lib/instruments.php";
include_once "lib/observers.php";
include_once "lib/cometobjects.php";
include_once "lib/util.php";
include_once "lib/setup/databaseInfo.php";

// creation of objects

$observations = new CometObservations;
$instruments = new Instruments;
$observers = new Observers;
$objects = new CometObjects;
$util = new util;
$util->checkUserInput();

// selection of all observations of one object

echo("<div id=\"main\">\n<h2>");

if(isset($_GET['objectname']))
{ 
  echo (LangSelectedObservationsTitle . $objects->getName($_GET['objectname'])); // page title
  echo("</h2>\n");

  // OBJECT TABLE HEADERS

  $queries = array("object" => $objects->getName($_GET['objectname'])); // sql query

  if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
  {
    $sort = $_GET['sort'];
    $obs = $observations->getObservationFromQuery($queries, $sort);
  }
  else
  {
    $sort = "id"; // standard sort on insertion date
    $obs = $observations->getObservationFromQuery($queries);
    if(sizeof($obs) > 0)
    {
      krsort($obs);
    } 
  }

  if(isset($_GET['previous']))
  {
    $prev = $_GET['previous'];
    $previous = $_GET['previous'];
  }
  else
  {
    $prev = '';
    $previous = '';
  }

  if(isset($_GET['sort']))
  {
    $sort = $_GET['sort'];
  }
  else
  {
    $sort = '';
  }

  if(($sort != '') && $previous == $sort) // reverse sort when pushed twice
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

  // save $obs as a session variable

  $_SESSION['obs'] = $obs;
  $_SESSION['observation_query'] = $obs;

  $count = 0; // counter for altering table colors

   if(isset($_GET['min']))
   {
      $min = $_GET['min'];
   } 
   else
   {
      $min = 0;
   }

  $link = "".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=".$sort."&amp;previous=".$prev;

  list($min, $max) = $util->printListHeader($obs, $link, $min, 25, "");

  if(sizeof($obs) > 0)
  {
    echo "<table>\n
      <tr class=\"type3\">\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=objectid&amp;previous=$previous\">" . LangOverviewObservationsHeader1 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=observerid&amp;previous=$previous\">" . LangOverviewObservationsHeader2 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=date&amp;previous=$previous\">" . LangOverviewObservationsHeader4 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=mag&amp;previous=$previous\">" . LangNewComet1 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=inst&amp;previous=$previous\">" .LangViewObservationField3 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=coma&amp;previous=$previous\">" . LangViewObservationField19 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=dc&amp;previous=$previous\">" . LangViewObservationField18b . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=tail&amp;previous=$previous\">" . LangViewObservationField20b . "</a></td>\n
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

        $object = $observations->getObjectId($value); // overhead as this is every time the same object?!

        // OUTPUT

        echo("<tr $typefield>\n
            <td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($object) . "\">" . $objects->getName($object) . "</a></td>\n");

        // OBSERVER

        $observer = $observations->getObserverId($value);

        echo("<td>");

        echo("<a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=" . urlencode($observer) . "\">" . $observers->getFirstName($observer) . "&nbsp;" . $observers->getObserverName($observer) . "</a>");

        echo("</td>");

        // DATE

        if ($observers->getUseLocal($_SESSION['deepskylog_id']))
        {
          $date = sscanf($observations->getLocalDate($value), "%4d%2d%2d");
        }
        else
        {
          $date = sscanf($observations->getDate($value), "%4d%2d%2d");
        }


        echo("<td>");

        echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));

        // TIME

        echo(" (");

        if ($observers->getUseLocal($_SESSION['deepskylog_id']))
        {
         $time = sscanf(sprintf("%04d", $observations->getLocalTime($value)), "%2d%2d");
        }
        else
        {
         $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
        }

         printf("%02d", $time[0]);

         echo (":");

         printf("%02d", $time[1]);

         $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");

     echo(")</td>");

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
       $mag = sprintf("%01.1f", $observations->getMagnitude($value));
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

      if($instrument != InstrumentsNakedEye && $instrument != "")
      {
         $instrument = $instrument . " (" . $instrumentsize . "&nbsp;mm" . ")";
      }

     echo(" <td>$mag</td>
            <td>$instrument</td>
            <td>$coma</td>
            <td>$dc</td>
            <td>$tail</td>");

     // DETAILS

     echo("<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

      // LINK TO DRAWING (IF AVAILABLE)

   echo("</a></td>\n</tr>\n");

   }

   $count++; // increase counter
}

echo ("</table>\n");
echo "<p><a href=\"".$baseURL."cometobservations.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a></p>";
}

else // no observations of object
{
   echo LangNoObservations;
}
echo "</div></body></html>";

}
elseif($_GET['user']) // selection of all observations of one observer 
{
echo (LangSelectedObservationsTitle . $observers->getFirstName($_GET['user']) . "&nbsp;" . $observers->getObserverName($_GET['user'])); // page title
echo("</h2>\n");

// OBJECT TABLE HEADERS

// NEW BEGIN

$query = array("observer" => $_GET['user']);

if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
{
  $sort = $_GET['sort'];
  $obs = $observations->getObservationFromQuery($query, $sort);
}
else
{
   $sort = "id"; // standard sort on date
   $obs = $observations->getObservationFromQuery($query, $sort);
   if(sizeof($obs) > 0)
   {
   krsort($obs);
   }
}


// save $obs as a session variable

$_SESSION['obs'] = $obs;
$_SESSION['observation_query'] = $obs;

$count = 0; // counter for altering table colors

if (isset($_GET['sort']))
{
 $sort = $_GET['sort'];
}
else
{
 $sort = '';
}
if (isset($_GET['min']))
{
 $min = $_GET['min'];
}
else
{
 $min = '';
}

if(isset($_GET['previous']))
{
 $previous = $_GET['previous'];
}
else
{
 $previous = '';
}

 $link = "".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=".$sort."&amp;previous=".$previous;
list($min, $max) = $util->printListHeader($obs, $link, $min, 25, "");

if(($sort != '') && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
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
if(sizeof($obs) > 0)
{
// OBJECT TABLE HEADERS
echo "<table>\n
      <tr class=\"type3\">\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=objectid&amp;previous=$previous\">" . LangOverviewObservationsHeader1 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=date&amp;previous=$previous\">" . LangOverviewObservationsHeader4 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=mag&amp;previous=$previous\">" . LangNewComet1 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=inst&amp;previous=$previous\">" . LangViewObservationField3 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=coma&amp;previous=$previous\">" . LangViewObservationField19 . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=dc&amp;previous=$previous\">" . LangViewObservationField18b . "</a></td>\n
      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=tail&amp;previous=$previous\">" . LangViewObservationField20b . "</a></td>\n
      <td></td>
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

      // OUTPUT

      echo("<tr $typefield>\n
            <td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($object) . "\">" . $objects->getName($object) . "</a></td>\n
            <td>");

      // DATE

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

      echo("&nbsp;(");

        if ($observers->getUseLocal($_SESSION['deepskylog_id']))
        {
         $time = sscanf(sprintf("%04d", $observations->getLocalTime($value)), "%2d%2d");
        }
        else
        {
         $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
        }

         printf("%02d", $time[0]);

         echo (":");

         printf("%02d", $time[1]);

     echo(")</td>");

      // INSTRUMENT

      $temp = $observations->getInstrumentId($value);
      $instrument = $instruments->getInstrumentName($temp);
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

     echo(" <td>$mag</td>
            <td>$instrument</td>
            <td>$coma</td>
            <td>$dc</td>
            <td>$tail</td>");

     // DETAILS

     echo("<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

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



$_SESSION['observation_query'] = $obs;
echo "<p><a href=\"".$baseURL."cometobservations.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a></p>";
//echo "<p><a href=\"".$baseURL."cometobservations.icq\" target=\"new_window\">".LangExecuteQueryObjectsMessage7."</a></p>";

}

echo "</div></body></html>";

}
?>
