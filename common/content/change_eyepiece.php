<?php
// change_eyepiece.php
// allows the eyepiece owner or an admin to an eyepiece
// or another user to view the eyepiece details
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($eyepieceid = $objUtil->checkGetKey ( 'eyepiece' )))
	throw new Exception(_("You wanted to change an eyepiece, but none is specified. Please contact the developers with this message."));
elseif (! ($objEyepiece->getEyepiecePropertyFromId ( $eyepieceid, 'name' )))
	throw new Exception ( "Eyepiece not found in change_eyepiece.php, please contact the developers with this message:" . $eyepieceid );
else
	change_eyepiece ();
function change_eyepiece() {
	global $baseURL, $loggedUser, $eyepieceid, $objEyepiece, $objPresentations, $objUtil;
	$disabled = " disabled=\"disabled\"";
	if (($loggedUser) && ($objUtil->checkAdminOrUserID ( $objEyepiece->getEyepiecePropertyFromId ( $eyepieceid, 'observer', '' ) )))
		$disabled = "";
	$eyepiece = $objEyepiece->getEyepiecePropertiesFromId ( $eyepieceid );
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"" . $eyepieceid . "\" />";
	$content = ($disabled ? "" : "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"change\" value=\"" . _("Adapt eyepiece") . "\" />&nbsp;");
	echo "<h4>" . stripslashes ( $eyepiece ['name'] ) . "</h4>";
	echo "<hr />";
	echo $content;
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . _("Name") . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"" . stripslashes ( $eyepiece ['name'] ) . "\" " . $disabled . "/>";
	echo "<span class=\"help-block\">" . _("(e.g. Televue 31mm Nagler)") . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . _("Focal length (mm)") . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"120\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"" . stripslashes ( $eyepiece ['focalLength'] ) . "\" " . $disabled . "/>";
	echo "</div>";
	echo "<span class=\"help-block\">" . _("e.g. 31") . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . _("Maximum focal length (in mm)") . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"100\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"" . ((($mfl = stripslashes ( $eyepiece ['maxFocalLength'] )) < 0) ? "" : $mfl) . "\" " . $disabled . "/>";
	echo "</div>";
	echo "<span class=\"help-block\">" . _("only needed for zoom eyepieces") . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . _("Apparent FOV (in °)") . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"1\" max=\"180\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"" . $eyepiece ['apparentFOV'] . "\" " . $disabled . "/>";
	echo "</div>";
	echo "<span class=\"help-block\">" . _("e.g. 82") . "</span>";
	echo "</div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
