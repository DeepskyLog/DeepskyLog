<?php

// validate_delete_observation.php
// deletes an observation
// Version 0.1: JV, 20040930

// Code cleanup - removed by David on 20080704
//include_once "../../lib/objects.php";


session_start(); // start session

include_once "../../lib/observations.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$observations = new Observations;

if (!$_GET['observationid'])
{
  unset($_SESSION['deepskylog_id']);
  header("Location:../index.php");
}
elseif(array_key_exists('observationid', $_GET) && $_GET['observationid']) // observationid given
{
  if($observations->getObserverId($_GET['observationid']) == $_SESSION['deepskylog_id']) // only allowed to delete your own observations
  {
    $observations->deleteDSObservation($_GET['observationid']);
    header("Location:../index.php?indexAction=result_selected_observations&catalogue=*");
  } // end if own observation.php
  else // try to delete an observation which doesn't belong to the observer logged in
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../index.php"); // back to entrance page
  }
}
?>
