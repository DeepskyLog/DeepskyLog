<?php
if(!$_GET['observationid'])
  throw new Exception("No observation to delete.");                           
elseif($GLOBALS['objUtil']->checkGetKey('observationid'))
{ if($GLOBALS['objObservation']->getObserverId($_GET['observationid'])==$GLOBALS['objUtil']->checkArrayKey($_SESSION,'deepskylog_id',-1)) // only allowed to delete your own observations
  { $GLOBALS['objObservation']->deleteDSObservation($_GET['observationid']);
  $_GET['indexAction']='default_action';
	$_SESSION['Qobs']=array();
	$_SESSION['QobsParams']=array();
  }
  else                                                                        // try to delete an observation which doesn't belong to the observer logged in
  $_GET['indexAction']='default_action';
}
?>
