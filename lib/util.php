<?php
// util.php
// several handy functions
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Utils {
	public function hourminuteTimeToValue($thetime) {
		if ($thetime == "-")
			return - 1;
		if ($thetime == ":")
			return - 1;
		if (($thepos = strpos ( $thetime, ":" )) === false)
			return - 1;
		if (strpos ( $thetime, "(" ) === "0")
			$thetime = substr ( $thetime, 1 );
		if (! (is_numeric ( $thehour = substr ( $thetime, 0, $thepos ) )))
			return - 1;
		if (! (is_numeric ( $theminute = substr ( $thetime, $thepos + 1, 2 ) )))
			return - 1;
		return 1 * (($thehour * 100) + $theminute);
	}
	public function checkNightHourMinuteBetweenOthers($thehour, $firsthour, $lasthour) {
		$thehourvalue = $this->hourminuteTimeToValue ( $thehour );
		$thefirstvalue = $this->hourminuteTimeToValue ( $firsthour );
		$thelastvalue = $this->hourminuteTimeToValue ( $lasthour );
		if ($thehourvalue < 1200) {
			if ($thelastvalue > 1200)
				return false;
			if ($thelastvalue < $thehourvalue)
				return false;
			if ($thefirstvalue > 1200)
				return true;
			if ($thefirstvalue > $thehourvalue)
				return false;
			return true;
		} else {
			if ($thefirstvalue < 1200)
				return false;
			if ($thefirstvalue > $thehourvalue)
				return false;
			if ($thelastvalue < 1200)
				return true;
			if ($thelastvalue < $thehourvalue)
				return false;
			return true;
		}
	}
	public function checkNightHourMinutePeriodOverlap($firststart, $firstend, $secondstart, $secondend) {
		$firststartvalue = $this->hourminuteTimeToValue ( $firststart );
		$firstendvalue = $this->hourminuteTimeToValue ( $firstend );
		$secondstartvalue = $this->hourminuteTimeToValue ( $secondstart );
		$secondendvalue = $this->hourminuteTimeToValue ( $secondend );
		if ($secondstartvalue < $secondendvalue)
			return ((($firststartvalue > $secondstartvalue) && ($firststartvalue < $secondendvalue)) || (($firstendvalue > $secondstartvalue) && ($firstendvalue < $secondendvalue)) || (($firststartvalue < $secondend) && ($firstendvalue > $secondendvalue)) || (($firststartvalue < $secondstartvalue) && ($firststartvalue > $firstendvalue)) || (($firstendvalue > $secondendvalue) && ($firststartvalue > $firstendvalue)));
		else
			return ($firststartvalue > $secondstartvalue) || ($firststartvalue < $secondendvalue) || ($firstendvalue > $secondstartvalue) || ($firstendvalue < $secondendvalue) || (($firststartvalue < $secondstartvalue) && ($firstendvalue > $secondendvalue) && ($firststartvalue > $firstendvalue));
	}
	public function __construct() {
		foreach ( $_POST as $foo => $bar ) {
			if (! is_array ( $_POST [$foo] )) {
				$_POST [$foo] = htmlentities ( stripslashes ( $bar ), ENT_COMPAT, "UTF-8", 0 );
			}
		}
		foreach ( $_GET as $foo => $bar ) {
			$_GET [$foo] = htmlentities ( stripslashes ( $bar ), ENT_COMPAT, "UTF-8", 0 );
		}
	}
	public function argoObjects($result) // Creates an argo navis file from an array of objects
{
		global $objObserver, $loggedUser, $objPresentations, $objAtlas;
		$result = $this->sortResult ( $result );

		while ( list ( $key, $valueA ) = each ( $result ) ) {
			echo "DSL " . $valueA ['objectname'] . "|" . $objPresentations->raArgoToString ( $valueA ['objectra'] ) . "|" . $objPresentations->decToArgoString ( $valueA ['objectdecl'], 0 ) . "|" . $GLOBALS ["argo" . $valueA ['objecttype']] . "|" . $objPresentations->presentationInt ( $valueA ['objectmagnitude'], 99.9, '' ) . "|" . $valueA ['objectsize'] . ";" . $objAtlas->atlasCodes [($atlas = $objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' ))] . " " . $valueA [$atlas] . ";" . "CR " . $valueA ['objectcontrast'] . ";" . $valueA ['objectseen'] . ";" . $valueA ['objectlastseen'] . "\n";
		}
	}
	public function checkAdminOrUserID($toCheck) {
		global $loggedUser;
		return ((array_key_exists ( 'admin', $_SESSION ) && ($_SESSION ['admin'] == "yes")) || ($loggedUser == $toCheck));
	}
	public function checkArrayKey($theArray, $key, $default = '') {
		return (array_key_exists ( $key, $theArray ) && ($theArray [$key] != '')) ? $theArray [$key] : $default;
	}
	public function checkGetDate($year, $month, $day) {
		if ($year = $this->checkGetKey ( $year ))
			return sprintf ( "%04d", $year ) . sprintf ( "%02d", $this->checkGetKey ( $month, '00' ) ) . sprintf ( "%02d", $this->checkGetKey ( $day, '00' ) );
		elseif ($month = $this->checkGetKey ( $month ))
			return sprintf ( "%02d", $month ) . sprintf ( "%02d", $this->checkGetKey ( $day, '00' ) );
		return '';
	}
	public function getLocalizedDate($date) {
		global $dateformat;
		$date = sscanf ( $date, "%4d%2d%2d" );
		$dateTimeText = date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );

		return $dateTimeText;
	}
	public function checkGetKey($key, $default = '') {
		return (array_key_exists ( $key, $_GET ) && ($_GET [$key] != '')) ? $_GET [$key] : $default;
	}
	public function checkGetKeyReturnString($key, $string, $default = '') {
		return array_key_exists ( $key, $_GET ) ? $string : $default;
	}
	public function checkGetTimeOrDegrees($hr, $min, $sec) {
		if ($this->checkGetKey ( $hr ) . $this->checkGetKey ( $min ) . $this->checkGetKey ( $sec )) {
			if (substr ( $this->checkGetKey ( $hr ), 0, 1 ) == "-")
				return - (abs ( $this->checkGetKey ( $hr, 0 ) ) + ($this->checkGetKey ( $min, 0 ) / 60) + ($this->checkGetKey ( $sec, 0 ) / 3600));
			else
				return $this->checkGetKey ( $hr, 0 ) + ($this->checkGetKey ( $min, 0 ) / 60) + ($this->checkGetKey ( $sec, 0 ) / 3600);
		} else
			return '';
	}
	public function checkLimitsInclusive($value, $low, $high) {
		return (($value >= $low) && ($value <= $high));
	}
	public function checkPostKey($key, $default = '') {
		return (array_key_exists ( $key, $_POST ) && ($_POST [$key] != '')) ? $_POST [$key] : $default;
	}
	public function checkRequestKey($key, $default = '') {
		return ((array_key_exists ( $key, $_REQUEST ) && ($_REQUEST [$key] != '')) ? $_REQUEST [$key] : ((array_key_exists ( $key, $_POST ) && ($_POST [$key] != '')) ? $_POST [$key] : ((array_key_exists ( $key, $_GET ) && ($_GET [$key] != '')) ? $_GET [$key] : $default)));
	}
	public function checkSessionKey($key, $default = '') {
		return (array_key_exists ( $key, $_SESSION ) && ($_SESSION [$key] != '')) ? $_SESSION [$key] : $default;
	}
	public function checkUserID($toCheck) {
		global $loggedUser;
		return ($loggedUser == $toCheck);
	}
	public function comastObservations($result) // Creates a oal file from an array of observations
{
		global $objPresentations, $objObservation, $objCatalog, $objSession, $loggedUser, $objDatabase;
		include_once "cometobjects.php";
		include_once "observers.php";
		include_once "instruments.php";
		include_once "locations.php";
		include_once "lenses.php";
		include_once "filters.php";
		include_once "cometobservations.php";
		include_once "icqmethod.php";
		include_once "icqreferencekey.php";
		include_once "catalogs.php";
		include_once "setup/vars.php";
		include_once "setup/databaseInfo.php";

		$observer = $GLOBALS ['objObserver'];
		$location = $GLOBALS ['objLocation'];

		$dom = new DomDocument ( '1.0', 'ISO-8859-1' );

		$observers = array ();
		$sites = array ();
		$objects = array ();
		$scopes = array ();
		$eyepieces = array ();
		$lenses = array ();
		$filters = array ();

		$cntObservers = 0;
		$cntSites = 0;
		$cntObjects = 0;
		$cntScopes = 0;
		$cntEyepieces = 0;
		$cntLens = 0;
		$cntFilter = 0;

		$allObs = $result;

		while ( list ( $key, $value ) = each ( $result ) ) {
			$obs = $objObservation->getAllInfoDsObservation ( $value ['observationid'] );
			$objectname = $obs ['objectname'];
			$observerid = $obs ['observerid'];
			$inst = $obs ['instrumentid'];
			$loc = $obs ['locationid'];
			$visibility = $obs ['visibility'];
			$seeing = $obs ['seeing'];
			$limmag = $obs ['limmag'];
			$filt = $obs ['filterid'];
			$eyep = $obs ['eyepieceid'];
			$lns = $obs ['lensid'];

			if (in_array ( $observerid, $observers ) == false) {
				$observers [$cntObservers] = $observerid;
				$cntObservers = $cntObservers + 1;
			}

			if (in_array ( $loc, $sites ) == false) {
				$sites [$cntSites] = $loc;
				$cntSites = $cntSites + 1;
			}

			if (in_array ( $objectname, $objects ) == false) {
				$objects [$cntObjects] = $objectname;
				$cntObjects = $cntObjects + 1;
			}

			if (in_array ( $inst, $scopes ) == false) {
				$scopes [$cntScopes] = $inst;
				$cntScopes = $cntScopes + 1;
			}

			if (in_array ( $eyep, $eyepieces ) == false) {
				$eyepieces [$cntEyepieces] = $eyep;
				$cntEyepieces = $cntEyepieces + 1;
			}

			if (in_array ( $lns, $lenses ) == false) {
				$lenses [$cntLens] = $lns;
				$cntLens = $cntLens + 1;
			}

			if (in_array ( $filt, $filters ) == false) {
				$filters [$cntFilter] = $filt;
				$cntFilter = $cntFilter + 1;
			}
		}

		// add root fcga -> The header
		$fcgaInfo = $dom->createElement ( 'oal:observations' );
		$fcgaDom = $dom->appendChild ( $fcgaInfo );

		$attr = $dom->createAttribute ( "version" );
		$fcgaInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "2.0" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "xmlns:oal" );
		$fcgaInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "http://groups.google.com/group/openastronomylog" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "xmlns:xsi" );
		$fcgaInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "http://www.w3.org/2001/XMLSchema-instance" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "xsi:schemaLocation" );
		$fcgaInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "http://groups.google.com/group/openastronomylog oal21.xsd" );
		$attr->appendChild ( $attrText );

		// add root - <observers>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'observers' ) );

		while ( list ( $key, $value ) = each ( $observers ) ) {
			$observer2 = $dom->createElement ( 'observer' );
			$observerChild = $observersDom->appendChild ( $observer2 );
			$attr = $dom->createAttribute ( "id" );
			$observer2->appendChild ( $attr );

			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\s+/", "_", $value ) ) );
			$attrText = $dom->createTextNode ( "usr_" . $correctedValue );
			$attr->appendChild ( $attrText );

			$name = $observerChild->appendChild ( $dom->createElement ( 'name' ) );
			$name->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $observer->getObserverProperty ( $value, 'firstname' ) ) ) ) );

			$surname = $observerChild->appendChild ( $dom->createElement ( 'surname' ) );
			$surname->appendChild ( $dom->createCDataSection ( ($observer->getObserverProperty ( $value, 'name' )) ) );

			$account = $observerChild->appendChild ( $dom->createElement ( 'account' ) );
			$account->appendChild ( $dom->createCDataSection ( utf8_encode ( html_entity_decode ( $value ) ) ) );

			$attr = $dom->createAttribute ( "name" );
			$account->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "www.deepskylog.org" );
			$attr->appendChild ( $attrText );

			if ($observer->getObserverProperty ( $value, 'fstOffset' ) != 0.0) {
				$fst = $observerChild->appendChild ( $dom->createElement ( 'fstOffset' ) );
				$fst->appendChild ( $dom->createTextNode ( ($observer->getObserverProperty ( $value, 'fstOffset' )) ) );
			}
		}

		// add root - <sites>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'sites' ) );

		while ( list ( $key, $value ) = each ( $sites ) ) {
			$site2 = $dom->createElement ( 'site' );
			$siteChild = $observersDom->appendChild ( $site2 );
			$attr = $dom->createAttribute ( "id" );
			$site2->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "site_" . $value );
			$attr->appendChild ( $attrText );

			$name = $siteChild->appendChild ( $dom->createElement ( 'name' ) );
			$name->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $location->getLocationPropertyFromId ( $value, 'name' ) ) ) ) );

			$longitude = $siteChild->appendChild ( $dom->createElement ( 'longitude' ) );
			$longitude->appendChild ( $dom->createTextNode ( $location->getLocationPropertyFromId ( $value, 'longitude' ) ) );

			$attr = $dom->createAttribute ( "unit" );
			$longitude->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "deg" );
			$attr->appendChild ( $attrText );

			$latitude = $siteChild->appendChild ( $dom->createElement ( 'latitude' ) );
			$latitude->appendChild ( $dom->createTextNode ( $location->getLocationPropertyFromId ( $value, 'latitude' ) ) );

			$attr = $dom->createAttribute ( "unit" );
			$latitude->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "deg" );
			$attr->appendChild ( $attrText );

			// ELEVATION
			$elevation = $siteChild->appendChild ( $dom->createElement ( 'elevation' ) );
			$elevation->appendChild ( $dom->createTextNode ( $location->getLocationPropertyFromId ( $value, 'elevation' ) ) );

			$timezone = $siteChild->appendChild ( $dom->createElement ( 'timezone' ) );
			$dateTimeZone = new DateTimeZone ( $location->getLocationPropertyFromId ( $value, 'timezone' ) );
			$datestr = "01/01/2008";
			$dateTime = new DateTime ( $datestr, $dateTimeZone );
			// Geeft tijdsverschil terug in seconden
			$timedifference = $dateTimeZone->getOffset ( $dateTime );
			$timedifference = $timedifference / 60.0;

			if (strncmp ( $location->getLocationPropertyFromId ( $value, 'timezone' ), "Etc/GMT", 7 ) == 0) {
				$timedifference = - $timedifference;
			}

			$timezone->appendChild ( $dom->createTextNode ( $timedifference ) );
		}

		// add root - <sessions> We export all the sessions of the logged observer
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'sessions' ) );

		if ($loggedUser != "") {
			$sessions = $objSession->getAllSessionsForUser ( $loggedUser );

			$usedSessions = Array ();
			// Only add session for which the location is also exported
			for($scnt = 0; $scnt < count ( $sessions ); $scnt ++) {
				if (in_array ( $sessions [$scnt] ['locationid'], $sites )) {
					$session = $dom->createElement ( 'session' );
					$sessionChild = $observersDom->appendChild ( $session );
					$attr = $dom->createAttribute ( "id" );
					$session->appendChild ( $attr );

					$attrText = $dom->createTextNode ( "se_" . $sessions [$scnt] ['id'] );
					$attr->appendChild ( $attrText );

					$attr = $dom->createAttribute ( "lang" );
					$session->appendChild ( $attr );

					$attrText = $dom->createTextNode ( $sessions [$scnt] ['language'] );
					$attr->appendChild ( $attrText );

					$begin = $sessionChild->appendChild ( $dom->createElement ( 'begin' ) );
					$begindate = $sessions [$scnt] ['begindate'];
					$begindate = str_replace ( " ", "T", $begindate ) . "+00:00";
					$begin->appendChild ( $dom->createTextNode ( $begindate ) );

					$end = $sessionChild->appendChild ( $dom->createElement ( 'end' ) );
					$enddate = $sessions [$scnt] ['enddate'];
					$enddate = str_replace ( " ", "T", $enddate ) . "+00:00";
					$end->appendChild ( $dom->createTextNode ( $enddate ) );

					$site = $sessionChild->appendChild ( $dom->createElement ( 'site' ) );
					$site->appendChild ( $dom->createTextNode ( "site_" . $sessions [$scnt] ['locationid'] ) );

					$weather = $sessionChild->appendChild ( $dom->createElement ( 'weather' ) );
					$weather->appendChild ( $dom->createCDATASection ( $sessions [$scnt] ['weather'] ) );

					$equipment = $sessionChild->appendChild ( $dom->createElement ( 'equipment' ) );
					$equipment->appendChild ( $dom->createCDATASection ( $sessions [$scnt] ['equipment'] ) );

					$comments = $sessionChild->appendChild ( $dom->createElement ( 'comments' ) );
					$comments->appendChild ( $dom->createCDATASection ( $sessions [$scnt] ['comments'] ) );

					// TODO : Also add images of the session to the export
					$usedSessions [] = $sessions [$scnt] ['id'];
				}
			}
		}

		// add root - <targets>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'targets' ) );

		while ( list ( $key, $value ) = each ( $objects ) ) {
			$object2 = $dom->createElement ( 'target' );
			$objectChild = $observersDom->appendChild ( $object2 );
			$attr = $dom->createAttribute ( "id" );
			$object2->appendChild ( $attr );

			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\s+/", "_", $value ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\+/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\//", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\,/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\(/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\)/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/ /", "_", $correctedValue ) ) );

			$attrText = $dom->createTextNode ( "_" . $correctedValue );
			$attr->appendChild ( $attrText );

			$attr = $dom->createAttribute ( "xsi:type" );
			$object2->appendChild ( $attr );

			$object = $GLOBALS ['objObject']->getAllInfoDsObject ( $value );

			$type = $object ["type"];
			if ($type == "OPNCL" || $type == "SMCOC" || $type == "LMCOC") {
				$type = "oal:deepSkyOC";
			} else if ($type == "GALXY") {
				$type = "oal:deepSkyGX";
			} else if ($type == "GALCL") {
				$type = "oal:deepSkyCG";
			} else if ($type == "PLNNB") {
				$type = "oal:deepSkyPN";
			} else if ($type == "ASTER" || $type == "AA1STAR" || $type == "AA3STAR" || $type == "AA4STAR" || $type == "AA8STAR") {
				$type = "oal:deepSkyAS";
			} else if ($type == "AA2STAR" || $type == "DS") {
				$type = "oal:deepSkyDS";
			} else if ($type == "GLOCL" || $type == "GXAGC" || $type == "LMCGC" || $type == "SMCGC") {
				$type = "oal:deepSkyGC";
			} else if ($type == "BRTNB" || $type == "CLANB" || $type == "EMINB" || $type == "ENRNN" || $type == "ENSTR" || $type == "GXADN" || $type == "GACAN" || $type == "HII" || $type == "LMCCN" || $type == "LMCDN" || $type == "REFNB" || $type == "RNHII" || $type == "SMCCN" || $type == "SMCDN" || $type == "SNREM" || $type == "STNEB" || $type == "WRNEB") {
				$type = "oal:deepSkyGN";
			} else if ($type == "QUASR") {
				$type = "oal:deepSkyQS";
			} else if ($type == "DRKNB") {
				$type = "oal:deepSkyDN";
			} else if ($type == "NONEX") {
				$type = "oal:deepSkyNA";
			}
			$attrText = $dom->createTextNode ( $type );
			$attr->appendChild ( $attrText );

			$datasource = $objectChild->appendChild ( $dom->createElement ( 'datasource' ) );
			$datasource->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $object ["datasource"] ) ) ) );

			$name = $objectChild->appendChild ( $dom->createElement ( 'name' ) );
			$name->appendChild ( $dom->createCDATASection ( ($objCatalog->checkObject ( $value )) ) );

			$altnames = $GLOBALS ['objObject']->getAlternativeNames ( $value );
			while ( list ( $key2, $value2 ) = each ( $altnames ) ) // go through names array
{
				if (trim ( $value2 ) != trim ( $value )) {
					if (trim ( $value2 ) != "") {
						$alias = $objectChild->appendChild ( $dom->createElement ( 'alias' ) );
						$alias->appendChild ( $dom->createCDataSection ( (trim ( $objCatalog->checkObject ( $value2 ) )) ) );
					}
				}
			}

			$position = $objectChild->appendChild ( $dom->createElement ( 'position' ) );

			$raDom = $dom->createElement ( 'ra' );
			$ra = $position->appendChild ( $raDom );
			$ra->appendChild ( $dom->createTextNode ( $object ["ra"] * 15.0 ) );

			$attr = $dom->createAttribute ( "unit" );
			$raDom->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "deg" );
			$attr->appendChild ( $attrText );

			$decDom = $dom->createElement ( 'dec' );
			$dec = $position->appendChild ( $decDom );
			$dec->appendChild ( $dom->createTextNode ( $object ["decl"] ) );

			$attr = $dom->createAttribute ( "unit" );
			$decDom->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "deg" );
			$attr->appendChild ( $attrText );

			$constellation = $objectChild->appendChild ( $dom->createElement ( 'constellation' ) );
			$constellation->appendChild ( $dom->createCDATASection ( ($object ["con"]) ) );

			if ($object ["diam2"] > 0.0 && $object ["diam2"] != 99.9) {
				$sdDom = $dom->createElement ( 'smallDiameter' );
				$diam2 = $objectChild->appendChild ( $sdDom );
				$sDiameter = $object ["diam2"] / 60.0;
				$diam2->appendChild ( $dom->createTextNode ( $sDiameter ) );

				$attr = $dom->createAttribute ( "unit" );
				$sdDom->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "arcmin" );
				$attr->appendChild ( $attrText );
			}

			$diameter1 = $object ["diam1"];
			if ($diameter1 > 0.0 && $diameter1 != 99.9) {
				$ldDom = $dom->createElement ( 'largeDiameter' );
				$diam1 = $objectChild->appendChild ( $ldDom );
				$lDiameter = $diameter1 / 60.0;
				$diam1->appendChild ( $dom->createTextNode ( $lDiameter ) );

				$attr = $dom->createAttribute ( "unit" );
				$ldDom->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "arcmin" );
				$attr->appendChild ( $attrText );
			}

			if ($object ["mag"] < 99.0) {
				$mag = $objectChild->appendChild ( $dom->createElement ( 'visMag' ) );
				$mag->appendChild ( $dom->createTextNode ( ($object ["mag"]) ) );
			}

			if ($object ["subr"] < 99.0) {
				$mag = $objectChild->appendChild ( $dom->createElement ( 'surfBr' ) );
				$mag->appendChild ( $dom->createTextNode ( ($object ["subr"]) ) );

				$attr = $dom->createAttribute ( "unit" );
				$mag->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "mags-per-squarearcmin" );
				$attr->appendChild ( $attrText );
			}

			if ($type != "oal:deepSkyCG" && $type != "oal:deepSkyGC" && $type != "oal:deepSkyNA" && $type != "oal:deepSkyOC" && $type != "oal:deepSkyPN" && $type != "oal:deepSkyQS") {
				if ($object ["pa"] < 999.0) {
					$pa = $objectChild->appendChild ( $dom->createElement ( 'pa' ) );
					$pa->appendChild ( $dom->createTextNode ( ($object ["pa"]) ) );
				}
			}
		}
		// add root - <scopes>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'scopes' ) );

		while ( list ( $key, $value ) = each ( $scopes ) ) {
			if ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'fixedMagnification' ) != 1) {
				if ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'name' ) != "") {
					$scope2 = $dom->createElement ( 'scope' );
					$siteChild = $observersDom->appendChild ( $scope2 );
					$attr = $dom->createAttribute ( "id" );
					$scope2->appendChild ( $attr );

					$attrText = $dom->createTextNode ( "opt_" . $value );
					$attr->appendChild ( $attrText );

					$attr = $dom->createAttribute ( "xsi:type" );
					$scope2->appendChild ( $attr );

					if ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'fixedMagnification' ) > 0) {
						$typeLong = "oal:fixedMagnificationOpticsType";
					} else {
						$typeLong = "oal:scopeType";
					}
					$tp = $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'type' );
					if ($tp == InstrumentOther || $tp == InstrumentRest) {
						$typeShort = "";
					} else if ($tp == InstrumentNakedEye) {
						$typeShort = "A";
					} else if ($tp == InstrumentBinoculars || $tp == InstrumentFinderscope) {
						$typeShort = "B";
					} else if ($tp == InstrumentRefractor) {
						$typeShort = "R";
					} else if ($tp == InstrumentReflector) {
						$typeShort = "N";
					} else if ($tp == InstrumentCassegrain) {
						$typeShort = "C";
					} else if ($tp == InstrumentKutter) {
						$typeShort = "K";
					} else if ($tp == InstrumentMaksutov) {
						$typeShort = "M";
					} else if ($tp == InstrumentSchmidtCassegrain) {
						$typeShort = "S";
					}

					if ($typeShort == "B") {
						$typeLong = "oal:fixedMagnificationOpticsType";
					}
					$attrText = $dom->createTextNode ( $typeLong );
					$attr->appendChild ( $attrText );

					$name = $siteChild->appendChild ( $dom->createElement ( 'model' ) );
					$name->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'name' ) ) ) ) );

					$type = $siteChild->appendChild ( $dom->createElement ( 'type' ) );
					$type->appendChild ( $dom->createCDATASection ( ($typeShort) ) );

					$aperture = $siteChild->appendChild ( $dom->createElement ( 'aperture' ) );
					$aperture->appendChild ( $dom->createTextNode ( ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'diameter' )) ) );

					if ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'fixedMagnification' ) > 0) {
						$magnification = $siteChild->appendChild ( $dom->createElement ( 'magnification' ) );
						$magnification->appendChild ( $dom->createTextNode ( ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'fixedMagnification' )) ) );
					} else {
						if ($typeShort == "B") {
							$magnification = $siteChild->appendChild ( $dom->createElement ( 'magnification' ) );
							$magnification->appendChild ( $dom->createTextNode ( "1" ) );
						} else {
							$focalLength = $siteChild->appendChild ( $dom->createElement ( 'focalLength' ) );
							$focalLength->appendChild ( $dom->createTextNode ( ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'fd' )) * $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $value, 'diameter' ) ) );
						}
					}
				}
			}
		}

		// add root - <eyepieces>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'eyepieces' ) );

		while ( list ( $key, $value ) = each ( $eyepieces ) ) {
			if ($value != "" && $value > 0) {
				$eyepiece2 = $dom->createElement ( 'eyepiece' );
				$eyepieceChild = $observersDom->appendChild ( $eyepiece2 );
				$attr = $dom->createAttribute ( "id" );
				$eyepiece2->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "ep_" . $value );
				$attr->appendChild ( $attrText );

				$model = $eyepieceChild->appendChild ( $dom->createElement ( 'model' ) );
				$model->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $GLOBALS ['objEyepiece']->getEyepiecePropertyFromId ( $value, 'name' ) ) ) ) );

				$focalLength = $eyepieceChild->appendChild ( $dom->createElement ( 'focalLength' ) );
				$focalLength->appendChild ( $dom->createTextNode ( ($GLOBALS ['objEyepiece']->getEyepiecePropertyFromId ( $value, 'focalLength' )) ) );

				if ($GLOBALS ['objEyepiece']->getEyepiecePropertyFromId ( $value, 'maxFocalLength' ) > 0) {
					$maxFocalLength = $eyepieceChild->appendChild ( $dom->createElement ( 'maxFocalLength' ) );
					$maxFocalLength->appendChild ( $dom->createTextNode ( ($GLOBALS ['objEyepiece']->getEyepiecePropertyFromId ( $value, 'maxFocalLength' )) ) );
				}

				$apparentFOV = $eyepieceChild->appendChild ( $dom->createElement ( 'apparentFOV' ) );
				$apparentFOV->appendChild ( $dom->createTextNode ( ($GLOBALS ['objEyepiece']->getEyepiecePropertyFromId ( $value, 'apparentFOV' )) ) );

				$attr = $dom->createAttribute ( "unit" );
				$apparentFOV->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "deg" );
				$attr->appendChild ( $attrText );
			}
		}

		// add root - <lenses>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'lenses' ) );

		while ( list ( $key, $value ) = each ( $lenses ) ) {
			if ($value != "" && $value > 0) {
				$lens2 = $dom->createElement ( 'lens' );
				$lensChild = $observersDom->appendChild ( $lens2 );
				$attr = $dom->createAttribute ( "id" );
				$lens2->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "le_" . $value );
				$attr->appendChild ( $attrText );

				$model = $lensChild->appendChild ( $dom->createElement ( 'model' ) );
				$model->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $GLOBALS ['objLens']->getLensPropertyFromId ( $value, 'name' ) ) ) ) );

				$factor = $lensChild->appendChild ( $dom->createElement ( 'factor' ) );
				$factor->appendChild ( $dom->createTextNode ( ($GLOBALS ['objLens']->getLensPropertyFromId ( $value, 'factor' )) ) );
			}
		}

		// add root - <filters>
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'filters' ) );

		while ( list ( $key, $value ) = each ( $filters ) ) {
			if ($value != "" && $value > 0) {
				$filter2 = $dom->createElement ( 'filter' );
				$filterChild = $observersDom->appendChild ( $filter2 );
				$attr = $dom->createAttribute ( "id" );
				$filter2->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "flt_" . $value );
				$attr->appendChild ( $attrText );

				$model = $filterChild->appendChild ( $dom->createElement ( 'model' ) );
				$model->appendChild ( $dom->createCDATASection ( utf8_encode ( html_entity_decode ( $GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'name' ) ) ) ) );

				$tp = $GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'type' );
				if ($tp == 0) {
					$filType = "other";
				} else if ($tp == 1) {
					$filType = "broad band";
				} else if ($tp == 2) {
					$filType = "narrow band";
				} else if ($tp == 3) {
					$filType = "O-III";
				} else if ($tp == 4) {
					$filType = "H-beta";
				} else if ($tp == 5) {
					$filType = "H-alpha";
				} else if ($tp == 6) {
					$filType = "color";
				} else if ($tp == 7) {
					$filType = "neutral";
				} else if ($tp == 8) {
					$filType = "corrective";
				}

				$type = $filterChild->appendChild ( $dom->createElement ( 'type' ) );
				$type->appendChild ( $dom->createCDATASection ( $filType ) );

				if ($filType == "color") {
					$col = $GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'color' );
					if ($col == 1) {
						$colName = "light red";
					} else if ($col == 2) {
						$colName = "red";
					} else if ($col == 3) {
						$colName = "deep red";
					} else if ($col == 4) {
						$colName = "orange";
					} else if ($col == 5) {
						$colName = "light yellow";
					} else if ($col == 6) {
						$colName = "deep yellow";
					} else if ($col == 7) {
						$colName = "yellow";
					} else if ($col == 8) {
						$colName = "yellow-green";
					} else if ($col == 9) {
						$colName = "light green";
					} else if ($col == 10) {
						$colName = "green";
					} else if ($col == 11) {
						$colName = "medium blue";
					} else if ($col == 12) {
						$colName = "pale blue";
					} else if ($col == 13) {
						$colName = "blue";
					} else if ($col == 14) {
						$colName = "deep blue";
					} else if ($col == 15) {
						$colName = "violet";
					}
					if ($colName != "") {
						$color = $filterChild->appendChild ( $dom->createElement ( 'color' ) );
						$color->appendChild ( $dom->createCDATASection ( $colName ) );
					}

					if ($GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'wratten' ) != "") {
						$wratten = $filterChild->appendChild ( $dom->createElement ( 'wratten' ) );
						$wratten->appendChild ( $dom->createCDATASection ( $GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'wratten' ) ) );
					}

					if ($GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'schott' ) != "") {
						$schott = $filterChild->appendChild ( $dom->createElement ( 'schott' ) );
						$schott->appendChild ( $dom->createCDATASection ( $GLOBALS ['objFilter']->getFilterPropertyFromId ( $value, 'schott' ) ) );
					}
				}
			}
		}

		// add root - <imagers> DeepskyLog has no imagers
		$observersDom = $fcgaDom->appendChild ( $dom->createElement ( 'imagers' ) );

		// Add the observations.
		while ( list ( $key, $value ) = each ( $allObs ) ) {
			$obs = $GLOBALS ['objObservation']->getAllInfoDsObservation ( $value ['observationid'] );
			$objectname = $obs ['objectname'];
			$observerid = $obs ['observerid'];
			$inst = $obs ['instrumentid'];
			$loc = $obs ['locationid'];
			$visibility = $obs ['visibility'];
			$seeing = $obs ['seeing'];
			$limmag = $obs ['limmag'];
			$filt = $obs ['filterid'];
			$eyep = $obs ['eyepieceid'];
			$lns = $obs ['lensid'];

			$observation = $fcgaDom->appendChild ( $dom->createElement ( 'observation' ) );
			$attr = $dom->createAttribute ( "id" );
			$observation->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "obs_" . $value ['observationid'] );
			$attr->appendChild ( $attrText );

			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\s+/", "_", $observerid ) ) );
			$observer = $observation->appendChild ( $dom->createElement ( 'observer' ) );
			$observer->appendChild ( $dom->createTextNode ( "usr_" . $correctedValue ) );

			$site = $observation->appendChild ( $dom->createElement ( 'site' ) );
			$site->appendChild ( $dom->createTextNode ( "site_" . $loc ) );

			// Check whether this observation is part of a session...
			for($scnt = 0; $scnt < count ( $usedSessions ); $scnt ++) {
				$sessionObs = $objDatabase->selectRecordsetArray ( "select * from sessionObservations where sessionid = \"" . $usedSessions [$scnt] . "\" and observationid = \"" . $value ['observationid'] . "\"" );

				if (count ( $sessionObs ) >= 1) {
					$session = $observation->appendChild ( $dom->createElement ( 'session' ) );
					$session->appendChild ( $dom->createTextNode ( "se_" . $usedSessions [$scnt] ) );
				}
			}

			$target = $observation->appendChild ( $dom->createElement ( 'target' ) );
			$correctedValue = $objCatalog->checkObject ( $objectname );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\s+/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\+/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\//", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\,/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\(/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/\)/", "_", $correctedValue ) ) );
			$correctedValue = utf8_encode ( html_entity_decode ( preg_replace ( "/ /", "_", $correctedValue ) ) );

			$target->appendChild ( $dom->createTextNode ( "_" . $correctedValue ) );

			if ($obs ["time"] >= 0) {
				$time = sprintf ( "T%02d:%02d:00+00:00", ( int ) ($obs ["time"] % 2400 / 100), $obs ["time"] % 2400 - ( int ) ($obs ["time"] % 2400 / 100) * 100 );
			} else {
				$time = "T22:00:00+00:00";
			}

			$year = ( int ) ($obs ["date"] / 10000);
			$month = ( int ) (($obs ["date"] - $year * 10000) / 100);
			$day = ( int ) (($obs ["date"] - $year * 10000 - $month * 100));
			if ($day == 0) {
				$day = 1;
			} else if ($day > 31) {
				$day = 31;
			}
			$date = sprintf ( "%4d-%02d-%02d", $year, $month, $day );

			$begin = $observation->appendChild ( $dom->createElement ( 'begin' ) );
			$begin->appendChild ( $dom->createTextNode ( $date . $time ) );

			if ($obs ["SQM"] > 0) {
				$magPerSquareArcsecond = $observation->appendChild ( $dom->createElement ( 'sky-quality' ) );
				$magPerSquareArcsecond->appendChild ( $dom->createTextNode ( $obs ["SQM"] ) );

				$attr = $dom->createAttribute ( "unit" );
				$magPerSquareArcsecond->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "mags-per-squarearcsec" );
				$attr->appendChild ( $attrText );
			} else if ($obs ["limmag"] > 0) {
				$faintestStar = $observation->appendChild ( $dom->createElement ( 'faintestStar' ) );
				$faintestStar->appendChild ( $dom->createTextNode ( $obs ["limmag"] ) );
			}

			if ($obs ["seeing"] > 0) {
				$seeing = $observation->appendChild ( $dom->createElement ( 'seeing' ) );
				$seeing->appendChild ( $dom->createTextNode ( $obs ["seeing"] ) );
			}

			if ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $inst, 'fixedMagnification' ) != 1) {
				$scope = $observation->appendChild ( $dom->createElement ( 'scope' ) );
				$scope->appendChild ( $dom->createTextNode ( "opt_" . $inst ) );
			}

			if ($eyep > 0) {
				$eyepiece = $observation->appendChild ( $dom->createElement ( 'eyepiece' ) );
				$eyepiece->appendChild ( $dom->createTextNode ( "ep_" . $eyep ) );
			}

			if ($lns > 0) {
				$lens = $observation->appendChild ( $dom->createElement ( 'lens' ) );
				$lens->appendChild ( $dom->createTextNode ( "le_" . $lns ) );
			}

			if ($filt > 0) {
				$filter = $observation->appendChild ( $dom->createElement ( 'filter' ) );
				$filter->appendChild ( $dom->createTextNode ( "flt_" . $filt ) );
			}

			$magni = 0;
			if ($GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $inst, 'fixedMagnification' ) > 0) {
				$magni = $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $inst, 'fixedMagnification' );
			} else if ($obs ["magnification"] > 0) {
				$magni = $obs ["magnification"];
			} else if ($eyep > 0 && $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $inst, 'fixedMagnification' ) > 0) {
				$factor = 1.0;
				if ($GLOBALS ['objLens']->getFilterPropertyFromId ( $lns, 'factor' ) > 0) {
					$factor = $GLOBALS ['objLens']->getFilterPropertyFromId ( $lns, 'factor' );
				}
				$magni = sprintf ( "%.2f", $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $inst, 'fixedMagnification' ) * $GLOBALS ['objInstrument']->getInstrumentPropertyFromId ( $inst, 'diameter' ) * $factor / $GLOBALS ['objEyepiece']->getEyepiecePropertyFromId ( $eyep, 'focalLength' ) );
			}

			// Replace , with .
			$magni = str_replace ( ",", ".", $magni );

			if ($magni > 0) {
				$magnification = $observation->appendChild ( $dom->createElement ( 'magnification' ) );
				$magnification->appendChild ( $dom->createTextNode ( ( int ) $magni ) );
			}

			$result = $observation->appendChild ( $dom->createElement ( 'result' ) );

			if ($obs ["extended"] > 0) {
				$attr = $dom->createAttribute ( "extended" );
				$result->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "true" );
				$attr->appendChild ( $attrText );
			}

			$attr = $dom->createAttribute ( "lang" );
			$result->appendChild ( $attr );

			$attrText = $dom->createTextNode ( $obs ["language"] );
			$attr->appendChild ( $attrText );

			if ($obs ["mottled"] > 0) {
				$attr = $dom->createAttribute ( "mottled" );
				$result->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "true" );
				$attr->appendChild ( $attrText );
			}

			$object = $GLOBALS ['objObject']->getAllInfoDsObject ( $objectname );

			$type = $object ["type"];

			if ($type == "OPNCL" || $type == "SMCOC" || $type == "LMCOC") {
				if ($obs ["partlyUnresolved"] > 0) {
					$attr = $dom->createAttribute ( "partlyUnresolved" );
					$result->appendChild ( $attr );

					$attrText = $dom->createTextNode ( "true" );
					$attr->appendChild ( $attrText );
				}

				if ($obs ["unusualShape"] > 0) {
					$attr = $dom->createAttribute ( "unusualShape" );
					$result->appendChild ( $attr );

					$attrText = $dom->createTextNode ( "true" );
					$attr->appendChild ( $attrText );
				}

				if ($obs ["colorContrasts"] > 0) {
					$attr = $dom->createAttribute ( "colorContrasts" );
					$result->appendChild ( $attr );

					$attrText = $dom->createTextNode ( "true" );
					$attr->appendChild ( $attrText );
				}
			}

			if ($type == "AA2STAR" || $type == "DS") {
				if ($obs ["equalBrightness"] >= 0) {
					$attr = $dom->createAttribute ( "equalBrightness" );
					$result->appendChild ( $attr );

					if ($obs ["equalBrightness"] == 0) {
						$attrText = $dom->createTextNode ( "false" );
					} else {
						$attrText = $dom->createTextNode ( "true" );
					}
					$attr->appendChild ( $attrText );
				}

				if ($obs ["niceField"] >= 0) {
					$attr = $dom->createAttribute ( "niceSurrounding" );
					$result->appendChild ( $attr );

					if ($obs ["equalBrightness"] == 0) {
						$attrText = $dom->createTextNode ( "false" );
					} else {
						$attrText = $dom->createTextNode ( "true" );
					}
					$attr->appendChild ( $attrText );
				}

				if ($obs ["component1"] > 0) {
					if ($obs ["component1"] == 1) {
						$col1 = "white";
					}
					if ($obs ["component1"] == 2) {
						$col1 = "red";
					}
					if ($obs ["component1"] == 3) {
						$col1 = "orange";
					}
					if ($obs ["component1"] == 4) {
						$col1 = "yellow";
					}
					if ($obs ["component1"] == 5) {
						$col1 = "green";
					}
					if ($obs ["component1"] == 6) {
						$col1 = "blue";
					}
					$colorMain = $result->appendChild ( $dom->createElement ( 'colorMain' ) );
					$colorMain->appendChild ( $dom->createTextNode ( $col1 ) );
				}

				if ($obs ["component2"] > 0) {
					if ($obs ["component2"] == 1) {
						$col2 = "white";
					}
					if ($obs ["component2"] == 2) {
						$col2 = "red";
					}
					if ($obs ["component2"] == 3) {
						$col2 = "orange";
					}
					if ($obs ["component2"] == 4) {
						$col2 = "yellow";
					}
					if ($obs ["component2"] == 5) {
						$col2 = "green";
					}
					if ($obs ["component2"] == 6) {
						$col2 = "blue";
					}
					$colorCompanion = $result->appendChild ( $dom->createElement ( 'colorCompanion' ) );
					$colorCompanion->appendChild ( $dom->createTextNode ( $col2 ) );
				}
			}

			if ($obs ["resolved"] > 0) {
				$attr = $dom->createAttribute ( "resolved" );
				$result->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "true" );
				$attr->appendChild ( $attrText );
			}

			if ($obs ["stellar"] > 0) {
				$attr = $dom->createAttribute ( "stellar" );
				$result->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "true" );
				$attr->appendChild ( $attrText );
			}

			$attr = $dom->createAttribute ( "xsi:type" );
			$result->appendChild ( $attr );

			$object = $GLOBALS ['objObject']->getAllInfoDsObject ( $objectname );

			$type = $object ["type"];
			if ($type == "OPNCL" || $type == "SMCOC" || $type == "LMCOC") {
				$type = "oal:findingsDeepSkyOCType";
			} else if ($type == "AA2STAR" || $type == "DS") {
				$type = "oal:findingsDeepSkyDSType";
			} else {
				$type = "oal:findingsDeepSkyType";
			}
			$attrText = $dom->createTextNode ( $type );
			$attr->appendChild ( $attrText );

			$description = $result->appendChild ( $dom->createElement ( 'description' ) );
			$description->appendChild ( $dom->createCDATASection ( utf8_encode ( $objPresentations->br2nl ( html_entity_decode ( $obs ["description"] ) ) ) ) );

			$rat = $obs ["visibility"];
			if ($rat == 0) {
				$rat = 99;
			}

			if ($obs ["smallDiameter"] > 0) {
				$smallDiameter = $result->appendChild ( $dom->createElement ( 'smallDiameter' ) );
				$smallDiameter->appendChild ( $dom->createTextNode ( $obs ["smallDiameter"] ) );

				$attr = $dom->createAttribute ( "unit" );
				$smallDiameter->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "arcsec" );
				$attr->appendChild ( $attrText );
			}

			if ($obs ["largeDiameter"] > 0) {
				$largeDiameter = $result->appendChild ( $dom->createElement ( 'largeDiameter' ) );
				$largeDiameter->appendChild ( $dom->createTextNode ( $obs ["largeDiameter"] ) );

				$attr = $dom->createAttribute ( "unit" );
				$largeDiameter->appendChild ( $attr );

				$attrText = $dom->createTextNode ( "arcsec" );
				$attr->appendChild ( $attrText );
			}

			$rating = $result->appendChild ( $dom->createElement ( 'rating' ) );
			$rating->appendChild ( $dom->createTextNode ( $rat ) );

			if ($obs ["clusterType"] != "" && $obs ["clusterType"] != 0) {
				$character = $result->appendChild ( $dom->createElement ( 'character' ) );
				$character->appendChild ( $dom->createCDATASection ( $obs ["clusterType"] ) );
			}
		}

		// generate xml
		$dom->formatOutput = true; // set the formatOutput attribute of
		                           // domDocument to true
		                           // save XML as string or file
		$test1 = $dom->saveXML (); // put string in test1

		print $test1;
	}
	public function csvObjects($result) // Creates a csv file from an array of objects
{
		global $objObject, $objPresentations, $objObserver, $loggedUser;
		$result = $this->sortResult ( $result );
		echo html_entity_decode ( LangCSVMessage7 ) . "\n";
		while ( list ( $key, $valueA ) = each ( $result ) ) {
			$alt = "";
			$alts = $objObject->getAlternativeNames ( $valueA ['objectname'] );
			while ( list ( $key, $value ) = each ( $alts ) )
				if ($value != $valueA ['objectname'])
					$alt .= " - " . trim ( $value );
			$alt = ($alt ? substr ( $alt, 3 ) : '');
			echo $valueA ['objectname'] . ";" . $alt . ";" . $objPresentations->raToStringHMS ( $valueA ['objectra'] ) . ";" . $objPresentations->decToStringDegMinSec ( $valueA ['objectdecl'], 0 ) . ";" . $GLOBALS [$valueA ['objectconstellation']] . ";" . $GLOBALS [$valueA ['objecttype']] . ";" . $objPresentations->presentationInt1 ( $valueA ['objectmagnitude'], 99.9, '' ) . ";" . $objPresentations->presentationInt1 ( $valueA ['objectsurfacebrightness'], 99.9, '' ) . ";" . $valueA ['objectsize'] . ";" . $objPresentations->presentationInt ( $valueA ['objectpa'], 999, '' ) . ";" . $valueA [$objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' )] . ";" . $valueA ['objectcontrast'] . ";" . $valueA ['objectoptimalmagnification'] . ";" . $valueA ['objectseen'] . ";" . $valueA ['objectlastseen'] . "\n";
		}
	}
	public function csvObjectsList($result) // Creates a csv file from an array of list objects
{
		global $objObject, $objPresentations, $objObserver, $loggedUser;
		echo html_entity_decode ( LangCSVMessage7List ) . "\n";
		while ( list ( $key, $valueA ) = each ( $result ) ) {
			$alt = "";
			$alts = $objObject->getAlternativeNames ( $valueA ['objectname'] );
			while ( list ( $key1, $value ) = each ( $alts ) )
				if ($value != $valueA ['objectname'])
					$alt .= " - " . trim ( $value );
			$alt = ($alt ? substr ( $alt, 3 ) : '');
			echo $valueA ['showname'] . ";" . $valueA ['objectname'] . ";" . $alt . ";" . $objPresentations->raToString ( $valueA ['objectra'] ) . ";" . $objPresentations->decToString ( $valueA ['objectdecl'], 0 ) . ";" . $GLOBALS [$valueA ['objectconstellation']] . ";" . $GLOBALS [$valueA ['objecttype']] . ";" . $objPresentations->presentationInt1 ( $valueA ['objectmagnitude'], 99.9, '' ) . ";" . $objPresentations->presentationInt1 ( $valueA ['objectsurfacebrightness'], 99.9, '' ) . ";" . $valueA ['objectsize'] . ";" . $objPresentations->presentationInt ( $valueA ['objectpa'], 999, '' ) . ";" . $valueA [$objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' )] . ";" . $valueA ['objectcontrast'] . ";" . $valueA ['objectoptimalmagnification'] . ";" . $valueA ['objectseen'] . ";" . $valueA ['objectlastseen'] . "\n";
		}
	}
	public function csvObservations($result) // Creates a csv file from an array of observations
{
		global $objLens, $objFilter, $objEyepiece, $objLocation, $objPresentations, $objObservation, $objObserver, $objInstrument;
		while ( list ( $key, $value ) = each ( $result ) ) {
			$obs = $objObservation->getAllInfoDsObservation ( $value ['observationid'] );
			$date = sscanf ( $obs ['date'], "%4d%2d%2d" );
			$time = $obs ['time'];
			if ($time >= "0") {
				$hours = ( int ) ($time / 100);
				$minutes = $time - (100 * $hours);
				$time = sprintf ( "%d:%02d", $hours, $minutes );
			} else
				$time = "";
			echo html_entity_decode ( $obs ['objectname'] ) . ";" . html_entity_decode ( $objObserver->getObserverProperty ( $obs ['observerid'], 'firstname' ) . " " . $objObserver->getObserverProperty ( $obs ['observerid'], 'name' ) ) . ";" . $date [2] . "-" . $date [1] . "-" . $date [0] . ";" . $time . ";" . html_entity_decode ( $objLocation->getLocationPropertyFromId ( $obs ['locationid'], 'name' ) ) . ";" . html_entity_decode ( $objInstrument->getInstrumentPropertyFromId ( $obs ['instrumentid'], 'name' ) ) . ";" . html_entity_decode ( $objEyepiece->getEyepiecePropertyFromId ( $obs ['eyepieceid'], 'name' ) ) . ";" . html_entity_decode ( $objFilter->getFilterPropertyFromId ( $obs ['filterid'], 'name' ) ) . ";" . html_entity_decode ( $objLens->getLensPropertyFromId ( $obs ['lensid'], 'name' ) ) . ";" . $obs ['seeing'] . ";" . $obs ['limmag'] . ";" . $objPresentations->presentationInt ( $obs ['visibility'], "0", "" ) . ";" . $obs ['language'] . ";" . preg_replace ( "/(\")/", "", preg_replace ( "/(\r\n|\n|\r)/", "", preg_replace ( "/;/", ",", $objPresentations->br2nl ( html_entity_decode ( $obs ['description'], ENT_COMPAT, 'UTF-8' ) ) ) ) ) . "\n";
		}
	}
	public function csvObservationsImportErrors($result) // Creates a csv file from an array of error csv import observations
{
		global $objLens, $objFilter, $objEyepiece, $objLocation, $objPresentations, $objObservation, $objObserver, $objInstrument;
		for($i = 0; $i < count ( $_SESSION ['csvImportErrorData'] ); $i ++) {
			for($j = 0; $j < 13; $j ++)
				echo $this->checkArrayKey ( $_SESSION ['csvImportErrorData'] [$i], $j, '' ) . ";";
			echo preg_replace ( "/(\")/", "", preg_replace ( "/(\r\n|\n|\r)/", "", preg_replace ( "/;/", ",", $objPresentations->br2nl ( html_entity_decode ( $this->checkArrayKey ( $_SESSION ['csvImportErrorData'] [$i], 13, '' ) ) ) ) ) );
			echo "\n";
		}
	}
	public function pdfCometObservations($result) // Creates a pdf document from an array of comet observations
{
		include_once "cometobjects.php";
		include_once "observers.php";
		include_once "instruments.php";
		include_once "locations.php";
		include_once "cometobservations.php";
		include_once "icqmethod.php";
		include_once "icqreferencekey.php";
		include_once "setup/vars.php";
		include_once "setup/databaseInfo.php";
		global $instDir, $objCometObject, $loggedUser, $dateformat;
		$result = $this->sortResult ( $result );

		$objects = new CometObjects ();
		$observer = new Observers ();
		$instrument = new Instruments ();
		$observation = new CometObservations ();
		$location = new Locations ();
		$util = $this;
		$ICQMETHODS = new ICQMETHOD ();
		$ICQREFERENCEKEYS = new ICQREFERENCEKEY ();
		$_GET ['pdfTitle'] = "CometObservations.pdf";
		// Create pdf file
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$pdf->ezStartPageNumbers ( 300, 30, 10 );

		$fontdir = $instDir . 'lib/fonts/Helvetica.afm';
		$pdf->selectFont ( $fontdir );
		$pdf->ezText ( utf8_decode ( html_entity_decode ( LangPDFTitle3 ) ) . "\n" );

		while ( list ( $key, $value ) = each ( $result ) ) {
			$objectname = $objCometObject->getName ( $observation->getObjectId ( $value ) );

			$pdf->ezText ( utf8_decode ( $objectname ), "14" );

			$observerid = $observation->getObserverId ( $value );

			if ($observer->getObserverProperty ( $loggedUser, 'UT' )) {
				$date = sscanf ( $observation->getDate ( $value ), "%4d%2d%2d" );
				$time = $observation->getTime ( $value );
			} else {
				$date = sscanf ( $observation->getLocalDate ( $value ), "%4d%2d%2d" );
				$time = $observation->getLocalTime ( $value );
			}
			$hour = ( int ) ($time / 100);
			$minute = $time - $hour * 100;
			$formattedDate = date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );

			if ($minute < 10) {
				$minute = "0" . $minute;
			}

			$observername = LangPDFMessage13 . $observer->getObserverProperty ( $observerid, 'firstname' ) . " " . $observer->getObserverProperty ( $observerid, 'name' ) . html_entity_decode ( LangPDFMessage14 ) . $formattedDate . " (" . $hour . ":" . $minute . ")";

			$pdf->ezText ( utf8_decode ( $observername ), "12" );

			// Location and instrument
			if (($observation->getLocationId ( $value ) != 0 && $observation->getLocationId ( $value ) != 1) || $observation->getInstrumentId ( $value ) != 0) {
				if ($observation->getLocationId ( $value ) != 0 && $observation->getLocationId ( $value ) != 1) {
					$locationname = LangPDFMessage10 . " : " . $location->getLocationPropertyFromId ( $observation->getLocationId ( $value ), 'name' );
					$extra = ", ";
				} else {
					$locationname = "";
				}

				if ($observation->getInstrumentId ( $value ) != 0) {
					$instr = $instrument->getInstrumentPropertyFromId ( $observation->getInstrumentId ( $value ), 'name' );
					if ($instr == "Naked eye") {
						$instr = InstrumentsNakedEye;
					}

					$locationname = $locationname . $extra . html_entity_decode ( LangPDFMessage11 ) . " : " . $instr;

					if (strcmp ( $observation->getMagnification ( $value ), "" ) != 0) {
						$locationname = $locationname . " (" . $observation->getMagnification ( $value ) . " x)";
					}
				}

				$pdf->ezText ( utf8_decode ( $locationname ), "12" );
			}

			// Methode
			$method = $observation->getMethode ( $value );

			if (strcmp ( $method, "" ) != 0) {
				$methodstr = html_entity_decode ( LangViewObservationField15 ) . " : " . $method . " - " . $ICQMETHODS->getDescription ( $method );

				$pdf->ezText ( utf8_decode ( $methodstr ), "12" );
			}

			// Used chart
			$chart = $observation->getChart ( $value );

			if (strcmp ( $chart, "" ) != 0) {
				$chartstr = html_entity_decode ( LangViewObservationField17 ) . " : " . $chart . " - " . $ICQREFERENCEKEYS->getDescription ( $chart );

				$pdf->ezText ( utf8_decode ( $chartstr ), "12" );
			}

			// Magnitude
			$magnitude = $observation->getMagnitude ( $value );

			if ($magnitude != - 99.9) {
				$magstr = "";

				if ($observation->getMagnitudeWeakerThan ( $value )) {
					$magstr = $magstr . LangNewComet3 . " ";
				}
				$magstr = $magstr . html_entity_decode ( LangViewObservationField16 ) . " : " . sprintf ( "%.01f", $magnitude );

				if ($observation->getMagnitudeUncertain ( $value )) {
					$magstr = $magstr . " (" . LangNewComet2 . ")";
				}

				$pdf->ezText ( utf8_decode ( $magstr ), "12" );
			}

			// Degree of condensation
			$dc = $observation->getDc ( $value );
			$coma = $observation->getComa ( $value );

			$dcstr = "";
			$extra = "";

			if (strcmp ( $dc, "" ) != 0 || $coma != - 99) {
				if (strcmp ( $dc, "" ) != 0) {
					$dcstr = $dcstr . html_entity_decode ( LangNewComet8 ) . " : " . $dc;
					$extra = ", ";
				}

				// Coma

				if ($coma != - 99) {
					$dcstr = $dcstr . $extra . html_entity_decode ( LangNewComet9 ) . " : " . $coma . "'";
				}

				$pdf->ezText ( utf8_decode ( $dcstr ), "12" );
			}

			// Tail
			$tail = $observation->getTail ( $value );
			$pa = $observation->getPa ( $value );

			$tailstr = "";
			$extra = "";

			if ($tail != - 99 || $pa != - 99) {
				if ($tail != - 99) {
					$tailstr = $tailstr . html_entity_decode ( LangNewComet10 ) . " : " . $tail . "'";
					$extra = ", ";
				}

				if ($pa != - 99) {
					$tailstr = $tailstr . $extra . html_entity_decode ( LangNewComet11 ) . " : " . $pa . "";
				}

				$pdf->ezText ( utf8_decode ( $tailstr ), "12" );
			}

			// Description
			$description = $observation->getDescription ( $value );

			if (strcmp ( $description, "" ) != 0) {
				$descstr = html_entity_decode ( LangPDFMessage15 ) . " : " . strip_tags ( $description );
				$pdf->ezText ( utf8_decode ( $descstr ), "12" );
			}

			$upload_dir = $instDir . 'comets/' . 'cometdrawings';
			$dir = opendir ( $upload_dir );

			while ( FALSE !== ($file = readdir ( $dir )) ) {
				if ("." == $file or ".." == $file) {
					continue; // skip current directory and directory above
				}
				if (fnmatch ( $value . ".gif", $file ) || fnmatch ( $value . ".jpg", $file ) || fnmatch ( $value . ".png", $file )) {
					$pdf->ezImage ( $upload_dir . "/" . $value . ".jpg", 0, 500, "none", "left" );
				}
			}

			$pdf->ezText ( "" );
		}

		$pdf->ezStream ();
	}
	public function pdfObjectnames($result) // Creates a pdf document from an array of objects
{
		global $instDir;
		$page = 1;
		$i = 0;
		$result = $this->sortResult ( $result );
		while ( list ( $key, $valueA ) = each ( $result ) )
			$obs1 [] = array (
					$valueA ['showname']
			);
			// Create pdf file
		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$pdf->ezStartPageNumbers ( 450, 15, 10 );
		$pdf->selectFont ( $instDir . 'lib/fonts/Helvetica.afm' );
		$pdf->ezText ( utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ), 18 );
		$pdf->ezColumnsStart ( array (
				'num' => 10
		) );
		$pdf->ezTable ( $obs1, '', '', array (
				"width" => "750",
				"cols" => array (
						array (
								'justification' => 'left',
								'width' => 80
						)
				),
				"fontSize" => "7",
				"showLines" => "0",
				"showHeadings" => "0",
				"rowGap" => "0",
				"colGap" => "0"
		) );
		$pdf->ezStream ();
	}
	public function sortResult($result) {
		// Sort the result based on the 'sortOrder' cookie.
		$sortOrderArray = explode ( ",", trim ( $_COOKIE ['sortOrder'], "|" ) );

		foreach ( $sortOrderArray as $sort ) {
			$sort = trim ( $sort, ")" );
			$sort = explode ( "(", $sort );
			$sortName [] = $sort [0];
			// 0 = up, 1 = down
			$sortOrder [] = $sort [1];
		}
		// Multicolumn sort
		$sort = array ();

		$cnt = 0;
		foreach ( $sortName as $sName ) {
			foreach ( $result as $k => $v ) {
				if ($v [$sName] == "") {
					if ($sortOrder [$cnt] == 1) {
						$sort [$sName] [$k] = - 99.0;
					} else {
						$sort [$sName] [$k] = + 99.0;
					}
				} else {
					$sort [$sName] [$k] = $v [$sName];
				}
			}
			$cnt ++;
		}
		$cnt = 0;
		$dynamicSort = array ();
		foreach ( $sortName as $sName ) {
			$dynamicSort [] = $sort [$sName];
			if ($sortOrder [$cnt] == 0) {
				$dynamicSort [] = SORT_ASC;
			} else {
				$dynamicSort [] = SORT_DESC;
			}
			$dynamicSort [] = SORT_NATURAL;
			$cnt ++;
		}
		$param = array_merge ( $dynamicSort, array (
				&$result
		) );
		call_user_func_array ( 'array_multisort', $param );

		// Return the sorted result
		return $result;
	}
	public function pdfObjects($result) // Creates a pdf document from an array of objects
{
		global $instDir, $objAtlas, $objObserver, $objPresentations, $loggedUser;

		$result = $this->sortResult ( $result );

		while ( list ( $key, $valueA ) = each ( $result ) )
			$obs1 [] = array (
					"Name" => $valueA ['showname'],
					"ra" => $objPresentations->raToString ( $valueA ['objectra'] ),
					"decl" => $objPresentations->decToString ( $valueA ['objectdecl'], 0 ),
					"mag" => $objPresentations->presentationInt1 ( $valueA ['objectmagnitude'], 99.9, '' ),
					"sb" => $objPresentations->presentationInt1 ( $valueA ['objectsurfacebrightness'], 99.9, '' ),
					"con" => $GLOBALS [$valueA ['objectconstellation']],
					"diam" => $valueA ['objectsize'],
					"pa" => $objPresentations->presentationInt ( $valueA ['objectpa'], 999, "-" ),
					"type" => $GLOBALS [$valueA ['objecttype']],
					"page" => $valueA [$objObserver->getObserverProperty ( $this->checkSessionKey ( 'deepskylog_id', '' ), 'standardAtlasCode', 'urano' )],
					"contrast" => $valueA ['objectcontrast'],
					"magnification" => $valueA ['objectoptimalmagnificationvalue'],
					"seen" => $valueA ['objectseen'],
					"seendate" => $valueA ['objectlastseen']
			);

		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$pdf->ezStartPageNumbers ( 450, 15, 10 );
		$fontdir = $instDir . 'lib/fonts/Helvetica.afm';
		$pdf->selectFont ( $fontdir );
		$pdf->ezTable ( $obs1, array (
				"Name" => html_entity_decode ( LangPDFMessage1 ),
				"ra" => html_entity_decode ( LangPDFMessage3 ),
				"decl" => html_entity_decode ( LangPDFMessage4 ),
				"type" => html_entity_decode ( LangPDFMessage5 ),
				"con" => html_entity_decode ( LangPDFMessage6 ),
				"mag" => html_entity_decode ( LangPDFMessage7 ),
				"sb" => html_entity_decode ( LangPDFMessage8 ),
				"diam" => html_entity_decode ( LangPDFMessage9 ),
				"pa" => html_entity_decode ( LangPDFMessage16 ),
				"page" => html_entity_decode ( $objAtlas->atlasCodes [$objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' )] ),
				"contrast" => html_entity_decode ( LangPDFMessage17 ),
				"magnification" => html_entity_decode ( LangPDFMessage18 ),
				"seen" => html_entity_decode ( LangOverviewObjectsHeader7 ),
				"seendate" => html_entity_decode ( LangOverviewObjectsHeader8 )
		), utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ), array (
				"width" => "750",
				"cols" => array (
						"Name" => array (
								'justification' => 'left',
								'width' => 100
						),
						"ra" => array (
								'justification' => 'center',
								'width' => 65
						),
						"decl" => array (
								'justification' => 'center',
								'width' => 50
						),
						"type" => array (
								'justification' => 'left',
								'width' => 110
						),
						"con" => array (
								'justification' => 'left',
								'width' => 90
						),
						"mag" => array (
								'justification' => 'center',
								'width' => 35
						),
						"sb" => array (
								'justification' => 'center',
								'width' => 35
						),
						"diam" => array (
								'justification' => 'center',
								'width' => 65
						),
						"pa" => array (
								'justification' => 'center',
								'width' => 30
						),
						"page" => array (
								'justification' => 'center',
								'width' => 45
						),
						"contrast" => array (
								'justification' => 'center',
								'width' => 35
						),
						"magnification" => array (
								'justification' => 'center',
								'width' => 35
						),
						"seen" => array (
								'justification' => 'center',
								'width' => 50
						),
						"seendate" => array (
								'justification' => 'center',
								'width' => 50
						)
				),
				"fontSize" => "7"
		) );
		$pdf->ezStream ();
	}
	public function pdfObjectsDetails($result) // Creates a pdf document from an array of objects
{
		global $dateformat, $baseURL, $instDir, $objObserver, $loggedUser, $objLocation, $objInstrument, $objPresentations;
		$result = $this->sortResult ( $result );

		$pdf = new Cezpdf ( 'a4', 'landscape' );
		$pdf->selectFont ( $instDir . 'lib/fonts/Helvetica.afm' );

		$bottom = 40;
		$bottomsection = 30;
		$top = 550;
		$header = 570;
		$footer = 10;
		$xleft = 20;
		$xmid = 431;
		$fontSizeSection = 10;
		$fontSizeText = 8;
		$descriptionLeadingSpace = 20;
		$sectionBarSpace = 3;
		$deltalineSection = 2;

		$deltaline = $fontSizeText + 4;
		$pagenr = 0;
		$y = 0;
		$xbase = $xmid;
		$sectionBarHeight = $fontSizeSection + 4;
		$SectionBarWidth = 400 + $sectionBarSpace;

		$theDate = date ( 'd/m/Y' );
		$pdf->addTextWrap ( $xleft, $header, 100, 8, utf8_decode ( $theDate ) );
		if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ))
			$pdf->addTextWrap ( $xleft, $footer, $xmid + $SectionBarWidth, 8, utf8_decode ( html_entity_decode ( LangPDFMessage19 . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . ' ' . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . ' ' . LangPDFMessage20 . $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) . ' ' . LangPDFMessage21 . $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) ) ), 'center' );
		$pdf->addTextWrap ( $xleft, $header, $xmid + $SectionBarWidth, 10, utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ), 'center' );
		$pdf->addTextWrap ( $xmid + $SectionBarWidth - $sectionBarSpace - 100, $header, 100, 8, utf8_decode ( LangPDFMessage22 . '1' ), 'right' );
		while ( list ( $key, $valueA ) = each ( $result ) ) {
			$con = $valueA ['objectconstellation'];
			if ($y < $bottom) {
				$y = $top;
				if ($xbase == $xmid) {
					if ($pagenr ++) {
						$pdf->newPage ();
						$pdf->addTextWrap ( $xleft, $header, 100, 8, utf8_decode ( $theDate ) );
						if ($loggedUser && $objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ))
							$pdf->addTextWrap ( $xleft, $footer, $xmid + $SectionBarWidth, 8, utf8_decode ( html_entity_decode ( LangPDFMessage19 . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . ' ' . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . ' ' . LangPDFMessage20 . $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) . ' ' . LangPDFMessage21 . $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) ) ), 'center' );
						$pdf->addTextWrap ( $xleft, $header, $xmid + $SectionBarWidth, 10, utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ), 'center' );
						$pdf->addTextWrap ( $xmid + $SectionBarWidth - $sectionBarSpace - 100, $header, 100, 8, utf8_decode ( LangPDFMessage22 . $pagenr ), 'right' );
					}
					$xbase = $xleft;
				} else {
					$xbase = $xmid;
				}
			}
			$pdf->addTextWrap ( $xbase, $y, 30, $fontSizeText, utf8_decode ( $valueA ['objectseen'] ) ); // seen
			$pdf->addTextWrap ( $xbase + 30, $y, 40, $fontSizeText, utf8_decode ( $valueA ['objectlastseen'] ) ); // last seen
			$pdf->addTextWrap ( $xbase + 70, $y, 85, $fontSizeText, utf8_decode ( '<b>' . '<c:alink:' . $baseURL . 'index.php?indexAction=detail_object&amp;object=' . urlencode ( $valueA ['objectname'] ) . '>' . $valueA ['showname'] ) ); // object
			$pdf->addTextWrap ( $xbase + 150, $y, 30, $fontSizeText, utf8_decode ( '</c:alink></b>' . $valueA ['objecttype'] ) ); // type
			$pdf->addTextWrap ( $xbase + 180, $y, 20, $fontSizeText, utf8_decode ( $valueA ['objectconstellation'] ) ); // constellation
			$pdf->addTextWrap ( $xbase + 200, $y, 17, $fontSizeText, utf8_decode ( $objPresentations->presentationInt1 ( $valueA ['objectmagnitude'], 99.9, '' ) ), 'left' ); // mag
			$pdf->addTextWrap ( $xbase + 217, $y, 18, $fontSizeText, utf8_decode ( $objPresentations->presentationInt1 ( $valueA ['objectsurfacebrightness'], 99.9, '' ) ), 'left' ); // sb
			$pdf->addTextWrap ( $xbase + 235, $y, 60, $fontSizeText, utf8_decode ( $objPresentations->raToStringHM ( $valueA ['objectra'] ) . ' ' . $objPresentations->decToString ( $valueA ['objectdecl'], 0 ) ) ); // ra - decl
			$pdf->addTextWrap ( $xbase + 295, $y, 55, $fontSizeText, utf8_decode ( $valueA ['objectsize'] . '/' . $objPresentations->presentationInt ( $valueA ['objectpa'], 999, "-" ) ) ); // size
			$pdf->addTextWrap ( $xbase + 351, $y, 17, $fontSizeText, utf8_decode ( $objPresentations->presentationInt1 ( $valueA ['objectcontrast'], '', '' ) ), 'left' ); // contrast
			$pdf->addTextWrap ( $xbase + 368, $y, 17, $fontSizeText, utf8_decode ( ( int ) $valueA ['objectoptimalmagnification'] ), 'left' ); // magnification
			$pdf->addTextWrap ( $xbase + 380, $y, 20, $fontSizeText, utf8_decode ( '<b>' . $valueA [($loggedUser ? $objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' ) : 'urano')] . '</b>' ), 'right' ); // atlas page
			$y -= $deltaline;
			if (array_key_exists ( 'objectlistdescription', $valueA ) && $valueA ['objectlistdescription']) {
				$theText = $objPresentations->br2nl ( $valueA ['objectlistdescription'] );
				$theText = $pdf->addTextWrap ( $xbase + $descriptionLeadingSpace, $y, $xmid - $xleft - $descriptionLeadingSpace - 10, $fontSizeText, '<i>' . utf8_decode ( $theText ) );
				$y -= $deltaline;
				while ( $theText ) {
					if ($y < $bottomsection) {
						$y = $top;
						if ($xbase == $xmid) {
							if ($pagenr ++) {
								$pdf->newPage ();
								$pdf->addTextWrap ( $xleft, $header, 100, 8, utf8_decode ( $theDate ) );
								if ($objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ))
									$pdf->addTextWrap ( $xleft, $footer, $xmid + $SectionBarWidth, 8, utf8_decode ( html_entity_decode ( LangPDFMessage19 . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . ' ' . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . LangPDFMessage20 . $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) . ' ' . LangPDFMessage21 . $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) ) ), 'center' );
								$pdf->addTextWrap ( $xleft, $header, $xmid + $SectionBarWidth, 10, utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ), 'center' );
								$pdf->addTextWrap ( $xmid + $SectionBarWidth - $sectionBarSpace - 100, $header, 100, 8, utf8_decode ( LangPDFMessage22 . $pagenr ), 'right' );
							}
							$xbase = $xleft;
							if ($sort) {
								$y -= $deltalineSection;
								$pdf->rectangle ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $SectionBarWidth, $sectionBarHeight );
								$pdf->addText ( $xbase, $y, $fontSizeSection, utf8_decode ( $GLOBALS [$$sort] ) );
								$y -= $deltaline + $deltalineSection;
							}
						} else {
							$xbase = $xmid;
							if ($sort) {
								$y -= $deltalineSection;
								$pdf->rectangle ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $SectionBarWidth, $sectionBarHeight );
								$pdf->addText ( $xbase, $y, $fontSizeSection, utf8_decode ( $GLOBALS [$$sort] ) );
								$y -= $deltaline + $deltalineSection;
							}
						}
					}
					$theText = $pdf->addTextWrap ( $xbase + $descriptionLeadingSpace, $y, $xmid - $xleft - $descriptionLeadingSpace - 10, $fontSizeText, utf8_decode ( $theText ) );
					$y -= $deltaline;
				}
				$pdf->addText ( 0, 0, 10, '</i>' );
			} elseif (array_key_exists ( 'objectdescription', $valueA ) && $valueA ['objectdescription']) {
				$theText = $objPresentations->br2nl ( $valueA ['objectdescription'] );
				$theText = $pdf->addTextWrap ( $xbase + $descriptionLeadingSpace, $y, $xmid - $xleft - $descriptionLeadingSpace - 10, $fontSizeText, '<i>' . utf8_decode ( $theText ) );
				$y -= $deltaline;
				while ( $theText ) {
					if ($y < $bottomsection) {
						$y = $top;
						if ($xbase == $xmid) {
							if ($pagenr ++) {
								$pdf->newPage ();
								$pdf->addTextWrap ( $xleft, $header, 100, 8, utf8_decode ( $theDate ) );
								if ($objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ))
									$pdf->addTextWrap ( $xleft, $footer, $xmid + $SectionBarWidth, 8, utf8_decode ( html_entity_decode ( LangPDFMessage19 . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . ' ' . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . LangPDFMessage20 . $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) . ' ' . LangPDFMessage21 . $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) ) ), 'center' );
								$pdf->addTextWrap ( $xleft, $header, $xmid + $SectionBarWidth, 10, utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ), 'center' );
								$pdf->addTextWrap ( $xmid + $SectionBarWidth - $sectionBarSpace - 100, $header, 100, 8, utf8_decode ( LangPDFMessage22 . $pagenr ), 'right' );
							}
							$xbase = $xleft;
							if ($sort) {
								$y -= $deltalineSection;
								$pdf->rectangle ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $SectionBarWidth, $sectionBarHeight );
								$pdf->addText ( $xbase, $y, $fontSizeSection, utf8_decode ( $GLOBALS [$$sort] ) );
								$y -= $deltaline + $deltalineSection;
							}
						} else {
							$xbase = $xmid;
							if ($sort) {
								$y -= $deltalineSection;
								$pdf->rectangle ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $SectionBarWidth, $sectionBarHeight );
								$pdf->addText ( $xbase, $y, $fontSizeSection, utf8_decode ( $GLOBALS [$$sort] ) );
								$y -= $deltaline + $deltalineSection;
							}
						}
					}
					$theText = $pdf->addTextWrap ( $xbase + $descriptionLeadingSpace, $y, $xmid - $xleft - $descriptionLeadingSpace - 10, $fontSizeText, utf8_decode ( $theText ) );
					$y -= $deltaline;
				}
				$pdf->addText ( 0, 0, 10, '</i>' );
			}
		}
		$pdf->Stream ();
	}
	public function firstpage(&$y, $bottomsection, $top, &$xbase, $xmid, &$pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, $i, $showelements, $reportdata) {
		global $objObserver, $loggedUser, $objLocation, $objInstrument;
		$y = $top;
		$xbase = $xleft;
		$pdf->addTextWrap ( $xleft, $header, 100, $fontSizeText, utf8_decode ( $theDate ) );
		if ($objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) && (strpos ( $showelements, 'h' ) !== FALSE)) {
			$pdf->addTextWrap ( $xleft, $footer, $xmid + $SectionBarWidth, $fontSizeText, utf8_decode ( html_entity_decode ( LangPDFMessage19 . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . ' ' . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . ' ' . LangPDFMessage20 . $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) . ' ' . LangPDFMessage21 . $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) . LangRistrasetOn . $this->checkSessionKey ( 'globalDay' ) . ' ' . $GLOBALS ['Month' . $this->checkSessionKey ( 'globalMonth' )] . ' ' . $this->checkSessionKey ( 'globalYear' ) ) ), 'center' );
		}
		if ($objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && (strpos ( $showelements, 'e' ) !== FALSE)) {
			$pdf->addTextWrap ( $xleft, $footer - $deltaline, $xmid + $SectionBarWidth, $fontSizeText, utf8_decode ( ReportSunDown . $_SESSION ['efemerides'] ['sset'] . LangTo . $_SESSION ['efemerides'] ['srise'] . " - " . ReportNautNight . $_SESSION ['efemerides'] ['naute'] . LangTo . $_SESSION ['efemerides'] ['nautb'] . " - " . ReportAstroNight . $_SESSION ['efemerides'] ['astroe'] . LangTo . $_SESSION ['efemerides'] ['astrob'] . " - " . ReportMoonUp . $_SESSION ['efemerides'] ['moon0'] . LangTo . $_SESSION ['efemerides'] ['moon2'] ), 'center' );
		}
		if (strpos ( $showelements, 'p' ) !== FALSE) {
			$pdf->addTextWrap ( $xmid + $SectionBarWidth - $sectionBarSpace - 100, $header, 100, $fontSizeText, utf8_decode ( LangPDFMessage22 . $pagenr ), 'right' );
		}
		if (strpos ( $showelements, 't' ) !== FALSE) {
			$pdf->addTextWrap ( $xleft, $header, $xmid + $SectionBarWidth, 10, utf8_decode ( html_entity_decode ( $this->checkRequestKey ( 'pdfTitle' ) ) ), 'center' );
		}
		if (strpos ( $showelements, 'l' ) !== FALSE) {
			$pdf->line ( $xbase - $sectionBarSpace, $y + $fontSizeText + $sectionBarSpace, $xbase + $SectionBarWidth, $y + $fontSizeText + $sectionBarSpace );
			reset ( $reportdata );
			$deltaymax = 0;
			while ( list ( $key, $dataelement ) = each ( $reportdata ) ) {
				if ($dataelement ['fieldwidth']) {
					$justification = 'left';
					if (strpos ( $dataelement ['fieldstyle'], 'r' ) !== FALSE)
						$justification = 'right';
					if (strpos ( $dataelement ['fieldstyle'], 'c' ) !== FALSE)
						$justification = 'center';
					if (strpos ( $dataelement ['fieldstyle'], 'b' ) !== FALSE)
						$pdf->addText ( 0, 0, $fontSizeText, '<b>' );
					if (strpos ( $dataelement ['fieldstyle'], 'i' ) !== FALSE)
						$pdf->addText ( 0, 0, $fontSizeText, '<i>' );
					$pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $dataelement ['fieldlegend'] ), $justification );
					$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
					if (strpos ( $dataelement ['fieldstyle'], 'b' ) !== FALSE)
						$pdf->addText ( 0, 0, $fontSizeText, '</b>' );
					if (strpos ( $dataelement ['fieldstyle'], 'i' ) !== FALSE)
						$pdf->addText ( 0, 0, $fontSizeText, '</i>' );
				}
			}
			$y -= $deltaline * ($deltaymax);
			$pdf->line ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $xbase + $SectionBarWidth, $y - $sectionBarSpace );
			$y -= ($deltaline + $sectionBarSpace);
		}
		$xbase = $xleft;
	}
	public function newpage(&$y, $bottomsection, $top, $bottom, &$xbase, $xmid, &$pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, $i, $b, $showelements, $reportdata) {
		global $objObserver, $loggedUser, $objLocation, $objInstrument;
		// if($y<$bottomsection)
		{
			if ($i)
				$pdf->addText ( 0, 0, $fontSizeText, '</i>' );
			if ($b)
				$pdf->addText ( 0, 0, $fontSizeText, '</b>' );
			$y = $top;
			if ($xbase == $xmid) {
				if ($pagenr ++) {
					$pdf->newPage ();
					$pdf->addTextWrap ( $xleft, $header, 100, $fontSizeText, utf8_decode ( $theDate ) );
					if ($objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) && (strpos ( $showelements, 'h' ) !== FALSE))
						$pdf->addTextWrap ( $xleft, $footer, $xmid + $SectionBarWidth, $fontSizeText, utf8_decode ( html_entity_decode ( LangPDFMessage19 . $objObserver->getObserverProperty ( $loggedUser, 'name' ) . ' ' . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . ' ' . LangPDFMessage20 . $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' ), 'name' ) . ' ' . LangPDFMessage21 . $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) . LangRistrasetOn . $this->checkSessionKey ( 'globalDay' ) . ' ' . $GLOBALS ['Month' . $this->checkSessionKey ( 'globalMonth' )] . ' ' . $this->checkSessionKey ( 'globalYear' ) ) ), 'center' );
					if ($objObserver->getObserverProperty ( $loggedUser, 'name' ) && $objLocation->getLocationPropertyFromId ( $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' ), 'name' ) && (strpos ( $showelements, 'e' ) !== FALSE))
						$pdf->addTextWrap ( $xleft, $footer - $deltaline, $xmid + $SectionBarWidth, $fontSizeText, utf8_decode ( ReportSunDown . $_SESSION ['efemerides'] ['sset'] . LangTo . $_SESSION ['efemerides'] ['srise'] . " - " . ReportNautNight . $_SESSION ['efemerides'] ['naute'] . LangTo . $_SESSION ['efemerides'] ['nautb'] . " - " . ReportAstroNight . $_SESSION ['efemerides'] ['astroe'] . LangTo . $_SESSION ['efemerides'] ['astrob'] . " - " . ReportMoonUp . $_SESSION ['efemerides'] ['moon0'] . LangTo . $_SESSION ['efemerides'] ['moon2'] ), 'center' );
					if (strpos ( $showelements, 'p' ) !== FALSE) {
						$pdf->addTextWrap ( $xmid + $SectionBarWidth - $sectionBarSpace - 100, $header, 100, $fontSizeText, utf8_decode ( LangPDFMessage22 . $pagenr ), 'right' );
					}
					if (strpos ( $showelements, 't' ) !== FALSE) {
						$pdf->addTextWrap ( $xleft, $header, $xmid + $SectionBarWidth, 10, utf8_decode ( html_entity_decode ( $this->checkRequestKey ( 'pdfTitle' ) ) ), 'center' );
					}
				}
				$xbase = $xleft;
			} else {
				$pdf->setLineStyle ( 0.5 );
				$pdf->line ( ($xbase + $SectionBarWidth + $xmid - $sectionBarSpace) / 2, $top + $fontSizeText, ($xbase + $SectionBarWidth + $xmid - $sectionBarSpace) / 2, $bottom + $fontSizeText );
				$pdf->setLineStyle ( 1 );
				$xbase = $xmid;
			}
			if (strpos ( $showelements, 'l' ) !== FALSE) {
				$pdf->line ( $xbase - $sectionBarSpace, $y + $fontSizeText + $sectionBarSpace, $xbase + $SectionBarWidth, $y + $fontSizeText + $sectionBarSpace );
				reset ( $reportdata );
				$deltaymax = 0;
				while ( list ( $key, $dataelement ) = each ( $reportdata ) ) {
					if ($dataelement ['fieldwidth']) {
						$justification = 'left';
						if (strpos ( $dataelement ['fieldstyle'], 'r' ) !== FALSE)
							$justification = 'right';
						if (strpos ( $dataelement ['fieldstyle'], 'c' ) !== FALSE)
							$justification = 'center';
						if (strpos ( $dataelement ['fieldstyle'], 'b' ) !== FALSE)
							$pdf->addText ( 0, 0, $fontSizeText, '<b>' );
						if (strpos ( $dataelement ['fieldstyle'], 'i' ) !== FALSE)
							$pdf->addText ( 0, 0, $fontSizeText, '<i>' );
						$pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $dataelement ['fieldlegend'] ), $justification );
						$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
						if (strpos ( $dataelement ['fieldstyle'], 'b' ) !== FALSE)
							$pdf->addText ( 0, 0, $fontSizeText, '</b>' );
						if (strpos ( $dataelement ['fieldstyle'], 'i' ) !== FALSE)
							$pdf->addText ( 0, 0, $fontSizeText, '</i>' );
					}
				}
				$y -= $deltaline * ($deltaymax);
				$pdf->line ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $xbase + $SectionBarWidth, $y - $sectionBarSpace );
				$y -= ($deltaline + $sectionBarSpace);
			}
			if ($sort) {
				$y -= $deltalineSection;
				$pdf->rectangle ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $SectionBarWidth, $sectionBarHeight );
				$pdf->addText ( $xbase, $y, $fontSizeSection, utf8_decode ( $GLOBALS [$$sort] ) );
				$y -= $deltaline + $deltalineSection;
			}
			if ($i)
				$pdf->addText ( 0, 0, $fontSizeText, '<i>' );
			if ($b)
				$pdf->addText ( 0, 0, $fontSizeText, '<b>' );
		}
	}
	public function pdfReportPersonalised($reportuser, $reportname, $reportlayout, $result, $sort = '') // Creates a pdf document from an array of objects
{
		global $objReportLayout, $dateformat, $baseURL, $instDir, $objObserver, $loggedUser, $objLocation, $objInstrument, $objPresentations;

		$result = $this->sortResult ( $result );

		$reportdata = $objReportLayout->getReportData ( $reportuser, $reportname, $reportlayout );
		if ($sort == 'objectconstellation')
			$sort = 'con';
		else
			$sort = '';
		$indexlist = array ();

		$pagesize = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'pagesize' );
		$pageorientation = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'pageorientation' );
		$bottom = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'bottom' );
		$top = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'top' );
		$header = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'header' );
		$footer = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'footer' );
		$xleft = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'xleft' );
		$xmid = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'xmid' );
		$fontSizeSection = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'fontSizeSection' );
		$fontSizeText = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'fontSizeText' );
		$sectionBarSpace = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'sectionbarspace' );
		$deltalineSection = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'deltalineSection' );
		$deltaline = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'deltalineExtra' ) + $fontSizeText;
		$deltaobjectline = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'deltaobjectline' );
		$pagenr = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'startpagenumber' );
		$sectionBarHeight = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'sectionBarHeightextra' ) + $fontSizeSection;
		$SectionBarWidth = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'SectionBarWidthbase' ) + $sectionBarSpace;
		$showelements = $objReportLayout->getLayoutFieldPosition ( $reportuser, $reportname, $reportlayout, 'showelements' );

		$pdf = new Cezpdf ( $pagesize, $pageorientation );
		$pdf->selectFont ( $instDir . 'lib/fonts/Helvetica.afm' );

		$actualsort = '';
		$theDate = date ( 'd/m/Y' );
		$this->firstpage ( $y, $bottom, $top, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, '', $showelements, $reportdata );

		while ( list ( $key, $valueA ) = each ( $result ) ) {
			$con = $valueA ['objectconstellation'];
			$deltaymax = 0;
			reset ( $reportdata );
			while ( list ( $key, $dataelement ) = each ( $reportdata ) ) {
				if ($dataelement ['fieldwidth']) {
					if (($dataelement ['fieldname'] == "objectlistdescription")) {
						if (array_key_exists ( 'objectlistdescription', $valueA ) && $valueA ['objectlistdescription'])
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
					} elseif ($dataelement ['fieldname'] == "objectdescription") {
						if (array_key_exists ( 'objectdescription', $valueA ) && ($valueA ['objectdescription'] != ''))
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
					} else
						$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
				}
			}
			$deltaymax ++;
			if (($y - ($deltaline * $deltaymax) < $bottom) && $sort)
				$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, "", "", $showelements, $reportdata );
			elseif (($y - ($deltaline * $deltaymax) < $bottom) && (! ($sort))) {
				$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, "", "", $showelements, $reportdata );
				if (strpos ( $showelements, 's' ) !== FALSE) {
					$pdf->setLineStyle ( 0.5 );
					$pdf->line ( $xbase - $sectionBarSpace, $y + (($deltaline + $deltaobjectline) * .75), $xbase + $SectionBarWidth, $y + (($deltaline + $deltaobjectline) * .75) );
					$pdf->setLineStyle ( 1 );
				}
			} elseif ($sort && ($$sort != $actualsort)) {
				if (($y - ($deltaline * $deltaymax) - $sectionBarSpace - $deltalineSection) < $bottom)
					$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, "", "", $showelements, $reportdata );
				else {
					$y -= $deltalineSection;
					$pdf->rectangle ( $xbase - $sectionBarSpace, $y - $sectionBarSpace, $SectionBarWidth, $sectionBarHeight );
					$pdf->addText ( $xbase, $y, $fontSizeSection, utf8_decode ( $GLOBALS [$$sort] ) );
					$y -= $deltaline + $deltalineSection;
				}
				$indexlist [$$sort] = $pagenr;
			} else if (strpos ( $showelements, 's' ) !== FALSE) {
				$pdf->setLineStyle ( 0.5 );
				$pdf->line ( $xbase - $sectionBarSpace, $y + (($deltaline + $deltaobjectline) * .75), $xbase + $SectionBarWidth, $y + (($deltaline + $deltaobjectline) * .75) );
				$pdf->setLineStyle ( 1 );
			}
			reset ( $reportdata );
			$deltaymax = 0;
			while ( list ( $key, $dataelement ) = each ( $reportdata ) ) {
				if ($dataelement ['fieldwidth']) {
					if ($y - ($deltaline * $dataelement ['fieldline']) < $bottom) {
						$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, "", "", $showelements, $reportdata );
					}
					$justification = 'left';
					$i = '';
					$b = '';
					if (strpos ( $dataelement ['fieldstyle'], 'r' ) !== FALSE)
						$justification = 'right';
					if (strpos ( $dataelement ['fieldstyle'], 'c' ) !== FALSE)
						$justification = 'center';
					if (strpos ( $dataelement ['fieldstyle'], 'b' ) !== FALSE) {
						$b = "<b>";
						$pdf->addText ( 0, 0, $fontSizeText, '<b>' );
					}
					if (strpos ( $dataelement ['fieldstyle'], 'i' ) !== FALSE) {
						$i = '<i>';
						$pdf->addText ( 0, 0, $fontSizeText, '<i>' );
					}
					if ($dataelement ['fieldname'] == "showname") {
						if ($valueA [$dataelement ['fieldname']]) {
							$pdf->addText ( 0, 0, $fontSizeText, utf8_decode ( '<c:alink:' . $baseURL . 'index.php?indexAction=detail_object&amp;object=' . urlencode ( $valueA ['objectname'] ) ) . '>' );
							$pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $dataelement ['fieldafter'] . html_entity_decode ( $valueA [$dataelement ['fieldname']] ) . $dataelement ['fieldafter'] ), $justification );
							$pdf->addText ( 0, 0, $fontSizeText, '</c:alink>' );
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
						}
					} else if ($dataelement ['fieldname'] == "objectuseratlaspage") {
						$pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $dataelement ['fieldbefore'] . html_entity_decode ( $valueA [($loggedUser ? $objObserver->getObserverProperty ( $loggedUser, 'standardAtlasCode', 'urano' ) : 'urano')] ) . $dataelement ['fieldafter'] ), $justification );
						$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
					} else if (($dataelement ['fieldname'] == "objectlistdescription")) {
						if (array_key_exists ( 'objectlistdescription', $valueA ) && ($valueA ['objectlistdescription'] != '')) {
							$theText = $dataelement ['fieldbefore'] . html_entity_decode ( $objPresentations->br2nl ( $valueA ['objectlistdescription'] ) ) . $dataelement ['fieldafter'];
							$theText = $pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $theText ), $justification );
							while ( $theText ) {
								$y -= $deltaline;
								if ($y - ($deltaline * $dataelement ['fieldline']) < $bottom) {
									$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, $i, $b, $showelements, $reportdata );
									$y += ($deltaline * $dataelement ['fieldline']);
								}
								$theText = $pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $theText ), $justification );
							}
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
						}
					} elseif ($dataelement ['fieldname'] == "objectdescription") {
						if (array_key_exists ( 'objectlistdescription', $valueA ) && ($valueA ['objectlistdescription'] != '')) {
							$theText = $dataelement ['fieldbefore'] . html_entity_decode ( $objPresentations->br2nl ( $valueA ['objectlistdescription'] ) ) . $dataelement ['fieldafter'];
							$theText = $pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $theText ), $justification );
							while ( $theText ) {
								$y -= $deltaline;
								if ($y - ($deltaline * $dataelement ['fieldline']) < $bottom) {
									$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, $i, $b, $showelements, $reportdata );
									$y += ($deltaline * $dataelement ['fieldline']);
								}
								$theText = $pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $theText ), $justification );
							}
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
						} else if (array_key_exists ( 'objectdescription', $valueA ) && ($valueA ['objectdescription'] != '')) {
							$theText = $dataelement ['fieldbefore'] . html_entity_decode ( $objPresentations->br2nl ( $valueA ['objectdescription'] ) ) . $dataelement ['fieldafter'];
							$theText = $pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $theText ), $justification );
							while ( $theText ) {
								$y -= $deltaline;
								if ($y - ($deltaline * $dataelement ['fieldline']) < $bottom) {
									$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, $sort, $con, $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, $i, $b, $showelements, $reportdata );
									$y += ($deltaline * $dataelement ['fieldline']);
								}
								$theText = $pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( $theText ), $justification );
							}
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
						}
					} else {
						if (trim ( $valueA [$dataelement ['fieldname']] ) != '') {
							$pdf->addTextWrap ( $xbase + $dataelement ['fieldposition'], $y - ($deltaline * $dataelement ['fieldline']), $dataelement ['fieldwidth'], $fontSizeText, utf8_decode ( html_entity_decode ( $dataelement ['fieldbefore'] . $valueA [$dataelement ['fieldname']] . $dataelement ['fieldafter'] ) ), $justification );
							$deltaymax = max ( $deltaymax, $dataelement ['fieldline'] );
						}
					}
					if (strpos ( $dataelement ['fieldstyle'], 'b' ) !== FALSE)
						$pdf->addText ( 0, 0, $fontSizeText, '</b>' );
					if (strpos ( $dataelement ['fieldstyle'], 'i' ) !== FALSE)
						$pdf->addText ( 0, 0, $fontSizeText, '</i>' );
				}
			}
			$y -= $deltaline * ($deltaymax);
			$y -= ($deltaline + $deltaobjectline);
			if ($sort)
				$actualsort = $$sort;
		}
		if ((strpos ( $showelements, 'i' ) !== FALSE) && (count ( $indexlist ) > 0) && ($sort)) {
			$base = $xmid;
			$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, '', '', $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, "", "", $showelements, $reportdata );
			$pdf->setLineStyle ( 0.5 );
			$y = $top;
			while ( list ( $key, $value ) = each ( $indexlist ) ) {
				$pdf->line ( $xbase - $sectionBarSpace, $y + (($deltaline + $deltaobjectline) * .75), $xbase + $SectionBarWidth, $y + (($deltaline + $deltaobjectline) * .75) );
				$pdf->addTextWrap ( $xbase, $y, 50, $fontSizeText, utf8_decode ( $key ), 'left' );
				$pdf->addTextWrap ( $xbase + $SectionBarWidth - $sectionBarSpace - 50, $y, 50, $fontSizeText, utf8_decode ( trim ( $value ) ), 'right' );

				$y -= ($deltaline + $deltaobjectline);
				if (($y - ($deltaline + $deltaobjectline)) < $bottom) {
					$this->newpage ( $y, $bottom, $top, $bottom, $xbase, $xmid, $pagenr, $pdf, $xleft, $header, $fontSizeText, $theDate, $footer, $SectionBarWidth, $sectionBarSpace, '', '', $deltalineSection, $sectionBarHeight, $fontSizeSection, $deltaline, $deltalineSection, "", "", $showelements, $reportdata );
					$pdf->setLineStyle ( 0.5 );
				}
			}
		}
		$pdf->Stream ();
	}
	public function pdfObservations($result) // Creates a pdf document from an array of observations
{
		global $loggedUser, $dateformat, $instDir, $objObservation, $objObserver, $objInstrument, $objLocation, $objPresentations, $objObject, $objFilter, $objEyepiece, $objLens;
		$result = $this->sortResult ( $result );
		$pdf = new Cezpdf ( 'a4', 'portrait' );
		$pdf->ezStartPageNumbers ( 300, 30, 10 );
		$pdf->selectFont ( $instDir . 'lib/fonts/Helvetica.afm' );
		$pdf->ezText ( utf8_decode ( html_entity_decode ( $_GET ['pdfTitle'] ) ) . "\n" );
		$i = 0;
		while ( list ( $key, $value ) = each ( $result ) ) {
			if ($i ++ > 0)
				$pdf->ezNewPage ();
			$obs = $objObservation->getAllInfoDsObservation ( $value ['observationid'] );
			$object = $objObject->getAllInfoDsObject ( $obs ['objectname'] );
			if ($loggedUser && ($objObserver->getObserverProperty ( $loggedUser, 'UT' )))
				$date = sscanf ( $obs ["date"], "%4d%2d%2d" );
			else
				$date = sscanf ( $obs ["localdate"], "%4d%2d%2d" );
			if ($obs ['seeing'] > - 1) {
				$seeing = true;
			} else {
				$seeing = false;
			}
			$formattedDate = date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );
			$temp = array (
					"Name" => html_entity_decode ( LangPDFMessage1 ) . " : " . $obs ['objectname'],
					"altname" => html_entity_decode ( LangPDFMessage2 ) . " : " . $object ["altname"],
					"type" => $GLOBALS [$object ['type']] . html_entity_decode ( LangPDFMessage12 ) . $GLOBALS [$object ['con']],
					"visibility" => (($obs ['visibility']) ? (html_entity_decode ( LangViewObservationField22 ) . " : " . $GLOBALS ['Visibility' . $obs ['visibility']]) : ''),
					"seeing" => (($seeing) ? (LangViewObservationField6 . " : " . $GLOBALS ['Seeing' . $obs ['seeing']]) : ''),
					"limmag" => (($obs ['limmag']) ? (LangViewObservationField7 . " : " . $obs ['limmag']) : ''),
					"filter" => (($obs ['filterid']) ? (LangViewObservationField31 . " : " . $objFilter->getFilterPropertyFromId ( $obs ['filterid'], 'name' )) : ''),
					"eyepiece" => (($obs ['eyepieceid']) ? (LangViewObservationField30 . " : " . $objEyepiece->getEyepiecePropertyFromId ( $obs ['eyepieceid'], 'name' )) : ''),
					"lens" => (($obs ['lensid']) ? (LangViewObservationField32 . " : " . $objLens->getLensPropertyFromId ( $obs ['lensid'], 'name' )) : ''),
					"observer" => html_entity_decode ( LangPDFMessage13 ) . $objObserver->getObserverProperty ( $obs ['observerid'], 'firstname' ) . " " . $objObserver->getObserverProperty ( $obs ['observerid'], 'name' ) . html_entity_decode ( LangPDFMessage14 ) . $formattedDate,
					"instrument" => html_entity_decode ( LangPDFMessage11 ) . " : " . $objInstrument->getInstrumentPropertyFromId ( $obs ['instrumentid'], 'name' ),
					"location" => html_entity_decode ( LangPDFMessage10 ) . " : " . $objLocation->getLocationPropertyFromId ( $obs ['locationid'], 'name' ),
					"description" => $objPresentations->br2nl ( html_entity_decode ( $obs ['description'] ) ),
					"desc" => html_entity_decode ( LangPDFMessage15 )
			);
			$obs1 [] = $temp;
			$nm = $obs ['objectname'];
			if ($object ["altname"])
				$nm = $nm . " (" . $object ["altname"] . ")";
			$pdf->ezText ( $nm, "14" );
			$tmp = array (
					array (
							"type" => $temp ["type"]
					)
			);
			$pdf->ezTable ( $tmp, array (
					"type" => utf8_decode ( html_entity_decode ( LangPDFMessage5 ) )
			), "", array (
					"width" => "500",
					"showHeadings" => "0",
					"showLines" => "0",
					"shaded" => "0"
			) );
			$tmp = array (
					array (
							"location" => $temp ["location"],
							"instrument" => $temp ["instrument"]
					)
			);
			$pdf->ezTable ( $tmp, array (
					"location" => utf8_decode ( html_entity_decode ( LangPDFMessage1 ) ),
					"instrument" => utf8_decode ( html_entity_decode ( LangPDFMessage2 ) )
			), "", array (
					"width" => "500",
					"showHeadings" => "0",
					"showLines" => "0",
					"shaded" => "0"
			) );
			$tmp = array (
					array (
							"eyepiece" => $temp ["eyepiece"]
					)
			);
			if ($obs ['eyepieceid'])
				$pdf->ezTable ( $tmp, array (
						"eyepiece" => "test"
				), "", array (
						"width" => "500",
						"showHeadings" => "0",
						"showLines" => "0",
						"shaded" => "0"
				) );
			$tmp = array (
					array (
							"filter" => $temp ["filter"]
					)
			);
			if ($obs ['filterid'])
				$pdf->ezTable ( $tmp, array (
						"filter" => "test"
				), "", array (
						"width" => "500",
						"showHeadings" => "0",
						"showLines" => "0",
						"shaded" => "0"
				) );
			$tmp = array (
					array (
							"lens" => $temp ["lens"]
					)
			);
			if ($obs ['lensid'])
				$pdf->ezTable ( $tmp, array (
						"lens" => "test"
				), "", array (
						"width" => "500",
						"showHeadings" => "0",
						"showLines" => "0",
						"shaded" => "0"
				) );
			$tmp = array (
					array (
							"seeing" => $temp ["seeing"]
					)
			);
			if ($seeing)
				$pdf->ezTable ( $tmp, array (
						"seeing" => "test"
				), "", array (
						"width" => "500",
						"showHeadings" => "0",
						"showLines" => "0",
						"shaded" => "0"
				) );
			$tmp = array (
					array (
							"limmag" => $temp ["limmag"]
					)
			);
			if ($obs ['limmag'])
				$pdf->ezTable ( $tmp, array (
						"limmag" => "test"
				), "", array (
						"width" => "500",
						"showHeadings" => "0",
						"showLines" => "0",
						"shaded" => "0"
				) );
			$tmp = array (
					array (
							"visibility" => $temp ["visibility"]
					)
			);
			if ($obs ['visibility'])
				$pdf->ezTable ( $tmp, array (
						"visibility" => "test"
				), "", array (
						"width" => "500",
						"showHeadings" => "0",
						"showLines" => "0",
						"shaded" => "0"
				) );
			$tmp = array (
					array (
							"observer" => $temp ["observer"]
					)
			);
			$pdf->ezTable ( $tmp, array (
					"observer" => utf8_decode ( html_entity_decode ( LangPDFMessage1 ) )
			), "", array (
					"width" => "500",
					"showHeadings" => "0",
					"showLines" => "0",
					"shaded" => "0"
			) );
			$pdf->ezText ( utf8_decode ( LangPDFMessage15 ), "12" );
			$pdf->ezText ( "" );
			$tmp = array (
					array (
							"description" => $temp ["description"]
					)
			);
			$pdf->ezTable ( $tmp, array (
					"description" => utf8_decode ( html_entity_decode ( LangPDFMessage1 ) )
			), "", array (
					"width" => "500",
					"showHeadings" => "0",
					"showLines" => "0",
					"shaded" => "0"
			) );
			if ($objObservation->getDsObservationProperty ( $value ['observationid'], 'hasDrawing' )) {
				$pdf->ezText ( "" );
				$pdf->ezImage ( $instDir . "deepsky/drawings/" . $value ['observationid'] . ".jpg", 0, 500, "none", "left" );
			}
			$pdf->ezText ( "" );
		}
		$pdf->ezStream ();
	}
	public function recordsetSort(array $data /*$name, $order, $mode*/)
  {
		$_argList = func_get_args ();
		$_data = array_shift ( $_argList );
		if (empty ( $_data ))
			return $_data;
		$_max = count ( $_argList );
		$_params = array ();
		$_cols = array ();
		$_rules = array ();
		for($_i = 0; $_i < $_max; $_i += 3) {
			$_name = ( string ) $_argList [$_i];
			if (! in_array ( $_name, array_keys ( current ( $_data ) ) ))
				continue;
			if (! isset ( $_argList [($_i + 1)] ) || is_string ( $_argList [($_i + 1)] )) {
				$_order = SORT_ASC;
				$_mode = SORT_REGULAR;
				$_i -= 2;
			} else if (3 > $_argList [($_i + 1)]) {
				$_order = SORT_ASC;
				$_mode = $_argList [($_i + 1)];
				$_i --;
			} else {
				$_order = $_argList [($_i + 1)] == SORT_ASC ? SORT_ASC : SORT_DESC;
				if (! isset ( $_argList [($_i + 2)] ) || is_string ( $_argList [($_i + 2)] )) {
					$_mode = SORT_REGULAR;
					$_i --;
				} else
					$_mode = $_argList [($_i + 2)];
			}
			$_mode = (($_mode != SORT_NUMERIC) ? (($_argList [($_i + 2)] != SORT_STRING) ? SORT_REGULAR : SORT_STRING) : SORT_NUMERIC);
			$_rules [] = array (
					'name' => $_name,
					'order' => $_order,
					'mode' => $_mode
			);
		}
		foreach ( $_data as $_k => $_row ) {
			foreach ( $_rules as $_rule ) {
				if (! isset ( $_cols [$_rule ['name']] )) {
					$_cols [$_rule ['name']] = array ();
					$_params [] = &$_cols [$_rule ['name']];
					$_params [] = $_rule ['order'];
					$_params [] = $_rule ['mode'];
				}
				$_cols [$_rule ['name']] [$_k] = strtolower ( $_row [$_rule ['name']] );
			}
		}
		$_params [] = &$_data;
		call_user_func_array ( 'array_multisort', $_params );
		return $_data;
	}
	public function removeFromLink($link, $value) {
		return (($a = strpos ( $link, $value )) ? (($b = strpos ( $link, '&', $a + 1 )) ? substr ( $link, 0, $a ) . substr ( $link, $b ) : substr ( $link, 0, $a - 5 )) : $link);
	}
	public function rssObservations() // Creates an rss feed for DeepskyLog
{
		global $objObservation, $objInstrument, $objLocation, $objPresentations, $objObserver, $baseURL, $objUtil;
		$dom = new DomDocument ( '1.0', 'US-ASCII' );

		// add root fcga -> The header
		$rssInfo = $dom->createElement ( 'rss' );
		$rssDom = $dom->appendChild ( $rssInfo );

		$attr = $dom->createAttribute ( "version" );
		$rssInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "2.0" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "xmlns:content" );
		$rssInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "http://purl.org/rss/1.0/modules/content/" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "xmlns:dc" );
		$rssInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "http://purl.org/dc/elements/1.1/" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "xmlns:atom" );
		$rssInfo->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "http://www.w3.org/2005/Atom" );
		$attr->appendChild ( $attrText );

		// add root - <channel>
		$channelDom = $rssDom->appendChild ( $dom->createElement ( 'channel' ) );

		// add root - <channel> - <title>
		$titleDom = $channelDom->appendChild ( $dom->createElement ( 'title' ) );
		$titleDom->appendChild ( $dom->createTextNode ( "DeepskyLog" ) );

		// add root - <channel> - <description>
		$descDom = $channelDom->appendChild ( $dom->createElement ( 'description' ) );
		$descDom->appendChild ( $dom->createTextNode ( "DeepskyLog - visual deepsky and comets observations" ) );

		// add root - <channel> - <atom>
		$atomDom = $channelDom->appendChild ( $dom->createElement ( 'atom:link' ) );

		$attr = $dom->createAttribute ( "href" );
		$atomDom->appendChild ( $attr );

		$attrText = $dom->createTextNode ( $baseURL . "observations.rss" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "rel" );
		$atomDom->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "self" );
		$attr->appendChild ( $attrText );

		$attr = $dom->createAttribute ( "type" );
		$atomDom->appendChild ( $attr );

		$attrText = $dom->createTextNode ( "application/rss+xml" );
		$attr->appendChild ( $attrText );

		// add root - <channel> - <link>
		$linkDom = $channelDom->appendChild ( $dom->createElement ( 'link' ) );
		$linkDom->appendChild ( $dom->createTextNode ( "http://www.deepskylog.org/" ) );

		$theDate = date ( 'r' );

		// add root - <channel> - <link>
		$lbdDom = $channelDom->appendChild ( $dom->createElement ( 'lastBuildDate' ) );
		$lbdDom->appendChild ( $dom->createTextNode ( $theDate ) );

		// Get the new deepsky observations of the last month
		$theDate = date ( 'Ymd', strtotime ( '-1 month' ) );

		$_GET ['minyear'] = substr ( $theDate, 0, 4 );

		$_GET ['minmonth'] = substr ( $theDate, 4, 2 );

		$_GET ['minday'] = substr ( $theDate, 6, 2 );

		$query = array (
				"catalog" => '%',
				"mindate" => $objUtil->checkGetDate ( 'minyear', 'minmonth', 'minday' )
		);

		$result = $objObservation->getObservationFromQuery ( $query, 'A' );

		while ( list ( $key, $value ) = each ( $result ) ) {
			// add root - <channel> - <item>
			$itemDom = $channelDom->appendChild ( $dom->createElement ( 'item' ) );

			$titleDom = $itemDom->appendChild ( $dom->createElement ( 'title' ) );
			$titleDom->appendChild ( $dom->createTextNode ( $value ['observername'] . " : " . $value ['objectname'] . " with " . htmlspecialchars_decode ( $objInstrument->getInstrumentPropertyFromId ( $value ['instrumentid'], 'name' ) ) . " from " . $objLocation->getLocationPropertyFromId ( $objObservation->getDsObservationProperty ( $value ['observationid'], 'locationid' ), 'name' ) ) );
			$linkDom = $itemDom->appendChild ( $dom->createElement ( 'link' ) );
			$linkDom->appendChild ( $dom->createCDATASection ( $baseURL . "index.php?indexAction=detail_observation&observation=" . $value ['observationid'] . "&QobsKey=0&dalm=D" ) );

			$descDom = $itemDom->appendChild ( $dom->createElement ( 'description' ) );
			$descDom->appendChild ( $dom->createCDATASection ( $objPresentations->br2nl ( utf8_encode ( $value ['observationdescription'] ) ) ) );

			$authorDom = $itemDom->appendChild ( $dom->createElement ( 'dc:creator' ) );
			$authorDom->appendChild ( $dom->createCDATASection ( $value ['observername'] ) );

			$guidDom = $itemDom->appendChild ( $dom->createElement ( 'guid' ) );
			$guidDom->appendChild ( $dom->createTextNode ( "deepsky" . $value ['observationid'] ) );

			$attr = $dom->createAttribute ( "isPermaLink" );
			$guidDom->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "false" );
			$attr->appendChild ( $attrText );

			$pubDateDom = $itemDom->appendChild ( $dom->createElement ( 'pubDate' ) );

			date_default_timezone_set ( 'UTC' );

			$time = - 999;

			$obs = $objObservation->getAllInfoDsObservation ( $value ['observationid'] );
			$time = $obs ['time'];

			if ($time >= "0") {
				$hour = ( int ) ($time / 100);
				$minute = $time - (100 * $hour);
			} else {
				$hour = 0;
				$minute = 0;
			}
			$date = $value ['observationdate'];

			$year = substr ( $date, 0, 4 );
			$month = substr ( $date, 4, 2 );
			$day = substr ( $date, 6, 2 );

			$pubDateDom->appendChild ( $dom->createTextNode ( date ( "r", mktime ( $hour, $minute, 0, $month, $day, $year ) ) ) );
		}

		include_once "cometobjects.php";
		include_once "observers.php";
		include_once "instruments.php";
		include_once "locations.php";
		include_once "cometobservations.php";
		include_once "icqmethod.php";
		include_once "icqreferencekey.php";
		global $instDir, $objCometObject;
		$objects = new CometObjects ();
		$observer = new Observers ();
		$instrument = new Instruments ();
		$observation = new CometObservations ();
		$location = new Locations ();
		$util = $this;
		$ICQMETHODS = new ICQMETHOD ();
		$ICQREFERENCEKEYS = new ICQREFERENCEKEY ();

		$cometsResult = $observation->getObservationFromQuery ( $query );

		while ( list ( $key, $value ) = each ( $cometsResult ) ) {
			$objectname = $objCometObject->getName ( $observation->getObjectId ( $value ) );

			// add root - <channel> - <item>
			$itemDom = $channelDom->appendChild ( $dom->createElement ( 'item' ) );

			$title = htmlspecialchars_decode ( $objectname );

			// Location and instrument
			if ($observation->getLocationId ( $value ) != 0 && $observation->getLocationId ( $value ) != 1) {
				$title = $title . " from " . htmlspecialchars_decode ( $location->getLocationPropertyFromId ( $observation->getLocationId ( $value ), 'name' ) );
			}

			if ($observation->getInstrumentId ( $value ) != 0) {
				$title = $title . " with " . htmlspecialchars_decode ( $instrument->getInstrumentPropertyFromId ( $observation->getInstrumentId ( $value ), 'name' ) );
			}

			$titleDom = $itemDom->appendChild ( $dom->createElement ( 'title' ) );
			$titleDom->appendChild ( $dom->createTextNode ( $title ) );
			$linkDom = $itemDom->appendChild ( $dom->createElement ( 'link' ) );
			$linkDom->appendChild ( $dom->createCDATASection ( $baseURL . "index.php?indexAction=comets_detail_observation&observation=" . $value ) );

			// Description
			$description = $observation->getDescription ( $value );

			if (strcmp ( $description, "" ) != 0) {
				$descDom = $itemDom->appendChild ( $dom->createElement ( 'description' ) );
				$descDom->appendChild ( $dom->createCDATASection ( $objPresentations->br2nl ( utf8_encode ( $description ) ) ) );
			} else {
				$descDom = $itemDom->appendChild ( $dom->createElement ( 'description' ) );
				$descDom->appendChild ( $dom->createCDATASection ( "" ) );
			}

			$observerid = $observation->getObserverId ( $value );
			$observername = $observer->getObserverProperty ( $observerid, 'firstname' ) . " " . $observer->getObserverProperty ( $observerid, 'name' );

			$authorDom = $itemDom->appendChild ( $dom->createElement ( 'dc:creator' ) );
			$authorDom->appendChild ( $dom->createCDATASection ( $observername ) );

			$guidDom = $itemDom->appendChild ( $dom->createElement ( 'guid' ) );
			$guidDom->appendChild ( $dom->createTextNode ( "comet" . $value ) );

			$attr = $dom->createAttribute ( "isPermaLink" );
			$guidDom->appendChild ( $attr );

			$attrText = $dom->createTextNode ( "false" );
			$attr->appendChild ( $attrText );

			$pubDateDom = $itemDom->appendChild ( $dom->createElement ( 'pubDate' ) );

			date_default_timezone_set ( 'UTC' );

			$date = sscanf ( $observation->getLocalDate ( $value ), "%4d%2d%2d" );
			$time = $observation->getLocalTime ( $value );

			$hour = ( int ) ($time / 100);
			$minute = $time - $hour * 100;

			$pubDateDom->appendChild ( $dom->createTextNode ( date ( "r", mktime ( $hour, $minute, 0, $date [1], $date [2], $date [0] ) ) ) );
		}

		// generate xml
		$dom->formatOutput = true; // set the formatOutput attribute of
		                           // domDocument to true
		                           // save XML as string or file
		$test1 = $dom->saveXML (); // put string in test1

		print $test1;
	}
	private function utilitiesCheckIndexActionAdmin($action, $includefile) {
		if (array_key_exists ( 'indexAction', $_REQUEST ) && ($_REQUEST ['indexAction'] == $action) && array_key_exists ( 'admin', $_SESSION ) && ($_SESSION ['admin'] == "yes"))
			return $includefile;
	}
	private function utilitiesCheckIndexActionAll($action, $includefile) {
		if (array_key_exists ( 'indexAction', $_GET ) && ($_GET ['indexAction'] == $action))
			return $includefile;
	}
	private function utilitiesCheckIndexActionDSquickPick() {
		global $objObject, $entryMessage;
		if ($this->checkGetKey ( 'indexAction' ) == 'quickpick') {
			if ($this->checkGetKey ( 'object' )) {
				if ($temp = $objObject->getExactDsObject ( $_GET ['object'] )) {
					$_GET ['object'] = $temp;
					if (array_key_exists ( 'searchObservationsQuickPick', $_GET ))
						return 'deepsky/content/selected_observations.php';
					elseif (array_key_exists ( 'newObservationQuickPick', $_GET ))
						return 'deepsky/content/new_observation.php';
					else {
						return 'deepsky/content/view_object.php';
					}
				} else {
					$_GET ['object'] = ucwords ( trim ( $_GET ['object'] ) );
					if (array_key_exists ( 'searchObservationsQuickPick', $_GET ))
						return 'deepsky/content/selected_observations.php';
					elseif (array_key_exists ( 'newObservationQuickPick', $_GET ))
						return 'deepsky/content/setup_objects_query.php';
					else
						return 'deepsky/content/setup_objects_query.php';
				}
			} else {
				if (array_key_exists ( 'searchObservationsQuickPick', $_GET ))
					return 'deepsky/content/setup_observations_query.php';
				elseif (array_key_exists ( 'newObservationQuickPick', $_GET ))
					return 'deepsky/content/new_observation.php';
				else
					return 'deepsky/content/setup_objects_query.php';
			}
		}
	}
	public function utilitiesDispatchIndexAction() {
		if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_csv', 'deepsky/content/new_observationcsv.php' )))
			if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_xml', 'deepsky/content/new_observationxml.php' )))
				if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_object', 'deepsky/content/new_object.php' )))
					if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_observation', 'deepsky/content/new_observation.php' )))
						if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_object', 'deepsky/content/view_object.php' )))
							if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_observation', 'deepsky/content/view_observation.php' )))
								if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'view_catalogs', 'deepsky/content/view_catalogs.php' )))
									if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'atlaspage', 'deepsky/content/dsatlas.php' )))
										if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'downloadAstroImageCatalogs', 'deepsky/content/downloadastroimagecatalogs.php' )))
											if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'import_csv_list', 'deepsky/content/new_listdatacsv.php' )))
												if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'listaction', 'deepsky/content/tolist.php' )))
													if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAdmin ( 'manage_csv_object', 'deepsky/content/manage_objects_csv.php' )))
														if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'query_objects', 'deepsky/content/setup_objects_query.php' )))
															if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'query_observations', 'deepsky/content/setup_observations_query.php' )))
																if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'rank_objects', 'deepsky/content/top_objects.php' )))
																	if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'rank_observers', 'deepsky/content/top_observers.php' )))
																		if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'view_lenses', 'common/content/view_lenses.php' )))
																			if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAdmin ( 'overview_lenses', 'common/content/overview_lenses.php' )))
																				if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'result_query_objects', 'deepsky/content/selected_objects.php' )))
																					if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'result_selected_observations', 'deepsky/content/selected_observations.php' )))
																						if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'result_selected_sessions', 'deepsky/content/selected_sessions.php' )))
																							if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'view_observer_catalog', 'deepsky/content/details_observer_catalog.php' )))
																								if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'objectsSets', 'common/content/objectsSets.php' )))
																									if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'view_atlaspages', 'common/content/atlasPages.php' )))
																										if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'change_account', 'common/content/change_account.php' )))
																											if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'adapt_eyepiece', 'common/content/change_eyepiece.php' )))
																												if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'adapt_filter', 'common/content/change_filter.php' )))
																													if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'adapt_instrument', 'common/content/change_instrument.php' )))
																														if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'adapt_lens', 'common/content/change_lens.php' )))
																															if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'adapt_site', 'common/content/change_site.php' )))
																																if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'adapt_session', 'deepsky/content/change_session.php' )))
																																	if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_session', 'deepsky/content/new_session.php' )))
																																		if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_eyepiece', 'common/content/new_eyepiece.php' )))
																																			if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_filter', 'common/content/new_filter.php' )))
																																				if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_instrument', 'common/content/new_instrument.php' )))
																																					if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_lens', 'common/content/new_lens.php' )))
																																						if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_sites', 'common/content/locations.php' )))
																																							if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_lists', 'deepsky/content/view_list.php' )))
																																								if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_eyepiece', 'common/content/change_eyepiece.php' )))
																																									if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_filter', 'common/content/change_filter.php' )))
																																										if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_instrument', 'common/content/change_instrument.php' )))
																																											if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_lens', 'common/content/change_lens.php' )))
																																												if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_location', 'common/content/change_site.php' )))
																																													if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_observer', 'common/content/view_observer.php' )))
																																														if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_observer1', 'common/content/view_observer1.php' )))
																																															if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_observer2', 'common/content/view_observer2.php' )))
																																																if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'detail_observer3', 'common/content/view_observer3.php' )))
																																																	if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'message', 'common/content/message.php' )))
																																																		if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'reportsLayout', 'common/content/reportslayout.php' )))
																																																			if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'search_sites', 'common/content/search_locations.php' )))
																																																				if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'site_result', 'common/content/getLocation.php' )))
																																																					if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'subscribe', 'common/content/register.php' )))
																																																						if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'overview_eyepieces', 'common/content/overview_eyepieces.php' )))
																																																							if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_eyepieces', 'common/content/view_eyepieces.php' )))
																																																								if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_filters', 'common/content/view_filters.php' )))
																																																									if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'overview_filters', 'common/content/overview_filters.php' )))
																																																										if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_instruments', 'common/content/view_instruments.php' )))
																																																											if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'overview_instruments', 'common/content/overview_instruments.php' )))
																																																												if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_lenses', 'common/content/overview_lenses.php' )))
																																																													if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'overview_locations', 'common/content/overview_locations.php' )))
																																																														if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'add_location', 'common/content/new_location.php' )))
																																																															if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'view_observers', 'common/content/overview_observers.php' )))
																																																																if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'show_messages', 'common/content/messages.php' )))
																																																																	if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'view_message', 'common/content/view_message.php' )))
																																																																		if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'new_message', 'common/content/new_message.php' )))
																																																																			if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'admin_check_objects', 'deepsky/control/admincheckobjects.php' )))

																																																																				if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_all_observations', 'comets/content/overview_observations.php' )))
																																																																					if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_detail_object', 'comets/content/view_object.php' )))
																																																																						if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_detail_observation', 'comets/content/view_observation.php' )))
																																																																							if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'comets_adapt_observation', 'comets/content/new_observation.php' )))
																																																																								if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'comets_add_observation', 'comets/content/new_observation.php' )))
																																																																									if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_result_query_observations', 'comets/content/selected_observations.php' )))
																																																																										if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_detail_observation', 'comets/content/view_observation.php' )))
																																																																											if (! ($indexActionInclude = $this->utilitiesCheckIndexActionMember ( 'comets_add_object', 'comets/content/new_object.php' )))
																																																																												if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_detail_object', 'comets/content/view_object.php' )))
																																																																													if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_view_objects', 'comets/content/overview_objects.php' )))
																																																																														if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_all_observations', 'comets/content/overview_observations.php' )))
																																																																															if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_result_query_objects', 'comets/content/execute_query_objects.php' )))
																																																																																if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_result_selected_observations', 'comets/content/selected_observations2.php' )))
																																																																																	if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_rank_observers', 'comets/content/top_observers.php' )))
																																																																																		if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_rank_objects', 'comets/content/top_objects.php' )))
																																																																																			if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'comets_query_observations', 'comets/content/setup_observations_query.php' )))
																																																																																				if (! ($indexActionInclude = $this->utilitiesCheckIndexActionall ( 'comets_query_objects', 'comets/content/setup_objects_query.php' )))
																																																																																					if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'main', 'deepsky/content/main.php' )))
																																																																																						if (! ($indexActionInclude = $this->utilitiesCheckIndexActionAll ( 'downloadForms', 'common/content/downloadForms.php' )))
																																																																																							if (! ($indexActionInclude = $this->utilitiesCheckIndexActionDSquickPick ()))
																																																																																								$indexActionInclude = $this->utilitiesGetIndexActionDefaultAction ();
		return $indexActionInclude;
	}
	private function utilitiesGetIndexActionDefaultAction() {
		global $lastReadObservation, $loggedUser, $objObserver;
		if ($_SESSION ['module'] == 'deepsky') {
			$_GET ['catalog'] = '%';
			$theDate = date ( 'Ymd', strtotime ( '-1 month' ) );
			$_GET ['minyear'] = substr ( $theDate, 0, 4 );
			$_GET ['minmonth'] = substr ( $theDate, 4, 2 );
			$_GET ['minday'] = substr ( $theDate, 6, 2 );
			$lastReadObservation = ($loggedUser ? $objObserver->getLastReadObservation ( $loggedUser ) : - 1);
			return 'deepsky/content/main.php';
		} else if ($_SESSION ['module'] == 'comets') {
			$theDate = date ( 'Ymd', strtotime ( '-1 year' ) );
			$_GET ['minyear'] = substr ( $theDate, 0, 4 );
			$_GET ['minmonth'] = substr ( $theDate, 4, 2 );
			$_GET ['minday'] = substr ( $theDate, 6, 2 );
			$_GET ['observer'] = '';
			$_GET ['instrument'] = '';
			$_GET ['site'] = '';
			$_GET ['maxyear'] = '';
			$_GET ['maxmonth'] = '';
			$_GET ['maxday'] = '';
			$_GET ['mindiameter'] = '';
			$_GET ['maxdiameter'] = '';
			$_GET ['mindiameterunits'] = '';
			$_GET ['maxdiameterunits'] = '';
			$_GET ['maxmag'] = '';
			$_GET ['minmag'] = '';
			$_GET ['description'] = '';
			$_GET ['object'] = '';
			$_GET ['mintail'] = '';
			$_GET ['maxtail'] = '';
			$_GET ['mincoma'] = '';
			$_GET ['maxcoma'] = '';
			$_GET ['mindc'] = '';
			$_GET ['maxdc'] = '';
			return 'comets/content/selected_observations2.php';
		}
	}
	private function utilitiesCheckIndexActionMember($action, $includefile) {
		global $loggedUser;
		if (array_key_exists ( 'indexAction', $_GET ) && ($_GET ['indexAction'] == $action) && $loggedUser)
			return $includefile;
	}
	public function utilitiesSetModuleCookie($module) {
		if ((! array_key_exists ( 'module', $_SESSION )) || (array_key_exists ( 'module', $_SESSION ) && ($_SESSION ['module'] != $module))) {
			$_SESSION ['module'] = $module;
			$cookietime = time () + 365 * 24 * 60 * 60; // 1 year
			setcookie ( "module", $module, $cookietime, "/" );
		}
	}
	public function getDrawAccomplishment($number) {
		return LangDrawAccomplishment1 . ( int ) $number . LangDrawAccomplishment2;
	}
	public function getDrawToAccomplish($number) {
		return LangDrawToAccomplish1 . ( int ) $number . LangDrawToAccomplish2;
	}
	public function getSeenAccomplishment($number) {
		return LangSeenAccomplishment1 . ( int ) $number . LangSeenAccomplishment2;
	}
	public function getSeenToAccomplish($number) {
		return LangSeenToAccomplish1 . ( int ) $number . LangSeenToAccomplish2;
	}

	// Add the table
	public function addTableColumSelector() {
		// Add the button for the columns
		echo "   <div class=\"columnSelectorWrapper\">
              <input id=\"colSelect1\" type=\"checkbox\" class=\"hidden\">
              <label class=\"columnSelectorButton\" for=\"colSelect1\">" . LangSelectColumns . "</label>
              <div id=\"columnSelector\" class=\"columnSelector\">
              </div>
	         </div>";
	}

	// Add the pager for the table
	public function addTablePager($id = "") {
		echo "<!-- pager -->
          <div id=\"pager" . $id . "\" class=\"pager\">
           <form>
            <span class=\"glyphicon glyphicon-step-backward first\"></span>
            <span class=\"glyphicon glyphicon-backward prev\"></span>
            <span class=\"pagedisplay\"></span> <!-- this can be any element, including an input -->
            <span class=\"glyphicon glyphicon-forward next\"></span>
            <span class=\"glyphicon glyphicon-step-forward last\"></span>
            <select class=\"pagesize\">
             <option selected=\"selected\" value=\"10\">10</option>
             <option value=\"20\">20</option>
             <option value=\"30\">30</option>
             <option value=\"40\">40</option>
            </select>
            <select class=\"gotoPage\" title=\"Select page number\"></select>
           </form>
          </div>";
	}
	// Add the javascript for the table
	public function addTableJavascript($id = "", $columSelect = true) {
		global $dateformat;
		// Make the table sorter, add the pager and add the column chooser
		echo "<script type=\"text/javascript\">";
		echo "	var date = new Date();
    			date.setTime(date.getTime()+(24*60*60*1000));
    			var expires = \"; expires=\"+date.toGMTString();
				document.cookie = \"sortOrder=\|showname(0)\|\"+expires+\"; path=/\";";
		echo "// add astrotime parser. Use with class=sorter-astrotime
              $.tablesorter.addParser({
                // set a unique id
                id: 'astrotime',
                is: function(s, table, cell, \$cell) {
                  // return false so this parser is not auto detected
                  return false;
                },
                format: function(s, table, cell, cellIndex) {
                  // format your data for normalization
                  var time = s.split(\":\");
                  var hour = time[0];
                  if (hour < 12) {
                    hour += 24;
                  }
                  return \"\" + hour + time[1];
                },
                // set type, either numeric or text
                type: 'numeric'
              });";

		echo "// add astrotime parser. Use with class=sorter-degrees
              $.tablesorter.addParser({
                // set a unique id
                id: 'degrees',
                is: function(s, table, cell, \$cell) {
                  // return false so this parser is not auto detected
                  return false;
                },
                format: function(s, table, cell, cellIndex) {
                  // format your data for normalization
				  s = s.replace('°', '.');
				  s = s.replace(/[^0-9-.]/g, '');

				  if(s == '-'){s = '0'};

                  return s;
                },
                // set type, either numeric or text
                type: 'numeric'
              });";

		echo "// add astrotime parser. Use with class=sorter-months
              $.tablesorter.addParser({
                // set a unique id
                id: 'months',
                is: function(s, table, cell, \$cell) {
                  // return false so this parser is not auto detected
                  return false;
                },
                format: function(s, table, cell, cellIndex) {
                  // format your data for normalization
                  var months = s.split(\" \");
				  var fraction = 0.75;
				  if (months[0] == \"" . LangMonthTransit . "\") {
				    fraction = 0.0;
				  } else if (months[0] == \"" . LangMonthStart . "\") {
				    fraction = 0.25;
				  } else if (months[0] == \"" . LangMonthMid . "\") {
				    fraction = 0.5;
				  }

				  var month = 1;
				  if (months[1] == \"" . $GLOBALS ['Month2Short'] . "\") {
				    month = 2;
				  } else if (months[1] == \"" . $GLOBALS ['Month3Short'] . "\") {
				    month = 3;
				  } else if (months[1] == \"" . $GLOBALS ['Month4Short'] . "\") {
				    month = 4;
				  } else if (months[1] == \"" . $GLOBALS ['Month5Short'] . "\") {
				    month = 5;
				  } else if (months[1] == \"" . $GLOBALS ['Month6Short'] . "\") {
				    month = 6;
				  } else if (months[1] == \"" . $GLOBALS ['Month7Short'] . "\") {
				    month = 7;
				  } else if (months[1] == \"" . $GLOBALS ['Month8Short'] . "\") {
				    month = 8;
				  } else if (months[1] == \"" . $GLOBALS ['Month9Short'] . "\") {
				    month = 9;
				  } else if (months[1] == \"" . $GLOBALS ['Month10Short'] . "\") {
				    month = 10;
				  } else if (months[1] == \"" . $GLOBALS ['Month11Short'] . "\") {
				    month = 11;
				  } else if (months[1] == \"" . $GLOBALS ['Month12Short'] . "\") {
				    month = 12;
				  }

                  return \"\" + month + fraction;
                },
                // set type, either numeric or text
                type: 'numeric'
              });";

		echo "$(function(){
			$(\".sort-table" . $id . "\").tablesorter({
		       theme: \"bootstrap\",
					 delayInit: \"true\",
					 filter_searchFiltered: \"true\",
			   stringTo: \"bottom\",
               dateFormat : \"";

		if ($dateformat == "d/m/Y") {
			echo "ddmmyyyy";
		} else {
			echo "mmddyyyy";
		}
		// Make sure the columnSelector module is only loaded when the columnSelector is indeed used.
		echo "\", // set the default date format
               headerTemplate: '{content} {icon}',
               widgets: [\"reorder\", \"uitheme\", ";
		if ($columSelect) {
			echo "\"columnSelector\", ";
		}
		echo "\"filter\", \"stickyHeaders\"],
               widgetOptions : {
                 // target the column selector markup
                 columnSelector_container : $('.columnSelector'),
                 // column status, true = display, false = hide
                 // disable = do not display on list
                 columnSelector_columns : {
                   0: 'disable',  /* set to disabled; not allowed to unselect it */
				   1 : false,     /* start with column hidden */
    			   2 : true,      /* start with column visible; default for undefined columns */
                 },
                 // remember selected columns (requires $.tablesorter.storage)
				 columnSelector_saveColumns: true,

                 // container layout
                 columnSelector_layout : '<label><input type=\"checkbox\">{name}</label>',
                 // data attribute containing column name to use in the selector container
                 columnSelector_name  : 'data-selector-name',

                 /* Responsive Media Query settings */
                 // enable/disable mediaquery breakpoints
                 columnSelector_mediaquery: true,
                 // toggle checkbox name
                 columnSelector_mediaqueryName: 'Auto: ',
                 // breakpoints checkbox initial setting
                 columnSelector_mediaqueryState: false,
                 // responsive table hides columns with priority 1-6 at these breakpoints
                 // see http://view.jquerymobile.com/1.3.2/dist/demos/widgets/table-column-toggle/#Applyingapresetbreakpoint
                 // *** set to false to disable ***
                 columnSelector_breakpoints : [ '20em', '30em', '40em', '50em', '60em', '70em' ],
                 // data attribute containing column priority
                 // duplicates how jQuery mobile uses priorities:
                 // http://view.jquerymobile.com/1.3.2/dist/demos/widgets/table-column-toggle/
                 columnSelector_priority : 'data-priority',

                 reorder_axis        : 'x', // 'x' or 'xy'
                 reorder_delay       : 300,
                 reorder_helperClass : 'tablesorter-reorder-helper',
                 reorder_helperBar   : 'tablesorter-reorder-helper-bar',
                 reorder_noReorder   : 'reorder-false',
                 reorder_blocked     : 'reorder-block-left reorder-block-end',
                 reorder_complete    : null // callback
	}
	})

	// Add the sort order to a cookie to read out when needed...
	// Shows id of sorted column and the number to see if we sort from high to low or from low to high.
	.bind(\"sortEnd\", function(sorter) {
		currentSort = sorter.target.config.sortList;
		var columns = \"\|\";
		for (column = 0;column < currentSort.length;column++) {
			columns = columns + $(sorter.target.config.headerList[currentSort[column][0]])[0].id +
				\"(\"+ (currentSort[column][1]) + \")\" + \",\";
		}
		columns = columns.substring(0, columns.length - 1);
		columns = columns + \"\|\";
        var date = new Date();
        date.setTime(date.getTime()+(24*60*60*1000));
        var expires = \"; expires=\"+date.toGMTString();
	    document.cookie = \"sortOrder=\"+columns+expires+\"; path=/\";
	});

    var pagerOptions = {

    // target the pager markup - see the HTML block below
    container: $(\"#pager" . $id . "\"),

    // use this url format \"http:/mydatabase.com?page={page}&size={size}&{sortList:col}\"
    ajaxUrl: null,

    // modify the url after all processing has been applied
    customAjaxUrl: function(table, url) { return url; },

    // process ajax so that the data object is returned along with the total number of rows
    // example: { \"data\" : [{ \"ID\": 1, \"Name\": \"Foo\", \"Last\": \"Bar\" }], \"total_rows\" : 100 }
    ajaxProcessing: function(ajax){
      if (ajax && ajax.hasOwnProperty('data')) {
        // return [ \"data\", \"total_rows\" ];
        return [ ajax.total_rows, ajax.data ];
      }
    },

    // output string - default is '{page}/{totalPages}'
    // possible variables: {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
    output: '{startRow} to {endRow} ({totalRows})',

    // apply disabled classname to the pager arrows when the rows at either extreme is visible - default is true
    updateArrows: true,

    // starting page of the pager (zero based index)
    page: 0,

    // Number of visible rows - default is 10
    size: 10,

    // Save pager page & size if the storage script is loaded (requires $.tablesorter.storage in jquery.tablesorter.widgets.js)
    savePages : false,

    //defines custom storage key
    storageKey:'tablesorter-pager',

    // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
    // table row set to a height to compensate; default is false
    fixedHeight: false,

    // remove rows from the table to speed up the sort of large tables.
    // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
    removeRows: true,

    // css class names of pager arrows
    cssNext: '.next', // next page arrow
    cssPrev: '.prev', // previous page arrow
    cssFirst: '.first', // go to first page arrow
    cssLast: '.last', // go to last page arrow
    cssGoto: '.gotoPage', // select dropdown to allow choosing a page

    cssPageDisplay: '.pagedisplay', // location of where the output is displayed
    cssPageSize: '.pagesize', // page size selector - select dropdown that sets the size option

    // class added to arrows when at the extremes (i.e. prev/first arrows are disabled when on the first page)
    cssDisabled: 'disabled', // Note there is no period " . " in front of this class name
    cssErrorRow: 'tablesorter-errorRow' // ajax error information row

  };

  // initialize column selector using default settings
  // note: no container is defined!
  $(\".bootstrap-popup\").tablesorter({
    theme: 'blue',
    widgets: ['columnSelector', 'stickyHeaders']
  });

		// bind to pager events
		// *********************
		$(\".sort-table" . $id . "\").bind('pagerChange pagerComplete pagerInitialized pageMoved', function(e, c){
			var msg = '\"</span> event triggered, ' + (e.type === 'pagerChange' ? 'going to' : 'now on') +
			' page <span class=\"typ\">' + (c.page + 1) + '/' + c.totalPages + '</span>';
			$('#display')
			.append('<li><span class=\"str\">\"' + e.type + msg + '</li>')
			.find('li:first').remove();
  })

  // initialize the pager plugin
  // ****************************
  $(\".sort-table" . $id . "\").tablesorterPager(pagerOptions);

	});";

		echo "</script>";
	}
	function addPager($name, $count, $tableSelector = true) {
		// We limit the number of rows in a table to 3000.
		$max = 1000;

		// For internet explorer, we limit the number of rows in the tables to 500 items.
		if (preg_match ( '/(?i)msie [2-9]/', $_SERVER ['HTTP_USER_AGENT'] )) {
			$max = 400;
		}

		if ($count < $max) {
			echo $this->addTablePager ( $name );

			echo $this->addTableJavascript ( $name, $tableSelector );
		}
	}
}
?>
