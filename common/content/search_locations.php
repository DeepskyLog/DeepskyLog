<?php
// search_locations.php
// allows the user to search a location in the database
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	search_locations ();
function search_locations() {
	global $baseURL, $objLocation, $objPresentations;
	echo "<div id=\"main\">";
	echo "<form action=\"" . $baseURL . "index.php?indexAction=site_result\" method=\"post\"><div>";
	echo "<h4>" . LangSearchLocations0 . "</h4>";
    echo "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"search\" value=\"" . LangSearchLocations7 . "\" />&nbsp;"; 
	echo "<hr />";
	echo "<strong>" . LangSearchLocations1 . "</strong><br />"; 
	$countries = $objLocation->getDatabaseCountries ();
	$content = "<select class=\"form-control\" name=\"country\">";
	while ( list ( $key, $value ) = each ( $countries ) )
		$content .= "<option>" . $value . "</option>";
	$content .= "</select>";
    echo $content;
    echo LangSearchLocations3; 
	echo "<br /><br />";
	
	echo "<strong>" . LangSearchLocations4 . "</strong>"; 

	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"location_name\" size=\"30\" value=\"\" />";
	echo LangSearchLocations6; 
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
