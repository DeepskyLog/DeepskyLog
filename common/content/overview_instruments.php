<?php
// overview_instruments.php
// generates an overview of all instruments (admin only)
if ((! isset ( $inIndex )) || (! $inIndex)) {
    include "../../redirect.php";
} elseif (! $loggedUser) {
    throw new Exception ( LangException002 );
} elseif ($_SESSION ['admin'] != "yes") {
    throw new Exception ( LangException001 );
} else {
    overview_instruments ();
}
function overview_instruments() {
	global $baseURL, $step, $min, $objInstrument, $objObserver, $objPresentations, $objUtil;
	$telescopes = $objInstrument->getSortedInstruments ( 'name', '%' );
	$insts = $objObserver->getListOfInstruments ();

	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewInstrumentsTitle1 . "</h4>";

	echo "<hr />";
	echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th>" . LangOverviewInstrumentsName . "</th>";
	echo "<th>" . LangOverviewInstrumentsDiameter . "</th>";
	echo "<th>" . LangOverviewInstrumentsFD . "</th>";
	echo "<th>" . LangOverviewInstrumentsFixedMagnification . "</th>";
	echo "<th>" . LangOverviewInstrumentsType . "</th>";
	echo "<th>" . LangViewObservationField2 . "</th>";
	echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>";
	echo "</tr></thead>";
	$count = 0;
	while ( list ( $key, $value ) = each ( $telescopes ) ) {
		$name = $objInstrument->getInstrumentPropertyFromId ( $value, 'name' );
		$diameter = round ( $objInstrument->getInstrumentPropertyFromId ( $value, 'diameter' ), 0 );
		$fd = round ( $objInstrument->getInstrumentPropertyFromId ( $value, 'fd' ), 1 );
		if ($fd == "0")
			$fd = "-";
		$type = $objInstrument->getInstrumentPropertyFromId ( $value, 'type' );
		$fixedMagnification = $objInstrument->getInstrumentPropertyFromId ( $value, 'fixedMagnification' );
		if ($fixedMagnification == "0")
			$fixedMagnification = "-";
		$observer = $objInstrument->getObserverFromInstrument ( $value );
		echo "<tr>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_instrument&amp;instrument=" . urlencode ( $value ) . "\">" . $name . "</a></td>";
		echo "<td>$diameter</td>";
		echo "<td>$fd</td>";
		echo "<td>$fixedMagnification</td>";
		echo "<td>";
		if ($type == INSTRUMENTREFLECTOR) {
			echo (InstrumentsReflector);
		}
		if ($type == INSTRUMENTFINDERSCOPE) {
			echo (InstrumentsFinderscope);
		}
		if ($type == INSTRUMENTREFRACTOR) {
			echo (InstrumentsRefractor);
		}
		if ($type == INSTRUMENTREST) {
			echo (InstrumentsOther);
		}
		if ($type == INSTRUMENTBINOCULARS) {
			echo (InstrumentsBinoculars);
		}
		if ($type == INSTRUMENTCASSEGRAIN) {
			echo (InstrumentsCassegrain);
		}
		if ($type == INSTRUMENTSCHMIDTCASSEGRAIN) {
			echo (InstrumentsSchmidtCassegrain);
		}
		if ($type == INSTRUMENTKUTTER) {
			echo (InstrumentsKutter);
		}
		if ($type == INSTRUMENTMAKSUTOV) {
			echo (InstrumentsMaksutov);
		}
		echo "</td>";
		echo "<td>" . $observer . "</td>";
		echo "<td>";
		// $queries = array("instrument" => $value);
		// $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");
		// $obscom = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
		if (! ($objInstrument->getInstrumentUsedFromId ( $value ))) // no observations with instrument yet
			echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_instrument&amp;instrumentid=" . urlencode ( $value ) . "\">" . LangRemove . "</a>";
		echo "</td>";
		echo "</tr>";
		$count ++;
	}
	echo "</table>";
	echo "<hr />";
	echo "</div>";

	$objUtil->addPager ( "", $count );
}
?>
