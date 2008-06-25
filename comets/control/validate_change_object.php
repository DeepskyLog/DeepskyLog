<?php

// validate_change_object.php
// checks if the change oject form is correctly filled in
// Version 0.1: JV, 20060428

session_start(); // start session

include_once "../../lib/cometobjects.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new CometObjects;

if(!$_POST['name'])
{
  // error
  $_SESSION['message'] = LangValidateObservationMessage1;
  header("Location:../../common/error.php");
}
else
{
  if($_POST['object']) // comet id given
  {
  // only admins may change a comet 

  $role = $obs->getRole($_SESSION['deepskylog_id']);

  if ($role == RoleAdmin || $role == RoleCometAdmin)
  {
     $name = $_POST['name'];
     $icqname = $_POST['icqname'];
     
     $objects->setName($_POST['object'], $name);
     $objects->setIcqName($_POST['object'], $icqname);

     header("Location:../detail_object.php?object=" . $_POST['object']);

   }
   else // not logged in as admin
   {
      unset($_SESSION['deepskylog_id']);
      header("Location: ../index.php"); // back to entrance page
   }
   }
   else // no comet id given
   {
      unset($_SESSION['deepskylog_id']);
      header("Location: ../index.php"); // back to entrance page
   }
}
?>
