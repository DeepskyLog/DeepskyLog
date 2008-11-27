<?php

// validate_eyepiece.php
// checks if the add new eyepiece or change eyepiece form is correctly filled in
// version 3.2: WDM, 16/01/2008

session_start(); // start session

include "lib/eyepieces.php";
include "lib/observers.php";
include_once "lib/setup/vars.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

if (!$_POST['eyepiecename'] || !$_POST['focalLength'] || !$_POST['apparentFOV'])
{
      $_SESSION['message'] = LangValidateEyepieceMessage1; 
      header("Location:error.php");
}
else
{
$eyepieces = new Eyepieces; // create new Eyepieces object

if(array_key_exists('add', $_POST) && $_POST['add'])
{
      // fill database
    	$id = $eyepieces->addEyepiece($_POST['eyepiecename'], $_POST['focalLength'], $_POST['apparentFOV']);
      $eyepieces->setEyepieceObserver($id, $_SESSION['deepskylog_id']);
			if ($_POST['maxFocalLength']) {
				$eyepieces->setMaxFocalLength($id, $_POST['maxFocalLength']);
			} else {
				$eyepieces->setMaxFocalLength($id, -1);
			}
      $_SESSION['message'] = LangValidateEyepieceMessage2;
  	  $_SESSION['title'] = LangValidateEyepieceMessage3;
}
if(array_key_exists('change', $_POST) && $_POST['change'])
{
          $eyepieces->setEyepieceName($_POST['id'], $_POST['eyepiecename']);
          $eyepieces->setEyepieceFocalLength($_POST['id'], $_POST['focalLength']);
          $eyepieces->setApparentFOV($_POST['id'], $_POST['apparentFOV']);
          $eyepieces->setEyepieceObserver($_POST['id'], $_SESSION['deepskylog_id']);
					if ($_POST['maxFocalLength']) {
						$eyepieces->setMaxFocalLength($_POST['id'], $_POST['maxFocalLength']);
					} else {
						$eyepieces->setMaxFocalLength($_POST['id'], -1);
					}
          $_SESSION['message'] = LangValidateEyepieceMessage5;
          $_SESSION['title'] = LangValidateEyepieceMessage4;
}
header("Location:add_eyepiece.php");
}
?>
