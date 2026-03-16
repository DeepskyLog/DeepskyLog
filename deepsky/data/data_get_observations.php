<?php

// data_get_observations.php
// gets the observations data from the database

global $inIndex;

if((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} else {
    data_get_observations();
}

function data_get_observations()
{
    global $allLanguages,$usedLanguages,$sort,$loggedUser,$includeFile,
    $objObject,$objObserver,$objObservation,$objUtil;
    $MaxCnt = $objObservation->getMaxObservation();
    //=========================================================================================== LOOKING FOR SPECIFIC OBJECT, OR LOOKING FOR SOME OTHER CHARACTERISTIC ============================================================
    if(array_key_exists('number', $_GET) && $_GET['number']) {
        $objectarray = $objObject->getLikeDsObject("", $_GET['catalog'], $_GET['number']);
        if(count($objectarray) == 1) {
            $object = $objectarray[0];
        }
    } else {
        $object = $objUtil->checkGetKey('object');
    }

    //200811151634B: dit wordt momenteel niet verwerkt, kan er met CONVERT_TZ(dt,from_tz,to_tz) in het sql statement gewerkt worden?
    $mindate = $objUtil->checkGetKey('mindate');
    $maxdate = $objUtil->checkGetKey('maxdate');
    if($loggedUser && (!($objObserver->getObserverProperty($loggedUser, 'UT')))) {
        if ($mindate != "") {
            $mindate = $mindate - 1;
        }
        if ($maxdate != "") {
            $maxdate = $maxdate + 1;
        }
    }
    //200811151634End

    $selectedLanguages = array();
    if($objUtil->checkGetKey('myLanguages', 'false') == 'true') {
        foreach ($allLanguages as $key => $value) {
            if(array_key_exists($key, $_GET)) {
                $selectedLanguages[] = $key;
            }
        }
    }
    if((!count($selectedLanguages)) && $objUtil->checkGetKey('myLanguages')) {
        reset($allLanguages);
        foreach ($allLanguages as $key => $value) {
            if(($loggedUser && in_array($key, $usedLanguages))
            || ((!$loggedUser) && ($key == $_SESSION['lang']))) {
                $selectedLanguages[] = $key;
            }
        }
    }

    $query = array("object"           => $object,
                   "catalog"          => $objUtil->checkGetKey('catalog'),
                   "number"           => $objUtil->checkGetKey('number'),
                   "observer"         => $objUtil->checkGetKey('observer'),
                   "instrument"       => $objUtil->checkGetKey('instrument'),
                   "location"         => $objUtil->checkGetKey('site'),
                   "mindate"          => $objUtil->checkGetDate('minyear', 'minmonth', 'minday'),
                   "maxdate"          => $objUtil->checkGetDate('maxyear', 'maxmonth', 'maxday'),
                   "maxdiameter"      => ($objUtil->checkGetKey('maxdiameter') ? ($objUtil->checkGetKey('maxdiameterunits') == "inch" ? $_GET['maxdiameter'] * 25.4 : $_GET['maxdiameter']) : ''),
                   "mindiameter"      => ($objUtil->checkGetKey('mindiameter') ? ($objUtil->checkGetKey('mindiameterunits') == "inch" ? $_GET['mindiameter'] * 25.4 : $_GET['mindiameter']) : ''),
                   "type"             => $objUtil->checkGetKey('type'),
                   "con"              => $objUtil->checkGetKey('con'),
                   "maxmag"           => $objUtil->checkGetKey('maxmag'),
                   "minmag"           => $objUtil->checkGetKey('minmag'),
                   "maxsb"            => $objUtil->checkGetKey('maxsb'),
                   "minsb"            => $objUtil->checkGetKey('minsb'),
                   "maxdecl"          => $objUtil->checkGetTimeOrDegrees('maxDeclDegrees', 'maxDeclMinutes', 'maxDeclSeconds'),
                   "mindecl"          => $objUtil->checkGetTimeOrDegrees('minDeclDegrees', 'minDeclMinutes', 'minDeclSeconds'),
                               "minLat"           => $objUtil->checkGetTimeOrDegrees('minLatDegrees', 'minLatMinutes', 'minLatSeconds'),
                               "maxLat"           => $objUtil->checkGetTimeOrDegrees('maxLatDegrees', 'maxLatMinutes', 'maxLatSeconds'),
                   "atlas"            => $objUtil->checkGetKey('atlas'),
                                 "atlasPageNumber"  => $objUtil->checkGetKey('atlasPageNumber'),
                   "minra"            => $objUtil->checkGetTimeOrDegrees('minRAhours', 'minRAminutes', 'minRAseconds'),
                   "maxra"            => $objUtil->checkGetTimeOrDegrees('maxRAhours', 'maxRAminutes', 'maxRAseconds'),
                   "mindiam1"         => ($objUtil->checkGetKey('minsize') ? ($objUtil->checkGetKey('size_min_units') == "min" ? $_GET['minsize'] * 60 : $_GET['minsize']) : ''),
                   "maxdiam1"         => ($objUtil->checkGetKey('maxsize') ? ($objUtil->checkGetKey('size_max_units') == "min" ? $_GET['maxsize'] * 60 : $_GET['maxsize']) : ''),
                   "description"      => $objUtil->checkGetKey('description'),
                   "minvisibility"    => $objUtil->checkGetKey('minvisibility'),
                   "maxvisibility"    => $objUtil->checkGetKey('maxvisibility'),
                   "minlimmag"        => $objUtil->checkGetKey('minlimmag'),
                   "maxlimmag"        => $objUtil->checkGetKey('maxlimmag'),
                   "minseeing"        => $objUtil->checkGetKey('minseeing'),
                   "maxseeing"        => $objUtil->checkGetKey('maxseeing'),
                   "lens"             => $objUtil->checkGetKey('lens'),
                   "filter"           => $objUtil->checkGetKey('filter'),
                   "eyepiece"         => $objUtil->checkGetKey('eyepiece'),
                   "hasDrawing"       => $objUtil->checkGetKey('drawings', 'off'),
                   "hasNoDrawing"     => $objUtil->checkGetKey('nodrawings', 'off'),
                   "languages"        => $selectedLanguages,
                   "minobservation"   => ($objUtil->checkGetKey('newobservations') ? $objObserver->getObserverProperty($loggedUser, 'lastReadObservationId', 0) : 0),
                   "seen"             => $objUtil->checkGetKey('seen', 'A'),
                   "includefile"      => $includeFile);
    // If user requested only new observations, clear any cached query
    // so we fetch fresh results for this session.
    if ($objUtil->checkGetKey('newobservations')) {
        if (isset($_SESSION['Qobs'])) {
            unset($_SESSION['Qobs']);
        }
        if (isset($_SESSION['QobsParams'])) {
            unset($_SESSION['QobsParams']);
        }
        if (isset($_SESSION['QobsTotal'])) {
            unset($_SESSION['QobsTotal']);
        }
        if (isset($_SESSION['QobsMaxCnt'])) {
            unset($_SESSION['QobsMaxCnt']);
        }
        if (isset($_SESSION['QobsSort'])) {
            unset($_SESSION['QobsSort']);
        }
        if (isset($_SESSION['QobsSortDirection'])) {
            unset($_SESSION['QobsSortDirection']);
        }
    }
    //============================================ CHECK TO SEE IF OBSERVATIONS ALREADY FETCHED BEFORE, OTHERWISE FETCH DATA FROM DB ===============================
    $validQobs = false;
    if(array_key_exists('QobsParams', $_SESSION) && (count($_SESSION['QobsParams']) > 1) && array_key_exists('Qobs', $_SESSION) && (count($_SESSION['Qobs']) > 0) && array_key_exists('QobsMaxCnt', $_SESSION) && ($_SESSION['QobsMaxCnt'] == $MaxCnt)) {
        $validQobs = true;
    }

    if ($validQobs) {
        foreach ($_SESSION['QobsParams'] as $key => $value) {
            if(!array_key_exists($key, $query) || ($value != $query[$key])) {
                $validQobs = false;
                break;
            }
        }
    }
    if ($validQobs) {
        foreach ($_SESSION['QobsParams'] as $key => $value) {
            if(!array_key_exists($key, $_SESSION['QobsParams']) || ($value != $_SESSION['QobsParams'][$key])) {
                $validQobs = false;
                break;
            }
        }
    }
    if(!$validQobs) {
        $_SESSION['Qobs'] = $objObservation->getObservationFromQuery($query, $objUtil->checkGetKey('seen', 'A'), $objUtil->checkGetKey('exactinstrumentlocation', 0));
        $_SESSION['QobsParams'] = $query;
        $_SESSION['QobsSort'] = 'observationid';
        $_SESSION['QobsSortDirection'] = 'desc';
        $query['countquery'] = 'true';
        $_SESSION['QobsTotal'] = $objObservation->getObservationFromQuery($query, $objUtil->checkGetKey('seen'), $objUtil->checkGetKey('exactinstrumentlocation', 0));
        $_SESSION['QobsMaxCnt'] = $MaxCnt;
        $min = 0;
        if($loggedUser && (!($objObserver->getObserverProperty($loggedUser, 'UT')))) {
            if(($mindate != "") || ($maxdate != "")) {
                if($mindate != "") {
                    $mindate = $mindate + 1;
                }
                if($maxdate != "") {
                    $maxdate = $maxdate - 1;
                }
                $newkey = 0;
                $new_obs = array();
                foreach ($_SESSION['Qobs'] as $key => $value) {
                    $newdate = $objObservation->getDsObservationLocalDate($value['observationid']);
                    if ($mindate != "" && $maxdate != "") {
                        if (($newdate >= $mindate) && ($newdate <= $maxdate)) {
                            $new_obs[] = $value;
                        }
                    } elseif ($maxdate != "") {
                        if ($newdate <= $maxdate) {
                            $new_obs[] = $value;
                        }
                    } elseif ($mindate != "") {
                        if ($newdate >= $mindate) {
                            $new_obs[] = $value;
                        }
                    }
                }
                $obs = $new_obs;
            }
        }
    }
    //=========================================== REMOVE EMPTY OBSERVATIONS OF OTHER USERS =======================================
    $nonempty = array();
    if(count($_SESSION['Qobs']) > 0) {
        foreach ($_SESSION['Qobs'] as $key => $value) {
            if((strlen(trim($value['observationdescription'])) > 0) || ($loggedUser && ($value['observerid'] == $loggedUser))) {
                $nonempty[] = $value;
            }
        }
    }
    $_SESSION['Qobs'] = $nonempty;
    //=========================================== CHECK TO SEE IF SORTING IS NECESSARY ===========================================
    if(!array_key_exists('sort', $_GET)) {
        $_GET['sort'] = $_SESSION['QobsSort'];
        $_GET['sortdirection'] = $_SESSION['QobsSortDirection'];
    }
    if(!array_key_exists('sortdirection', $_GET)) {
        $_GET['sortdirection'] = $_SESSION['QobsSortDirection'];
    }
    if($_SESSION['QobsSort'] != $_GET['sort']) {
        if($_GET['sortdirection'] == 'desc') {
            if(count($_SESSION['Qobs']) > 1) {
                foreach ($_SESSION['Qobs'] as $key => $value) {
                    if($_GET['sort'] == 'observationdescription') {
                        $sortarray[strlen($value['observationdescription'])] = $value;
                    } else {
                        $sortarray[$value[$_GET['sort']].'_'.(99999999 - $value['observationid'])] = $value;
                    }
                }
                if($_GET['sort'] == 'observationdescription') {
                    ksort($sortarray, SORT_NUMERIC);
                } else {
                    uksort($sortarray, "strnatcasecmp");
                }
                $_SESSION['Qobs'] = array_values(array_reverse($sortarray, true));
            }
            $_SESSION['QobsSort'] = $_GET['sort'];
            $_SESSION['QobsSortDirection'] = 'desc';
            $min = 0;
        } else {
            if(count($_SESSION['Qobs']) > 1) {
                foreach ($_SESSION['Qobs'] as $key => $value) {
                    if($_GET['sort'] == 'observationdescription') {
                        $sortarray[strlen($value['observationdescription'])] = $value;
                    } else {
                        $sortarray[(array_key_exists($_GET['sort'], $value) ? $value[$_GET['sort']] : '').'_'.(99999999 - $value['observationid'])] = $value;
                    }
                }
                if($_GET['sort'] == 'observationdescription') {
                    ksort($sortarray, SORT_NUMERIC);
                } else {
                    uksort($sortarray, "strnatcasecmp");
                }
                $_SESSION['Qobs'] = array_values($sortarray);
            }
            $_SESSION['QobsSort'] = $_GET['sort'];
            $_SESSION['QobsSortDirection'] = 'asc';
            $min = 0;
        }
    }
    if($_SESSION['QobsSortDirection'] != $_GET['sortdirection']) {
        if(count($_SESSION['Qobs']) > 1) {
            $_SESSION['Qobs'] = array_reverse($_SESSION['Qobs'], true);
        }
        $_SESSION['QobsSortDirection'] = $_GET['sortdirection'];
        $min = 0;
    }
}
