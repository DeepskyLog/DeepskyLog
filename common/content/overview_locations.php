<?php
/**
 * Generates an overview of all locations (admin only).
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!$loggedUser) {
    throw new Exception(_("You need to be logged in to change your locations or equipment."));
} elseif ($_SESSION['admin'] != "yes") {
    throw new Exception(_("You need to be logged in as an administrator to execute these operations."));
} else {
    overviewLocations();
}

/**
 * Generates an overview of all locations (admin only).
 *
 * @return None
 */
function overviewLocations()
{
    global $baseURL, $step, $min, $objLocation, $objObserver;
    global $objObservation, $objPresentations, $objUtil;
    $sites = $objLocation->getSortedLocations("name");
    $locs = $objObserver->getListOfLocations();
    echo "<div id=\"main\">";
    echo "<h4>" . _("Locations overview") . "</h4>";
    echo "<hr />";
    echo "<table class=\"table sort-table table-condensed table-striped "
        . "table-hover tablesorter custom-popup\">";
    echo "<thead><tr>";
    echo "<th>" . _("Location") . "</th>";
    echo "<th>" . _("Country") . "</th>";
    echo "<th>" . _("Longitude") . "</th>";
    echo "<th>" . _("Latitude") . "</th>";
    echo "<th>" . _("Elevation") . "</th>";
    echo "<th>" . _("Time Zone") . "</th>";
    echo "<th>" . _("NELM") . "</th>";
    echo "<th>" . _("SQM") . "</th>";
    echo "<th>" . _("Observer") . "</th>";
    echo "<th class=\"filter-false columnSelector-disable\""
        . " data-sorter=\"false\"></th>";
    echo "</tr></thead>";

    foreach ($sites as $key => $value) {
        $sitename = stripslashes(
            $objLocation->getLocationPropertyFromId($value, 'name')
        );
        $country = $objLocation->getLocationPropertyFromId($value, 'country');
        if ($objLocation->getLocationPropertyFromId($value, 'longitude') > 0) {
            $longitude = "&nbsp;" . $objPresentations->decToString(
                $objLocation->getLocationPropertyFromId($value, 'longitude')
            );
        } else {
            $longitude = $objPresentations->decToString(
                $objLocation->getLocationPropertyFromId($value, 'longitude')
            );
        }
        if ($objLocation->getLocationPropertyFromId($value, 'latitude') > 0) {
            $latitude = "&nbsp;"
            . $objPresentations->decToString(
                $objLocation->getLocationPropertyFromId($value, 'latitude')
            );
        } else {
            $latitude = $objPresentations->decToString(
                $objLocation->getLocationPropertyFromId($value, 'latitude')
            );
        }
        $timezone = $objLocation->getLocationPropertyFromId($value, 'timezone');
        $elevation = $objLocation->getLocationPropertyFromId($value, 'elevation');
        $observer = $objLocation->getLocationPropertyFromId($value, 'observer');
        $limmag = $objLocation->getLocationPropertyFromId(
            $value,
            'limitingMagnitude'
        );
        if ($limmag < -900) {
            $limmag = "&nbsp;";
        }
        $sb = $objLocation->getLocationPropertyFromId($value, 'skyBackground');
        if ($sb < -900) {
            $sb = "&nbsp;";
        }
        if ($value != "1") {
            echo "<tr>";
            echo "<td><a href=\"" . $baseURL
                . "index.php?indexAction=adapt_site&amp;location="
                . urlencode($value) . "\">$sitename</a></td>";
            echo "<td>" . $country . "</td>";
            echo "<td>" . $longitude . "</td>";
            echo "<td>" . $latitude . "</td>";
            echo "<td>" . $elevation . "</td>";
            echo "<td>" . $timezone . "</td>";
            echo "<td>" . $limmag . "</td>";
            echo "<td>" . $sb . "</td>";
            echo "<td>" . $observer . "</td>";
            // check if there are no observations made from this location
            $queries = array(
                    "location" => $value
            );
            $obs = $objObservation->getObservationFromQuery(
                $queries,
                "",
                "1",
                "False"
            );
            echo "<td>";
            if (!($objLocation->getLocationUsedFromId($value))) {
                echo "<a href=\"" . $baseURL
                    . "index.php?indexAction=validate_delete_location"
                    . "&amp;locationid="
                    . urlencode($value) . "\">" . _("Delete") . "</a>";
            }
            echo "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "<hr />";
    echo "</div>";
    $objUtil->addPager("", 50);
}
