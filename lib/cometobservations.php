<?php

// The observations class collects all functions needed to enter, retrieve and
// adapt observation data from the database.
class CometObservations
{
    // addObservation adds a new observation to the database. The objectid,
    // observerid, date and time should be given as parameters.
    public function addObservation($objectid, $observerid, $date, $time)
    {
        global $objAccomplishments, $objDatabase;
        if (! $_SESSION ['lang']) {
            $_SESSION ['lang'] = "English";
        }

        // Add the observation
        $sql = "INSERT INTO cometobservations (objectid, observerid, date, time, description, timestamp) VALUES (\"$objectid\", \"$observerid\", \"$date\", \"$time\", \"\", ".time().")";
        $objDatabase->execSQL($sql);

        $query = "SELECT id FROM cometobservations ORDER BY id DESC LIMIT 1";
        $run = $objDatabase->selectRecordset($query);
        $get = $run->fetch(PDO::FETCH_OBJ);
        $id = $get->id;

        // Recalculate the accomplishments
        $objAccomplishments->recalculateComets($observerid);

        return $id;
    }

    // deleteObservation($id) deletes the observation with the given id.
    public function deleteObservation($id)
    {
        global $objDatabase;
        $sql = "DELETE FROM cometobservations WHERE id=\"$id\"";
        $objDatabase->execSQL($sql);
    }

    // getObjectId returns the objectid of the given observation
    public function getObjectId($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        if ($get) {
            return $get->objectid;
        } else {
            return 0;
        }
    }

