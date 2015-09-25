<?php
// selected_sessions.php
// generates an overview of selected observations in the database
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	selected_sessions ();
function selected_sessions() {
	global $baseURL, $FF, $loggedUser, $object, $myList, $step, $objObject, $objObserver, $objSession, $objPresentations, $objUtil;
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/presentation.js\"></script>";
	reset ( $_GET );

	// First check the number of sessions for the observer
	$sessions = $objSession->getListWithActiveSessions ( $loggedUser );

  $objSession->showListSessions ( $sessions, $loggedUser );
}
?>
