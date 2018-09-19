<?php
// new_filter.php
// allows the user to add a new filter
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception(_("You need to be logged in to change your locations or equipment."));
else
	new_filter ();
function new_filter() {
	global $baseURL, $loggedUserName, $objFilter, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . _("Add new filter") . "</h4>";
	$filts = $objFilter->getSortedFilters ( 'name', "" );
	echo "<form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
	$content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
	while ( list ( $key, $value ) = each ( $filts ) )
		$content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_filter&amp;filterid=" . urlencode ( $value ) . "\" " . (($value == $objUtil->checkRequestKey ( 'filterid' )) ? " selected=\"selected\" " : '') . ">" . $objFilter->getFilterPropertyFromId ( $value, 'name' ) . "</option>";
	$content1b .= "</select>";

	echo "<hr />";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"add\" value=\"" . _("Add filter") . "\" />&nbsp;";
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . _("Select an existing filter") . "</label>";
	echo "<div class=\"form-inline\">";
	echo $content1b;
	echo "</div></div>";

	echo "<hr />";
	echo _("or specify your filter details manually");
	echo "<br /><br />";

	echo "<div class=\"form-group\">
	       <label for=\"filtername\">" . _("Name") . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'filtername', '' ) ) . stripslashes ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'name' ) ) . "\" />";
	echo "<span class=\"help-block\">" . _("(e.g. Lumicon O-III)") . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label for=\"type\">" . _("Type") . "</label>";
	echo "<div class=\"form-inline\">";
	echo $objFilter->getEchoListType ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'type' ) );
	echo "</div></div>";

	echo "<div class=\"form-group\">
	       <label for=\"color\">" . _("Color") . "</label>";
	echo "<div class=\"form-inline\">";
	echo $objFilter->getEchoListColor ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'color' ) );
	echo "</div></div>";

	echo "<div class=\"form-group\">
	       <label for=\"wratten\">" . _("Wratten number") . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'wratten' ) ) . stripslashes ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'wratten' ) ) . "\" />";
	echo "</div></div>";

	echo "<div class=\"form-group\">
	       <label for=\"schott\">" . _("Schott number") . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'schott' ) ) . stripslashes ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'schott' ) ) . "\" />";
	echo "</div></div>";

	echo "<input type=\"submit\" class=\"btn btn-success\" name=\"add\" value=\"" . _("Add filter") . "\" />&nbsp;";

	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
