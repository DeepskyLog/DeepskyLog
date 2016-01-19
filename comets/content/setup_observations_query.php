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
	echo "<h4>" . LangQueryObservationsTitle . "</h4>";
	echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"query\" value=\"" . LangQueryObservationsTitle . "\" />";
	echo "<br /><hr />";

	// OBJECT NAME
	$content1 = LangQueryObjectsField1;
	$content2 = "<select class=\"form-control\" name=\"object\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	$catalogs = $objCometObject->getSortedObjects ( "name" );
	while ( list ( $key, $value ) = each ( $catalogs ) )
		$content2 .= "<option value=\"" . $value [0] . "\"" . ((($id) && ($id == $objCometObject->getId ( $value[0] ))) ? " selected=\"selected\" " : "") . ">" . $value [0] . "</option>";
	$content2 .= "</select>";
	echo "<strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// OBSERVER
	$content1 = LangViewObservationField2;
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
	$content1 = LangViewObservationField3;
	$content2 = "<select class=\"form-control\" name=\"instrument\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	$inst = $objInstrument->getSortedInstrumentsList ( "name" );
	while ( list ( $key, $value ) = each ( $inst ) )
		$content2 .= "<option value=\"" . $key . "\">" . $value . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM DIAMETER
	$content1 = LangViewObservationField13;
	$content2 = "<input type=\"number\" min=\"0\" step=\"0.1\" class=\"form-control\" maxlength=\"64\" name=\"mindiameter\" size=\"10\" />";
	$content2 .= "&nbsp;";
	$content2 .= "<select name=\"mindiameterunits\" class=\"form-control\"><option selected=\"selected\">&nbsp;</option><option>inch</option><option>mm</option></select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM DIAMETER
	$content1 = LangViewObservationField14;
	$content2 = "<input type=\"number\" min=\"0\" step=\"0.1\" class=\"form-control\" maxlength=\"64\" name=\"maxdiameter\" size=\"10\" />";
	$content2 .= "&nbsp;";
	$content2 .= "<select name=\"maxdiameterunits\" class=\"form-control\"><option selected=\"selected\">&nbsp;</option><option>inch</option><option>mm</option></select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// SITE
	$content1 = LangViewObservationField4;
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
	$content1 = LangFromDate;
	$content2 = "<input type=\"number\" min=\"1\" max=\"31\" class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"minday\" value=\"\" />";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<select class=\"form-control\" name=\"minmonth\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i < 13; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . constant ( "LangNewObservationMonth" . $i ) . "</option>";
	$content2 .= "</select>";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<input type=\"number\" min=\"1609\" class=\"form-control\" maxlength=\"4\" size=\"5\" name=\"minyear\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM DATE
	$content1 = LangTillDate;
	$content2 = "<input type=\"number\" min=\"1\" max=\"31\" class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"maxday\" value=\"\" />";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<select class=\"form-control\" name=\"maxmonth\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i < 13; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . constant ( "LangNewObservationMonth" . $i ) . "</option>";
	$content2 .= "</select>";
	$content2 .= "&nbsp;&nbsp;";
	$content2 .= "<input type=\"number\" min=\"1609\" class=\"form-control\" name=\"maxyear\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// DESCRIPTION
	$content1 = LangQueryObservationsMessage2;
	$content2 = "<input type=\"text\" class=\"form-control\" maxlength=\"40\" name=\"description\" size=\"35\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM MAGNITUDE
	$content1 = LangQueryObjectsField4;
	$content2 = "<input type=\"number\" min=\"-5.0\" max=\"20.0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" name=\"maxmag\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM MAGNITUDE
	$content1 = LangQueryObjectsField3;
	$content2 = "<input type=\"number\" min=\"-5.0\" max=\"20.0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" name=\"minmag\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM DC
	$content1 = LangQueryCometObjectsField3;
	$content2 = "<select class=\"form-control\" name=\"mindc\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i <= 9; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . $i . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM DC
	$content1 = LangQueryCometObjectsField4;
	$content2 = "<select class=\"form-control\" name=\"maxdc\">";
	$content2 .= "<option value=\"\">&nbsp;</option>";
	for($i = 1; $i <= 9; $i ++)
		$content2 .= "<option value=\"" . $i . "\">" . $i . "</option>";
	$content2 .= "</select>";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM COMA
	$content1 = LangQueryCometObjectsField5;
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" name=\"mincoma\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM COMA
	$content1 = LangQueryCometObjectsField6;
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" name=\"maxcoma\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MINIMUM TAIL
	$content1 = LangQueryCometObjectsField7;
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\"  class=\"form-control\" maxlength=\"4\" name=\"mintail\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";

	// MAXIMUM TAIL
	$content1 = LangQueryCometObjectsField8;
	$content2 = "<input type=\"number\" min=\"0.0\" step=\"0.01\"  class=\"form-control\" maxlength=\"4\" name=\"maxtail\" size=\"4\" value=\"\" />";
	echo "<br /><strong>" . $content1 . "</strong><br />";
	echo "<span class=\"form-inline\">" . $content2 . "</span>";
	echo "</div>";
	echo "<br /><input type=\"submit\" class=\"btn btn-success\" name=\"query\" value=\"" . LangQueryObservationsTitle . "\" />";
	echo "</form>";
	echo "</div>";
}
?>
