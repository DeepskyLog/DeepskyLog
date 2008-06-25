<?php

// validate_instrument.php
// checks if the add new instrument form is correctly filled in
// version 0.2: JV 20041125 

session_start(); // start session

include_once "../../lib/instruments.php";
include_once "../../lib/observers.php";
include_once "../../lib/objects.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

if ($_POST['adaption'] == 1)
{
	$observer = new Observers;
	$observer->setStandardTelescope($_SESSION['deepskylog_id'], $_POST['stdtelescope']);

  header("Location:../add_instrument.php");
} else if (!$_POST['instrumentname'] || !$_POST['diameter'] || !$_POST['type'] && $_POST['type'] != InstrumentBinoculars)
{
   $_SESSION['message'] = LangValidateAccountMessage1;
   header("Location:../error.php");
}
else
{
   if (!$_POST['fd'] && !$_POST['focallength'] && ($_POST['type'] != InstrumentBinoculars && $_POST['type'] != InstrumentFinderscope)) // none of fd AND focallength
   {
      $_SESSION['message'] = LangValidateInstrumentMessage2;
      header("Location:../error.php");
   }
   elseif (!$_POST['fd'] && !$_POST['focallength'] && ($_POST['type'] != InstrumentBinoculars && $_POST['type'] != InstrumentFinderscope)) // fd AND focallength filled in 
   {

      $_SESSION['message'] = LangValidateInstrumentMessage2; 
      header("Location:../error.php");
   }
   else
   {
      $instrumentname = htmlspecialchars($_POST['instrumentname']);
      $instrumentname = htmlspecialchars_decode($instrumentname, ENT_QUOTES);
      $type = htmlspecialchars($_POST['type']);
      $diameter = $_POST['diameter'];
      $fd = 0;
      $fixedMagnification = $_POST['fixedMagnification'];

      if ($_POST['diameterunits'] == "inch")
      {
         $diameter *= 25.4;
      }
      if ($_POST['focallength'] && $_POST['type'] != InstrumentBinoculars) // focal length filled in
      {
         $focallength = $_POST['focallength'];
         //echo ("focal length" . $focallength);
         if(array_key_exists('focallengthunits', $_POST) && $_POST['focallengthunits'] == "inch" && !array_key_exists('fd', $_POST))
         {
            $focallength = $focallength * 25.4;
         }
         $fd = $focallength / $diameter;
      }
      elseif (array_key_exists('fd', $_POST) && $_POST['fd'] && array_key_exists('type', $_POST) && ($_POST['type'] != InstrumentBinoculars))
      {
         $fd = $_POST['fd'];
      }

      $instruments = new Instruments; // create new Instruments object

      // fill database

      if(array_key_exists('add', $_POST) && $_POST['add']) // add instrument
      {
        $instruments->addInstrument($instrumentname, $diameter, $fd, $type, $fixedMagnification, $_SESSION['deepskylog_id']);
        $_SESSION['message'] = LangValidateInstrumentMessage3;
      }
      if(array_key_exists('change', $_POST) && $_POST['change']) // change instrument
      {
        $id = $_POST['id'];
        $instruments->setType($id, $type);
        $instruments->setName($id, $instrumentname);
        $instruments->setDiameter($id, $diameter);
        $instruments->setFd($id, $fd);
        $instruments->setFixedMagnification($id, $fixedMagnification);
        $_SESSION['message'] = LangValidateInstrumentMessage4;
      }

      header("Location:../add_instrument.php");
   }
}
?>
