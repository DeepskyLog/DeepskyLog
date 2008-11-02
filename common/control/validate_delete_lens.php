<?php

// validate_delete_lens.php
// deletes a lens if no observations done with this lens yet 
// version 3.2: WDM, 21/01/2008

// Code cleanup - removed by David on 20080704
//include_once "../../lib/objects.php";


session_start(); // start session

include_once "../../lib/lenses.php";
include_once "../../lib/observations.php";
include_once "../../lib/cometobservations.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$lenses = new Lenses;
$observations = new Observations;
$cometobservations = new CometObservations;

if (!$_GET['lensid']) // no filterid given as a parameter
{
  unset($_SESSION['deepskylog_id']);
  header("Location:../index.php");
}
elseif(array_key_exists('lensid', $_GET) && $_GET['lensid']) // lensid given
{
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
  array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $lenses->getObserverFromLens($_GET['lensid'])) // only admin may delete locations 
  {
   $queries = array("lens" => $_GET['lensid'], "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries);
//   $comobs = $cometobservations->getObservationFromQuery($queries);

   if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
   {
    $lenses->deleteLens($_GET['lensid']);
    header("Location:../add_lens.php");
   }
   else // still observations with given lens 
   {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_lens.php"); // back to entrance page
   }
  } 
  else // not logged in as an administrator 
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_lens.php"); // back to entrance page
  }
}
?>
