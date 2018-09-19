<?php
// new_object.php
// allows the user to add an object to the database
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception(_("You need to be logged in to change your locations or equipment."));
else
	new_object ();
function new_object() {
	global $baseURL, $entryMessage, $DSOcatalogs, $FF, $objConstellation, $objObject, $objPresentations, $objUtil;
	$phase = $objUtil->checkRequestKey ( 'phase', 0 );
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	$link = $baseURL . "index.php?indexAction=add_object";
	$content = "";
	$content2 = "";
	$content3 = "";
	$content4 = "";
	$content5 = "";
	if ($phase == 2) {
		$content = "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"newobject\" value=\"" . _("Add object") . "\" />&nbsp;";
		echo "<input type=\"hidden\" name=\"indexAction\" id=\"indexAction\" value=\"validate_object\" />";
		$entryMessage .= _("Now continue entering the object details.".
            "When all is ready, confirm using the button above, named 'Add object'.");
	} elseif ($phase == 1) {
		$content = "<a href=\"" . $baseURL . "index.php?indexAction=defaultAction\">" . "<input class=\"btn btn-danger pull-right\" type=\"button\" name=\"cancelnewobject\" value=\"" . _("Cancel adding") . "\" />&nbsp;" . "</a>";
		$content4 = "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"phase20\" id=\"phase20\" value=\"" . _("Check the coordinates") . "\" />";
		$content3 = "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"phase2\" id=\"phase2\" value=\"" . _("Confirm") . "\" />";
		echo "<input type=\"hidden\" name=\"phase\" id=\"phase\" value=\"1\" />";
		echo "<input type=\"hidden\" name=\"indexAction\" id=\"indexAction\" value=\"add_object\" />";
		if ($objUtil->checkRequestKey(('phase20'))) {
			$entryMessage .= _("Now you have to check if the name doesn't already exist in the list below, based on its coordinates. Take your time to carefully check the entries. ".
            "If the object is in the list, you can leave this section by e.g. clicking the object to verify it's the right one. ".
            "If the object is not in the list, you can continue entering it by clicking the 'Confirm' button. Attention: it's possible you have to scroll to see the whole list.");
        } else {
			$entryMessage .= _("Now you give the coordinates of the object: right ascension and declination. ".
                "Our coordinates are in the epoch 2000 reference system (J2000).");
        }
		$link .= "&amp;phase=1&amp;phase20=phase20&amp;catalog=" . urlencode ( $objUtil->checkRequestKey ( 'catalog' ) ) . "&amp;number=" . urlencode ( $objUtil->checkRequestKey ( 'number' ) );
		$link .= "&amp;RAhours=" . urlencode ( $objUtil->checkRequestKey ( 'RAhours' ) );
		$link .= "&amp;RAminutes=" . urlencode ( $objUtil->checkRequestKey ( 'RAminutes' ) );
		$link .= "&amp;RAseconds=" . urlencode ( $objUtil->checkRequestKey ( 'RAseconds' ) );
		$link .= "&amp;DeclDegrees=" . urlencode ( $objUtil->checkRequestKey ( 'DeclDegrees' ) );
		$link .= "&amp;DeclMinutes=" . urlencode ( $objUtil->checkRequestKey ( 'DeclMinutes' ) );
		$link .= "&amp;DeclSeconds=" . urlencode ( $objUtil->checkRequestKey ( 'DeclSeconds' ) );
	} else {
		$content = "<a href=\"" . $baseURL . "index.php?indexAction=defaultAction\">" . "<input class=\"btn btn-danger pull-right\" type=\"button\" name=\"cancelnewobject\" value=\"" . _("Cancel adding") . "\" />&nbsp;" . "</a>";
		$content2 = "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"phase10\" id=\"phase10\" value=\"" . _("Check the name") . "\" />";
		$content3 = "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"phase1\" id=\"phase1\" value=\"" . _("Confirm") . "\" />";
		echo "<input type=\"hidden\" name=\"phase\" id=\"phase\" value=\"0\" />";
		echo "<input type=\"hidden\" name=\"indexAction\" id=\"indexAction\" value=\"add_object\" />";
		if ($objUtil->checkRequestKey ( ('phase10') )) {
			$entryMessage .= _("Now you have to check if the name doesn't already exist in the list below, based on its name. Take your time to carefully check the entries. ".
                "If the object is in the list, you can leave this section by e.g. clicking the object to verify it's the right one. ".
                "If the object is not in the list, you can continue entering it by clicking the 'Confirm' button. Attention: it's possible you have to scroll to see the whole list.");
        } else {
			$entryMessage .= _("Only enter new objects that are not in the system yet. First, you have to enter the name of the object. ".
                "In the first box, you enter the catalog name, in the second box, you enter the number of the object within the catalog (e.g. M in the first box, 1 in the second box). ");
        }
		$link .= "&amp;phase=0&amp;phase10=phase10&amp;catalog=" . urlencode ( $objUtil->checkRequestKey ( 'catalog' ) ) . "&amp;number=" . urlencode ( $objUtil->checkRequestKey ( 'number' ) );
	}
	echo "<h4>" . _("Add new object") . "</h4>";
	echo "<hr />";
	echo $content;
	$disabled = " disabled=\"disabled\" ";
	
	// NAME
	if ($phase == 0)
		if ($_SESSION ['admin'] == "yes") {
			echo "<div class=\"form-group\">
	               <label>" . _("Name") . "</label>";
			echo "<div class=\"form-inline\">";
			echo "<input type=\"text\" required class=\"form-control\" maxlength=\"20\" name=\"catalog\" size=\"20\" value=\"" . $objUtil->checkRequestKey ( 'catalog' ) . "\" " . (($phase == 0) ? "" : $disabled) . "/>" . "&nbsp;&nbsp;" . "<input type=\"text\" required class=\"form-control\" maxlength=\"20\" name=\"number\" size=\"20\" value=\"" . $objUtil->checkRequestKey ( 'number' ) . "\" " . (($phase == 0) ? "" : $disabled) . "/>";
			echo $content2;
			echo "</div>";
			echo "</div>";
		} else {
			$tempcat = ("<select name=\"catalog\" class=\"form-control\"" . (($phase == 0) ? "" : $disabled) . ">");
			$tempcat .= ("<option value=\"\">-----</option>"); // empty field
			while ( list ( $key, $value ) = each ( $DSOcatalogs ) )
				$tempcat .= ("<option value=\"" . $value . "\" " . ($objUtil->checkRequestKey ( 'catalog' ) == $value ? "selected=\"selected\"" : "") . " >" . $value . "</option>");
			$tempcat .= ("</select>");
			
			echo "<div class=\"form-group\">
	               <label>" . _("Name") . "</label>";
			echo "<div class=\"form-inline\">";
			echo $tempcat . "&nbsp;&nbsp;" . "<input type=\"text\" required class=\"form-control\" maxlength=\"20\" name=\"number\" size=\"20\" value=\"" . $objUtil->checkRequestKey ( 'number' ) . "\" " . (($phase == 0) ? "" : $disabled) . "/>";
			echo $content2;
			echo "</div>";
			echo "</div>";
		}
	else {
		echo "<div class=\"form-group\">
	               <label>" . _("Name") . "</label>";
		echo "<div class=\"form-inline\">";
		echo "<input type=\"text\" required class=\"form-control\" maxlength=\"20\" name=\"catalog0\" size=\"20\" value=\"" . $objUtil->checkRequestKey ( 'catalog' ) . "\" " . $disabled . "/>" . "&nbsp;&nbsp;" . "<input type=\"text\" required class=\"form-control\" maxlength=\"20\" name=\"number0\" size=\"20\" value=\"" . $objUtil->checkRequestKey ( 'number' ) . "\" " . $disabled . "/>";
		echo "</div>";
		echo "</div>";
		
		echo "<input type=\"hidden\" name=\"catalog\" id=\"catalog\" value=\"" . $objUtil->checkRequestKey ( 'catalog' ) . "\" />";
		echo "<input type=\"hidden\" name=\"number\" id=\"number\" value=\"" . $objUtil->checkRequestKey ( 'number' ) . "\" />";
	}
	if (($phase == 10) || ($phase == 1) || ($phase == 20) || ($phase == 2)) { // RIGHT ASCENSION
	                                                                          // DECLINATION
		if ($phase == 1) {
			$content = "<input type=\"number\" min=\"0\" max=\"23\" required class=\"form-control\" maxlength=\"2\" name=\"RAhours\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAhours' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;h&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"RAminutes\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAminutes' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;m&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"RAseconds\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAseconds' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;s&nbsp;";
			
			echo "<div class=\"form-group\">
	               <label>" . _("RA") . "</label>";
			echo "<div class=\"form-inline\">";
			echo $content;
			echo "</div>";
			echo "</div>";
			
			$content = "<input type=\"number\" min=\"-89\" max=\"89\" required class=\"form-control\" maxlength=\"3\" name=\"DeclDegrees\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclDegrees' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;d&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"DeclMinutes\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclMinutes' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;m&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"DeclSeconds\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclSeconds' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;s&nbsp;";
			echo "<div class=\"form-group\">
	               <label>" . _("Declination") . "</label>";
			echo "<div class=\"form-inline\">";
			echo $content;
			echo $content4;
			echo "</div>";
			echo "</div>";
		} else {
			$content = "<input type=\"number\" min=\"0\" max=\"23\" required class=\"form-control\" maxlength=\"2\" name=\"RAhours1\"   size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAhours' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;h&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"RAminutes1\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAminutes' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;m&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"RAseconds1\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAseconds' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;s&nbsp;";
			echo "<div class=\"form-group\">
	               <label>" . _("RA") . "</label>";
			echo "<div class=\"form-inline\">";
			echo $content;
			echo "</div>";
			echo "</div>";
			$content = "<input type=\"number\" min=\"-89\" max=\"89\" required class=\"form-control\" maxlength=\"3\" name=\"DeclDegrees1\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclDegrees' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;d&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"DeclMinutes1\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclMinutes' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;m&nbsp;";
			$content .= "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" name=\"DeclSeconds1\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclSeconds' ) . "\" " . (($phase == 1) ? "" : $disabled) . "/>&nbsp;s&nbsp;";
			echo "<div class=\"form-group\">
	               <label>" . _("Declination") . "</label>";
			echo "<div class=\"form-inline\">";
			echo $content;
			echo $content4;
			echo "</div>";
			echo "</div>";
			echo "<input type=\"hidden\" name=\"RAhours\"     size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAhours' ) . "\"/>";
			echo "<input type=\"hidden\" name=\"RAminutes\"   size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAminutes' ) . "\"/>";
			echo "<input type=\"hidden\" name=\"RAseconds\"   size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'RAseconds' ) . "\"/>";
			echo "<input type=\"hidden\" name=\"DeclDegrees\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclDegrees' ) . "\"/>";
			echo "<input type=\"hidden\" name=\"DeclMinutes\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclMinutes' ) . "\"/>";
			echo "<input type=\"hidden\" name=\"DeclSeconds\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'DeclSeconds' ) . "\"/>";
		}
		if (($phase == 2) || ($phase == 20)) { // CONSTELLATION
			$thecon = '';
			$thecon = $objConstellation->getConstellationFromCoordinates ( $objUtil->checkRequestKey ( 'RAhours', 0 ) + ($objUtil->checkRequestKey ( 'RAminutes', 0 ) / 60) + ($objUtil->checkRequestKey ( 'RAhours', 0 ) / 3600), $objUtil->checkRequestKey ( 'DeclDegrees', 0 ) + ($objUtil->checkRequestKey ( 'DeclMinutes', 0 ) / 60) + ($objUtil->checkRequestKey ( 'DeclSeconds', 0 ) / 3600) );
			$content = "<input type=\"text\" required class=\"form-control\" maxlength=\"3\" disabled=\"disabled\" name=\"showcon\" size=\"3\" value=\"" . $thecon . "\" />";
			echo "<div class=\"form-group\">
	               <label>" . _("Constellation") . "</label>";
			echo "<div class=\"form-inline\">";
			echo $content;
			echo "<input type=\"hidden\" name=\"con\" value=\"" . $thecon . "\" />";
			echo "</div>";
			echo "</div>";
		}
	}
	if ($phase == 2) { // TYPE
		$content = "<select name=\"type\" required class=\"form-control\"" . (($phase == 2) ? "" : $disabled) . ">";
		$types = $objObject->getDsObjectTypes ();
		while ( list ( $key, $value ) = each ( $types ) )
			$stypes [$value] = $GLOBALS [$value];
		asort ( $stypes );
		while ( list ( $key, $value ) = each ( $stypes ) )
			$content .= "<option value=\"" . $key . "\"" . (($key == $objUtil->checkRequestKey ( 'type' )) ? " selected=\"selected\" " : "") . ">" . $value . "</option>";
		$content .= "</select>";
		echo "<div class=\"form-group\">
	               <label>" . _("Type") . "</label>";
		echo "<div class=\"form-inline\">";
		echo $content; 
		echo "</div>";
		echo "</div>";
		// MAGNITUDE
		$content = "<input type=\"number\" min=\"-5.5\" max=\"20.0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" name=\"magnitude\" size=\"4\" value=\"" . $objUtil->checkRequestKey ( 'magnitude' ) . "\" " . (($phase == 2) ? "" : $disabled) . "/>";
		echo "<div class=\"form-group\">
	               <label>" . _("Magnitude") . "</label>";
		echo "<div class=\"form-inline\">";
		echo $content; 
		echo "</div>";
		echo "</div>";
		// SURFACE BRIGHTNESS
		$content = "<input type=\"number\" min=\"-5.5\" max=\"20.0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" name=\"sb\" size=\"4\" value=\"" . $objUtil->checkRequestKey ( 'sb' ) . "\" " . (($phase == 2) ? "" : $disabled) . "/>";
		echo "<div class=\"form-group\">
	               <label>" . _("Surface brightness") . "</label>";
		echo "<div class=\"form-inline\">";
		echo $content; 
		echo "</div>";
		echo "</div>";
		// SIZE
		$content = "<input type=\"number\" class=\"form-control\" maxlength=\"4\" name=\"size_x\" size=\"4\" value=\"" . $objUtil->checkRequestKey ( 'size_x' ) . "\"" . (($phase == 2) ? "" : $disabled) . "/>&nbsp;&nbsp;";
		$content .= "<select class=\"form-control\" name=\"size_x_units\"" . (($phase == 2) ? "" : $disabled) . "> <option value=\"min\"" . (("min" == $objUtil->checkRequestKey ( 'size_x_units' )) ? " selected=\"selected\" " : "") . ">" . _("arcminutes") . "</option>
					                               <option value=\"sec\"" . (("sec" == $objUtil->checkRequestKey ( 'size_x_units' )) ? " selected=\"selected\" " : "") . ">" . _("arcseconds") . "</option>";
		$content .= "</select>";
		$content .= "&nbsp;&nbsp;X&nbsp;&nbsp;";
		$content .= "<input type=\"number\" class=\"form-control\" maxlength=\"4\" name=\"size_y\" size=\"4\" value=\"" . $objUtil->checkRequestKey ( 'size_y' ) . "\"" . (($phase == 2) ? "" : $disabled) . "/>&nbsp;&nbsp;";
		$content .= "<select class=\"form-control\" name=\"size_y_units\"" . (($phase == 2) ? "" : $disabled) . "> <option value=\"min\"" . (("min" == $objUtil->checkRequestKey ( 'size_y_units' )) ? " selected=\"selected\" " : "") . ">" . _("arcminutes") . "</option>
					                               <option value=\"sec\"" . (("sec" == $objUtil->checkRequestKey ( 'size_y_units' )) ? " selected=\"selected\" " : "") . ">" . _("arcseconds") . "</option>";
		$content .= "</select>";
		echo "<div class=\"form-group\">
	               <label>" . _("Size") . "</label>";
		echo "<div class=\"form-inline\">";
		echo $content; 
		echo "</div>";
		echo "</div>";
		// POSITION ANGLE
		$content = "<input type=\"number\" min=\"-360\" max=\"360\" class=\"form-control\" maxlength=\"3\" name=\"posangle\" size=\"3\" value=\"" . $objUtil->checkRequestKey ( 'posangle' ) . "\" " . (($phase == 2) ? "" : $disabled) . "/>&deg;";
		echo "<div class=\"form-group\">
	               <label>" . _("Position angle") . "</label>";
		echo "<div class=\"form-inline\">";
		echo $content; 
		echo "</div>";
		echo "</div>";
	}
	echo "<hr />";
	if ($objUtil->checkRequestKey ( ('phase10') )) {
		echo "<h4>" . _("Possible candidates") . "</h4>";
		
		echo _("Please confirm that the object is not listed below");
		echo $content3;
		
		echo "<hr />";
		$objObject->showObjectsFields ( $link, 0, 100, "", 0, array (
				"showname",
				"objectra",
				"objectdecl",
				"objecttype",
				"objectconstellation",
				"objectmagnitude",
				"objectsurfacebrightness",
				"objectdiam" 
		) );
		echo "<hr />";
	}
	if ($objUtil->checkRequestKey ( ('phase20') )) {
		echo "<h4>" . _("Possible candidates") . "</h4>";
		
		echo _("Please confirm that the object is not listed below");
		echo $content3;
		echo "<hr />";
		$objObject->showObjectsFields ( $link, 0, 100, "", 0, array (
				"showname",
				"objectra",
				"objectdecl",
				"objecttype",
				"objectconstellation",
				"objectmagnitude",
				"objectsurfacebrightness",
				"objectdiam" 
		) );
		echo "<hr />";
	}
	echo "</div></form>";
	echo "</div>";
}
?>
