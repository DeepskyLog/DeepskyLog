<?php
// selected_observations2.php
// generates an overview of selected observations in the database
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	selected_observations ();
function selected_observations() {
	global $instDir, $baseURL, $loggedUser, $step, $dateformat, $objPresentations, $objUtil;
	$observations = new CometObservations ();
	$instruments = new Instruments ();
	$observers = new Observers ();
	$objects = new CometObjects ();

	$util = $objUtil;

	// TITLE

	echo "<div id=\"main\">";

	$mindate = '';
	$maxdate = '';
	if ($_GET ['observer'] || $_GET ['instrument'] || $_GET ['site'] || $_GET ['minyear'] || $_GET ['maxyear'] || ($_GET ['mindiameter'] && $_GET ['mindiameterunits']) || ($_GET ['maxdiameter'] && $_GET ['maxdiameterunits']) || $_GET ['minmag'] || $_GET ['maxmag'] || $_GET ['description'] || $_GET ['mindc'] || $_GET ['maxdc'] || $_GET ['mincoma'] || $_GET ['maxcoma'] || $_GET ['mintail'] || $_GET ['maxtail'] || $_GET ['object']) 	// at least 1 field to search on
	{

		if ($_GET ['minyear'] && $_GET ['minmonth'] && $_GET ['minday']) 		// exact date given
		{
			$mindate = $_GET ['minyear'] . sprintf ( "%02d", $_GET ['minmonth'] ) . sprintf ( "%02d", $_GET ['minday'] );
		} elseif ($_GET ['minyear'] && $_GET ['minmonth']) 		// month and year given
		{
			$mindate = $_GET ['minyear'] . sprintf ( "%02d", $_GET ['minmonth'] ) . "00";
		} elseif ($_GET ['minyear']) 		// only year given
		{
			$mindate = $_GET ['minyear'] . "0000";
		}

		if ($_GET ['maxyear'] && $_GET ['maxmonth'] && $_GET ['maxday']) 		// exact date given
		{
			$maxdate = $_GET ['maxyear'] . sprintf ( "%02d", $_GET ['maxmonth'] ) . sprintf ( "%02d", $_GET ['maxday'] );
		} elseif ($_GET ['maxyear'] && $_GET ['maxmonth']) 		// month and year given
		{
			$maxdate = $_GET ['maxyear'] . sprintf ( "%02d", $_GET ['maxmonth'] ) . "31";
		} elseif ($_GET ['maxyear']) 		// only year given
		{
			$maxdate = $_GET ['maxyear'] . "1231";
		}

		if ($_GET ['mindiameter'] && ($_GET ['mindiameterunits'] == "inch")) 		// convert minimum diameter in inches to mm
		{
			$mindiam = $_GET ['mindiameter'] * 25.4;
		} else {
			$mindiam = $_GET ['mindiameter'];
		}

		if ($_GET ['maxdiameter'] && ($_GET ['maxdiameterunits'] == "inch")) 		// convert maximum diameter in inches to mm
		{
			$maxdiam = $_GET ['maxdiameter'] * 25.4;
		} else {
			$maxdiam = $_GET ['maxdiameter'];
		}

		$maxmag = $_GET ['maxmag'];
		$minmag = $_GET ['minmag'];
		$description = $_GET ['description'];
		$object = $_GET ['object'];
		$mintail = $_GET ['mintail'];
		$maxtail = $_GET ['maxtail'];
		$mincoma = $_GET ['mincoma'];
		$maxcoma = $_GET ['maxcoma'];
		$mindc = $_GET ['mindc'];
		$maxdc = $_GET ['maxdc'];
		$observer = $_GET ['observer'];

		if (array_key_exists ( 'instrument', $_GET ) && $_GET ['instrument'] != "") {
			$instrument = $_GET ['instrument'];
			$name = $instruments->getInstrumentPropertyFromId ( $instrument, 'name' );
			$instrument = $instruments->getId ( $name, $loggedUser );
		} else {
			$instrument = '';
		}

		if (array_key_exists ( 'site', $_GET ) && $_GET ['site'] != "") {
			$site = $_GET ['site'];
			$name = $objLocation->getLocationPropertyFromId ( $site, 'name' );
			$site = $objLocation->getLocationId ( $name, $loggedUser );
		} else {
			$site = '';
		}

		// QUERY

		$query = array (
				"object" => $object,
				"observer" => $observer,
				"instrument" => $instrument,
				"location" => $site,
				"mindate" => $mindate,
				"maxdate" => $maxdate,
				"maxdiameter" => $maxdiam,
				"mindiameter" => $mindiam,
				"maxmag" => $maxmag,
				"minmag" => $minmag,
				"description" => $description,
				"mintail" => $mintail,
				"maxtail" => $maxtail,
				"mincoma" => $mincoma,
				"maxcoma" => $maxcoma,
				"mindc" => $mindc,
				"maxdc" => $maxdc
		);

		if (! ($observers->getObserverProperty ( $loggedUser, 'UT' ))) {
			if ($mindate != "") {
				$mindate = $mindate - 1;
			}
			if ($maxdate != "") {
				$maxdate = $maxdate + 1;
			}
		}
		$sort = "date";
		if (isset ( $catalogsearch )) {
			if ($catalogsearch == "yes") {
				$obs = $observations->getObservationFromQuery ( $query, $sort, 0 ); // LIKE
			} else {
				$obs = $observations->getObservationFromQuery ( $query, $sort ); // EXACT MATCH
			}
		} else {
			$obs = $observations->getObservationFromQuery ( $query, $sort ); // EXACT MATCH
		}

		// Dates can changes when we use local time!
		if (! ($observers->getObserverProperty ( $loggedUser, 'UT' ))) {
			if ($mindate != "" || $maxdate != "") {
				if ($mindate != "") {
					$mindate = $mindate + 1;
				}
				if ($maxdate != "") {
					$maxdate = $maxdate - 1;
				}

				$newkey = 0;

				$new_obs = Array ();

				while ( list ( $key, $value ) = each ( $obs ) ) 				// go through observations array
				{
					$newdate = $observations->getLocalDate ( $value );

					if ($mindate != "" && $maxdate != "") {
						if (($newdate >= $mindate) && ($newdate <= $maxdate)) {
							$new_obs [$newkey] = $value;
							$newkey ++;
						}
					} else if ($maxdate != "") {
						if ($newdate <= $maxdate) {
							$new_obs [$newkey] = $value;
							$newkey ++;
						}
					} else if ($mindate != "") {
						if ($newdate >= $mindate) {
							$new_obs [$newkey] = $value;
							$newkey ++;
						}
					}
				}
				$obs = $new_obs;
			}
		}

		if (sizeof ( $obs ) > 0) 		// ONLY WHEN OBSERVATIONS AVAILABLE
		{
			$link = $baseURL . "index.php?indexAction=comets_result_selected_observations" . "&amp;object=" . urlencode ( $_GET ['object'] ) . "&amp;instrument=" . urlencode ( $_GET ['instrument'] ) . "&amp;observer=" . urlencode ( $_GET ['observer'] ) . "&amp;site=" . urlencode ( $_GET ['site'] ) . "&amp;minyear=" . $_GET ['minyear'] . "&amp;minmonth=" . $_GET ['minmonth'] . "&amp;minday=" . $_GET ['minday'] . "&amp;maxyear=" . $_GET ['maxyear'] . "&amp;maxmonth=" . $_GET ['maxmonth'] . "&amp;maxday=" . $_GET ['maxday'] . "&amp;maxdiameter=" . $_GET ['maxdiameter'] . "&amp;maxdiameterunits=" . urlencode ( $_GET ['maxdiameterunits'] ) . "&amp;mindiameter=" . $_GET ['mindiameter'] . "&amp;mindiameterunits=" . urlencode ( $_GET ['mindiameterunits'] ) . "&amp;maxmag=" . $_GET ['maxmag'] . "&amp;minmag=" . $_GET ['minmag'] . "&amp;description=" . $_GET ['description'] . "&amp;mindc=" . $_GET ['mindc'] . "&amp;maxdc=" . $_GET ['maxdc'] . "&amp;mincoma=" . $_GET ['mincoma'] . "&amp;maxcoma=" . $_GET ['maxcoma'] . "&amp;mintail=" . $_GET ['mintail'] . "&amp;maxtail=" . $_GET ['maxtail'];

			echo "<h4>" . _("Overview selected observations") . "</h4>";
			echo "<hr />";
			echo "<table class=\"table sort-tablecometobservations table-condensed table-striped table-hover tablesorter custom-popup\">";

			echo "<thead><tr>";

			// OBJECT NAME

			echo "<th>" . _("Object name") . "</th>";

			// OBSERVER

			echo "<th>" . _("Observer") . "</th>";

			// DATE

			echo "<th>" . _("Date") . "</th>";

			// MAGNITUDE
			echo "<th>" . _("Magnitude") . "</th>";

			// INSTRUMENT
			echo "<th>" . _("Instrument") . "</th>";

			// COMA
			echo "<th>" . _("Coma") . "</th>";

			// DC
			echo "<th>" . _("DC") . "</th>";

			// TAIL
			echo "<th>" . _("Tail") . "</td>";
			echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th></tr></thead>";
			$count = 0;

			while ( list ( $key, $value ) = each ( $obs ) ) 			// go through observations array
			{
				// OBJECT

				$object = $observations->getObjectId ( $value );

				// OBSERVER

				$observer = $observations->getObserverId ( $value );

				// INSTRUMENT

				$temp = $observations->getInstrumentId ( $value );
				$instrument = $instruments->getInstrumentPropertyFromId ( $temp, 'name' );
				$instrumentsize = $instruments->getInstrumentPropertyFromId ( $temp, 'diameter' );
				if ($instrument == "Naked eye") {
					$instrument = _("Naked Eye");
				}

				// MAGNITUDE

				$mag = $observations->getMagnitude ( $value );

				if ($mag < - 90) {
					$mag = '';
				} else {
					$mag = sprintf ( "%2.01f", $mag );
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
				if ($tail > - 90) {
					$tail = $tail . "'";
				} else {
					$tail = '';
				}

				// OUTPUT

				echo ("<tr>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $object ) . "\">" . $objects->getName ( $object ) . "</a></td>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $observer ) . "\">" . $observers->getObserverProperty ( $observer, 'firstname' ) . "&nbsp;" . $observers->getObserverProperty ( $observer, 'name' ) . "</a></td>
	            <td>");

				if ($instrument != _("Naked Eye") && $instrument != "") {
					$instrument = $instrument . " (" . $instrumentsize . "&nbsp;mm" . ")";
				}

				if (! ($observers->getObserverProperty ( $loggedUser, 'UT' ))) {
					$date = sscanf ( $observations->getLocalDate ( $value ), "%4d%2d%2d" );
				} else {
					$date = sscanf ( $observations->getDate ( $value ), "%4d%2d%2d" );
				}

				echo date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );

				// TIME

				if (! ($observers->getObserverProperty ( $loggedUser, 'UT' ))) {
					$time = sscanf ( sprintf ( "%04d", $observations->getLocalTime ( $value ) ), "%2d%2d" );
				} else {
					$time = sscanf ( sprintf ( "%04d", $observations->getTime ( $value ) ), "%2d%2d" );
				}

				echo ("&nbsp;(");

				printf ( "%02d", $time [0] );

				echo (":");

				printf ( "%02d", $time [1] );

				$time = sscanf ( sprintf ( "%04d", $observations->getTime ( $value ) ), "%2d%2d" );

				echo (")");

				echo ("</td>
	            <td>$mag</td>
	            <td>$instrument</td>
	            <td>$coma</td>
	            <td>$dc</td>
	            <td>$tail</td>
	            <td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");

				// LINK TO DRAWING (IF AVAILABLE)

				$upload_dir = 'cometdrawings';
				$dir = opendir ( $instDir . "comets/" . $upload_dir );

				while ( FALSE !== ($file = readdir ( $dir )) ) {
					if ("." == $file or ".." == $file) {
						continue; // skip current directory and directory above
					}
					if (fnmatch ( $value . "_resized.gif", $file ) || fnmatch ( $value . "_resized.jpg", $file ) || fnmatch ( $value . "_resized.png", $file )) {
						echo ("&nbsp;+&nbsp;");
						echo _("drawing");
					}
				}

				echo ("</a></td></tr>");
				$count++;
			}

			echo "</table>";
			$objUtil->addPager ( "cometobservations", $count );

			echo "<hr />";

			$_SESSION ['observation_query'] = $obs;

			echo "<p><a class=\"btn btn-primary\" href=\"" . $baseURL . "cometobservations.pdf.php\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . _("pdf") . "</a>";
			echo "  <a class=\"btn btn-primary\" href=\"" . $baseURL . "cometobservations.icq\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . _("ICQ") . "</a></p>";
		} else 		// NO OBSERVATIONS FOUND
		{
			echo "<p>" . _("Sorry, no observations found!") . "</p>";
		}
		echo ("<p><a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=comets_query_observations\">" . _("Perform another search") . "</a></p>");
	} else 	// no search fields filled in
	{
		echo "<p>" . _("You didn't specify any queries to search on!") . "</p>";
		echo "<p><a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=comets_query_observations\">" . _("Perform another search") . "</a>";
		echo " " . _("or") . " ";
		echo "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=comets_all_observations\">" . _("View all observations") . "</a></p>";
	}
	echo ("</div>");
}
?>
