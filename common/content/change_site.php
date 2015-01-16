<?php
// change_site.php
// allows a site owner or an the administrator to change site details
// or another user to view the site details
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($locationid = $objUtil->checkGetKey ( 'location' )))
	throw new Exception ( LangException011b );
elseif (! ($objLocation->getLocationPropertyFromId ( $locationid, 'name' )))
	throw new Exception ( "Location not found in change_instrument.php, please contact the developers with this message:" . $eyepieceid );
else
	change_site ();
function change_site() {
	global $baseURL, $locationid, $loggedUser, $objLocation, $objPresentations, $objUtil;
	$disabled = " disabled=\"disabled\"";
	if (($loggedUser) && ($objUtil->checkAdminOrUserID ( $objLocation->getLocationPropertyFromId ( $locationid, 'observer', '' ) )))
		$disabled = "";
	$content = ($disabled ? "" : "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"change\" value=\"" . LangAddSiteButton2 . "\" />&nbsp;");
	$latitudestr = $objLocation->getLocationPropertyFromId ( $locationid, 'latitude' );
	$latitudedeg = ( int ) ($latitudestr);
	$latitudemin = round ( (( float ) ($latitudestr) - ( int ) ($latitudestr)) * 60 );
	$longitudestr = $objLocation->getLocationPropertyFromId ( $locationid, 'longitude' );
	$longitudedeg = ( int ) ($longitudestr);
	$longitudemin = round ( (( float ) ($longitudestr) - ( int ) ($longitudestr)) * 60 );
	$lm = $objLocation->getLocationPropertyFromId ( $locationid, 'limitingMagnitude' );
	$sb = $objLocation->getLocationPropertyFromId ( $locationid, 'skyBackground' );
	
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
	echo "<input type=\"hidden\" name=\"id\" value=\"" . $locationid . "\" />&nbsp;";
	echo "<h4>" . stripslashes ( $objLocation->getLocationPropertyFromId ( $locationid, 'name' ) ) . "</h4>";
	echo "<hr />";
	echo $content;
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddSiteField1 . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"sitename\" size=\"30\" value=\"" . stripslashes ( $objLocation->getLocationPropertyFromId ( $locationid, 'name' ) ) . "\"  " . $disabled . " />";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddSiteField2 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"region\" size=\"30\" value=\"" . stripslashes ( $objLocation->getLocationPropertyFromId ( $locationid, 'region' ) ) . "\"  " . $disabled . " />";
	echo "<span class=\"help-block\">" . LangAddSiteField2Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddSiteField3 . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"country\" size=\"30\" value=\"" . $objLocation->getLocationPropertyFromId ( $locationid, 'country' ) . "\"  " . $disabled . " />";
	echo "<span class=\"help-block\">" . LangAddSiteField3Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label class=\"control-label\" for=\"filtername\">" . LangAddSiteField4 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"-90\" max=\"90\" required class=\"form-control\" maxlength=\"3\" name=\"latitude\" size=\"4\" value=\"" . $latitudedeg . "\" " . $disabled . " />&deg;<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"latitudemin\" size=\"3\" value=\"" . abs ( $latitudemin ) . "\"  " . $disabled . " />'";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSiteField4Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddSiteField5 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"-180\" max=\"180\" required class=\"form-control\" maxlength=\"4\" name=\"longitude\" size=\"4\" value=\"" . $longitudedeg . "\" " . $disabled . " />&deg;<input type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" name=\"longitudemin\" size=\"3\" value=\"" . abs ( $longitudemin ) . "\"  " . $disabled . " />'";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSiteField5Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddSiteField7 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0.0\" max=\"9.9\" step=\"0.1\" class=\"form-control\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"" . (($lm > - 900) ? $lm : "") . "\"  " . $disabled . " />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSiteField7Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddSiteField8 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"" . (($sb > - 900) ? $sb : "") . "\"  " . $disabled . " />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSiteField8Expl . "</span>";
	echo "</div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
