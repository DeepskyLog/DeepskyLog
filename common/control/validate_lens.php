<?php

// validate_lens.php
// checks if the add new lens or change lens form is correctly filled in
// version 3.2: WDM, 11/05/2008

session_start(); // start session

include "../../lib/lenses.php";
include "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

if (!$_POST['lensname'] || !$_POST['factor'])
{
      $_SESSION['message'] = LangValidateEyepieceMessage1; 
      header("Location:../error.php");
}
else
{
$lenses = new Lenses; // create new Lenses object

if(array_key_exists('add', $_POST) && $_POST['add'])
{
      // fill database
    	$id = $lenses->addLens($_POST['lensname'], $_POST['factor']);

      $lenses->setObserver($id, $_SESSION['deepskylog_id']);
      $_SESSION['message'] = LangValidateLensMessage2;
  	  $_SESSION['title'] = LangValidateLensMessage3;
}
if(array_key_exists('change', $_POST) && $_POST['change'])
{
          $lenses->setName($_POST['id'], $_POST['lensname']);
          $lenses->setFactor($_POST['id'], $_POST['factor']);
          $lenses->setObserver($_POST['id'], $_SESSION['deepskylog_id']);
          $_SESSION['message'] = LangValidateLensMessage5;
          $_SESSION['title'] = LangValidateLensMessage4;
}
header("Location:../add_lens.php");
}
?>
