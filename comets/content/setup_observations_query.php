<?php
// setup_observations_query.php
// interface to query observations
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	setup_observations_query ();
function setup_observations_query() {
	global $baseURL, $objUtil, $objPresentations, $objCometObject, $objObserver, $objCometObservation, $objInstrument, $objLocation;
	$_SESSION ['result'] = "";
	echo "<div id=\"main\">";
	echo "<form action=\"" . $baseURL . "index.php\" method=\"get\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_result_selected_observations\" />";
	$id = $objUtil->checkSessionKey ( 'observedobject', $objUtil->checkGetKey ( 'observedobject' ) );
	echo "<h4>" . _("Search observations") . "</h4>";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"query\" value=\"" . _("Search observations") . "\" />";
	echo "<br /><hr />";

	// OBJECT NAME
	$content1 = _("Object name");
	$content2 = "<select class=\"form-control\" name=\"object\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	$catalogs = $objCometObject->getSortedObjects ( "name" );
	while ( list ( $key, $value ) = each ( $catalogs ) )
		$content2 .= "<option value=\"" . $value [0] . "\"" . ((($id) && ($id == $objCometObject->getId ( $value[0] ))) ? " selected=\"selected\" " : "") . ">" . $value [0] . "</option>";
	$content2 .= "</select>";
	echo "<strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// OBSERVER
	$content1 = _("Observer");
	$content2 = "<select class=\"form-control\" name=\"observer\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	$obs = $objObserver->getSortedObservers ( 'name' );
	$obs = $objCometObservation->getPopularObservers ();
	while ( list ( $key, $value ) = each ( $obs ) )
		$sortobs [$value] = $objObserver->getObserverProperty ( $value, 'name' ) . " " . $objObserver->getObserverProperty ( $value, 'firstname' );
	natcasesort ( $sortobs );
	while ( list ( $value, $key ) = each ( $sortobs ) )
		$content2 .= "<option value=\"" . $value . "\">" . $key . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// INSTRUMENT
	$content1 = _("Instrument");
	$content2 = "<select class=\"form-control\" name=\"instrument\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	$inst = $objInstrument->getSortedInstrumentsList ( "name" );
	while ( list ( $key, $value ) = each ( $inst ) )
		$content2 .= "<option value=\"" . $key . "\">" . $value . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM DIAMETER
	$content1 = _("Minimum instrument diameter");
	$content2 = "<input type=\"number\" min=\"0\" step=\"0.1\" class=\"form-control\" maxlength=\"64\" name=\"mindiameter\" size=\"10\" />";
	$content2 .= "&nbsp;";
	$content2 .= "<select name=\"mindiameterunits\" class=\"form-control\"><option selected=\"selected\">&nbsp;</option><option>inch</option><option>mm</option></select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM DIAMETER
	$content1 = _("Maximum instrument diameter");
	$content2 = "<input type=\"number\" min=\"0\" step=\"0.1\" class=\"form-control\" maxlength=\"64\" name=\"maxdiameter\" size=\"10\" />";
	$content2 .= "&nbsp;";
	$content2 .= "<select name=\"maxdiameterunits\" class=\"form-control\"><option selected=\"selected\">&nbsp;</option><option>inch</option><option>mm</option></select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// SITE
	$content1 = _("Location");
	$content2 = "<select class=\"form-control\" name=\"site\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	$sites = $objLocation->getSortedLocations ( "name" );
	while ( list ( $key, $value ) = each ( $sites ) )
		if ($key)
			$content2 .= "<option value=\"" . $value . "\"" . ">" . $objLocation->getLocationPropertyFromId ( $value, 'name' ) . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM DATE
	$content1 = _("From");
	$content2 = "<input type=\"number\" min=\"1\" max=\"31\" class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"minday\" value=\"\" />";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<select class=\"form-control\" name=\"minmonth\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i < 13; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . constant("MONTH" . $i) . "</option>";
	$content2 .= "</select>";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<input type=\"number\" min=\"1609\" class=\"form-control\" maxlength=\"4\" size=\"5\" name=\"minyear\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM DATE
	$content1 = _("Till");
	$content2 = "<input type=\"number\" min=\"1\" max=\"31\" class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"maxday\" value=\"\" />";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<select class=\"form-control\" name=\"maxmonth\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i < 13; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . constant("MONTH" . $i) . "</option>";
	$content2 .= "</select>";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<input type=\"number\" min=\"1609\" class=\"form-control\" name=\"maxyear\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// DESCRIPTION
	$content1 = _("Description contains");
	$content2 = "<input type=\"text\" class=\"form-control\" maxlength=\"40\" name=\"description\" size=\"35\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM MAGNITUDE
	$content1 = _("Magnitude brighter than");
	$content2 = "<input type=\"number\" min=\"-5.0\" max=\"20.0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" name=\"maxmag\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM MAGNITUDE
	$content1 = _("Magnitude fainter than");
	$content2 = "<input type=\"number\" min=\"-5.0\" max=\"20.0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" name=\"minmag\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM DC
	$content1 = _("Minimum degree of condensation");
	$content2 = "<select class=\"form-control\" name=\"mindc\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i <= 9; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . $i . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM DC
	$content1 = _("Maximum degree of condensation");
	$content2 = "<select class=\"form-control\" name=\"maxdc\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i <= 9; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . $i . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM COMA
	$content1 = _("Minimum coma (arcminutes)");
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" name=\"mincoma\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM COMA
	$content1 = _("Maximum coma (arcminutes)");
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" name=\"maxcoma\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM TAIL
	$content1 = _("Minimum tail length (arcminutes)");
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\"  class=\"form-control\" maxlength=\"4\" name=\"mintail\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM TAIL
	$content1 = _("Maximum tail length (arcminutes)");
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\"  class=\"form-control\" maxlength=\"4\" name=\"maxtail\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";
	echo "</div>";
	echo "<br /><input type=\"submit\" class=\"btn btn-success\" name=\"query\" value=\"" . _("Search observations") . "\" />";
	echo "</form>";
	echo "</div>";
}
?>
