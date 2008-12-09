<?php

// validate_delete_observation.php
// deletes an observation
// Version 0.1: JV, 20040930

session_start(); // start session

include_once "../lib/cometobservations.php";
include_once "../lib/cometobjects.php";
include_once "../lib/setup/vars.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$cometobservations = new CometObservations;

if (!$_GET['observationid']) // not logged in
{
  unset($_SESSION['deepskylog_id']);
  header("Location:../index.php");
}
elseif($_GET['observationid']) // observationid given
{
  // only admins may delete a comet observation

  $role = $obs->getRole($_SESSION['deepskylog_id']);

  if ($role == RoleAdmin || $role == RoleCometAdmin)
  {
    $cometobservations->deleteObservation($_GET['observationid']);
    $_GET['indexAction']='comets_all_observations';
  }
  else // not logged in as admin 
  {
    unset($_SESSION['deepskylog_id']);
    header("Location: ../index.php"); // back to entrance page
  }
}
?>
