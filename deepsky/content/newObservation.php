<?php
/** 
 * Adds a new observation to the database.
 * 
 * PHP Version 7
 * 
 * @category Deepsky
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (! $loggedUser) {
    throw new Exception(LangException002);
} else {
    newObservation();
}

/** 
 * Adds a new observation to the database.
 * 
 * @return None
 */
function newObservation()
{
    global $baseURL, $loggedUser, $DSOcatalogs, $ClusterTypeA, $ClusterTypeB;
    global $ClusterTypeC, $ClusterTypeD, $ClusterTypeE, $ClusterTypeF, $ClusterTypeG;
    global $ClusterTypeH, $ClusterTypeI, $ClusterTypeX, $objObservation, $objObject;
    global $objFilter, $objLens, $objEyepiece, $objLanguage, $objObserver;
    global $objInstrument, $objLocation, $objPresentations, $objUtil;

    echo "<script type=\"text/javascript\" src=\"" . $baseURL 
        . "lib/javascript/checkUtils.js\"></script>";
    echo '<script type="text/javascript">
            $(document).ready(function() {
                $("#sqm").change();
                $("#site").change(function() {
                    // We have the id of the location, we need to find the sqm now...
                    var id = ($(this).find("option:selected").attr("value"));
                    // Read from ajaxinterface.php -> getLocationSqm, using id
                    var url="' . $baseURL 
        . 'ajaxinterface.php?instruction=getLocationSqm&id=" + id;

                    var jsonhttp;
                    if(window.XMLHttpRequest)
                      jsonhttp=new XMLHttpRequest();
                    else if(window.activeXObject)
                      jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    jsonhttp.onreadystatechange=function()
                    { 
                      if(jsonhttp.readyState==4)
                      { 
                        // We now have the sqm from the selected location
                        // Convert to number...
                        $("#sqm").val(Number(JSON.parse(jsonhttp.responseText))).change();
                      }
                    };
                    jsonhttp.open("GET",url,true);
                    jsonhttp.send(null);
                });
            });
            </script>';

    // Function to convert between bortle, sqm and limiting magnitude
    echo '<script src="' . $baseURL 
        . 'lib/javascript/sqm.js" type="text/javascript"></script>';
    
    // Script to change the visibility when we are observing a resolved open cluster
    echo "<script type=\"text/javascript\">
    function setOptions(opn, oc1, oc2, oc3, oc4, oc5, oc6, oc7, 
                        vis1, vis2, vis3, vis4, vis5, vis6, vis7)
    {
        var selbox = document.getElementById('visibility');
        if(opn == true) {
            selbox.options.length = 0;
            if (document.getElementById('resolved').checked == true) {
                selbox.options[selbox.options.length] = new Option('-----','0');
                selbox.options[selbox.options.length] = new Option(oc1,'1');
                selbox.options[selbox.options.length] = new Option(oc2,'2');
                selbox.options[selbox.options.length] = new Option(oc3,'3');
                selbox.options[selbox.options.length] = new Option(oc4,'4');
                selbox.options[selbox.options.length] = new Option(oc5,'5');
                selbox.options[selbox.options.length] = new Option(oc6,'6');
                selbox.options[selbox.options.length] = new Option(oc7,'7');
            } else {
                selbox.options[selbox.options.length] = new Option('-----','0');
                selbox.options[selbox.options.length] = new Option(vis1,'1');
                selbox.options[selbox.options.length] = new Option(vis2,'2');
                selbox.options[selbox.options.length] = new Option(vis3,'3');
                selbox.options[selbox.options.length] = new Option(vis4,'4');
                selbox.options[selbox.options.length] = new Option(vis5,'5');
                selbox.options[selbox.options.length] = new Option(vis6,'6');
                selbox.options[selbox.options.length] = new Option(vis7,'7');
            }
        }
    }
    </script>";

    $eyeps = $objEyepiece->getSortedEyepieces("focalLength", $loggedUser, "");
    $instruments = $objInstrument->getSortedInstruments("name", $loggedUser, "");
    $lns = $objLens->getSortedLenses("name", $loggedUser, "");

    echo "<script type=\"text/javascript\">
            var eyePieces = [";
    $num_rows = count($eyeps);
    $i = 0;
    foreach ($eyeps as $key=>$value) {
        $i++;
        echo "{";
        echo "\"id\" : \"" 
            . $objEyepiece->getEyepiecePropertyFromId($value, 'id') . "\",";
        echo "\"focalLength\" : \"" 
            . $objEyepiece->getEyepiecePropertyFromId($value, 'focalLength') . "\"";
        echo "}";
        echo ($i < $num_rows) ? "," : "";

    }
    echo "];";

    echo "var instruments = [";
    $num_rows = count($instruments);
    $i = 0;
    foreach ($instruments as $key=>$value) {
        $i++;
        echo "{";
        echo "\"id\" : \""
            . $objInstrument->getInstrumentPropertyFromId($value, 'id') . "\",";
        echo "\"diameter\" : \""
            . $objInstrument->getInstrumentPropertyFromId($value, 'diameter') 
            . "\",";
        echo "\"fd\" : \""
            . $objInstrument->getInstrumentPropertyFromId($value, 'fd') . "\",";
        echo "\"fixedMagnification\" : \""
            . $objInstrument->getInstrumentPropertyFromId(
                $value, 'fixedMagnification'
            ) . "\"";
        echo "}";
        echo ($i < $num_rows) ? "," : "";

    }
    echo "];";

    echo "var lenses = [";
    $num_rows = count($lns);
    $i = 0;
    foreach ($lns as $key=>$value) {
        $i++;
        echo "{";
        echo "\"id\" : \"". $objLens->getLensPropertyFromId($value, 'id') . "\",";
        echo "\"factor\" : \"". $objLens->getLensPropertyFromId($value, 'factor')
            . "\"";
        echo "}";
        echo ($i < $num_rows) ? "," : "";

    }
    echo "];";

    echo"function fillMagnification(){
    var instrument = $.grep(instruments, function(e) {
        return e.id == $('#instrumentSelect').val()
    })[0];
    var focalLengthInstrument = instrument.fd * instrument.diameter;
    var eyepiece = $.grep(eyePieces, function(e) {
        return e.id == $('#eyepieceSelect').val()}
    )[0];
    var lens = $.grep(lenses, function(e){return e.id == $('#lensSelect').val()})[0];
    var magnification;

    if(eyepiece != null){
        var magnification = focalLengthInstrument/eyepiece.focalLength;
    }
    if(lens != null){
        magnification = magnification * lens.factor;
    }
    if(instrument.fixedMagnification != 0){
        magnification = instrument.fixedMagnification;
    }

    magnification = Math.round(magnification * 10 ) / 10;

    $('#magnificationInput').val(magnification);
    }
    </script>";
    echo "<div id=\"main\">";
    $observationid = $objUtil->checkGetKey('observation');
    if ($observationid 
        && $objUtil->checkAdminOrUserID(
            $objObservation->getDsObservationProperty(
                $_GET['observation'], 'observerid'
            )
        )
    ) {
        $object = $objObservation->getDsObservationProperty(
            $observationid, 'objectname'
        );
    } else {
        $observationid = 0;
        $object = $objUtil->checkPostKey('object', $objUtil->checkGetKey('object'));
    }
    $timestamp = $objUtil->checkPostKey('timestamp', -1);
    if ($object 
        && ($objUtil->checkArrayKey($_SESSION, 'addObs', 0) == $timestamp)
    ) {
        echo "<form role=\"form\" action=\"" . $baseURL 
            . "index.php\" method=\"post\" enctype=\"multipart/form-data\"><div>";
        echo "<input type=\"hidden\" name=\"indexAction\"" 
            . " value=\"validate_observation\" />";
        echo "<input type=\"hidden\" name=\"titleobject\"   value=\"" 
            . LangQuickPickNewObservation . "\" />";
        echo "<input type=\"hidden\" name=\"observationid\" value=\"" 
            . $observationid . "\" />";
        echo "<input type=\"hidden\" name=\"timestamp\"     value=\"" 
            . $_POST['timestamp'] . "\" />";
        echo "<input type=\"hidden\" name=\"object\"        value=\"" 
            . $object . "\" />";
        if ($observationid) {
            $content = "<input class=\"btn btn-success pull-right\" type=\"submit\"" 
                . " name=\"changeobservation\" value=\"" 
                . LangChangeObservationButton . "\" />&nbsp;";
            echo "<h4>" . LangNewObservationSubtitle3B 
                . LangNewObservationSubtitle3C 
                . $object . "</h4>";
            echo $content;
        } else {
            $content = "<input class=\"btn btn-success pull-right\" type=\"submit\"" 
                . " name=\"addobservation\" value=\"" 
                . LangViewObservationButton1 . "\" />&nbsp;";
            echo "<h4>" . LangNewObservationSubtitle3 . LangNewObservationSubtitle3C 
                . $object . "</h4>";
            echo $content;
        }
        echo "<hr />";
        echo "<div class=\"inputDiv\">";
        // Location ==========================================================
        if (array_key_exists('observation', $_GET)) {
            $activeSites = '';
        } else {
            $activeSites = 1;
        }
        $sites = $objLocation->getSortedLocationsList(
            "name", $loggedUser, $activeSites
        );
        $theLoc = (($observationid) 
            ? $objObservation->getDsObservationProperty(
                $_GET['observation'], 'locationid'
            ) : $objUtil->checkPostKey('site'));
        $contentLoc = "<select required class=\"form-control\" name=\"site\"" 
            . " id=\"site\">";
        foreach ($sites as $key=>$value) {
            $contentLoc .= "<option " 
                . (($value[0] == $theLoc) ? "selected=\"selected\"" : '') 
                . "\" value=\"" . $value[0] . "\">" 
                . $value[1] . "</option>";
        }
        $contentLoc .= "</select>&nbsp;";
        // Date and time =====================================================
        if ($observationid) {
            if ($objObserver->getObserverProperty($loggedUser, 'UT')) {
                $date = sscanf(
                    $objObservation->getDsObservationProperty(
                        $observationid, 'date'
                    ), "%4d%2d%2d"
                );
                $timestr = $objObservation->getDsObservationProperty(
                    $observationid, 'time'
                );
            } else {
                $date = sscanf(
                    $objObservation->getDsObservationLocalDate($observationid),
                    "%4d%2d%2d"
                );
                $timestr = $objObservation->getDsObservationLocalTime(
                    $observationid
                );
            }
            if ($timestr >= 0) {
                $time = sscanf(sprintf("%04d", $timestr), "%2d%2d");
                $theHour = $time[0];
                $theMinute = $time[1];
            } else {
                $theHour = "";
                $theMinute = "";
            }
            $theDay = $date [2];
            $theMonth = $date [1];
            $theYear = $date [0];
        } elseif ($objUtil->checkPostKey('month')) {
            $theDay = $objUtil->checkPostKey('day');
            $theMonth = $objUtil->checkPostKey('month');
            $theYear = $objUtil->checkPostKey('year');
            $theHour = $objUtil->checkPostKey('hours');
            $theMinute = $objUtil->checkPostKey('minutes');
        } else {
            $yesterday = date('Ymd', strtotime('-1 day'));
            $theYear = substr($yesterday, 0, 4);
            $theMonth = substr($yesterday, 4, 2);
            $theDay = substr($yesterday, 6, 2);
            $theHour = "";
            $theMinute = "";
        }
        $contentDate = "<input type=\"number\" min=\"1\" max=\"31\"" 
            . " class=\"form-control\" maxlength=\"2\" size=\"3\"" 
            . " name=\"day\" id=\"day\" value=\"" . $theDay 
            . "\" onkeypress=\"return checkPositiveInteger(event);\" />";
        $contentDate .= "&nbsp;&nbsp;";
        $contentDate .= "<select name=\"month\" id=\"month\"" 
            . " class=\"form-control\">";
        for ($i = 1; $i < 13; $i ++) {
            $contentDate .= "<option value=\"" . $i . "\"" 
                . (($theMonth == $i) ? " selected=\"selected\"" : "") 
                . ">" . $GLOBALS ['Month' . $i] . "</option>";
        }
        $contentDate .= "</select>";
        $contentDate .= "&nbsp;&nbsp;";
        $contentDate .= "<input type=\"number\" min=\"1609\" class=\"form-control\"" 
            . " maxlength=\"4\" size=\"4\"  name=\"year\" id=\"year\"" 
            . " onkeypress=\"return checkPositiveInteger(event);\" value=\"" 
            . $theYear . "\" />";
        $contentTime = "<input type=\"number\" min=\"0\" max=\"23\"" 
            . " class=\"form-control\"" 
            . " maxlength=\"2\" size=\"3\"name=\"hours\" value=\"" . $theHour 
            . "\" />";
        $contentTime .= "&nbsp;&nbsp;";
        $contentTime .= "<input type=\"number\" min=\"0\" max=\"59\"" 
            . " class=\"form-control\"" 
            . " maxlength=\"2\" size=\"3\" name=\"minutes\" value=\"" . $theMinute 
            . "\" />&nbsp;&nbsp;";
        // Instrument ========================================================
        $instr = $objInstrument->getSortedInstrumentsList(
            "name", $loggedUser, $activeSites
        );
        $theInstrument = (($observationid) 
            ? $objObservation->getDsObservationProperty(
                $observationid, 'instrumentid'
            ) : $objUtil->checkPostKey('instrument', 0));
        $contentInstrument = "<select required id=\"instrumentSelect\" " 
            . "onChange=\"fillMagnification();\" name=\"instrument\" " 
            . "class=\"form-control\">";
        foreach ($instr as $key=>$value) {
            $contentInstrument .= "<option " 
                . (($theInstrument == $key) 
                ? "selected=\"selected\"" : '') 
                . " value=\"" . $key . "\">" . $value . "</option>";
        }
        $contentInstrument .= "</select>&nbsp;";
        // Description =======================================================
        $theDescription = (($observationid) 
            ? $objPresentations->br2nl(
                html_entity_decode(
                    preg_replace(
                        "/&amp;/", "&", 
                        $objObservation->getDsObservationProperty(
                            $observationid, 'description'
                        )
                    )
                )
            ) : $objUtil->checkPostKey('description'));
        $contentDescription = "<textarea maxlength=\"5000\" name=\"description\"" 
            . " class=\"form-control\" rows=\"7\">" 
            . $theDescription . "</textarea>";
        // Language ==========================================================
        $theLanguage = (($observationid) 
            ? $objObservation->getDsObservationProperty($observationid, 'language') 
            : (($tempLang = $objUtil->checkPostKey('description_language')) 
            ? $tempLang 
            : $objObserver->getObserverProperty(
                $loggedUser, 'observationlanguage'
            ))
        );
        $allLanguages = $objLanguage->getAllLanguages(
            $objObserver->getObserverProperty($loggedUser, 'language')
        );
        $contentLanguage = "<select name=\"description_language\"" 
            . " class=\"form-control\">";
        foreach ($allLanguages as $key=>$value) {
            $contentLanguage .= "<option value=\"" . $key 
                . "\"" . (($theLanguage == $key) ? "selected=\"selected\"" : '') 
                . ">" . $value . "</option>";
        }
        $contentLanguage .= "</select>&nbsp;";
        // Limiting Magnitude, SQM, and Bortle ===============================
        $theLM = (($observationid) 
            ? $objObservation->getDsObservationProperty(
                $observationid, 'limmag'
            ) : $objUtil->checkPostKey('limit'));
        $contentLM = "<input type=\"number\" min=\"0.0\" max=\"8.0\" step=\"0.1\"" 
            . " class=\"form-control\" maxlength=\"3\" name=\"limit\" id=\"lm\"" 
            . " size=\"4\"" 
            . " value=\"" . ($theLM ? sprintf("%1.1f", $theLM) : '') . "\" />";
        $knownSQM = $objUtil->checkPostKey('sqm');
        if ($knownSQM == '') {
            // We read the standard SQM value of the location
            $knownSQM = $objLocation->getLocationPropertyFromId(
                $theLoc, 'skyBackground', ''
            );
        }
        $theSQM = (($observationid) 
            ? ((($tempSQM = $objObservation->getDsObservationProperty(
                $_GET['observation'], 'SQM'
            )) != - 1) ? $tempSQM : '') : $knownSQM);
        if ($theSQM < 10) {
            $theSQM = '';
        }



        $contentSQM = "<input type=\"number\" min=\"10.00\" max=\"25.00\"" 
            . " step=\"0.01\" class=\"form-control\" maxlength=\"4\" id=\"sqm\"" 
            . "  name=\"sqm\" size=\"4\"  value=\"" 
            . ($theSQM ? sprintf("%2.1f", $theSQM) : '') 
            . "\" />";

        $contentBortle =  '<select id="bortle" name="bortle">
                <option></option>
                <option value="1">1 - ' . _("Excellent dark-sky site") . '</option>
                <option value="2">2 - ' . _("Typical truly dark site") . '</option>
                <option value="3">3 - ' . _("Rural sky") . '</option>
                <option value="4">4 - ' . _("Rural/suburban transition") . '</option>
                <option value="5">5 - ' . _("Suburban sky") . '</option>
                <option value="6">6 - ' . _("Bright suburban sky") . '</option>
                <option value="7">7 - ' . _("Suburban/urban transition") . '</option>
                <option value="8">8 - ' . _("City sky") . '</option>
                <option value="9">9 - ' . _("Inner-city sky") . '</option>
              </select>';
    
        // Seeing ============================================================
        $theSeeing = (($observationid) 
            ? $objObservation->getDsObservationProperty($observationid, 'seeing') 
            : $objUtil->checkPostKey('seeing', 0));
        $contentSeeing = "<select name=\"seeing\" class=\"form-control\">";
        $contentSeeing .= "<option value=\"0\">-----</option>";
        for ($i = 1; $i < 6; $i ++) {
            $contentSeeing .= "<option value=\"" . $i . "\"" 
                . (($theSeeing == $i) ? " selected=\"selected\"" : '') 
                . ">" . $GLOBALS ['Seeing' . $i] . "</option>";
        }
        $contentSeeing .= "</select>&nbsp;";
        // Eyepiece ==========================================================
        $theEyepiece = (($observationid) 
            ? $objObservation->getDsObservationProperty(
                $observationid, 'eyepieceid'
            ) : $objUtil->checkPostKey('eyepiece'));
        $eyeps = $objEyepiece->getSortedEyepieces(
            "focalLength", $loggedUser, $activeSites
        );
        $contentEyepiece = "<select id=\"eyepieceSelect\" " 
            . "onChange=\"fillMagnification();\" name=\"eyepiece\" " 
            . "class=\"form-control\">";
        $contentEyepiece .= "<option value=\"\">-----</option>";
        foreach ($eyeps as $key=>$value) {
            $contentEyepiece .= "<option value=\"" . $value . "\" " 
                . (($value == $theEyepiece) ? " selected=\"selected\" " : '') 
                . ">" 
                . stripslashes(
                    $objEyepiece->getEyepiecePropertyFromId($value, 'name')
                ) . "</option>";
        }
        $contentEyepiece .= "</select>&nbsp;";
        // Lens ==============================================================
        $theLens = (($observationid) 
            ? $objObservation->getDsObservationProperty($observationid, 'lensid') 
            : $objUtil->checkPostKey('lens'));
        $lns = $objLens->getSortedLenses("name", $loggedUser, $activeSites);
        $contentLens = "<select id=\"lensSelect\" onChange=\"fillMagnification();\"" 
            . " name=\"lens\" class=\"form-control\">";
        $contentLens .= "<option value=\"\">-----</option>";
        foreach ($lns as $key=>$value) {
            $contentLens .= "<option value=\"" . $value . "\" " 
                . (($value == $theLens) ? " selected=\"selected\" " : '') 
                . ">" 
                . stripslashes($objLens->getLensPropertyFromId($value, 'name')) 
                . "</option>";
        }
        $contentLens .= "</select>&nbsp;";
        // Filter ============================================================
        $theFilter = (($observationid) 
            ? $objObservation->getDsObservationProperty(
                $observationid, 'filterid'
            ) : $objUtil->checkPostKey('filter'));
        $filts = $objFilter->getSortedFilters("name", $loggedUser, $activeSites);
        $contentFilter = "<select name=\"filter\" class=\"form-control\">";
        $contentFilter .= "<option value=\"\">-----</option>";
        foreach ($filts as $key=>$value) {
            $contentFilter .= "<option value=\"" . $value . "\" " 
                . (($value == $theFilter) ? " selected=\"selected\" " : '') 
                . ">" . stripslashes(
                    $objFilter->getFilterPropertyFromId($value, 'name')
                ) . "</option>";
        }
        $contentFilter .= "</select>&nbsp;";
        // Magnification =====================================================
        $theMagnification = ($observationid 
            ? $objObservation->getDsObservationProperty(
                $observationid, 'magnification'
            ) : (($tempMag = $objUtil->checkPostKey('magnification')) 
            ? sprintf("%2d", $tempMag) : ''));
        $contentMagnification = "<input id=\"magnificationInput\" type=\"number\"" 
            . " min=\"1\" step=\"0.01\" class=\"form-control\" maxlength=\"4\"" 
            . " name=\"magnification\" size=\"4\"  value=\"" . $theMagnification 
            . "\" /> x";
        // Visibility ========================================================
        $theVisibility = ($observationid 
            ? $objObservation->getDsObservationProperty(
                $observationid, 'visibility'
            ) 
            : $objUtil->checkPostKey('visibility'));
        $contentVisibility = "<select name=\"visibility\" id=\"visibility\"" 
            . " class=\"form-control\">";
        $contentVisibility .= "<option value=\"0\">-----</option>";
        for ($i = 1; $i < 8; $i ++) { 
            $contentVisibility .= "<option value=\"" . $i . "\" " 
                . (($theVisibility == $i) ? "selected=\"selected\" " : "") 
                . ">" . $GLOBALS['Visibility' . $i] . "</option>";
            $vis [$i] = $GLOBALS ['Visibility' . $i];
        }
        $contentVisibility .= "</select>&nbsp;";
        // Visibility for resolved open clusters ========================================================================================================================================
        $contentVisibilityOc = "<select name=\"visibility\" id=\"visibility\"" 
            . " class=\"form-control\">";
        $contentVisibilityOc .= "<option value=\"0\">-----</option>";
        for ($i = 1; $i < 8; $i ++) {
            $contentVisibilityOc .= "<option value=\"" . $i . "\" " 
                . (($theVisibility == $i) ? "selected=\"selected\" " : "") 
                . ">" . $GLOBALS ['VisibilityOC' . $i] . "</option>";
            $visOc [$i] = $GLOBALS ['VisibilityOC' . $i];
        }
        $contentVisibilityOc .= "</select>&nbsp;";
        // Visibility for double stars ==================================================================================================================================================
        $contentVisibilityDs = "<select name=\"visibility\" id=\"visibility\"" 
            . " class=\"form-control\">";
        $contentVisibilityDs .= "<option value=\"0\">-----</option>";
        for ($i = 1; $i < 4; $i ++) {
            $contentVisibilityDs .= "<option value=\"" . $i . "\" " 
                . (($theVisibility == $i) ? "selected=\"selected\" " : "") 
                . ">" . $GLOBALS ['VisibilityDS' . $i] . "</option>";
        }
        $contentVisibilityDs .= "</select>&nbsp;";
        // Diameter =====================================================================================================================================================================
        $theDiameter1 = ($observationid 
            ? (($tempD1 = $objObservation->getDsObservationProperty(
                $observationid, 'largeDiameter'
            )) 
            ? $tempD1 : '') : $objUtil->checkPostKey('largeDiam'));
        $theDiameter2 = ($observationid 
            ? (($tempD2 = $objObservation->getDsObservationProperty(
                $observationid, 'smallDiameter'
            )) 
            ? $tempD2 : '') : $objUtil->checkPostKey('smallDiam'));
        $theDiameterUnit = ($observationid ? 'sec' 
            : $objUtil->checkPostKey('size_units'));
        $contentDiameter = "<input type=\"number\" min=\"0.01\" step=\"0.01\"" 
            . " class=\"form-control\" maxlength=\"5\" name=\"largeDiam\"" 
            . " size=\"5\" value=\"" . $theDiameter1 . "\" />";
        $contentDiameter .= "&nbsp;x&nbsp;";
        $contentDiameter .= "<input type=\"number\" min=\"0.01\" step=\"0.01\""
            . " class=\"form-control\" maxlength=\"5\" name=\"smallDiam\"" 
            . " size=\"5\" value=\"" 
            . $theDiameter2 . "\" />";
        $contentDiameter .= "&nbsp;";
        $contentDiameter .= "<select name=\"size_units\" class=\"form-control\">";
        $contentDiameter .= "<option value=\"min\"" 
            . ($theDiameterUnit == 'min' ? " selected=\"selected\"" : "") 
            . ">" . LangNewObjectSizeUnits1 . "</option>";
        $contentDiameter .= "<option value=\"sec\"" 
            . ($theDiameterUnit == 'sec' ? " selected=\"selected\"" : "") 
            . ">" . LangNewObjectSizeUnits2 . "</option>";
        $contentDiameter .= "</select>&nbsp;";
        // Misc ========================================================
        $contentMisc1 = "<input type=\"radio\" name=\"stellarextended\"" 
            . " value=\"stellar\" " 
            . (($objUtil->checkPostKey("stellarextended") == "stellar") 
            ? "checked=\"checked\" " : "") 
            . "/>" . LangViewObservationField35 . "&nbsp;";
        $contentMisc1 .= "<input type=\"radio\" name=\"stellarextended\"" 
            . " value=\"extended\" " 
            . (($objUtil->checkPostKey("stellarextended") == "extended") 
            ? "checked=\"checked\" " : "") 
            . " />" . LangViewObservationField36 . "&nbsp;";
        $contentMisc1 .= "<input type=\"checkbox\" name=\"mottled\" " 
            . ($objUtil->checkPostKey("mottled") ? "checked " : "") 
            . "/>" . LangViewObservationField38 . "&nbsp;";
        $contentMisc2 = "";
        $contentMisc3 = "";
        $contentMisc4 = "";
        if (in_array(
            $objObject->getDsoProperty($object, 'type'), array(
                "ASTER",
                "CLANB",
                "OPNCL",
                "AA1STAR",
                "AA3STAR",
                "AA4STAR",
                "AA8STAR",
                "GLOCL"
            )
        )
        ) {
            if (in_array(
                $objObject->getDsoProperty($object, 'type'), array(
                    "OPNCL"
                )
            )
            ) {
                $opn = true;
            } else {
                $opn = false;
            }
            $contentMisc2 .= "<input type=\"checkbox\" name=\"resolved\"" 
                . " id=\"resolved\" " 
                . "onclick=\"setOptions($opn, '$visOc[1]', '$visOc[2]',"
                . " '$visOc[3]', '$visOc[4]', '$visOc[5]', '$visOc[6]',"
                . " '$visOc[7]', '$vis[1]', '$vis[2]', '$vis[3]', '$vis[4]'," 
                . " '$vis[5]', '$vis[6]', '$vis[7]')\"" 
                . ($objUtil->checkPostKey("resolved") ? "checked " : "") . "/>" 
                . LangViewObservationField37 . "&nbsp;";
            $contentMisc2 .= "<input type=\"checkbox\" name=\"unusualShape\" />" 
                . LangViewObservationField41 . "&nbsp;";
            $contentMisc2 .= "<input type=\"checkbox\" name=\"partlyUnresolved\" />" 
                . LangViewObservationField42 . "&nbsp;";
            $contentMisc2 .= "<input type=\"checkbox\" name=\"colorContrasts\" />" 
                . LangViewObservationField43;
            if ($objObject->getDsoProperty($object, 'type') != "GLOCL") {
                $contentMisc3 .= "<a href=\"" . OpenClustersLink 
                . "\" rel=\"external\" title=\"" 
                    . LangViewObservationField40Expl . "\" >" 
                    . LangViewObservationField40 . "</a>";
                $theClustertype = ($observationid 
                    ? $objObservation->getDsObservationProperty(
                        $observationid, 'clusterType'
                    ) 
                    : $objUtil->checkPostKey('clusterType'));
                $contentMisc4 = "<select class=\"form-control\"" 
                    . " name=\"clusterType\"" 
                    . " class=\"form-control\">";
                $contentMisc4 .= "<option value=\"\">-----</option>";
                $contentMisc4 .= "<option value=\"A\"" 
                    . (($theClustertype == 'A') 
                    ? " selected=\"selected\" " : '') . ">A - " . $ClusterTypeA 
                    . "</option>";
                $contentMisc4 .= "<option value=\"B\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'B') 
                    ? " selected=\"selected\" " : '') . ">B - " . $ClusterTypeB 
                    . "</option>";
                $contentMisc4 .= "<option value=\"C\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'C') 
                    ? " selected=\"selected\" " : '') . ">C - " . $ClusterTypeC 
                    . "</option>";
                $contentMisc4 .= "<option value=\"D\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'D') 
                    ? " selected=\"selected\" " : '') . ">D - " . $ClusterTypeD 
                    . "</option>";
                $contentMisc4 .= "<option value=\"E\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'E') 
                    ? " selected=\"selected\" " : '') . ">E - " . $ClusterTypeE 
                    . "</option>";
                $contentMisc4 .= "<option value=\"F\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'F') 
                    ? " selected=\"selected\" " : '') . ">F - " . $ClusterTypeF 
                    . "</option>";
                $contentMisc4 .= "<option value=\"G\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'G') 
                    ? " selected=\"selected\" " : '') . ">G - " . $ClusterTypeG 
                    . "</option>";
                $contentMisc4 .= "<option value=\"H\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'H') 
                    ? " selected=\"selected\" " : '') . ">H - " . $ClusterTypeH 
                    . "</option>";
                $contentMisc4 .= "<option value=\"I\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'I') 
                    ? " selected=\"selected\" " : '') . ">I - " . $ClusterTypeI 
                    . "</option>";
                $contentMisc4 .= "<option value=\"X\"" 
                    . (($objUtil->checkPostKey('clusterType') == 'X') 
                    ? " selected=\"selected\" " : '') . ">J - " . $ClusterTypeX 
                    . "</option>";
                $contentMisc4 .= "</select>&nbsp;";
            }
        } else if (in_array(
            $objObject->getDsoProperty($object, 'type'), array(
                "DS"
            )
        )
        ) {
            $contentMisc2 .= "<input type=\"checkbox\" name=\"equalBrightness\" />" 
                . LangDetailDS1 . "&nbsp;";
            $contentMisc2 .= "<input type=\"checkbox\" name=\"niceField\" />" 
                . LangDetailDS2;
            if ($objObject->getDsoProperty($object, 'type') != "GLOCL") {
                $contentMisc4 = LangDetailDS3 . "&nbsp;";
                $theComponent1Color = ($observationid 
                    ? $objObservation->getDsObservationProperty(
                        $observationid, 'component1'
                    ) : $objUtil->checkPostKey('component1'));
                $contentMisc4 .= "<select name=\"component1\""
                    . " class=\"form-control\">";
                $contentMisc4 .= "<option value=\"\">-----</option>";
                $contentMisc4 .= "<option value=\"1\"" 
                . (($theComponent1Color == '1') 
                    ? " selected=\"selected\" " : '') . ">" 
                    . LangDetailDSColor1 . "</option>";
                $contentMisc4 .= "<option value=\"2\"" 
                . (($theComponent1Color == '2') 
                    ? " selected=\"selected\" " : '') . ">" 
                    . LangDetailDSColor2 . "</option>";
                $contentMisc4 .= "<option value=\"3\"" 
                . (($theComponent1Color == '3') 
                    ? " selected=\"selected\" " : '') . ">" 
                    . LangDetailDSColor3 . "</option>";
                $contentMisc4 .= "<option value=\"4\"" 
                . (($theComponent1Color == '4') 
                    ? " selected=\"selected\" " : '') . ">" 
                    . LangDetailDSColor4 . "</option>";
                $contentMisc4 .= "<option value=\"5\"" 
                . (($theComponent1Color == '5') 
                    ? " selected=\"selected\" " : '') . ">" 
                    . LangDetailDSColor5 . "</option>";
                $contentMisc4 .= "<option value=\"6\"" 
                . (($theComponent1Color == '6') 
                    ? " selected=\"selected\" " : '') . ">" 
                    . LangDetailDSColor6 . "</option>";
                $contentMisc4 .= "</select>&nbsp;";

                $contentMisc4 .= "&nbsp;" . LangDetailDS4 . "&nbsp;";
                $theComponent2Color = ($observationid 
                    ? $objObservation->getDsObservationProperty(
                        $observationid, 'component2'
                    ) 
                    : $objUtil->checkPostKey('component2'));
                $contentMisc4 .= "<select name=\"component2\"" 
                    . " class=\"form-control\">";
                $contentMisc4 .= "<option value=\"\">-----</option>";
                $contentMisc4 .= "<option value=\"1\"" 
                    . (($theComponent2Color == '1') 
                    ? " selected=\"selected\" " : '') 
                    . ">" . LangDetailDSColor1 . "</option>";
                $contentMisc4 .= "<option value=\"2\"" 
                    . (($theComponent2Color == '2') 
                    ? " selected=\"selected\" " : '') 
                    . ">" . LangDetailDSColor2 . "</option>";
                $contentMisc4 .= "<option value=\"3\"" 
                    . (($theComponent2Color == '3') 
                    ? " selected=\"selected\" " : '') 
                    . ">" . LangDetailDSColor3 . "</option>";
                $contentMisc4 .= "<option value=\"4\"" 
                    . (($theComponent2Color == '4') 
                    ? " selected=\"selected\" " : '') 
                    . ">" . LangDetailDSColor4 . "</option>";
                $contentMisc4 .= "<option value=\"5\"" 
                    . (($theComponent2Color == '5') 
                    ? " selected=\"selected\" " : '') 
                    . ">" . LangDetailDSColor5 . "</option>";
                $contentMisc4 .= "<option value=\"6\"" 
                    . (($theComponent2Color == '6') 
                    ? " selected=\"selected\" " : '') 
                    . ">" . LangDetailDSColor6 . "</option>";
                $contentMisc4 .= "</select>&nbsp;";
            }
        }
        // Presentation =====================================================================================================================================================================
        echo "<div class=\"form-group\">
                   <label>" . "<a href=\"" . $baseURL 
            . "index.php?indexAction=add_location\" title=\"" 
            . LangChangeAccountField7Expl . "\" >" . LangViewObservationField4 
            . "</a></label>";
        echo "<div class=\"form-inline\">";
        echo $contentLoc;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField5 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentDate;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" 
            . (($objObserver->getObserverProperty($loggedUser, 'UT')) 
            ? LangViewObservationField9 : LangViewObservationField9lt) . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentTime . LangViewObservationField11;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . "<a href=\"" . $baseURL 
            . "index.php?indexAction=add_instrument\" title=\"" 
            . LangChangeAccountField8Expl . "\" >" . LangViewObservationField3 
            . "</a></label>";
        echo "<div class=\"form-inline\">";
        echo $contentInstrument;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField8 . "</label>";
        echo $contentDescription;
        echo "<span class=\"help-block\">" 
            . "<a href=\"http://www.distant-targets.be/beschrijven\"" 
            . " rel=\"external\">" 
            . LangViewObservationFieldHelpDescription . "</a>" . "</span>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField12 . "</label>";
        echo "<div class=\"form\">";
        echo "<input type=\"file\" id=\"drawing\" name=\"drawing\"" 
            . " data-show-remove=\"false\" accept=\"image/*\"" 
            . " class=\"file-loading\"/>";

        echo "</div>";
        echo "</div>";

        // The javascript for the fileinput plugins
        // Make sure to show the correct image.
        $imaLocation = "";
        if ($observationid) {
            if ($objObservation->getDsObservationProperty(
                $observationid, 'hasDrawing'
            )
            ) {
                $imaLocation = $baseURL . "deepsky/drawings/" 
                    . $observationid . ".jpg";
            }
        }
        echo "<script type=\"text/javascript\">";
        echo "$(document).on(\"ready\", function() {
                  $(\"#drawing\").fileinput({";
        if ($imaLocation != "") {
            echo "    initialPreview: [
                              // Show the correct file.
                              '<img src=\"" . $imaLocation 
                . "\" class=\"file-preview-image\">'
                          ],";
        }
        echo "    maxFileCount: 1,
                validateInitialCount: true,
                autoReplace: true,
                showRemove: false,
                showUpload: false,
                removeLabel: '',
                removeIcon: '',
                removeTitle: '',
                layoutTemplates: {actionDelete: ''},
                allowedFileTypes: [\"image\"],
                  });
              });";
        echo "</script>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField29 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentLanguage;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField6 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentSeeing;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField7 . " / " 
            . LangViewObservationField34 . " / " . LangAddSiteField9 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentLM . " / " . $contentSQM . " / " . $contentBortle;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . "<a href=\"" . $baseURL 
                . "index.php?indexAction=add_eyepiece\" title=\"" 
                . LangViewObservationField30Expl . "\">" 
                . LangViewObservationField30 . "</a></label>";
        echo "<div class=\"form-inline\">";
        echo $contentEyepiece;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                <label>" . "<a href=\"" . $baseURL 
            . "index.php?indexAction=add_lens\" title=\"" 
            . LangViewObservationField32Expl . "\" >" 
            . LangViewObservationField32 . "</a></label>";
        echo "<div class=\"form-inline\">";
        echo $contentLens;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                <label>" . "<a href=\"" . $baseURL 
            . "index.php?indexAction=add_filter\" title=\"" 
            . LangViewObservationField31Expl . "\" >" 
            . LangViewObservationField31 . "</a></label>";

        echo "<div class=\"form-inline\">";
        echo $contentFilter;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField39 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentMagnification;
        echo "</div>";
        echo "</div>";

        // Check if we are observing a double star.
        // If it is the case, use VisibilityDs
        if (in_array(
            $objObject->getDsoProperty($object, 'type'), array(
                "DS"
            )
        )
        ) {
            echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField22 . "</label>";
            echo "<div class=\"form-inline\">";
            echo $contentVisibilityDs;
            echo "</div>";
            echo "</div>";

            echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField33 . "</label>";
            echo "<div class=\"form-inline\">";
            echo $contentDiameter;
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField22 . "</label>";
            echo "<div class=\"form-inline\">";
            echo $contentVisibility;
            echo "</div>";
            echo "</div>";

            echo "<div class=\"form-group\">
                   <label>" . LangViewObservationField33 . "</label>";
            echo "<div class=\"form-inline\">";
            echo $contentDiameter;
            echo "</div>";
            echo "</div>";
        }
        echo "<div class=\"form-group\">";
        echo "<div class=\"form-inline\">";
        echo $contentMisc1 . $contentMisc2;
        echo "</div>";
        echo "</div>";

        echo "<div class=\"form-group\">
               <label>" . $contentMisc3 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $contentMisc4;
        echo "</div>";
        if ($observationid) {
            $content = "<input class=\"btn btn-success\" type=\"submit\"" 
                . " name=\"changeobservation\" value=\"" .
                 LangChangeObservationButton . "\" />&nbsp;";
            echo $content;
        } else {
            $content = "<input class=\"btn btn-success\" type=\"submit\"" 
                . " name=\"addobservation\" value=\"" . LangViewObservationButton1 
                . "\" />&nbsp;";
            echo $content;
        }
        echo "</div>";


        echo "</div>";
        echo "</div></form>";

        echo "<hr />";
        $seen = $objObject->getDSOseenLink($object);

        echo "<h4>" . LangViewObjectTitle . "&nbsp;" 
            . $object . "&nbsp;:&nbsp;" . $seen . "</h4>";
        echo $objPresentations->getDSSDeepskyLiveLinks1($object);
        echo $objPresentations->getDSSDeepskyLiveLinks2($object);
        $objObject->showObject($object);

        echo '<script type="text/javascript">
        var bortleChange = 1;
        // Javascript to convert from bortle to limiting magnitude and sqm
        $(document).ready(function() {  
            $("#bortle").change(function(){
                bortleChange = 1;
                bortle = $(this).find("option:selected").attr("value");

                if (bortleChange == 1) {
                    $("#lm").val(bortleToLm(bortle));
                    $("#sqm").val(bortleToSqm(bortle));
                } else {
                    bortleChange = 1;
                }
            });
        });

        $("#lm").on("keyup change", function(event) {
            lm = event.target.value;
            if (lm < 0) {
                lm = 0.0;
                $("#lm").val(lm);
            }
            sqm = lmToSqm(lm);
            $("#sqm").val(sqm);
            bortleChange = 0;
            $("#bortle").val(sqmToBortle(sqm)).change();
        });

        // Javascript to convert from sqm to limiting magnitude and bortle
        $("#sqm").on("keyup change", function(event) {
            sqm = event.target.value;

            if (sqm > 22.0) {
                sqm = 22.0;
                $("#sqm").val(22.0);
            }

            lm = sqmToLm(sqm);
            $("#lm").val(lm);

            bortleChange = 0;
            $("#bortle").val(sqmToBortle(sqm)).change();
        });

        </script>';
    
    } else {
        // no object found or not pushed on search button yet
        echo "<h4>" . LangNewObservationTitle . "</h4>";
        echo "<hr />";
        $content = LangNewObservationSubtitle1a . ", ";
        $content .= "<a href=\"" . $baseURL 
            . "index.php?indexAction=add_csv\">" . LangNewObservationSubtitle1b 
            . "</a>" . LangNewObservationSubtitle1abis;
        $content .= "<a href=\"" . $baseURL 
            . "index.php?indexAction=add_xml\">" . LangNewObservationSubtitle1c 
            . "</a>";
        echo $content;
        echo "<form role=\"form\" action=\"" . $baseURL 
            . "index.php\" method=\"post\"><div>";
        echo "<input type=\"hidden\" name=\"indexAction\"" 
            . " value=\"add_observation\" />";
        echo "<input type=\"hidden\" name=\"titleobject\" value=\"" 
            . LangQuickPickNewObservation . "\" />";
        $content = "<select name=\"catalog\" class=\"form-control\">";
        $content .= "<option value=\"\">&nbsp;</option>";
        foreach ($DSOcatalogs as $key=>$value) {
            $content .= "<option value=\"$value\">$value</option>";
        }
        $content .= "</select>";
        $content .= "&nbsp;";
        $content .= "<input type=\"text\" class=\"form-control\" maxlength=\"255\"" 
            . " name=\"number\" size=\"50\" value=\"\" />";
        $content3 = "<input class=\"btn btn-success\" type=\"submit\"" 
            . " name=\"objectsearch\" value=\"" . LangNewObservationButton1 
            . "\" />";
        echo "<div class=\"form-group\">
                   <label>" . LangViewObjectField1 . "</label>";
        echo "<div class=\"form-inline\">";
        echo $content;
        echo "</div>";
        echo "</div>";
        echo $content3;
        echo "<hr />";
        echo "</div></form>";
    }
    echo "</div>";
}
?>
