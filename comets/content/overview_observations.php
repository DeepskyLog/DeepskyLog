<?php
// overview_observations.php
// generates an overview of all observations in the database
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	overview_observations ();
function overview_observations() {
	global $instDir, $baseURL, $step, $loggedUser, $dateformat, $objInstrument, $objCometObject, $objCometObservation, $objUtil, $objObserver, $objPresentations;
	$objects = new CometObjects ();
	$instruments = new Instruments ();
	$observers = new Observers ();

	$sort = "date"; // standard sort on date
	$obs = $objCometObservation->getSortedObservations ( $sort );
	if (sizeof ( $obs ) > 0)
		krsort ( $obs );

		// save $obs as a session variable
	$_SESSION ['obs'] = $obs;
	$_SESSION ['observation_query'] = $obs;

	echo "<div id=\"main\">";
	$link = $baseURL . "index.php?indexAction=comets_all_observations";

	echo "<h4>" . _("Overview all observations") . "</h4>";
	echo "<hr />";

	if (sizeof ( $obs ) > 0) {
		// OBJECT TABLE HEADERS

		echo "<table class=\"table sort-tableallcometobservations table-condensed table-striped table-hover tablesorter custom-popup\">
	      <thead>
		  <tr>
	      <th>" . _("Object name") . "</th>
	      <th>" . _("Observer") . "</th>
	      <th>" . _("Date") . "</th>
	      <th>" . _("Magnitude") . "</th>
	      <th>" . _("Instrument") . "</th>
	      <th>" . _("Coma") . "</th>
	      <th>" . _("DC") . "</th>
	      <th>" . _("Tail") . "</th>
	      <th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>
	      </tr>
	      </thead>";
		while ( list ( $key, $value ) = each ( $obs ) ) 		// go through observations array
		{
			// OBJECT

			$object = $objCometObservation->getObjectId ( $value );

			// OBSERVER

			$observer = $objCometObservation->getObserverId ( $value );

			// DATE

			if ($objObserver->getObserverProperty ( $loggedUser, 'UT' )) {
				$date = sscanf ( $objCometObservation->getDate ( $value ), "%4d%2d%2d" );
			} else {
				$date = sscanf ( $objCometObservation->getLocalDate ( $value ), "%4d%2d%2d" );
			}

			// TIME
			if ($objObserver->getObserverProperty ( $loggedUser, 'UT' )) {
				$time = sscanf ( sprintf ( "%04d", $objCometObservation->getTime ( $value ) ), "%2d%2d" );
			} else {
				$time = sscanf ( sprintf ( "%04d", $objCometObservation->getLocalTime ( $value ) ), "%2d%2d" );
			}

			// INSTRUMENT

			$temp = $objCometObservation->getInstrumentId ( $value );
			$instrument = $objInstrument->getInstrumentPropertyFromId ( $temp, 'name' );
			$instrumentsize = round ( $objInstrument->getInstrumentPropertyFromId ( $temp, 'diameter' ), 0 );
			if ($instrument == "Naked eye") {
				$instrument = _("Naked Eye");
			}

			// MAGNITUDE

			$mag = $objCometObservation->getMagnitude ( $value );

			if ($mag < - 90) {
				$mag = '';
			} else {
				$mag = sprintf ( "%01.1f", $mag );
				if ($objCometObservation->getMagnitudeWeakerThan ( $value ) == "1") {
					$mag = "[" . $mag;
				}
				if ($objCometObservation->getMagnitudeUncertain ( $value ) == "1") {
					$mag = $mag . ":";
				}
			}

			// COMA

			$coma = $objCometObservation->getComa ( $value );
			if ($coma < - 90) {
				$coma = '';
			} else {
				$coma = $coma . "'";
			}

			// DC

			$dc = $objCometObservation->getDc ( $value );

			if ($dc < - 90) {
				$dc = '';
			}

			// TAIL

			$tail = $objCometObservation->getTail ( $value );
			if ($tail < - 90) {
				$tail = '';
			} else {
				$tail = $tail . "'";
			}

			// OUTPUT

			echo ("<tr>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $object ) . "\">" . $objCometObject->getName ( $object ) . "</a></td>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $observer ) . "\">" . $objObserver->getObserverProperty ( $observer, 'firstname' ) . "&nbsp;" . $objObserver->getObserverProperty ( $observer, 'name' ) . "</a></td><td>");

			echo date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );

			echo ("&nbsp;(");

			printf ( "%02d", $time [0] );

			echo (":");

			printf ( "%02d", $time [1] );

			if ($instrument != _("Naked Eye") && $instrumentsize != "0" && $instrumentsize != "1") {
				$instrument = $instrument . "(" . $instrumentsize . "&nbsp;mm" . ")";
			}

			echo (")</td>
	            <td>$mag</td>
	            <td>$instrument</td>
	            <td>$coma</td>
	            <td>$dc</td>
	            <td>$tail</td>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

			// LINK TO DRAWING (IF AVAILABLE)

			$upload_dir = 'cometdrawings';
			$dir = opendir ( $instDir . 'comets/' . $upload_dir );
			while ( FALSE !== ($file = readdir ( $dir )) ) {
				if ("." == $file or ".." == $file)
					continue; // skip current directory and directory above
				if (fnmatch ( $value . "_resized.gif", $file ) || fnmatch ( $value . "_resized.jpg", $file ) || fnmatch ( $value . "_resized.png", $file )) {
					echo ("&nbsp;+&nbsp;");
					echo _("drawing");
				}
			}

			echo ("</a></td></tr>");
		}

		echo "</table>";
		$objUtil->addPager ( "allcometobservations", sizeof ( $obs ) );
	}
	echo "<hr />";
	echo "</div>";
}
?>
