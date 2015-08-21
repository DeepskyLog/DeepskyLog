<?php
// selected_observations.php
// generates an overview of selected observations in the database
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	selected_observation ();
function selected_observation() {
	global $instDir, $baseURL, $dateformat, $step, $loggedUser, $objUtil, $objPresentations;
	// creation of objects

	$observations = new CometObservations ();
	$instruments = new Instruments ();
	$observers = new Observers ();
	$objects = new CometObjects ();
	$util = $objUtil;

	// selection of all observations of one object

	echo "<div id=\"main\">";
	if (isset ( $_GET ['objectname'] )) {
		$queries = array (
				"object" => $objects->getName ( $_GET ['objectname'] )
		); // sql query
		$sort = "id"; // standard sort on insertion date
		$obs = $observations->getObservationFromQuery ( $queries );
		if (sizeof ( $obs ) > 0) {
			krsort ( $obs );
		}

		// save $obs as a session variable

		$_SESSION ['obs'] = $obs;
		$_SESSION ['observation_query'] = $obs;

		$count = 0; // counter for altering table colors

		$link = "" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET ['objectname'];
		echo "<h4>" . LangSelectedObservationsTitle . $objects->getName ( $_GET ['objectname'] ) . "</h4>";
		echo "<hr />";

		if (sizeof ( $obs ) > 0) {
			echo "<table class=\"table sort-tablecometobservations table-condensed table-striped table-hover tablesorter custom-popup\">
	      <thead>
		  <tr>
	      <th>" . LangOverviewObservationsHeader1 . "</th>
	      <th>" . LangOverviewObservationsHeader2 . "</th>
	      <th>" . LangOverviewObservationsHeader4 . "</th>
	      <th>" . LangNewComet1 . "</th>
	      <th>" . LangViewObservationField3 . "</th>
	      <th>" . LangViewObservationField19 . "</th>
	      <th>" . LangViewObservationField18b . "</th>
	      <th>" . LangViewObservationField20b . "</th>
	      <th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>
	      </tr>
	      </thead>";

			while ( list ( $key, $value ) = each ( $obs ) ) 			// go through observations array
			{
				// OBJECT

				$object = $observations->getObjectId ( $value ); // overhead as this is every time the same object?!

				// OUTPUT

				echo ("<tr>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $object ) . "\">" . $objects->getName ( $object ) . "</a></td>");

				// OBSERVER

				$observer = $observations->getObserverId ( $value );

				echo ("<td>");

				echo ("<a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $observer ) . "\">" . $observers->getObserverProperty ( $observer, 'firstname' ) . "&nbsp;" . $observers->getObserverProperty ( $observer, 'name' ) . "</a>");

				echo ("</td>");

				// DATE

				if ($observers->getObserverProperty ( $loggedUser, 'UT' )) {
					$date = sscanf ( $observations->getDate ( $value ), "%4d%2d%2d" );
				} else {
					$date = sscanf ( $observations->getLocalDate ( $value ), "%4d%2d%2d" );
				}

				echo ("<td>");

				echo date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );

				// TIME

				echo (" (");

				if ($observers->getObserverProperty ( $loggedUser, 'UT' )) {
					$time = sscanf ( sprintf ( "%04d", $observations->getTime ( $value ) ), "%2d%2d" );
				} else {
					$time = sscanf ( sprintf ( "%04d", $observations->getLocalTime ( $value ) ), "%2d%2d" );
				}

				printf ( "%02d", $time [0] );
				echo (":");

				printf ( "%02d", $time [1] );

				$time = sscanf ( sprintf ( "%04d", $observations->getTime ( $value ) ), "%2d%2d" );

				echo (")</td>");

				// INSTRUMENT

				$temp = $observations->getInstrumentId ( $value );
				$instrument = $instruments->getInstrumentPropertyFromId ( $temp, 'name' );
				$instrumentsize = $instruments->getInstrumentPropertyFromId ( $temp, 'diameter' );
				if ($instrument == "Naked eye") {
					$instrument = InstrumentsNakedEye;
				}

				// MAGNITUDE

				$mag = $observations->getMagnitude ( $value );

				if ($mag < - 90) {
					$mag = '';
				} else {
					$mag = sprintf ( "%01.1f", $observations->getMagnitude ( $value ) );
				}

				// COMA

				$coma = $observations->getComa ( $value );
				if ($coma < - 90) {
					$coma = '';
				} else {
					$coma = $coma . "'";
				}

				// DC

				$dc = $observations->getDc ( $value );

				if ($dc < - 90) {
					$dc = '';
				}

				// TAIL

				$tail = $observations->getTail ( $value );
				if ($tail < - 90) {
					$tail = '';
				} else {
					$tail = $tail . "'";
				}

				if ($instrument != InstrumentsNakedEye && $instrument != "") {
					$instrument = $instrument . " (" . $instrumentsize . "&nbsp;mm" . ")";
				}

				echo (" <td>$mag</td>
	            <td>$instrument</td>
	            <td>$coma</td>
	            <td>$dc</td>
	            <td>$tail</td>");

				// DETAILS

				echo ("<td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

				// LINK TO DRAWING (IF AVAILABLE)

				echo ("</a></td></tr>");
			}

			echo ("</table>");
			$objUtil->addPager ( "cometobservations", sizeof ( $obs ) );

			echo "<hr />";
			echo "<a class=\"btn btn-success\" href=\"" . $baseURL . "cometobservations.pdf.php\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage4a . "</a>";
			echo "<br /><br />";
		} else 		// no observations of object
		{
			echo LangNoObservations;
		}
		echo "</div>";
	} elseif ($_GET ['user']) 	// selection of all observations of one observer
	{
		$query = array (
				"observer" => $_GET ['user']
		);
		$sort = "id"; // standard sort on date
		$obs = $observations->getObservationFromQuery ( $query, $sort );
		if (sizeof ( $obs ) > 0)
			krsort ( $obs );
			// save $obs as a session variable
		$_SESSION ['obs'] = $obs;
		$_SESSION ['observation_query'] = $obs;
		$link = "" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET ['user'];
		echo "<h4>" . LangSelectedObservationsTitle . $observers->getObserverProperty ( $_GET ['user'], 'firstname' ) . "&nbsp;" . $observers->getObserverProperty ( $_GET ['user'], 'name' ) . "</h4>";
		echo "<hr />";

		// NEW BEGIN

		if (sizeof ( $obs ) > 0) { // OBJECT TABLE HEADERS
			echo "<table class=\"table sort-tablecometobservations table-condensed table-striped table-hover tablesorter custom-popup\">
			      <thead>
				  <tr>
			      <th>" . LangOverviewObservationsHeader1 . "</th>
			      <th>" . LangOverviewObservationsHeader4 . "</th>
			      <th>" . LangNewComet1 . "</th>
			      <th>" . LangViewObservationField3 . "</th>
			      <th>" . LangViewObservationField19 . "</th>
			      <th>" . LangViewObservationField18b . "</th>
			      <th>" . LangViewObservationField20b . "</th>
			      <th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>
			      </thead>
			      </tr>";

			while ( list ( $key, $value ) = each ( $obs ) ) 			// go through observations array
			{
				// OBJECT

				$object = $observations->getObjectId ( $value );

				// OUTPUT

				echo ("<tr>
			            <td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $object ) . "\">" . $objects->getName ( $object ) . "</a></td>
			            <td>");

				// DATE

				if ($observers->getObserverProperty ( $loggedUser, 'UT' )) {
					$date = sscanf ( $observations->getDate ( $value ), "%4d%2d%2d" );
				} else {
					$date = sscanf ( $observations->getLocalDate ( $value ), "%4d%2d%2d" );
				}

				echo date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );

				// TIME

				echo ("&nbsp;(");

				if ($observers->getObserverProperty ( $loggedUser, 'UT' )) {
					$time = sscanf ( sprintf ( "%04d", $observations->getTime ( $value ) ), "%2d%2d" );
				} else {
					$time = sscanf ( sprintf ( "%04d", $observations->getLocalTime ( $value ) ), "%2d%2d" );
				}

				printf ( "%02d", $time [0] );

				echo (":");

				printf ( "%02d", $time [1] );

				echo (")</td>");

				// INSTRUMENT

				$temp = $observations->getInstrumentId ( $value );
				$instrument = $instruments->getInstrumentPropertyFromId ( $temp, 'name' );
				if ($instrument == "Naked eye") {
					$instrument = InstrumentsNakedEye;
				}

				// MAGNITUDE

				$mag = $observations->getMagnitude ( $value );

				if ($mag < - 90) {
					$mag = '';
				}

				// COMA

				$coma = $observations->getComa ( $value );
				if ($coma < - 90) {
					$coma = '';
				} else {
					$coma = $coma . "'";
				}

				// DC

				$dc = $observations->getDc ( $value );

				if ($dc < - 90) {
					$dc = '';
				}

				// TAIL

				$tail = $observations->getTail ( $value );
				if ($tail < - 90) {
					$tail = '';
				} else {
					$tail = $tail . "'";
				}

				echo (" <td>$mag</td>
			            <td>$instrument</td>
			            <td>$coma</td>
			            <td>$dc</td>
			            <td>$tail</td>");

				// DETAILS

				echo ("<td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

				// LINK TO DRAWING (IF AVAILABLE)

				$upload_dir = 'cometdrawings';
				$dir = opendir ( $instDir . "comets/" . $upload_dir );

				while ( FALSE !== ($file = readdir ( $dir )) ) {
					if ("." == $file or ".." == $file) {
						continue; // skip current directory and directory above
					}
					if (fnmatch ( $value . "_resized.gif", $file ) || fnmatch ( $value . "_resized.jpg", $file ) || fnmatch ( $value . "_resized.png", $file )) {
						echo ("&nbsp;+&nbsp;");
						echo LangDrawing;
					}
				}
				echo ("</a></td></tr>");
			}
			echo ("</table>");
			$objUtil->addPager ( "cometobservations", sizeof ( $obs ) );

			echo "<hr />";
			$_SESSION ['observation_query'] = $obs;
			echo "<a class=\"btn btn-success\" href=\"" . $baseURL . "cometobservations.pdf.php\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage4a . "</a>";
		}
		echo "<br /><br />";
		echo "</div>";
	}
}
?>
