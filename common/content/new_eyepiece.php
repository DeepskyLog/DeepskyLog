<?php

// new_eyepiece.php
// allows the user to add a new eyepiece
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!$loggedUser) {
    throw new Exception(_("You need to be logged in to change your locations or equipment."));
} else {
    new_eyepiece();
}
function new_eyepiece()
{
    global $baseURL, $loggedUserName, $objEyepiece, $objPresentations, $objUtil;
    $mfl = $objUtil->checkRequestKey('maxFocalLength', -1);
    if ($eyepieceid = $objUtil->checkRequestKey('eyepieceid')) {
        $mfl = stripslashes($objEyepiece->getEyepiecePropertyFromId($eyepieceid, 'maxFocalLength'));
    }
    if ($mfl < 0) {
        $mfl = '';
    }
    $eyeps = $objEyepiece->getSortedEyepieces('MAX(focalLength)');
    echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\" />";
    $content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
    foreach ($eyeps as $key => $value) {
        $content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_eyepiece&amp;eyepieceid=" . urlencode($value) . "\" " . (($value == $objUtil->checkRequestKey('eyepieceid')) ? " selected=\"selected\" " : '') . ">" . trim($objEyepiece->getEyepiecePropertyFromId($value, 'name')) . "</option>";
    }
    $content1b .= "</select>&nbsp;";
    echo "<h4>" . _("Add a new eyepiece") . "</h4>";
    echo "<hr />";
    echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\""._("Add eyepiece")."\" />&nbsp;";

    echo "<div class=\"form-group\">
	       <label for=\"catalog\">". _("Select an existing eyepiece")."</label>";
    echo "<div class=\"form-inline\">";
    echo $content1b;
    echo "</div></div>";

    echo "<hr />";
    echo _("or specify your eyepiece details manually");
    echo "<br /><br />";

    echo "<div class=\"form-group\">
	       <label for=\"catalog\">". _("Name")."</label>";
    echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"" . stripslashes($objUtil->checkRequestKey('eyepiecename')) . stripslashes($objEyepiece->getEyepiecePropertyFromId($objUtil->checkRequestKey('eyepieceid'), 'name')) . "\" />";
    echo "<span class=\"help-block\">" . _("(e.g. Televue 31mm Nagler)") . "</span>";
    echo "</div>";

    echo "<div class=\"form-group\">
	       <label for=\"catalog\">". _("Focal length (mm)")."</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0\" max=\"120\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"" . stripslashes($objEyepiece->getEyepiecePropertyFromId($objUtil->checkRequestKey('eyepieceid'), 'focalLength', $objUtil->checkRequestKey('focalLength'))) . "\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" . _("e.g. 31") . "</span>";
    echo "</div>";

    echo "<div class=\"form-group\">
	       <label for=\"catalog\">". _("Maximum focal length (in mm)")."</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0\" max=\"100\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"" . $mfl . "\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" . _("only needed for zoom eyepieces") . "</span>";
    echo "</div>";

    echo "<div class=\"form-group\">
	       <label for=\"catalog\">". _("Apparent FOV (in °)")."</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"1\" max=\"180\" step=\"0.01\" required class=\"form-control\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"" . stripslashes($objEyepiece->getEyepiecePropertyFromId($objUtil->checkRequestKey('eyepieceid'), 'apparentFOV', $objUtil->checkRequestKey('apparentFOV'))) . "\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" . _("e.g. 82") . "</span>";
    echo "</div>";
    echo "<input type=\"submit\" class=\"btn btn-success\" name=\"add\" value=\""._("Add eyepiece")."\" />&nbsp;";

    echo "<hr />";
    echo "</div></form>";
    echo "</div>";
}
