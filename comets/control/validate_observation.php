<?php

// validate_observation.php
// checks if the add new observation form is correctly filled in

include_once "lib/cometobjects.php";
include_once "lib/cometobservations.php";
include_once "lib/observers.php";
include_once "lib/setup/vars.php";
include_once "lib/util.php";

$util = new Utils();
$util->checkUserInput();

$cometobjects = new CometObjects;
$cometobservations = new CometObservations;
$observers = new Observers();

if($_SESSION['deepskylog_id']) // logged in
{
   if($_POST['addobservation']) // pushed add observation button
   {
      if (!$_POST['day'] || !$_POST['month'] || !$_POST['year'] || !$_POST['comet'] || (!$_POST['hours'] && strcmp($_POST['hours'], 0) != "0") || (!$_POST['minutes'] && strcmp($_POST['minutes'],0) != "0"))
      {
         // user forgot required field

    $entryMessage = LangValidateObservationMessage1;
    $_GET['indexAction']='default_action';
               }
      else // all fields filled in
      {
         if($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
         {
   $entryMessage = LangValidateObservationMessage6;
    $_GET['indexAction']='default_action';
         	         }
         else
         {
            $date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);

            if ($_POST['hours'] != "")
            {
               if(isset($_POST['minutes']))
               { 
                  $time = ($_POST['hours'] * 100) + $_POST['minutes'];
               }
               else
               {
                  $time = ($_POST['hours'] * 100);
               }
            }

            // add observation to database

            $current_observation = $cometobservations->addObservation($cometobjects->getId($_POST['comet']), $_SESSION['deepskylog_id'], $date, $time);
            $cometobservations->setLocationId($current_observation, $_POST['site']);

            if(!($observers->getObserverProperty($_SESSION['deepskylog_id'],'UT')))
            {
               $cometobservations->setLocalDateAndTime($current_observation, $date, $time);
            }

	    $cometobservations->setDescription($current_observation, nl2br(htmlentities($_POST['description'])));
            $cometobservations->setInstrumentId($current_observation, $_POST['instrument']);
        $coma = $_POST['coma'];
        if ($coma == '')
        {
         $coma = -99;
        }
            $cometobservations->setComa($current_observation, $coma);

        $tail = $_POST['tail_length'];
        if ($tail == '')
        {
         $tail = -99;
        }
	    $cometobservations->setTail($current_observation, $tail);

        $pa = $_POST['position_angle'];
        if ($pa == '')
        {
         $pa = -99;
        }
	    $cometobservations->setPa($current_observation, $pa);

      // MAGNITUDE AND RELATED FIELDS

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
             $cometobservations->setMagnitude($current_observation, $magnitude);
             if($_POST['uncertain'])
             {
                 $cometobservations->setMagnitudeUncertain($current_observation, 1);
             }
             else
             {
                 $cometobservations->setMagnitudeUncertain($current_observation, 0);
             }
             $cometobservations->setMagnitudeWeakerThan($current_observation, $_POST['smaller']);
          }
          else // reset all related values when invalid magnitude
          {
             $cometobservations->setMagnitude($current_observation, -99.9);
             $cometobservations->setMagnitudeUncertain($current_observation, 0);
             $cometobservations->setMagnitudeWeakerThan($current_observation, 0);
          }
      }
      else
      {
          $cometobservations->setMagnitude($current_observation, -99.9);
          $cometobservations->setMagnitudeUncertain($current_observation, 0);
          $cometobservations->setMagnitudeWeakerThan($current_observation, 0);
      }

            $cometobservations->setChart($current_observation, $_POST['icq_reference_key']);
            $cometobservations->setMagnification($current_observation, $_POST['magnification']);
	    $cometobservations->setMethode($current_observation, $_POST['icq_method']);
	    $cometobservations->setDc($current_observation, $_POST['condensation']);

            if($_FILES['drawing']['tmp_name'] != "") // drawing to upload
            {
               $upload_dir = 'cometdrawings';
               $dir = opendir($instDir."comets/".$upload_dir);

               // resize code

                include "common/control/resize.php";

                $original_image = $_FILES['drawing']['tmp_name'];
                $destination_image = $instDir.'comets/'.$upload_dir . "/" . $current_observation . "_resized.jpg";
                $max_width = "490";
                $max_height = "490";
                $resample_quality = "100";

                $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);

               move_uploaded_file($_FILES['drawing']['tmp_name'], $instDir.'comets/'.$upload_dir . "/" . $current_observation . ".jpg");
            }

            // save current details for faster submission of multiple observations

            $_SESSION['year'] = $_POST['year']; // save current year
            $_SESSION['month'] = $_POST['month']; // save current month
            $_SESSION['day'] = $_POST['day']; // save current day
            $_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
            $_SESSION['location'] = $_POST['site']; // save current location
            $_SESSION['savedata'] = "yes"; // session variable to tag multiple observations 
            $_SESSION['observation_query'] = "";

            $_GET['indexAction']='comets_detail_observation';
            $_GET['observation']=$current_observation;
            $_GET['new']="yes";
         }  
      }
   }
   elseif($_POST['clearfields']) // pushed clear fields button
   {
      $_SESSION['savedata'] = "no";
      $_GET['indexAction']="add_observation";
   }
}
else // not logged in
{
   $_GET['indexAction']="default_action";
}

?>
