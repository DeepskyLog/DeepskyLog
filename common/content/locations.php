<?php
// locations.php
// allows the user to add a new site
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	locations ();
function locations() {
	global $baseURL, $loggedUser, $loggedUserName, $sites, $objLocation, $objObserver, $objPresentations, $objUtil;
	$sort = $objUtil->checkRequestKey ( 'sort', 'name' );
	$locationid = $objUtil->checkRequestKey ( 'locationid' );
	$tempCountryList = "<select name=\"country\" class=\"form-control\">";
	$countries = $objLocation->getCountries ();
	$tempCountryList .= "<option value=\"\">-----</option>";
	while ( list ( $key, $value ) = each ( $countries ) ) {
		$sites = $objLocation->getSortedLocations ( $sort, $loggedUser );
		$locs = $objObserver->getListOfLocations ();
		if ($objUtil->checkRequestKey ( 'country' ) == $value)
			$tempCountryList .= "<option selected=\"selected\" value=\"" . $value . "\">" . $value . "</option>";
		elseif ($locationid && ($objLocation->getLocationPropertyFromId ( $locationid, 'country' ) == $value))
			$tempCountryList .= "<option selected=\"selected\" value=\"" . $value . "\">" . $value . "</option>";
		else
			$tempCountryList .= "<option value=\"" . $value . "\">" . $value . "</option>";
	}
	$tempCountryList .= "</select>";
	$latitudedeg = $objUtil->checkRequestKey ( 'latitude' );
	$latitudemin = $objUtil->checkRequestKey ( 'latitudemin' );
	$longitudedeg = $objUtil->checkRequestKey ( 'longitude' );
	$longitudemin = $objUtil->checkRequestKey ( 'longitudemin' );
	if ($locationid = $objUtil->checkRequestKey ( 'locationid' )) {
		// TODO: ROUND???
		$latitudestr = $objLocation->getLocationPropertyFromId ( $locationid, 'latitude' );
		$latitudedeg = ( int ) ($latitudestr);
		$latitudeminfloat = ( (( float ) ($latitudestr) - ( int ) ($latitudestr)) * 60.0 );
		$latitudemin = (int)  $latitudeminfloat;
		$latitudesec = ($latitudeminfloat - $latitudemin) * 60.0;
		$longitudestr = $objLocation->getLocationPropertyFromId ( $locationid, 'longitude' );
		$longitudedeg = ( int ) ($longitudestr);
		$longitudeminfloat = ( (( float ) ($longitudestr) - ( int ) ($longitudestr)) * 60 );
		$longitudemin = (int)  $longitudeminfloat;
		$longitudesec = ($longitudeminfloat - $longitudemin) * 60.0;
	}
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewSiteTitle . " " . $loggedUserName . "</h4>";
	echo "<hr />";
 	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
 	echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_location\" />";
	echo "<input type=\"submit\" class=\"btn btn-primary pull-right tour4\" name=\"add\" value=\"" . LangAddSiteButton . "\" />&nbsp;";
	echo "</form>";
	$objLocation->showLocationsObserver ();
	echo "<br /><br />";
	
	echo "</div></form>";
	echo "</div>";
}
?>
