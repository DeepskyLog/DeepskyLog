<?php
// view_observation.php
// view information of observation
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else if (! ($observationid = $objUtil->checkGetKey ( 'observation' )))
	throw new Exception ( "No observation defined in view_observation.php" );
else if (! ($object = $objObservation->getDsObservationProperty ( $observationid, 'objectname' ))) // check if observation exists
	throw new Exception ( "No observed object found in view_observation.php" );
else
	view_observation ();
function view_observation() {
	global $baseURL, $object, $loggedUser, $myList, $observationid, $listname_ss, $objObservation, $objObject, $objPresentations, $objUtil, $objList, $objObserver;
	echo "<div id=\"main\">";
	$object_ss = stripslashes ( $object );
	$seen = "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
	$seenDetails = $objObject->getSeenComprehensive ( $object );
	if (substr ( $seenDetails, 0, 1 ) == "X") {
		$seen = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectXSeen . "\">"
				  . $seenDetails . "</a>";
	}
	if ($loggedUser) {
		if (substr ( $seenDetails, 0, 1 ) == "Y") {
			$seen = "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\" title=\"" . LangObjectYSeen . "\">" 
				  . $seenDetails . "</a>";
		}
	}
	echo "<h4>" . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "</h4>";
	echo $objPresentations->getDSSDeepskyLiveLinks1 ( $object );
	
	$topline = "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\">" . LangViewObjectViewNearbyObject . " " . $object_ss . "</a>";
	if (substr ( $objObject->getSeen ( $object ), 0, 1 ) != '-')
		$topline .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\">" . LangViewObjectObservations . "&nbsp;" . $object_ss . "</a>";
	if ($loggedUser)
		$topline .= "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;object=" . urlencode ( $object ) . "\">" . LangViewObjectAddObservation . "&nbsp;" . $object_ss . "</a>";
	if ($myList) {
		if ($objList->checkObjectInMyActiveList ( $object ))
			$topline .= "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "&amp;removeObjectFromList=" . urlencode ( $object ) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
		else
			$topline .= "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "&amp;addObjectToList=" . urlencode ( $object ) . "&amp;showname=" . urlencode ( $object ) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
	}
	echo $objPresentations->getDSSDeepskyLiveLinks2 ( $object );
	echo $topline;
	echo "<hr />";
	$objObject->showObject ( $object );
	$content = '';
	if ($loggedUser) // LOGGED IN
{
		if ($_GET ['dalm'] != "D")
			$content = "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText . "</a>" . "&nbsp;";
		if ($_GET ["dalm"] != "AO")
			$content .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=AO\" title=\"" . LangAO . "\">" . LangAOText . "</a>" . "&nbsp;";
		if ($objObservation->getObservationsUserObject ( $loggedUser, $object ) > 0) {
			if ($_GET ['dalm'] != "MO")
				$content .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=MO\" title=\"" . LangMO . "\">" . LangMOText . "</a>" . "&nbsp;";
			if ($_GET ['dalm'] != "LO")
				$content .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=LO\" title=\"" . LangLO . "\">" . LangLOText . "</a>" . "&nbsp;";
		}
		$content .= LangOverviewObservationsHeader5a;
		echo $content;
		echo "<br /><br />";
	}
	$objObservation->showObservation ( $_GET ['observation'] );
	if ($_GET ['dalm'] == "AO") {
		$AOid = $objObservation->getAOObservationsId ( $object, $_GET ['observation'] );
	} elseif ($_GET ['dalm'] == "MO") {
		$AOid = $objObservation->getMOObservationsId ( $object, $loggedUser, $_GET ['observation'] );
	} elseif ($_GET ['dalm'] == "LO") {
		$AOid = array (
				$objObservation->getLOObservationId ( $object, $loggedUser, $_GET ['observation'] ) 
		);
	} else {
		$AOid = array ();
	}
	while ( list ( $key, $LOid ) = each ( $AOid ) ) {
		echo "<strong>" . LangObservationOf . $object . "</strong>";
		$objObservation->showObservation ( $LOid );
	}
	if ($loggedUser != "") {
		$observerid = $objObservation->getDsObservationProperty ( $_GET ['observation'], 'observerid' );
		$name = $objObserver->getObserverProperty ( $observerid, 'firstname' ) . " " . $objObserver->getObserverProperty ( $observerid, 'name' ) . " ";
		
		$date = sscanf ( $objObservation->getDsObservationProperty ( $_GET ['observation'], 'date' ), "%4d%2d%2d" );
		
		$subject = LangMessageYourObservation . $objObservation->getDsObservationProperty ( $_GET ['observation'], 'objectname' ) . LangMessageOn . $date [2] . "/" . $date [1] . "/" . $date [0];
		echo "&nbsp;<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=new_message&amp;receiver=" . urlencode ( $observerid ) . "&amp;subject=" . urlencode ( $subject ) . "\"><span class=\"glyphicon glyphicon-envelope\"></span> " . $name . LangMessageAboutObservation . "</a>";
	}
	echo "</div>";
}
?>
