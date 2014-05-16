<?php 
// validate_observation.php
// checks if the add new observation form is correctly filled in

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($loggedUser)) throw new Exception(LangExcpetion001);
else validate_observation();

function validate_observation()
{ global $instDir,$entryMessage,$loggedUser,$maxFileSize,
         $objObserver,$objCometObservation,$objCometObject,$objUtil;
	if($loggedUser) // logged in
	{ if($_POST['addobservation']) // pushed add observation button
	  { if (!$_POST['day'] || !$_POST['month'] || !$_POST['year'] || !$_POST['comet'] || (!$_POST['hours'] && strcmp($_POST['hours'], 0) != "0") || (!$_POST['minutes'] && strcmp($_POST['minutes'],0) != "0"))
	    { // user forgot required field
	      $entryMessage = LangValidateObservationMessage1;
	      $_GET['indexAction']='default_action';
	    }
	    else // all fields filled in
	    { if($_FILES['drawing']['size'] > $maxFileSize) // file size of drawing too big
	      { $entryMessage = LangValidateObservationMessage6;
	        $_GET['indexAction']='default_action';
	      }
	      else
	      { $date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);
	        if ($_POST['hours'] != "")
	          if(isset($_POST['minutes']))
	            $time = ($_POST['hours'] * 100) + $_POST['minutes'];
	          else
	            $time = ($_POST['hours'] * 100);
	        // add observation to database
	        $current_observation = $objCometObservation->addObservation($objCometObject->getId($_POST['comet']), $loggedUser, $date, $time);
	        $objCometObservation->setLocationId($current_observation, $_POST['site']);
	        if(!($objObserver->getObserverProperty($loggedUser,'UT')))
	          $objCometObservation->setLocalDateAndTime($current_observation, $date, $time);
	  	    $objCometObservation->setDescription($current_observation, nl2br(htmlentities($_POST['description'])));
	        $objCometObservation->setInstrumentId($current_observation, $_POST['instrument']);
	        $objCometObservation->setComa($current_observation, $objUtil->checkRequestKey('coma',-99));
	        $objCometObservation->setTail($current_observation, $objUtil->checkRequestKey('tail_length',-99));
	        $objCometObservation->setPa($current_observation, $objUtil->checkRequestKey('position_angle',-99));
	        // MAGNITUDE AND RELATED FIELDS
	        if($_POST['mag']) // magnitude given
	        { if(ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$', $_POST['mag'], $matches)) // correct magnitude
	          { $magnitude = "" . $matches[1] . ".";
	            if($matches[2] != "")
	              $magnitude = $magnitude . $matches[2];
	            else
	              $magnitude = $magnitude . "0";
	            $objCometObservation->setMagnitude($current_observation, $magnitude);
	            if($_POST['uncertain'])
	              $objCometObservation->setMagnitudeUncertain($current_observation, 1);
	            else
	              $objCometObservation->setMagnitudeUncertain($current_observation, 0);
	            $objCometObservation->setMagnitudeWeakerThan($current_observation, $_POST['smaller']);
	          }
	          else // reset all related values when invalid magnitude
	          { $objCometObservation->setMagnitude($current_observation, -99.9);
	            $objCometObservation->setMagnitudeUncertain($current_observation, 0);
	            $objCometObservation->setMagnitudeWeakerThan($current_observation, 0);
	          }
	        }
	        else
	        { $objCometObservation->setMagnitude($current_observation, -99.9);
	          $objCometObservation->setMagnitudeUncertain($current_observation, 0);
	          $objCometObservation->setMagnitudeWeakerThan($current_observation, 0);
	        }
	        $objCometObservation->setChart($current_observation, $_POST['icq_reference_key']);
	        $objCometObservation->setMagnification($current_observation, $_POST['magnification']);
	 	      $objCometObservation->setMethode($current_observation, $_POST['icq_method']);
		      $objCometObservation->setDc($current_observation, $_POST['condensation']);
	        if($_FILES['drawing']['tmp_name'] != "") // drawing to upload
	        { $upload_dir = 'cometdrawings';
	          $dir = opendir($instDir."comets/".$upload_dir);
	          // resize code
	          require_once "common/control/resize.php";
	          $original_image = $_FILES['drawing']['tmp_name'];
	          $destination_image = $instDir.'comets/'.$upload_dir . "/" . $current_observation . "_resized.jpg";
	          $new_image = image_createThumb($original_image,$destination_image,490,490,100);
	          move_uploaded_file($_FILES['drawing']['tmp_name'], $instDir.'comets/'.$upload_dir . "/" . $current_observation . ".jpg");
              $objCometObservation->setDrawing($current_observation);
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
	  { $_SESSION['savedata'] = "no";
	    $_GET['indexAction']="add_observation";
	  }
	}
	else // not logged in
	{  $_GET['indexAction']="default_action";
	}
}
?>