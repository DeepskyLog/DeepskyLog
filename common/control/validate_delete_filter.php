<?php
// validate_delete_filter.php
// deletes a filter if no observations done with this filter yet 

if (!$_GET['filterid']) // no filterid given as a parameter
{ unset($_SESSION['deepskylog_id']);
  $_GET['indexAction']="error_action";
}
elseif(array_key_exists('filterid', $_GET) && $_GET['filterid']) // filterid given
{ if((array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) || 
     (array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] == $objFilter->getObserverFromFilter($_GET['filterid'])))
  { $queries = array("filter" => $_GET['filterid'], "observer" => $_SESSION['deepskylog_id']);
    $obs = $objObservation->getObservationFromQuery($queries);
//   $comobs = $objCometObservation->getObservationFromQuery($queries);
    if(!sizeof($obs) > 0) // && !sizeof($comobs) > 0) // no observations from location yet
    { $objFilter->deleteFilter($_GET['filterid']);
      $_GET['indexAction']="add_filter";
    }
    else // still observations from given location 
    { unset($_SESSION['deepskylog_id']);
      $_GET['indexAction']="add_filter";
    }
  } 
  else // not logged in as an administrator 
  { unset($_SESSION['deepskylog_id']);
    $_GET['indexAction']="add_filter";
  }
}
?>
