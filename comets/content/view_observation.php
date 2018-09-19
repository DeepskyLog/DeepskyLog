<?php
// view_observation.php
// view information of observation
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	view_observation ();
function view_observation() {
	global $instDir, $baseURL, $loggedUser, $dateformat, $objCometObservation, $objCometObject, $objInstrument, $objLocation, $objObserver, $objPresentations;
	$ICQMETHODS = new ICQMETHOD ();
	$ICQREFERENCEKEYS = new ICQREFERENCEKEY ();

	if (! $_GET ['observation']) 	// no observation defined
	{
		header ( "Location: " . $baseURL . "index.php" );
	}

	if ($objCometObservation->getObjectId ( $_GET ['observation'] )) 	// check if observation exists
	{
		echo "<div id=\"main\">";
		$content = "";
		if ($_SESSION ['observation_query']) 		// array of observations
		{
			$arrayIndex = array_search ( $_GET ['observation'], $_SESSION ['observation_query'] );
			$previousIndex = $arrayIndex + 1;
			@$previousObservation = $_SESSION ['observation_query'] [$previousIndex];
			$nextIndex = $arrayIndex - 1;
			@$nextObservation = $_SESSION ['observation_query'] [$nextIndex];
			if ($previousObservation != "")
				$content .= "<a href=\"" . $baseURL . "index.php?indexAction=comets_detail_observation&amp;observation=" . $previousObservation . "\">&lt;</a>&nbsp;&nbsp;&nbsp;";
			if ($nextObservation != "")
				$content .= "<a href=\"" . $baseURL . "index.php?indexAction=comets_detail_observation&amp;observation=" . $nextObservation . "\">&gt;</a> ";
		}
		echo "<h4>" . _("Observation details") . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $content . "</h4>";

		echo "<table class=\"table\">";
		echo "<tr><td><strong>" . _("Object name") . "</strong></td>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $objCometObservation->getObjectId ( $_GET ['observation'] ) ) . "\">" . $objCometObject->getName ( $objCometObservation->getObjectId ( $_GET ['observation'] ) ) . "</a></td>";
		echo "</tr>";

		echo "<tr><td><strong>" . _("Observer") . "</strong></td>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $objCometObservation->getObserverId ( $_GET ['observation'] ) ) . "\">" . $objObserver->getObserverProperty ( $objCometObservation->getObserverId ( $_GET ['observation'] ), 'firstname' ) . "&nbsp;" . $objObserver->getObserverProperty ( $objCometObservation->getObserverId ( $_GET ['observation'] ), 'name' ) . "</a></td>";
		echo "</tr>";
		$date = sscanf ( $objCometObservation->getDate ( $_GET ['observation'] ), "%4d%2d%2d" );
		if ($objCometObservation->getTime ( $_GET ['observation'] ) >= 0)
			if (! ($objObserver->getObserverProperty ( $loggedUser, 'UT' )))
				$date = sscanf ( $objCometObservation->getLocalDate ( $_GET ['observation'] ), "%4d%2d%2d" );
		echo "<tr><td><strong>" . _("Date") . "</strong></td>";
		echo "<td>" . date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) ) . "</td>";
		echo "</tr>";

		if ($objCometObservation->getTime ( $_GET ['observation'] ) >= 0) {
			if (! ($objObserver->getObserverProperty ( $loggedUser, 'UT' ))) {
				$content1 = _("Time (local time)");
				$time = $objCometObservation->getLocalTime ( $_GET ['observation'] );
			} else {
				$content1 = _("Time (UT)");
				$time = $objCometObservation->getTime ( $_GET ['observation'] );
			}
			$time = sscanf ( sprintf ( "%04d", $time ), "%2d%2d" );
			$content2 = $time [0] . ":" . sprintf ( "%02d", $time [1] );
		}
		echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2 . "</td>";
		echo "</tr>";
		if ($objCometObservation->getLocationId ( $_GET ['observation'] ) != 0 && $objCometObservation->getLocationId ( $_GET ['observation'] ) != 1) {
			$content1 = _("Location");
			$content2 = "<a href=\"" . $baseURL . "index.php?indexAction=detail_location&amp;location=" . urlencode ( $objCometObservation->getLocationId ( $_GET ['observation'] ) ) . "\">" . $objLocation->getLocationPropertyFromId ( $objCometObservation->getLocationId ( $_GET ['observation'] ), 'name' ) . "</a>";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
			echo "<td>" . $content2 . "</td>";
			echo "</tr>";
		}
		if ($objCometObservation->getInstrumentId ( $_GET ['observation'] ) != 0) {
			$content1 = _("Instrument");
			$inst = $objInstrument->getInstrumentPropertyFromId ( $objCometObservation->getInstrumentId ( $_GET ['observation'] ), 'name' );
			if ($objCometObservation->getMagnification ( $_GET ['observation'] ) != 0)
				$inst = $inst . " (" . $objCometObservation->getMagnification ( $_GET ['observation'] ) . "x)";
			if (strcmp ( $objInstrument->getInstrumentPropertyFromId ( $objCometObservation->getInstrumentId ( $_GET ['observation'] ), 'name' ), "Naked eye" ) == 0)
				$inst = _("Naked Eye");
			$content2 = "<a href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $objCometObservation->getInstrumentId ( $_GET ['observation'] ) ) . "\">" . $inst . "</a>";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
			echo "<td>" . $content2 . "</td>";
			echo "</tr>";
		}
		if ($objCometObservation->getMethode ( $_GET ['observation'] ) != "") {
			$content1 = _("Magnitude method");
			$descr = $ICQMETHODS->getDescription ( $objCometObservation->getMethode ( $_GET ['observation'] ) );
			$content2 = $objCometObservation->getMethode ( $_GET ['observation'] ) . " - " . $descr;
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		if ($objCometObservation->getChart ( $_GET ['observation'] ) != "") {
			$content1 = _("Magnitude reference chart");
			$descr = $ICQREFERENCEKEYS->getDescription ( $objCometObservation->getChart ( $_GET ['observation'] ) );
			$content2 = $objCometObservation->getChart ( $_GET ['observation'] ) . " - " . $descr;
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		if ($objCometObservation->getMagnitude ( $_GET ['observation'] ) > - 90) {
			$content1 = _("Magnitude");
			$content2 = "";
			if ($objCometObservation->getMagnitudeWeakerThan ( $_GET ['observation'] ) == "1")
				$content2 .= _("Weaker than") . "&nbsp;";
			$content2 .= sprintf ( "%01.1f", $objCometObservation->getMagnitude ( $_GET ['observation'] ) );
			if ($objCometObservation->getMagnitudeUncertain ( $_GET ['observation'] ) == "1")
				$content2 .= "&nbsp;(" . _("Uncertain") . ")";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		if ($objCometObservation->getDc ( $_GET ['observation'] ) != '') {
			$content1 = _("Degree of condensation");
			$content2 = $objCometObservation->getDc ( $_GET ['observation'] );
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		if ($objCometObservation->getComa ( $_GET ['observation'] ) > - 90) {
			$content1 = _("Coma");
			$content2 = $objCometObservation->getComa($_GET['observation']) . "'";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		if ($objCometObservation->getTail ( $_GET ['observation'] ) > - 90) {
			$content1 = _("Length of the tail");
			$content2 = $objCometObservation->getTail ( $_GET ['observation'] ) . "'";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		if ($objCometObservation->getPa ( $_GET ['observation'] ) > - 90) {
			$content1 = _("Position Angle");
			$content2 = $objCometObservation->getPa ( $_GET ['observation'] ) . "&deg;";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2 . "</td>";
		echo "</tr>";
		}
		$description = $objCometObservation->getDescription ( $_GET ['observation'] );
		if ($description != "") {
			$content1 = _("Description");
			$content2 = "<textarea maxlength=\"500\" name=\"description\" class=\"description\" readonly cols=\"100\" rows=\"5\" >" . $objPresentations->br2nl ( $description ) . "</textarea>";
			echo "<tr><td><strong>" . $content1 . "</strong></td>";
		echo "<td>" . $content2. "</td>";
		echo "</tr>";
		}
		echo "</table>";
		$upload_dir = 'cometdrawings';
		$dir = opendir ( $instDir . 'comets/' . $upload_dir );
		while ( FALSE !== ($file = readdir ( $dir )) ) {
			if ("." == $file or ".." == $file) {
				continue; // skip current directory and directory above
			}
			if (fnmatch ( $_GET ['observation'] . "_resized.gif", $file ) || fnmatch ( $_GET ['observation'] . "_resized.jpg", $file ) || fnmatch ( $_GET ['observation'] . "_resized.png", $file )) {
				$content1 = $baseURL . "comets/" . $upload_dir . "/" . $_GET ['observation'] . ".jpg";
				$content2 = "<a href=\"" . $baseURL . "comets/" . $upload_dir . "/" . $_GET ['observation'] . ".jpg\" data-lightbox=\"image-1\" data-title=\"\"><img class=\"account\" src=\"" . $baseURL . "comets/$upload_dir" . "/" . "$file\" alt=\"\"></img></a>";
				echo $content2;
				echo "<hr />";
			}
		}
		$role = $objObserver->getObserverProperty ( $loggedUser, 'role', 2 );
		if (($loggedUser == $objCometObservation->getObserverId ( $_GET ['observation'] )) || ($role == ROLEADMIN) || ($role == ROLECOMETADMIN)) {
			echo "<p><a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=comets_adapt_observation&amp;observation=" . $_GET ['observation'] . "\">" . _("Change observation") . "</a>";
			echo "&nbsp;&nbsp;<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=comets_validate_delete_observation&amp;observationid=" . $_GET ['observation'] . "\">" . _("Delete observation") . "</a></p>";
		}
		if ($loggedUser != "") {
			$observerid = $objCometObservation->getObserverId ( $_GET ['observation'] );
			$name = $objObserver->getObserverProperty ( $observerid, 'firstname' ) . " " . $objObserver->getObserverProperty ( $observerid, 'name' ) . " ";

			$date = sscanf ( $objCometObservation->getDate ( $_GET ['observation'] ), "%4d%2d%2d" );
			$object = $objCometObject->getName ( $objCometObservation->getObjectId ( $_GET ['observation'] ) );

			$subject = sprintf(
                _('Your observation of %s on %s'), 
                $object, 
                $date[2] . "/" . $date[1] . "/" . $date[0]
            );
            echo "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=new_message&amp;receiver=" . urlencode ( $observerid ) . "&amp;subject=" . urlencode($subject) . "\"><span class=\"glyphicon glyphicon-envelope\"></span> " 
                . sprintf(
                    _('%s about this observation') . "</a>", 
                    $name);
			echo "<br /><br />";
		}
	}
	echo ("</div>");
}
?>
