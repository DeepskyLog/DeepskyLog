<?php
// new_filter.php
// allows the user to add a new filter
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
else
	new_filter ();
function new_filter() {
	global $baseURL, $loggedUserName, $objFilter, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewFilterTitle . " " . $loggedUserName . "</h4>"; 
	echo "<hr />";
	$objFilter->showFiltersObserver ();
	echo "<h4>" . LangAddFilterTitle . "</h4>";
	$filts = $objFilter->getSortedFilters ( 'name', "" );
	echo "<form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\" />";
	$content1b = "<select class=\"form-control\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
	while ( list ( $key, $value ) = each ( $filts ) )
		$content1b .= "<option value=\"" . $baseURL . "index.php?indexAction=add_filter&amp;filterid=" . urlencode ( $value ) . "\" " . (($value == $objUtil->checkRequestKey ( 'filterid' )) ? " selected=\"selected\" " : '') . ">" . $objFilter->getFilterPropertyFromId ( $value, 'name' ) . "</option>";
	$content1b .= "</select>";
	
	echo "<hr />";
	echo "<input type=\"submit\" class=\"btn btn-primary pull-right\" name=\"add\" value=\"" . LangAddFilterButton . "\" />&nbsp;";
	echo "<div class=\"form-group\">
	       <label for=\"catalog\">" . LangAddFilterExisting . "</label>";
	echo "<div class=\"form-inline\">";
	echo $content1b;
	echo "</div></div>";
	
	echo "<hr />";
	echo LangAddSiteFieldOr . " " . LangAddLensFieldManually;
	echo "<br /><br />";
	
	echo "<div class=\"form-group\">
	       <label for=\"filtername\">" . LangAddFilterField1 . "</label>";
	echo "<input type=\"text\" required class=\"form-control\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'filtername', '' ) ) . stripslashes ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'name' ) ) . "\" />";
	echo "<span class=\"help-block\">" . LangAddFilterField1Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"type\">" . LangAddFilterField2 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $objFilter->getEchoListType ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'type' ) );
	echo "</div></div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"color\">" . LangAddFilterField3 . "</label>";
	echo "<div class=\"form-inline\">";
	echo $objFilter->getEchoListColor ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'color' ) );
	echo "</div></div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"wratten\">" . LangAddFilterField4 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'wratten' ) ) . stripslashes ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'wratten' ) ) . "\" />";
	echo "</div></div>";
	
	echo "<div class=\"form-group\">
	       <label for=\"schott\">" . LangAddFilterField5 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"text\" class=\"inputfield form-control\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"" . stripslashes ( $objUtil->checkRequestKey ( 'schott' ) ) . stripslashes ( $objFilter->getFilterPropertyFromId ( $objUtil->checkRequestKey ( 'filterid' ), 'schott' ) ) . "\" />";
	echo "</div></div>";
	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
