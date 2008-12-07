<?php
// validate_delete_eyepiece.php
// deletes an eyepiece if no observations done with this eyepiece yet 

if((array_key_exists('eyepieceid', $_GET) && $_GET['eyepieceid']) &&
   ((array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
    (array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $objEyepiece->getObserverFromEyepiece($_GET['eyepieceid']))))
{ $queries = array("eyepiece" => $_GET['eyepieceid'], "observer" => $_SESSION['deepskylog_id']);
  $obs = $objObservation->getObservationFromQuery($queries);
  $comobs = $objCometObservation->getObservationFromQuery($queries);
  if((!sizeof($obs)>0) && (!sizeof($comobs)>0))                               // no observations from location yet
    $objEyepiece->deleteEyepiece($_GET['eyepieceid']);
} 
$_GET['indexAction']='add_eyepiece';
?>
