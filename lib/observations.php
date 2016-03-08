<?php
// observations.php
// The observations class collects all functions needed to enter, retrieve and adapt observation data from the database.
global $inIndex;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
class Observations {
	public function addCSVobservations() {
		global $objPresentations, $messageLines, $objObject, $objLocation, $loggedUser, $objInstrument, $objEyepiece, $objLens, $objFilter, $baseURL, $objObserver, $objUtil, $objSession;
		$_GET ['indexAction'] = 'default_action';
		if ($_FILES ['csv'] ['tmp_name'] != "")
			$csvfile = $_FILES ['csv'] ['tmp_name'];
		$data_array = file ( $csvfile );
		set_time_limit ( count ( $data_array ) );
		for($i = 0; $i < count ( $data_array ); $i ++)
			$parts_array [$i] = explode ( ";", $data_array [$i] );
		for($i = 0; $i < count ( $parts_array ); $i ++) {
			$objects [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 0, '' ) );
			$dates [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 2, '' ) );
			$locations [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 4, '' ), ENT_COMPAT, "UTF-8", 0 );
			$instruments [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 5, '' ), ENT_COMPAT, "UTF-8", 0 );
			$filters [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 7, '' ), ENT_COMPAT, "UTF-8", 0 );
			$eyepieces [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 6, '' ), ENT_COMPAT, "UTF-8", 0 );
			$lenses [$i] = htmlentities ( $objUtil->checkArrayKey ( $parts_array [$i], 8, '' ), ENT_COMPAT, "UTF-8", 0 );
		}
		if (! is_array ( $objects ))
			throw new Exception ( LangInvalidCSVfile );
		else {
			$noDates = array ();
			$wrongDates = array ();
			$objectsMissing = array ();
			$locationsMissing = array ();
			$instrumentsMissing = array ();
			$filtersMissing = array ();
			$eyepiecesMissing = array ();
			$lensesMissing = array ();
			$errorlist = array ();
			// Test if the objects, locations and instruments are available in the database
			for($i = 0, $j = 0; $i < count ( $objects ); $i ++) {
				$objectsquery = $objObject->getExactDSObject ( trim ( $objects [$i] ) );
				if (! $objectsquery) {
					if (! in_array ( trim ( $objects [$i] ), $objectsMissing ))
						$objectsMissing [$j ++] = trim ( $objects [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				} else
					$correctedObjects [$i] = $objectsquery;
			}
			// Check for existence of locations
			for($i = 0, $j = 0, $temploc = ''; $i < count ( $locations ); $i ++)
				if ((! trim ( $locations [$i] )) || ($temploc != trim ( $locations [$i] )) && ($objLocation->getLocationId ( trim ( $locations [$i] ), $loggedUser ) == - 1)) {
					if (! in_array ( $locations [$i], $locationsMissing ))
						$locationsMissing [$j ++] = trim ( $locations [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				} else
					$temploc = trim ( $locations [$i] );

				// Check for existence of instruments
			for($i = 0, $j = 0, $tempinst = ''; $i < count ( $instruments ); $i ++)
				if ((! trim ( $instruments [$i] )) || ($objInstrument->getInstrumentId ( trim ( $instruments [$i] ), $loggedUser ) == - 1)) {
					if (! in_array ( trim ( $instruments [$i] ), $instrumentsMissing ))
						$instrumentsMissing [$j ++] = trim ( $instruments [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				} else
					$tempinst = $instruments [$i];
				// Check for the existence of the eyepieces
			for($i = 0, $j = 0; $i < count ( $eyepieces ); $i ++)
				if (trim ( $eyepieces [$i] ) && (! ($objEyepiece->getEyepieceObserverPropertyFromName ( trim ( $eyepieces [$i] ), $loggedUser, 'id' )))) {
					if (! in_array ( trim ( $eyepieces [$i] ), $eyepiecesMissing ))
						$eyepiecesMissing [$j ++] = trim ( $eyepieces [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				}
				// Check for the existence of the filters
			for($i = 0, $j = 0; $i < count ( $filters ); $i ++)
				if (trim ( $filters [$i] ) && (! ($objFilter->getFilterObserverPropertyFromName ( trim ( $filters [$i] ), $loggedUser, 'id' )))) {
					if (! in_array ( trim ( $filters [$i] ), $filtersMissing ))
						$filtersMissing [$j ++] = trim ( $filters [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				}
				// Check for the existence of the lenses
			for($i = 0, $j = 0; $i < count ( $lenses ); $i ++)
				if (trim ( $lenses [$i] ) && (! ($objLens->getLensObserverPropertyFromName ( trim ( $lenses [$i] ), $loggedUser, 'id' )))) {
					if (! in_array ( trim ( $lenses [$i] ), $lensesMissing ))
						$lensesMissing [$j ++] = trim ( $lenses [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				}

				// Check for the correctness of dates
			for($i = 0, $j = 0, $k = 0; $i < count ( $dates ); $i ++) {
				$parsed_date = date_parse ( $dates [$i] );

				if ($parsed_date ["error_count"] > 0 || $parsed_date ["year"] < 1900) {
					if (! in_array ( trim ( $dates [$i] ), $wrongDates ))
						$wrongDates [$k ++] = trim ( $dates [$i] );
					if (! in_array ( $i, $errorlist ))
						$errorlist [] = $i;
				}
			}
			// error catching
			if (count ( $errorlist ) > 0) {
				$errormessage = LangCSVError1 . "<br />";
				if (count ( $noDates ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError8 . " : <ul>";
					for($i = 0; $i < count ( $noDates ); $i ++)
						$errormessage .= "<li>" . ($noDates [$i] ? $noDates [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $wrongDates ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError9 . " : <ul>";
					for($i = 0; $i < count ( $wrongDates ); $i ++)
						$errormessage .= "<li>" . ($wrongDates [$i] ? $wrongDates [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $objectsMissing ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError2 . " : <ul>";
					for($i = 0; $i < count ( $objectsMissing ); $i ++)
						$errormessage .= "<li>" . ($objectsMissing [$i] ? $objectsMissing [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $locationsMissing ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError3 . " : <ul>";
					for($i = 0; $i < count ( $locationsMissing ); $i ++)
						$errormessage .= "<li>" . ($locationsMissing [$i] ? $locationsMissing [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $instrumentsMissing ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError4 . " : <ul>";
					for($i = 0; $i < count ( $instrumentsMissing ); $i ++)
						$errormessage .= "<li>" . ($instrumentsMissing [$i] ? $instrumentsMissing [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $filtersMissing ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError5 . " : <ul>";
					for($i = 0; $i < count ( $filtersMissing ); $i ++)
						$errormessage .= "<li>" . ($filtersMissing [$i] ? $filtersMissing [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $eyepiecesMissing ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError6 . " : <ul>";
					for($i = 0; $i < count ( $eyepiecesMissing ); $i ++)
						$errormessage .= "<li>" . ($eyepiecesMissing [$i] ? $eyepiecesMissing [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				if (count ( $lensesMissing ) > 0) {
					$errormessage .= "<ul><li>" . LangCSVError7 . " : <ul>";
					for($i = 0; $i < count ( $lensesMissing ); $i ++)
						$errormessage .= "<li>" . ($lensesMissing [$i] ? $lensesMissing [$i] : "&nbsp;") . "</li>";
					$errormessage .= "</ul></li></ul>";
				}
				unset ( $_SESSION ['csvImportErrorData'] );
				while ( list ( $key, $j ) = each ( $errorlist ) )
					$_SESSION ['csvImportErrorData'] [$key] = $parts_array [$j];
				$messageLines [] = "<h4>" . LangCSVError0 . "</h4>" . "<p>" . LangCSVError0 . "</p>" . $errormessage . "<p>" . LangCSVError10 . "<a href=\"" . $baseURL . "index.php?indexAction=add_csv\">" . LangCSVError10a . "</a>" . LangCSVError10b . "</p><hr /><p>" . LangCSVError10e . "<a href=\"" . $baseURL . "observationserrors.csv\">" . LangCSVError10c . "</a>" . LangCSVError10d . "</p><hr /><p>" . LangCSVMessage4 . "</p>";
				$_GET ['indexAction'] = 'message';
			}

			$username = $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . " " . $objObserver->getObserverProperty ( $loggedUser, 'name' );
			$added = 0;
			$double = 0;
			for($i = 0; $i < count ( $parts_array ); $i ++) {
				if (! in_array ( $i, $errorlist )) {
					$observername = $objObserver->getObserverProperty ( htmlentities ( trim ( $parts_array [$i] [1] ) ), 'firstname' ) . " " . $objObserver->getObserverProperty ( htmlentities ( trim ( $parts_array [$i] [1] ) ), 'name' );
					if (trim ( $parts_array [$i] [1] ) == $username) {
						$instrum = $objInstrument->getInstrumentId ( htmlentities ( trim ( $parts_array [$i] [5] ), ENT_COMPAT, "UTF-8", 0 ), $loggedUser );
						$locat = $objLocation->getLocationId ( htmlentities ( trim ( $parts_array [$i] [4] ), ENT_COMPAT, "UTF-8", 0 ), $loggedUser );

						$parsed_date = date_parse ( $dates [$i] );
						$date = sprintf ( "%04d%02d%02d", $parsed_date ["year"], $parsed_date ["month"], $parsed_date ["day"] );
						if ($parts_array [$i] [3]) {
							$times = sscanf ( trim ( $parts_array [$i] [3] ), "%2d%c%2d" );
							$time = sprintf ( "%02d%02d", $times [0], $times [2] );
						} else
							$time = "-9999";
						$obsid = $this->addDSObservation2 ( $correctedObjects [$i], $loggedUser, $instrum, $locat, $date, $time, trim ( $parts_array [$i] [13] ), htmlentities ( trim ( $parts_array [$i] [9] ) ), str_replace ( ',', '.', htmlentities ( trim ( $parts_array [$i] [10] ) ) ), htmlentities ( ((trim ( $parts_array [$i] [11] ) == "") ? "0" : trim ( $parts_array [$i] [11] )) ), htmlentities ( trim ( $parts_array [$i] [12] ) ), ((trim ( $parts_array [$i] [6] ) != "") ? Nz0 ( $objEyepiece->getEyepieceObserverPropertyFromName ( htmlentities ( trim ( $parts_array [$i] [6] ) ), $loggedUser, 'id' ) ) : 0), ((trim ( $parts_array [$i] [7] ) != "") ? Nz0 ( $objFilter->getFilterObserverPropertyFromName ( htmlentities ( trim ( $parts_array [$i] [7] ) ), $loggedUser, 'id' ) ) : 0), ((trim ( $parts_array [$i] [8] ) != "") ? Nz0 ( $objLens->getLensObserverPropertyFromName ( htmlentities ( trim ( $parts_array [$i] [8] ) ), $loggedUser, 'id' ) ) : 0) );
						if ($obsid) {
							$added ++;
							// Add the observation to all the sessions
							$current_observation = $objSession->addObservationToSessions ( $obsid );
						} else
							$double ++;
					}
					unset ( $_SESSION ['QobsParams'] );
				}
			}
			return LangCSVMessage8 . ": " . $added . LangCSVMessage9 . ": " . count ( $errorlist ) . LangCSVMessage10 . ": " . $double . ".";
		}
	}
	public function addDSObservation($objectname, $observerid, $instrumentid, $locationid, $date, $time, $description, $seeing, $limmag, $visibility, $language) { // adds a new observation to the database. The name, observerid, instrumentid, locationid, date, time, description, seeing and limiting magnitude should be given as parameters. The id of the latest observation is returned.
	                                                                                                                                                               // If the time and date are given in local time, you should execute setLocalDateAndTime after inserting the observation!
		global $objDatabase;
		if (($seeing == "-1") || ($seeing == ""))
			$seeing = "NULL";
		if ($limmag == "")
			$limmag = "NULL";
		else {
			if (preg_match ( '/([0-9]{1})[.,]([0-9]{1})/', $limmag, $matches )) // limiting magnitude like X.X or X,X with X a number between 0 and 9
				$limmag = $matches [1] . "." . $matches [2]; // valid limiting magnitude // save current magnitude limit
			$limmag = "$limmag";
		}
		$description = preg_replace ( "/(\")/", "", $description );

		$objDatabase->execSQL ( "INSERT INTO observations (objectname, observerid, instrumentid, locationid, date, time, description, seeing, limmag, visibility, language) " . "VALUES (\"$objectname\", \"$observerid\", \"$instrumentid\", \"$locationid\", \"$date\", \"$time\", \"$description\", $seeing, $limmag, $visibility, \"$language\")" );
		return $objDatabase->selectSingleValue ( "SELECT id FROM observations ORDER BY id DESC LIMIT 1", 'id' );
	}
	public function addDSObservation2($objectname, $observerid, $instrumentid, $locationid, $date, $time, $description, $seeing, $limmag, $visibility, $language, $eyepieceid, $filterid, $lensid) {
		global $objDatabase, $objPresentations;
		if (($seeing == "-1") || ($seeing == ""))
			$seeing = "-1";
		if ($limmag == "")
			$limmag = "0";
		$sqm = "-1";
		if (($limmag > 15) && ($limmag < 25)) {
			$sqm = $limmag;
			$limmag = "0";
		} elseif (($limmag > 0) && ($limmag < 10))
			$limmag = $limmag;
		else
			$limmag = "0";
		$description = preg_replace ( "/(\")/", "", $description );
		$description = preg_replace ( "/;/", ",", $description );
		$description = htmlentities ( $description, ENT_COMPAT, "UTF-8" );

		if ($id = $objDatabase->selectSingleValue ( "SELECT id FROM observations WHERE objectname=\"$objectname\" AND
		                                                                          observerid=\"$observerid\" AND
    	          	                                                            instrumentid=\"$instrumentid\" AND
		                                                                          locationid=\"$locationid\" AND
		                                                                          date=\"$date\" AND
		                                                                          time=\"$time\" AND
		                                                                          description=\"$description\" AND
		                                                                          seeing=$seeing AND
		                                                                          ROUND(limmag)=ROUND($limmag) AND
          		                                                                ROUND(SQM)=ROUND($sqm) AND
          		                                                                visibility=$visibility AND
		                                                                          language=\"$language\" AND
		                                                                          eyepieceid=$eyepieceid AND
		                                                                          filterid=$filterid AND
		                                                                          lensid=$lensid", 'id', 0 ))
			return 0;
		else
			$objDatabase->execSQL ( "INSERT INTO observations (objectname,
		                                                 observerid,
		                                                 instrumentid,
		                                                 locationid,
		                                                 date,
		                                                 time,
		                                                 description,
		                                                 seeing,
		                                                 limmag,
		                                                 visibility,
		                                                 language,
		                                                 eyepieceid,
		                                                 filterid,
		                                                 lensid,
		                                                 SQM)
		                                       VALUES (  \"$objectname\",
		                                                 \"$observerid\",
		                                                 \"$instrumentid\",
		                                                 \"$locationid\",
		                                                 \"$date\",
		                                                 \"$time\",
		                                                 \"$description\",
		                                                 $seeing,
		                                                 $limmag,
		                                                 $visibility,
		                                                 \"$language\",
		                                                 $eyepieceid,
		                                                 $filterid,
		                                                 $lensid,
		                                                 $sqm)" );
			// Return the obsid
		return $objDatabase->selectSingleValue ( "SELECT id FROM observations ORDER BY id DESC LIMIT 1", 'id' );
	}
	public function getAllInfoDsObservation($id) // returns all information of an observation
{
		global $objDatabase;
		$obs = $objDatabase->selectRecordArray ( "SELECT * FROM observations WHERE id=\"$id\"" );
		$obs ["localdate"] = $this->getDsObservationLocalDate ( $id );
		$obs ["localtime"] = $this->getDsObservationLocalTime ( $id );
		$obs ["language"] = $this->getDsObservationProperty ( $id, 'language' );
		/*
		 * $ob["name"] = $get->objectname; $ob["observer"] = $get->observerid; $ob["instrument"] = $get->instrumentid; $ob["location"] = $get->locationid; $ob["date"] = $get->date; $ob["time"] = $get->time; $ob["description"] = $get->description; $ob["seeing"] = $get->seeing; $ob["limmag"] = $get->limmag; $ob["visibility"] = $get->visibility; $ob["eyepiece"] = $get->eyepieceid; $ob["filter"] = $get->filterid; $ob["lens"] = $get->lensid; $ob["sqm"] = $get->SQM; $ob["largeDiam"] = $get->largeDiameter; $ob["smallDiam"] = $get->smallDiameter; $ob["stellar"] = $get->stellar; $ob["extended"] = $get->extended; $ob["resolved"] = $get->resolved; $ob["mottled"] = $get->mottled; $ob["clusterType"] = $get->clusterType; $ob["unusualShape"] = $get->unusualShape; $ob["partlyUnresolved"] = $get->partlyUnresolved; $ob["colorContrasts"] = $get->colorContrasts;
		 */
		return $obs;
	}
	public function getAOObservationsId($object, $notobservation) {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT observations.id FROM observations WHERE objectname=\"" . $object . "\" AND id!=\"" . $notobservation . "\" ORDER BY id DESC", 'id' );
	}
	public function getDsObservationLocalDate($id) // returns the date of the given observation in local time
{
		global $objDatabase, $objLocation;
		$run = $objDatabase->selectRecordset ( "SELECT date,time,locationid FROM observations WHERE id=\"" . $id . "\"" );
		if ($get = $run->fetch ( PDO::FETCH_OBJ )) {
			$date = $get->date;
			$time = $get->time;
			$loc = $get->locationid;
			if ($time >= 0) {
				$date = sscanf ( $get->date, "%4d%2d%2d" );
				$timezone = $objLocation->getLocationPropertyFromId ( $get->locationid, 'timezone' );
				$dateTimeZone = new DateTimeZone ( $timezone );
				$datestr = sprintf ( "%02d", $date [1] ) . "/" . sprintf ( "%02d", $date [2] ) . "/" . $date [0];
				$dateTime = new DateTime ( $datestr, $dateTimeZone );
				// Geeft tijdsverschil terug in seconden
				$timedifference = $dateTimeZone->getOffset ( $dateTime );
				$timedifference = $timedifference / 3600.0;
				if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0) {
					$timedifference = - $timedifference;
				}
				$jd = cal_to_jd ( CAL_GREGORIAN, $date [1], $date [2], $date [0] );
				$time = sscanf ( sprintf ( "%04d", $time ), "%2d%2d" );
				$hours = $time [0] + ( int ) $timedifference;
				$minutes = $time [1];
				// We are converting from UT to local time -> we should add the time difference!
				$timedifferenceminutes = ($timedifference - ( int ) $timedifference) * 60;
				$minutes = $minutes + $timedifferenceminutes;
				if ($minutes < 0) {
					$hours = $hours - 1;
					$minutes = $minutes + 60;
				} else if ($minutes >= 60) {
					$hours = $hours + 1;
					$minutes = $minutes - 60;
				}
				if ($hours < 0) {
					$hours = $hours + 24;
					$jd = $jd - 1;
				}
				if ($hours >= 24) {
					$hours = $hours - 24;
					$jd = $jd + 1;
				}
				$dte = JDToGregorian ( $jd );
				sscanf ( $dte, "%2d/%2d/%4d", $month, $day, $year );
				$date = sprintf ( "%d%02d%02d", $year, $month, $day );
			}
			return $date;
		}
	}
	public function getDsObservationLocalTime($id) // returns the time of the given observation in local time
{
		global $objDatabase, $objLocation;
		if ($get = $objDatabase->selectrecordset ( "SELECT date, time, locationid FROM observations WHERE id=\"$id\"" )->fetch ( PDO::FETCH_OBJ )) {
			$date = $get->date;
			$time = $get->time;
			$loc = $get->locationid;
			$date = sscanf ( $date, "%4d%2d%2d" );
			$timezone = $objLocation->getLocationPropertyFromId ( $loc, 'timezone' );
			$dateTimeZone = new DateTimeZone ( $timezone );
			$datestr = sprintf ( "%02d", $date [1] ) . "/" . sprintf ( "%02d", $date [2] ) . "/" . $date [0];
			$dateTime = new DateTime ( $datestr, $dateTimeZone );
			// Geeft tijdsverschil terug in seconden
			$timedifference = $dateTimeZone->getOffset ( $dateTime );
			$timedifference = $timedifference / 3600.0;
			if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0) {
				$timedifference = - $timedifference;
			}
			if ($time < 0)
				return $time;
			$time = sscanf ( sprintf ( "%04d", $time ), "%2d%2d" );
			$hours = $time [0] + ( int ) $timedifference;
			$minutes = $time [1];
			// We are converting from UT to local time -> we should add the time difference!
			$timedifferenceminutes = ($timedifference - ( int ) $timedifference) * 60;
			$minutes = $minutes + $timedifferenceminutes;
			if ($minutes < 0) {
				$hours = $hours - 1;
				$minutes = $minutes + 60;
			} else if ($minutes >= 60) {
				$hours = $hours + 1;
				$minutes = $minutes - 60;
			}
			if ($hours < 0)
				$hours = $hours + 24;
			if ($hours >= 24)
				$hours = $hours - 24;
			$time = $hours * 100 + $minutes;
			return $time;
		} else
			throw new Exception ( "Error in getDsObservationLocalTime of observations.php" );
	}
	public function getDsObservationProperty($id, $property, $defaultvalue = '') // returns the property of the observation
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT " . $property . " FROM observations WHERE id=\"" . $id . "\"", $property, $defaultvalue );
	}
	public function getDsDrawingsCountFromObserver($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT COUNT(*) as Cnt FROM observations WHERE observations.observerid = \"$id\" and visibility != 7 AND hasDrawing=1", "Cnt", 0 );
	}
	public function getDsObservationsCountFromObserver($id) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT COUNT(*) as Cnt FROM observations WHERE observations.observerid = \"$id\" and visibility != 7 ", "Cnt", 0 );
	}
	public function getLOObservationId($objectname, $userid, $notobservation) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT id FROM observations WHERE objectname=\"" . $objectname . "\" and observerid=\"" . $userid . "\" and id!=\"" . $notobservation . "\" ORDER BY date DESC", 'id', 0 );
	}
	public function getMaxObservation() {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( 'SELECT MAX(observations.id) as MaxCnt FROM observations', 'MaxCnt', 0 );
	}
	public function getMOObservationsId($object, $userid, $notobservation) {
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT observations.id FROM observations WHERE objectname=\"" . $object . "\" and observerid=\"" . $userid . "\" AND id!=\"" . $notobservation . "\" ORDER BY id DESC", 'id' );
	}
	public function getNumberOfDifferentObservedDSObjects( $country = "" ) // Returns the number of different objects observed
{
		global $objDatabase;

		if (strcmp($country, "") == 0) {
			return $objDatabase->selectSingleValue ( "SELECT COUNT(DISTINCT objectname) As Cnt FROM observations WHERE visibility != 7 ", 'Cnt' );
		} else {
			return $objDatabase->selectSingleValue ( "SELECT COUNT(DISTINCT objectname) As Cnt FROM observations JOIN locations ON observations.locationid=locations.id WHERE observations.visibility != 7 and locations.country=\"" . $country . "\"", 'Cnt', 0 );
		}
	}
	public function getNumberOfDsDrawings() // returns the total number of observations
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT COUNT(objectname) As Cnt FROM observations WHERE visibility != 7 AND hasDrawing=1", 'Cnt', 0 );
	}
	public function getNumberOfDsObservations( $country="" ) // returns the total number of observations for a country
{
		global $objDatabase;
		if (strcmp($country, "") == 0) {
			return $objDatabase->selectSingleValue ( "SELECT COUNT(objectname) As Cnt FROM observations WHERE visibility != 7 ", 'Cnt', 0 );
		} else {
			return $objDatabase->selectSingleValue ( "SELECT COUNT(objectname) As Cnt FROM observations JOIN locations ON observations.locationid=locations.id WHERE visibility != 7 and locations.country=\"" . $country . "\"", 'Cnt', 0 );
		}
	}
	public function getNumberOfObjects($id) // return the number of different objects seen by the observer
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT COUNT(DISTINCT objectname) As Cnt FROM observations WHERE observerid=\"" . $id . "\" AND visibility != 7 ", 'Cnt', 0 );
	}
	public function getNumberOfObjectDrawings($id) // return the number of different objects seen by the observer
{
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT COUNT(DISTINCT objectname) As Cnt FROM observations WHERE observerid=\"" . $id . "\" AND visibility != 7 and hasDrawing = 1", 'Cnt', 0 );
	}
	public function getObjectsFromObservations($observations, $showPartOfs = 0) {
		global $objObject;
		$objects = array ();
		$i = 0;
		while ( list ( $key, $observation ) = each ( $observations ) )
			if (! array_key_exists ( $observation ['objectname'], $objects ))
				$objects [$observation ['objectname']] = array (
						$i ++,
						$observation ['objectname']
				);
		if ($showPartOfs)
			$objects = $objObject->getPartOfs ( $objects );
		return $objects;
	}
	/** Returns all the observations from a query.

		@param $queries The query to find the observations. An example:
											array("object" => "NGC 7293", "observer" => "wim")
											You can really enter a lot of options here to find the needed observations:
	  										+ instrument: The used instrument. Be carefull, because each observer has unique instruments.
												+ location: The location where the observation was done. Be carefull, because each observer has unique locations.
	  										+ mindate, maxdate: The date interval to search for observations.
	  										+ mindiameter, maxdiameter: The interval of telescope diameter for the observations.
												+ type: The object type, eg GALXY.
												+ con: The constellation where the observation was made.
	  										+ minmag, maxmag: The interval of the magnitudes of the observed objects.
												+ minsubr, maxsubr: The interval of the surface brightness of the observed objects.
	  										+ minra, maxra: The interval of the right ascension of the observed objects.
	  										+ mindecl, maxdecl: The interval of the declination of the observed objects.
	  										+ urano, uranonew, sky, msa, ... : The atlas page of the observed objects.
	  										+ mindiam1, maxdiam1: The interval of the largest diameter of the observed objects.
												+ mindiam2, maxdiam2: The interval of the smallest diameter of the observed objects.
	  										+ description: A part of the description
												+ minvisibility, maxvisibility: The interval of the visibility of the observations.
	  										+ minseeing, maxseeing: The interval of the seeing conditions of the observations.
	  										+ minlimmag, maxlimmag: The interval of the naked eye limiting magnitude of the observations.
												+ $languages: An array with the languages, for example: $languages => Array ( [0] => en ))
	  										+ eyepiece: The eyepiece used for the observations. Be carefull, because each observer has unique eyepieces.
												+ filter: The filter used for the observations. Be carefull, because each observer has unique filters.
												+ lens: The lens used for the observations. Be carefull, because each observer has unique lenses.
												+ minSmallDiameter, maxSmallDiameter: The interval of the estimated smallest diameters of the observed objects.
												+ minLargeDiameter, maxLargeDiameter: The interval of the estimated largest diameters of the observed objects.
	  									  + stellar, extended, resolved, mottled, unusualShape, partlyUnresolved, colorContrasts: The extra parameters of the observations. 1 if true, 0 if false.
	  										+ clusterType: The cluster type of the observations (From "A" to "I" or "X").
	  										+ minSQM, maxSQM: The interval of SQM values for the observations.
		@param $seenpar TO FIND OUT WHAT THIS PARAMETER MEANS!!!
		@param $exactinstrumentlocation TO FIND OUT WHAT THIS PARAMETER MEANS!!!
	*/
	public function getObservationFromQuery($queries, $seenpar = "A", $exactinstrumentlocation = "0") // returns an array with the names of all observations where the queries are defined in an array.
	{
		global $objInstrument, $objEyepiece, $objFilter, $objLens, $objLocation, $objDatabase, $loggedUser;
		$object = "";
		$sqland = "";
		$alternative = "";
		if (! array_key_exists ( 'countquery', $queries ))
			$sql1 = "SELECT DISTINCT observations.id as observationid,
									                       observations.objectname as objectname,
												  							 observations.date as observationdate,
																				 observations.description as observationdescription,
						  													 observers.id as observerid,
																				 CONCAT(observers.firstname , ' ' , observers.name) as observername,
						  													 CONCAT(observers.name , ' ' , observers.firstname) as observersortname,
																				 objects.con as objectconstellation,
																				 objects.type as objecttype,
																				 objects.mag as objectmagnitude,
																				 objects.subr as objectsurfacebrigthness,
																				 instruments.id as instrumentid,
																				 instruments.name as instrumentname,
																				 instruments.diameter as instrumentdiameter,
						  													 CONCAT(10000+instruments.diameter,' mm ',instruments.name) as instrumentsort
																				 ";
		else
			$sql1 = "SELECT count(DISTINCT observations.id) as ObsCnt ";
		$sql2 = $sql1;
		$sql1 .= "FROM observations " . "JOIN instruments on observations.instrumentid=instruments.id " . "JOIN objects on observations.objectname=objects.name " . "JOIN locations on observations.locationid=locations.id " . "JOIN objectnames on observations.objectname=objectnames.objectname " . "JOIN observers on observations.observerid=observers.id ";
		$sql2 .= "FROM observations " . "JOIN objectpartof on objectpartof.objectname=observations.objectname " . "JOIN instruments on observations.instrumentid=instruments.id " . "JOIN objects on observations.objectname=objects.name " . "JOIN locations on observations.locationid=locations.id " . "JOIN objectnames on objectpartof.partofname=objectnames.objectname " . "JOIN observers on observations.observerid=observers.id ";
		if (array_key_exists ( 'object', $queries ) && ($queries ["object"] != ""))
			$sqland .= "AND (objectnames.altname like \"" . $queries ["object"] . "\") ";
			// $sqland .= " AND (CONCAT(UPPER(objectnames.catalog),UPPER(objectnames.catindex)) like \"" . strtoupper(str_replace(' ','',$queries["object"])) . "\") ";
		elseif (array_key_exists ( 'catalog', $queries ) && $queries ["catalog"] && $queries ['catalog'] != '%')
			$sqland .= "AND (objectnames.altname like \"" . trim ( $queries ["catalog"] . ' ' . $queries ['number'] . '%' ) . "\") ";
		elseif (array_key_exists ( 'number', $queries ) && $queries ['number'])
			$sqland .= "AND (objectnames.altname like \"" . trim ( $queries ["number"] ) . "\") ";
		$sqland .= (isset ( $queries ["observer"] ) && $queries ["observer"]) ? " AND observations.observerid = \"" . $queries ["observer"] . "\" " : '';
		if (isset ( $queries ["instrument"] ) && ($queries ["instrument"] != "")) {
			$sqland .= "AND (observations.instrumentid = \"" . $queries ["instrument"] . "\" ";
			if (! $exactinstrumentlocation) {
				$insts = $objInstrument->getAllInstrumentsIds ( $queries ["instrument"] );
				while ( list ( $key, $value ) = each ( $insts ) )
					$sqland .= " || observations.instrumentid = \"" . $value . "\" ";
			}
			$sqland .= ") ";
		}
		if (isset ( $queries ["eyepiece"] ) && ($queries ["eyepiece"] != "")) {
			$sqland .= "AND (observations.eyepieceid = \"" . $queries ["eyepiece"] . "\" ";
			if (! $exactinstrumentlocation) {
				$eyeps = $objEyepiece->getAllEyepiecesIds ( $queries ["eyepiece"] );
				while ( list ( $key, $value ) = each ( $eyeps ) )
					$sqland .= " || observations.eyepieceid = \"" . $value . "\" ";
			}
			$sqland .= ") ";
		}
		if (isset ( $queries ["filter"] ) && ($queries ["filter"] != "")) {
			$sqland .= " AND (observations.filterid = \"" . $queries ["filter"] . "\" ";
			if (! $exactinstrumentlocation) {
				$filts = $objFilter->getAllFiltersIds ( $queries ["filter"] );
				while ( list ( $key, $value ) = each ( $filts ) )
					$sqland .= " || observations.filterid = \"" . $value . "\" ";
			}
			$sqland .= ") ";
		}
		if (isset ( $queries ["lens"] ) && ($queries ["lens"] != "")) {
			$sqland .= "AND (observations.lensid = \"" . $queries ["lens"] . "\" ";
			if (! $exactinstrumentlocation) {
				$lns = $objLens->getAllLensesIds ( $queries ["lens"] );
				while ( list ( $key, $value ) = each ( $lns ) )
					$sqland .= " || observations.lensid = \"" . $value . "\" ";
			}
			$sqland .= ") ";
		}
		if (isset ( $queries ["location"] ) && ($queries ["location"] != "")) {
			$sqland .= "AND (observations.locationid=" . $queries ["location"] . " ";
			if (! $exactinstrumentlocation) {
				$locs = $objLocation->getAllLocationsIds ( $queries ["location"] );
				while ( list ( $key, $value ) = each ( $locs ) )
					if ($value != $queries ["location"])
						$sqland .= " || observations.locationid = " . $value . " ";
			}
			$sqland .= ") ";
		}
		if (isset ( $queries ["maxdate"] ) && ($queries ["maxdate"] != ""))
			if (strlen ( $queries ["maxdate"] ) > 4)
				$sqland .= "AND observations.date <= \"" . $queries ["maxdate"] . "\" ";
			else
				$sqland .= "AND RIGHT(observations.date,4) <= \"" . $queries ["maxdate"] . "\" ";
		if (isset ( $queries ["mindate"] ) && ($queries ["mindate"] != ""))
			if (strlen ( $queries ["mindate"] ) > 4)
				$sqland .= "AND observations.date >= \"" . $queries ["mindate"] . "\" ";
			else
				$sqland .= "AND RIGHT(observations.date,4) >= \"" . $queries ["mindate"] . "\" ";
		$sqland .= (isset ( $queries ["description"] ) && $queries ["description"]) ? "AND observations.description like \"%" . $queries ["description"] . "%\" " : '';
		$sqland .= (isset ( $queries ["mindiameter"] ) && $queries ["mindiameter"]) ? "AND instruments.diameter >= \"" . $queries ["mindiameter"] . "\" " : '';
		$sqland .= (isset ( $queries ["maxdiameter"] ) && $queries ["maxdiameter"]) ? "AND instruments.diameter <= \"" . $queries ["maxdiameter"] . "\" " : '';
		$sqland .= (isset ( $queries ["type"] ) && $queries ["type"]) ? "AND objects.type = \"" . $queries ["type"] . "\" " : '';
		$sqland .= (isset ( $queries ["con"] ) && $queries ["con"]) ? "AND objects.con = \"" . $queries ["con"] . "\" " : '';
		$sqland .= (isset ( $queries ["minmag"] ) && (strcmp ( $queries ["minmag"], "" ) != 0)) ? "AND (objects.mag > \"" . $queries ["minmag"] . "\" OR objects.mag like \"" . $queries ["minmag"] . "\") AND (objects.mag < 99)" : '';
		if (isset ( $queries ["maxmag"] ) && (strcmp ( $queries ["maxmag"], "" ) != 0))
			$sqland .= "AND (objects.mag < \"" . $queries ["maxmag"] . "\" OR objects.mag like \"" . $queries ["maxmag"] . "\") ";
		if (isset ( $queries ["minsb"] ) && (strcmp ( $queries ["minsb"], "" ) != 0))
			$sqland .= "AND objects.subr >= \"" . $queries ["minsb"] . "\" ";
		if (isset ( $queries ["maxsb"] ) && (strcmp ( $queries ["maxsb"], "" ) != 0))
			$sqland .= "AND objects.subr <= \"" . $queries ["maxsb"] . "\" ";
		if (isset ( $queries ["minra"] ) && (strcmp ( $queries ["minra"], "" ) != 0))
			$sqland .= "AND (objects.ra >= \"" . $queries ["minra"] . "\" OR objects.ra like \"" . $queries ["minra"] . "\") ";
		if (isset ( $queries ["maxra"] ) && (strcmp ( $queries ["maxra"], "" ) != 0))
			$sqland .= "AND (objects.ra <= \"" . $queries ["maxra"] . "\" OR objects.ra like \"" . $queries ["maxra"] . "\") ";
		if (isset ( $queries ["mindecl"] ) && (strcmp ( $queries ["mindecl"], "" ) != 0))
			$sqland .= "AND objects.decl >= \"" . $queries ["mindecl"] . "\" ";
		if (isset ( $queries ["maxdecl"] ) && (strcmp ( $queries ["maxdecl"], "" ) != 0))
			$sqland .= "AND objects.decl <= \"" . $queries ["maxdecl"] . "\" ";
		if (isset ( $queries ["minLat"] ) && (strcmp ( $queries ["minLat"], "" ) != 0))
			$sqland .= "AND locations.latitude >= " . $queries ["minLat"] . " ";
		if (isset ( $queries ["maxLat"] ) && (strcmp ( $queries ["maxLat"], "" ) != 0))
			$sqland .= "AND locations.latitude <= " . $queries ["maxLat"] . " ";
		if (isset ( $queries ["mindiam1"] ) && (strcmp ( $queries ["mindiam1"], "" ) != 0))
			$sqland .= "AND (objects.diam1 > \"" . $queries ["mindiam1"] . "\" or objects.diam1 like \"" . $queries ["mindiam1"] . "\") ";
		if (isset ( $queries ["maxdiam1"] ) && (strcmp ( $queries ["maxdiam1"], "" ) != 0))
			$sqland .= "AND (objects.diam1 <= \"" . $queries ["mindiam1"] . "\" or objects.diam1 like \"" . $queries ["maxdiam1"] . "\") ";
		if (isset ( $queries ["mindiam2"] ) && (strcmp ( $queries ["mindiam2"], "" ) != 0))
			$sqland .= "AND (objects.diam2 > \"$diam2\" or objects.diam2 like \"" . $queries ["mindiam2"] . "\") ";
		if (isset ( $queries ["maxdiam2"] ) && (strcmp ( $queries ["maxdiam2"], "" ) != 0))
			$sqland .= "AND (objects.diam2 <= \"$diam2\" or objects.diam2 like \"" . $queries ["mindiam2"] . "\") ";
		$sqland .= (isset ( $queries ["atlas"] ) && $queries ["atlas"] && isset ( $queries ["atlasPageNumber"] ) && $queries ["atlasPageNumber"]) ? "AND " . $queries ["atlas"] . "=\"" . $queries ["atlasPageNumber"] . "\" " : '';
		if (isset ( $queries ["minvisibility"] ) && ($queries ["minvisibility"] != ""))
			$sqland .= "AND observations.visibility <= \"" . $queries ["minvisibility"] . "\" AND observations.visibility >= \"1\" ";
		if (isset ( $queries ["maxvisibility"] ) && ($queries ["maxvisibility"] != ""))
			$sqland .= "AND observations.visibility >= \"" . $queries ["maxvisibility"] . "\" ";
		if (isset ( $queries ["minseeing"] ) && ($queries ["minseeing"] != ""))
			$sqland .= "AND observations.seeing <= \"" . $queries ["minseeing"] . "\" ";
		if (isset ( $queries ["maxseeing"] ) && ($queries ["maxseeing"] != ""))
			$sqland .= "AND observations.seeing >= \"" . $queries ["maxseeing"] . "\" ";
		if (isset ( $queries ["minlimmag"] ) && ($queries ["minlimmag"] != ""))
			$sqland .= "AND observations.limmag >= \"" . $queries ["minlimmag"] . "\" ";
		if (isset ( $queries ["maxlimmag"] ) && ($queries ["maxlimmag"] != ""))
			$sqland .= "AND observations.limmag <= \"" . $queries ["maxlimmag"] . "\" ";
		if (isset ( $queries ["minSmallDiameter"] ) && ($queries ["minSmallDiameter"] != ""))
			$sqland .= "AND observations.smallDiameter >= \"" . $queries ["smallDiameter"] . "\" ";
		if (isset ( $queries ["maxSmallDiameter"] ) && ($queries ["maxSmallDiameter"] != ""))
			$sqland .= "AND observations.smallDiameter <= \"" . $queries ["smallDiameter"] . "\" ";
		if (isset ( $queries ["minLargeDiameter"] ) && ($queries ["minLargeDiameter"] != ""))
			$sqland .= "AND observations.largeDiameter >= \"" . $queries ["largeDiameter"] . "\" ";
		if (isset ( $queries ["maxLargeDiameter"] ) && ($queries ["maxLargeDiameter"] != ""))
			$sqland .= "AND observations.largeDiameter <= \"" . $queries ["largeDiameter"] . "\" ";
		if (isset ( $queries ["stellar"] ) && ($queries ["stellar"] != ""))
			$sqland .= "AND observations.stellar = \"" . $queries ["stellar"] . "\" ";
		if (isset ( $queries ["extended"] ) && ($queries ["extended"] != ""))
			$sqland .= "AND observations.extended = \"" . $queries ["extended"] . "\" ";
		if (isset ( $queries ["resolved"] ) && ($queries ["resolved"] != ""))
			$sqland .= "AND observations.resolved = \"" . $queries ["resolved"] . "\" ";
		if (isset ( $queries ["mottled"] ) && ($queries ["mottled"] != ""))
			$sqland .= "AND observations.mottled = \"" . $queries ["mottled"] . "\" ";
		if (isset ( $queries ["clusterType"] ) && ($queries ["clusterType"] != ""))
			$sqland .= "AND observations.clusterType = \"" . $queries ["clusterType"] . "\" ";
		if (isset ( $queries ["unusualShape"] ) && ($queries ["unusualShape"] != ""))
			$sqland .= "AND observations.unusualShape = \"" . $queries ["unusualShape"] . "\" ";
		if (isset ( $queries ["partlyUnresolved"] ) && ($queries ["partlyUnresolved"] != ""))
			$sqland .= "AND observations.partlyUnresolved = \"" . $queries ["partlyUnresolved"] . "\" ";
		if (isset ( $queries ["colorContrasts"] ) && ($queries ["colorContrasts"] != ""))
			$sqland .= "AND observations.colorContrasts = \"" . $queries ["colorContrasts"] . "\" ";
		if (isset ( $queries ["minSQM"] ) && ($queries ["minSQM"] != ""))
			$sqland .= "AND observations.SQM >= \"" . $queries ["minSQM"] . "\" ";
		if (isset ( $queries ["maxSQM"] ) && ($queries ["maxSQM"] != ""))
			$sqland .= "AND observations.SQM <= \"" . $queries ["minSQM"] . "\" ";
		if (isset ( $queries ["hasDrawing"] ) && ($queries ["hasDrawing"] == 'on'))
			$sqland .= "AND observations.hasDrawing=TRUE ";
		if (isset ( $queries ["hasNoDrawing"] ) && ($queries ["hasNoDrawing"] == 'on'))
			$sqland .= "AND observations.hasDrawing=FALSE ";
		if (isset ( $queries ["minobservation"] ) && ($queries ["minobservation"] != ''))
			$sqland .= "AND observations.id> " . $queries ["minobservation"] . " ";
		if ((! array_key_exists ( 'countquery', $queries )) && (isset ( $queries ["languages"] ))) {
			$extra2 = "";
			for($i = 0; $i < count ( $queries ["languages"] ); $i ++)
				$extra2 .= "OR observations.language=\"" . $queries ["languages"] [$i] . "\" ";
			if ($extra2)
				$sqland .= " AND (" . substr ( $extra2, 3 ) . ") ";
		}
		$sql = "(" . $sql1;
		if ($sqland)
			$sql .= " WHERE " . substr ( $sqland, 4 );
		if (array_key_exists ( 'object', $queries ) && ($queries ["object"] != "") && (! array_key_exists ( 'countquery', $queries ))) {
			$sql .= ") UNION (" . $sql2;
			if ($sqland)
				$sql .= " WHERE " . substr ( $sqland, 4 );
		}
		$sql .= ")";
		if (! array_key_exists ( 'countquery', $queries ))
			$sql .= " ORDER BY observationid DESC";
		$sql = $sql . ";";
		// echo $sql.'<p>'; //=========================================================== HANDY DEBUG LINE
		$run = $objDatabase->selectRecordset ( $sql );
		if (! array_key_exists ( 'countquery', $queries )) {
			$j = 0;
			$result = array ();
			while ( $get = $run->fetch ( PDO::FETCH_OBJ ) ) {
				$seentype = "X";
				if (array_key_exists ( 'deepskylog_id', $_SESSION ) && ($seenpar != "A"))
					if ($objDatabase->SelectSingleValue ( "SELECT observations.id FROM observations WHERE objectname = \"" . $get->objectname . "\" AND observerid = \"" . $loggedUser . "\"", 'id' )) // object has been seen by the observer logged in
						$seentype = "Y";
				if (($seenpar == "A") || ($seenpar == $seentype)) {
					while ( list ( $key, $value ) = each ( $get ) )
						$result [$j] [$key] = $value;
					$j ++;
				}
			}
			return $result;
		} else {
			$get = $run->fetch ( PDO::FETCH_OBJ );
			return $get->ObsCnt;
		}
	}
	public function getDrawingsLastYear($id) {
		global $objDatabase;
		$t = getdate ();
		return $objDatabase->selectSingleValue ( "SELECT COUNT(*) AS Cnt FROM observations WHERE observations.observerid LIKE \"" . $id . "\" AND observations.date > \"" . date ( 'Ymd', strtotime ( '-1 year' ) ) . "\" AND observations.visibility != 7 AND hasDrawing=1 ", 'Cnt', 0 );
	}
	public function getObservationsLastYear($id, $country = "") {
		global $objDatabase;
		$t = getdate ();

		if (strcmp($country, "") == 0) {
			return $objDatabase->selectSingleValue ( "SELECT COUNT(*) AS Cnt FROM observations WHERE observations.observerid LIKE \"" . $id . "\" AND observations.date > \"" . date ( 'Ymd', strtotime ( '-1 year' ) ) . "\" AND observations.visibility != 7 ", 'Cnt', 0 );
		} else {
			return $objDatabase->selectSingleValue ( "SELECT COUNT(objectname) As Cnt FROM observations JOIN locations ON observations.locationid=locations.id WHERE observations.date > \"" . date ( 'Ymd', strtotime ( '-1 year' ) ) . "\" AND observations.visibility != 7 and locations.country=\"" . $country . "\"", 'Cnt', 0 );
		}
	}
	public function getObservationsUserObject($userid, $object) {
		global $objDatabase;
		return $objDatabase->selectSingleValue ( "SELECT COUNT(*) As ObsCnt FROM observations WHERE observerid=\"" . $userid . "\" AND observations.objectname=\"" . $object . "\"", "ObsCnt" );
	}
	public function getObservedCountFromCatalogOrList($id, $catalog) {
		global $objDatabase, $loggedUser;
		if (substr ( $catalog, 0, 5 ) == 'List:') {
			$sql = "SELECT COUNT(DISTINCT observations.objectname) AS CatCnt " . "FROM observations " . "JOIN observerobjectlist on observerobjectlist.objectname=observations.objectname " . "JOIN observers on observations.observerid = observers.id " . "WHERE observerobjectlist.listname=\"" . substr ( $catalog, 5 ) . "\" " . "AND observations.observerid=\"" . $id . "\" " . "AND observations.visibility != 7 ";
		} else {
			$sql = "SELECT COUNT(DISTINCT objectnames.catindex) AS CatCnt FROM objectnames " . "INNER JOIN observations ON observations.objectname = objectnames.objectname " . "WHERE objectnames.catalog = \"" . $catalog . "\" " . "AND observations.observerid=\"" . $id . "\" " . "AND observations.visibility != 7 ";
		}
		return $objDatabase->selectSingleValue ( $sql, 'CatCnt', 0 );
	}
	public function getDrawingsCountFromCatalog($id, $catalog) {
		global $objDatabase, $loggedUser;
		$sql = "SELECT COUNT(DISTINCT objectnames.catindex) AS CatCnt FROM objectnames " . "INNER JOIN observations ON observations.objectname = objectnames.objectname " . "WHERE objectnames.catalog = \"" . $catalog . "\" " . "AND observations.observerid=\"" . $id . "\" " . "AND observations.visibility != 7 " . "AND observations.hasDrawing = 1";
		return $objDatabase->selectSingleValue ( $sql, 'CatCnt', 0 );
	}
	public function getObservedFromCatalog($id, $catalog) {
		global $objDatabase, $loggedUser;
		if (substr ( $catalog, 0, 5 ) == "List:")
			$sql = "SELECT DISTINCT observerobjectlist.objectname FROM observerobjectlist " . "INNER JOIN observations ON observations.objectname = observerobjectlist.objectname " . "WHERE ((observerobjectlist.listname = \"" . substr ( $catalog, 5 ) . "\") " . "AND (observations.observerid = \"" . $id . "\") " . "AND (observations.visibility != 7))";
		else
			$sql = "SELECT DISTINCT CONCAT(objectnames.catindex,' ',objectnames.objectname) AS Temp, objectnames.objectname FROM objectnames " . "INNER JOIN observations ON observations.objectname = objectnames.objectname " . "WHERE ((objectnames.catalog = \"$catalog\") " . "AND (observations.observerid=\"$id\") " . "AND (observations.visibility != 7))";
		return $objDatabase->selectSingleArray ( $sql, 'objectname' );
	}
	public function getObservedFromCatalogPartOf($id, $catalog) {
		global $objDatabase, $loggedUser;
		if (substr ( $catalog, 0, 5 ) == "List:")
			$sql = "SELECT DISTINCT observerobjectlist.objectname FROM observerobjectlist " . " JOIN objectpartof ON objectpartof.partofname = observerobjectlist.objectname " . " JOIN observations ON observations.objectname = objectpartof.objectname " . " WHERE ((observerobjectlist.listname = \"" . substr ( $catalog, 5 ) . "\") " . " AND (observations.observerid = \"" . $id . "\") " . " AND (observations.visibility != 7))";
		else
			$sql = "SELECT DISTINCT objectnames.objectname FROM objectnames " . " JOIN objectpartof ON objectpartof.partofname = objectnames.objectname " . " JOIN observations ON observations.objectname = objectpartof.objectname " . " WHERE ((objectnames.catalog = \"$catalog\") " . " AND (observations.observerid=\"$id\") " . " AND (observations.visibility != 7))";
		return $objDatabase->selectSingleArray ( $sql, 'objectname' );
	}
	public function getPopularObservations() {
		// returns the number of observations of the objects
		global $objDatabase;
		$run = $objDatabase->selectRecordset ( "SELECT observations.objectname, COUNT(observations.id) As ObservationCount FROM observations GROUP BY observations.objectname ORDER BY ObservationCount DESC" );
		$i = 1;
		while ( $run->fetch ( PDO::FETCH_OBJ ) )
			$numberOfObservations [$get->objectname] = array (
					$i ++,
					$get->objectname
			);
		return $numberOfObservations;
	}
	public function getPopularObservers() {
		// returns the number of observations of the observers
		global $objDatabase;
		return $objDatabase->selectSingleArray ( "SELECT observations.observerid, COUNT(observations.id) As Cnt FROM observations where observations.visibility != 7 GROUP BY observations.observerid ORDER BY Cnt DESC", 'observerid' );
	}
	public function getPopularObserversOverviewCatOrList($sort, $cat = "") {
		global $objDatabase, $loggedUser;
		$sql = "SELECT observations.observerid, COUNT(*) AS Cnt " . "FROM observations " . "JOIN observers on observations.observerid = observers.id WHERE observations.visibility != 7 ";
		$sql .= "GROUP BY observations.observerid, observers.name ";
		$sql .= "ORDER BY Cnt DESC, observers.name ASC ";
		return $objDatabase->selectKeyValueArray ( $sql, 'observerid', 'Cnt' );
	}
	public function getPopularObserversOverviewCatOrListAllInfo() {
		global $objDatabase;
		$sql = "SELECT observations.observerid, CONCAT(observers.firstname,' ',observers.name) As observername, COUNT(*) AS Cnt " . "FROM observations " . "JOIN observers on observations.observerid = observers.id WHERE observations.visibility != 7 ";
		$sql .= "GROUP BY observations.observerid, observers.name ";
		$sql .= "ORDER BY Cnt DESC, observers.name ASC;";

		return $objDatabase->selectRecordsetArray($sql);
	}
	public function getDsDrawingsCount() {
		global $objDatabase;
		$sql = "SELECT observerid, COUNT(*) AS Cnt " . "FROM observations " .
						" WHERE visibility != 7 AND hasDrawing=1 ";
		$sql .= "GROUP BY observerid ";

		return $objDatabase->selectKeyValueArray($sql, "observerid", "Cnt");
	}
	public function getAllObservationsLastYearCount() {
		global $objDatabase;
		$t = getdate ();

		global $objDatabase;
		$sql = "SELECT observerid, COUNT(*) AS Cnt " . "FROM observations " .
											"WHERE visibility != 7
											  AND date > \"" .
												date ( 'Ymd', strtotime ( '-1 year' ) ) . "\"";
		$sql .= "GROUP BY observerid ";

		return $objDatabase->selectKeyValueArray($sql, "observerid", "Cnt");
	}
	public function getAllDrawingsLastYearCount() {
		global $objDatabase;
		$t = getdate ();

		global $objDatabase;
		$sql = "SELECT observerid, COUNT(*) AS Cnt " . "FROM observations " .
											"WHERE visibility != 7 AND hasDrawing = 1
											  AND date > \"" .
												date ( 'Ymd', strtotime ( '-1 year' ) ) . "\"";
		$sql .= "GROUP BY observerid ";

		return $objDatabase->selectKeyValueArray($sql, "observerid", "Cnt");
	}
	public function getNumberOfObjectsCount()
	{
		global $objDatabase;
		$sql = "SELECT observerid, COUNT(DISTINCT objectname) As Cnt FROM observations WHERE visibility != 7 ";
		$sql .= "GROUP BY observerid ";
		return $objDatabase->selectKeyValueArray($sql, "observerid", "Cnt");
	}
	public function getAllObservedCountFromCatalogOrList($catalog) {
		global $objDatabase;
		if (substr ( $catalog, 0, 5 ) == 'List:') {
			$sql = "SELECT observations.observerid, COUNT(DISTINCT observations.objectname) AS Cnt " . "FROM observations " . "JOIN observerobjectlist on observerobjectlist.objectname=observations.objectname " . "JOIN observers on observations.observerid = observers.id " . "WHERE observerobjectlist.listname=\"" . substr ( $catalog, 5 ) . "\" " . "AND observations.visibility != 7 ";
		} else {
			$sql = "SELECT observerid, COUNT(DISTINCT objectnames.catindex) AS Cnt FROM objectnames " . "INNER JOIN observations ON observations.objectname = objectnames.objectname " . "WHERE objectnames.catalog = \"" . $catalog . "\" " . "AND observations.visibility != 7 ";
		}
		$sql .= "GROUP BY observerid ";

		return $objDatabase->selectKeyValueArray($sql, "observerid", "Cnt");
	}
	public function setDsObservationProperty($id, $property, $propertyValue) // sets the property to the specified value for the given observation
{
		global $objDatabase;
		return $objDatabase->execSQL ( "UPDATE observations SET " . $property . " = " . (($propertyValue == "NULL") ? "NULL" : "\"" . $propertyValue . "\"") . " WHERE id = \"" . $id . "\"" );
	}
	public function setLocalDateAndTime($id, $date, $time) // sets the date and time for the given observation when the time is given in local time
{
		global $objDatabase, $objLocation;
		if ($time >= 0) {
			$timezone = $objLocation->getLocationPropertyFromId ( $this->getDsObservationProperty ( $id, 'locationid' ), 'timezone' );
			$datearray = sscanf ( $date, "%4d%2d%2d" );
			$dateTimeZone = new DateTimeZone ( $timezone );
			$date = sprintf ( "%02d", $datearray [1] ) . "/" . sprintf ( "%02d", $datearray [2] ) . "/" . $datearray [0];
			$dateTime = new DateTime ( $date, $dateTimeZone );
			// Returns the timedifference in seconds
			$timedifference = $dateTimeZone->getOffset ( $dateTime );
			$timedifference = $timedifference / 3600.0;
			$timestr = sscanf ( sprintf ( "%04d", $time ), "%2d%2d" );
			$jd = cal_to_jd ( CAL_GREGORIAN, $datearray [1], $datearray [2], $datearray [0] );
			$hours = $timestr [0] - ( int ) $timedifference;
			$timedifferenceminutes = ($timedifference - ( int ) $timedifference) * 60;
			$minutes = $timestr [1] - $timedifferenceminutes;
			if ($minutes < 0) {
				$hours = $hours - 1;
				$minutes = $minutes + 60;
			}
			if ($minutes > 60) {
				$hours = $hours + 1;
				$minutes = $minutes - 60;
			}
			if ($hours < 0) {
				$hours = $hours + 24;
				$jd = $jd - 1;
			}
			if ($hours >= 24) {
				$hours = $hours - 24;
				$jd = $jd + 1;
			}
			$time = $hours * 100 + $minutes;
			$dte = JDToGregorian ( $jd );
			sscanf ( $dte, "%2d/%2d/%4d", $month, $day, $year );
			$date = $year . sprintf ( "%02d", $month ) . sprintf ( "%02d", $day );
		}
		$objDatabase->execSQL ( "UPDATE observations SET date = \"" . $date . "\" WHERE id = \"" . $id . "\"" );
		$objDatabase->execSQL ( "UPDATE observations SET time = \"" . $time . "\" WHERE id = \"" . $id . "\"" );
	}
	public function getLastObservations($number = 10) {
		// TODO : Implement
		// global $objDatabase;
		// $run = $objDatabase->selectRecordset("select count(DISTINCT id) from observations order by id desc LIMIT " . $number . ";");
		// $get = $run->fetch ( PDO::FETCH_OBJ );

		// return $get->ObsCnt;
	}
	public function showListObservation($link, $lco) {
		global $lastReadObservation, $objDatabase, $objObject, $baseURL, $loggedUser, $objObserver, $dateformat, $myList, $objUtil, $objInstrument, $listname, $listname_ss, $objPresentations, $objObservation;

		// Add a google translate button
		echo "<script>
		       function googleSectionalElementInit() {
		          new google.translate.SectionalElement({
		              sectionalNodeClassName: 'goog-trans-section',
		              controlNodeClassName: 'goog-trans-control',
			          background: '#f4fa58'
		          }, 'google_sectional_element');
		          }
		       </script>";
		$usedLang = $objObserver->getObserverProperty ( $loggedUser, "language" );
		echo "<script src=\"//translate.google.com/translate_a/element.js?cb=googleSectionalElementInit&ug=section&hl=" . $usedLang . "\"></script>";

		$parsed = parse_url ( htmlspecialchars_decode ( $link ), PHP_URL_QUERY );
		parse_str ( $parsed, $query );

		if (array_key_exists ( 'object', $query )) {
			$queries = array (
					"object" => $query ['object']
			);
			$_SESSION ['Qobs'] = $objObservation->getObservationFromQuery ( $queries );
		}
		echo "<table class=\"table sort-tableObject tablesorter custom-popup\">";
		echo "<thead>";
		echo "<tr>";
		echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\" width=\"10px\">";
		echo "&nbsp;";
		if (($loggedUser) && ($lastReadObservation >= 0))
			echo "<a href=\"" . $link . "&amp;markAsRead=All\" title=\"" . LangMarkAllAsRead . "\">!</a>";
		echo "</th>";
		if ($myList)
			echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">&nbsp;</th>";
		echo "<th id=\"objectname\">" . LangOverviewObservationsHeader1 . "</th>";
		echo "<th id=\"objectconstellation\">" . LangViewObservationField1b . "</th>";
		echo "<th id=\"observername\">" . LangOverviewObservationsHeader2 . "</th>";
		echo "<th id=\"instrumentname\">" . LangOverviewObservationsHeader3 . "</th>";
		echo "<th id=\"observationdate\">" . LangOverviewObservationsHeader4 . "</th>";
		if ($lco != "O") {
			echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">&nbsp;</th>";
		} else {
			echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">" . LangOverviewObservationsHeader8 . "</th>" . "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">" . LangOverviewObservationsHeader9 . "</th>" . "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\">" . LangOverviewObservationsHeader5 . "</th>";
		}
		echo "</tr>";
		echo "</thead>";
		echo "<tbody id=\"obs_list\" class=\"tbody_obs\">";
		$count = 0;
		if (! array_key_exists ( 'Qobs', $_SESSION )) {
			// TODO : Get the new observations.
			// $_SESSION ['Qobs'] = $objObservation->get
			// TODO : If this array is empty, get the 10 last observations
			$_SESSION ['Qobs'] = $objObservation->getLastObservations ();
		}
		while ( list ( $key, $value ) = each ( $_SESSION ['Qobs'] ) ) {
			$obsKey = $key;
			$LOid = "";
			$LOinstrumentsize = '';
			$LOdescription = "";
			$LOinstrumentId = '';
			$LOinstrument = '';
			$value = $_SESSION ['Qobs'] [$obsKey];
			$alt = "";
			$altnames = $objObject->getAlternativeNames ( $value ['objectname'] );
			while ( list ( $key, $altvalue ) = each ( $altnames ) )
				if (trim ( $altvalue ) != trim ( $value ['objectname'] ))
					$alt .= "<br />" . trim ( $altvalue );
			$alt = substr ( $alt, 6 );
			$explanation = "(" . $GLOBALS [$value ['objecttype']] . " " . LangOverviewObservations12 . " " . $GLOBALS [$value ['objectconstellation']] . (($value ['objectmagnitude'] != '') && ($value ['objectmagnitude'] < 99.9) ? ", " . LangOverviewObservations13 . " " . sprintf ( "%.1f", $value ['objectmagnitude'] ) : "") . (($value ['objectsurfacebrigthness'] != '') && ($value ['objectsurfacebrigthness'] < 99.9) ? ", " . LangOverviewObservations14 . " " . sprintf ( "%.1f", $value ['objectsurfacebrigthness'] ) : "") . (($alt) ? (", " . LangOverviewObservations15 . " ") . $objPresentations->br2dash ( $alt ) : "") . ")";
			$explantation1 = LangOverviewObservations16 . " " . ($seen = $objObject->getseen ( $value ['objectname'] ));
			$title = trim ( $value ['objectname'] . " " . LangMessageBy . $value ['observername'] );

			if (($LOid = $this->getLOObservationId ( $value ['objectname'], $loggedUser, $value ['observationid'] )) && ($lco == "O")) {
				$LOdescription = $objPresentations->searchAndLinkCatalogsInText ( preg_replace ( "/&amp;/", "&", $this->getDsObservationProperty ( $LOid, 'description' ) ) );
				$LOinstrumentId = $this->getDsObservationProperty ( $LOid, 'instrumentid' );
				$LOinstrument = $objInstrument->getInstrumentPropertyFromId ( $LOinstrumentId, 'name' );
				$LOinstrumentsize = round ( $objInstrument->getInstrumentPropertyFromId ( $LOinstrumentId, 'diameter' ), 0 );
			}
			if ($LOinstrument == "Naked eye")
				$LOinstrument = InstrumentsNakedEye;
			if ($loggedUser && (! ($objObserver->getObserverProperty ( $loggedUser, 'UT' )))) {
				$date = sscanf ( $this->getDsObservationLocalDate ( $value ['observationid'] ), "%4d%2d%2d" );
				if ($lco == "O") {
					$LOdate = sscanf ( $this->getDsObservationLocalDate ( $LOid ), "%4d%2d%2d" );
				}
			} else {
				$date = sscanf ( $this->getDsObservationProperty ( $value ['observationid'], 'date' ), "%4d%2d%2d" );
				if ($lco == "O")
					$LOdate = sscanf ( $this->getDsObservationProperty ( $LOid, 'date' ), "%4d%2d%2d" );
			}
			if ($lco == 'L')
				if (($value ['observerid'] == $loggedUser) && ($objUtil->checkGetKey ( 'noOwnColor' ) == "no"))
					echo "<tr class=\"green\">";
				else
					echo "<tr class=\"type" . (2 - ($count % 2)) . "\">";
			else
				echo "<tr class=\"type" . (2 - ($count % 2)) . "\">";
			if ($lco == "L") {
				$rowspan = 1;
				$hasDrawing = false;
			} else {
				$rowspan = 2;
				$hasDrawing = $this->getDsObservationProperty ( $value ['observationid'], 'hasDrawing' );
				if ($hasDrawing) {
					$rowspan ++;
				}
				if ((($lco == "C") || ($lco == "O")) && ($objUtil->checkGetKey ( 'expand' ) != $value ['observationid']) && ($copyright = $objObserver->getObserverProperty ( $value ['observerid'], 'copyright' ))) {
					$rowspan ++;
				}
				if ($objUtil->checkGetKey ( 'expand' ) == $value ['observationid']) {
					$rowspan --;
				}
			}
			echo "<td rowspan=\"" . $rowspan . "\" class=\"centered\">";
			if ($objUtil->checkGetKey ( 'expand' ) == $value ['observationid'])
				echo "<a name=\"name" . $value ['observationid'] . "\" id=\"name" . $value ['observationid'] . "\" href=\"" . $link . "&amp;expand=0#name" . $value ['observationid'] . "\" title=\"" . $explantation1 . "\">" . "-" . "</a>";
			else
				echo "<a name=\"name" . $value ['observationid'] . "\" id=\"name" . $value ['observationid'] . "\" href=\"" . $link . "&amp;expand=" . $value ['observationid'] . "#name" . $value ['observationid'] . "\" title=\"" . $explantation1 . "\">" . ((substr ( $seen, 0, 1 ) != "Y") ? "x" : "+") . "</a>";
			if (($value ['observationid'] > $lastReadObservation) && ($lastReadObservation >= 0))
				echo "&nbsp;<a href=\"" . $link . "&amp;markAsRead=" . $value ['observationid'] . "\" title=\"" . LangMarkUpToHereAsRead . "\">!</a>";
			echo "</td>";
			if ($myList) {
				echo "<td>";
				if ($objDatabase->selectSingleValue ( "SELECT Count(observerobjectlist.objectname) As ObjCnt FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND objectname=\"" . $value ['objectname'] . "\" AND listname=\"" . $listname . "\"", 'ObjCnt', 0 ) > 0) {
					echo "<a  href=\"" . $link . "&amp;addObservationToList=" . urlencode ( $value ['observationid'] ) . "\" title=\"" . LangViewObservationField44 . "\">E</a>";
					echo "&nbsp;-&nbsp;";
					echo "<a  href=\"" . $link . "&amp;removeObjectFromList=" . urlencode ( $value ['objectname'] ) . "\" title=\"" . $value ['objectname'] . LangListQueryObjectsMessage3 . $listname_ss . "\">R</a>";
				} else {
					echo "<a  href=\"" . $link . "&amp;addObjectToList=" . urlencode ( $value ['objectname'] ) . "&amp;showname=" . urlencode ( $value ['objectname'] ) . "\" title=\"" . $value ['objectname'] . LangListQueryObjectsMessage2 . $listname_ss . "\">L</a>";
					echo "&nbsp;-&nbsp;";
					echo "<a  href=\"" . $link . "&amp;addObservationToList=" . urlencode ( $value ['observationid'] ) . "\" title=\"" . LangViewObservationField44 . "\">E</a>";
				}
				echo "</td>";
			}
			echo "<td><a  href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $value ['objectname'] ) . "\" title=\"" . $explanation . "\">" . $value ['objectname'] . "</a></td>";
			if ($objUtil->checkGetKey ( 'expand' ) == $value ['observationid']) {
				echo "<td colspan=\"" . ($myList ? (($lco == 'O') ? 6 : 4) : (($lco == 'O') ? 6 : 4)) . "\">" . $explanation . "</td>";
				echo "<td>";
				echo "<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;QobsKey=" . $obsKey . "&amp;dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText . ($hasDrawing ? LangDetailDrawingText : "") . "</a>&nbsp;";
				echo "<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;dalm=AO\" title=\"" . LangAO . "\">" . LangAOText . "</a>";
				if ($loggedUser && $LOid) {
					echo "&nbsp;<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;dalm=MO\" title=\"" . LangMO . "\">" . LangMOText . "</a>";
					echo "&nbsp;<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;dalm=LO\" title=\"" . LangLO . "\">" . LangLOText . "</a>";
				}
				echo "</td>";
				echo "</tr>";
				echo "<tr class=\"type" . (2 - ($count % 2)) . " tablesorter-childRow\">";
				echo "<td class=\"expandedObservation\" colspan=\"" . ($myList ? (($lco == 'O') ? 10 : 8) : (($lco == 'O') ? 8 : 6)) . "\">";
				$this->showObservation ( $value ['observationid'] );
				echo "</td>";
			} else {
				echo "<td>" . $GLOBALS [$value ['objectconstellation']] . "</td>";
				echo "<td><a   href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $value ['observerid'] ) . "\">" . $value ['observername'] . "</a></td>";
				echo "<td><a   href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $value ['instrumentid'] ) . "\">" . (($value ['instrumentname'] == "Naked eye") ? InstrumentsNakedEye : $value ['instrumentname'] . " &nbsp;(" . round ( $value ['instrumentdiameter'], 0 ) . "&nbsp;mm)") . "</a></td>";
				echo "<td>" . date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) ) . "</td>";
				if ($lco == "O") {
					echo "<td>" . (($LOid) ? "<a  href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $LOinstrumentId ) . "\">" . $LOinstrument . " &nbsp;" . (($LOinstrument != InstrumentsNakedEye) ? ("(" . $LOinstrumentsize . "&nbsp;mm" . ")") : "") . "</a>" : "") . "</td>";
					echo "<td>" . ((($lco == "O") && $LOid) ? date ( $dateformat, mktime ( 0, 0, 0, $LOdate [1], $LOdate [2], $LOdate [0] ) ) : "") . "</td>";
				}
				echo "<td>";
				echo "<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;QobsKey=" . $obsKey . "&amp;dalm=D\" title=\"" . LangDetail . "\">" . LangDetailText . (($this->getDsObservationProperty ( $value ['observationid'], 'hasDrawing' )) ? LangDetailDrawingText : "") . "</a>&nbsp;";
				echo "<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;dalm=AO\" title=\"" . LangAO . "\">" . LangAOText . "</a>";
				if ($loggedUser && $LOid) {
					echo "&nbsp;<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;dalm=MO\" title=\"" . LangMO . "\">" . LangMOText . "</a>";
					echo "&nbsp;<a  href=\"" . $baseURL . "index.php?indexAction=detail_observation&amp;observation=" . $value ['observationid'] . "&amp;dalm=LO\" title=\"" . LangLO . "\">" . LangLOText . "</a>";
				}
				echo "</td>";
			}
			echo "</tr>";
			if ($lco != 'L') {
				if ($objUtil->checkGetKey ( 'expand' ) != $value ['observationid']) {
					echo "<tr class=\"type" . (2 - ($count % 2)) . " tablesorter-childRow\">";
					echo "<td valign=\"top\">" . $alt . "</td>";
					if ($lco == "C") {
						echo "<td colspan=\"5\">";
						$toClose = false;
						if ($loggedUser != "") {
							if ($usedLang != $this->getDsObservationProperty ( $value ['observationid'], 'language' )) {
								$toClose = true;

								// Make the google translate control node
								echo "<div class=\"goog-trans-section\">";
								echo "<div class=\"goog-trans-control\">";
								echo "</div>";
							}
						}
						echo $objPresentations->searchAndLinkCatalogsInText ( $value ['observationdescription'] ) . "<br />";
						if ($toClose) {
							echo "</div>";
						}

						echo "</td>";
					} elseif ($lco == "O") {
						echo "<td colspan=\"4\">";
						$toClose = false;
						if ($loggedUser != "") {
							if ($usedLang != $this->getDsObservationProperty ( $value ['observationid'], 'language' )) {
								$toClose = true;
								// Make the google translate control node
								echo "<div class=\"goog-trans-section\">";
								echo "<div class=\"goog-trans-control\">";
								echo "</div>";
							}
						}
						echo $objPresentations->searchAndLinkCatalogsInText ( $value ['observationdescription'] ) . "<br />";
						if ($toClose) {
							echo "</div>";
						}

						echo "</td>";
						echo "<td colspan=\"4\">";
						$toClose = false;
						if ($loggedUser != "") {
							if ($usedLang != $this->getDsObservationProperty ( $LOid, 'language' )) {
								$toClose = true;
								// Make the google translate control node
								echo "<div class=\"goog-trans-section\">";
								echo "<div class=\"goog-trans-control\">";
								echo "</div>";
							}
						}
						echo $LOdescription . "<br />";
						if ($toClose) {
							echo "</div>";
						}
						echo "</td>";
					}
					echo "</tr>";
				}
				if ((($lco == "O") && $LOid && $hasDrawing) || $hasDrawing && ($objUtil->checkGetKey ( 'expand', 0 ) != $value ['observationid'])) {
					echo "<tr class=\"type" . (2 - ($count % 2)) . " tablesorter-childRow\">";
					if ($lco == "C") {
						echo "<td colspan=\"7\">" . (($this->getDsObservationProperty ( $value ['observationid'], 'hasDrawing' )) ? "<p>" . "<a  href=\"" . $baseURL . "deepsky/drawings/" . $value ['observationid'] . ".jpg\" data-lightbox=" . $value ['observationid'] . " data-title=\"\"><img class=\"account\" src=\"" . $baseURL . "deepsky/drawings/" . $value ['observationid'] . "_resized.jpg\" alt=\"" . $title . "\"></img></a>" . "</p>" : "") . "</td>";
					} elseif ($lco == "O") {
						if ($myList) {
							if (($objUtil->checkGetKey ( 'expand' ) == $value ['observationid'])) {
								echo "<td> &nbsp; </td><td colspan=\"6\"> &nbsp;</td>";
							} else {
								echo "<td> &nbsp; </td>
								  <td colspan=\"6\">" . (($this->getDsObservationProperty ( $value ['observationid'], 'hasDrawing' )) ? "<p>" . "<a  href=\"" . $baseURL . "deepsky/drawings/" . $value ['observationid'] . ".jpg\"  title=\"\">
								  	   <img class=\"account\" src=\"" . $baseURL . "deepsky/drawings/" . $value ['observationid'] . "_resized.jpg\" alt=\"" . $title . "\"></img>
								  	  </a>" . "</p>" : "") . "</td>";
							}
						} else {
							if (($objUtil->checkGetKey ( 'expand' ) == $value ['observationid'])) {
								echo "<td> &nbsp; </td><td colspan=\"6\"> &nbsp;</td>";
							} else {
								echo "<td colspan=\"6\">" . (($this->getDsObservationProperty ( $value ['observationid'], 'hasDrawing' )) ? "<p>" . "<a  href=\"" . $baseURL . "deepsky/drawings/" . $value ['observationid'] . ".jpg\" data-lightbox=\"image-1\" data-title=\"\">
									   <img class=\"account\" src=\"" . $baseURL . "deepsky/drawings/" . $value ['observationid'] . "_resized.jpg\" alt=\"" . $title . "\"></img>
									  </a>" . "</p>" : "") . "</td>";
							}
						}
						echo "<td colspan=\"4\">" . (($LOdescription && ($this->getDsObservationProperty ( $LOid, 'hasDrawing' ))) ? "<p>" . "<a  href=\"" . $baseURL . "deepsky/drawings/" . $LOid . ".jpg" . "\" data-lightbox=\"image-1\" data-title=\"\">
								       <img class=\"account\" src=\"" . $baseURL . "deepsky/drawings/" . $LOid . "_resized.jpg\" alt=\"" . $title . "\"></img>
								      </a>" . "</p>" : "") . "</td>";
					}
					echo "</tr>";
				}
			}
			if ((($lco == "C") || ($lco == "O")) && ($objUtil->checkGetKey ( 'expand' ) != $value ['observationid']) && ($copyright = $objObserver->getObserverProperty ( $value ['observerid'], 'copyright' )))
				echo "<tr class=\"copyright tablesorter-childRow\"><td colspan=\"" . (($lco == "O") ? 8 : 6) . "\">" . $copyright . "</td></tr>";
			$count ++;
		}

		echo "</tbody>";
		echo "</table>";

		$objUtil->addPager ( "Object", $count, false );
	}
	public function showObservation($LOid) {
		global $objUtil, $dateformat, $myList, $listname, $listname_ss, $baseURL, $objAstroCalc, $objEyepiece, $objObserver, $objInstrument, $loggedUser, $objObject, $objLens, $objFilter, $objPresentations, $objDatabase, $objLocation, $instDir;
		$link = $baseURL . "index.php?";
		$linkamp = "";
		reset ( $_GET );
		while ( list ( $key, $value ) = each ( $_GET ) )
			$linkamp .= $key . "=" . urlencode ( $value ) . "&amp;";
		$inst = $objInstrument->getInstrumentPropertyFromId ( $this->getDsObservationProperty ( $LOid, 'instrumentid' ), 'name' );
		if ($inst == "Naked eye")
			$inst = InstrumentsNakedEye;
		$dateTimeText = "";
		// $date=sscanf($this->getDsObservationProperty($LOid,'date'),"%4d%2d%2d");
		if ($loggedUser && (! ($objObserver->getObserverProperty ( $loggedUser, 'UT' ))))
			$date = sscanf ( $this->getDsObservationLocalDate ( $LOid ), "%4d%2d%2d" );
		else
			$date = sscanf ( $this->getDsObservationProperty ( $LOid, 'date' ), "%4d%2d%2d" );
		$time = "";
		$dateTimeLabelText = "";
		$dateTimeText = date ( $dateformat, mktime ( 0, 0, 0, $date [1], $date [2], $date [0] ) );
		if ($this->getDsObservationProperty ( $LOid, 'time' ) >= 0)
			if ($loggedUser && (! ($objObserver->getObserverProperty ( $loggedUser, 'UT' )))) {
				$date = sscanf ( $this->getDsObservationLocalDate ( $LOid ), "%4d%2d%2d" );
				$dateTimeLabelText = "&nbsp;" . LangViewObservationField9lt;
				$time = $this->getDsObservationLocalTime ( $LOid );
			} else {
				$dateTimeLabelText = "&nbsp;" . LangViewObservationField9;
				$time = $this->getDsObservationProperty ( $LOid, 'time' );
			}
		if ($time) {
			$time = sscanf ( sprintf ( "%04d", $time ), "%2d%2d" );
			$dateTimeText .= "&nbsp;" . $time [0] . ":" . sprintf ( "%02d", $time [1] );
		}
		if ($this->getDsObservationProperty ( $LOid, 'time' ) >= 0) {
			if ($time == 0) {
				$time = sscanf ( sprintf ( "%04d", $time ), "%2d%2d" );
				$dateTimeText .= "&nbsp;00:00";
			}
		}
		$seeing = $this->getDsObservationProperty ( $LOid, 'seeing' );
		if (($seeing < 0) || ($seeing > 5))
			$seeing = 0;
		$diameterText = '';
		if ($largeDiameter = $this->getDsObservationProperty ( $LOid, 'largeDiameter' ))
			if ($largeDiameter > 60)
				$diameterText = sprintf ( "%.1f ", $largeDiameter / 60.0 ) . (($smallDiameter = $this->getDsObservationProperty ( $LOid, 'smalldiameter' )) ? sprintf ( "x %.1f ", $smallDiameter / 60.0 ) : '') . LangNewObjectSizeUnits1;
			else
				$diameterText = sprintf ( "%.1f ", $largeDiameter ) . (($smallDiameter = $this->getDsObservationProperty ( $LOid, 'smalldiameter' )) ? sprintf ( "x %.1f ", $smallDiameter ) : '') . LangNewObjectSizeUnits2;
		else
			$diameterText = "-";
		$details1Text = "";
		if ($this->getDsObservationProperty ( $LOid, 'stellar' ) > 0)
			$details1Text .= ", " . LangViewObservationField35;
		if ($this->getDsObservationProperty ( $LOid, 'extended' ) > 0)
			$details1Text .= ", " . LangViewObservationField36;
		if ($this->getDsObservationProperty ( $LOid, 'resolved' ) > 0)
			$details1Text .= ", " . LangViewObservationField37;
		if ($this->getDsObservationProperty ( $LOid, 'mottled' ) > 0)
			$details1Text .= ", " . LangViewObservationField38;
		if ($this->getDsObservationProperty ( $LOid, 'component1' ) == 1)
			$details1Text .= ", " . LangDetailDSColor1;
		if ($this->getDsObservationProperty ( $LOid, 'component1' ) == 2)
			$details1Text .= ", " . LangDetailDSColor2;
		if ($this->getDsObservationProperty ( $LOid, 'component1' ) == 3)
			$details1Text .= ", " . LangDetailDSColor3;
		if ($this->getDsObservationProperty ( $LOid, 'component1' ) == 4)
			$details1Text .= ", " . LangDetailDSColor4;
		if ($this->getDsObservationProperty ( $LOid, 'component1' ) == 5)
			$details1Text .= ", " . LangDetailDSColor5;
		if ($this->getDsObservationProperty ( $LOid, 'component1' ) == 6)
			$details1Text .= ", " . LangDetailDSColor6;
		if ($this->getDsObservationProperty ( $LOid, 'component2' ) == 1)
			$details1Text .= "-" . LangDetailDSColor1;
		if ($this->getDsObservationProperty ( $LOid, 'component2' ) == 2)
			$details1Text .= "-" . LangDetailDSColor2;
		if ($this->getDsObservationProperty ( $LOid, 'component2' ) == 3)
			$details1Text .= "-" . LangDetailDSColor3;
		if ($this->getDsObservationProperty ( $LOid, 'component2' ) == 4)
			$details1Text .= "-" . LangDetailDSColor4;
		if ($this->getDsObservationProperty ( $LOid, 'component2' ) == 5)
			$details1Text .= "-" . LangDetailDSColor5;
		if ($this->getDsObservationProperty ( $LOid, 'component2' ) == 6)
			$details1Text .= "-" . LangDetailDSColor6;
		$details1Text = substr ( $details1Text, 2 );
		$details2Text = "";
		if ($this->getDsObservationProperty ( $LOid, 'unusualShape' ) > 0)
			$details2Text .= ", " . LangViewObservationField41;
		if ($this->getDsObservationProperty ( $LOid, 'partlyUnresolved' ) > 0)
			$details2Text .= ", " . LangViewObservationField42;
		if ($this->getDsObservationProperty ( $LOid, 'colorContrasts' ) > 0)
			$details2Text .= ", " . LangViewObservationField43;
		if ($this->getDsObservationProperty ( $LOid, 'equalBrightness' ) > 0)
			$details2Text .= ", " . LangDetailDS1;
		if ($this->getDsObservationProperty ( $LOid, 'niceField' ) > 0)
			$details2Text .= ", " . LangDetailDS2;
		$details2Text = substr ( $details2Text, 2 );
		$charTypeText = "-";
		$object = $this->getDsObservationProperty ( $LOid, 'objectname' );
		$object_ss = stripslashes ( $object );
		if (in_array ( $objObject->getDsoProperty ( $object, 'type' ), array (
				"ASTER",
				"CLANB",
				"DS",
				"OPNCL",
				"AA1STAR",
				"AA2STAR",
				"AA3STAR",
				"AA4STAR",
				"AA8STAR",
				"GLOCL"
		) ))
			$charTypeText = (($clusterType = $this->getDsObservationProperty ( $LOid, 'clusterType' )) ? $clusterType . ': ' . $GLOBALS ['ClusterType' . $clusterType] : "-");

		echo "<table class=\"table\">";
		echo "<tr>";
		echo "<td>" . LangViewObservationField2 . "</td>";
		$observer = $this->getDsObservationProperty ( $LOid, 'observerid' );
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . urlencode ( $observer ) . "&amp;back=index.php?indexAction=detail_observation\">" . $objObserver->getObserverProperty ( $this->getDsObservationProperty ( $LOid, 'observerid' ), 'firstname' ) . "&nbsp;" . $objObserver->getObserverProperty ( $this->getDsObservationProperty ( $LOid, 'observerid' ), 'name' ) . "</a>";

		// Show the picture of the sender
		$dir = opendir ( $instDir . 'common/observer_pics' );
		while ( FALSE !== ($file = readdir ( $dir )) ) {
			if (("." == $file) or (".." == $file))
				continue; // skip current directory and directory above
			if (fnmatch ( $observer . ".gif", $file ) || fnmatch ( $observer . ".jpg", $file ) || fnmatch ( $observer . ".png", $file )) {
				echo "<img height=\"72\" src=\"" . $baseURL . "/common/observer_pics/" . $file . "\" class=\"img-rounded pull-right\">";
			}
		}

		echo "</td>";

		echo "<td>" . LangViewObservationField3 . "</td>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $this->getDsObservationProperty ( $LOid, 'instrumentid' ) ) . "\">" . $inst . "</a>";

		// Show the moon during the observation
		$year = $date [0];
		$month = $date [1];
		$day = $date [2];
		$date = $date [0] . "-" . $date [1] . "-" . $date [2];

		$realTime = $this->getDsObservationProperty ( $LOid, 'time' );
		if ($realTime < 0) {
			$time = "23:59:59";
		} else {
			$time = $realTime;
		}
		$tzone = "GMT";
		$moondata = phase ( strtotime ( $date . ' 23:59:59 ' . $tzone ) );
		$MoonIllum = $moondata [1];
		$MoonAge = $moondata [2];
		// Convert $MoonIllum to percent and round to whole percent.
		$MoonIllum = round ( $MoonIllum, 2 );
		$MoonIllum *= 100;
		$file = "m" . round ( ($MoonAge / SYNMONTH) * 40 ) . ".gif";

		// Moon is above the horizon
		if ($realTime < 0) {
			$moon = "<img src=\"" . $baseURL . "/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" />";
		} else {
			// Get location
			$longitude = $objLocation->getLocationPropertyFromId ( $this->getDsObservationProperty ( $LOid, 'locationid' ), 'longitude' );
			$latitude = $objLocation->getLocationPropertyFromId ( $this->getDsObservationProperty ( $LOid, 'locationid' ), 'latitude' );

			// Calculate altitude of the moon for this date, time and location

			// Get the julian day of the observation...
			$jd = gregoriantojd ( $month, $day, $year );

			$timezone = $objLocation->getLocationPropertyFromId ( $this->getDsObservationProperty ( $LOid, 'locationid' ), 'timezone' );
			$dateTimeZone = new DateTimeZone ( $timezone );

			$datestr = sprintf ( "%02d", $month ) . "/" . sprintf ( "%02d", $day ) . "/" . $year;
			$dateTime = new DateTime ( $datestr, $dateTimeZone );
			// Geeft tijdsverschil terug in seconden
			$timedifference = $dateTimeZone->getOffset ( $dateTime );
			$timedifference = $timedifference / 3600.0;

			if (strncmp ( $timezone, "Etc/GMT", 7 ) == 0)
				$timedifference = - $timedifference;
				// Calculate the rise and set time of the moon
			$moonCalc = $objAstroCalc->calculateMoonRiseTransitSettingTime ( $jd, $longitude, $latitude, $timedifference );

			// Now we know when the moon rises and sets. We have to convert the time and compare with the time of the observation.
			// $moonCalc[0] = rise
			// $moonCalc[2] = set
			$moonriseArray = sscanf ( $moonCalc [0], "%d:%d" );
			$moonsetArray = sscanf ( $moonCalc [2], "%d:%d" );
			$moonRise = $moonriseArray [0] * 100.0 + $moonriseArray [1];
			$moonSet = $moonsetArray [0] * 100.0 + $moonsetArray [1];

			$moonAboveHorizon = true;
			if ($moonRise > $moonSet) {
				if ($time <= $moonRise && $time >= $moonSet) {
					$moonAboveHorizon = false;
				}
			} else {
				if ($time <= $moonRise || $time >= $moonSet) {
					$moonAboveHorizon = false;
				}
			}

			if ($moonAboveHorizon) {
				// Moon is above the horizon
				$moon = "<img src=\"" . $baseURL . "/lib/moonpics/" . $file . "\" class=\"moonpic\" title=\"" . $MoonIllum . "%\" alt=\"" . $MoonIllum . "%\" />";
			} else {
				// Moon is under the horizon
				$moon = "<img src=\"" . $baseURL . "/lib/moonpics/below.png\" class=\"moonpic\" title=\"" . $MoonIllum . "% - " . LangUnderHorizon . "\" alt=\"" . $MoonIllum . "%\" />";
			}
		}
		echo "<td></td><td>" . $moon . "</td>";

		echo "</tr>";
		echo "<tr>";
		echo "<td>" . LangViewObservationField5 . $dateTimeLabelText . "</td>";
		echo "<td>" . $dateTimeText . "</td>";
		echo "<td>" . LangViewObservationField4 . "</td>";
		echo "<td>" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_location&amp;location=" . urlencode ( $this->getDsObservationProperty ( $LOid, 'locationid' ) ) . "\">" . $objLocation->getLocationPropertyFromId ( $this->getDsObservationProperty ( $LOid, 'locationid' ), 'name' ) . "</a></td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>" . LangViewObservationField7 . "/" . LangViewObservationField34 . "</td>";
		echo "<td>" . (($limmag = $this->getDsObservationProperty ( $LOid, 'limmag' )) ? sprintf ( "%1.1f", $limmag ) : "-") . "/" . ((($sqm = $this->getDsObservationProperty ( $LOid, 'SQM' )) != - 1) ? sprintf ( "%2.1f", $sqm ) : '-') . "</td>";
		echo "<td>" . LangViewObservationField6 . "</td>";
		echo "<td>" . (($seeing) ? $GLOBALS ['Seeing' . $seeing] : "-") . "</td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td>" . LangViewObservationField30 . "</td>";
		echo "<td>" . (((($eyepiece = $this->getDsObservationProperty ( $LOid, 'eyepieceid' )) == "") || ($eyepiece == 0)) ? "-" : "<a  href=\"" . $baseURL . "index.php?indexAction=detail_eyepiece&amp;eyepiece=" . urlencode ( $eyepiece ) . "\">" . stripslashes ( $objEyepiece->getEyepiecePropertyFromId ( $eyepiece, 'name' ) ) . "</a>") . (((($mag = $this->getDsObservationProperty ( $LOid, 'magnification' )) == "")) ? "" : " (" . $mag . "x)") . "</td>";
		echo "<td>" . LangViewObservationField31 . "</td>";
		echo "<td>" . (((($filter = $this->getDsObservationProperty ( $LOid, 'filterid' )) == "") || ($filter == 0)) ? "-" : "<a  href=\"" . $baseURL . "index.php?indexAction=detail_filter&amp;filter=" . urlencode ( $filter ) . "\">" . $objFilter->getFilterPropertyFromId ( $filter, 'name' ) . "</a>") . "</td>";
		echo "<td>" . LangViewObservationField32 . "</td>";
		echo "<td>" . (((($lens = $this->getDsObservationProperty ( $LOid, 'lensid' )) == "") || ($lens == 0)) ? "-" : "<a  href=\"" . $baseURL . "index.php?indexAction=detail_lens&amp;lens=" . urlencode ( $lens ) . "\">" . $objLens->getLensPropertyFromId ( $lens, 'name' ) . "</a>") . "</td>";
		echo "</tr>";

		if (in_array ( $objObject->getDsoProperty ( $object, 'type' ), array (
				"DS",
				"AA2STAR"
		) )) {
			echo "<tr>";
			echo "<td>" . LangViewObservationField22 . "</td>";
			echo "<td>" . (($visibility = $this->getDsObservationProperty ( $LOid, 'visibility' )) ? $GLOBALS ['VisibilityDS' . $visibility] : "-") . "</td>";
			echo "<td>" . LangViewObservationField33 . "</td>";
			echo "<td>" . $diameterText . "</td>";
			echo "<td>" . LangViewObservationField40 . "</td>";
			echo "<td>" . $charTypeText . "</td>";
			echo "</tr>";
			echo "</table>";
			echo $details1Text . " " . $details2Text;
		} else if (in_array ( $objObject->getDsoProperty ( $object, 'type' ), array (
				"OPNCL"
		) ) && $this->getDsObservationProperty ( $LOid, 'resolved' ) > 0) {
			echo "<tr>";
			echo "<td>" . LangViewObservationField22 . "</td>";
			echo "<td>" . (($visibility = $this->getDsObservationProperty ( $LOid, 'visibility' )) ? $GLOBALS ['VisibilityOC' . $visibility] : "-") . "</td>";
			echo "<td>" . LangViewObservationField33 . "</td>";
			echo "<td>" . $diameterText . "</td>";
			echo "<td>" . LangViewObservationField40 . "</td>";
			echo "<td>" . $charTypeText . "</td>";
			echo "</tr>";
			echo "</table>";
			echo $details1Text . " " . $details2Text;
		} else {
			echo "<tr>";
			echo "<td>" . LangViewObservationField22 . "</td>";
			echo "<td>" . (($visibility = $this->getDsObservationProperty ( $LOid, 'visibility' )) ? $GLOBALS ['Visibility' . $visibility] : "-") . "</td>";
			echo "<td>" . LangViewObservationField33 . "</td>";
			echo "<td>" . $diameterText . "</td>";
			echo "<td>" . LangViewObservationField40 . "</td>";
			echo "<td>" . $charTypeText . "</td>";
			echo "</tr>";
			echo "</table>";
		}

		$toClose = false;
		if ($loggedUser != "") {
			$usedLang = $objObserver->getObserverProperty ( $loggedUser, "language" );
			if ($usedLang != $this->getDsObservationProperty ( $LOid, 'language' )) {
				$toClose = true;
				// Make the google translate control node
				echo "<div class=\"goog-trans-section\">";
				echo "<div class=\"goog-trans-control\">";
				echo "</div>";
			}
		}
		echo $objPresentations->searchAndLinkCatalogsInText ( $this->getDsObservationProperty ( $LOid, 'description' ) );
		if ($toClose) {
			echo "</div>";
		}

		$title = $object . " " . LangMessageBy . $this->getDsObservationProperty ( $LOid, 'observerid' );

		if ($this->getDsObservationProperty ( $LOid, 'hasDrawing' ))
			echo "<p>" . "<a  href=\"" . $baseURL . "deepsky/drawings/" . $LOid . ".jpg" . "\" data-lightbox=\"image-1\" data-title=\"\"> <img class=\"account\" src=\"" . $baseURL . "deepsky/drawings/" . $LOid . "_resized.jpg\" alt=\"" . $title . "\"></img></a></p>";
		if ($copyright = $objObserver->getObserverProperty ( $this->getDsObservationProperty ( $LOid, 'observerid' ), 'copyright' ))
			echo "<p class=\"copyright\">" . $copyright . "</p>";
		echo "<br /><br />";
		$bottomline = "";
		if ($myList) {
			$bottomline .= "<a class=\"btn btn-success\" href=\"" . $link . $linkamp . "addObservationToList=" . urlencode ( $LOid ) . "\"><span class=\"glyphicon glyphicon-plus\"></span> " . LangViewObservationField44 . $listname_ss . "</a>";
			if ($objDatabase->selectSingleValue ( "SELECT Count(observerobjectlist.objectname) As ObjCnt FROM observerobjectlist WHERE observerid = \"" . $loggedUser . "\" AND objectname=\"" . $object . "\" AND listname=\"" . $listname . "\"", 'ObjCnt', 0 ) > 0)
				$bottomline .= "&nbsp;<a class=\"btn btn-danger\" href=\"" . $link . $linkamp . "removeObjectFromList=" . urlencode ( $object ) . "&amp;showname=" . urlencode ( $object ) . "\"><span class=\"glyphicon glyphicon-minus\"></span> " . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a><br /><br />";
			else
				$bottomline .= "&nbsp;<a class=\"btn btn-success\" href=\"" . $link . $linkamp . "addObjectToList=" . urlencode ( $object ) . "&amp;showname=" . urlencode ( $object ) . "\"><span class=\"glyphicon glyphicon-plus\"></span> " . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a><br /><br />";
			echo $bottomline;
		}
		if ($objUtil->checkAdminOrUserID ( $this->getDsObservationProperty ( $LOid, 'observerid' ) )) {
			$bottomline = "<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;observation=" . $LOid . "\">" . LangChangeObservationTitle . "</a>";
			$bottomline .= "&nbsp;<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=validate_delete_observation&amp;observationid=" . $LOid . "\">" . LangDeleteObservation . "</a>";
			echo $bottomline . "<br /><br />";
		}
	}
	public function validateDeleteDSObservation() // removes the observation with id = $id
{
		global $objDatabase, $objAccomplishments, $objUtil;
		if (! $_GET ['observationid'])
			throw new Exception ( "No observation to delete." );
		$id = $objUtil->checkGetKey ( 'observationid' );
		$user = $this->getDsObservationProperty ( $id, 'observerid' );
		if ($id && ($objUtil->checkAdminOrUserID ( $user ))) {
			$objDatabase->execSQL ( "DELETE FROM observations WHERE id=\"" . $id . "\"" );
			$objDatabase->execSQL ( "DELETE FROM sessionObservations WHERE observationid=\"" . $id . "\"" );
			$_SESSION ['Qobs'] = array ();
			$_SESSION ['QobsParams'] = array ();
			// Recalculate the accomplishments
			$objAccomplishments->recalculateDeepsky ( $user );
			return LangObservationDeleted;
		}
	}
	public function validateObservation() {
		global $loggedUser, $objUtil, $objObservation, $objObserver, $maxFileSize, $entryMessage, $objPresentations, $inIndex, $instDir, $objSession, $objAccomplishments;
		if (! ($loggedUser))
			throw new Exception ( LangException002b );
		elseif ($objUtil->checkSessionKey ( 'addObs', 0 ) != $objUtil->checkPostKey ( 'timestamp', - 1 )) {
			$_GET ['indexAction'] = "default_action";
			$_GET ['dalm'] = 'D';
			// $_GET['observation']=$current_observation;
		} elseif ((! $_POST ['day']) || (! $_POST ['month']) || (! $_POST ['year']) || ($_POST ['site'] == "1") || (! $_POST ['instrument']) || (! $_POST ['description'])) {
			if ($objUtil->checkPostKey ( 'limit' ))
				if (preg_match ( '/([0-9]{1})[.,]{0,1}([0-9]{0,1})/', $_POST ['limit'], $matches )) // limiting magnitude like X.X or X,X with X a number between 0 and 9
					$_POST ['limit'] = $matches [1] . "." . (($matches [2]) ? $matches [2] : "0");
				else
					$_POST ['limit'] = 0; // clear current magnitude limit
			else if ($objUtil->checkPostKey ( 'sqm' ))
				if (preg_match ( '/([0-9]{1})([0-9]{1})[.,]{0,1}([0-9]{0,1})/', $_POST ['sqm'], $matches )) // sqm value
					$_POST ['sqm'] = $matches [1] . $matches [2] . "." . (($matches [3]) ? $matches [3] : "0");
				else
					$_POST ['sqm'] = - 1; // clear current magnitude limit
			else {
				$_POST ['limit'] = 0;
				$_POST ['sqm'] = - 1;
			}
			$entryMessage .= LangValidateObservationMessage1;
			$_GET ['indexAction'] = 'add_observation';
		} else // all fields filled in
{
			$time = - 9999;
			if (strlen ( $_POST ['hours'] )) {
				if (isset ( $_POST ['minutes'] ))
					$time = ($_POST ['hours'] * 100) + $_POST ['minutes'];
				else
					$time = ($_POST ['hours'] * 100);
			}
			if ($_FILES ['drawing'] ['size'] > $maxFileSize) // file size of drawing too big
{
				$entryMessage .= LangValidateObservationMessage6;
				$_GET ['indexAction'] = 'add_observation';
			} elseif ((! is_numeric ( $_POST ['month'] )) || (! is_numeric ( $_POST ['day'] )) || (! is_numeric ( $_POST ['year'] )) || (! checkdate ( $_POST ['month'], $_POST ['day'], $_POST ['year'] )) || ((sprintf ( "%04d", $_POST ['year'] ) . sprintf ( "%02d", $_POST ['month'] ) . sprintf ( "%02d", $_POST ['day'] )) < '19500000') || ((sprintf ( "%04d", $_POST ['year'] ) . sprintf ( "%02d", $_POST ['month'] ) . sprintf ( "%02d", $_POST ['day'] )) > date ( 'Ymd', strtotime ( '+1 day' ) ))) {
				$entryMessage .= LangValidateObservationMessage2;
				$_GET ['indexAction'] = 'add_observation';
			} elseif (($date = $_POST ['year'] . sprintf ( "%02d", $_POST ['month'] ) . sprintf ( "%02d", $_POST ['day'] )) > date ( 'Ymd' )) {
				$entryMessage .= LangValidateObservationMessage3;
				$_GET ['indexAction'] = 'add_observation';
			} elseif (($time > - 9999) && ((! is_numeric ( $_POST ['hours'] )) || (! is_numeric ( $_POST ['minutes'] )) || ($_POST ['hours'] < 0) || ($_POST ['hours'] > 23) || ($_POST ['minutes'] < 0) || ($_POST ['minutes'] > 59))) {
				$entryMessage .= LangValidateObservationMessage4;
				$_GET ['indexAction'] = 'add_observation';
			} else {
				if ($objUtil->checkPostKey ( 'limit' ))
					if (preg_match ( '/([0-9]{1})[.,]{0,1}([0-9]{0,1})/', $_POST ['limit'], $matches )) // limiting magnitude like X.X or X,X with X a number between 0 and 9
						$_POST ['limit'] = $matches [1] . "." . (($matches [2]) ? $matches [2] : "0");
					else // clear current magnitude limit
						$_POST ['limit'] = "";
				if ($_POST ['observationid']) {
					$current_observation = $_POST ['observationid'];
					if (! ($objUtil->checkAdminOrUserID ( $objObservation->getDsObservationProperty ( $current_observation, 'observerid' ) ))) {
						$indexAction = '';
						return;
					} else {
						$objObservation->setDsObservationProperty ( $current_observation, 'instrumentid', $_POST ['instrument'] );
						$objObservation->setDsObservationProperty ( $current_observation, 'locationid', $_POST ['site'] );
						$objObservation->setDsObservationProperty ( $current_observation, 'date', $date );
						$objObservation->setDsObservationProperty ( $current_observation, 'time', $time );
						$objObservation->setDsObservationProperty ( $current_observation, 'description', nl2br ( $_POST ['description'] ) );
						$objObservation->setDsObservationProperty ( $current_observation, 'seeing', $_POST ['seeing'] );
						$objObservation->setDsObservationProperty ( $current_observation, 'limmag', $objUtil->checkPostKey ( 'limit', 0 ) );
						$objObservation->setDsObservationProperty ( $current_observation, 'visibility', $objUtil->checkPostKey ( 'visibility' ) );
						$objObservation->setDsObservationProperty ( $current_observation, 'language', $_POST ['description_language'] );
					}
				} else
					$current_observation = $objObservation->addDSObservation ( $_POST ['object'], $loggedUser, $_POST ['instrument'], $_POST ['site'], $date, $time, nl2br ( $_POST ['description'] ), $_POST ['seeing'], $_POST ['limit'], $objUtil->checkPostKey ( 'visibility' ), $_POST ['description_language'] );
				$_SESSION ['addObs'] = '';
				$_SESSION ['Qobs'] = array ();
				$_SESSION ['QobsParams'] = array ();
				if ($objUtil->checkPostKey ( 'sqm' ))
					if (preg_match ( '/([0-9]{1})([0-9]{0,1})[.,]{0,1}([0-9]{0,1})/', $_POST ['sqm'], $matches )) // sqm value
						$_POST ['sqm'] = $matches [1] . $matches [2] . "." . (($matches [3]) ? $matches [3] : "0");
					else
						$_POST ['sqm'] = ""; // clear current magnitude limit
				if ($objUtil->checkPostKey ( 'largeDiam' ))
					if (preg_match ( '/([0-9]+)[.,]{0,1}([0-9]{0,1})/', $_POST ['largeDiam'], $matches )) // large diameter
						$_POST ['largeDiam'] = (($matches [1]) ? $matches [1] : "0") . "." . (($matches [2]) ? $matches [2] : "0");
					else // clear current large diameter
						$_POST ['largeDiam'] = "";
				if ($objUtil->checkPostKey ( 'smallDiam' ))
					if (preg ( '/([0-9]+)[.,]{0,1}([0-9]{0,1})/', $_POST ['smallDiam'], $matches )) // large diameter
						$_POST ['smallDiam'] = (($matches [1]) ? $matches [1] : "0") . "." . (($matches [2]) ? $matches [2] : "0");
					else // clear current large diameter
						$_POST ['smallDiam'] = "";

				if ($_POST ['smallDiam'] > $_POST ['largeDiam']) {
					$tmp = $_POST ['largeDiam'];
					$_POST ['largeDiam'] = $_POST ['smallDiam'];
					$_POST ['smallDiam'] = $tmp;
				}
				if ($objUtil->checkPostKey ( 'size_units' ) == "min") {
					$_POST ['smallDiam'] = $_POST ['smallDiam'] * 60.0;
					$_POST ['largeDiam'] = $_POST ['largeDiam'] * 60.0;
				}
				if ($_POST ['sqm'])
					$objObservation->setDsObservationProperty ( $current_observation, 'SQM', preg_replace ( "/,/", ".", $objUtil->checkPostKey ( 'sqm', - 1 ) ) );
				if ($_POST ['smallDiam'])
					$objObservation->setDsObservationProperty ( $current_observation, 'smallDiameter', $_POST ['smallDiam'] );
				if ($_POST ['largeDiam'])
					$objObservation->setDsObservationProperty ( $current_observation, 'largeDiameter', $_POST ['largeDiam'] );
				if (array_key_exists ( 'stellarextended', $_POST ) && ($_POST ['stellarextended'] == "stellar"))
					$objObservation->setDsObservationProperty ( $current_observation, 'stellar', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'stellar', - 1 );
				if (array_key_exists ( 'stellarextended', $_POST ) && ($_POST ['stellarextended'] == "extended"))
					$objObservation->setDsObservationProperty ( $current_observation, 'extended', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'extended', - 1 );
				if (array_key_exists ( 'resolved', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'resolved', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'resolved', - 1 );
				if (array_key_exists ( 'mottled', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'mottled', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'mottled', - 1 );
				if (array_key_exists ( 'unusualShape', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'unusualShape', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'unusualShape', - 1 );
				if (array_key_exists ( 'partlyUnresolved', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'partlyUnresolved', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'partlyUnresolved', - 1 );
				if (array_key_exists ( 'colorContrasts', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'colorContrasts', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'colorContrasts', - 1 );
				if (array_key_exists ( 'equalBrightness', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'equalBrightness', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'equalBrightness', - 1 );
				if (array_key_exists ( 'niceField', $_POST ))
					$objObservation->setDsObservationProperty ( $current_observation, 'niceField', 1 );
				else
					$objObservation->setDsObservationProperty ( $current_observation, 'niceField', - 1 );
				if ($_POST ['filter'])
					$objObservation->setDsObservationProperty ( $current_observation, 'filterid', $_POST ['filter'] );
				if ($_POST ['lens'])
					$objObservation->setDsObservationProperty ( $current_observation, 'lensid', $_POST ['lens'] );
				if ($_POST ['eyepiece'])
					$objObservation->setDsObservationProperty ( $current_observation, 'eyepieceid', $_POST ['eyepiece'] );
				if ($_POST ['magnification'])
					$objObservation->setDsObservationProperty ( $current_observation, 'magnification', $_POST ['magnification'] );
				if (! ($objObserver->getObserverProperty ( $loggedUser, 'UT' )))
					$objObservation->setLocalDateAndTime ( $current_observation, $date, $time );
				$objObservation->setDsObservationProperty ( $current_observation, 'clusterType', $objUtil->checkPostKey ( 'clusterType' ) );
				$objObservation->setDsObservationProperty ( $current_observation, 'component1', $objUtil->checkPostKey ( 'component1', - 1 ) );
				$objObservation->setDsObservationProperty ( $current_observation, 'component2', $objUtil->checkPostKey ( 'component2', - 1 ) );
				if ($_FILES ['drawing'] ['tmp_name'] != "") // drawing to upload
{
					$upload_dir = $instDir . 'deepsky/drawings';
					$dir = opendir ( $upload_dir );
					$original_image = $_FILES ['drawing'] ['tmp_name'];
					$destination_image = $upload_dir . "/" . $current_observation . "_resized.jpg";
					require_once $instDir . "common/control/resize.php"; // resize code
					$new_image = image_createThumb ( $original_image, $destination_image, 490, 490, 100 );
					move_uploaded_file ( $_FILES ['drawing'] ['tmp_name'], $upload_dir . "/" . $current_observation . ".jpg" );
					$objObservation->setDsObservationProperty ( $current_observation, 'hasDrawing', 1 );
				}

				// Add the observation to all the sessions
				$objSession->addObservationToSessions ( $current_observation );

				// Recalculate the accomplishments
				$objAccomplishments->recalculateDeepsky ( $loggedUser );

				$_SESSION ['newObsYear'] = $_POST ['year']; // save current details for faster submission of multiple observations
				$_SESSION ['newObsMonth'] = $_POST ['month'];
				$_SESSION ['newObsDay'] = $_POST ['day'];
				$_SESSION ['newObsInstrument'] = $_POST ['instrument'];
				$_SESSION ['newObsLocation'] = $_POST ['site'];
				$_SESSION ['newObsLimit'] = $_POST ['limit'];
				$_SESSION ['newObsSqm'] = $_POST ['sqm'];
				$_SESSION ['newObsSQM'] = $_POST ['sqm'];
				$_SESSION ['newObsSeeing'] = $_POST ['seeing'];
				$_SESSION ['newObsLanguage'] = $_POST ['description_language'];
				$_SESSION ['newObsSavedata'] = "yes";
				$_GET ['indexAction'] = "detail_observation";
				$_GET ['dalm'] = 'D';
				$_GET ['observation'] = $current_observation;
			}
		}
	}
	public function getLastObservationsWithDrawing($numberOfObservations = 4) {
		global $objDatabase;
		return $objDatabase->selectRecordsetArray ( "SELECT id, objectname, observerid, date FROM observations WHERE hasDrawing=\"1\" ORDER BY id DESC LIMIT 4", 'id' );
	}
}
?>
