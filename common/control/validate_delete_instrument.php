<?php

// validate_delete_instrument.php
// deletes an instrument if no observations done with this instrument yet 
// version 3.2: WDM, 21/01/2008

// Code cleanup - removed by David on 20080704
//include_once "../../lib/objects.php";



session_start(); // start session

include_once "../../lib/instruments.php";
include_once "../../lib/observations.php";
include_once "../../lib/cometobservations.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$instruments = new Instruments;
$observations = new Observations;
$cometobservations = new CometObservations;

if (!$_GET['instrumentid']) // no instrumentid given as a parameter
{
  unset($_SESSION['deepskylog_id']);
  header("Location:../index.php");
}
elseif(array_key_exists('instrumentid', $_GET) && $_GET['instrumentid']) // instrumentid given
{
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
  array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $instruments->getObserverFromInstrument($_GET['insturmentid'])) // only admin may delete locations 
  {
   $queries = array("instrument" => $_GET['instrumentid'], "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries, "", "1", "False", "D", "1");
   $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

   if(!sizeof($obs) > 0 && !sizeof($comobs) > 0) // no observations from location yet
   {
    $instruments->deleteInstrument($_GET['instrumentid']);
    header("Location:../add_instrument.php");
   }
   else // still observations from given location 
   {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_instrument.php"); // back to entrance page
   }
  } 
  else // not logged in as an administrator 
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_instrument.php"); // back to entrance page
  }
}
?>