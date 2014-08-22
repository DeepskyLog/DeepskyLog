<?php
// setup_objects_query.php
// interface to query objects
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	setup_objects_query ();
function setup_objects_query() {
	global $baseURL, $loggedUser, $objPresentations, $objUtil, $objObject, $objList, $objAtlas, $catalog, $catNumber, $atlas, $atlasPageNumber, $entryMessage, $DSOcatalogs, $pageError, $minDeclDegreesError, $minDeclMinutesError, $minDeclSecondsError, $maxDeclDegreesError, $maxDeclMinutesError, $maxDeclSecondsError, $minRAHoursError, $minRAMinutesError, $minRASecondsError, $maxRAHoursError, $maxRAMinutesError, $maxRASecondsError, $maxMagError, $minMagError, $maxSBError, $minSBError, $minSizeError, $maxSizeError, $minContrastError, $maxContrastError, $listError;
	$QobjParamsCount = 26;
	if ($objUtil->checkGetKey ( 'object' ))
		$entryMessage .= LangInstructionsNoObjectFound . $_GET ['object'];
	$link = $baseURL . "index.php?indexAction=query_objects";
	reset ( $_GET );
	while ( list ( $key, $value ) = each ( $_GET ) )
		if (($key != 'indexAction') && ($key != 'multiplepagenr') && ($key != 'sort') && ($key != 'sortdirection') && ($key != 'showPartOfs'))
			$link .= '&amp;' . $key . '=' . $value;
	echo "<div id=\"main\">";
	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"get\">";
	echo "<div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"query_objects\" />";
	echo "<input type=\"hidden\" name=\"title\" value=\"" . LangSelectedObjectsTitle . "\" />";
	echo "<input type=\"hidden\" name=\"source\" value=\"setup_objects_query\" />";
	echo "<input type=\"hidden\" name=\"sort\" value=\"showname\" />";
	echo "<input type=\"hidden\" name=\"sortdirection\" value=\"asc\" />";
	echo "<input type=\"hidden\" name=\"showPartOfs\" value=\"0\" />";
	$content1 = LangSeen;
	$seen = $objUtil->checkGetKey ( 'seen' );
	if ($seen == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$seen = $_SESSION ['QobjParams'] ['seen'];
	$content2 = "<select name=\"seen\" id=\"seen\" class=\"form-control\">";
	$content2 .= "<option value=\"A\"  " . ($seen == "A" ? "selected=\"selected\"" : "") . ">" . LangSeenDontCare . "</option>";
	$content2 .= "<option value=\"XY\" " . ($seen == "XY" ? "selected=\"selected\"" : "") . ">" . LangSeenByMeOrSomeoneElse . "</option>";
	$content2 .= "<option value=\"SD\" " . ($seen == "SD" ? "selected=\"selected\"" : "") . ">" . LangDrawn . "</option>";
	$content2 .= "<option value=\"-\"  " . ($seen == "-" ? "selected=\"selected\"" : "") . ">" . LangNotSeen . "</option>";
	$content2 .= "<option value=\"-Z\" " . ($seen == "-Z" ? "selected=\"selected\"" : "") . ">" . LangNotDrawn . "</option>";
	if ($loggedUser) {
		$content2 .= "<option value=\"Y\"   " . ($seen == "Y" ? "selected=\"selected\"" : "") . ">" . LangSeenByMe . "</option>";
		$content2 .= "<option value=\"D\"   " . ($seen == "D" ? "selected=\"selected\"" : "") . ">" . LangDrawnByMe . "</option>";
		$content2 .= "<option value=\"-X\"  " . ($seen == "-X" ? "selected=\"selected\"" : "") . ">" . LangNotSeenByMeOrNotSeenAtAll . "</option>";
		$content2 .= "<option value=\"-SZ\" " . ($seen == "-SZ" ? "selected=\"selected\"" : "") . ">" . LangNotDrawnByMe . "</option>";
		$content2 .= "<option value=\"X\"   " . ($seen == "X" ? "selected=\"selected\"" : "") . ">" . LangSeenSomeoneElse . "</option>";
	}
	$content2 .= "</select>";
	$content3 = "<input type=\"submit\" class=\"btn btn-success\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />&nbsp;";
	$content3 .= '<input type="button" class="btn btn-danger" onclick="clearFields();" value="' . LangQueryObjectsButton2 . '"/>';
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "deepsky/content/setup_objects_query.js\"></script>";
	echo "<h4>" . LangQueryObjectsTitle . "</h4>";
	echo "<span class=\"pull-right\">" . $content3 . "</span>";
	echo "<br /><hr />";
	
	echo "<table><tr><td><strong>" . $content1 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">" . $content2;
	echo "</td></tr>";
	
	// OBJECT NAME
	if ($catalog == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$catalog = $_SESSION ['QobjParams'] ['catalog'];
	echo "<tr><td><strong>" . LangQueryObjectsField1 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	echo "<select id=\"catalog\" name=\"catalog\" class=\"form-control\">";
	echo "<option value=\"\">-----</option>";
	while ( list ( $key, $value ) = each ( $DSOcatalogs ) )
		echo "<option" . (($value == $catalog) ? " selected=\"selected\"" : "") . " value=\"" . $value . "\">$value</option>";
	echo "</select>";
	if ($catNumber == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$catNumber = $_SESSION ['QobjParams'] ['catNumber'];
	echo "<input id=\"catNumber\" name=\"catNumber\" type=\"text\" class=\"form-control\" maxlength=\"255\" size=\"30\" value=\"" . $catNumber . "\" />";
	echo "</td></tr>";
	
	// ATLAS PAGE NUMBER
	echo "<tr><td><strong>" . LangQueryObjectsField12 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if ($atlas == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$atlas = $_SESSION ['QobjParams'] ['atlas'];
	echo "<select id=\"atlas\" name=\"atlas\" class=\"form-control\">";
	echo "<option value=\"\">-----</option>";
	while ( list ( $key, $value ) = each ( $objAtlas->atlasCodes ) )
		echo "<option " . (($key == $atlas) ? " selected=\"selected\"" : "") . " value=\"" . $key . "\">" . $value . "</option>";
	echo "</select>";
	if ($atlasPageNumber == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$atlasPageNumber = $_SESSION ['QobjParams'] ['atlasPageNumber'];
	echo "<input id=\"atlasPageNumber\" name=\"atlasPageNumber\" type=\"number\" min=\"1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $atlasPageNumber . "\" />";
	echo "</td></tr>";
	// CONSTELLATION
	echo "<tr><td><strong>" . LangQueryObjectsField2 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	$constellations = $objObject->getConstellations (); // should be sorted
	while ( list ( $key, $value ) = each ( $constellations ) )
		$cons [$value] = $GLOBALS [$value];
	asort ( $cons );
	reset ( $cons );
	$con = $objUtil->checkGetKey ( 'con' );
	if ($con == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$con = $_SESSION ['QobjParams'] ['con'];
	echo "<select id=\"con\" name=\"con\" class=\"form-control\">";
	echo "<option value=\"\">-----</option>"; // empty field
	while ( list ( $key, $value ) = each ( $cons ) )
		echo "<option" . (($key == $con) ? " selected=\"selected\"" : "") . " value=\"$key\">" . $value . "</option>";
	echo "</select>";
	echo LangTo;
	$conto = $objUtil->checkGetKey ( 'conto', '' );
	if ($conto == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$conto = $_SESSION ['QobjParams'] ['conto'];
	if ($conto == '')
		$conto = $con;
	echo "<select id=\"conto\" name=\"conto\" class=\"form-control\">";
	echo "<option value=\"\">-----</option>"; // empty field
	if (array_key_exists ( 'conto', $_GET ))
		$con = $_GET ['conto'];
	else
		$con = '';
	reset ( $cons );
	while ( list ( $key, $value ) = each ( $cons ) )
		echo "<option " . (($key == $conto) ? "selected=\"selected\"" : "") . " value=\"$key\">" . $value . "</option>";
	echo "</select>";
	echo "</td></tr>";
	// TYPE
	echo "<tr><td><strong>" . LangQueryObjectsField11 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	$types = $objObject->getDsObjectTypes ();
	while ( list ( $key, $value ) = each ( $types ) )
		$stypes [$value] = $GLOBALS [$value];
	asort ( $stypes );
	$type = $objUtil->checkGetKey ( 'type' );
	if ($type == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$type = $_SESSION ['QobjParams'] ['type'];
	echo "<select id=\"type\" name=\"type\" class=\"form-control\">";
	echo "<option value=\"\">-----</option>";
	while ( list ( $key, $value ) = each ( $stypes ) )
		echo "<option " . (($key == $type) ? "selected=\"selected\" " : "") . "value=\"$key\">" . $value . "</option>";
	echo "</select>";
	echo "</td></tr>";
	// MINIMUM DECLINATION
	echo "<tr><td><strong>" . LangQueryObjectsField9 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($minDeclDegrees = $objUtil->checkGetKey ( 'minDeclDegrees' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['mindecl'] !== ''))
			$minDeclDegrees = ( int ) ($_SESSION ['QobjParams'] ['mindecl']);
	if (($minDeclMinutes = $objUtil->checkGetKey ( 'minDeclMinutes' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['mindecl'] !== ''))
			$minDeclMinutes = ( int ) (abs ( $_SESSION ['QobjParams'] ['mindecl'] * 60 ) % 60);
	if (($minDeclSeconds = $objUtil->checkGetKey ( 'minDeclSeconds' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['mindecl'] !== ''))
			$minDeclSeconds = round ( abs ( $_SESSION ['QobjParams'] ['mindecl'] * 3600 ) ) % 60;
	echo "<input id=\"minDeclDegrees\" name=\"minDeclDegrees\" type=\"number\" min=\"-90\" max=\"90\" class=\"form-control\" maxlength=\"3\" size=\"4\" value=\"" . $minDeclDegrees . "\" />&nbsp;&deg;&nbsp;";
	echo "<input id=\"minDeclMinutes\" name=\"minDeclMinutes\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $minDeclMinutes . "\" />&nbsp;&#39;&nbsp;";
	echo "<input id=\"minDeclSeconds\" name=\"minDeclSeconds\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $minDeclSeconds . "\" />&nbsp;&quot;&nbsp;";
	echo "</td></tr>";
	// MAXIMUM DECLINATION
	echo "<tr><td><strong>" . LangQueryObjectsField10 . "</strong></td>";
	echo "<td colspan=\"3\"  class=\"form-inline\">";
	if (($maxDeclDegrees = $objUtil->checkGetKey ( 'maxDeclDegrees' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['maxdecl'] !== ''))
			$maxDeclDegrees = ( int ) ($_SESSION ['QobjParams'] ['maxdecl']);
	if (($maxDeclMinutes = $objUtil->checkGetKey ( 'maxDeclMinutes' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['maxdecl'] !== ''))
			$maxDeclMinutes = ( int ) (abs ( $_SESSION ['QobjParams'] ['maxdecl'] * 60 ) % 60);
	if (($maxDeclSeconds = $objUtil->checkGetKey ( 'maxDeclSeconds' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['maxdecl'] !== ''))
			$maxDeclSeconds = round ( abs ( $_SESSION ['QobjParams'] ['maxdecl'] * 3600 ) ) % 60;
	echo "<input id=\"maxDeclDegrees\" name=\"maxDeclDegrees\" type=\"number\" min=\"-90\" max=\"90\" class=\"form-control\" maxlength=\"3\" size=\"4\" value=\"" . $maxDeclDegrees . "\" />&nbsp;&deg;&nbsp;";
	echo "<input id=\"maxDeclMinutes\" name=\"maxDeclMinutes\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $maxDeclMinutes . "\" />&nbsp;&#39;&nbsp;";
	echo "<input id=\"maxDeclSeconds\" name=\"maxDeclSeconds\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $maxDeclSeconds . "\" />&nbsp;&quot;&nbsp;";
	echo "</td></tr>";
	// MINIMUM RIGHT ASCENSION
	echo "<tr><td><strong>" . LangQueryObjectsField7 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($minRAHours = $objUtil->checkGetKey ( 'minRAHours' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['minra'] !== ''))
			$minRAHours = ( int ) ($_SESSION ['QobjParams'] ['minra']);
	if (($minRAMinutes = $objUtil->checkGetKey ( 'minRAMinutes' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['minra'] !== ''))
			$minRAMinutes = ( int ) (abs ( $_SESSION ['QobjParams'] ['minra'] * 60 ) % 60);
	if (($minRASeconds = $objUtil->checkGetKey ( 'minRASeconds' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['minra'] !== ''))
			$minRASeconds = round ( abs ( $_SESSION ['QobjParams'] ['minra'] * 3600 ) ) % 60;
	echo "<input id=\"minRAHours\"   name=\"minRAHours\"   type=\"number\" min=\"0\" max=\"23\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $minRAHours . "\" />&nbsp;h&nbsp;";
	echo "<input id=\"minRAMinutes\" name=\"minRAMinutes\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $minRAMinutes . "\" />&nbsp;m&nbsp;";
	echo "<input id=\"minRASeconds\" name=\"minRASeconds\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $minRASeconds . "\" />&nbsp;s&nbsp;";
	echo "</div></div></div>";
	// MAXIMUM RIGHT ASCENSION
	echo "<tr><td><strong>" . LangQueryObjectsField8 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($maxRAHours = $objUtil->checkGetKey ( 'maxRAHours' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['maxra'] !== ''))
			$maxRAHours = ( int ) ($_SESSION ['QobjParams'] ['maxra']);
	if (($maxRAMinutes = $objUtil->checkGetKey ( 'maxRAMinutes' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['maxra'] !== ''))
			$maxRAMinutes = ( int ) (abs ( $_SESSION ['QobjParams'] ['maxra'] * 60 ) % 60);
	if (($maxRASeconds = $objUtil->checkGetKey ( 'maxRASeconds' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount) && ($_SESSION ['QobjParams'] ['maxra'] !== ''))
			$maxRASeconds = round ( abs ( $_SESSION ['QobjParams'] ['maxra'] * 3600 ) ) % 60;
	echo "<input id=\"maxRAHours\"   name=\"maxRAHours\"   type=\"number\" min=\"0\" max=\"23\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $maxRAHours . "\" />&nbsp;h&nbsp;";
	echo "<input id=\"maxRAMinutes\" name=\"maxRAMinutes\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $maxRAMinutes . "\" />&nbsp;m&nbsp;";
	echo "<input id=\"maxRASeconds\" name=\"maxRASeconds\" type=\"number\" min=\"0\" max=\"59\" class=\"form-control\" maxlength=\"2\" size=\"3\" value=\"" . $maxRASeconds . "\" />&nbsp;s&nbsp;";
	echo "</td></tr>";
	// MAGNITUDE BRIGHTER THAN
	echo "<tr><td><strong>" . LangQueryObjectsField4 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($maxMag = $objUtil->checkGetKey ( 'maxMag' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$maxMag = $_SESSION ['QobjParams'] ['maxmag'];
	echo "<input id=\"maxMag\" name=\"maxMag\" type=\"number\" min=\"-5\" max=\"24\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $maxMag . "\" />";
	echo "</td></tr>";
	// MAGNITUDE LESS THAN
	echo "<tr><td><strong>" . LangQueryObjectsField3 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($minMag = $objUtil->checkGetKey ( 'minMag' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$minMag = $_SESSION ['QobjParams'] ['minmag'];
	echo "<input id=\"minMag\" name=\"minMag\" type=\"number\" min=\"-5\" max=\"24\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $minMag . "\" />";
	echo "</td></tr>";
	// SURFACE BRIGHTNESS BRIGHTER THAN
	echo "<tr><td><strong>" . LangQueryObjectsField6 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($maxSB = $objUtil->checkGetKey ( 'maxSB' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$maxSB = $_SESSION ['QobjParams'] ['maxsubr'];
	echo "<input id=\"maxSB\" name=\"maxSB\" type=\"number\" min=\"-5\" max=\"24\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $maxSB . "\" />";
	echo "</td></tr>";
	// SURFACE BRIGHTNESS LESS THAN
	echo "<tr><td><strong>" . LangQueryObjectsField5 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	if (($minSB = $objUtil->checkGetKey ( 'minSB' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$minSB = $_SESSION ['QobjParams'] ['minsubr'];
	echo "<input id=\"minSB\" name=\"minSB\" type=\"number\" min=\"-5\" max=\"24\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $minSB . "\" />";
	echo "</td></tr>";
	// MINIMIM SIZE
	echo "<tr><td><strong>" . LangQueryObjectsField13 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	$size_min_units = $objUtil->checkGetKey ( 'size_min_units' );
	if (($minSize = $objUtil->checkGetKey ( 'minSize' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount)) {
			$minSize = $_SESSION ['QobjParams'] ['mindiam1'];
			$size_min_units = "sec";
		}
	echo "<input id=\"minSize\" name=\"minSize\" type=\"number\" min=\"0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $minSize . "\" />";
	echo "&nbsp;&nbsp;";
	echo "<select id=\"size_min_units\" name=\"size_min_units\" class=\"form-control\">";
	echo "<option" . (($size_min_units == "min") ? " selected=\"selected\"" : "") . " value=\"min\">" . LangNewObjectSizeUnits1 . "</option>";
	echo "<option" . (($size_min_units == "sec") ? " selected=\"selected\"" : "") . " value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
	echo "</select>";
	echo "</td></tr>";
	// MAXIMUM SIZE
	echo "<tr><td><strong>" . LangQueryObjectsField14 . "</strong></td>";
	echo "<td colspan=\"3\" class=\"form-inline\">";
	$size_max_units = $objUtil->checkGetKey ( 'size_max_units' );
	if (($maxSize = $objUtil->checkGetKey ( 'maxSize' )) == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount)) {
			$maxSize = $_SESSION ['QobjParams'] ['maxdiam1'];
			$size_max_units = "sec";
		}
	echo "<input id=\"maxSize\" name=\"maxSize\" type=\"number\" min=\"0\" step=\"0.1\" class=\"form-control\" maxlength=\"4\" size=\"4\" value=\"" . $maxSize . "\" />";
	echo "&nbsp;&nbsp;";
	echo "<select id=\"size_max_units\" name=\"size_max_units\" class=\"form-control\">";
	echo "<option" . (($size_max_units == "min") ? " selected=\"selected\"" : "") . " value=\"min\">" . LangNewObjectSizeUnits1 . "</option>";
	echo "<option" . (($size_max_units == "sec") ? " selected=\"selected\"" : "") . " value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
	echo "</select>";
	echo "</td></tr>";
	if ($loggedUser) {
		echo "<tr><td><strong>" . LangQueryObjectsField18 . "</strong></td>";
		echo "<td colspan=\"3\" class=\"form-inline\">";
		if (($minContrast = $objUtil->checkGetKey ( 'minContrast' )) == '')
			if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
				$minContrast = $_SESSION ['QobjParams'] ['minContrast'];
		echo "<input id=\"minContrast\" name=\"minContrast\" type=\"number\" min=\"-5.0\" max=\"10.0\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" size=\"5\" value=\"" . $minContrast . "\" />";
		echo "</td></tr>";
		
		// MAXIMUM CONTRAST RESERVE
		echo "<tr><td><strong>" . LangQueryObjectsField17 . "</strong></td>";
		echo "<td colspan=\"3\" class=\"form-inline\">";
		if (($maxContrast = $objUtil->checkGetKey ( 'maxContrast' )) == '')
			if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
				$maxContrast = $_SESSION ['QobjParams'] ['maxContrast'];
		echo "<input id=\"maxContrast\" name=\"maxContrast\" type=\"number\" min=\"-5.0\" max=\"10.0\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" size=\"5\" value=\"" . $maxContrast . "\" />";
		echo "</td></tr>";
		// IN LIST
		echo "<tr><td><strong>" . LangQueryObjectsField19 . "</strong></td>";
		echo "<td colspan=\"3\" class=\"form-inline\">";
		$lists = $objList->getLists ();
		echo "<select id=\"inList\" name=\"inList\" class=\"form-control\">";
		echo "<option value=\"\">-----</option>";
		if (($inList = $objUtil->checkGetKey ( 'inList' )) == '')
			if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
				$inList = $_SESSION ['QobjParams'] ['inList'];
		while ( list ( $key, $value ) = each ( $lists ) )
			echo ("<option" . (($value == $inList) ? " selected=\"selected\"" : "") . " value=\"" . $value . "\">" . $value . "</option>");
		echo "</select>";
		echo "</td></tr>";
		// NOT IN LIST
		echo "<tr><td><strong>" . LangQueryObjectsField20 . "</strong></td>";
		echo "<td colspan=\"3\" class=\"form-inline\">";
		reset ( $lists );
		echo "<select id=\"notInList\" name=\"notInList\" class=\"form-control\">";
		echo "<option value=\"\">-----</option>";
		if (($notInList = $objUtil->checkGetKey ( 'notInList' )) == '')
			if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
				$notInList = $_SESSION ['QobjParams'] ['notInList'];
		while ( list ( $key, $value ) = each ( $lists ) )
			echo ("<option" . (($value == $notInList) ? " selected=\"selected\"" : "") . " value=\"" . $value . "\">" . $value . "</option>");
		echo "</select>";
		echo "</td></tr>";
		// DESCRIPTION CONTAINS
		$descriptioncontains = $objUtil->checkGetKey ( 'descriptioncontains' );
		if (($descriptioncontains = $objUtil->checkGetKey ( 'descriptioncontains' )) == '')
			if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
				$descriptioncontains = $_SESSION ['QobjParams'] ['descriptioncontains'];
		echo "<tr><td><strong>" . LangDescriptioncontains . "</strong></td>";
		echo "<td colspan=\"3\" class=\"form-inline\">";
		echo "<input id=\"descriptioncontains\" name=\"descriptioncontains\" type=\"text\" class=\"form-control\" maxlength=\"50\" size=\"30\" value=\"" . $descriptioncontains . "\" />";
		echo "</td></tr>";
	}
	// EXCLUDE LARGE CATALOGS
	echo "<tr>";
	echo "<td><strong>" . LangExclude . "</strong></td>";
	$j = 1;
	reset ( $DSOcatalogs );
	$temp = "";
	while ( list ( $key, $value ) = each ( $DSOcatalogs ) ) {
		if (($nmb = $objObject->getNumberOfObjectsInCatalog ( $value )) > 10) {
			$checked = '';
			if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
				$checked = in_array ( $value, $_SESSION ['QobjParams'] ['excl'] );
			echo "<td class=\"form-inline\"><input id=\"excl_" . $value . "\" name=\"excl_" . $value . "\" type=\"checkbox\" " . ($checked ? "checked=\"checked\"" : "") . "/>" . $value . " (" . $nmb . " objects" . ")</td>";
			$temp = $temp . "excl_" . $value . "/";
			if (! ($j ++ % 3))
				echo "</tr><tr><td></td>";
		}
	}
	echo "</tr>";
	// NO EXCLUDE IF SEEN
	echo "<tr>";
	echo "<td><strong>";
	echo LangNoExcludeIfSeen;
	echo "</strong></td>";
	$excludeexceptseen = $objUtil->checkGetKey ( 'exclexceptseen' );
	if ($excludeexceptseen == '')
		if (array_key_exists ( 'QobjParams', $_SESSION ) && (count ( $_SESSION ['QobjParams'] ) == $QobjParamsCount))
			$excludeexceptseen = $_SESSION ['QobjParams'] ['exclexceptseen'];
	echo "<td><input id=\"excludeexceptseen\" name=\"excludeexceptseen\" type=\"checkbox\" " . ($excludeexceptseen == "on" ? "checked=\"checked\" " : '') . " /></td>";
	echo "<td></td>";
	echo "</tr>";
	echo "</table>";
	echo "<input id=\"temp\" type=\"hidden\" value=\"" . $temp . "\" />";
	echo "</div>";
	echo "</form>";
	echo "</table><hr />";
	if ($loggedUser) {
		echo "<form role=\"form\"><div class=\"form-inline\">";
		$content = LangStoredQueries . "&nbsp;";
		$content .= '<select id="observerqueries" class="form-control" onchange="restoreQuery();"><option value="-----">-----</option></select>' . '&nbsp;';
		$content .= '<input id="savequeryas" class="btn btn-success" type="button" value="' . LangSaveAs . '" onclick="saveObserverQueryAs();"/>' . '&nbsp;';
		$content .= '<input id="deletequery" class="btn btn-danger" type="button" value="' . LangRemoveQuery . '" class="hidden" onclick="removeQuery();"/>' . '&nbsp;';
		echo $content;
		echo "</div></form>";
	}
	echo "</div>";
	echo '<script type="text/javascript">setobserverqueries();</script>';
}
?>
