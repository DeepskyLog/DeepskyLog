<?php
// validate_delete_observation.php
// deletes an observation

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else validate_delete_observation();

function validate_delete_observation()
{ global $objObserver;
	$util = new Utils();
	
	$cometobservations = new CometObservations;
	
	if (!$_GET['observationid']) // not logged in
	{
	  unset($_SESSION['deepskylog_id']);
	  header("Location:../index.php");
	}
	elseif($_GET['observationid']) // observationid given
	{
	  // only admins may delete a comet observation
	
	  $role = $objObserver->getObserverProperty($_SESSION['deepskylog_id'],'role',2);
	
	  if ($role == RoleAdmin || $role == RoleCometAdmin)
	  {
	    $cometobservations->deleteObservation($_GET['observationid']);
	    $_GET['indexAction']='comets_all_observations';
	  }
	  else // not logged in as admin 
	  {
	    unset($_SESSION['deepskylog_id']);
	    header("Location: ../index.php"); // back to entrance page
	  }
	}
}
?>
