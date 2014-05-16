<?php 
// validate_change_observation.php
// checks if the change new observation form is correctly filled in

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else validate_change_observation();

function validate_change_observation()
{ global $instDir,$entryMessage,$maxFileSize,$loggedUser, 
         $objObserver,$objCometObservation,$objUtil;
	if((!$_POST['day'])||
	   (!$_POST['month'])||
	   (!$_POST['year'])||
	   ((!$_POST['hours']) && 
	     strcmp($_POST['hours'],0)!="0")||(!$_POST['minutes']&& strcmp($_POST['minutes'], 0)!="0"))
	{ $entryMessage=LangValidateObservationMessage1;
	  $_GET['indexAction']='default_action';
	}
	elseif($_FILES['drawing']['size']>$maxFileSize) // file size of drawing too big
	{ $entryMessage=LangValidateObservationMessage6;
	  $_GET['indexAction']='default_action';
	}
	elseif($_POST['observation']) // all fields filled in and observationid given
	{ // only admins may change a comet observation
	  $role = $objObserver->getObserverProperty($loggedUser,'role',2);
	  if(($role==RoleAdmin)||($role==RoleCometAdmin))
	  { $date = $_POST['year'] . sprintf("%02d", $_POST['month']) . sprintf("%02d", $_POST['day']);
	    $time = ($_POST['hours'] * 100) + $_POST['minutes'];
	    $objCometObservation->setDescription($_POST['observation'], nl2br(htmlentities($_POST['description'])));
	    $objCometObservation->setLocationId($_POST['observation'], $_POST['site']);
	    if(!($objObserver->getObserverProperty($loggedUser,'UT')))
	      $objCometObservation->setLocalDateAndTime($_POST['observation'], $date, $time);
	    else
	    { $objCometObservation->setTime($_POST['observation'], $time);
	      $objCometObservation->setDate($_POST['observation'], $date);
	    }
	    $objCometObservation->setInstrumentId($_POST['observation'], $_POST['instrument']);
	    $objCometObservation->setComa($_POST['observation'], $objUtil('coma',-99));
	    $objCometObservation->setTail($_POST['observation'], $objUtil->checkRequestKey('tail_length',-99));
	    $objCometObservation->setPa($_POST['observation'], $objUtil->checkRequestKey('position_angle',-99));
	    $objCometObservation->setChart($_POST['observation'], $objUtil->checkRequestKey('icq_reference_key'));
	    $objCometObservation->setMagnification($_POST['observation'], $objUtil->checkRequestKey('magnification'));
	    $objCometObservation->setMethode($_POST['observation'], $objUtil->checkRequestKey('icq_method'));
	    $objCometObservation->setDc($_POST['observation'], $objUtil->checkRequestKey('condensation'));
	    if($_POST['mag']) // magnitude given
	    { if(ereg('^([0-9]{1,2})[.,]{0,1}([0-9]{0,1})$', $_POST['mag'], $matches)) // correct magnitude
	      { $magnitude = "" . $matches[1] . ".";
	        if($matches[2]!="")
	          $magnitude = $magnitude . $matches[2];
	        else
	          $magnitude = $magnitude . "0";
	        $objCometObservation->setMagnitude($_POST['observation'], $magnitude);
	        if($_POST['uncertain'])
	          $objCometObservation->setMagnitudeUncertain($_POST['observation'], 1);
	        else
	          $objCometObservation->setMagnitudeUncertain($_POST['observation'], 0);
	        $objCometObservation->setMagnitudeWeakerThan($_POST['observation'], $_POST['smaller']);
	      }
	      else // reset all related values when invalid magnitude
	      { $objCometObservation->setMagnitude($_POST['observation'], -99.9);
	        $objCometObservation->setMagnitudeUncertain($_POST['observation'], 0);
	        $objCometObservation->setMagnitudeWeakerThan($_POST['observation'], 0);
	      }
	    }
	    else
	    { $objCometObservation->setMagnitude($_POST['observation'], -99.9);
	      $objCometObservation->setMagnitudeUncertain($_POST['observation'], 0);
	      $objCometObservation->setMagnitudeWeakerThan($_POST['observation'], 0);
	    }
	    if($_FILES['drawing']['tmp_name'] != "")
	    { $upload_dir = 'cometdrawings';
	      $dir = opendir($instDir."comets/".$upload_dir);
	      // resize code
	      require_once "common/control/resize.php";
	      $original_image=$_FILES['drawing']['tmp_name'];
	      $destination_image=$instDir."comets/".$upload_dir . "/" . $_POST['observation'] . "_resized.jpg";
	      $new_image=image_createThumb($original_image,$destination_image,490,490,100);
	      move_uploaded_file($_FILES['drawing']['tmp_name'], $instDir."comets/".$upload_dir . "/" . $_POST['observation'] . ".jpg");
          $objCometObservation->setDrawing($current_observation);
	    }
	    // save current details for faster submission of multiple observations
	    $_SESSION['year'] = $_POST['year']; // save current year
	    $_SESSION['month'] = $_POST['month']; // save current month
	    $_SESSION['day'] = $_POST['day']; // save current day
	    $_SESSION['instrument'] = $_POST['instrument']; // save current instrument for new observations
	    $_SESSION['location'] = $_POST['site']; // save current location
	    $_SESSION['seeing'] = $_POST['seeing']; // save current seeing
	    $_SESSION['savedata'] = "yes"; // session variable to tag multiple observations
	    $_GET['indexAction']='comets_detail_observation';
	    $_GET['observation']=$_POST['observation'];
	    $_GET['new']="yes";
	  } // end if own observation.php
	  else // try to change an observation which doesn't belong to the observer logged in
	  { unset($_SESSION['deepskylog_id']);
	    $_GET['indexAction']='default_action';
	  }
	}
	else // no observation id given
	{ unset($_SESSION['deepskylog_id']);
	  $_GET['indexAction']='default_action';
	}
}
?>
