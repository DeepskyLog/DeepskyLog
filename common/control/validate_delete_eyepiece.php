<?php
// validate_delete_eyepiece.php
// deletes an eyepiece if no observations done with this eyepiece yet 

if (!$_GET['eyepieceid'])                                                       // no eyepieceid given as a parameter
{ unset($_SESSION['deepskylog_id']);
  $_GET['indexAction']='add_eyepiece'; 
}
elseif(array_key_exists('eyepieceid', $_GET) && $_GET['eyepieceid'])            // eyepieceid given
{ if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
  array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $objEyepiece->getObserverFromEyepiece($_GET['eyepieceid'])) // only admin may delete locations 
  { $queries = array("eyepiece" => $_GET['eyepieceid'], "observer" => $_SESSION['deepskylog_id']);
    $obs = $objObservation->getObservationFromQuery($queries);
//   $comobs = $objCometObservation->getObservationFromQuery($queries);
   if(!sizeof($obs)>0) // && !sizeof($comobs) > 0)                              // no observations from location yet
   { $objEyepiece->deleteEyepiece($_GET['eyepieceid']);
     $_GET['indexAction']='add_eyepiece'; 
   }
   else                                                                         // still observations from given location 
   { unset($_SESSION['deepskylog_id']);
     $_GET['indexAction']='add_eyepiece';                                       // back to entrance page
   }
  } 
  else                                                                          // not logged in as an administrator 
  { unset($_SESSION['deepskylog_id']);
    $_GET['indexAction']='add_eyepiece';                                        // back to entrance page
  }
}
?>
