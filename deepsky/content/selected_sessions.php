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
	$link2 = $baseURL . "index.php?indexAction=result_selected_sessions";
	reset ( $_GET );
	while ( list ( $key, $value ) = each ( $_GET ) )
		if (! in_array ( $key, array (
				'indexAction',
				'collapsed' 
		) ))
			$link2 .= "&amp;" . $key . "=" . urlencode ( $value );
	
	$link = $link2;
	
	// First check the number of sessions for the observer
	if (array_key_exists ( 'observer', $_GET )) {
		$observer = $_GET ['observer'];
		$sessions = $objSession->getListWithActiveSessions ( $observer );
	} else {
		$sessions = $objSession->getListWithAllActiveSessions ();
		$observer = "-1";
	}
	// Get the number of sessions
	if (count ( $sessions ) == 0) 	// ================================================================================================== no result present =======================================================================================
	{
		echo "<h4>" . LangSessionNoResults . " " . $objObserver->getObserverProperty ( $observer, "firstname" ) . " " . $objObserver->getObserverProperty ( $observer, "name" ) . "!</h4>";
	} else { // =============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
		echo "<div id=\"main\">";
		if ($observer == "-1") {
			$content1 = "<h4>" . LangSearchMenuItem12;
		} else {
			$content1 = "<h4>" . LangOverviewSessionTitle . $objObserver->getObserverProperty ( $observer, "firstname" ) . " " . $objObserver->getObserverProperty ( $observer, "name" );
		}
		$content1 .= "</h4>";
		
		echo $content1;
		
		$objSession->showListSessions ( $sessions, $link );
	}
}
?>  
