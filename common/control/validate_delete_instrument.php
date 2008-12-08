<?php
// validate_delete_instrument.php
// deletes an instrument if no observations done with this instrument yet 

if($objUtil->checkGetKey('instrumentid')                                                     // no instrumentid given as a parameter
&& $objUtil->checkUserID($objInstrument->getObserverFromInstrument($objUtil->checkGetKey('instrumentid'))))
{ $queries = array("instrument" => $_GET['instrumentid'], "observer" => $_SESSION['deepskylog_id']);
  $obs = $objObservation->getObservationFromQuery($queries, "D", "1");
  $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
  if(!sizeof($obs) > 0 && !sizeof($comobs) > 0)                               // no observations from location yet
    $objInstrument->deleteInstrument($_GET['instrumentid']);
}
$_GET['indexAction']='add_instrument'
?>