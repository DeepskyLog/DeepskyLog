<?php

// validate_delete_location.php
// deletes a location if no observations done from this location yet 
// version 0.1: JV, 20050212

// Code cleanup - removed by David on 20080704
//include_once "../../lib/objects.php";


session_start(); // start session

include_once "../../lib/locations.php";
include_once "../../lib/observations.php";
include_once "../../lib/cometobservations.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$locations = new Locations;
$observations = new Observations;
$cometobservations = new CometObservations;

if (!$_GET['locationid']) // no locationid given as a parameter
{
  unset($_SESSION['deepskylog_id']);
  header("Location:../index.php");
}
elseif(array_key_exists('locationid', $_GET) && $_GET['locationid']) // locationid given
{
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
  array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $locations->getObserver($_GET['locationid'])) // only admin may delete locations 
  {
   $queries = array("location" => $_GET['locationid'], "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries, "", "1", "False", "D", "1");
   $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

   if(!sizeof($obs) > 0 && !sizeof($comobs) > 0) // no observations from location yet
   {
    $locations->deleteLocation($_GET['locationid']);
    header("Location:../add_site.php");
   }
   else // still observations from given location 
   {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_site.php"); // back to entrance page
   }
  } 
  else // not logged in as an administrator 
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_site.php"); // back to entrance page
  }
}
?>
