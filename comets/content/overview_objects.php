<?php

// overview_objects.php
// generates an overview of all comets in the database
global $inIndex, $loggedUser, $objUtil;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} else {
    overview_objects();
}
function overview_objects()
{
    global $baseURL, $step, $loggedUser, $objCometObject, $objPresentations, $objUtil;
    $sort = "name"; // standard sort on name
    $obstest = $objCometObject->getObjects(); // check to test if there are any objects in database
    if (sizeof($obstest) > 0) { 	// at least one object in database
        $obs = $objCometObject->getSortedObjects($sort);
    }
    $link = "" . $baseURL . "index.php?indexAction=comets_view_objects";
    // PAGE TITLE
    echo "<div id=\"main\">";
    echo "<h4>" . _("Overview all objects") . "</h4>";
    echo "<hr />";
    if (sizeof($obstest) > 0) {
        $count = 0; // counter for altering table colors
        // OBJECT TABLE HEADERS
        echo "<table class=\"table sort-tableallcomets table-condensed table-striped table-hover tablesorter custom-popup\">";
        echo "<thead><tr>";
        echo "<th>" . _("Name") . "</th>";
        echo "<th>" . _("ICQ name") . "</th>";
        echo "<th>" . _("Seen") . "</th>";
        echo "</tr></thead>";
        foreach ($obs as $key => $value) {		// go through object array
            // NAME
            $name = $value [0];
            $icqname = $objCometObject->getIcqname($objCometObject->getId($value [0]));
            // SEEN
            $seen = "-";
            $see = $objCometObject->getObserved($name);
            if ($see == 1) {
                $seen = "<a href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objCometObject->getId($value [0])) . "\">X</a>";
            }
            if ($loggedUser) {
                $see = $objCometObject->getObservedbyUser($name, $loggedUser);
                if ($see == 1) {
                    $seen = "<a href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objCometObject->getId($value [0])) . "\">Y</a>";
                }
            }
            // OUTPUT
            echo "<tr>";
            echo "<td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objCometObject->getId($value [0])) . "\">$value[0]</a></td>";
            echo "<td>$icqname</td>";
            echo "<td>$seen</td></tr>";
            $count++; // increase line counter
        }
        echo "</table>";

        $objUtil->addPager("allcomets", $count);

        echo "<hr />";
    }
    echo "</div>";
}
