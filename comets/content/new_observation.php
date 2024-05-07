<?php

// new_observation.php
// GUI to add a new observation of a comet to the database
global $inIndex, $loggedUser, $objUtil;
if ((! isset($inIndex)) || (! $inIndex)) {
    include "../../redirect.php";
} elseif (! ($loggedUser)) {
    throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
} else {
    new_observation();
}
function new_observation()
{
    global $baseURL, $loggedUser, $objInstrument, $objCometObject, $objCometObservation, $objPresentations, $objObserver, $objUtil, $objLocation, $instDir;
    $role = $objObserver->getObserverProperty($loggedUser, 'role', 2);
    $adapt = false;
    echo "<div id=\"main\">";
    echo "<form action=\"" . $baseURL . "index.php\" method=\"post\" enctype=\"multipart/form-data\"><div>";
    $obsid = $objUtil->checkRequestKey('observation', 0);
    if (($objUtil->checkGetKey('indexAction') == "comets_adapt_observation") && (($role == ROLEADMIN) || ($role == ROLECOMETADMIN) || ($loggedUser == $objCometObservation->getObserverId($obsid)))) {
        $adapt = true;
        echo "<input type=\"hidden\" name=\"observation\" value=\"" . $obsid . "\" />";
    }
    echo "<input type=\"hidden\" name=\"indexAction\" value=\"" . ($adapt ? "comets_validate_change_observation" : "comets_validate_observation") . "\" />";
    echo "<h4>" . _("New observation") . "</h4>";
    echo "<input type=\"submit\" class=\"btn btn-success pull-right\" name=\"addobservation\" value=\"" . ($adapt ? _("Change observation") : _("Add observation")) . "\" />&nbsp;";
    echo "<hr />";
    $id = $objUtil->checkSessionKey('observedobject', $objUtil->checkGetKey('observedobject'));
    $content = "<select name=\"comet\" class=\"form-control\">";
    $content .= "<option value=\"\">&nbsp;</option>";
    if ($adapt) {
        $objID = $objCometObservation->getObjectId($obsid);
    } else {
        $objID = $objUtil->checkSessionKey('observedobject', $objUtil->checkGetKey('observedobject', - 1));
    }
    $catalogs = $objCometObject->getSortedObjects("name");
    foreach ($catalogs as $key => $value) {
        $content .= "<option value=\"" . $value [0] . "\"" . (($objID == $objCometObject->getId($value [0])) ? " selected=\"selected\" " : "") . ">" . $value [0] . "</option>";
    }
    $content .= "</select>";
    echo "<strong>" . _("Object name") . "&nbsp;*</strong><br />";
    echo "<span class=\"form-inline\">" . $content . "</span><br />";

    $content = "<input type=\"number\" min=\"1\" max=\"31\" required class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"day\"  value=\"" . ($adapt ? substr($objCometObservation->getDate($obsid), 6, 2) : $objUtil->checkSessionKey('day')) . "\" />";
    $content .= "&nbsp;&nbsp;";
    $content .= "<select name=\"month\" required class=\"form-control\">";
    for($i = 1; $i < 13; $i++) {
        $content .= "<option value=\"" . $i . "\"" . ($adapt ? (substr($objCometObservation->getDate($obsid), 4, 2) == $i ? " selected=\"selected\"" : "") : (($objUtil->checkSessionKey('month') == $i) ? " selected=\"selected\"" : "")) . ">" . $GLOBALS ['Month' . $i] . "</option>";
    }
    $content .= "</select>";
    $content .= "&nbsp;&nbsp;";
    $content .= "<input type=\"number\" min=\"1609\" required class=\"form-control\" maxlength=\"4\" size=\"5\" name=\"year\" value=\"" . ($adapt ? substr($objCometObservation->getDate($obsid), 0, 4) : $objUtil->checkSessionKey('year')) . "\" />";
    echo "<strong>" . _("Date") . "&nbsp;*</strong><br />";
    echo "<span class=\"form-inline\">" . $content;
    echo "&nbsp;" . _("(day-month-year)") . "</span>";
    if ($objObserver->getObserverProperty($loggedUser, 'UT')) {
        $content1 = _("Time (UT)") . "&nbsp;*";
    } else {
        $content1 = _("Time (local time)") . "&nbsp;*";
    }
    $content2 = "<input type=\"number\" min=\"0\" max=\"23\" required class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"hours\" value=\"" . ($adapt ? ( int ) ($objCometObservation->getTime($obsid) / 100) : "") . "\" />&nbsp;&nbsp;" . "<input type=\"number\" min=\"0\" max=\"59\" required class=\"form-control\" maxlength=\"2\" size=\"3\" name=\"minutes\" value=\"" . ($adapt ? ($objCometObservation->getTime($obsid) % 100) : "") . "\" />";
    $content3 = _("(hours-minutes)");
    echo "<br /><strong>" . $content1 . "</strong><br />";
    echo "<span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span>";

    $content1 = _("Location");
    $content2 = "<select name=\"site\" class=\"form-control\">";
    $sites = $objLocation->getSortedLocationsList("name", $loggedUser);
    if ($adapt) {
        $theLocation = $objCometObservation->getLocationId($obsid);
    } elseif (! ($theLocation = $objUtil->checkSessionKey('location'))) {
        $theLocation = $objObserver->getObserverProperty($loggedUser, 'stdlocation', 0);
    }
    $content2 .= "<option value=\"\">&nbsp;</option>";
    for($i = 0; $i < count($sites); $i++) {
        $content2 .= "<option " . (($theLocation == $sites [$i] [0]) ? (" selected=\"selected\" ") : "") . " value=\"" . $sites [$i] [0] . "\" >" . $sites [$i] [1] . "</option>";
    }
    $content2 .= "</select>";
    $content3 = "<a href=\"" . $baseURL . "index.php?indexAction=add_location\">" . _("Add new observing site") . "</a>";
    echo "<br /><strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    $content1 = _("Instrument");
    $content2 = "<select name=\"instrument\" class=\"form-control\">";
    $instr = $objInstrument->getSortedInstrumentsList("name", $loggedUser);
    $content2 .= "<option value=\"\">&nbsp;</option>";
    foreach ($instr as $key => $value) { 	// go through instrument array
        $instrumentname = $value;
        if ($adapt) {
            $theInstrument = $objCometObservation->getInstrumentId($obsid);
        } elseif (! ($theInstrument = $objUtil->checkSessionKey('instrument'))) {
            $theInstrument = $objObserver->getObserverProperty($loggedUser, 'stdtelescope');
        }
        $content2 .= "<option " . (($key == $theInstrument) ? " selected=\"selected\"" : "") . " value=\"" . $key . "\">" . (($value == "Naked eye") ? _("Naked Eye") : $value) . "</option>";
    }
    $content2 .= "</select>";
    $content3 = "<a href=\"" . $baseURL . "index.php?indexAction=add_instrument\">" . _("Add instrument") . "</a>";
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    echo "<strong>" . _("Magnification") . "</strong><br />";
    echo "<input type=\"number\" min=\"0.0\" step=\"0.1\" class=\"form-control form-inline\" maxlength=\"3\" name=\"magnification\" size=\"4\" value=\"" . ($adapt ? $objCometObservation->getMagnification($obsid) : "") . "\"/><br />";

    $ICQMETHODS = new ICQMETHOD();
    $methods = $ICQMETHODS->getIds();
    $content1 = _("Magnitude method key");
    $content2 = "<select name=\"icq_method\" class=\"form-control\">";
    $content2 .= "<option value=\"\">&nbsp;</option>";
    foreach ($methods as $key => $value) {
        $content2 .= "<option value=\"" . $value . "\"" . ($adapt ? ($objCometObservation->getMethode($obsid) == $value ? " selected=\"selected\" " : "") : "") . ">" . $value . " - " . $ICQMETHODS->getDescription($value) . "</option>";
    }
    $content2 .= "</select>";
    $content3 = "<a href=\"http://cfa-www.harvard.edu/icq/ICQKeys.html\" rel=\"external\">" . _("More info about the codes") . "</a>";
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    $ICQREFERENCEKEYS = new ICQREFERENCEKEY();
    $methods = $ICQREFERENCEKEYS->getIds();
    $content1 = _("Magnitude reference key");
    $content2 = "<select name=\"icq_reference_key\" class=\"form-control\">";
    $content2 .= "<option value=\"\">&nbsp;</option>";
    foreach ($methods as $key => $value) {
        $content2 .= "<option value=\"$value\"" . ($adapt ? ($objCometObservation->getChart($obsid) == $value ? " selected=\"selected\" " : "") : "") . ">" . $value . " - " . $ICQREFERENCEKEYS->getDescription($value) . "</option>";
    }
    $content2 .= "</select>";
    $content3 = "<a href=\"http://cfa-www.harvard.edu/icq/ICQRec.html\" rel=\"external\">" . _("More info about the codes") . "</a>";
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    $content1 = _("Magnitude");
    $content2 = "<select name=\"smaller\" class=\"form-control\">";
    $content2 .= "<option value=\"0\">&nbsp;</option>";
    $content2 .= "<option value=\"1\"" . ($adapt && $objCometObservation->getMagnitudeWeakerThan($obsid) ? " selected=\"selected\" " : "") . ">" . _("Weaker than") . "</option>";
    $content2 .= "</select>";
    $content2 .= "&nbsp;";
    $content2 .= "<input type=\"number\" min=\"-5.00\" step=\"0.01\" class=\"form-control\" maxlength=\"4\" name=\"mag\" size=\"4\" value=\"" . ($adapt ? ($objCometObservation->getMagnitude($obsid) != - 99.9 ? $objCometObservation->getMagnitude($obsid) : '') : "") . "\"/>";
    $content2 .= "&nbsp;<input type=\"checkbox\" name=\"uncertain\" " . ($adapt && $objCometObservation->getMagnitudeUncertain($obsid) ? " checked=\"checked\" " : "") . " />" . _("Uncertain");
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "</span><br />";

    $content1 = _("Degree of condensation");
    $content2 = "<select name=\"condensation\" class=\"form-control\">";
    $content2 .= "<option value=\"\">&nbsp;</option>";
    for($i = 0; $i <= 9; $i++) {
        $content2 .= "<option value=\"" . $i . "\"" . ($adapt && ($objCometObservation->getDc($obsid) == $i) ? " selected=\"selected\" " : "") . ">" . $i . "</option>";
    }
    $content2 .= "</select>";
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "</span><br />";

    $content1 = _("Coma");
    $content2 = "<input type=\"number\" min=\"0.0\" step=\"0.1\" class=\"form-control\" maxlength=\"3\" name=\"coma\" size=\"4\" value=\"" . ($adapt ? ($objCometObservation->getComa($obsid) != - 99 ? $objCometObservation->getComa($obsid) : '') : "") . "\" />";
    $content3 = _("arcminutes");
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    $content1 = _("Tail length");
    $content2 = "<input type=\"number\" min=\"0.0\" step=\"0.1\" class=\"form-control\" maxlength=\"3\" name=\"tail_length\" size=\"4\" value=\"" . ($adapt ? ($objCometObservation->getTail($obsid) != - 99 ? $objCometObservation->getTail($obsid) : '') : "") . "\" />";
    $content3 = _("arcminutes");
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    $content1 = _("Position angle of tail");
    $content2 = "<input type=\"number\" min=\"0.0\" max=\"360.0\" step=\"0.1\" class=\"form-control\" maxlength=\"3\" name=\"position_angle\" size=\"4\" value=\"" . ($adapt ? ($objCometObservation->getPa($obsid) != - 99 ? $objCometObservation->getPa($obsid) : '') : "") . "\" />";
    $content3 = _("degrees");
    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form-inline\">" . $content2;
    echo "&nbsp;" . $content3 . "</span><br />";

    $content1 = _("Drawing");
    $content2 = "<input type=\"file\" id=\"drawing\" name=\"drawing\" data-show-remove=\"false\" accept=\"image/*\" class=\"file-loading\" />";

    // Make sure to show the current drawing
    $imaLocation = "";
    if ($obsid) {
        $upload_dir = 'cometdrawings';
        $dir = opendir($instDir . 'comets/' . $upload_dir);
        while (false !== ($file = readdir($dir))) {
            if ("." == $file or ".." == $file) {
                continue; // skip current directory and directory above
            }
            if (fnmatch($obsid . "_resized.gif", $file) || fnmatch($obsid . "_resized.jpg", $file) || fnmatch($obsid . "_resized.png", $file)) {
                $imaLocation = $baseURL . "comets/" . $upload_dir . "/" . $obsid . ".jpg";
            }
        }
    }

    // The javascript for the fileinput plugins
    echo "<script type=\"text/javascript\">";
    echo "$(document).on(\"ready\", function() {
		$(\"#drawing\").fileinput({";
    if ($imaLocation != "") {
        echo "    initialPreview: [
					// Show the correct file.
					'<img src=\"" . $imaLocation . "\" class=\"file-preview-image\">'
				],";
    }

    echo "
			maxFileCount: 1,
			validateInitialCount: true,
			overwriteInitial: true,
			autoReplace: true,
			showRemove: false,
			showUpload: false,
			removeLabel: '',
			removeIcon: '',
			removeTitle: '',
			layoutTemplates: {actionDelete: ''},
			allowedFileTypes: [\"image\"],
			initialCaption: \"" . _("Drawing") . "\",
		});
	});";
    echo "</script>";

    echo "<strong>" . $content1 . "</strong>";
    echo "<br /><span class=\"form\">" . $content2;
    echo "</span><br />";

    $content1 = _("Description");
    $content2 = "<textarea maxlength=\"500\" name=\"description\" class=\"form-control\" rows=\"5\" >" . ($adapt ? $objCometObservation->getDescription($obsid) : "") . "</textarea>";
    echo "<strong>" . $content1 . "</strong>";
    echo "<br />" . $content2;
    echo "<br />";

    echo "</div>";
    echo "<input type=\"submit\" class=\"btn btn-success\" name=\"addobservation\" value=\"" . ($adapt ? _("Change observation") : _("Add observation")) . "\" />&nbsp;";

    echo "</form>";
    echo "<hr />";
    echo "</div>";
}
