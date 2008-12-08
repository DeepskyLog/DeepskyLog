<?php
// validate_delete_location.php
// deletes a location if no observations done from this location yet 

if($objUtil->checkGetKey('locationid')
&& $objUtil->checkUserID($objLocation->getObserverFromLocation($objUtil->checkGetKey('locationid'))))
{ $queries = array("location" => $_GET['locationid'], "observer" => $_SESSION['deepskylog_id']);
  $obs = $objObservation->getObservationFromQuery($queries, "D", "1");
  $comobs = $objCometObservation->getObservationFromQuery($queries, "D", "1");
  if(!sizeof($obs) > 0 && !sizeof($comobs) > 0) // no observations from location yet
    $objLocation->deleteLocation($_GET['locationid']);
      $_GET['indexAction']='add_site';
}
$_GET['indexAction']='add_site';
?>
