<?php
/** 
 * Shows information of an observer.
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
} elseif (!($user = $objUtil->checkGetKey('user'))) {
    throw new Exception(_("You wanted to watch an observer, but none is specified. Please contact the developers with this message."));
} else {
    viewObserver();
}

/** 
 * Shows the page with all information, stars and statistic of the observer.
 * The observer also can change some settings.
 * 
 * @return None
 */
function viewObserver()
{
    global $user, $modules, $deepsky, $comets, $baseURL, $instDir, $loggedUser;
    global $objDatabase, $objAccomplishments, $objInstrument, $objPresentations;
    global $objObservation, $objUtil, $objCometObservation, $objObserver;
    global $objLocation;

    $name = $objObserver->getObserverProperty($user, 'name');
    $firstname = $objObserver->getObserverProperty($user, 'firstname');
    $location_id = $objObserver->getObserverProperty($user, 'stdlocation');
    $location_name = $objLocation->getLocationPropertyFromId($location_id, 'name');
    $instrumentname = $objInstrument->getInstrumentPropertyFromId(
        $objObserver->getObserverProperty($user, 'stdtelescope'), 'name' 
    );
    $userDSobservation = $objObserver->getNumberOfDsObservations($user);
    $totalDSObservations = $objObservation->getNumberOfDsObservations();
    $userDSYearObservations = $objObservation->getObservationsLastYear($user);
    $totalDSYearObservations = $objObservation->getObservationsLastYear('%');
    $userDSObjects = $objObservation->getNumberOfObjects($user);
    $totalDSobjects = $objObservation->getNumberOfDifferentObservedDSObjects();
    $totalDSDrawings = $objObservation->getNumberOfDsDrawings();
    $userDSDrawings = $objObserver->getNumberOfDsDrawings($user);
    $userDSYearDrawings = $objObservation->getDrawingsLastYear($user);
    $totalDSYearDrawings = $objObservation->getDrawingsLastYear('%');
    $userMobjects = $objObservation->getObservedCountFromCatalogOrList($user, "M");
    $userCaldwellObjects = $objObservation->getObservedCountFromCatalogOrList(
        $user, "Caldwell"
    );
    $userH400objects = $objObservation->getObservedCountFromCatalogOrList(
        $user, "H400"
    );
    $userHIIobjects = $objObservation->getObservedCountFromCatalogOrList(
        $user, "H400-II"
    );
    $userDSrank = $objObserver->getDsRank($user);
    if ($userDSrank === false) {
        $userDSrank = "-";
    } else {
        $userDSrank ++;
    }
    $userCometobservation = $objObserver->getNumberOfCometObservations($user);
    $totalCometObservations = $objCometObservation->getNumberOfObservations();
    $userCometYearObservations = $objCometObservation->getObservationsThisYear(
        $user
    );
    $totalCometYearObservations
        = $objCometObservation->getNumberOfObservationsThisYear();
    $userCometObjects = $objCometObservation->getNumberOfObjects($user);
    $totalCometobjects = $objCometObservation->getNumberOfDifferentObjects();
    $cometrank = $objObserver->getCometRank($user);
    if ($cometrank === false) {
        $cometrank = "-";
    } else {
        $cometrank ++;
    }
    $totalCometDrawings = $objCometObservation->getNumberOfDrawings();
    $userCometDrawings = $objObserver->getNumberOfCometDrawings($user);
    $userCometYearDrawings = $objCometObservation->getDrawingsLastYear($user);
    $totalCometYearDrawings = $objCometObservation->getNumberOfDrawingsLastYear();

    for ($i = 0; $i < count($modules); $i ++) {
        if (strcmp(${$modules[$i]}, $deepsky) == 0) {
            $key = $i;
            $information[$i][0] = $userDSobservation;
            $information[$i][10] = $totalDSObservations;

            $information[$i][1] = $userDSYearObservations;
            $information[$i][11] = $totalDSYearObservations;

            $information[$i][2] = $userDSObjects;
            $information[$i][12] = $totalDSobjects;
            
            $information[$i][4] = $userDSrank;

            $information[$i][5] = $userDSDrawings;
            $information[$i][15] = $totalDSDrawings;
            
            $information[$i][6] = $userDSYearDrawings;
            $information[$i][16] = $totalDSYearDrawings;
        }
        if (strcmp(${$modules[$i]}, $comets) == 0) {
            $information[$i][0] = $userCometobservation;
            $information[$i][10] = $totalCometObservations;

            $information[$i][1] = $userCometYearObservations;
            $information[$i][11] = $totalCometYearObservations;

            $information[$i][2] = $userCometObjects;
            $information[$i][12] = $totalCometobjects;
            
            $information[$i][4] = $cometrank;

            $information[$i][5] = $userCometDrawings;
            $information[$i][15] = $totalCometDrawings;

            $information[$i][6] = $userCometYearDrawings;
            $information[$i][16] = $totalCometYearDrawings;
        }
    }
    $information[count($modules)][0] = 0;
    $information[count($modules)][1] = 0;
    $information[count($modules)][2] = 0;
    $information[count($modules)][4] = 0;
    $information[count($modules)][5] = 0;
    $information[count($modules)][6] = 0;
    $information[count($modules)][10] = 0;
    $information[count($modules)][11] = 0;
    $information[count($modules)][12] = 0;
    $information[count($modules)][15] = 0;
    $information[count($modules)][16] = 0;

    for ($i = 0; $i < count($modules); $i++) {
        $information[count($modules)][0] += $information[$i][0];
        $information[count($modules)][10] += $information[$i][10];
        $information[count($modules)][1] += $information[$i][1];
        $information[count($modules)][11] += $information[$i][11];
        $information[count($modules)][2] += $information[$i][2];
        $information[count($modules)][12] += $information[$i][12];
        $information[count($modules)][5] += $information[$i][5];
        $information[count($modules)][15] += $information[$i][15];
        $information[count($modules)][6] += $information[$i][6];
        $information[count($modules)][16] += $information[$i][16];
    }

    echo "<div>";
    echo "<h4>" . $firstname . ' ' . $name . "</h4>";
    echo "<hr />";
    // We make some tabs.
    echo "<ul id=\"tabs\" class=\"nav nav-tabs\" data-tabs=\"tabs\">
          <li class=\"active\"><a href=\"#info\" data-toggle=\"tab\">" 
        . _("Info") . "</a></li>
          <li><a href=\"#observationsPerYear\" data-toggle=\"tab\">" 
        . _("Observations per year") . "</a></li>
                    <li><a href=\"#observationsPerMonth\" data-toggle=\"tab\">" 
        . _("Observations per month") . "</a></li>
          <li><a href=\"#objectTypes\" data-toggle=\"tab\">" 
        . _("Object types observed") . "</a></li>
                    <li><a href=\"#countries\" data-toggle=\"tab\">" 
        . _("Observations per country") . "</a></li>
          <li><a href=\"#stars\" data-toggle=\"tab\">" 
          . _('DeepskyLog stars') . "</a></li>
        </ul>";

    echo "<div id=\"my-tab-content\" class=\"tab-content\">";
    echo "<div class=\"tab-pane active\" id=\"info\">";
    if (array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes")) {
        // admin logged in
        echo "<br />";
        echo "<form class=\"form-horizontal\" role=\"form\" action=\"" 
            . $baseURL . "index.php\" >";

        echo "<input type=\"hidden\" name=\"indexAction\" " 
            . "value=\"change_emailNameFirstname_Password\" />";
        echo "<input type=\"hidden\" name=\"user\" value=\"" . $user . "\" />";
        echo "<div class=\"form-group\">";
        echo "<label class=\"col-sm-2 control-label\">" 
            . _("Username") . "</label>";
        echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">" 
            . $objObserver->getObserverProperty($user, 'id') . "</p>";
        echo "</div></div>";
        echo "<div class=\"form-group\">
             <label for=\"email\" class=\"col-sm-2 control-label\">" 
            . _("Email address") . "</label>
             <div class=\"col-sm-5\">
              <input type=\"email\" name=\"email\" class=\"form-control\" " 
              . "id=\"email\" value=\"" 
              . $objObserver->getObserverProperty($user, 'email') . "\">
           </div>
            </div>";
        echo "<div class=\"form-group\">
             <label for=\"firstname\" class=\"col-sm-2 control-label\">" 
            . _("First name") . "</label>
             <div class=\"col-sm-5\">
              <input type=\"text\" name=\"firstname\" class=\"form-control\"" 
            . " id=\"firstname\" value=\"" 
            . $objObserver->getObserverProperty($user, 'firstname') . "\">
                     </div>
                        <input type=\"submit\" class=\"btn btn-danger\"" 
            . " name=\"change_email_name_firstname\" value=\""
            ._("Change email / firstname / name")."\" />
            </div>";
        echo "<div class=\"form-group\">
             <label for=\"name\" class=\"col-sm-2 control-label\">" 
             . _("Last Name") . "</label>
             <div class=\"col-sm-5\">
              <input type=\"text\" name=\"name\" class=\"form-control\"" 
              . " id=\"name\" value=\"" 
              . $objObserver->getObserverProperty($user, 'name') . "\">
           </div>
            </div>";
        echo "<div class=\"form-group\">
             <label for=\"password\" class=\"col-sm-2 control-label\">" 
            . _("Password") . "</label>
             <div class=\"col-sm-3\">
              <input type=\"text\" name=\"password\" class=\"form-control\"" 
            . " id=\"password\" value=\"\" />
           </div>
             <div class=\"col-sm-2\">
                 <input type=\"submit\" class=\"btn btn-primary\"" 
            . " name=\"change_password\" value=\"" . "Change password" . "\" />
             </div>
            </div>";
        echo "<div class=\"form-group\">";
        echo "<label class=\"col-sm-2 control-label\">" 
            . _("Default observing site") . "</label>";
        echo "<div class=\"col-sm-5\"><p class=\"form-control-static\"><a href=\"" 
            . $baseURL . "index.php?indexAction=detail_location&amp;location=" 
            . urlencode($location_id) . "\">" . $location_name . "</a></p>";
        echo "</div></div>";
        echo "<div class=\"form-group\">";
        echo "<label class=\"col-sm-2 control-label\">" 
            . _("Default instrument") . "</label>";
        // Here, we set the name of the default instrument. For the current user, 
        // we need to make it possible to change the default instrument.
        echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">";
        if ($instrumentname) {
            echo "<a href=\"" . $baseURL 
                . "index.php?indexAction=detail_instrument&amp;instrument=" 
                . urlencode(
                    $objObserver->getObserverProperty($user, 'stdtelescope')
                ) . "\">" 
                . (($instrumentname == "Naked eye") 
                    ? _("Naked Eye") : $instrumentname) 
                . "</a>";
        } else {
            echo "";
        }
        echo "</p></div></div>";
        echo "</form>";
    } else {
        echo "<table class=\"table table-striped\">";
        echo " <tr>
                <td>" . _("First name") . "</td>
                <td>" 
            . $objObserver->getObserverProperty($user, 'firstname') . "</td>
               </tr>";

        echo " <tr>
                <td>" . _("Last Name") . "</td>
                <td>" . $objObserver->getObserverProperty($user, 'name') . "</td>
               </tr>";
        // Setting the default location
        echo " <tr>
                <td>" . _("Default observing site") . "</td>";
        echo "<td>";
        if ($loggedUser == $user) {
            if (array_key_exists('activeLocationId', $_GET) 
                && $_GET['activeLocationId']
            ) {
                $objObserver->setObserverProperty(
                    $loggedUser, 'stdlocation', $_GET['activeLocationId']
                );
                if (array_key_exists('Qobj', $_SESSION)) {
                    $_SESSION ['Qobj'] = $objObject->getObjectVisibilities(
                        $_SESSION['Qobj']
                    );
                }
            }
            $result = $objLocation->getSortedLocations('name', $loggedUser, 1);
            $loc = $objObserver->getObserverProperty($loggedUser, 'stdlocation');

            if ($result) {
                echo "<div class=\"btn-group\">
                  <button type=\"button\" class=\"btn btn-default dropdown-toggle\"" 
                . " data-toggle=\"dropdown\" aria-expanded=\"false\">
                    " . $objLocation->getLocationPropertyFromId($loc, 'name') 
                . "&nbsp;<span class=\"caret\"></span>";
                echo "</button> <ul class=\"dropdown-menu\">";

                $url = $baseURL . "index.php?indexAction=detail_observer&user=" 
                    . $loggedUser;
                foreach ($result as $key2=>$value) {
                    echo "  <li><a href=\"" . $url . "&amp;activeLocationId=" 
                        . $value . "\">" 
                        . $objLocation->getLocationPropertyFromId($value, 'name') 
                        . "</a></li>";
                }

                echo " </ul>";
                echo "</li>
                      </div>";
            }
            echo "</td>";
        } else {
            echo "<a href=\"" . $baseURL 
                . "index.php?indexAction=detail_location&amp;location=" 
                . urlencode($location_id) . "\">" . $location_name . "</a>
              </td>
             </tr>";
        }
        // Setting the default instrument
        echo " <tr>
              <td>" . _("Default instrument") . "</td>";
        echo "<td>";
        if ($loggedUser == $user) {
            if (array_key_exists('activeTelescopeId', $_GET) 
                && $_GET['activeTelescopeId']
            ) {
                $objObserver->setObserverProperty(
                    $loggedUser, 'stdtelescope', $_GET['activeTelescopeId']
                );
                if (array_key_exists('Qobj', $_SESSION)) {
                    $_SESSION['Qobj'] = $objObject->getObjectVisibilities(
                        $_SESSION ['Qobj']
                    );
                }
            }
            $result = $objInstrument->getSortedInstruments('name', $loggedUser, 1);
            $inst = $objObserver->getObserverProperty($loggedUser, 'stdtelescope');

            if ($result) {
                echo "<div class=\"btn-group\">
                  <button type=\"button\" class=\"btn btn-default dropdown-toggle\"" 
                    . " data-toggle=\"dropdown\" aria-expanded=\"false\">
                    " . $objInstrument->getInstrumentPropertyFromId($inst, 'name') 
                    . "&nbsp;<span class=\"caret\"></span>";
                echo "</button> <ul class=\"dropdown-menu\">";

                $url = $baseURL . "index.php?indexAction=detail_observer&user=" 
                    . $loggedUser;
                foreach ($result as $key2=>$value) {
                    echo "  <li><a href=\"" . $url . "&amp;activeTelescopeId=" 
                        . $value . "\">" 
                        . $objInstrument->getInstrumentPropertyFromId(
                            $value, 'name'
                        ) . "</a></li>";
                }

                echo " </ul>";
                echo "</li>
                      </div>";
            }
            echo "</td>";
        } else {
            echo ($instrumentname 
                ? "<a href=\"" . $baseURL 
                . "index.php?indexAction=detail_instrument&amp;instrument=" 
                . urlencode(
                    $objObserver->getObserverProperty($user, 'stdtelescope')
                ) . "\">" 
                . (($instrumentname == "Naked eye") 
                    ? _("Naked Eye") : $instrumentname) . "</a>" 
                : "") . "</td>
              </tr>";
        }
        echo '<tr>
               <td>';
        echo _("Copyright notice");
        echo ' </td>
               <td>';
        echo $objObserver->getCopyright($user);

        echo ' </td>
              </tr>';
    }
    if ($objUtil->checkSessionKey('admin') == "yes") {
        echo "<form class=\"form-horizontal\" role=\"form\" action=\"" 
        . $baseURL . "index.php\" >";
        echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_role\" />";
        echo "<input type=\"hidden\" name=\"user\" value=\"" . $user . "\" />";
        echo "<div class=\"form-group\">";
        $content = '';
        $observerRole = $objObserver->getObserverProperty($user, 'role', 2);
        if ($user != "admin") {
            echo "<div class=\"form-group\">
                <label for=\"role\" class=\"col-sm-2 control-label\">" 
                . _("Role") . "</label>
                <div class=\"col-sm-3\">
                     <select name=\"role\" class=\"form-control\">
                 <option " 
                . (($observerRole == ROLEADMIN) 
                ? "selected=\"selected\"" : "") . " value=\"0\">" 
                 . _("Admin") . "</option>
                 <option " 
                . (($observerRole == ROLEUSER) 
                ? "selected=\"selected\"" : "") . " value=\"1\">" 
                . _("User") . "</option>
                <option " 
                . (($observerRole == ROLECOMETADMIN) 
                ? "selected=\"selected\"" : "") . " value=\"4\">" 
                . _("Comet admin") . "</option>
                <option " 
                . (($observerRole == ROLEWAITLIST) 
                ? "selected=\"selected\"" : "") . " value=\"2\">" 
                . _("Waitlist") . "</option>
               </select>&nbsp;
           </div>
           <div class=\"col-sm-2\">
                <button type=\"submit\" class=\"btn btn-default\" name=\"change\">" 
            . _("Change role") . "</button>
           </div>
            </div>";
        } elseif ($observerRole == ROLEWAITLIST) {
            echo "<div class=\"form-group\">";
            echo "<label class=\"col-sm-2 control-label\">" 
                . _("Role") . "</label>";
            echo "<div class=\"col-sm-5\">" . _("Waitlist");
            echo "</div></div>";
        } else {
            // fixed admin role
            echo "<div class=\"form-group\">";
            echo "<label class=\"col-sm-2 control-label\">" 
                . _("Role") . "</label>";
            echo "<div class=\"col-sm-5\">" . _("Admin");
            echo "</div></div>";
        }
        echo "</div></form>";
    }
    echo "</table>";
    echo "<hr />";
    echo "<table class=\"table table-striped\">";
    echo " <tr>";
    echo "  <th></th>";
    echo "  <th>" . _("Total") . "</th>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <th>" . $GLOBALS[$modules[$i]];
        echo " </th>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Number of observations") . "</td>";
    echo " <td>" . $information[count($modules)][0]  . " / " 
        . $information[count($modules)][10] . " (" 
        . sprintf(
            "%.2f", 
            $information[count($modules)][0] 
            / $information[count($modules)][10] * 100
        ) . "%)";

    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][0]  . " / " 
            . $information[$i][10] . " (" 
            . sprintf(
                "%.2f", $information[$i][0] / $information[$i][10] * 100
            ) . "%)";
        echo " </td>";
    }

    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Observations last year") . "</td>";
    echo " <td>" . $information[count($modules)][1]  . " / " 
        . $information[count($modules)][11] . " (" 
        . sprintf(
            "%.2f", 
            $information[count($modules)][1] 
            / $information[count($modules)][11] * 100
        ) . "%)";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][1]  . " / " 
            . $information[$i][11] . " (" 
            . sprintf(
                "%.2f", $information[$i][1] / $information[$i][11] * 100
            ) . "%)";
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Number of drawings") . "</td>";
    echo " <td>" . $information[count($modules)][5]  . " / " 
        . $information[count($modules)][15] . " (" 
        . sprintf(
            "%.2f", 
            $information[count($modules)][5] 
            / $information[count($modules)][15] * 100
        ) . "%)";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][5]  . " / " 
            . $information[$i][15] . " (" 
            . sprintf(
                "%.2f", $information[$i][5] / $information[$i][15] * 100
            ) . "%)";
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Drawings last year") . "</td>";
    echo " <td>" . $information[count($modules)][6]  . " / " 
        . $information[count($modules)][16] . " (" 
        . sprintf(
            "%.2f", 
            $information[count($modules)][6] 
            / $information[count($modules)][16] * 100
        ) . "%)";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][6]  . " / " 
            . $information[$i][16] . " (" 
            . sprintf(
                "%.2f", $information[$i][6] / $information[$i][16] * 100
            ) . "%)";
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Different objects") . "</td>";
    echo " <td>" . $information[count($modules)][2]  . " / " 
        . $information[count($modules)][12] . " (" 
        . sprintf(
            "%.2f", 
            $information[count($modules)][2] 
            / $information[count($modules)][12] * 100
        ) . "%)";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][2]  . " / " 
            . $information[$i][12] . " (" 
            . sprintf(
                "%.2f", $information[$i][2] / $information[$i][12] * 100
            ) . "%)";
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Messier objects") . "</td>";
    echo "  <td></td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . (($key == $i) ? $userMobjects . " / 110" : "-");
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Caldwell objects") . "</td>";
    echo "  <td></td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . (($key == $i) ? $userCaldwellObjects . " / 110" : "-");
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("H400 objects") . "</td>";
    echo "  <td></td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . (($key == $i) ? $userH400objects . " / 400" : "-");
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("H II objects") . "</td>";
    echo "  <td></td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . (($key == $i) ? $userHIIobjects . " / 400" : "-");
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Rank") . "</td>";
    echo "  <td></td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][4];
        echo " </td>";
    }
    echo " </tr>";

    echo "</table>";

    if ($loggedUser != "") {
        if ($user != $loggedUser) {
            echo "<br />";
            echo "<a class=\"btn btn-primary\" href=\"" . $baseURL 
                . "index.php?indexAction=new_message&amp;receiver=" . $user . "\">";
            echo "<span class=\"glyphicon glyphicon-envelope\"></span> " 
                . _('Send message to ') . $firstname . "</a>";
        }
    }

    echo "<hr />";
    $dir = opendir($instDir . 'common/observer_pics');
    while (false !== ($file = readdir($dir))) {
        if (("." == $file) or (".." == $file)) {
            continue; // skip current directory and directory above
        }
        if (fnmatch($user . ".gif", $file) 
            || fnmatch($user . ".jpg", $file) 
            || fnmatch($user . ".png", $file)
        ) {
            echo "<div>";
            echo "<a href=\"" . $baseURL . "common/observer_pics/" . $file 
                . "\" data-lightbox=\"image-1\" data-title=\"\">";
            echo "<img class=\"viewobserver\" src=\"" . $baseURL 
                . "common/observer_pics/" . $file 
                . "\" alt=\"" . $firstname . "&nbsp;" . $name . "\"></img>
              </a></div>";
            echo "<hr />";
        }
    }

    echo "</div>";

    // The observations per year page
    echo "<div class=\"tab-pane\" id=\"observationsPerYear\">";
    // GRAPH
    // Check the date of the first observation
    $currentYear = date("Y");
    $sql = $objDatabase->selectKeyValueArray(
        "select YEAR(date),count(*) from observations where observerid=\"" . $user 
        . "\" group by YEAR(date)", "YEAR(date)", "count(*)"
    );
    $sql2 = $objDatabase->selectKeyValueArray(
        "select YEAR(date),count(*) from cometobservations where observerid=\"" 
        . $user 
        . "\" group by YEAR(date);", "YEAR(date)", "count(*)"
    );

    if (sizeof($sql) == 0) {
        $startYear = min(array_keys($sql2));
    } else if (sizeof($sql2) == 0) {
        $startYear = min(array_keys($sql));
    } else {
        $startYear = min([min(array_keys($sql)), min(array_keys($sql2))]);
    }

    // Add the JavaScript to initialize the chart on document ready
    echo "<script type=\"text/javascript\">

                var chart;
                var DSdataYear = [";
    if ($startYear < 1900) {
        $startYear = $currentYear;
    }
    for ($i = $startYear; $i <= $currentYear; $i++) {
        if (array_key_exists($i, $sql)) {
            $obs = $sql[$i];
        } else {
            $obs = 0;
        }
        if ($i != $currentYear) {
            echo $obs . ", ";
        } else {
            echo $obs;
        }
    }
    echo "];
    var cometdataYear = [";
    for ($i = $startYear; $i <= $currentYear; $i++) {
        if (array_key_exists($i, $sql2)) {
            $obs = $sql2[$i];
        } else {
            $obs = 0;
        }
        if ($i != $currentYear) {
            echo $obs . ", ";
        } else {
            echo $obs;
        }
    }
    echo "];
                        var dataYear = [";
    for ($i = $startYear; $i <= $currentYear; $i ++) {
        $obs = 0;
        if (array_key_exists($i, $sql2)) {
            $obs += $sql2[$i];
        }
        if (array_key_exists($i, $sql)) {
            $obs += $sql[$i];
        }
        if ($i != $currentYear) {
            echo $obs . ", ";
        } else {
            echo $obs;
        }
    }
    echo "];
                        var dataYearSum = 0;
                        for (var i=0;i < dataYear.length;i++) {
                            dataYearSum += dataYear[i];
                        }
                        var DSdataYearSum = 0;
                        for (var i=0;i < DSdataYear.length;i++) {
                            DSdataYearSum += DSdataYear[i];
                        }
                        var cometdataYearSum = 0;
                        for (var i=0;i < cometdataYear.length;i++) {
                            cometdataYearSum += cometdataYear[i];
                        }
                $(document).ready(function() {
                chart = new Highcharts.Chart({
                  chart: {
                    renderTo: 'container',
                    defaultSeriesType: 'line',
                                zoomType: 'x',
                    marginRight: 130,
                    marginBottom: 40
                  },
                  title: {
                    text: \"" . _("Number of observations per year") 
        . ": " . html_entity_decode($firstname, ENT_QUOTES, "UTF-8") . " " 
        . html_entity_decode($name, ENT_QUOTES, "UTF-8") . "\",
                    x: -20 //center
                  },
                  subtitle: {
                    text: '" . _("Source: ") . $baseURL . "',
                    x: -20
                  },
                  xAxis: {
                    categories: [";

    for ($i = $startYear; $i <= $currentYear; $i++) {
        if ($i != $currentYear) {
            echo "'" . $i . "', ";
        } else {
            echo "'" . $i . "'";
        }
    }

    echo "]
                  },
                  yAxis: {
                    title: {
                      text: '" . _("Observations") . "'
                  },
                            min: 0,
                  plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                  }]
                },
                tooltip: {
                  formatter: function() {
                    if (this.series.name === \"" . _("Total") . "\") {
                        return '<b>'+ this.series.name +'</b><br/>'+
                                this.x +': '+ this.y + ' (' + 
                                Highcharts.numberFormat(
                                    this.y / dataYearSum * 100
                                ) + '%)';
                    } else if (this.series.name === \"" . _("Comets") . "\") {
                        return '<b>'+ this.series.name +'</b><br/>'+
                            this.x +': '+ this.y + ' (' + 
                            Highcharts.numberFormat(
                                this.y / cometdataYearSum * 100
                            ) + '%)';
                    } else if (this.series.name === \"" . _("Deepsky") . "\") {
                            return '<b>'+ this.series.name +'</b><br/>'+
                                    this.x +': '+ this.y + ' (' + 
                                    Highcharts.numberFormat(
                                        this.y / DSdataYearSum * 100
                                    ) + '%)';
                    }
                  },
                  useHTML: true,
                              },
                              legend: {
                              layout: 'vertical',
                              align: 'right',
                              verticalAlign: 'top',
                              x: -10,
                                  y: 100,
                              borderWidth: 0
                },
                series: [{
                    name: '" 
. _("Total") . "',
                      data: dataYear
                    }, {
                    name: '" 
. _("Deepsky") . "',
                      data: DSdataYear
                    }, {
                  name: '" 
. _("Comets") . "',
                    data: cometdataYear }]
                    });
                });

                </script>";

    // Show graph
    echo "<div id=\"container\" style=\"" 
        . "width: 800px; height: 400px; margin: 0 auto\"></div>";
    echo "</div>";


    // The observations per month page
    echo "<div class=\"tab-pane\" id=\"observationsPerMonth\">";
    // GRAPH
    // Add the JavaScript to initialize the chart on document ready
    $sql = $objDatabase->selectKeyValueArray(
        "select MONTH(date),count(*) from observations where observerid=\"" 
        . $user . "\" group by MONTH(date)", "MONTH(date)", "count(*)"
    );
    $sql2 = $objDatabase->selectKeyValueArray(
        "select MONTH(date),count(*) from cometobservations where observerid=\"" 
        . $user . "\" group by MONTH(date);", "MONTH(date)", "count(*)"
    );

    echo "<script type=\"text/javascript\">
                var chart;
                        var data = [";
    for ($i = 1; $i <= 12; $i++) {
        if (array_key_exists($i, $sql)) {
            $obs = $sql[$i];
        } else {
            $obs = 0;
        }
        if ($i != 12) {
            echo $obs . ", ";
        } else {
            echo $obs;
        }
    }
    echo "];
        var cometdata = [";
    for ($i = 1; $i <= 12; $i ++) {
        if (array_key_exists($i, $sql2)) {
            $obs = $sql2[$i];
        } else {
            $obs = 0;
        }
        if ($i != 12) {
            echo $obs . ", ";
        } else {
            echo $obs;
        }
    }
    echo "];
                        var dataSum = 0;
                        for (var i=0;i < data.length;i++) {
                        dataSum += data[i];
                        }
                        var cometdataSum = 0;
                        for (var i=0;i < data.length;i++) {
                        cometdataSum += cometdata[i];
                        }

                $(document).ready(function() {
                chart = new Highcharts.Chart({
                  chart: {
                    renderTo: 'container3',
                                type: 'column',
                    marginRight: 130,
                    marginBottom: 25
                  },
                  title: {
                    text: \"" . _("Number of observations per month") . ": " 
        . html_entity_decode($firstname, ENT_QUOTES, "UTF-8") . " " 
        . html_entity_decode($name, ENT_QUOTES, "UTF-8") . "\",
                    x: -20 //center
                  },
                  subtitle: {
                    text: '" . _("Source: ") . $baseURL . "',
                    x: -20
                  },
                  xAxis: {
                    categories: [ ";

                                global $Month1Short, $Month2Short, $Month3Short;
                                global $Month4Short, $Month5Short, $Month6Short;
                                global $Month7Short, $Month8Short, $Month9Short;
                                global $Month10Short, $Month11Short, $Month12Short;
                                echo '"' . $Month1Short . '", ';
                                echo '"' . $Month2Short . '", ';
                                echo '"' . $Month3Short . '", ';
                                echo '"' . $Month4Short . '", ';
                                echo '"' . $Month5Short . '", ';
                                echo '"' . $Month6Short . '", ';
                                echo '"' . $Month7Short . '", ';
                                echo '"' . $Month8Short . '", ';
                                echo '"' . $Month9Short . '", ';
                                echo '"' . $Month10Short . '", ';
                                echo '"' . $Month11Short . '", ';
                                echo '"' . $Month12Short . "\"]
                            },
                  yAxis: {
                    title: {
                      text: '" . _("Observations") . "'
                  },
                            min: 0,
                  plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                  }]
                },
                tooltip: {
                  formatter: function() {
                    if (this.series.name === \"Deepsky\") {
                        return '<b>'+ this.series.name +'</b><br/>'+
                            this.x +': '+ this.y + ' (' + 
                            Highcharts.numberFormat(this.y / dataSum * 100) + '%)';
                    } else {
                        return '<b>'+ this.series.name +'</b><br/>'+
                            this.x +': '+ this.y + ' (' + 
                            Highcharts.numberFormat(this.y / cometdataSum * 100) 
                            + '%)';
                    }
                  },
                  useHTML: true,
                              },
                              legend: {
                              layout: 'vertical',
                              align: 'right',
                              verticalAlign: 'top',
                              x: -10,
                                  y: 100,
                              borderWidth: 0
                },
                        plotOptions: {
            column: {
                stacking: 'normal'
                            } },
                              series: [{
                                name: '" 
                    . html_entity_decode($deepsky, ENT_QUOTES, "UTF-8") . "',
                                  data: data
                                }, {
                              name: '" 
                    . html_entity_decode($comets, ENT_QUOTES, "UTF-8") . "',
                                data: cometdata
                                                           }]
                                });
                                });

                                </script>";

    // Show graph
    echo "<div id=\"container3\" style=\"" 
        . "width: 800px; height: 400px; margin: 0 auto\"></div>";
    echo "</div>";

    // The tab with the object types
    echo "<div class=\"tab-pane\" id=\"objectTypes\">";
    // Pie chart
    $deepskyobservations = $objDatabase->selectKeyValueArray(
        "select objects.type,count(*) from observations JOIN objects on " 
        . "observations.objectname=objects.name where observerid=\"" . $user 
        . "\" group by objects.type;", "type", "count(*)"
    );
    $cometobservations = count(
        $objDatabase->selectRecordsetArray(
            "select * from cometobservations where observerid=\"" . $user . "\""
        )
    );

    $objectsArray = array ();
    $colors = Array ();

    // Correct the deepskyobservations array. Make sure that all the entries 
    // are available.
    if (!array_key_exists("QUASR", $deepskyobservations)) {
        $deepskyobservations["QUASR"] = 0;
    }
    if (!array_key_exists("DS", $deepskyobservations)) {
        $deepskyobservations["DS"] = 0;
    }
    if (!array_key_exists("GALXY", $deepskyobservations)) {
        $deepskyobservations["GALXY"] = 0;
    }
    if (!array_key_exists("GALCL", $deepskyobservations)) {
        $deepskyobservations["GALCL"] = 0;
    }
    if (!array_key_exists("OPNCL", $deepskyobservations)) {
        $deepskyobservations["OPNCL"] = 0;
    }
    if (!array_key_exists("DRKNB", $deepskyobservations)) {
        $deepskyobservations["DRKNB"] = 0;
    }
    if (!array_key_exists("BRTNB", $deepskyobservations)) {
        $deepskyobservations["BRTNB"] = 0;
    }
    if (!array_key_exists("STNEB", $deepskyobservations)) {
        $deepskyobservations["STNEB"] = 0;
    }
    if (!array_key_exists("RNHII", $deepskyobservations)) {
        $deepskyobservations["RNHII"] = 0;
    }
    if (!array_key_exists("HII", $deepskyobservations)) {
        $deepskyobservations["HII"] = 0;
    }
    if (!array_key_exists("ASTER", $deepskyobservations)) {
        $deepskyobservations["ASTER"] = 0;
    }
    if (!array_key_exists("GLOCL", $deepskyobservations)) {
        $deepskyobservations["GLOCL"] = 0;
    }
    if (!array_key_exists("SNREM", $deepskyobservations)) {
        $deepskyobservations["SNREM"] = 0;
    }
    if (!array_key_exists("GXAGC", $deepskyobservations)) {
        $deepskyobservations["GXAGC"] = 0;
    }
    if (!array_key_exists("PLNNB", $deepskyobservations)) {
        $deepskyobservations["PLNNB"] = 0;
    }
    if (!array_key_exists("REFNB", $deepskyobservations)) {
        $deepskyobservations["REFNB"] = 0;
    }
    if (!array_key_exists("EMINB", $deepskyobservations)) {
        $deepskyobservations["EMINB"] = 0;
    }
    if (!array_key_exists("WRNEB", $deepskyobservations)) {
        $deepskyobservations["WRNEB"] = 0;
    }
    if (!array_key_exists("ENSTR", $deepskyobservations)) {
        $deepskyobservations["ENSTR"] = 0;
    }
    if (!array_key_exists("CLANB", $deepskyobservations)) {
        $deepskyobservations["CLANB"] = 0;
    }
    if (!array_key_exists("AA1STAR", $deepskyobservations)) {
        $deepskyobservations["AA1STAR"] = 0;
    }
    if (!array_key_exists("AA3STAR", $deepskyobservations)) {
        $deepskyobservations["AA3STAR"] = 0;
    }
    if (!array_key_exists("AA4STAR", $deepskyobservations)) {
        $deepskyobservations["AA4STAR"] = 0;
    }
    if (!array_key_exists("AA8STAR", $deepskyobservations)) {
        $deepskyobservations["AA8STAR"] = 0;
    }
    if (!array_key_exists("NONEX", $deepskyobservations)) {
        $deepskyobservations["NONEX"] = 0;
    }
    if (!array_key_exists("GACAN", $deepskyobservations)) {
        $deepskyobservations["GACAN"] = 0;
    }
    if (!array_key_exists("GXADN", $deepskyobservations)) {
        $deepskyobservations["GXADN"] = 0;
    }
    if (!array_key_exists("ENRNN", $deepskyobservations)) {
        $deepskyobservations["ENRNN"] = 0;
    }
    if (!array_key_exists("SNOVA", $deepskyobservations)) {
        $deepskyobservations["SNOVA"] = 0;
    }

    $all = array_sum($deepskyobservations) + $cometobservations;
    if ($all == 0) {
        $all = 1;
    }
    $rest = 0;

    if (($cometobservations / $all) >= 0.01) {
        $objectsArray ["comets"] = $cometobservations;
    } else {
        $rest += $cometobservations;
    }
    $colors ["comets"] = "#4572A7";

    $aster = $deepskyobservations["ASTER"];
    $aster += $deepskyobservations["AA8STAR"];
    $aster += $deepskyobservations["AA4STAR"];
    $aster += $deepskyobservations["AA3STAR"];

    if (($aster / $all) >= 0.01) {
        $objectsArray ["ASTER"] = $aster;
    } else {
        $rest += $aster;
    }
    $colors ["ASTER"] = "#AA4643";

    $brtnb = $deepskyobservations["BRTNB"];

    if (($brtnb / $all) >= 0.01) {
        $objectsArray ["BRTNB"] = $brtnb;
    } else {
        $rest += $brtnb;
    }
    $colors ["BRTNB"] = "#89A54E";

    $ds = $deepskyobservations["DS"];

    if (($ds / $all) >= 0.01) {
        $objectsArray ["DS"] = $ds;
    } else {
        $rest += $ds;
    }
    $colors ["DS"] = "#80699B";

    $star = $deepskyobservations["AA1STAR"];

    if (($star / $all) >= 0.01) {
        $objectsArray ["AA1STAR"] = $star;
    } else {
        $rest += $star;
    }
    $colors ["AA1STAR"] = "#3D96AE";

    $drknb = $deepskyobservations["DRKNB"];

    if (($drknb / $all) >= 0.01) {
        $objectsArray ["DRKNB"] = $drknb;
    } else {
        $rest += $drknb;
    }
    $colors ["DRKNB"] = "#DB843D";

    $galcl = $deepskyobservations["GALCL"];

    if (($galcl / $all) >= 0.01) {
        $objectsArray ["GALCL"] = $galcl;
    } else {
        $rest += $galcl;
    }
    $colors ["GALCL"] = "#92A8CD";

    $galxy = $deepskyobservations["GALXY"];

    if (($galxy / $all) >= 0.01) {
        $objectsArray ["GALXY"] = $galxy;
    } else {
        $rest += $galxy;
    }
    $colors ["GALXY"] = "#68302F";

    $plnnb = $deepskyobservations["PLNNB"];

    if (($plnnb / $all) >= 0.01) {
        $objectsArray ["PLNNB"] = $plnnb;
    } else {
        $rest += $plnnb;
    }
    $colors ["PLNNB"] = "#A47D7C";

    $opncl = $deepskyobservations["OPNCL"];
    $opncl += $deepskyobservations["CLANB"];

    if (($opncl / $all) >= 0.01) {
        $objectsArray ["OPNCL"] = $opncl;
    } else {
        $rest += $opncl;
    }
    $colors ["OPNCL"] = "#B5CA92";

    $glocl = $deepskyobservations["GLOCL"];

    if (($glocl / $all) >= 0.01) {
        $objectsArray ["GLOCL"] = $glocl;
    } else {
        $rest += $glocl;
    }
    $colors ["GLOCL"] = "#00FF00";

    $eminb = $deepskyobservations["EMINB"];
    $eminb += $deepskyobservations["ENRNN"];
    $eminb += $deepskyobservations["ENSTR"];

    if (($eminb / $all) >= 0.01) {
        $objectsArray ["EMINB"] = $eminb;
    } else {
        $rest += $eminb;
    }
    $colors ["EMINB"] = "#C0FFC0";

    $refnb = $deepskyobservations["REFNB"];
    $refnb += $deepskyobservations["RNHII"];
    $refnb += $deepskyobservations["HII"];

    if (($refnb / $all) >= 0.01) {
        $objectsArray ["REFNB"] = $refnb;
    } else {
        $rest += $refnb;
    }
    $colors ["REFNB"] = "#0000C0";

    $nonex = $deepskyobservations["NONEX"];

    if (($nonex / $all) >= 0.01) {
        $objectsArray ["NONEX"] = $nonex;
    } else {
        $rest += $nonex;
    }
    $colors ["NONEX"] = "#C0C0FF";

    $snrem = $deepskyobservations["SNREM"];

    if (($snrem / $all) >= 0.01) {
        $objectsArray ["SNREM"] = $snrem;
    } else {
        $rest += $snrem;
    }
    $colors ["SNREM"] = "#808000";

    $quasr = $deepskyobservations["QUASR"];

    if (($quasr / $all) >= 0.01) {
        $objectsArray ["QUASR"] = $quasr;
    } else {
        $rest += $quasr;
    }
    $colors ["QUASR"] = "#C0C000";

    $wrneb = $deepskyobservations["WRNEB"];

    if (($wrneb / $all) >= 0.01) {
        $objectsArray ["WRNEB"] = $wrneb;
    } else {
        $rest += $wrneb;
    }
    $colors ["WRNEB"] = "#008080";

    $objectsArray ["REST"] = $rest;
    $colors ["REST"] = "#00FFFF";
    echo "<script type=\"text/javascript\">

            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'container2',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: \"" . _("Object types seen") . ": " 
        . html_entity_decode($firstname, ENT_QUOTES, "UTF-8") . " " 
        . html_entity_decode($name, ENT_QUOTES, "UTF-8") . "\"
                    },
                subtitle: {
                  text: '" . _("Source: ") . $baseURL . "'
                },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '
                                + Math.round(this.percentage * 100) / 100 + '%';
                        },
                        useHTML: true,
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            showCheckbox: true,
                            dataLabels: {
                                enabled: true,
                                color: '#000000',
                                connectorColor: '#000000',
                                formatter: function() {
                                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                                }
                            }
                        }
                    },
                    series: [{
                        type: 'pie',
                        name: 'Objects seen',
                        data: [";

    foreach ($objectsArray as $key => $value) {
        if ($key != "REST") {
            print "{name: \"" 
                . html_entity_decode($GLOBALS[$key], ENT_QUOTES, "UTF-8") 
                . "\", color: '" . $colors[$key] . "', y: " . $value . "}, ";
        } else {
            print "{name: \"" 
                . html_entity_decode($GLOBALS[$key], ENT_QUOTES, "UTF-8") 
                . "\", color: '" . $colors[$key] . "', y: " . $value . "}";
        }
    }
    echo "
                        ]
                    }]
                });
            });

        </script>";
    echo "<div id=\"container2\" style=\"" 
        . "width: 800px; height: 400px; margin: 0 auto\"></div>";

    echo "</div>";

    // The tab with the observations per country
    echo "<div class=\"tab-pane\" id=\"countries\">";
    // Pie chart
    $countriesArray = array ();

    // First find a list of all countries
    $all = array_count_values(
        $objDatabase->selectSingleArray(
            "select locations.country from observations join locations on " 
            . "observations.locationid=locations.id where " 
            . "((observations.observerid=\"" . $user . "\"))", "country"
        )
    );
    $allComets = array_count_values(
        $objDatabase->selectSingleArray(
            "select locations.country from cometobservations join locations on " 
            . "cometobservations.locationid=locations.id where " 
            . "((cometobservations.observerid=\"" . $user . "\"))", "country"
        )
    );

    // We loop over the countries (we merge the deepsky and comet observations)
    $countryList = array_unique(
        array_merge(array_keys($all), array_keys($allComets))
    );
    foreach ($countryList as $country) {
        $obs = 0;
        if (array_key_exists($country, $all)) {
            $obs += $all[$country];
        }
        if (array_key_exists($country, $allComets)) {
            $obs += $allComets[$country];
        }
        $countriesArray [$country] = $obs;
    }

    echo "<script type=\"text/javascript\">

            var chart;
            $(document).ready(function() {
                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'containerCountry',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: \"" . _("Observations per country") . ": " 
        . html_entity_decode($firstname, ENT_QUOTES, "UTF-8") . " " 
        . html_entity_decode($name, ENT_QUOTES, "UTF-8") . "\"
                    },
                subtitle: {
                  text: '" . _("Source: ") . $baseURL . "'
                },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name 
                                +'</b>: '
                                + Math.round(this.percentage * 100) / 100 + '%';
                        },
                        useHTML: true,
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            showCheckbox: true,
                            dataLabels: {
                                enabled: true,
                                color: '#000000',
                                connectorColor: '#000000',
                                formatter: function() {
                                    return '<b>'+ this.point.name +'</b>: '+ this.y;
                                }
                            }
                        }
                    },
                    series: [{
                        type: 'pie',
                        name: 'Objects seen',
                        data: [";

    foreach ($countriesArray as $key => $value) {
        print "{name: \"" . $key . "\", y: " . $value . "},";
    }
    echo "
                        ]
                    }]
                });
            });

        </script>";
    echo "<div id=\"containerCountry\" style=\"" 
        . "width: 800px; height: 400px; margin: 0 auto\"></div>";

    echo "</div>";

    // Draw the stars
    echo "<div class=\"tab-pane\" id=\"stars\">";

    // Messier
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Messier objects') . "</h4>";

    $accomplishments = $objAccomplishments->getAllAccomplishments($user);

    drawStar(
        $accomplishments['messierBronze'], _('Bronze'), "bronze", 
        _("Bronze Messier certificat! You observed 25 Messier objects!"),
        _("Observe at least 25 Messier objects to get this certificat!")
    );
    drawStar(
        $accomplishments['messierSilver'], _('Silver'), "silver", 
        _("Silver Messier certificat! You observed 50 Messier objects!"),
        _("Observe at least 50 Messier objects to get this certificat!")
    );
    drawStar(
        $accomplishments['messierGold'], _('Gold'), "gold", 
        _("Golden Messier certificat! You observed all 110 Messier objects!"),
        _("Observe all 110 Messier objects to get this certificat!")
    );

    echo "</div>";

    // Messier Drawings
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of Messier objects') . "</h4>";

    drawStar(
        $accomplishments['messierDrawingsBronze'], _('Bronze'), 
        "bronze", 
        _("Bronze Messier drawing-certificat! You drew 25 Messier objects!"), 
        _("Draw at least 25 different Messier objects to get this certificat!")
    );
    drawStar(
        $accomplishments['messierDrawingsSilver'], _('Silver'), 
        "silver", 
        _("Silver Messier drawing-certificat! You drew 50 Messier objects!"), 
        _("Draw at least 50 Messier objects to get this certificat!")
    );
    drawStar(
        $accomplishments['messierDrawingsGold'], _('Gold'), 
        "gold",
        _("Golden Messier drawing-certificat! You drew all 110 Messier objects!"),
        _("Draw all 110 Messier objects to get this certificat!")
    );
    echo "</div>";

    // Caldwell
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Caldwell objects') . "</h4>";

    drawStar(
        $accomplishments['caldwellBronze'], _('Bronze'), "bronze", 
        _("Bronze Caldwell certificat! You observed 25 Caldwell objects!"), 
        _("Observe at least 25 Caldwell objects to get this certificat!")
    );
    drawStar(
        $accomplishments['caldwellSilver'], _('Silver'), "silver",
        _("Silver Caldwell certificat! You observed 50 Caldwell objects!"),
        _("Observe at least 50 Caldwell objects to get this certificat!")
    );
    drawStar(
        $accomplishments['caldwellGold'], _('Gold'), "gold", 
        _("Golden Caldwell certificat! You observed all 110 Caldwell objects!"),
        _("Observe all 110 Caldwell objects to get this certificat!")
    );
    echo "</div>";

    // Caldwell drawings
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of Caldwell objects') . "</h4>";

    drawStar(
        $accomplishments['caldwellDrawingsBronze'], _('Bronze'), 
        "bronze",
        _("Bronze Caldwell drawing-certificat! You drew 25 Caldwell objects!"), 
        _("Draw at least 25 Caldwell objects to get this certificat!")
    );
    drawStar(
        $accomplishments['caldwellDrawingsSilver'], _('Silver'), 
        "silver", 
        _("Silver Caldwell drawing-certificat! You drew 50 Caldwell objects!"), 
        _("Draw at least 50 Caldwell objects to get this certificat!")
    );
    drawStar(
        $accomplishments['caldwelldrawingsGold'], _('Gold'), 
        "gold", 
        _("Golden Caldwell drawing-certificat! You drew all 110 Caldwell objects!"), 
        _("Draw all 110 Caldwell objects to get this certificat!")
    );
    echo "</div>";

    // Herschel - 400
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Herschel 400 objects') . "</h4>";

    drawStar(
        $accomplishments['herschelBronze'], _('Bronze'), 
        "bronze", 
        _("Bronze Herschel 400 certificat! You observed 25 Herschel 400 objects!"), 
        _("Observe at least 25 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelSilver'], _('Silver'), 
        "silver", 
        _("Silver Herschel 400 certificat! You observed 50 Herschel 400 objects!"), 
        _("Observe at least 50 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelGold'], _('Gold'), 
        "gold", 
        _("Golden Herschel 400 certificat! You observed 100 Herschel 400 objects!"), 
        _("Observe at least 100 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelDiamond'], _('Diamond'), 
        "diamond", 
        _("Diamond Herschel 400 certificat! You observed 200 Herschel 400 objects!"), 
        _("Observe at least 200 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelPlatina'], _('Platinum'), 
        "platinum", 
        _("Platinum Herschel 400 certificat! You observed all 400 Herschel 400 objects!"), 
        _("Observe all 400 Herschel 400 objects to get this certificat!")
    );
    echo "</div>";

    // Herschel 400 drawings
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of Herschel 400 objects') . "</h4>";

    drawStar(
        $accomplishments['herschelDrawingsBronze'], _('Bronze'), 
        "bronze", 
        _("Bronze Herschel 400 drawing-certificat! You drew 25 Herschel 400 objects!"), 
        _("Draw at least 25 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelDrawingsSilver'], _('Silver'),
        "silver", 
        _("Silver Herschel 400 drawing-certificat! You drew 50 Herschel 400 objects!"), 
        _("Draw at least 50 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelDrawingsGold'], _('Gold'), 
        "gold", 
        _("Golden Herschel 400 drawing-certificat! You drew 100 Herschel 400 objects!"), 
        _("Draw at least 100 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelDrawingsDiamond'], _('Diamond'), 
        "diamond", 
        _("Diamond Herschel 400 drawing-certificat! You drew 200 Herschel 400 objects!"), 
        _("Draw at least 200 Herschel 400 objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelDrawingsPlatina'], _('Platinum'), 
        "platinum", 
        _("Platinum Herschel 400 drawing-certificat! You drew all 400 Herschel 400 objects!"), 
        _("Draw all 400 Herschel 400 objects to get this certificat!")
    );
    echo "</div>";

    // Herschel II
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Herschel II objects') . "</h4>";

    drawStar(
        $accomplishments['herschelIIBronze'], _('Bronze'), "bronze",
        _("Bronze Herschel II certificat! You observed 25 Herschel II objects!"), 
        _("Observe at least 25 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIISilver'], _('Silver'),
        "silver", 
        _("Silver Herschel II certificat! You observed 50 Herschel II objects!"), 
        _("Observe at least 50 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIGold'], _('Gold'), 
        "gold", 
        _("Golden Herschel II certificat! You observed 100 Herschel II objects!"), 
        _("Observe at least 100 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIDiamond'], _('Diamond'), 
        "diamond", 
        _("Diamond Herschel II certificat! You observed 200 Herschel II objects!"), 
        _("Observe at least 200 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIPlatina'], _('Platinum'), 
        "platinum", 
        _("Platinum Herschel II certificat! You observed all 400 Herschel II objects!"), 
        _("Observe all 400 Herschel II objects to get this certificat!")
    );
    echo "</div>";

    // Herschel II drawings
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of Herschel II objects') . "</h4>";

    drawStar(
        $accomplishments['herschelIIDrawingsBronze'], _('Bronze'), 
        "bronze", 
        _("Bronze Herschel II drawing-certificat! You drew 25 Herschel II objects!"), 
        _("Draw at least 25 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIDrawingsSilver'], _('Silver'), 
        "silver", 
        _("Silver Herschel II drawing-certificat! You drew 50 Herschel II objects!"), 
        _("Draw at least 50 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIDrawingsGold'], _('Gold'), 
        "gold", 
        _("Golden Herschel II drawing-certificat! You drew 100 Herschel II objects!"), 
        _("Draw at least 100 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIDrawingsDiamond'], _('Diamond'), 
        "diamond", 
        _("Diamond Herschel II drawing-certificat! You drew 200 Herschel II objects!"), 
        _("Draw at least 200 Herschel II objects to get this certificat!")
    );
    drawStar(
        $accomplishments['herschelIIDrawingsPlatina'], _('Platinum'), 
        "platinum", 
        _("Platinum Herschel II drawing-certificat! You drew all 400 Herschel II objects!"), 
        _("Draw all 400 Herschel II objects to get this certificat!")
    );
    echo "</div>";

    // Total number of drawings
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Total number of drawings') . "</h4>";

    drawStar(
        $accomplishments['drawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), $objUtil->getDrawToAccomplish(1) 
    );
    drawStar(
        $accomplishments['drawingsRookie'], 10, "rookie", 
        $objUtil->getDrawAccomplishment(10), $objUtil->getDrawToAccomplish(10) 
    );
    drawStar(
        $accomplishments['drawingsBeginner'], 25, "beginner", 
        $objUtil->getDrawAccomplishment(25), $objUtil->getDrawToAccomplish(25) 
    );
    drawStar(
        $accomplishments['drawingsTalented'], 50, "talented", 
        $objUtil->getDrawAccomplishment(50), $objUtil->getDrawToAccomplish(50) 
    );
    drawStar(
        $accomplishments['drawingsSkilled'], 100, "skilled", 
        $objUtil->getDrawAccomplishment(100), $objUtil->getDrawToAccomplish(100) 
    );
    drawStar(
        $accomplishments['drawingsIntermediate'], 250, "intermediate", 
        $objUtil->getDrawAccomplishment(250), $objUtil->getDrawToAccomplish(250) 
    );
    drawStar(
        $accomplishments['drawingsExperienced'], 500, "experienced", 
        $objUtil->getDrawAccomplishment(500), $objUtil->getDrawToAccomplish(500) 
    );
    drawStar(
        $accomplishments['drawingsAdvanced'], 1000, "advanced", 
        $objUtil->getDrawAccomplishment(1000), $objUtil->getDrawToAccomplish(1000) 
    );
    drawStar(
        $accomplishments['drawingsSenior'], 2500, "senior", 
        $objUtil->getDrawAccomplishment(2500), $objUtil->getDrawToAccomplish(2500) 
    );
    drawStar(
        $accomplishments['drawingsExpert'], 5000, "expert", 
        $objUtil->getDrawAccomplishment(5000), $objUtil->getDrawToAccomplish(5000)
    );

    echo "</div>";

    // Total number of open clusters
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Open clusters') . "</h4>";

    drawStar(
        $accomplishments['openClusterNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), $objUtil->getSeenToAccomplish(1)
    );
    drawStar(
        $accomplishments['openClusterRookie'], (int) (1700 / 500), "rookie", 
        $objUtil->getSeenAccomplishment(1700 / 500), 
        $objUtil->getSeenToAccomplish(1700 / 500)
    );
    drawStar(
        $accomplishments['openClusterBeginner'], (int) (1700 / 200), "beginner", 
        $objUtil->getSeenAccomplishment(1700 / 200), 
        $objUtil->getSeenToAccomplish(1700 / 200)
    );
    drawStar(
        $accomplishments['openClusterTalented'], (int) (1700 / 100), "talented", 
        $objUtil->getSeenAccomplishment(1700 / 100), 
        $objUtil->getSeenToAccomplish(1700 / 100)
    );
    drawStar(
        $accomplishments['openClusterSkilled'], (int) (1700 / 50), "skilled", 
        $objUtil->getSeenAccomplishment(1700 / 50), 
        $objUtil->getSeenToAccomplish(1700 / 50)
    );
    drawStar(
        $accomplishments['openClusterIntermediate'], 
        (int) (1700 / 20), "intermediate", 
        $objUtil->getSeenAccomplishment(1700 / 20), 
        $objUtil->getSeenToAccomplish(1700 / 20)
    );
    drawStar(
        $accomplishments['openClusterExperienced'], 
        (int) (1700 / 10), "experienced", 
        $objUtil->getSeenAccomplishment(1700 / 10), 
        $objUtil->getSeenToAccomplish(1700 / 10)
    );
    drawStar(
        $accomplishments['openClusterAdvanced'], (int) (1700 / 5), "advanced", 
        $objUtil->getSeenAccomplishment(1700 / 5), 
        $objUtil->getSeenToAccomplish(1700 / 5)
    );
    drawStar(
        $accomplishments['openClusterSenior'], (int) (1700 / 2), "senior", 
        $objUtil->getSeenAccomplishment(1700 / 2), 
        $objUtil->getSeenToAccomplish(1700 / 2)
    );
    drawStar(
        $accomplishments['openClusterExpert'], 1700, "expert", 
        $objUtil->getSeenAccomplishment(1700), $objUtil->getSeenToAccomplish(1700)
    );
    echo "</div>";

    // Total number of open clusters drawn
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of open clusters') . "</h4>";

    drawStar(
        $accomplishments['openClusterDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), $objUtil->getDrawToAccomplish(1) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsRookie'], 
        (int) (1700 / 500), "rookie", 
        $objUtil->getDrawAccomplishment(1700 / 500), 
        $objUtil->getDrawToAccomplish(1700 / 500) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsBeginner'], 
        (int) (1700 / 200), "beginner", 
        $objUtil->getDrawAccomplishment(1700 / 200), 
        $objUtil->getDrawToAccomplish(1700 / 200) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsTalented'], 
        (int) (1700 / 100), "talented", 
        $objUtil->getDrawAccomplishment(1700 / 100), 
        $objUtil->getDrawToAccomplish(1700 / 100) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsSkilled'], 
        (int) (1700 / 50), "skilled", 
        $objUtil->getDrawAccomplishment(1700 / 50), 
        $objUtil->getDrawToAccomplish(1700 / 50) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsIntermediate'], 
        (int) (1700 / 20), "intermediate", 
        $objUtil->getDrawAccomplishment(1700 / 20), 
        $objUtil->getDrawToAccomplish(1700 / 20) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsExperienced'], 
        (int) (1700 / 10), "experienced", 
        $objUtil->getDrawAccomplishment(1700 / 10), 
        $objUtil->getDrawToAccomplish(1700 / 10) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsAdvanced'], 
        (int) (1700 / 5), "advanced", 
        $objUtil->getDrawAccomplishment(1700 / 5), 
        $objUtil->getDrawToAccomplish(1700 / 5) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsSenior'], (int) (1700 / 2), "senior", 
        $objUtil->getDrawAccomplishment(1700 / 2), 
        $objUtil->getDrawToAccomplish(1700 / 2) 
    );
    drawStar(
        $accomplishments['openClusterDrawingsExpert'], 1700, "expert", 
        $objUtil->getDrawAccomplishment(1700), $objUtil->getDrawToAccomplish(1700) 
    );

    echo "</div>";

    // Total number of globular clusters
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Globular clusters') . "</h4>";

    drawStar( 
        $accomplishments['globularClusterNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), $objUtil->getSeenToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['globularClusterRookie'], 2, "rookie", 
        $objUtil->getSeenAccomplishment(2), $objUtil->getSeenToAccomplish(2) 
    );
    drawStar( 
        $accomplishments['globularClusterBeginner'], 3, "beginner", 
        $objUtil->getSeenAccomplishment(3), $objUtil->getSeenToAccomplish(3) 
    );
    drawStar( 
        $accomplishments['globularClusterTalented'], 4, "talented", 
        $objUtil->getSeenAccomplishment(4), $objUtil->getSeenToAccomplish(4) 
    );
    drawStar( 
        $accomplishments['globularClusterSkilled'], 5, "skilled", 
        $objUtil->getSeenAccomplishment(5), $objUtil->getSeenToAccomplish(5) 
    );
    drawStar( 
        $accomplishments['globularClusterIntermediate'], 
        (int) (152 / 20), "intermediate", 
        $objUtil->getSeenAccomplishment(152 / 20), 
        $objUtil->getSeenToAccomplish(152 / 20) 
    );
    drawStar( 
        $accomplishments['globularClusterExperienced'], 
        (int) (152 / 10), "experienced", 
        $objUtil->getSeenAccomplishment(152 / 10), 
        $objUtil->getSeenToAccomplish(152 / 10) 
    );
    drawStar( 
        $accomplishments['globularClusterAdvanced'], (int) (152 / 5), "advanced", 
        $objUtil->getSeenAccomplishment(152 / 5), 
        $objUtil->getSeenToAccomplish(152 / 5) 
    );
    drawStar( 
        $accomplishments['globularClusterSenior'], (int) (152 / 2), "senior", 
        $objUtil->getSeenAccomplishment(152 / 2), 
        $objUtil->getSeenToAccomplish(152 / 2) 
    );
    drawStar( 
        $accomplishments['globularClusterExpert'], 152, "expert", 
        $objUtil->getSeenAccomplishment(152), $objUtil->getSeenToAccomplish(152) 
    );
    echo "</div>";

    // Total number of globular clusters drawn
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of globular clusters') . "</h4>";

    drawStar( 
        $accomplishments['globularClusterDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), $objUtil->getDrawToAccomplish(1)
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsRookie'], 2, "rookie", 
        $objUtil->getDrawAccomplishment(2), $objUtil->getDrawToAccomplish(2) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsBeginner'], 3, "beginner", 
        $objUtil->getDrawAccomplishment(3), $objUtil->getDrawToAccomplish(3) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsTalented'], 4, "talented", 
        $objUtil->getDrawAccomplishment(4), $objUtil->getDrawToAccomplish(4) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsSkilled'], 5, "skilled", 
        $objUtil->getDrawAccomplishment(5), $objUtil->getDrawToAccomplish(5) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsIntermediate'], 
        (int) (152 / 20), "intermediate", 
        $objUtil->getDrawAccomplishment(152 / 20), 
        $objUtil->getDrawToAccomplish(152 / 20) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsExperienced'], 
        (int) (152 / 10), "experienced", 
        $objUtil->getDrawAccomplishment(152 / 10), 
        $objUtil->getDrawToAccomplish(152 / 10) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsAdvanced'], 
        (int) (152 / 5), "advanced", 
        $objUtil->getDrawAccomplishment(152 / 5), 
        $objUtil->getDrawToAccomplish(152 / 5) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsSenior'], 
        (int) (152 / 2), "senior", 
        $objUtil->getDrawAccomplishment(152 / 2), 
        $objUtil->getDrawToAccomplish(152 / 2) 
    );
    drawStar( 
        $accomplishments['globularClusterDrawingsExpert'], 
        152, "expert", 
        $objUtil->getDrawAccomplishment(152), 
        $objUtil->getDrawToAccomplish(152) 
    );

    echo "</div>";

    // Total number of planetary nebulae
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Planetary Nebulae') . "</h4>";

    drawStar( 
        $accomplishments['planetaryNebulaNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), 
        $objUtil->getSeenToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaRookie'], (int) (1023 / 500), "rookie", 
        $objUtil->getSeenAccomplishment(1023 / 500), 
        $objUtil->getSeenToAccomplish(1023 / 500) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaBeginner'], (int) (1023 / 200), "beginner", 
        $objUtil->getSeenAccomplishment(1023 / 200), 
        $objUtil->getSeenToAccomplish(1023 / 200) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaTalented'], (int) (1023 / 100), "talented", 
        $objUtil->getSeenAccomplishment(1023 / 100), 
        $objUtil->getSeenToAccomplish(1023 / 100) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaSkilled'], (int) (1023 / 50), "skilled", 
        $objUtil->getSeenAccomplishment(1023 / 50), 
        $objUtil->getSeenToAccomplish(1023 / 50) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaIntermediate'], (int) (1023 / 20), 
        "intermediate", 
        $objUtil->getSeenAccomplishment(1023 / 20), 
        $objUtil->getSeenToAccomplish(1023 / 20) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaExperienced'], (int) (1023 / 10), 
        "experienced", 
        $objUtil->getSeenAccomplishment(1023 / 10), 
        $objUtil->getSeenToAccomplish(1023 / 10) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaAdvanced'], (int) (1023 / 5), "advanced", 
        $objUtil->getSeenAccomplishment(1023 / 5), 
        $objUtil->getSeenToAccomplish(1023 / 5) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaSenior'], (int) (1023 / 2), "senior", 
        $objUtil->getSeenAccomplishment(1023 / 2), 
        $objUtil->getSeenToAccomplish(1023 / 2) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaExpert'], 1023, "expert", 
        $objUtil->getSeenAccomplishment(1023), 
        $objUtil->getSeenToAccomplish(1023) 
    );
    echo "</div>";

    // Total number of planetary nebulae drawn
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of planetary nebulae') . "</h4>";

    drawStar( 
        $accomplishments['planetaryNebulaDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), 
        $objUtil->getDrawToAccomplish(1)
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsRookie'], 
        (int) (1023 / 500), "rookie", 
        $objUtil->getDrawAccomplishment(1023 / 500), 
        $objUtil->getDrawToAccomplish(1023 / 500) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsBeginner'], 
        (int) (1023 / 200), "beginner", 
        $objUtil->getDrawAccomplishment(1023 / 200), 
        $objUtil->getDrawToAccomplish(1023 / 200) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsTalented'], 
        (int) (1023 / 100), "talented", 
        $objUtil->getDrawAccomplishment(1023 / 100), 
        $objUtil->getDrawToAccomplish(1023 / 100) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsSkilled'], 
        (int) (1023 / 50), "skilled", 
        $objUtil->getDrawAccomplishment(1023 / 50), 
        $objUtil->getDrawToAccomplish(1023 / 50) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsIntermediate'], 
        (int) (1023 / 20), "intermediate", 
        $objUtil->getDrawAccomplishment(1023 / 20), 
        $objUtil->getDrawToAccomplish(1023 / 20) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsExperienced'], 
        (int) (1023 / 10), "experienced", 
        $objUtil->getDrawAccomplishment(1023 / 10), 
        $objUtil->getDrawToAccomplish(1023 / 10) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsAdvanced'], 
        (int) (1023 / 5), "advanced", 
        $objUtil->getDrawAccomplishment(1023 / 5), 
        $objUtil->getDrawToAccomplish(1023 / 5) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsSenior'], 
        (int) (1023 / 2), "senior", 
        $objUtil->getDrawAccomplishment(1023 / 2), 
        $objUtil->getDrawToAccomplish(1023 / 2) 
    );
    drawStar( 
        $accomplishments['planetaryNebulaDrawingsExpert'], 1023, "expert", 
        $objUtil->getDrawAccomplishment(1023), 
        $objUtil->getDrawToAccomplish(1023) 
    );

    echo "</div>";

    // Total number of galaxies
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Galaxies') . "</h4>";

    drawStar( 
        $accomplishments['galaxyNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), $objUtil->getSeenToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['galaxyRookie'], 10, "rookie", 
        $objUtil->getSeenAccomplishment(10), $objUtil->getSeenToAccomplish(10) 
    );
    drawStar( 
        $accomplishments['galaxyBeginner'], 25, "beginner", 
        $objUtil->getSeenAccomplishment(25), $objUtil->getSeenToAccomplish(25) 
    );
    drawStar( 
        $accomplishments['galaxyTalented'], 50, "talented", 
        $objUtil->getSeenAccomplishment(50), $objUtil->getSeenToAccomplish(50) 
    );
    drawStar( 
        $accomplishments['galaxySkilled'], 100, "skilled", 
        $objUtil->getSeenAccomplishment(100), $objUtil->getSeenToAccomplish(100) 
    );
    drawStar( 
        $accomplishments['galaxyIntermediate'], 250, "intermediate", 
        $objUtil->getSeenAccomplishment(250), $objUtil->getSeenToAccomplish(250) 
    );
    drawStar( 
        $accomplishments['galaxyExperienced'], 500, "experienced", 
        $objUtil->getSeenAccomplishment(500), $objUtil->getSeenToAccomplish(500) 
    );
    drawStar( 
        $accomplishments['galaxyAdvanced'], 1000, "advanced", 
        $objUtil->getSeenAccomplishment(1000), $objUtil->getSeenToAccomplish(1000) 
    );
    drawStar( 
        $accomplishments['galaxySenior'], 2500, "senior", 
        $objUtil->getSeenAccomplishment(2500), $objUtil->getSeenToAccomplish(2500) 
    );
    drawStar( 
        $accomplishments['galaxyExpert'], 5000, "expert", 
        $objUtil->getSeenAccomplishment(5000), $objUtil->getSeenToAccomplish(5000) 
    );
    echo "</div>";

    // Total number of galaxies drawn
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of galaxies') . "</h4>";

    drawStar( 
        $accomplishments['galaxyDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), $objUtil->getDrawToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsRookie'], 10, "rookie", 
        $objUtil->getDrawAccomplishment(10), $objUtil->getDrawToAccomplish(10) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsBeginner'], 25, "beginner", 
        $objUtil->getDrawAccomplishment(25), $objUtil->getDrawToAccomplish(25) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsTalented'], 50, "talented", 
        $objUtil->getDrawAccomplishment(50), $objUtil->getDrawToAccomplish(50) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsSkilled'], 100, "skilled", 
        $objUtil->getDrawAccomplishment(100), $objUtil->getDrawToAccomplish(100) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsIntermediate'], 250, "intermediate", 
        $objUtil->getDrawAccomplishment(250), $objUtil->getDrawToAccomplish(250) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsExperienced'], 500, "experienced", 
        $objUtil->getDrawAccomplishment(500), $objUtil->getDrawToAccomplish(500) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsAdvanced'], 1000, "advanced", 
        $objUtil->getDrawAccomplishment(1000), $objUtil->getDrawToAccomplish(1000) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsSenior'], 2500, "senior", 
        $objUtil->getDrawAccomplishment(2500), $objUtil->getDrawToAccomplish(2500) 
    );
    drawStar( 
        $accomplishments['galaxyDrawingsExpert'], 5000, "expert", 
        $objUtil->getDrawAccomplishment(5000), $objUtil->getDrawToAccomplish(5000) 
    );

    echo "</div>";

    // Total number of nebulae
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Nebulae') . "</h4>";

    drawStar( 
        $accomplishments['nebulaNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), 
        $objUtil->getSeenToAccomplish(1)
    );
    drawStar( 
        $accomplishments['nebulaRookie'], 2, "rookie", 
        $objUtil->getSeenAccomplishment(2), 
        $objUtil->getSeenToAccomplish(2)
    );
    drawStar( 
        $accomplishments['nebulaBeginner'], 3, "beginner", 
        $objUtil->getSeenAccomplishment(3), 
        $objUtil->getSeenToAccomplish(3)
    );
    drawStar( 
        $accomplishments['nebulaTalented'], 4, "talented", 
        $objUtil->getSeenAccomplishment(4), 
        $objUtil->getSeenToAccomplish(4)
    );
    drawStar( 
        $accomplishments['nebulaSkilled'], (int) (384 / 50), "skilled", 
        $objUtil->getSeenAccomplishment(384 / 50), 
        $objUtil->getSeenToAccomplish(384 / 50) 
    );
    drawStar( 
        $accomplishments['nebulaIntermediate'], (int) (384 / 20), "intermediate", 
        $objUtil->getSeenAccomplishment(384 / 20), 
        $objUtil->getSeenToAccomplish(384 / 20) 
    );
    drawStar( 
        $accomplishments['nebulaExperienced'], (int) (384 / 10), "experienced", 
        $objUtil->getSeenAccomplishment(384 / 10), 
        $objUtil->getSeenToAccomplish(384 / 10) 
    );
    drawStar( 
        $accomplishments['nebulaAdvanced'], (int) (384 / 5), "advanced", 
        $objUtil->getSeenAccomplishment(384 / 5), 
        $objUtil->getSeenToAccomplish(384 / 5) 
    );
    drawStar( 
        $accomplishments['nebulaSenior'], (int) (384 / 2), "senior", 
        $objUtil->getSeenAccomplishment(384 / 2), 
        $objUtil->getSeenToAccomplish(384 / 2) 
    );
    drawStar( 
        $accomplishments['nebulaExpert'], 384, "expert", 
        $objUtil->getSeenAccomplishment(384), 
        $objUtil->getSeenToAccomplish(384) 
    );
    echo "</div>";

    // Total number of nebulae drawn
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of nebulae') . "</h4>";

    drawStar( 
        $accomplishments['nebulaDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), 
        $objUtil->getDrawToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsRookie'], 2, "rookie", 
        $objUtil->getDrawAccomplishment(2), 
        $objUtil->getDrawToAccomplish(2) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsBeginner'], 3, "beginner", 
        $objUtil->getDrawAccomplishment(3), 
        $objUtil->getDrawToAccomplish(3) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsTalented'], 4, "talented", 
        $objUtil->getDrawAccomplishment(4), 
        $objUtil->getDrawToAccomplish(4) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsSkilled'], (int) (384 / 50), "skilled", 
        $objUtil->getDrawAccomplishment(384 / 50), 
        $objUtil->getDrawToAccomplish(384 / 50) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsIntermediate'], 
        (int) (384 / 20), "intermediate", 
        $objUtil->getDrawAccomplishment(384 / 20), 
        $objUtil->getDrawToAccomplish(384 / 20) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsExperienced'], 
        (int) (384 / 10), "experienced", 
        $objUtil->getDrawAccomplishment(384 / 10), 
        $objUtil->getDrawToAccomplish(384 / 10) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsAdvanced'], (int) (384 / 5), "advanced", 
        $objUtil->getDrawAccomplishment(384 / 5), 
        $objUtil->getDrawToAccomplish(384 / 5) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsSenior'], (int) (384 / 2), "senior", 
        $objUtil->getDrawAccomplishment(384 / 2), 
        $objUtil->getDrawToAccomplish(384 / 2) 
    );
    drawStar( 
        $accomplishments['nebulaDrawingsExpert'], 384, "expert", 
        $objUtil->getDrawAccomplishment(384), 
        $objUtil->getDrawToAccomplish(384) 
    );

    echo "</div>";

    // Total number of different objects
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Different objects') . "</h4>";

    drawStar( 
        $accomplishments['objectsNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), $objUtil->getSeenToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['objectsRookie'], 10, "rookie", 
        $objUtil->getSeenAccomplishment(10), $objUtil->getSeenToAccomplish(10) 
    );
    drawStar( 
        $accomplishments['objectsBeginner'], 25, "beginner", 
        $objUtil->getSeenAccomplishment(25), $objUtil->getSeenToAccomplish(25) 
    );
    drawStar( 
        $accomplishments['objectsTalented'], 50, "talented", 
        $objUtil->getSeenAccomplishment(50), $objUtil->getSeenToAccomplish(50) 
    );
    drawStar( 
        $accomplishments['objectsSkilled'], 100, "skilled", 
        $objUtil->getSeenAccomplishment(100), $objUtil->getSeenToAccomplish(100) 
    );
    drawStar( 
        $accomplishments['objectsIntermediate'], 250, "intermediate", 
        $objUtil->getSeenAccomplishment(250), $objUtil->getSeenToAccomplish(250) 
    );
    drawStar( 
        $accomplishments['objectsExperienced'], 500, "experienced", 
        $objUtil->getSeenAccomplishment(500), $objUtil->getSeenToAccomplish(500) 
    );
    drawStar( 
        $accomplishments['objectsAdvanced'], 1000, "advanced", 
        $objUtil->getSeenAccomplishment(1000), $objUtil->getSeenToAccomplish(1000) 
    );
    drawStar( 
        $accomplishments['objectsSenior'], 2500, "senior", 
        $objUtil->getSeenAccomplishment(2500), $objUtil->getSeenToAccomplish(2500) 
    );
    drawStar( 
        $accomplishments['objectsExpert'], 5000, "expert", 
        $objUtil->getSeenAccomplishment(5000), $objUtil->getSeenToAccomplish(5000) 
    );
    echo "</div>";

    // Total number of nebulae drawn
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of different objects') . "</h4>";

    drawStar( 
        $accomplishments['objectsDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), $objUtil->getDrawToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsRookie'], 10, "rookie", 
        $objUtil->getDrawAccomplishment(10), $objUtil->getDrawToAccomplish(10) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsBeginner'], 25, "beginner", 
        $objUtil->getDrawAccomplishment(25), $objUtil->getDrawToAccomplish(25) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsTalented'], 50, "talented", 
        $objUtil->getDrawAccomplishment(50), $objUtil->getDrawToAccomplish(50) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsSkilled'], 100, "skilled", 
        $objUtil->getDrawAccomplishment(100), $objUtil->getDrawToAccomplish(100) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsIntermediate'], 250, "intermediate", 
        $objUtil->getDrawAccomplishment(250), $objUtil->getDrawToAccomplish(250) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsExperienced'], 500, "experienced", 
        $objUtil->getDrawAccomplishment(500), $objUtil->getDrawToAccomplish(500) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsAdvanced'], 1000, "advanced", 
        $objUtil->getDrawAccomplishment(1000), $objUtil->getDrawToAccomplish(1000) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsSenior'], 2500, "senior", 
        $objUtil->getDrawAccomplishment(2500), $objUtil->getDrawToAccomplish(2500) 
    );
    drawStar( 
        $accomplishments['objectsDrawingsExpert'], 5000, "expert", 
        $objUtil->getDrawAccomplishment(5000), $objUtil->getDrawToAccomplish(5000) 
    );

    echo "</div>";

    // Total number of comet observations
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Total comet observations') . "</h4>";

    drawStar( 
        $accomplishments['cometObservationsNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), $objUtil->getSeenToAccomplish(1)
    );
    drawStar( 
        $accomplishments['cometObservationsRookie'], 10, "rookie", 
        $objUtil->getSeenAccomplishment(10), $objUtil->getSeenToAccomplish(10)
    );
    drawStar( 
        $accomplishments['cometObservationsBeginner'], 25, "beginner", 
        $objUtil->getSeenAccomplishment(25), $objUtil->getSeenToAccomplish(25)
    );
    drawStar( 
        $accomplishments['cometObservationsTalented'], 50, "talented", 
        $objUtil->getSeenAccomplishment(50), $objUtil->getSeenToAccomplish(50)
    );
    drawStar( 
        $accomplishments['cometObservationsSkilled'], 100, "skilled", 
        $objUtil->getSeenAccomplishment(100), $objUtil->getSeenToAccomplish(100)
    );
    drawStar( 
        $accomplishments['cometObservationsIntermediate'], 250, "intermediate", 
        $objUtil->getSeenAccomplishment(250), $objUtil->getSeenToAccomplish(250)
    );
    drawStar( 
        $accomplishments['cometObservationsExperienced'], 500, "experienced", 
        $objUtil->getSeenAccomplishment(500), $objUtil->getSeenToAccomplish(500)
    );
    drawStar( 
        $accomplishments['cometObservationsAdvanced'], 1000, "advanced", 
        $objUtil->getSeenAccomplishment(1000), $objUtil->getSeenToAccomplish(1000)
    );
    drawStar( 
        $accomplishments['cometObservationsSenior'], 2500, "senior", 
        $objUtil->getSeenAccomplishment(2500), $objUtil->getSeenToAccomplish(2500)
    );
    drawStar( 
        $accomplishments['cometObservationsExpert'], 5000, "expert", 
        $objUtil->getSeenAccomplishment(5000), $objUtil->getSeenToAccomplish(5000)
    );
    echo "</div>";

    // Total number of different comets seen
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Different comets') . "</h4>";

    drawStar( 
        $accomplishments['cometsObservedNewbie'], 1, "newbie", 
        $objUtil->getSeenAccomplishment(1), $objUtil->getSeenToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['cometsObservedRookie'], 10, "rookie", 
        $objUtil->getSeenAccomplishment(10), $objUtil->getSeenToAccomplish(10) 
    );
    drawStar( 
        $accomplishments['cometsObservedBeginner'], 25, "beginner", 
        $objUtil->getSeenAccomplishment(25), $objUtil->getSeenToAccomplish(25) 
    );
    drawStar( 
        $accomplishments['cometsObservedTalented'], 50, "talented", 
        $objUtil->getSeenAccomplishment(50), $objUtil->getSeenToAccomplish(50) 
    );
    drawStar( 
        $accomplishments['cometsObservedSkilled'], 100, "skilled", 
        $objUtil->getSeenAccomplishment(100), $objUtil->getSeenToAccomplish(100) 
    );
    drawStar( 
        $accomplishments['cometsObservedIntermediate'], 250, "intermediate", 
        $objUtil->getSeenAccomplishment(250), $objUtil->getSeenToAccomplish(250) 
    );
    drawStar( 
        $accomplishments['cometsObservedExperienced'], 500, "experienced", 
        $objUtil->getSeenAccomplishment(500), $objUtil->getSeenToAccomplish(500) 
    );
    drawStar( 
        $accomplishments['cometsObservedAdvanced'], 1000, "advanced", 
        $objUtil->getSeenAccomplishment(1000), $objUtil->getSeenToAccomplish(1000) 
    );
    drawStar( 
        $accomplishments['cometsObservedSenior'], 2500, "senior", 
        $objUtil->getSeenAccomplishment(2500), $objUtil->getSeenToAccomplish(2500) 
    );
    drawStar( 
        $accomplishments['cometsObservedExpert'], 5000, "expert", 
        $objUtil->getSeenAccomplishment(5000), $objUtil->getSeenToAccomplish(5000) 
    );

    echo "</div>";

    // Total number of different comet drawings
    echo "<div class=\"accomplishmentRow\">";
    echo "<h4>" . _('Drawings of comets') . "</h4>";

    drawStar( 
        $accomplishments['cometDrawingsNewbie'], 1, "newbie", 
        $objUtil->getDrawAccomplishment(1), $objUtil->getDrawToAccomplish(1) 
    );
    drawStar( 
        $accomplishments['cometDrawingsRookie'], 10, "rookie", 
        $objUtil->getDrawAccomplishment(10), $objUtil->getDrawToAccomplish(10) 
    );
    drawStar( 
        $accomplishments['cometDrawingsBeginner'], 25, "beginner", 
        $objUtil->getDrawAccomplishment(25), $objUtil->getDrawToAccomplish(25) 
    );
    drawStar( 
        $accomplishments['cometDrawingsTalented'], 50, "talented", 
        $objUtil->getDrawAccomplishment(50), $objUtil->getDrawToAccomplish(50) 
    );
    drawStar( 
        $accomplishments['cometDrawingsSkilled'], 100, "skilled", 
        $objUtil->getDrawAccomplishment(100), $objUtil->getDrawToAccomplish(100) 
    );
    drawStar( 
        $accomplishments['cometDrawingsIntermediate'], 250, "intermediate", 
        $objUtil->getDrawAccomplishment(250), $objUtil->getDrawToAccomplish(250) 
    );
    drawStar( 
        $accomplishments['cometDrawingsExperienced'], 500, "experienced", 
        $objUtil->getDrawAccomplishment(500), $objUtil->getDrawToAccomplish(500) 
    );
    drawStar( 
        $accomplishments['cometDrawingsAdvanced'], 1000, "advanced", 
        $objUtil->getDrawAccomplishment(1000), $objUtil->getDrawToAccomplish(1000) 
    );
    drawStar( 
        $accomplishments['cometDrawingsSenior'], 2500, "senior", 
        $objUtil->getDrawAccomplishment(2500), $objUtil->getDrawToAccomplish(2500) 
    );
    drawStar( 
        $accomplishments['cometDrawingsExpert'], 5000, "expert", 
        $objUtil->getDrawAccomplishment(5000), $objUtil->getDrawToAccomplish(5000) 
    );

    echo "</div>";

    echo "</div>";
    echo "<br />";

    echo "</div>";
    echo "</div>";
}

/** 
 * Draws a star on the info page.
 * 
 * @param bool   $done        true if the star has be earned.
 * @param string $text        The text to print together with the star.
 * @param string $color       The color (defined in css) for the star.
 * @param string $tooltip     The tooltip describing the number of objects observed.
 * @param string $tooltipToDo The tooltip describing the number of objects still to 
 *                            observe.
 * 
 * @return None
 */
function drawStar($done, $text, $color, $tooltip, $tooltipToDo) 
{
    global $baseURL;

    if ($done) {
        print "<div class=\"star\" id=\"" . $color . "\">";
        print "<div class=\"accomplishmentText\" title=\"" . $tooltip . "\">" 
            . ucfirst($text) . "</div>";
        print "</div>";
    } else {
        print "<div class=\"star notAccomplished\" id=\"" . $color . "\">";
        print "<div class=\"accomplishmentText notAccomplished\" title=\"" 
            . $tooltipToDo . "\">" . ucfirst($text) . "</div>";
        print "</div>";
    }
}

?>
