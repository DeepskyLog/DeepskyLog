<?php
// top_observers.php
// generates an overview of all observers and their rank
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	top_observers ();
function top_observers() {
	global $baseURL, $step, $objCometObservation, $objPresentations, $objObserver, $objUtil;
	$rank = $objCometObservation->getPopularObservers ();
	$link = $baseURL . "index.php?indexAction=comets_rank_observers";
	echo "<div id=\"main\">";
	echo "<h4>" . LangTopObserversTitle . "</h4>";
	echo "<hr />";
	$count = 0;
	echo "<table class=\"table sort-tablecometobservers table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th class=\"filter-false columnSelector-disable\">" . LangTopObserversHeader1 . "</th>";
	echo "<th>" . LangTopObserversHeader2 . "</th>";
	echo "<th>" . LangTopObserversHeader3 . "</th>";
	echo "<th>" . LangTopObserversHeader4 . "</th>";
	echo "<th>" . LangTopObserversHeader6 . "</th>";
	echo "</tr></thead><tbody>";
	$numberOfObservations = $objCometObservation->getNumberOfObservations ();
	$numberOfObservationsThisYear = $objCometObservation->getNumberOfObservationsThisYear ();
	$numberOfDifferentObjects = $objCometObservation->getNumberOfDifferentObjects ();
	while ( list ( $key, $value ) = each ( $rank ) ) {
		$name = $objObserver->getObserverProperty ( $value, 'name' );
		$firstname = $objObserver->getObserverProperty ( $value, 'firstname' );
		echo "<tr>
				<td>" . ($count + 1) . "</td>
				<td><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $value ) . "\">$firstname&nbsp;$name</a></td>";
		echo "  <td> " . $objCometObservation->getObservationsThisObserver ( $value ) . " &nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf ( "%.2f", (($objCometObservation->getObservationsThisObserver ( $value ) / $numberOfObservations) * 100) ) . "%) </td>";
		$objCometObservationThisYear = $objCometObservation->getObservationsThisYear ( $value );
		if ($numberOfObservationsThisYear != 0) {
			$percentObservations = ($objCometObservationThisYear / $numberOfObservationsThisYear) * 100;
		} else {
			$percentObservations = 0;
		}
		echo "<td>" . $objCometObservationThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf ( "%.2f", $percentObservations ) . "%)</td>";
		$numberOfObjects = $objCometObservation->getNumberOfObjects ( $value );
		echo "<td>" . $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;(" . sprintf ( "%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100) ) . "%)</td>";
		echo "</tr>";
		$count ++;
	}
	echo "</tbody><tfoot><tr><td>" . LangTopObservers1 . "</td><td></td><td>$numberOfObservations</td><td>$numberOfObservationsThisYear</td><td>$numberOfDifferentObjects</td></tr></tfoot>";
	echo "</table>";
	echo $objUtil->addTablePager ( "cometobservers" );
	
	echo $objUtil->addTableJavascript ( "cometobservers" );
	echo "<hr />";
	echo "</div>";
}
?>