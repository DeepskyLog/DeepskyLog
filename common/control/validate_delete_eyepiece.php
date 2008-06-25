<?php

// validate_delete_eyepiece.php
// deletes an eyepiece if no observations done with this eyepiece yet 
// version 3.2: WDM, 19/01/2008

session_start(); // start session

include_once "../../lib/eyepieces.php";
include_once "../../lib/observations.php";
include_once "../../lib/cometobservations.php";
include_once "../../lib/objects.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$eyepieces = new Eyepieces;
$observations = new Observations;
$cometobservations = new CometObservations;

if (!$_GET['eyepieceid']) // no eyepieceid given as a parameter
{
  unset($_SESSION['deepskylog_id']);
  header("Location:../index.php");
}
elseif(array_key_exists('eyepieceid', $_GET) && $_GET['eyepieceid']) // eyepieceid given
{
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
  array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $eyepieces->getObserver($_GET['eyepieceid'])) // only admin may delete locations 
  {
   $queries = array("eyepiece" => $_GET['eyepieceid'], "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries);
//   $comobs = $cometobservations->getObservationFromQuery($queries);

   if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
   {
    $eyepieces->deleteEyepiece($_GET['eyepieceid']);
    header("Location:../add_eyepiece.php");
   }
   else // still observations from given location 
   {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_eyepiece.php"); // back to entrance page
   }
  } 
  else // not logged in as an administrator 
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../add_eyepiece.php"); // back to entrance page
  }
}
?>
