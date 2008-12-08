<?php
// validate_delete_eyepiece.php
// deletes an eyepiece if no observations done with this eyepiece yet 

if($objUtil->checkGetKey('eyepieceid') 
&& $objUtil->checkUserID($objEyepiece->getObserverFromEyepiece($objUtil->checkGetKey('eyepieceid'))))
{ $queries = array("eyepiece" => $_GET['eyepieceid'], "observer" => $_SESSION['deepskylog_id']);
  $obs = $objObservation->getObservationFromQuery($queries,"D","1");
  $comobs = $objCometObservation->getObservationFromQuery($queries);
  if((!sizeof($obs)>0) && (!sizeof($comobs)>0))                               // no observations from location yet
    $objEyepiece->deleteEyepiece($_GET['eyepieceid']);
} 
$_GET['indexAction']='add_eyepiece';
?>
