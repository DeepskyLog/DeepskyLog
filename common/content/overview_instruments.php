<?php
// overview_instruments.php
// generates an overview of all instruments (admin only)
if ((! isset ( $inIndex )) || (! $inIndex)) {
    include "../../redirect.php";
} elseif (! $loggedUser) {
    throw new Exception(_("You need to be logged in to change your locations or equipment."));
} elseif ($_SESSION ['admin'] != "yes") {
    throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
} else {
    overview_instruments ();
}
function overview_instruments() {
	global $baseURL, $step, $min, $objInstrument, $objObserver, $objPresentations, $objUtil;
	$telescopes = $objInstrument->getSortedInstruments ( 'name', '%' );
	$insts = $objObserver->getListOfInstruments ();

	echo "<div id=\"main\">";
	echo "<h4>" . _("Overview Instruments") . "</h4>";

	echo "<hr />";
	echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th>" . _("Name") . "</th>";
	echo "<th>" . _("Diameter (mm)") . "</th>";
	echo "<th>" . _("F/D") . "</th>";
	echo "<th>" . _("Fixed magnification") . "</th>";
	echo "<th>" . _("Type") . "</th>";
	echo "<th>" . _("Observer") . "</th>";
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
			echo (_("Reflector"));
		}
		if ($type == INSTRUMENTFINDERSCOPE) {
			echo (_("Finderscope"));
		}
		if ($type == INSTRUMENTREFRACTOR) {
			echo (_("Refractor"));
		}
		if ($type == INSTRUMENTREST) {
			echo (_("Other"));
		}
		if ($type == INSTRUMENTBINOCULARS) {
			echo (_("Binoculars"));
		}
		if ($type == INSTRUMENTCASSEGRAIN) {
			echo (_("Cassegrain"));
		}
		if ($type == INSTRUMENTSCHMIDTCASSEGRAIN) {
			echo (_("Schmidt Cassegrain"));
		}
		if ($type == INSTRUMENTKUTTER) {
			echo (_("Kutter"));
		}
		if ($type == INSTRUMENTMAKSUTOV) {
			echo (_("Maksutov"));
		}
		echo "</td>";
		echo "<td>" . $observer . "</td>";
		echo "<td>";
		// $queries = array("instrument" => $value);
		// $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");
		// $obscom = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
		if (! ($objInstrument->getInstrumentUsedFromId ( $value ))) // no observations with instrument yet
			echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_instrument&amp;instrumentid=" . urlencode ( $value ) . "\">" . _("Delete") . "</a>";
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
