<?php

// new_lens.php
// allows the user to add a new lens
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!$loggedUser) {
    throw new Exception(_("You need to be logged in to change your locations or equipment."));
} else {
    new_lens();
}
function new_lens()
{
    global $baseURL, $loggedUserName, $objLens, $objPresentations, $objUtil;
    echo "<div id=\"main\">";
    echo "<h4>" . _("Add a new lens") . "</h4>";
    $lns = $objLens->getSortedLenses('name');
    echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
    $content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
    foreach ($lns as $key => $value) {
        $content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_lens&amp;lensid=" . urlencode($value) . "\" " . (($value == $objUtil->checkRequestKey('lensid')) ? " selected=\"selected\" " : '') . ">" . $objLens->getLensPropertyFromId($value, 'name') . "</option>";
    }
    $content1b .= "</select>&nbsp;";

    echo "<hr />";
    echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . _("Add lens") . "\" />&nbsp;";
    echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . _("Select an existing lens") . "</label>";
    echo "<div class=\"form-inline\">";
    echo $content1b;
    echo "</div></div>";

    echo _("or specify your lens details manually");
    echo "<br /><br />";

    echo "<div class=\"form-group\">
	       <label for=\"lensname\">" . _("Name") . "</label>";
    echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"" . stripslashes($objUtil->checkRequestKey('lensname', '')) . stripslashes($objLens->getLensPropertyFromId($objUtil->checkRequestKey('lensid'), 'name')) . "\" />";
    echo "<span class=\"help-block\">" . _("e.g. Televue 2x Barlow") . "</span>";
    echo "</div>";

    echo "<div class=\"form-group\">
	       <label for=\"factor\">" . _("Factor") . "</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0.01\" max=\"99.99\" required step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"" . stripslashes($objUtil->checkRequestKey('factor', '')) . stripslashes($objLens->getLensPropertyFromId($objUtil->checkRequestKey('lensid'), 'factor')) . "\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" . _("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.") . "</span>";
    echo "</div>";
    echo "<input type=\"submit\" class=\"btn btn-success\" name=\"add\" value=\"" . _("Add lens") . "\" />&nbsp;";

    echo "<hr />";
    echo "</div></form>";
    echo "</div>";
}
