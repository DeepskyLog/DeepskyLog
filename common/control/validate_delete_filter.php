<?php
// validate_delete_filter.php
// deletes a filter if no observations done with this filter yet 

if($objUtil->checkGetKey('filterid')
&& $objUtil->checkUserID($objFilter->getObserverFromFilter($objUtil->checkGetKey('filterid'))))
{ $queries = array("filter" => $_GET['filterid'], "observer" => $_SESSION['deepskylog_id']);
  $obs = $objObservation->getObservationFromQuery($queries,"D","1");
//   $comobs = $objCometObservation->getObservationFromQuery($queries);
  if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
    $objFilter->deleteFilter($_GET['filterid']); 
} 
$_GET['indexAction']="add_filter";
?>
