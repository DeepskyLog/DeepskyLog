<?php
// validate_delete_lens.php
// deletes a lens if no observations done with this lens yet 

if($objUtil->checkGetKey('lensid')
&& $objUtil->checkUserID($objLens->getObserverFromLens($objUtil->checkGetKey('lensid'))))
{ $queries = array("lens" => $_GET['lensid'], "observer" => $_SESSION['deepskylog_id']);
  $obs = $objObservation->getObservationFromQuery($queries,"D","1");
  //   $comobs = $objCometObservation->getObservationFromQuery($queries);
  if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
    $objLens->deleteLens($_GET['lensid']);
}
$_GET['indexAction']="add_lens";
?>
