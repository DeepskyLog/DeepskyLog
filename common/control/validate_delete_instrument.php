<?php
// validate_delete_instrument.php
// deletes an instrument if no observations done with this instrument yet 

if (!$_GET['instrumentid'])                                                     // no instrumentid given as a parameter
{ unset($_SESSION['deepskylog_id']);
  $_GET['indexAction']='default_action';
}
elseif(array_key_exists('instrumentid', $_GET) && $_GET['instrumentid'])        // instrumentid given
{ if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes") || 
     array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id']==$objInstrument->getObserverFromInstrument($_GET['insturmentid'])) 
  { $queries = array("instrument" => $_GET['instrumentid'], "observer" => $_SESSION['deepskylog_id']);
    $obs = $objObservation->getObservationFromQuery($queries, "D", "1");
    $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
    if(!sizeof($obs) > 0 && !sizeof($comobs) > 0)                               // no observations from location yet
    { $objInstrument->deleteInstrument($_GET['instrumentid']);
      $_GET['indexAction']='add_instrument';
    }
    else                                                                        // still observations from given location 
    { unset($_SESSION['deepskylog_id']);
      $_GET['indexAction']='add_instrument';                                    // back to entrance page
    }
  } 
  else                                                                          
  { unset($_SESSION['deepskylog_id']);
    $_GET['indexAction']='add_instrument';                                      // back to entrance page
  }
}
?>