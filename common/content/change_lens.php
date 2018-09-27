<?php
// change_lens.php
// allows the lens owner or an administrator to change a lens
// or another user to view the lens details
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($lensid = $objUtil->checkGetKey ( 'lens' )))
	throw new Exception(_("You wanted to change a lens, but none is specified. Please contact the developers with this message."));
elseif (! ($objLens->getLensPropertyFromId ( $lensid, 'name' )))
	throw new Exception ( "Lens not found in change_lens.php, please contact the developers with this message:" . $eyepieceid );
else
	change_lens ();
function change_lens() {
	global $baseURL, $lensid, $loggedUser, $objLens, $objPresentations, $objUtil;
	$disabled = " disabled=\"disabled\"";
	if (($loggedUser) && ($objUtil->checkAdminOrUserID ( $objLens->getLensPropertyFromId ( $lensid, 'observer', '' ) )))
		$disabled = "";
	$content = ($disabled ? "" : "<input type=\"submit\" name=\"change\" class=\"btn btn-primary pull-right\" value=\"" . _("Change lens") . "\" />&nbsp;");
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
	echo "<input type=\"hidden\" name=\"id\"          value=\"" . $lensid . "\" />";
	echo "<h4>" . stripslashes ( $objLens->getLensPropertyFromId ( $lensid, 'name' ) ) . "</h4>";
	echo "<hr />";
	echo $content;
	
	echo "<div class=\"form-group\">
	       <label for=\"lensname\">" . _("Name") . "</label>";
	echo "<input type=\"text\" required class=\"inputfield form-control\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"" . stripslashes ( $objLens->getLensPropertyFromId ( $lensid, 'name' ) ) . "\" " . $disabled . " />";
	echo "<span class=\"help-block\">" . _("e.g. Televue 2x Barlow") . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"factor\">" . _("Factor") . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0.01\" max=\"99.99\" step=\"0.01\" required class=\"inputfield form-control\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"" . stripslashes ( $objLens->getLensPropertyFromId ( $lensid, 'factor' ) ) . "\" " . $disabled . " />";
	echo "</div>";
	echo "<span class=\"help-block\">" . _("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.") . "</span>";
	echo "</div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>