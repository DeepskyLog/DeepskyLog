<?php

// add_csv_observations.php
// adds observations from a csv file to the database

session_start(); // start session

include_once "../../lib/observations.php";
include_once "../../lib/observers.php";
include_once "../../lib/objects.php";
include_once "../../lib/locations.php";
include_once "../../lib/instruments.php";
include_once "../../lib/filters.php";
include_once "../../lib/eyepieces.php";
include_once "../../lib/lenses.php";
include_once "../../lib/setup/vars.php"; // sets language for errors
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obs = new Observers;
$observation = new Observations;
$obj = new Objects;
$loc = new Locations;
$inst = new Instruments;
$eyep = new Eyepieces;
$filt = new Filters;
$lns = new Lenses;

if($_FILES['csv']['tmp_name'] != "")
{
   $csvfile = $_FILES['csv']['tmp_name'];
}

$data_array = file($csvfile); 
for ( $i = 0; $i < count($data_array); $i++ ) 
{ 
  $parts_array[$i] = explode(";",$data_array[$i]); 
}

// Get the arrays with the objects, locations and instruments
for ( $i = 1; $i < count($parts_array); $i++)
{
  $objects[$i] = $parts_array[$i][0];
  $locations[$i] = $parts_array[$i][4];
  $instruments[$i] = $parts_array[$i][5];
  $filters[$i] = $parts_array[$i][7];
  $eyepieces[$i] = $parts_array[$i][6];
  $lenses[$i] = $parts_array[$i][8];
}
//$objects = array_unique($objects);

// JV 20060224 add check to see if $objects contains data or not
// -> show error page

