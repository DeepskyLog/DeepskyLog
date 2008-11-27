<?php

// validate_delete_filter.php
// deletes a filter if no observations done with this filter yet 
// version 3.2: WDM, 21/01/2008

// Code cleanup - removed by David on 20080704
//include_once "lib/objects.php";


session_start(); // start session

include_once "lib/filters.php";
include_once "lib/observations.php";
include_once "lib/cometobservations.php";
include_once "lib/setup/vars.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

$filters = new Filters;
$observations = new Observations;
$cometobservations = new CometObservations;

if (!$_GET['filterid']) // no filterid given as a parameter
{
  unset($_SESSION['deepskylog_id']);
  header("Location:index.php");
}
elseif(array_key_exists('filterid', $_GET) && $_GET['filterid']) // filterid given
{
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
  array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $filters->getObserverFromFilter($_GET['filterid'])) // only admin may delete locations 
  {
   $queries = array("filter" => $_GET['filterid'], "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries);
//   $comobs = $cometobservations->getObservationFromQuery($queries);

   if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
   {
    $filters->deleteFilter($_GET['filterid']);
    header("Location:add_filter.php");
   }
   else // still observations from given location 
   {
    unset($_SESSION['deepskylog_id']);
    header("Location: add_filter.php"); // back to entrance page
   }
  } 
  else // not logged in as an administrator 
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: add_filter.php"); // back to entrance page
  }
}
?>
