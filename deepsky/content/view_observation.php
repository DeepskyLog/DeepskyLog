<?php
// view_observation.php
// view information of observation
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else if (! ($observationid = $objUtil->checkGetKey ( 'observation' ))) {
	print _("No observation to display.");
} else if (! ($object = $objObservation->getDsObservationProperty ( $observationid, 'objectname' ))) {
	// check if observation exists
	print _("The requested observation does not exist.");
} else {
	view_observation ();
}
function view_observation() {
	global $baseURL, $object, $loggedUser, $myList, $observationid, $listname_ss, $objObservation, $objObject, $objPresentations, $objUtil, $objList, $objObserver;
	echo "<div id=\"main\">";
	$object_ss = stripslashes ( $object );
	echo "<h4>" . _("Object details") . "&nbsp;-&nbsp;<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\">" . $object_ss . "</a></h4>";
	$seenDetails = $objObject->getSeenComprehensive ( $object );
	echo $objPresentations->getDSSDeepskyLiveLinks1 ( $object );
	echo $objPresentations->getDSSDeepskyLiveLinks2 ( $object );
	echo "</div>";

	$topline = "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\">" . _("Nearby objects") . " " . $object_ss . "</a>";
	if (substr ( $objObject->getSeen ( $object ), 0, 1 ) != '-')
		$topline .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "\">" . _("All observations") . "&nbsp;" . $object_ss . "</a>";
	if ($loggedUser)
		$topline .= "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;object=" . urlencode ( $object ) . "\">" . _("New observation") . "&nbsp;" . $object_ss . "</a>";
	if ($myList) {
		if ($objList->checkObjectInMyActiveList ( $object ))
			$topline .= "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object ) . "\">" . sprintf(_("%s to remove from the list %s"), $object_ss, $listname_ss) . "</a>";
		else
			$topline .= "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . sprintf(_("%s to add to the list %s"), $object_ss, $listname_ss) . "</a>";
	}
	echo $topline;
	echo "<br /><br />";
	$objObject->showObject ( $object );
	$content = '';
	if ($loggedUser) // LOGGED IN
{
		if ($_GET ['dalm'] != "D")
			$content = "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=D\" title=\"" . _("Details of this observation") . "\">" . "D" . "</a>" . "&nbsp;";
		if ($_GET ["dalm"] != "AO")
			$content .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=AO\" title=\"" . _("Compare this observation with all observations of this object") . "\">" . "AO" . "</a>" . "&nbsp;";
		if ($objObservation->getObservationsUserObject ( $loggedUser, $object ) > 0) {
			if ($_GET ['dalm'] != "MO")
				$content .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=MO\" title=\"" . _("Compare this observation with all my observations of this object") . "\">" . "MO" . "</a>" . "&nbsp;";
			if ($_GET ['dalm'] != "LO")
				$content .= "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $observationid . "&amp;dalm=LO\" title=\"" . _("Compare this observation with my last observation of this object") . "\">" . "LO" . "</a>" . "&nbsp;";
		}
		$content .= _("(*) All Observations(AO), My observations(MO), my Last observations(LO) of this object");
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
		echo "<strong>" . _("Observation of ") . $object . "</strong>";
		$objObservation->showObservation ( $LOid );
	}
	if ($loggedUser != "") {
		$observerid = $objObservation->getDsObservationProperty ( $_GET ['observation'], 'observerid' );
		$name = $objObserver->getObserverProperty ( $observerid, 'firstname' ) . " " . $objObserver->getObserverProperty ( $observerid, 'name' ) . " ";

		$date = sscanf ( $objObservation->getDsObservationProperty ( $_GET ['observation'], 'date' ), "%4d%2d%2d" );

		$subject = sprintf(
            _('Your observation of %s on %s'), 
            $objObservation->getDsObservationProperty($_GET['observation'], 'objectname'),
            $date[2] . "/" . $date[1] . "/" . $date[0]
        );
        echo "&nbsp;<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=new_message&amp;receiver=" . urlencode ( $observerid ) . "&amp;subject=" . urlencode ( $subject ) . "\"><span class=\"glyphicon glyphicon-envelope\"></span> " 
            . sprintf(
                _('%s about this observation') . "</a>", 
                $name
            );
	}
	echo "</div>";
}
?>
