<?php
// validate_change_observation.php
// checks if the change new observation form is correctly filled in

if(array_key_exists('changeobservation', $_POST) && $_POST['changeobservation']) // pushed change observation button
{ if(!$_POST['day']||!$_POST['month']||!$_POST['year']||$_POST['location']=="1"||!$_POST['instrument']||!$_POST['description'])
  {  $_SESSION['message'] = LangValidateObservationMessage1;
     header("Location:../../common/error.php");
  }
   elseif($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
   {
       $_SESSION['message'] = LangValidateObservationMessage6;
       header("Location:../../common/error.php");
   }
   elseif(array_key_exists('observationid', $_POST) && $_POST['observationid']) // all fields filled in and observationid given
   {
      if($observations->getObserverId($_POST['observationid']) == $_SESSION['deepskylog_id']) // only allowed to change your own observations
      {
        $date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);

        if(array_key_exists('hours', $_POST) && ($_POST['hours'] != ''))
        {
           if(array_key_exists('minutes', $_POST) && $_POST['minutes'])
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

      $observations->setDescription($_POST['observationid'], nl2br($_POST['description']));

			if ($_POST['filter'])
			{
				$observations->setFilterId($_POST['observationid'], $_POST['filter']);
			}
      else
      {
				$observations->setFilterId($_POST['observationid'], 0);
      }

			if ($_POST['lens'])
			{
				$observations->setLensId($_POST['observationid'], $_POST['lens']);
			}
      else
      {
				$observations->setLensId($_POST['observationid'], 0);
      }

			if ($_POST['eyepiece'])
			{
				$observations->setEyepieceId($_POST['observationid'], $_POST['eyepiece']);
			}
      else
      {
				$observations->setEyepieceId($_POST['observationid'], 0);
      }

      if ($observers->getUseLocal($_SESSION['deepskylog_id']))
      {
        $observations->setLocalDateAndTime($_POST['observationid'], $date, $time);
      }
      else
      {
        $observations->setTime($_POST['observationid'], $time);
        $observations->setDate($_POST['observationid'], $date);
      }
      $observations->setInstrumentId($_POST['observationid'], $_POST['instrument']);
      $observations->setLocationId($_POST['observationid'], $_POST['location']);

      $observations->setSeeing($_POST['observationid'], $_POST['seeing']);

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
          $_SESSION['limit'] = "";
      $observations->setObservationLimitingMagnitude($_POST['observationid'], $_SESSION['limit']);
      $observations->setObservationLanguage($_POST['observationid'], $_POST['description_language']);
      if(array_key_exists('visibility', $_POST) && $_POST['visibility'])
         $visibility = $_POST['visibility'];
      else
         $visibility = 0;
      $observations->setVisibility($_POST['observationid'], $visibility);

      if($_FILES['drawing']['tmp_name'] != "")
      {
         $upload_dir = '../drawings';
         $dir = opendir($upload_dir);

// resize code

         include "../../common/control/resize.php";

         $original_image = $_FILES['drawing']['tmp_name'];
         $destination_image = $upload_dir . "/" . $_POST['observationid'] . "_resized.jpg";
         $max_width = "490";
         $max_height = "490";
         $resample_quality = "100";

         $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);


          move_uploaded_file($_FILES['drawing']['tmp_name'], $upload_dir . "/" . $_POST['observationid'] . ".jpg");
      }

            // save current details for faster submission of multiple observations

            $_SESSION['year'] = $_POST['year']; // save current year
            $_SESSION['month'] = $_POST['month']; // save current month
            $_SESSION['day'] = $_POST['day']; // save current day
            $_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
            $_SESSION['location'] = $_POST['location']; // save current location
            $_SESSION['seeing'] = $_POST['seeing']; // save current seeing
            $_SESSION['savedata'] = "yes"; // session variable to tag multiple observations

      header("Location:../index.php?indexAction=detail_observation&dalm=D&observation=" . $_POST['observationid'] . "&new=yes");

   } // end if own observation.php
   else // try to change an observation which doesn't belong to the observer logged in
   {
      unset($_SESSION['deepskylog_id']);
      header("Location: ../index.php"); // back to entrance page
   }
 }
 else // no observation id given
  {
      unset($_SESSION['deepskylog_id']);
      header("Location: ../index.php"); // back to entrance page
  }

}
?>
