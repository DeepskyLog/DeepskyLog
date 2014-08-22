<?php
// change_eyepiece.php
// allows the eyepiece owner or an admin to an eyepiece
// or another user to view the eyepiece details
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($eyepieceid = $objUtil->checkGetKey ( 'eyepiece' )))
	throw new Exception ( LangException003 );
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
	$content = ($disabled ? "" : "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"change\" value=\"" . LangAddEyepieceButton2 . "\" />&nbsp;");
	echo "<h4>" . stripslashes ( $eyepiece ['name'] ) . "</h4>";
	echo "<hr />";
	echo $content;
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddEyepieceField1 . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"" . stripslashes ( $eyepiece ['name'] ) . "\" " . $disabled . "/>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField1Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddEyepieceField2 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"100\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"" . stripslashes ( $eyepiece ['focalLength'] ) . "\" " . $disabled . "/>";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField2Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddEyepieceField4 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"100\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"" . ((($mfl = stripslashes ( $eyepiece ['maxFocalLength'] )) < 0) ? "" : $mfl) . "\" " . $disabled . "/>";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField4Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
 	       <label for=\"filtername\">" . LangAddEyepieceField3 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"1\" max=\"180\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"" . $eyepiece ['apparentFOV'] . "\" " . $disabled . "/>";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField3Expl . "</span>";
	echo "</div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
