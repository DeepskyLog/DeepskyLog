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
// 	$sites = $objLocation->getSortedLocations ( 'name' );
// 	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
// 	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
// 	$content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
// 	while ( list ( $key, $value ) = each ( $sites ) )
// 		$content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_site&amp;locationid=" . urlencode ( $value ) . "\" " . (($value == $objUtil->checkRequestKey ( 'locationid' )) ? " selected=\"selected\" " : '') . ">" . $objLocation->getLocationPropertyFromId ( $value, 'name' ) . "</option>";
// 	$content1b .= "</select>";
	
//	echo "<h4>" . LangAddSiteTitle . "</h4>";
//	echo "<hr />";
	
//	echo "<a href=\"" . $baseURL . "index.php?indexAction=search_sites\" class=\"btn btn-success\">" . LangAddSiteFieldSearchDatabase . "</a>";
	echo "<br /><br />";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteExisting . "</label>";
// 	echo "<div class=\"form-inline\">";
// 	echo $content1b;
// 	echo "</div></div>";
	
// 	echo LangAddSiteFieldOr . " " . LangAddSiteFieldManually;
// 	echo "<br /><br />";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField1 . "</label>";
// 	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'sitename' ) ) . stripslashes ( $objLocation->getLocationPropertyFromId ( $objUtil->checkRequestKey ( 'locationid' ), 'name' ) ) . "\" />";
// 	echo "</div>";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField2 . "</label>";
// 	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'region' ) ) . stripslashes ( $objLocation->getLocationPropertyFromId ( $objUtil->checkRequestKey ( 'locationid' ), 'region' ) ) . "\" />";
// 	echo "<span class=\"help-block\">" . LangAddSiteField2Expl . "</span>";
// 	echo "</div>";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField3 . "</label>";
// 	echo "<div class=\"form-inline\">";
// 	echo $tempCountryList;
// 	echo "</div></div>";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField4 . "</label>";
// 	echo "<div class=\"form-inline\">";
// 	echo "<input type=\"number\" min=\"-90\" max=\"90\" required class=\"form-control\" maxlength=\"3\" name=\"latitude\" size=\"4\" value=\"" . $latitudedeg . "\" />&deg;&nbsp;" .  
// 		 "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"latitudemin\" size=\"4\"	value=\"" . abs ( $latitudemin ) . "\" />'" . 
// 	     "<input type=\"number\" min=\"0.0\" max=\"59.999\" required class=\"form-control\" maxlength=\"5\" name=\"latitudesec\" size=\"5\"	value=\"" . abs ( $latitudesec ) . "\" />''";
// 	echo "</div>";
// 	echo "<span class=\"help-block\">" . LangAddSiteField4Expl . "</span>";
// 	echo "</div>";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField5 . "</label>";
// 	echo "<div class=\"form-inline\">";
// 	echo "<input type=\"number\" min=\"-180\" max=\"180\" required class=\"form-control\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"" . $longitudedeg . "\" />&deg;&nbsp;" . 
// 	     "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\"	name=\"longitudemin\" size=\"4\" value=\"" . abs ( $longitudemin ) . "\" />'" . 
// 	     "<input type=\"number\" min=\"0.0\" max=\"59.999\" required class=\"form-control\" maxlength=\"5\"	name=\"longitudesec\" size=\"5\" value=\"" . abs ( $longitudesec ) . "\" />''";
// 	echo "</div>";
// 	echo "<span class=\"help-block\">" . LangAddSiteField5Expl . "</span>";
// 	echo "</div>";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField7 . "</label>";
// 	echo "<div class=\"form-inline\">";
// 	echo "<input type=\"number\" min=\"0\" max=\"9.9\" step=\"0.1\" class=\"form-control\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"" . (($objLocation->getLocationPropertyFromId ( $objUtil->checkRequestKey ( 'locationid' ), 'limitingMagnitude' ) > - 900) ? $objLocation->getLocationPropertyFromId ( $objUtil->checkRequestKey ( 'locationid' ), 'limitingMagnitude' ) : "") . "\" />";
// 	echo "</div>";
// 	echo "<span class=\"help-block\">" . LangAddSiteField7Expl . "</span>";
// 	echo "</div>";
	
// 	echo "<div class=\"form-group\">
// 	       <label>" . LangAddSiteField8 . "</label>";
// 	echo "<div class=\"form-inline\">";
// 	echo "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"" . (($objLocation->getLocationPropertyFromId ( $objUtil->checkRequestKey ( 'locationid' ), 'skyBackground' ) > - 900) ? $objLocation->getLocationPropertyFromId ( $objUtil->checkRequestKey ( 'locationid' ), 'skyBackground' ) : "") . "\" />";
// 	echo "</div>";
// 	echo "<span class=\"help-block\">" . LangAddSiteField8Expl . "</span>";
// 	echo "</div>";
	
//	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
