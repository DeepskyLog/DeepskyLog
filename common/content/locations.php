<?php
/** 
 * Generates an overview of all locations of the logged in observer.
 * 
 * PHP Version 7
 * 
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!$loggedUser) {
    throw new Exception(_("You need to be logged in to change your locations or equipment."));
} else {
    locations();
}

/** 
 * Generates an overview of all locations of the logged in observer.
 * 
 * @return None
 */
function locations() 
{
    global $baseURL, $loggedUser, $loggedUserName, $sites;
    global $objLocation, $objObserver, $objPresentations, $objUtil;
    $sort = $objUtil->checkRequestKey('sort', 'name');
    $sites = $objLocation->getSortedLocations($sort, $loggedUser);
    echo "<div id=\"main\">";
    echo "<h4>" . sprintf(_("Observing sites of %s"), $loggedUserName) . "</h4>";
    echo "<hr />";
    echo "<form role=\"form\" action=\"" . $baseURL 
        . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\" value=\"add_location\" />";
    echo "<input type=\"submit\" class=\"btn btn-success pull-right tour4\"" 
        . " name=\"add\" value=\"" . _("Add site") . "\" />&nbsp;";
    echo "</form>";
    $objLocation->showLocationsObserver();
    echo "<br /><br />";

    echo "</div></form>";
    echo "</div>";
}
?>
