<?php

// validate_eyepiece.php
// checks if the add new eyepiece or change eyepiece form is correctly filled in
// version 3.2: WDM, 16/01/2008

session_start(); // start session

include "../../lib/filters.php";
include "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

if (!$_POST['filtername'] || !$_POST['type'])
{
      $_SESSION['message'] = LangValidateEyepieceMessage1; 
      header("Location:../error.php");
}
else
{
$filters = new Filters; // create new Eyepieces object

if(array_key_exists('add', $_POST) && $_POST['add'])
{
      // fill database
    	$id = $filters->addFilter($_POST['filtername'], $_POST['type'], $_POST['color'], $_POST['wratten'], $_POST['schott']);

      $filters->setObserver($id, $_SESSION['deepskylog_id']);
      $_SESSION['message'] = LangValidateEyepieceMessage2;
  	  $_SESSION['title'] = LangValidateEyepieceMessage3;
}
if(array_key_exists('change', $_POST) && $_POST['change'])
{
          $filters->setName($_POST['id'], $_POST['filtername']);
          $filters->setType($_POST['id'], $_POST['type']);
          $filters->setColor($_POST['id'], $_POST['color']);
          $filters->setWratten($_POST['id'], $_POST['wratten']);
          $filters->setSchott($_POST['id'], $_POST['schott']);
          $filters->setObserver($_POST['id'], $_SESSION['deepskylog_id']);
          $_SESSION['message'] = LangValidateEyepieceMessage5;
          $_SESSION['title'] = LangValidateEyepieceMessage4;
}
header("Location:../add_filter.php");
}
?>
