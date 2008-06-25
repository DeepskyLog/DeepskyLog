<?php

// validate_observation.php
// checks if the add new observation form is correctly filled in

include_once "../../lib/objects.php";
include_once "../../lib/observations.php";
include_once "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";
include_once "../../lib/locations.php";

$util = new Util();
$util->checkUserInput();

$objects = new Objects;
$observations = new Observations;
$observers = new Observers;
$locations = new Locations;

if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id']) // logged in
{
   if(array_key_exists('addobservation', $_POST) && $_POST['addobservation']) // pushed add observation button
   {
      if (!$_POST['day'] || !$_POST['month'] || !$_POST['year'] || $_POST['site'] == "1" || !$_POST['instrument'] || !$_POST['description'])
      {
         // user forgot required field

         $_SESSION['message'] = LangValidateObservationMessage1;
         $_SESSION['backlink'] = "validate_observation.php"; // also show lower form when back button used

         // save filled in fields to automatically fill form fields when back button is used 

         $_SESSION['year'] = $_POST['year']; // save current year
         $_SESSION['month'] = $_POST['month']; // save current month
         $_SESSION['day'] = $_POST['day']; // save current day
         $_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
         $_SESSION['location'] = $_POST['site']; // save current location
         $_SESSION['seeing'] = $_POST['seeing']; // save current seeing
         $_SESSION['savedata'] = "yes"; // session variable to tag multiple observations
         $_SESSION['observation_query'] = "";

         if(array_key_exists('limit', $_POST) && $_POST['limit'])
         {
            if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})', $_POST['limit'], $matches)) // limiting magnitude like X.X or X,X with X a number between 0 and 9
            {
               // valid limiting magnitude
               $_SESSION['limit'] = $matches[1] . ".";
               if($matches[2] != "")
               {
                  $_SESSION['limit'] = $_SESSION['limit'] . $matches[2];
               }
               else
               {
                  $_SESSION['limit'] = $_SESSION['limit'] . "0";
               }
            }
            else // invalid limiting magnitude
            {
               $_SESSION['limit'] = ""; // clear current magnitude limit
            }
         }
         else
         {
            $_SESSION['limit'] = "";
         }

         $_SESSION['visibility'] = $_POST['visibility'];

         // add session variables for retaining description and time
         // check in new observation form
         // these should be cleared when an observation has been validated successfully!!!

               // Security is handled in CheckUserInput
	       $_SESSION['description'] = nl2br($_POST['description']);
	       $_SESSION['hours'] = $_POST['hours'];
	       $_SESSION['minutes'] = $_POST['minutes'];

         header("Location:../../common/error.php");
      }
      else // all fields filled in
      {
         if($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
         {
            $_SESSION['message'] = LangValidateObservationMessage6;
            header("Location:../../common/error.php");
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
            else
            {
               $time = -9999;
            }

            if(array_key_exists('visibility', $_POST) && $_POST['visibility'])
            {
              $visibility = $_POST['visibility'];
            }
            else
            {
              $visibility = 0;
            }

            // add observation to database
            if(array_key_exists('limit', $_POST) && $_POST['limit'])
            {
               if (ereg('([0-9]{1})[.,]{0,1}([0-9]{0,1})', $_POST['limit'], $matches)) // limiting magnitude like X.X or X,X with X a number between 0 and 9
               {
                  // valid limiting magnitude
                  $_SESSION['limit'] = $matches[1] . "." . $matches[2]; // save current magnitude limit
               }
               else // invalid limiting magnitude
               {
                  $_SESSION['limit'] = ""; // clear current magnitude limit 
               }
            }
            else
            {
               $_SESSION['limit'] = "";
            }

            // add observation to database
            $current_observation = $observations->addDSObservation($_POST['observedobject'], $_SESSION['deepskylog_id'], $_POST['instrument'], $_POST['site'], $date, $time, nl2br($_POST['description']), $_POST['seeing'], $_SESSION['limit'], $visibility, $_POST['description_language']);

						if ($_POST['filter'])
						{
							$observations->setFilterId($current_observation, $_POST['filter']);
						}

						if ($_POST['lens'])
						{
							$observations->setLensId($current_observation, $_POST['lens']);
						}

						if ($_POST['eyepiece'])
						{
							$observations->setEyepieceId($current_observation, $_POST['eyepiece']);
						}

            if ($observers->getUseLocal($_SESSION['deepskylog_id']))
            {
               $observations->setLocalDateAndTime($current_observation, $date, $time);
            }

            if($_FILES['drawing']['tmp_name'] != "") // drawing to upload
            {
               $upload_dir = '../drawings';
               $dir = opendir($upload_dir);

               // resize code

                include "../../common/control/resize.php";

                $original_image = $_FILES['drawing']['tmp_name'];
                $destination_image = $upload_dir . "/" . $current_observation . "_resized.jpg";
                $max_width = "490";
                $max_height = "490";
                $resample_quality = "100";

                $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);

               move_uploaded_file($_FILES['drawing']['tmp_name'], $upload_dir . "/" . $current_observation . ".jpg");
            }

            // save current details for faster submission of multiple observations

            $_SESSION['year'] = $_POST['year']; // save current year
            $_SESSION['month'] = $_POST['month']; // save current month
            $_SESSION['day'] = $_POST['day']; // save current day
            $_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
            $_SESSION['location'] = $_POST['site']; // save current location
            $_SESSION['seeing'] = $_POST['seeing']; // save current seeing
            $_SESSION['language'] = $_POST['description_language']; // save current language
            $_SESSION['savedata'] = "yes"; // session variable to tag multiple observations 
            $_SESSION['visibility'] = "";
            $_SESSION['observation_query'] = "";

	    // clear session variables for description and time when form was not correctly filled in

	    $_SESSION['description'] = "";
	    $_SESSION['hours'] = "";
	    $_SESSION['minutes'] = "";

            header("Location:../index.php?indexAction=detail_observation&dalm=D&observation=" . $current_observation . "&new=yes");
         }  
      }
   }
   elseif(array_key_exists('clearfields', $_POST) && $_POST['clearfields']) // pushed clear fields button
   {
      $_SESSION['savedata'] = "no";
      header("Location:../index.php?indexAction=add_observation");
   }
}
else // not logged in
{
   header("Location:../index.php");
}

?>
