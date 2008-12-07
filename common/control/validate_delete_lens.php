<?php
// validate_delete_lens.php
// deletes a lens if no observations done with this lens yet 

if (!$_GET['lensid'])                                                           // no filterid given as a parameter
{ unset($_SESSION['deepskylog_id']);
  $_GET['indexAction']='error_action';
}
elseif(array_key_exists('lensid', $_GET) && $_GET['lensid'])                    // lensid given
{ if((array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
     (array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $objLens->getObserverFromLens($_GET['lensid']))) 
  { $queries = array("lens" => $_GET['lensid'], "observer" => $_SESSION['deepskylog_id']);
    $obs = $objObservation->getObservationFromQuery($queries);
    //   $comobs = $objCometObservation->getObservationFromQuery($queries);
    if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
    { $objLens->deleteLens($_GET['lensid']);
      $_GET['indexAction']="add_lens";
    }
    else // still observations with given lens 
    { unset($_SESSION['deepskylog_id']);
      $_GET['indexAction']="add_lens"; // back to entrance page
    }
  } 
  else // not logged in as an administrator 
  { unset($_SESSION['deepskylog_id']);
    $_GET['indexAction']="add_lens"; // back to entrance page
  }
}
?>
