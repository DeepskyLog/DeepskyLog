<?php
// new_instrument.php
// allows the user to add a new instrument
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	new_instrument ();
function new_instrument() {
	global $baseURL, $loggedUserName, $objInstrument, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewInstrumentsTitle . " " . $loggedUserName . "</h4>"; 
	echo "<hr />";
	$objInstrument->showInstrumentsObserver ();
	$insts = $objInstrument->getSortedInstruments ( 'name', "", true );
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_instrument\" />";
	$content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
	$content1b .= "<option selected=\"selected\" value=\"" . $baseURL . "index.php?indexAction=add_instrument\"> &nbsp; </option>";
	while ( list ( $key, $value ) = each ( $insts ) )
		$content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_instrument&amp;instrumentid=" . urlencode ( $value ) . "\" " . (($value == $objUtil->checkRequestKey ( 'instrumentid' )) ? " selected=\"selected\" " : '') . ">" . $objInstrument->getInstrumentPropertyFromId ( $value, 'name' ) . "</option>";
	$content1b .= "</select>";
	
	echo "<h4>" . LangAddInstrumentTitle . "</h4>";
	echo "<hr />";
	echo "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"add\" value=\"" . LangAddInstrumentAdd . "\" />&nbsp;";
	
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddInstrumentExisting . "</label>";
	echo "<div class=\"form-inline\">";
	echo $content1b;
	echo "</div></div>";
	
	echo LangAddSiteFieldOr . " " . LangAddInstrumentManually; 
	echo "<br /><br />";
	
	$type = $objUtil->checkRequestKey ( 'type' );
	if ($instrumentid = $objUtil->checkRequestKey ( 'instrumentid', 0 ))
		$type = $objInstrument->getInstrumentPropertyFromId ( $instrumentid, 'type' );
	
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddInstrumentField1 . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"instrumentname\" size=\"30\"  value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'instrumentname' ) ) . stripslashes ( $objInstrument->getInstrumentPropertyFromId ( $objUtil->checkRequestKey ( 'instrumentid' ), 'name' ) ) . "\" />";
	echo "</div>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddInstrumentField2 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0.01\" step=\"0.01\" required class=\"form-control\" maxlength=\"64\" name=\"diameter\" size=\"10\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'diameter' ) ) . stripslashes ( $objInstrument->getInstrumentPropertyFromId ( $objUtil->checkRequestKey ( 'instrumentid' ), 'diameter' ) ) . "\" />" . "<select name=\"diameterunits\" class=\"form-control\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>";
	echo "</div>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddInstrumentField5 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $objInstrument->getInstrumentEchoListType ( $type );
	echo "</div>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddInstrumentField4 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0.01\" step=\"0.01\" class=\"form-control\" maxlength=\"64\" name=\"focallength\" size=\"10\"  value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'focallength' ) ) . stripslashes ( $objInstrument->getInstrumentPropertyFromId ( $objUtil->checkRequestKey ( 'instrumentid' ), 'diameter' ) * $objInstrument->getInstrumentPropertyFromId ( $objUtil->checkRequestKey ( 'instrumentid' ), 'fd' ) ) . "\" />" . "<select class=\"form-control\" name=\"focallengthunits\"> <option>inch</option> <option selected=\"selected\">mm</option> </select>" . "&nbsp;<span>" . LangAddInstrumentOr . "&nbsp;" . LangAddInstrumentField3 . "</span>&nbsp;" . "<input type=\"number\" min=\"0.01\" step=\"0.01\" class=\"form-control\" maxlength=\"64\" name=\"fd\" size=\"10\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'fd' ) ) . stripslashes ( $objInstrument->getInstrumentPropertyFromId ( $objUtil->checkRequestKey ( 'instrumentid' ), 'fd' ) ) . "\" />";
	echo "</div>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddInstrumentField6 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0.0\" step=\"0.1\" class=\"form-control\" maxlength=\"5\" name=\"fixedMagnification\" size=\"5\" value=\"" . ($objUtil->checkRequestKey ( 'fixedMagnification' )) . stripslashes ( $objInstrument->getInstrumentPropertyFromId ( $objUtil->checkRequestKey ( 'instrumentid' ), 'fixedMagnification' ) ) . "\" />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddInstrumentField6Expl . "</span>";
	echo "</div>";

	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
