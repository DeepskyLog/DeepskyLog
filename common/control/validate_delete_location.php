<?php
// validate_delete_location.php
// deletes a location if no observations done from this location yet 

if(!$_GET['locationid']) // no locationid given as a parameter
{ $entryMessage="No location selected";
  $_GET['indexAction']='error_action';
}
elseif(array_key_exists('locationid', $_GET) && $_GET['locationid']) // locationid given
{ if((array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
     (array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $locations->getObserverFromLocation($_GET['locationid']))) // only admin may delete locations 
  { $queries = array("location" => $_GET['locationid'], "observer" => $_SESSION['deepskylog_id']);
    $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False", "D", "1");
    $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
    if(!sizeof($obs) > 0 && !sizeof($comobs) > 0) // no observations from location yet
    { $objLocation->deleteLocation($_GET['locationid']);
      $_GET['indexAction']='add_site';
    }
    else // still observations from given location 
      $_GET['indexAction']='add_site';
  } 
  else // not logged in as an administrator 
   $_GET['indexAction']='add_site';
}
else
  $_GET['indexAction']='default_action';
?>
