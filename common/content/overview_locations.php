<?php
// overview_locations.php
// generates an overview of all locations (admin only)
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
elseif ($_SESSION ['admin'] != "yes")
	throw new Exception ( LangException001 );
else
	overview_locations ();
function overview_locations() {
	global $baseURL, $step, $min, $objLocation, $objObserver, $objObservation, $objPresentations, $objUtil;
	$sites = $objLocation->getSortedLocations ( "name" );
	$locs = $objObserver->getListOfLocations ();
	echo "<div id=\"main\">";
	echo "<h4>" . LangViewLocationTitle . "</h4>";
	echo "<hr />";
	echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th>" . LangViewLocationLocation . "</th>";
	echo "<th>" . LangViewLocationCountry . "</th>";
	echo "<th>" . LangViewLocationLongitude . "</th>";
	echo "<th>" . LangViewLocationLatitude . "</th>";
	echo "<th>" . LangAddSiteField6 . "</th>";
	echo "<th>" . LangViewLocationLimMag . "</th>";
	echo "<th>" . LangViewLocationSB . "</th>";
	echo "<th>" . LangViewObservationField2 . "</th>";
	echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>";
	echo "</tr></thead>";
	$count = 0;
	while ( list ( $key, $value ) = each ( $sites ) ) {
			$sitename = stripslashes ( $objLocation->getLocationPropertyFromId ( $value, 'name' ) );
			$country = $objLocation->getLocationPropertyFromId ( $value, 'country' );
			if ($objLocation->getLocationPropertyFromId ( $value, 'longitude' ) > 0)
				$longitude = "&nbsp;" . $objPresentations->decToString ( $objLocation->getLocationPropertyFromId ( $value, 'longitude' ) );
			else
				$longitude = $objPresentations->decToString ( $objLocation->getLocationPropertyFromId ( $value, 'longitude' ) );
			if ($objLocation->getLocationPropertyFromId ( $value, 'latitude' ) > 0)
				$latitude = "&nbsp;" . $objPresentations->decToString ( $objLocation->getLocationPropertyFromId ( $value, 'latitude' ) );
			else
				$latitude = $objPresentations->decToString ( $objLocation->getLocationPropertyFromId ( $value, 'latitude' ) );
			$timezone = $objLocation->getLocationPropertyFromId ( $value, 'timezone' );
			$observer = $objLocation->getLocationPropertyFromId ( $value, 'observer' );
			$limmag = $objLocation->getLocationPropertyFromId ( $value, 'limitingMagnitude' );
			if ($limmag < - 900)
				$limmag = "&nbsp;";
			$sb = $objLocation->getLocationPropertyFromId ( $value, 'skyBackground' );
			if ($sb < - 900)
				$sb = "&nbsp;";
			if ($value != "1") {
				echo "<tr>";
				echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_site&amp;location=" . urlencode ( $value ) . "\">$sitename</a></td>";
				echo "<td>" . $country . "</td>";
				echo "<td>" . $longitude . "</td>";
				echo "<td>" . $latitude . "</td>";
				echo "<td>" . $timezone . "</td>";
				echo "<td>" . $limmag . "</td>";
				echo "<td>" . $sb . "</td>";
				echo "<td>" . $observer . "</td>";
				// check if there are no observations made from this location
				$queries = array (
						"location" => $value 
				);
				$obs = $objObservation->getObservationFromQuery ( $queries, "", "1", "False" );
				// $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
				echo "<td>";
				// if(!sizeof($obs) > 0 && !in_array($value, $locs)) // && !sizeof($comobs) > 0) // no observations from location yet
				// echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_location&amp;locationid=" . urlencode($value) . "\">" . LangRemove . "</a>");
				if (! ($objLocation->getLocationUsedFromId ( $value )))
					echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_location&amp;locationid=" . urlencode ( $value ) . "\">" . LangRemove . "</a>";
				echo "</td>";
				echo "</tr>";
		}
		$count ++;
	}
	echo "</table>";
	echo "<hr />";
	echo "</div>";
	echo $objUtil->addTablePager ();
	
	echo $objUtil->addTableJavascript ();
}
?>
