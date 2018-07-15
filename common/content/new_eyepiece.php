<?php
// new_eyepiece.php
// allows the user to add a new eyepiece
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	new_eyepiece ();
function new_eyepiece() {
	global $baseURL, $loggedUserName, $objEyepiece, $objPresentations, $objUtil;
	$mfl = $objUtil->checkRequestKey ( 'maxFocalLength', - 1 );
	if ($eyepieceid = $objUtil->checkRequestKey ( 'eyepieceid' ))
		$mfl = stripslashes ( $objEyepiece->getEyepiecePropertyFromId ( $eyepieceid, 'maxFocalLength' ) );
	if ($mfl < 0)
		$mfl = '';
	$eyeps = $objEyepiece->getSortedEyepieces ( 'focalLength' );
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\" />";
	$content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
	while ( list ( $key, $value ) = each ( $eyeps ) )
		$content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_eyepiece&amp;eyepieceid=" . urlencode ( $value ) . "\" " . (($value == $objUtil->checkRequestKey ( 'eyepieceid' )) ? " selected=\"selected\" " : '') . ">" . trim ( $objEyepiece->getEyepiecePropertyFromId ( $value, 'name' ) ) . "</option>";
	$content1b .= "</select>&nbsp;";
	echo "<h4>" . LangAddEyepieceTitle . "</h4>";
	echo "<hr />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"".LangAddEyepieceButton."\" />&nbsp;";

	echo "<div class=\"form-group\">
	       <label for=\"catalog\">". _("Select an existing eyepiece")."</label>";
	echo "<div class=\"form-inline\">";
	echo $content1b;
	echo "</div></div>";

	echo "<hr />";
	echo LangAddSiteFieldOr." ".LangAddEyepieceManually;
	echo "<br /><br />";

	echo "<div class=\"form-group\">
	       <label for=\"catalog\">". LangAddEyepieceField1."</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'eyepiecename' ) ) . stripslashes ( $objEyepiece->getEyepiecePropertyFromId ( $objUtil->checkRequestKey ( 'eyepieceid' ), 'name' ) ) . "\" />";
	echo "<span class=\"help-block\">" . LangAddEyepieceField1Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label for=\"catalog\">". LangAddEyepieceField2."</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"100\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"" . stripslashes ( $objEyepiece->getEyepiecePropertyFromId ( $objUtil->checkRequestKey ( 'eyepieceid' ), 'focalLength', $objUtil->checkRequestKey ( 'focalLength' ) ) ) . "\" />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField2Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label for=\"catalog\">". LangAddEyepieceField4."</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"100\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"" . $mfl . "\" />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField4Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label for=\"catalog\">". LangAddEyepieceField3."</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"1\" max=\"180\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"" . stripslashes ( $objEyepiece->getEyepiecePropertyFromId ( $objUtil->checkRequestKey ( 'eyepieceid' ), 'apparentFOV', $objUtil->checkRequestKey ( 'apparentFOV' ) ) ) . "\" />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddEyepieceField3Expl . "</span>";
	echo "</div>";
	echo "<input type=\"submit\" class=\"btn btn-success\" name=\"add\" value=\"".LangAddEyepieceButton."\" />&nbsp;";

	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
