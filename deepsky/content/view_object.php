<?php
// view_object.php
// view all information of one object
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else if (! ($object = $objUtil->checkGetKey ( 'object' )))
	throw new Exception ( 'To implement' );
else
	view_object ();
function showButtons($theLocation, $viewobjectdetails, $viewobjectephemerides, $viewobjectobjectsnearby, $viewobjectobservations) {
	global $baseURL, $object, $objLocation, $objObject, $objPresentations, $objUtil;
	$object_ss = stripslashes ( $object );
	$seen = $objObject->getSeen ( $object );
	$content1 = "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectextrainfo=hidden' . "\" >-</a>";
	$content1 .= "&nbsp;&nbsp;&nbsp;&nbsp;";
	$content1 .= "<input type=\"button\" class=\"btn btn-success\" value=\">\" 
	               title=\"" . LangButtonOnlyObjectDetails . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "\"
	               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=show&amp;viewobjectephemerides=hidden&amp;viewobjectobjectsnearby=hidden&amp;viewobjectobservations=hidden' . "';\"/>";
	if ($viewobjectdetails == "hidden")
		$content1 .= "<input type=\"button\" class=\"btn\" value=\"+ " . LangButtonObjectDetails . "\" 
	               title=\"" . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "\"
	               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=show' . "';\"/>";
	else
		$content1 .= "<input type=\"button\" class=\"btn\" value=\"- " . LangButtonObjectDetails . "\" 
	               title=\"" . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "\"
	               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=hidden' . "';\"/>";
	if ($theLocation) {
		$content1 .= "&nbsp;" . "&nbsp;" . "&nbsp;";
		$content1 .= "<input type=\"button\" class=\"btn btn-success\" value=\">\" 
		               title=\"" . LangButtonOnlyObjectEphemerides . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "\"
		               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=hidden&amp;viewobjectephemerides=show&amp;viewobjectobjectsnearby=hidden&amp;viewobjectobservations=hidden' . "';\"/>";
		if ($viewobjectephemerides == "hidden")
			$content1 .= "<input type=\"button\" class=\"btn\" value=\"+ " . LangButtonObjectEphemerides . "\" 
		               title=\"" . ReportEpehemeridesFor . "&nbsp;" . $object_ss . ' ' . ReportEpehemeridesIn . ' ' . $objLocation->getLocationPropertyFromId ( $theLocation, 'name' ) . "\"
		               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectephemerides=show' . "';\"/>";
		else
			$content1 .= "<input type=\"button\" class=\"btn\" value=\"- " . LangButtonObjectEphemerides . "\" 
		               title=\"" . ReportEpehemeridesFor . "&nbsp;" . $object_ss . ' ' . ReportEpehemeridesIn . ' ' . $objLocation->getLocationPropertyFromId ( $theLocation, 'name' ) . "\"
		               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectephemerides=hidden' . "';\"/>";
	}
	$content1 .= "&nbsp;" . "&nbsp;" . "&nbsp;";
	$content1 .= "<input type=\"button\" class=\"btn btn-success\" value=\">\" 
	               title=\"" . LangButtonOnlyObjectObjectsNearby . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "\"
	               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=hidden&amp;viewobjectephemerides=hidden&amp;viewobjectobjectsnearby=show&amp;viewobjectobservations=hidden' . "';\"/>";
	if ($viewobjectobjectsnearby == "hidden")
		$content1 .= "<input type=\"button\" class=\"btn\" value=\"+ " . LangButtonObjectObjectsNearby . "\" onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectobjectsnearby=show' . "';\"/>";
	else
		$content1 .= "<input type=\"button\" class=\"btn\" value=\"- " . LangButtonObjectObjectsNearby . "\" onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectobjectsnearby=hidden' . "';\"/>";
	$content1 .= "&nbsp;" . "&nbsp;" . "&nbsp;";
	$content1 .= "<input type=\"button\" class=\"btn btn-success\" value=\">\" 
	               title=\"" . LangButtonOnlyObjectObservations . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "\"
	               onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=hidden&amp;viewobjectephemerides=hidden&amp;viewobjectobjectsnearby=hidden&amp;viewobjectobservations=show' . "';\"/>";
	if ($viewobjectobservations == "hidden")
		$content1 .= "<input type=\"button\" class=\"btn\" value=\"+ " . LangButtonObjectObservations . "\" onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectobservations=show' . "';\"/>";
	else
		$content1 .= "<input type=\"button\" class=\"btn\" value=\"- " . LangButtonObjectObservations . "\" onclick=\"location='" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectobservations=hidden' . "';\"/>";
	$content2 = "<a href=\"" . $baseURL . "index.php?indexAction=atlaspage&amp;object=" . urlencode ( $object ) . "\"><input type=\"button\" class=\"btn pull-right btn-success\" value=\"" . LangAtlasPage . "\"/></a>";
	
	echo $content1;
	echo $content2;
	echo "<hr />";
}
function showObjectDetails($object_ss) {
	global $baseURL, $object, $objObject, $objPresentations, $objUtil;
	$objPresentations->line ( array (
			"<h4>" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectdetails=hidden' . "\" title=\"" . ObjectDetailsHide . "\">-</a> " . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $objObject->getDSOseenLink ( $object ) . "</h4>" 
	), "L", array (
			100 
	), 30 );
	echo "<hr />";
	$objObject->showObject ( $object );
}
function showObjectsNearby() {
	global $baseURL, $FF, $step, $objObject, $objPresentations, $objUtil;
	$maxcount = count ( $_SESSION ['Qobj'] );
	$max = 9999;
	if ((array_key_exists ( 'steps', $_SESSION )) && (array_key_exists ( "nearbyObjects", $_SESSION ['steps'] )))
		$step = $_SESSION ['steps'] ["nearbyObjects"];
	if (array_key_exists ( 'multiplepagenr', $_GET ))
		$min = ($_GET ['multiplepagenr'] - 1) * $step;
	elseif (array_key_exists ( 'multiplepagenr', $_POST ))
		$min = ($_POST ['multiplepagenr'] - 1) * $step;
	elseif (array_key_exists ( 'min', $_GET ))
		$min = $_GET ['min'];
	elseif (array_key_exists ( 'minViewObjectObjectsNearby', $_SESSION ))
		$min = $_SESSION ['minViewObjectObjectsNearby'];
	else
		$min = 0;
	$_SESSION ['minViewObjectObjectsNearby'] = $min;
	
	$link = $baseURL . 'index.php?indexAction=detail_object&amp;object=' . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( 'zoom', 30 ) . '&amp;SID=Qobj';
	
	$content1 = "<h4>";
	$content1 .= "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectobjectsnearby=hidden' . "\" title=\"" . ObjectNearbyObjectsHide . "\">-</a> ";
	$content1 .= $_GET ['object'];
	if (count ( $_SESSION ['Qobj'] ) > 2)
		$content1 .= ' ' . LangViewObjectAndNearbyObjects . ' ' . (count ( $_SESSION ['Qobj'] ) - 1) . ' ' . LangViewObjectNearbyObjects;
	elseif (count ( $_SESSION ['Qobj'] ) > 1)
		$content1 .= ' ' . LangViewObjectAndNearbyObjects . ' ' . (count ( $_SESSION ['Qobj'] ) - 1) . ' ' . LangViewObjectNearbyObject;
	else
		$content1 .= ' ' . LangViewObjectNoNearbyObjects;
	$content1 .= "</h4>";
	list ( $min, $max, $content2, $pageleft, $pageright, $pagemax ) = $objUtil->printNewListHeader4 ( $_SESSION ['Qobj'], $link, $min, $step );
	$objPresentations->line ( array (
			$content1,
			$content2 
	), "LR", array (
			50,
			50 
	), 30 );
	$content1 = "<form action=\"" . $link . "\" method=\"get\"><div>";
	$content1 .= LangViewObjectNearbyObjectsMoreLess . ":&nbsp;";
	$content1 .= "<select name=\"zoom\" onchange=\"submit();\">";
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "180")
		$content1 .= ("<option selected=\"selected\" value=\"180\">3x3&deg;</option>");
	else
		$content1 .= ("<option value=\"180\">3x3&deg;</option>");
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "120")
		$content1 .= ("<option selected=\"selected\" value=\"120\">2x2&deg;</option>");
	else
		$content1 .= ("<option value=\"120\">2x2&deg;</option>");
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "60")
		$content1 .= ("<option selected=\"selected\" value=\"60\">1x1&deg;</option>");
	else
		$content1 .= ("<option value=\"60\">1x1&deg;</option>");
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "30")
		$content1 .= ("<option selected=\"selected\" value=\"30\">30x30'</option>");
	else
		$content1 .= ("<option value=\"30\">30x30'</option>");
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "15")
		$content1 .= ("<option selected=\"selected\" value=\"15\">15x15'</option>");
	else
		$content1 .= ("<option value=\"15\">15x15'</option>");
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "10")
		$content1 .= ("<option selected=\"selected\" value=\"10\">10x10'</option>");
	else
		$content1 .= ("<option value=\"10\">10x10'</option>");
	if ($objUtil->checkGetKey ( 'zoom', 30 ) == "5")
		$content1 .= ("<option selected=\"selected\" value=\"5\">5x5'</option>");
	else
		$content1 .= ("<option value=\"5\">5x5'</option>");
	$content1 .= "</select>";
	$content1 .= "<input type=\"hidden\" name=\"object\" value=\"" . $_GET ['object'] . "\" /> ";
	$content1 .= "<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" /> ";
	$content1 .= "</div></form>";
	$content2 = "";
	$content2 = $objUtil->printStepsPerPage3 ( $link, "nearbyObjects", $step );
	$objPresentations->line ( array (
			$content1,
			$content2 
	), "LR", array (
			50,
			50 
	), 25 );
	echo "<hr />";
	$objObject->showObjects ( $link, $_GET ['object'], 0, '', 'view_object' );
	echo "<hr />";
	echo "<script type=\"text/javascript\">";
	echo "
  function pageOnKeyDown1(event)
  { if(event.keyCode==37)
      if(event.shiftKey)
        if(event.ctrlKey)
          location=html_entity_decode('" . $link . "&amp;multiplepagenr=0" . "');    
        else
          location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pageleft . "');
    if(event.keyCode==39)
      if(event.shiftKey) 
        if(event.ctrlKey)
          location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pagemax . "');
        else  
          location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pageright . "');
  }
  this.onKeyDownFns[this.onKeyDownFns.length] = pageOnKeyDown1;
  ";
	echo "</script>";
}
function showObjectEphemerides($theLocation) {
	global $baseURL, $object, $theMonth, $theDay, $objLocation, $objObject, $objPresentations, $objUtil;
	$longitude = 1.0 * $objLocation->getLocationPropertyFromId ( $theLocation, 'longitude' );
	$latitude = 1.0 * $objLocation->getLocationPropertyFromId ( $theLocation, 'latitude' );
	$timezone = $objLocation->getLocationPropertyFromId ( $theLocation, 'timezone' );
	$dateTimeZone = new DateTimeZone ( $timezone );
	echo "<div id=\"ephemeridesdiv\">";
	for($i = 1; $i < 13; $i ++) {
		$datestr = sprintf ( "%02d", $i ) . "/" . sprintf ( "%02d", 1 ) . "/" . $_SESSION ['globalYear'];
		$dateTime = new DateTime ( $datestr, $dateTimeZone );
		$timedifference = $dateTimeZone->getOffset ( $dateTime );
		if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0)
			$timedifference = - $timedifference;
		date_default_timezone_set ( "UTC" );
		$theTimeDifference1 [$i] = $timedifference;
		$theEphemerides1 [$i] = $objObject->getEphemerides ( $object, 1, $i, 2010 );
		$theNightEphemerides1 [$i] = date_sun_info ( strtotime ( "2010" . "-" . $i . "-" . "1" ), $latitude, $longitude );
		$datestr = sprintf ( "%02d", $i ) . "/" . sprintf ( "%02d", 1 ) . "/" . $_SESSION ['globalYear'];
		$dateTime = new DateTime ( $datestr, $dateTimeZone );
		$timedifference = $dateTimeZone->getOffset ( $dateTime );
		if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0)
			$timedifference = - $timedifference;
		date_default_timezone_set ( "UTC" );
		$theTimeDifference15 [$i] = $timedifference;
		$theEphemerides15 [$i] = $objObject->getEphemerides ( $object, 15, $i, 2010 );
		$theNightEphemerides15 [$i] = date_sun_info ( strtotime ( "2010" . "-" . $i . "-" . "15" ), $latitude, $longitude );
	}
	$objPresentations->line ( array (
			"<h4>" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectephemerides=hidden' . "\" title=\"" . ReportEpehemeridesForHide . "\">-</a> " . ReportEpehemeridesFor . "&nbsp;" . stripslashes ( $object ) . ' ' . ReportEpehemeridesIn . ' ' . $objLocation->getLocationPropertyFromId ( $theLocation, 'name' ) . "</h4>" 
	), "L", array (
			100 
	), 30 );
	echo "<hr />";
	echo "<table>";
	echo "<tr class=\"type10\">";
	echo "<td class=\"right\">" . LangMonth . " > </td>";
	for($i = 1; $i < 13; $i ++) {
		$background1 = '';
		$background15 = '';
		if ((($i == $theMonth) && ($theDay < 8)) || ((($i - 1) == $theMonth) && ($theDay > 22)))
			$background1 = " style=\"background-color:#FFAAAA\" ";
		
		if (($i == $theMonth) && ($theDay >= 8) && ($theDay <= 22))
			$background15 = " style=\"background-color:#FFAAAA\" ";
		
		echo "<td " . $background1 . ">&nbsp;</td><td class=\"centered\" " . $background15 . ">" . $i . "</td>";
	}
	$background1 = '';
	if ((12 == $theMonth) && ($theDay > 22))
		$background1 = " style=\"background-color:#FFAAAA\" ";
	echo "<td" . $background1 . ">&nbsp;</td>";
	echo "</tr>";
	echo "<tr class=\"type20\">";
	echo "<td class=\"centered\">" . LangMaxAltitude . "</td>";
	for($i = 1; $i < 13; $i ++) {
		$colorclass = "";
		if ($i == 1) {
			if (($theEphemerides1 [$i] ['altitude'] != '-') && ($theEphemerides15 [$i] ['altitude'] != '-') && (($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [$i] ['altitude']) || ($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [12] ['altitude'])))
				$colorclass = "ephemeridesgreen";
		} else if (($theEphemerides1 [$i] ['altitude'] != '-') && ($theEphemerides15 [$i] ['altitude'] != '-') && (($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [$i] ['altitude']) || ($theEphemerides1 [$i] ['altitude'] == $theEphemerides15 [$i - 1] ['altitude'])))
			$colorclass = "ephemeridesgreen";
		echo "<td class=\"centered " . $colorclass . "\">" . $theEphemerides1 [$i] ['altitude'] . "</td>";
		$colorclass = "";
		if ($i == 12) {
			if (($theEphemerides1 [$i] ['altitude'] != '-') && ($theEphemerides15 [$i] ['altitude'] != '-') && (($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [$i] ['altitude']) || ($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [1] ['altitude'])))
				$colorclass = "ephemeridesgreen";
		} else if (($theEphemerides15 [$i] ['altitude'] != '-') && ($theEphemerides15 [$i] ['altitude'] != '-') && (($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [$i] ['altitude']) || ($theEphemerides15 [$i] ['altitude'] == $theEphemerides1 [$i + 1] ['altitude'])))
			$colorclass = "ephemeridesgreen";
		echo "<td class=\"centered " . $colorclass . "\">" . $theEphemerides15 [$i] ['altitude'] . "</td>";
	}
	$colorclass = "";
	if (($theEphemerides1 [1] ['altitude'] != '-') && ($theEphemerides15 [1] ['altitude'] != '-') && (($theEphemerides1 [1] ['altitude'] == $theEphemerides15 [1] ['altitude']) || ($theEphemerides1 [1] ['altitude'] == $theEphemerides15 [12] ['altitude'])))
		$colorclass = "ephemeridesgreen";
	echo "<td class=\"centered " . $colorclass . "\">" . $theEphemerides1 [1] ['altitude'] . "</td>";
	echo "</tr>";
	echo "<tr class=\"type10\">";
	echo "<td class=\"centered\">" . LangTransit . "</td>";
	for($i = 1; $i < 13; $i ++) {
		$colorclass = "";
		if ((date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinuteBetweenOthers ( $theEphemerides1 [$i] ['transit'], date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] + $theTimeDifference1 [$i] ), date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_begin"] + $theTimeDifference1 [$i] ) ))
			$colorclass = "ephemeridesgreen";
		elseif ((date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinuteBetweenOthers ( $theEphemerides1 [$i] ['transit'], date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] + $theTimeDifference1 [$i] ), date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_begin"] + $theTimeDifference1 [$i] ) ))
			$colorclass = "ephemeridesyellow";
		echo "<td class=\"centered " . $colorclass . "\">" . $theEphemerides1 [$i] ['transit'] . "</td>";
		$colorclass = "";
		if ((date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinuteBetweenOthers ( $theEphemerides15 [$i] ['transit'], date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_end"] + $theTimeDifference15 [$i] ), date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_begin"] + $theTimeDifference15 [$i] ) ))
			$colorclass = "ephemeridesgreen";
		elseif ((date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinuteBetweenOthers ( $theEphemerides15 [$i] ['transit'], date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] + $theTimeDifference15 [$i] ), date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_begin"] + $theTimeDifference15 [$i] ) ))
			$colorclass = "ephemeridesyellow";
		echo "<td class=\"centered " . $colorclass . "\">" . $theEphemerides15 [$i] ['transit'] . "</td>";
	}
	$colorclass = "";
	if ((date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinuteBetweenOthers ( $theEphemerides1 [1] ['transit'], date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] + $theTimeDifference1 [1] ), date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_begin"] + $theTimeDifference1 [1] ) ))
		$colorclass = "ephemeridesgreen";
	elseif ((date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinuteBetweenOthers ( $theEphemerides1 [1] ['transit'], date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] + $theTimeDifference1 [1] ), date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_begin"] + $theTimeDifference1 [1] ) ))
		$colorclass = "ephemeridesyellow";
	echo "<td class=\"centered " . $colorclass . "\">" . $theEphemerides1 [1] ['transit'] . "</td>";
	echo "</tr>";
	echo "<tr class=\"type20\">";
	echo "<td class=\"centered\">" . LangAstroNight . "</td>";
	for($i = 1; $i < 13; $i ++) {
		echo "<td class=\"centered\">" . ((date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] ) != "00:00") ? date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] + $theTimeDifference1 [$i] ) . "<br />-<br />" . date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_begin"] + $theTimeDifference1 [$i] ) : "-") . "</td>";
		echo "<td class=\"centered\">" . ((date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_end"] ) != "00:00") ? date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_end"] + $theTimeDifference15 [$i] ) . "<br />-<br />" . date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_begin"] + $theTimeDifference15 [$i] ) : "-") . "</td>";
	}
	echo "<td class=\"centered\">" . ((date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] ) != "00:00") ? date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] + $theTimeDifference1 [1] ) . "<br />-<br />" . date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_begin"] + $theTimeDifference1 [1] ) : "-") . "</td>";
	echo "</tr>";
	echo "<tr class=\"type10\">";
	echo "<td class=\"centered\">" . LangNauticalNight . "</td>";
	for($i = 1; $i < 13; $i ++) {
		echo "<td class=\"centered\">" . ((date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] ) != "00:00") ? date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] + $theTimeDifference1 [$i] ) . "<br />-<br />" . date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_begin"] + $theTimeDifference1 [$i] ) : "-") . "</td>";
		echo "<td class=\"centered\">" . ((date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] ) != "00:00") ? date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] + $theTimeDifference15 [$i] ) . "<br />-<br />" . date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_begin"] + $theTimeDifference15 [$i] ) : "-") . "</td>";
	}
	echo "<td class=\"centered\">" . ((date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] ) != "00:00") ? date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] + $theTimeDifference1 [1] ) . "<br />-<br />" . date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_begin"] + $theTimeDifference1 [1] ) : "-") . "</td>";
	echo "</tr>";
	echo "<tr class=\"type20\">";
	echo "<td class=\"centered\">" . LangObjectRiseSet . "</td>";
	for($i = 1; $i < 13; $i ++) {
		$colorclass = "";
		if ($theEphemerides1 [$i] ['rise'] == '-') {
			if ((date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] ) != "00:00"))
				$colorclass = "ephemeridesgreen";
			else if ((date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] ) != "00:00"))
				$colorclass = "ephemeridesyellow";
		}
		if ((date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinutePeriodOverlap ( $theEphemerides1 [$i] ['rise'], $theEphemerides1 [$i] ['set'], date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_end"] + $theTimeDifference1 [$i] ), date ( "H:i", $theNightEphemerides1 [$i] ["astronomical_twilight_begin"] + $theTimeDifference1 [$i] ) ))
			$colorclass = "ephemeridesgreen";
		else if ((date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinutePeriodOverlap ( $theEphemerides1 [$i] ['rise'], $theEphemerides1 [$i] ['set'], date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_end"] + $theTimeDifference1 [$i] ), date ( "H:i", $theNightEphemerides1 [$i] ["nautical_twilight_begin"] + $theTimeDifference1 [$i] ) ))
			$colorclass = "ephemeridesyellow";
		echo "<td class=\"centered " . $colorclass . "\">" . ($theEphemerides1 [$i] ['rise'] == '-' ? "-" : $theEphemerides1 [$i] ['rise'] . "<br />-<br />" . $theEphemerides1 [$i] ['set']) . "</td>";
		$colorclass = "";
		if ($theEphemerides15 [$i] ['rise'] == '-') {
			if ((date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_end"] ) != "00:00"))
				$colorclass = "ephemeridesgreen";
			else if ((date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] ) != "00:00"))
				$colorclass = "ephemeridesyellow";
		} else if ((date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinutePeriodOverlap ( $theEphemerides15 [$i] ['rise'], $theEphemerides15 [$i] ['set'], date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_end"] + $theTimeDifference15 [$i] ), date ( "H:i", $theNightEphemerides15 [$i] ["astronomical_twilight_begin"] + $theTimeDifference15 [$i] ) ))
			$colorclass = "ephemeridesgreen";
		else if ((date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinutePeriodOverlap ( $theEphemerides15 [$i] ['rise'], $theEphemerides15 [$i] ['set'], date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_end"] + $theTimeDifference15 [$i] ), date ( "H:i", $theNightEphemerides15 [$i] ["nautical_twilight_begin"] + $theTimeDifference15 [$i] ) ))
			$colorclass = "ephemeridesyellow";
		echo "<td class=\"centered " . $colorclass . "\">" . ($theEphemerides15 [$i] ['rise'] == "-" ? "-" : $theEphemerides15 [$i] ['rise'] . "<br />-<br />" . $theEphemerides15 [$i] ['set']) . "</td>";
	}
	$colorclass = "";
	if ($theEphemerides1 [1] ['rise'] == '-') {
		if ((date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] ) != "00:00"))
			$colorclass = "ephemeridesgreen";
		else if ((date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] ) != "00:00"))
			$colorclass = "ephemeridesyellow";
	} else if ((date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinutePeriodOverlap ( $theEphemerides1 [1] ['rise'], $theEphemerides1 [1] ['set'], date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_end"] + $theTimeDifference1 [1] ), date ( "H:i", $theNightEphemerides1 [1] ["astronomical_twilight_begin"] + $theTimeDifference1 [1] ) ))
		$colorclass = "ephemeridesgreen";
	else if ((date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] ) != "00:00") && $objUtil->checkNightHourMinutePeriodOverlap ( $theEphemerides1 [1] ['rise'], $theEphemerides1 [1] ['set'], date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_end"] + $theTimeDifference1 [1] ), date ( "H:i", $theNightEphemerides1 [1] ["nautical_twilight_begin"] + $theTimeDifference1 [1] ) ))
		$colorclass = "ephemeridesyellow";
	echo "<td class=\"centered " . $colorclass . "\">" . ($theEphemerides1 [1] ['rise'] == '-' ? '-' : $theEphemerides1 [1] ['rise'] . "<br />-<br />" . $theEphemerides1 [1] ['set']) . "</td>";
	echo "</tr>";
	echo "</table>";
	echo "<hr />";
	echo "</div>";
}
function showObjectImage($imagesize) {
	global $object, $objPresentations, $objUtil;
	$objPresentations->line ( array (
			"<h4>" . LangViewDSSImageTitle . $object . "&nbsp;(" . $imagesize . "&#39;&nbsp;x&nbsp;" . $imagesize . "&#39;)</h4>" 
	), "L" );
	$imagelink = "http://archive.stsci.edu/cgi-bin/dss_search?" . "v=poss2ukstu_red&amp;r=" . urlencode ( $objUtil->checkRequestKey ( 'raDSS' ) ) . ".0&amp;d=" . urlencode ( $objUtil->checkRequestKey ( 'declDSS' ) ) . "&amp;e=J2000&amp;h=" . $imagesize . ".0&amp;w=" . $imagesize . "&amp;f=gif&amp;c=none&amp;fov=NONE&amp;v3=";
	echo "<p class=\"centered DSSImage\" > 
	       <a href=\"" . $imagelink . "\" data-lightbox=\"image-1\" data-title=\"\">
	       <img class=\"centered DSSImage\" src=\"" . $imagelink . "\" alt=\"" . $object . "\" ></img> 
	       </a></p>";
	echo "<p>&copy;&nbsp;<a href=\"http://archive.stsci.edu/dss/index.html\">STScI Digitized Sky Survey</a></p>";
	echo "<hr />";
}
function showObjectObservations() {
	global $baseURL, $FF, $object, $loggedUser, $min, $step, $objObservation, $objPresentations, $objUtil;
	
	if ((array_key_exists ( 'steps', $_SESSION )) && (array_key_exists ( "selObjObs" . $_SESSION ['lco'], $_SESSION ['steps'] )))
		$step = $_SESSION ['steps'] ["selObjObs" . $_SESSION ['lco']];
	if (array_key_exists ( 'viewObjectObservationsmultiplepagenr', $_GET ))
		$min = ($_GET ['viewObjectObservationsmultiplepagenr'] - 1) * $step;
	elseif (array_key_exists ( 'viewObjectObservationsmultiplepagenr', $_POST ))
		$min = ($_POST ['viewObjectObservationsmultiplepagenr'] - 1) * $step;
	elseif (array_key_exists ( 'minViewObjectObservations', $_SESSION ))
		$min = $_SESSION ['minViewObjectObservations'];
	else
		$min = 0;
	$_SESSION ['minViewObjectObservations'] = $min;
	$link = $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj';
	$link2 = $link;
	$link3 = $link;
	$content3 = "<h4>";
	$min = 0;
	$max = 0;
	$pageleft = '';
	$pageright = '';
	$pagemax = '';
	if (count ( $_SESSION ['Qobs'] ) == 0) {
		$objPresentations->line ( array (
				"<h4>" . LangObservationNoResults . (($objUtil->checkGetKey ( 'myLanguages' )) ? (" (" . LangSelectedObservationsSelectedLanguagesIndication . ")") : (" (" . LangSelectedObservationsAllLanguagesIndication . ")")) . "</h4>" 
		), "L", array (
				100 
		), 30 );
		if ($objUtil->checkGetKey ( 'myLanguages' ))
			echo "<p>" . "<a href=\"" . $link2 . "\">" . LangSearchAllLanguages . "</a>&nbsp;</p>";
		echo "<p>" . "<a href=\"" . $baseURL . "index.php?indexAction=query_observations\">" . LangSearchDetailPage . "</a>" . "</p>";
	} else {
		$theDate = date ( 'Ymd', strtotime ( '-1 year' ) );
		$content1 = "<h4>" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectobservations=hidden' . "\" title=\"" . ObjectObservationsHide . "\">-</a> ";
		if (array_key_exists ( 'minyear', $_GET ) && ($_GET ['minyear'] == substr ( $theDate, 0, 4 )) && array_key_exists ( 'minmonth', $_GET ) && ($_GET ['minmonth'] == substr ( $theDate, 4, 2 )) && array_key_exists ( 'minday', $_GET ) && ($_GET ['minday'] == substr ( $theDate, 6, 2 )))
			$content1 .= LangSelectedObservationsTitle3;
		elseif ($object)
			$content1 .= LangSelectedObservationsTitle . $object;
		else
			$content1 .= LangSelectedObservationsTitle2;
		$content1 .= "</h4>";
		list ( $min, $max, $content2, $pageleft, $pageright, $pagemax ) = $objUtil->printNewListHeader5 ( $_SESSION ['Qobs'], $link, $min, $step, $_SESSION ['QobsTotal'] );
		$objPresentations->line ( array (
				$content1,
				$content2 
		), "LR", array (
				50,
				50 
		), 30 );
	}
	if ($objUtil->checkGetKey ( 'myLanguages' )) {
		$content3 .= " (" . LangSelectedLanguagesShown . ")";
		$link .= "&amp;myLanguages=true";
		$link2 .= "&amp;myLanguages=true";
	} else
		$content3 .= " (" . LangAllLanguagesShown . ")";
	$content3 .= "</h4>";
	$content4 = $objUtil->printStepsPerPage3 ( $link, "selObjViewObs" . $_SESSION ['lco'], $step );
	$objPresentations->line ( array (
			$content3,
			$content4 
	), "LR", array (
			50,
			50 
	), 25 );
	$content5 = "";
	if (($objUtil->checkSessionKey ( 'lco', '' ) != "L"))
		$content5 .= "&nbsp;-&nbsp;<a href=\"" . $link . "&amp;lco=L" . "&amp;min=" . urlencode ( $min ) . "\" title=\"" . LangOverviewObservationTitle . "\">" . LangOverviewObservations . "</a>";
	if (($objUtil->checkSessionKey ( 'lco', '' ) != "C"))
		$content5 .= "&nbsp;-&nbsp;<a href=\"" . $link . "&amp;lco=C" . "&amp;min=" . urlencode ( $min ) . "\" title=\"" . LangCompactObservationsTitle . "\">" . LangCompactObservations . "</a>";
	if ($loggedUser && ($objUtil->checkSessionKey ( 'lco', '' ) != "O"))
		$content5 .= "&nbsp;-&nbsp;<a href=\"" . $link . "&amp;lco=O" . "&amp;min=" . urlencode ( $min ) . "\" title=\"" . LangCompactObservationsLOTitle . "\">" . LangCompactObservationsLO . "</a>";
	if ($loggedUser && (! ($objUtil->checkGetKey ( 'noOwnColor' ))) && (($objUtil->checkSessionKey ( 'lco', '' ) == "L")))
		$content5 .= "&nbsp;-&nbsp;" . "<a href=\"" . $link . "&amp;noOwnColor=yes\">" . LangNoOwnColor . "</a>";
	$content5 = substr ( $content5, 13 );
	if ($objUtil->checkGetKey ( 'myLanguages' ))
		$content6 = "<a href=\"" . $link3 . "\">" . LangShowAllLanguages . "</a>";
	elseif ($loggedUser)
		$content6 = "<a href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowMyLanguages . "</a>";
	else
		$content6 = "<a href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowInterfaceLanguage . "</a>";
	$objPresentations->line ( array (
			$content5,
			$content6 
	), "LR", array (
			50,
			50 
	), 20 );
	echo "<hr />";
	
	$_GET ['min'] = $min;
	$_GET ['max'] = $max;
	if (($FF) && ($_SESSION ['lco'] == "O")) {
		echo "<script type=\"text/javascript\">";
		echo "theResizeElement='obs_list';";
		echo "theResizeSize=150;";
		echo "</script>";
	} elseif (($FF)) {
		echo "<script type=\"text/javascript\">";
		echo "theResizeElement='obs_list';";
		echo "theResizeSize=150;";
		echo "</script>";
	}
	$objObservation->showListObservation ( $link . "&amp;min=" . $min, $link2, $_SESSION ['lco'], $step );
	echo "<hr />";
	if ($_SESSION ['lco'] == "O")
		$objPresentations->line ( array (
				LangOverviewObservationsHeader5a 
		), "R", array (
				100 
		), 25 );
	$content1 = "<a href=\"" . $baseURL . "index.php?indexAction=query_objects&amp;source=observation_query\">" . LangExecuteQueryObjectsMessage9 . "</a> - ";
	$content1 .= LangExecuteQueryObjectsMessage4 . "&nbsp;";
	$content1 .= $objPresentations->promptWithLinkText ( LangOverviewObservations10, LangOverviewObservations11, $baseURL . "observations.pdf.php?SID=Qobs", LangExecuteQueryObjectsMessage4a );
	$content1 .= " - ";
	$content1 .= "<a href=\"" . $baseURL . "observations.csv\" rel=\"external\">" . LangExecuteQueryObjectsMessage5 . "</a> - ";
	$content1 .= "<a href=\"" . $baseURL . "observations.xml\" rel=\"external\">" . LangExecuteQueryObjectsMessage10 . "</a>";
	$objPresentations->line ( array (
			$content1 
	), "L", array (
			100 
	), 25 );
	echo "<hr />";
	echo "<script type=\"text/javascript\">";
	echo "
  function pageOnKeyDown2(event)
  { if(event.keyCode==37)
      if(event.shiftKey)
        if(event.ctrlKey)
          location=html_entity_decode('" . $link . "&amp;viewObjectObservationsmultiplepagenr=0" . "');    
        else
          location=html_entity_decode('" . $link . "&amp;viewObjectObservationsmultiplepagenr=" . $pageleft . "');
    if(event.keyCode==39)
      if(event.shiftKey) 
        if(event.ctrlKey)
          location=html_entity_decode('" . $link . "&amp;viewObjectObservationsmultiplepagenr=" . $pagemax . "');
        else  
          location=html_entity_decode('" . $link . "&amp;viewObjectObservationsmultiplepagenr=" . $pageright . "');
  }
  this.onKeyDownFns[this.onKeyDownFns.length] = pageOnKeyDown2;
  ";
	echo "</script>";
}
function showAdminObjectFunctions() {
	global $baseURL, $object, $DSOcatalogs, $objObject;
	echo "<hr />";
	echo "<form action=\"" . $baseURL . "index.php\" method=\"get\"><div>";
	echo "<input type=\"hidden\" name=\"object\" value=\"" . $object . "\" />";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" />";
	echo "<select name=\"newaction\">";
	echo "<option value=\"\">&nbsp;</option>";
	echo "<option value=\"NewName\">" . LangObjectNewName . "</option>";
	echo "<option value=\"NewAltName\">" . LangObjectNewAltName . "</option>";
	echo "<option value=\"RemoveAltNameName\">" . LangObjectRemoveAltNameName . "</option>";
	echo "<option value=\"NewPartOf\">" . LangObjectNewPartOf . "</option>";
	echo "<option value=\"RemovePartOf\">" . LangObjectRemovePartOf . "</option>";
	echo "<option value=\"RemoveAndReplaceObjectBy\">" . LangObjectRemoveAndReplaceObjectBy . "</option>";
	echo "<option value=\"LangObjectSetRA\">" . LangObjectSetRA . "</option>";
	echo "<option value=\"LangObjectSetDECL\">" . LangObjectSetDECL . "</option>";
	echo "<option value=\"LangObjectSetCon\">" . LangObjectSetCon . "</option>";
	echo "<option value=\"LangObjectSetType\">" . LangObjectSetType . "</option>";
	echo "<option value=\"LangObjectSetMag\">" . LangObjectSetMag . "</option>";
	echo "<option value=\"LangObjectSetSUBR\">" . LangObjectSetSUBR . "</option>";
	echo "<option value=\"LangObjectSetDiam1\">" . LangObjectSetDiam1 . "</option>";
	echo "<option value=\"LangObjectSetDiam2\">" . LangObjectSetDiam2 . "</option>";
	echo "<option value=\"LangObjectSetPA\">" . LangObjectSetPA . "</option>";
	echo "<option value=\"LangObjectSetDESC\">" . LangEditObjectDescription . "</option>";
	echo "</select>";
	echo "<select name=\"newcatalog\">";
	echo "<option value=\"\">&nbsp;</option>";
	while ( list ( $key, $value ) = each ( $DSOcatalogs ) )
		echo "<option value=\"$value\">" . $value . "</option>";
	echo "</select>";
	echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"newnumber\" size=\"40\" value=\"\"/>";
	echo "<input type=\"submit\" name=\"gonew\" value=\"Go\"/><br />";
	echo "<a href=\"" . $baseURL . "index.php?indexAction=manage_csv_object\">" . LangNewObjectSubtitle1b . "</a><br />";
	echo "</div></form>";
}
function view_object() {
	global $baseURL, $FF, $link, $link2, $loggedUser, $min, $object, $step, $objLocation, $objObject, $objObservation, $objObserver, $objPresentations, $objUtil;
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/presentation.js\"></script>";
	if (array_key_exists ( 'viewobjectextrainfo', $_GET ))
		$viewobjectextrainfo = $_GET ['viewobjectextrainfo'];
	elseif (array_key_exists ( 'viewobjectextrainfo', $_COOKIE ))
		$viewobjectextrainfo = $_COOKIE ['viewobjectextrainfo'];
	else
		$viewobjectextrainfo = 'hidden';
	if (array_key_exists ( 'viewobjectdetails', $_GET ))
		$viewobjectdetails = $_GET ['viewobjectdetails'];
	elseif (array_key_exists ( 'viewobjectdetails', $_COOKIE ))
		$viewobjectdetails = $_COOKIE ['viewobjectdetails'];
	else
		$viewobjectdetails = 'show';
	if (array_key_exists ( 'viewobjectephemerides', $_GET ))
		$viewobjectephemerides = $_GET ['viewobjectephemerides'];
	elseif (array_key_exists ( 'viewobjectephemerides', $_COOKIE ))
		$viewobjectephemerides = $_COOKIE ['viewobjectephemerides'];
	else
		$viewobjectephemerides = 'hidden';
	if (array_key_exists ( 'viewobjectobjectsnearby', $_GET ))
		$viewobjectobjectsnearby = $_GET ['viewobjectobjectsnearby'];
	elseif (array_key_exists ( 'viewobjectobjectsnearby', $_COOKIE ))
		$viewobjectobjectsnearby = $_COOKIE ['viewobjectobjectsnearby'];
	else
		$viewobjectobjectsnearby = 'show';
	if (array_key_exists ( 'viewobjectobservations', $_GET ))
		$viewobjectobservations = $_GET ['viewobjectobservations'];
	elseif (array_key_exists ( 'viewobjectobservations', $_COOKIE ))
		$viewobjectobservations = $_COOKIE ['viewobjectobservations'];
	else
		$viewobjectobservations = 'hidden';
	if (! ($theLocation = ($loggedUser ? $objObserver->getObserverProperty ( $loggedUser, 'stdLocation' ) : '')))
		$viewobjectephemerides = 'hidden';
	
	echo "<div id=\"main\">";
	if ($viewobjectextrainfo == "show") {
		showButtons ( $theLocation, $viewobjectdetails, $viewobjectephemerides, $viewobjectobjectsnearby, $viewobjectobservations );
		echo $objPresentations->getDSSDeepskyLiveLinks1 ( $object );
		echo $objPresentations->getDSSDeepskyLiveLinks2 ( $object ); 
		echo "<hr />";
	}
	if ($viewobjectextrainfo == "hidden") {
		$content = "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $_GET ['object'] ) . '&amp;zoom=' . $objUtil->checkGetKey ( "zoom", 30 ) . '&amp;SID=Qobj&amp;viewobjectextrainfo=show' . "\" >+&nbsp;" . LangObjectShowExtraInfo . "</a>";
		$objPresentations->line ( array (
				$content,
				$objPresentations->getDSSDeepskyLiveLinks1 ( $object ),
				$objPresentations->getDSSDeepskyLiveLinks2 ( $object ) 
		), "LRR", array (
				20,
				35,
				45 
		), 15 );
		echo "<hr />";
	}
	if ($viewobjectdetails == "show")
		showObjectDetails ( stripslashes ( $object ) );
	if ($viewobjectephemerides == "show")
		showObjectEphemerides ( $theLocation );
	if ($viewobjectobjectsnearby == "show")
		showObjectsNearby ();
	if ($imagesize = $objUtil->checkRequestKey ( 'imagesize' ))
		showObjectImage ( $imagesize );
	if ($viewobjectobservations == "show")
		showObjectObservations ();
	if (array_key_exists ( 'admin', $_SESSION ) && $_SESSION ['admin'] == "yes")
		showAdminObjectFunctions ();
	echo "</div>";
}
?>
	