    // setObjectId sets a new object for the given observation
    public function setObjectId($id, $object)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET objectid = \"$object\" WHERE id = \"$id\"";
        $objDatabase->execSQL($sql);
    }

    // setHasDrawing sets a drawing for the given observation
    public function setHasDrawing($id)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET hasDrawing = \"1\" WHERE id = \"$id\"";
        $objDatabase->execSQL($sql);
    }

    // getObserverId returns the observerid of the given observation
    public function getObserverId($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $observerid = $get->observerid;

        return $observerid;
    }

    // setObserverId sets a new observer for the given observation
    public function setObserverId($id, $observer)
    {
        global $objDatabase;

        print $observer;
        exit();

        $sql = "UPDATE cometobservations SET observerid = \"$observer\" WHERE id = \"$id\" ";
        $objDatabase->execSQL($sql);
    }

    // getInstrumentId returns the instrumentid of the given observation
    public function getInstrumentId($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $instrumentid = $get->instrumentid;

        return $instrumentid;
    }

    // setInstrumentId sets a new instrument for the given observation
    public function setInstrumentId($id, $instrument)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET instrumentid = \"$instrument\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getLocationId returns the locationid of the given observation
    public function getLocationId($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        if ($get) {
            return $get->locationid;
        } else {
            return 0;
        }
    }

    // setLocationId sets a new location for the given observation
    public function setLocationId($id, $location)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET locationid = \"$location\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getDate returns the date of the given observation
    public function getDate($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $date = $get->date;

        return $date;
    }

    // setDate sets a new date for the given observation
    public function setDate($id, $date)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET date = \"$date\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }
    public function getCometDrawingsCountFromObserver($id)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue("SELECT COUNT(*) as Cnt FROM cometobservations WHERE cometobservations.observerid = \"$id\" AND hasDrawing=1", "Cnt", 0);
    }

    // setLocalDateAndTime sets the date and time for the given observation
    // when the time is given in local time
    public function setLocalDateAndTime($id, $date, $time)
    {
        global $objDatabase;
        $locations = new Locations();

        if ($time >= 0) {

            $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
            $run = $objDatabase->selectRecordset($sql);

            $get = $run->fetch(PDO::FETCH_OBJ);

            $location = $get->locationid;

            if ($location) {
                $timezone = $locations->getLocationPropertyFromId($location, 'timezone');

                $datearray = sscanf($date, "%4d%2d%2d");

                $dateTimeZone = new DateTimeZone($timezone);
                $date = sprintf("%02d", $datearray [1]) . "/" . sprintf("%02d", $datearray [2]) . "/" . $datearray [0];

                $dateTime = new DateTime($date, $dateTimeZone);
                // Returns the timedifference in seconds
                $timedifference = $dateTimeZone->getOffset($dateTime);
                $timedifference = $timedifference / 3600.0;

                $timestr = sscanf(sprintf("%04d", $time), "%2d%2d");

                $jd = cal_to_jd(CAL_GREGORIAN, $datearray [1], $datearray [2], $datearray [0]);

                $hours = $timestr [0] - ( int ) $timedifference;

                $timedifferenceminutes = ($timedifference - ( int ) $timedifference) * 60;

                $minutes = $timestr [1] - $timedifferenceminutes;

                if ($minutes < 0) {
                    $hours = $hours - 1;
                    $minutes = $minutes + 60;
                } elseif ($minutes > 60) {
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

                $dte = JDToGregorian($jd);
                sscanf($dte, "%2d/%2d/%4d", $month, $day, $year);
                $date = $year . sprintf("%02d", $month) . sprintf("%02d", $day);
            }
        }

        $sql = "UPDATE cometobservations SET date = \"$date\" WHERE id = \"$id\"";
        $run = $objDatabase->execSQL($sql);

        $sql = "UPDATE cometobservations SET time = \"$time\" WHERE id = \"$id\"";
        $run = $objDatabase->execSQL($sql);
    }

    // getLocalDate returns the date of the given observation in local time
    public function getLocalDate($id)
    {
        global $objDatabase;
        $locations = new Locations();

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        if ($get) {
            $date = $get->date;

            $time = $get->time;
            $loc = $get->locationid;

            if ($loc) {
                if ($time >= 0) {
                    $date = sscanf($date, "%4d%2d%2d");

                    $timezone = $locations->getLocationPropertyFromId($loc, 'timezone');

                    $dateTimeZone = new DateTimeZone($timezone);

                    $datestr = sprintf("%02d", $date [1]) . "/" . sprintf("%02d", $date [2]) . "/" . $date [0];
                    $dateTime = new DateTime($datestr, $dateTimeZone);
                    // Geeft tijdsverschil terug in seconden
                    $timedifference = $dateTimeZone->getOffset($dateTime);
                    $timedifference = $timedifference / 3600.0;

                    $jd = cal_to_jd(CAL_GREGORIAN, $date [1], $date [2], $date [0]);

                    $time = sscanf(sprintf("%04d", $time), "%2d%2d");

                    $hours = $time [0] + ( int ) $timedifference;
                    $minutes = $time [1];

                    // We are converting from UT to local time -> we should add the time difference!
                    $timedifferenceminutes = ($timedifference - ( int ) $timedifference) * 60;

                    $minutes = $minutes + $timedifferenceminutes;

                    if ($minutes < 0) {
                        $hours = $hours - 1;
                        $minutes = $minutes + 60;
                    } elseif ($minutes > 60) {
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

                    $dte = JDToGregorian($jd);

                    sscanf($dte, "%2d/%2d/%4d", $month, $day, $year);

                    $date = sprintf("%d%02d%02d", $year, $month, $day);
                }
            }
        }

        return $date;
    }

    // getTime returns the Time of the given observation
    public function getTime($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        if ($get) {
            return $get->time;
        } else {
            return 0;
        }
    }

    // getLocalTime returns the time of the given observation in local time
    public function getLocalTime($id)
    {
        global $objDatabase;

        $locations = new Locations();

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        if ($get) {
            $date = $get->date;
            $time = $get->time;
            $loc = $get->locationid;

            if ($loc) {
                $date = sscanf($date, "%4d%2d%2d");

                $timezone = $locations->getLocationPropertyFromId($loc, 'timezone');

                $dateTimeZone = new DateTimeZone($timezone);

                $datestr = sprintf("%02d", $date [1]) . "/" . sprintf("%02d", $date [2]) . "/" . $date [0];

                $dateTime = new DateTime($datestr, $dateTimeZone);
                // Geeft tijdsverschil terug in seconden
                $timedifference = $dateTimeZone->getOffset($dateTime);
                $timedifference = $timedifference / 3600.0;

                if ($time < 0) {
                    return $time;
                }
                $time = sscanf(sprintf("%04d", $time), "%2d%2d");

                $hours = $time [0] + ( int ) $timedifference;
                $minutes = $time [1];

                // We are converting from UT to local time -> we should add the time difference!
                $timedifferenceminutes = ($timedifference - ( int ) $timedifference) * 60;

                $minutes = $minutes + $timedifferenceminutes;

                if ($minutes < 0) {
                    $hours = $hours - 1;
                    $minutes = $minutes + 60;
                } elseif ($minutes > 60) {
                    $hours = $hours + 1;
                    $minutes = $minutes - 60;
                }

                if ($hours < 0) {
                    $hours = $hours + 24;
                }
                if ($hours >= 24) {
                    $hours = $hours - 24;
                }

                $time = $hours * 100 + $minutes;
            }
        }

        return $time;
    }

    // setTime sets a new time for the given observation
    public function setTime($id, $time)
    {
        global $objDatabase;
        $sql = "UPDATE cometobservations SET time = \"$time\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getDescription returns the Description of the given observation
    public function getDescription($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $description = $get->description;

        return $description;
    }

    // setDescription sets a new Description for the given observation
    public function setDescription($id, $description)
    {
        global $objDatabase;
        $sql = "UPDATE cometobservations SET description = \"" . $description . "\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getMethode returns the Methode of the given observation
    public function getMethode($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $methode = $get->methode;

        return $methode;
    }

    // setMethode sets a new Methode for the given observation
    public function setMethode($id, $methode)
    {
        global $objDatabase;
        $sql = "UPDATE cometobservations SET methode = \"$methode\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getMagnitude returns the Magnitude of the given observation
    public function getMagnitude($id)
    {
        global $objDatabase;
        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $magnitude = $get->mag;

        return $magnitude;
    }

    // setMagnitude sets a new Magnitude for the given observation
    public function setMagnitude($id, $magnitude)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET mag = \"$magnitude\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // setMagnitudeUncertain set the uncertain flag for the given magnitude
    public function setMagnitudeUncertain($id, $magnitude)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET maguncertain = \"$magnitude\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getMagnitudeUncertain returns 1 if the magnitude is uncertain
    public function getMagnitudeUncertain($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $magnitude = $get->maguncertain;

        return $magnitude;
    }

    // setMagnitudeWeakerThan set the weaker than flag for the given magnitude
    public function setMagnitudeWeakerThan($id, $magnitude)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET lessmagnitude = \"$magnitude\" WHERE id =
\"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getMagnitudeWeakerThan returns 1 if the magnitude is weaker than the given magnitude
    public function getMagnitudeWeakerThan($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $magnitude = $get->lessmagnitude;

        return $magnitude;
    }

    // getChart returns the Chart of the given observation
    public function getChart($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $chart = $get->chart;

        return $chart;
    }

    // setChart sets a new Chart for the given observation
    public function setChart($id, $chart)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET chart = \"$chart\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getMagnification returns the Magnification of the given observation
    public function getMagnification($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $magnification = $get->magnification;

        return $magnification;
    }

    // setMagnification sets a new Magnification for the given observation
    public function setMagnification($id, $magnification)
    {
        global $objDatabase;
        $sql = "UPDATE cometobservations SET magnification = \"$magnification\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getDc returns the Dc of the given observation
    public function getDc($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $dc = $get->dc;

        return $dc;
    }

    // setDc sets a new Dc for the given observation
    public function setDc($id, $dc)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET dc = \"$dc\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getComa returns the Coma of the given observation
    public function getComa($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $coma = $get->coma;

        return $coma;
    }

    // setComa sets a new Coma for the given observation
    public function setComa($id, $coma)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET coma = \"$coma\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getTail returns the Tail of the given observation
    public function getTail($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $tail = $get->tail;

        return $tail;
    }

    // setTail sets a new Tail for the given observation
    public function setTail($id, $tail)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET tail = \"$tail\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getPa returns the Pa of the given observation
    public function getPa($id)
    {
        global $objDatabase;

        $sql = "SELECT * FROM cometobservations WHERE id = \"$id\"";
        $run = $objDatabase->selectRecordset($sql);

        $get = $run->fetch(PDO::FETCH_OBJ);

        $pa = $get->pa;

        return $pa;
    }

    // setPa sets a new Pa for the given observation
    public function setPa($id, $pa)
    {
        global $objDatabase;

        $sql = "UPDATE cometobservations SET pa = \"$pa\" WHERE id = \"$id\" ";
        $run = $objDatabase->execSQL($sql);
    }

    // getObservations returns an array with all observations
    public function getObservations()
    {
        global $objDatabase;

        $observers = new Observers();

        $sql = "SELECT * FROM cometobservations";

        $run = $objDatabase->selectRecordset($sql);

        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $observations [] = $get->id;
        }

        if ($observations) {
            sort($observations);
        }

        return $observations;
    }

    // getPopularObservers() returns the number of observations of the
    // observers
    public function getPopularObservers()
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray("SELECT cometobservations.observerid, COUNT(cometobservations.id) As Cnt FROM cometobservations GROUP BY cometobservations.observerid ORDER BY Cnt DESC", 'observerid');
    }

    // getNumberOfDifferentObjects() returns the number of different objects
    // observed
    public function getNumberOfDifferentObjects($country = "")
    {
        global $objDatabase;

        $observers = new Observers();

        if (strcmp($country, "") == 0) {
            $sql = "SELECT COUNT(DISTINCT objectid) FROM cometobservations";
        } else {
            $sql = "SELECT COUNT(DISTINCT cometobservations.objectid) FROM cometobservations JOIN locations ON cometobservations.locationid=locations.id WHERE locations.country = \"" . $country . "\"";
        }

        return $objDatabase->result($sql);
    }

    // getNumberOfObservations() returns the total number of observations
    public function getNumberOfObservations($country = "")
    {
        global $objDatabase;

        if (strcmp($country, "") == 0) {
            $sql = "SELECT COUNT(id) FROM cometobservations";
        } else {
            $sql = "SELECT COUNT(cometobservations.id) FROM cometobservations JOIN locations ON cometobservations.locationid=locations.id WHERE locations.country = \"" . $country . "\"";
        }
        return $objDatabase->result($sql);
    }

    // getNumberOfDrawings() returns the total number of drawings
    public function getNumberOfDrawings($country = "")
    {
        global $objDatabase;

        if (strcmp($country, "") == 0) {
            $sql = "SELECT COUNT(id) FROM cometobservations WHERE hasDrawing=1";
        } else {
            $sql = "SELECT COUNT(cometobservations.id) FROM cometobservations JOIN locations ON cometobservations.locationid=locations.id WHERE locations.country = \"" . $country . "\" AND hasDrawing=1";
        }
        return $objDatabase->result($sql);
    }

    public function getDrawingsLastYear($id, $country = "")
    {
        global $objDatabase;
        $t = getdate();
        if (strcmp($country, "") == 0) {
            return $objDatabase->selectSingleValue(
                "SELECT COUNT(*) AS Cnt FROM cometobservations "
                . "WHERE observerid LIKE \""
                . $id . "\" AND date > \""
                . date('Ymd', strtotime('-1 year'))
                . "\" AND hasDrawing=1 ",
                'Cnt',
                0
            );
        } else {
            return $objDatabase->selectSingleValue(
                "SELECT COUNT(objectname) As Cnt FROM cometobservations JOIN locations "
                . "ON cometobservations.locationid=locations.id "
                . "WHERE cometobservations.date > \""
                . date('Ymd', strtotime('-1 year'))
                . "\" AND hasDrawing=1 AND cometobservations.visibility != 7 and "
                . "locations.country=\""
                . $country . "\"",
                'Cnt',
                0
            );
        }
    }

    // getNumberOfObservationsThisYear() returns the number of observations this
    // year
    public function getNumberOfObservationsThisYear($country = "")
    {
        global $objDatabase;
        $observers = new Observers();

        $t = getdate();

        if (strcmp($country, "") == 0) {
            $sql = "SELECT COUNT(id) FROM cometobservations WHERE date > \""
                . date('Ymd', strtotime('-1 year')) . "\"";
        } else {
            $sql = "SELECT COUNT(cometobservations.id) FROM cometobservations "
                . "JOIN locations ON cometobservations.locationid=locations.id "
                . "WHERE locations.country = \"" . $country . "\" AND date > \""
                . date('Ymd', strtotime('-1 year')) . "\"";
        }
        return $objDatabase->result($sql);
    }

    // getNumberOfDrawingsLastYear() returns the number of drawings this
    // year
    public function getNumberOfDrawingsLastYear($country = "")
    {
        global $objDatabase;
        $observers = new Observers();

        $t = getdate();

        if (strcmp($country, "") == 0) {
            $sql = "SELECT COUNT(id) FROM cometobservations WHERE date > \""
                . date('Ymd', strtotime('-1 year')) . "\" AND hasdrawing=1";
        } else {
            $sql = "SELECT COUNT(cometobservations.id) FROM cometobservations "
                . "JOIN locations ON cometobservations.locationid=locations.id "
                . "WHERE locations.country = \"" . $country . "\" AND date > \""
                . date('Ymd', strtotime('-1 year')) . "\" AND hasdrawing=1";
        }
        return $objDatabase->result($sql);
    }

    // getObservationsThisYear($id) returns the number of observations of the
    // observer the last year
    public function getObservationsThisYear($id)
    {
        $date = date("Y") . "0101";
        $q = array(
                "observer" => $id,
                "mindate" => $date
        );
        $observations = $this->getObservationFromQuery($q);
        $numberOfObservations = count($observations);
        return $numberOfObservations;
    }

    public function getObservationsThisObserver($id)
    {
        $q = array(
                "observer" => $id
        );
        $observations = $this->getObservationFromQuery($q);
        $numberOfObservations = count($observations);
        return $numberOfObservations;
    }

    // getNumberOfObjects($id) return the number of different objects seen by
    // the observer
    public function getNumberOfObjects($id)
    {
        global $objDatabase;

        $sql = "SELECT COUNT(DISTINCT objectid) FROM cometobservations "
            . "WHERE observerid=\"$id\"";

        return $objDatabase->result($sql);
    }

    // getPopularObservations() returns the number of observations of the
    // objects
    public function getPopularObservations()
    {
        global $objDatabase;
        $observers = new Observers();

        $sql = "SELECT * FROM cometobservations";
        $run = $objDatabase->selectRecordset($sql);

        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $observations [] = $get->objectid;
        }

        $numberOfObservations = array_count_values($observations);

        arsort($numberOfObservations);

        return $numberOfObservations;
    }

    // getSortedObservations returns an array with the ids of all observations,
    // sorted by the column specified in $sort
    public function getSortedObservations($sort = "date")
    {
        global $objDatabase;
        $observers = new Observers();

        if ($sort == "date") {
            $sort = "date, time";
        }

        if ($sort == "objectname") {
            $sql = "SELECT cometobservations.id FROM cometobservations "
                . "LEFT JOIN cometobjects on "
                . "cometobservations.objectid=cometobjects.id "
                . "ORDER BY cometobjects.name ";

            $run = $objDatabase->selectRecordset($sql);
            $get = $run->fetch(PDO::FETCH_OBJ);
        } else {
            if ($sort == "inst") {
                $sort = " instruments.diameter, instruments.id";
            } elseif ($sort == "observerid") {
                $sort = " observers.name, observers.firstname";
            }
            $sql = "SELECT cometobservations.* FROM cometobservations "
                . "LEFT JOIN instruments on "
                . "cometobservations.instrumentid=instruments.id "
                . "LEFT JOIN observers on "
                . "cometobservations.observerid=observers.id ORDER BY $sort";
        }

        $run = $objDatabase->selectRecordset($sql);

        while ($get = $run->fetch(PDO::FETCH_OBJ)) {
            $observations [] = $get->id;
        }
        return $observations;
    }

    /**
     * Adds the drawing flag to the database.
     *
     * @param int $id The id of the comet observation.
     *
     * @return None
     */
    public function setDrawing($id)
    {
        global $objDatabase;
        $sql = "UPDATE cometobservations SET hasDrawing = \"1\" "
            . "where id = \"" . $id . "\"";
        $run = $objDatabase->execSQL($sql);
    }

    /**
     * Returns all the observations from a query.
     *
     * @param array $queries    The query to find the observations.
     *                          You can really enter a lot of options
     *                          here to find the needed observations:
     *                          + instrument: The id of the used instrument.
     *                          + location: The id of the location where the
     *                          observation was done.
     *                          + mindate, maxdate: The date interval to search for
     *                          observations.
     *                          + minmag, maxmag: The interval of the magnitudes of
     *                          the observed objects.
     *                          + description: A part of the description
     *                          + mindc, maxdc: The interval of the degree of
     *                          condensation.
     *                          + mincoma, maxcoma: The interval of the size
     *                          of the coma.
     *                          + mintail, maxtail: The interval of the size
     *                          of the tail.
     * @param bool  $exactmatch If set to true, the exact location, instrument,
     *                          eyepiece, lens and filter will be used as given by
     *                          ids.
     *                          If set to false, all locations, eyepieces, lenses,
     *                          filters and instruments with the same name will be
     *                          used.
     *
     * @return array An array with the names of all observations where the queries
     *               are defined in an array.
     */
    public function getObservationFromQuery($queries, $exactmatch = "1")
    {
        global $objDatabase;
        $observers = new Observers();
        $objects = new CometObjects();
        $locations = new Locations();

        $object = "";
        $sqland = 0;

        if (array_key_exists("object", $queries) && ($queries["object"] != "")) {
            if ($exactmatch == "1") {
                $alternative = $objects->getName($queries["object"]);
            } else {
                $objectList = $objects->getNameList($queries["object"]);
            }

            if ($object == "") {
                $object = $queries ["object"];
            }
        }

        $sql = "SELECT cometobservations.* FROM cometobservations "
            . "LEFT JOIN instruments on "
            . "cometobservations.instrumentid=instruments.id "
            . "LEFT JOIN cometobjects on "
            . "cometobservations.objectid=cometobjects.id "
            . "LEFT JOIN observers on "
            . "cometobservations.observerid=observers.id where";

        if ($object != "") {
            if ($exactmatch == "1") {
                $sql = $sql . " (cometobjects.name = \"$object\")";
            } else {
                $sql = $sql . " (cometobjects.name like \"%$object%\"";
                if ($objectList == "") {
                    $sql = $sql . ")";
                } else {
                    foreach ($objectList as $key => $value) {
                        $sql = $sql . " or cometobjects.name = \"$value\"";
                    }
                    $sql = $sql . ")";
                }
            }
            $sqland = 1;
        }
        if (isset($queries["observer"]) && $queries["observer"] != "") {
            $observer = $queries["observer"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.observerid = \"$observer\"";
            $sqland = 1;
        }

        if (array_key_exists("instrument", $queries)
            && ($queries["instrument"] != "")
        ) {
            $inst = $queries["instrument"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.instrumentid = \"$inst\"";
            $sqland = 1;
        }

        if (array_key_exists("mindiameter", $queries)
            && ($queries["mindiameter"] != "")
        ) {
            $diam = $queries["mindiameter"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " instruments.diameter >= \"$diam\"";
            $sqland = 1;
        }

        if (array_key_exists("maxdiameter", $queries)
            && ($queries["maxdiameter"] != "")
        ) {
            $diam = $queries["maxdiameter"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " instruments.diameter <= \"$diam\"";
            $sqland = 1;
        }

        if (isset($queries["location"]) && ($queries["location"] != "")) {
            $locs = $locations->getAllLocationsIds($queries["location"]);

            $loc = $queries["location"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }

            $sql = $sql . " (cometobservations.locationid = \"$locs[0]\"";

            $i = 1;
            while ($i < count($locs)) {
                $sql = $sql . " || cometobservations.locationid = \"$locs[$i]\"";
                $i = $i + 1;
            }
            $sql = $sql . ") ";

            $sqland = 1;
        }

        if (array_key_exists("maxdate", $queries) && ($queries["maxdate"] != "")) {
            $date = $queries["maxdate"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.date <= \"$date\"";
            $sqland = 1;
        }

        if (array_key_exists("mindate", $queries) && ($queries["mindate"] != "")) {
            $date = $queries["mindate"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.date >= \"$date\"";
            $sqland = 1;
        }

        if (array_key_exists("description", $queries)
            && ($queries["description"] != "")
        ) {
            $description = $queries["description"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.description like \"%$description%\"";
            $sqland = 1;
        }

        if (array_key_exists("minmag", $queries)
            && (strcmp($queries["minmag"], "") != 0)
        ) {
            $mag = $queries ["minmag"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " (cometobservations.mag > $mag "
                . "or cometobservations.mag like $mag)";
            $sqland = 1;
        }

        if (array_key_exists("maxmag", $queries)
            && (strcmp($queries ["maxmag"], "") != 0)
        ) {
            $mag = $queries ["maxmag"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " (cometobservations.mag < $mag "
                . "or cometobservations.mag like $mag)";
            $sqland = 1;
        }

        if (array_key_exists("maxdc", $queries) && ($queries["maxdc"] != "")) {
            $dc = $queries ["maxdc"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.dc <= $dc";
            $sqland = 1;
        }

        if (array_key_exists("mindc", $queries) && ($queries["mindc"] != "")) {
            $dc = $queries ["mindc"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.dc >= $dc";
            $sqland = 1;
        }

        if (array_key_exists("maxcoma", $queries) && ($queries["maxcoma"] != "")) {
            $coma = $queries ["maxcoma"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.coma <= $coma";
            $sqland = 1;
        }

        if (array_key_exists("mincoma", $queries) && ($queries["mincoma"] != "")) {
            $coma = $queries ["mincoma"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.coma >= $coma";
            $sqland = 1;
        }

        if (array_key_exists("maxtail", $queries) && ($queries["maxtail"] != "")) {
            $tail = $queries ["maxtail"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.tail <= $tail";
            $sqland = 1;
        }

        if (array_key_exists("mintail", $queries) && ($queries["mintail"] != "")) {
            $tail = $queries["mintail"];
            if ($sqland == 1) {
                $sql = $sql . " and";
            }
            $sql = $sql . " cometobservations.tail >= $tail";
            $sqland = 1;
        }

        $sql = $sql . ";";

        $obs = $objDatabase->selectSingleArray($sql, "id");

        if (isset($obs)) {
            return $obs;
        } else {
            return null;
        }
    }
}