if(!is_array($objects))
{
  $_SESSION['message'] = LangInvalidCSVfile;
  header("Location:../../common/error.php");
}
else
{
  $objects = array_values($objects);
  $locations = array_unique($locations);
  $locations = array_values($locations);
  $instruments = array_unique($instruments);
  $instruments = array_values($instruments);
  $filters = array_unique($filters);
  $filters = array_values($filters);
  $eyepieces = array_unique($eyepieces);
  $eyepieces = array_values($eyepieces);
  $lenses = array_unique($lenses);
  $lenses = array_values($lenses);

	$objectsMissing = array();
	$locationsMissing = array();
	$instrumentsMissing = array();
	$filtersMissing = array();
  $eyepiecesMissing = array();
  $lensesMissing = array();

  // Test if the objects, locations and instruments are available in the database
  $j = 0;


  for ( $i = 0;$i < count($objects); $i++)
  {
    $objectsquery = $obj->getExactObject($objects[$i]);

    if (count($objectsquery) == 0)
    {
      $objectsMissing[$j] = $objects[$i];
      $j++;
    }
    else
    {
      $correctedObjects[] = $objectsquery[0];
    }
  }

  // Check for existence of locations
  $j = 0;
	$temploc='';
  for ( $i = 0;$i < count($locations); $i++)
  {
		if(($temploc!=$locations[$i]) && ($loc->getLocationId($locations[$i], $_SESSION['deepskylog_id']) == -1))
		{
      $locationsMissing[$j] = $locations[$i];
      $j++;
    }
		else
		  $temploc = $locations[$i];
  }


  // Check for existence of instruments
  $j = 0;
  for ( $i = 0;$i < count($instruments); $i++)
  {
    if ($inst->getInstrumentId($instruments[$i], $_SESSION['deepskylog_id']) == -1)
    {
      $instrumentsMissing[$j] = $instruments[$i];
      $j++;
    }
  }

  // Check for the existence of the eyepieces
  $j = 0;
  for ( $i = 0;$i < count($eyepieces); $i++)
  {
    if ($eyepieces[$i] != "")
    {
      if ($eyep->getEyepieceId($eyepieces[$i], $_SESSION['deepskylog_id']) == -1)
      {
        $eyepiecesMissing[$j] = $eyepieces[$i];
        $j++;
      }
    }
  }

  // Check for the existence of the filters
  $j = 0;
  for ( $i = 0;$i < count($filters); $i++)
  {
    if ($filters[$i] != "")
    {
      if ($filt->getFilterId($filters[$i], $_SESSION['deepskylog_id']) == -1)
      {
        $filtersMissing[$j] = $filters[$i];
        $j++;
      }
    }
  }

  // Check for the existence of the eyepieces
  $j = 0;
  for ( $i = 0;$i < count($lenses); $i++)
  {
    if ($lenses[$i] != "")
    {
      if ($lns->getLensId($lenses[$i], $_SESSION['deepskylog_id']) == -1)
      {
        $lensesMissing[$j] = $lenses[$i];
        $j++;
      }
    }
  }

// error catching
  if ((count($objectsMissing) > 0) || (count($locationsMissing) > 0) || (count($instrumentsMissing) > 0) || (count($eyepiecesMissing) > 0) || (count($filtersMissing) > 0) || (count($lensesMissing) > 0))
  {
    $errormessage = "";
    $errormessage = $errormessage . LangCSVError1 . "<br />\n";
    if (count($objectsMissing) > 0)
    {
      $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError2." : ";
      $errormessage = $errormessage .  "<ul>";
      for ( $i = 0;$i < count($objectsMissing);$i++ )
      {
        $errormessage = $errormessage . "<li>".$objectsMissing[$i]."</li>";
      }
      $errormessage = $errormessage .  "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if (count($locationsMissing) > 0)
    {
      $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError3." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($locationsMissing);$i++ )
      {
        $errormessage = $errormessage . "<li>".$locationsMissing[$i]."</li>";
      }
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if (count($instrumentsMissing) > 0)
    {
      $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage . "<li>".LangCSVError4." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($instrumentsMissing);$i++ )
      {
        $errormessage = $errormessage . "<li>".$instrumentsMissing[$i]."</li>";
      }
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage . "</li>\n";
      $errormessage = $errormessage . "</ul>";
    }
    if (count($filtersMissing) > 0)
    {
      $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError5." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($filtersMissing);$i++ )
      {
        $errormessage = $errormessage . "<li>".$filtersMissing[$i]."</li>";
      }
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if (count($eyepiecesMissing) > 0)
    {
      $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError6." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($eyepiecesMissing);$i++ )
      {
        $errormessage = $errormessage . "<li>".$eyepiecesMissing[$i]."</li>";
      }
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    if (count($lensesMissing) > 0)
    {
      $errormessage = $errormessage . "<ul>";
      $errormessage = $errormessage .  "<li>".LangCSVError7." : ";
      $errormessage = $errormessage . "<ul>";
      for ( $i = 0;$i < count($lensesMissing);$i++ )
      {
        $errormessage = $errormessage . "<li>".$lensesMissing[$i]."</li>";
      }
      $errormessage = $errormessage . "</ul>";
      $errormessage = $errormessage .  "</li>\n";
      $errormessage = $errormessage .  "</ul>";
    }
    $_SESSION['message'] = $errormessage;
		header("Location:../../common/error.php");
  }
  else
  {
    $username = $obs->getFirstname($_SESSION['deepskylog_id']). " ".$obs->getObserverName($_SESSION['deepskylog_id']);
    for ( $i = 1; $i < count($parts_array); $i++)
    {
      $observername = $obs->getFirstname($parts_array[$i][1]). " ".$obs->getObserverName($parts_array[$i][1]);
      if ($parts_array[$i][1] == $username)
      {
        $instrum = $inst->getInstrumentId($parts_array[$i][5], $_SESSION['deepskylog_id']);
        $locat = $loc->getLocationId($parts_array[$i][4], $_SESSION['deepskylog_id']);
        $dates = sscanf($parts_array[$i][2], "%2d%c%2d%c%4d");
        $date = sprintf("%04d%02d%02d", $dates[4], $dates[2], $dates[0]);
        $times = sscanf($parts_array[$i][3], "%2d%c%2d");
        $time = sprintf("%02d%02d", $times[0], $times[2]);
//        if ($time == "0000")
//        {
//          $time = "0";
//        }
        if ($parts_array[$i][11] == "")
        {
          $parts_array[$i][11] = "0";
        }

        $obsid = $observation->addDSObservation($correctedObjects[$i - 1], 
				     $_SESSION['deepskylog_id'], 
					$instrum, 
					$locat, 
					$date, 
					$time, 
					htmlentities($parts_array[$i][13]), 
					$parts_array[$i][9], 
					$parts_array[$i][10],
					$parts_array[$i][11],
					$parts_array[$i][12]);

				if ($parts_array[$i][6] != "")
				{
					$observation->setEyepieceId($obsid, $eyep->getEyepieceId($parts_array[$i][6], $_SESSION['deepskylog_id']));
				}
				if ($parts_array[$i][7] != "")
				{
					$observation->setFilterId($obsid, $filt->getFilterId($parts_array[$i][7], $_SESSION['deepskylog_id']));
				}
				if ($parts_array[$i][8] != "")
				{
					$observation->setLensId($obsid, $lns->getLensId($parts_array[$i][8], $_SESSION['deepskylog_id']));
				}
      }
    }
    // upload successful
    header("Location:../index.php");
  }
}
?>
