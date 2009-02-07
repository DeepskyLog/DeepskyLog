<?php

// validate_change_observation.php
// checks if the change new observation form is correctly filled in
// Version 0.1: JV, 20051206

include_once "../lib/cometobservations.php";
include_once "../lib/cometobjects.php";
include_once "../lib/observers.php";
include_once "../lib/setup/vars.php";
include_once "../lib/util.php";

$util = new Utils();
$util->checkUserInput();

$objects = new CometObjects;
$observations = new CometObservations;
$observers = new Observers();

if($_POST['changeobservation']) // pushed change observation button
{
   if(!$_POST['day'] || !$_POST['month'] || !$_POST['year'] || (!$_POST['hours'] && strcmp($_POST['hours'], 0) != "0") || (!$_POST['minutes'] && strcmp($_POST['minutes'], 0) != "0"))
   {
       $entryMessage = LangValidateObservationMessage1;
       $_GET['indexAction']='default_action';
   }
   elseif($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
   {
       $entryMessage = LangValidateObservationMessage6;
       $_GET['indexAction']='default_action';
          }

   elseif($_POST['observationid']) // all fields filled in and observationid given
   {
      // only admins may change a comet observation

      $role = $objObserver->getObserverProperty($_SESSION['deepskylog_id'],'role',2);

      if ($role == RoleAdmin || $role == RoleCometAdmin)
      {
        $date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);
        
        $time = ($_POST['hours'] * 100) + $_POST['minutes'];

        $observations->setDescription($_POST['observationid'], nl2br(htmlentities($_POST['description'])));

        $observations->setLocationId($_POST['observationid'], $_POST['location']);

        if(!($observers->getObserverProperty($_SESSION['deepskylog_id'],'UT')))
        {
          $observations->setLocalDateAndTime($_POST['observationid'], $date, $time);
        }
        else
        {
          $observations->setTime($_POST['observationid'], $time);
          $observations->setDate($_POST['observationid'], $date);
        }
        $observations->setInstrumentId($_POST['observationid'], $_POST['instrument']);

      $coma = $_POST['coma'];
      if ($coma == '')
      {
       $coma = -99;
      }
      $observations->setComa($_POST['observationid'], $coma);

      $tail = $_POST['tail_length'];
      if ($tail == '')
      {
       $tail = -99;
      }
      $observations->setTail($_POST['observationid'], $tail);

      $pa = $_POST['position_angle'];
      if ($pa == '')
      {
       $pa = -99;
      }
      $observations->setPa($_POST['observationid'], $pa);
      $observations->setChart($_POST['observationid'], $_POST['icq_reference_key']);
      $observations->setMagnification($_POST['observationid'], $_POST['magnification']);
      $observations->setMethode($_POST['observationid'], $_POST['icq_method']);
      $observations->setDc($_POST['observationid'], $_POST['condensation']);

      if($_POST['mag']) // magnitude given
      {
          if(ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$', $_POST['mag'], $matches)) // correct magnitude
          {
             $magnitude = "" . $matches[1] . ".";
             if($matches[2] != "")
             {
                $magnitude = $magnitude . $matches[2];
             }
             else
             {
                $magnitude = $magnitude . "0";
             }
             $observations->setMagnitude($_POST['observationid'], $magnitude);
             if($_POST['uncertain'])
             {
                 $observations->setMagnitudeUncertain($_POST['observationid'], 1);
             }
             else
             {
                 $observations->setMagnitudeUncertain($_POST['observationid'], 0);
             }
             $observations->setMagnitudeWeakerThan($_POST['observationid'], $_POST['smaller']);
          }
          else // reset all related values when invalid magnitude
          {
             $observations->setMagnitude($_POST['observationid'], -99.9);
             $observations->setMagnitudeUncertain($_POST['observationid'], 0);
             $observations->setMagnitudeWeakerThan($_POST['observationid'], 0);
          }
      }
      else
      {
          $observations->setMagnitude($_POST['observationid'], -99.9);
          $observations->setMagnitudeUncertain($_POST['observationid'], 0);
          $observations->setMagnitudeWeakerThan($_POST['observationid'], 0);
      }

      if($_FILES['drawing']['tmp_name'] != "")
      {
         $upload_dir = 'cometdrawings';
         $dir = opendir($instDir."comets/".$upload_dir);

// resize code

         include "common/control/resize.php";

         $original_image = $_FILES['drawing']['tmp_name'];
         $destination_image = $instDir."comets/".$upload_dir . "/" . $_POST['observationid'] . "_resized.jpg";
         $max_width = "490";
         $max_height = "490";
         $resample_quality = "100";

         $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);


          move_uploaded_file($_FILES['drawing']['tmp_name'], $instDir."comets/".$upload_dir . "/" . $_POST['observationid'] . ".jpg");
      }

            // save current details for faster submission of multiple observations

            $_SESSION['year'] = $_POST['year']; // save current year
            $_SESSION['month'] = $_POST['month']; // save current month
            $_SESSION['day'] = $_POST['day']; // save current day
            $_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
            $_SESSION['location'] = $_POST['location']; // save current location
            $_SESSION['seeing'] = $_POST['seeing']; // save current seeing
            $_SESSION['savedata'] = "yes"; // session variable to tag multiple observations

      $_GET['indexAction']='detail_observation';
      $_GET['observation']=$_POST['observationid'];
      $_GET['new']="yes";

   } // end if own observation.php
   else // try to change an observation which doesn't belong to the observer logged in
   {
      unset($_SESSION['deepskylog_id']);
       $_GET['indexAction']='default_action';
         }
 }
 else // no observation id given
  {
      unset($_SESSION['deepskylog_id']);
       $_GET['indexAction']='default_action';
        }

}
?>
