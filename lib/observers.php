<?php
/**
 * The observers class collects all functions needed to enter,
 * retrieve and adapt observer data from the database and functions
 * to display the data.
 *
 * PHP Version 7
 *
 * @category Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}

/**
 * The observers class collects all functions needed to enter,
 * retrieve and adapt observer data from the database and functions
 * to display the data.
 *
 * PHP Version 7
 *
 * @category Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
class Observers
{
    /**
     * Adds a new observer to the database.
     * The new observer will not be able to log in yet.
     * Before being able to do so, the administrator must validate
     * the new user.
     *
     * @param string $id        The id of the new user.
     * @param string $name      The last name of the observer.
     * @param string $firstname The first name of the observer.
     * @param string $email     The mail address of the observer.
     * @param string $password  The md5(...) encoded password.
     *
     * @return string The return string of the SQL command.
     */
    public function addObserver($id, $name, $firstname, $email, $password)
    {
        global $objDatabase, $objAccomplishments;
        $toReturn = $objDatabase->execSQL(
            "INSERT INTO observers (id, name, firstname, email, password, role, language)"
            . " VALUES (\"$id\", \"$name\", \"$firstname\", \"$email\","
            . " \"$password\","
            . " \"" . ROLEWAITLIST . "\", \"" . $_SESSION['lang'] . "\")"
        );
        $objAccomplishments->addObserver($id);
        return $toReturn;
    }
    /**
     * Returns the user id if the mail is given.
     *
     * @param string $mail The mail address of the observer.
     *
     * @return string The user id that belongs to the mail address.
     */
    public function getUserIdFromEmail($mail)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT id FROM observers WHERE email = \"" . $mail . "\"", 'id'
        );
    }
    /**
     * Returns an array with all administrators.
     *
     * @return array A list with all administrators.
     */
    public function getAdministrators()
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray(
            "SELECT id FROM observers WHERE role = \"ROLEADMIN\"", 'id'
        );
    }
    /**
     * Returns the rank of the given observer in comet observations.
     *
     * @param string $observer The observerid
     *
     * @return string The rank of the observer in comet observations.
     */
    public function getCometRank($observer)
    {
        global $objCometObservation;
        return array_search(
            $observer, $objCometObservation->getPopularObservers()
        );
    }
    /**
     * Returns the rank of the given observer in deepsky observations.
     *
     * @param string $observer The observerid
     *
     * @return string The rank of the observer in deepsky observations.
     */
    public function getDsRank($observer)
    {
        global $objObservation;
        return array_search(
            $observer, $objObservation->getPopularObservers()
        );
    }
    /**
     * Returns the last version of DeepskyLog the observer has used.
     *
     * @param string $observer The observerid
     *
     * @return string The last version of DeepskyLog used.
     */
    public function getLastVersion($observer)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT version FROM observers WHERE id=\"" . $observer . "\"",
            'version', '5.0.0'
        );
    }
    /**
     * Returns the id of the last observation the observer has seen.
     *
     * @param string $observerid The observerid
     *
     * @return integer The id of the last read observation.
     */
    public function getLastReadObservation($observerid)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT lastReadObservationId FROM observers WHERE id=\""
            . $observerid . "\"", 'lastReadObservationId', 0
        );
    }
    /**
     * Returns a list of all StandardInstruments of all observers.
     *
     * @return Array A list of all standard instruments of all observers.
     */
    public function getListOfInstruments()
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray(
            "SELECT stdtelescope FROM observers GROUP BY stdtelescope",
            'stdtelescope'
        );
    }
    /**
     * Returns a list of all StandardLocations of all observers.
     *
     * @return Array A list of all standard locations of all observers.
     */
    public function getListOfLocations()
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray(
            "SELECT stdlocation FROM observers GROUP BY stdlocation", 'stdlocation'
        );
    }
    /**
     * Returns the number of comet observations for the given observerid.
     *
     * @param string $observerid The observerid
     *
     * @return integer The number of comet observations.
     */
    public function getNumberOfCometObservations($observerid)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT COUNT(cometobservations.id) As Cnt FROM cometobservations "
            . ($observerid ? "WHERE observerid = \"" . $observerid . "\"" : ""),
            'Cnt', 0
        );
    }
    /**
     * Returns the number of comet drawings for the given observerid.
     *
     * @param string $observerid The observerid
     *
     * @return integer The number of drawings observations.
     */
    public function getNumberOfCometDrawings($observerid)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT COUNT(cometobservations.id) As Cnt FROM cometobservations"
            . " WHERE hasDrawing=1"
            . ($observerid ? " AND observerid = \"" . $observerid . "\"" : ""),
            'Cnt', 0
        );
    }
    /**
     * Returns the number of deepsky observations for the given observerid.
     *
     * @param string $observerid The observerid
     *
     * @return integer The number of deepsky observations.
     */
    public function getNumberOfDsObservations($observerid)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT COUNT(observations.id) As Cnt FROM observations "
            . ($observerid ? "WHERE observerid = \"" . $observerid . "\"" : ""),
            'Cnt', 0
        );
    }
    /**
     * Returns the number of deepsky drawings for the given observerid.
     *
     * @param string $observerid The observerid
     *
     * @return integer The number of deepsky drawings.
     */
    public function getNumberOfDsDrawings($observerid)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT COUNT(observations.id) As Cnt FROM observations"
            . " WHERE hasDrawing=1"
            . ($observerid ? " AND observerid = \"" . $observerid . "\"" : ""),
            'Cnt', 0
        );
    }
    /**
     * Returns the requested property from the observer.
     *
     * @param string $id           The observer id.
     * @param string $property     The requested property.
     * @param string $defaultValue A default value if the requested property
     *                             is not known.
     *
     * @return The value of the requested property.
     */
    public function getObserverProperty($id, $property, $defaultValue = '')
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT " . $property . " FROM observers WHERE id=\"" . $id . "\"",
            $property, $defaultValue
        );
    }
    /**
     * Returns the requested property from the observer, in utf8
     *
     * @param string $id           The observer id.
     * @param string $property     The requested property.
     * @param string $defaultValue A default value if the requested property
     *                             is not known.
     *
     * @return The value of the requested property.
     */
    public function getObserverPropertyCS($id, $property, $defaultValue = '')
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT " . $property
            . " FROM observers WHERE id COLLATE utf8_bin =\"" . $id . "\"",
            $property, $defaultValue
        );
    }
    /**
     * Returns an array with the ids(key) and names(value) of all active observers,
     * sorted by name
     *
     * @return Array Ids and names of all active observers.
     */
    public function getPopularObserversByName()
    {
        global $objDatabase;
        return $objDatabase->selectKeyValueArray(
            "SELECT DISTINCT observers.id, "
            . "CONCAT(observers.firstname,' ',observers.name) "
            . "As observername, observers.name FROM observers "
            . "JOIN observations ON (observers.id = observations.observerid) "
            . "ORDER BY observers.name", 'id', 'observername'
        );
    }
    /**
     * Returns an array with the ids of all observers,
     * sorted by the column specified in $sort.
     *
     * @param string $sort The parameter to sort on.
     *
     * @return Array The array with the sorted observers.
     */
    public function getSortedObservers($sort)
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray(
            "SELECT observers.id FROM observers ORDER BY $sort", 'id'
        );
    }
    /**
     * Returns an array with the ids of all observers with a lot
     * of information for the administrators,
     * sorted by the column specified in $sort
     *
     * @param string $sort The parameter to sort on.
     *
     * @return Array The array with the sorted observers.
     */
    public function getSortedObserversAdmin($sort)
    {
        global $objDatabase;
        return $objDatabase->selectRecordsetArray(
            "SELECT observers.*, B.instrumentCount, C.listCount, "
            . "D.obsCount, E.cometobsCount, "
            . "(IFNULL(B.instrumentCount,0) + IFNULL(C.listCount,0) "
            . "+ IFNULL(D.obsCount,0) + IFNULL(E.cometobsCount,0)) AS maxMax "
            . "FROM observers "
            . "LEFT JOIN (SELECT instruments.observer, COUNT(instruments.id) "
            . "AS instrumentCount FROM instruments GROUP BY instruments.observer) "
            . "AS B ON observers.id=B.observer "
            . "LEFT JOIN (SELECT observerobjectlist.observerid, "
            . "COUNT(DISTINCT observerobjectlist.listname) AS listCount "
            . "FROM observerobjectlist GROUP BY observerobjectlist.observerid) "
            . "AS C on observers.id=C.observerid "
            . "LEFT JOIN (SELECT observations.observerid, COUNT(observations.id) "
            . "AS obsCount FROM observations GROUP BY observations.observerid) "
            . "AS D on observers.id=D.observerid "
            . "LEFT JOIN (SELECT cometobservations.observerid, "
            . "COUNT(cometobservations.id) AS cometobsCount FROM cometobservations "
            . "GROUP BY cometobservations.observerid) "
            . "AS E on observers.id=E.observerid "
            . "GROUP BY observers.id ORDER BY " . $sort
        );
    }
    /**
     * Returns a list of all languages an observer is interested in.
     *
     * @param string $id The observerid
     *
     * @return Array All languages the observer is interested in.
     */
    public function getUsedLanguages($id)
    {
        global $objDatabase;
        return unserialize(
            $objDatabase->selectSingleValue(
                "SELECT usedLanguages FROM observers WHERE id = \"$id\"",
                'usedLanguages', ''
            )
        );
    }
    /**
     * Mark all observations as read.
     *
     * @return None
     */
    public function markAllAsRead()
    {
        global $objDatabase, $loggedUser;
        if ($loggedUser) {
            $objDatabase->execSQL(
                "UPDATE observers SET lastReadObservationId="
                . $objDatabase->selectSingleValue(
                    "SELECT MAX(id) AS MaxID FROM observations", 'MaxID', 0
                ) . " WHERE id=\"" . $loggedUser . "\""
            );
        }
    }
    /**
     * Mark all observations till the given observationid as read.
     *
     * @param integer $themark The observationid of the last read observation.
     *
     * @return None
     */
    public function markAsRead($themark)
    {
        global $objDatabase, $loggedUser;
        if ($loggedUser) {
            $objDatabase->execSQL(
                "UPDATE observers SET lastReadObservationId="
                . $themark . " WHERE id=\"" . $loggedUser . "\""
            );
        }
        unset($_SESSION['Qobs']);
    }
    /**
     * Sets a new value for the given property of the observer.
     *
     * @param string $id            The observerid.
     * @param string $property      The property to set.
     * @param string $propertyValue The new value for the given property.
     *
     * @return None
     */
    public function setObserverProperty($id, $property, $propertyValue)
    {
        global $objDatabase;
        $objDatabase->execSQL(
            "UPDATE observers SET " . $property . "=\""
            . $propertyValue . "\" WHERE id=\"" . $id . "\""
        );
    }
    /**
     * Sets all the used languages for the observer.
     *
     * @param string $id       The observerid.
     * @param string $language The languages to set.
     *
     * @return None
     */
    private function _setUsedLanguages($id, $language)
    {
        global $objDatabase;
        $objDatabase->execSQL(
            "UPDATE observers SET usedLanguages = '"
            . serialize($language) . "' WHERE id=\"$id\""
        );
    }
    /**
     * Returns the full name of the observer.
     *
     * @param string $id The observerid.
     *
     * @return string The full name.
     */
    public function getFullName($id)
    {
        global $objDatabase;
        $names = $objDatabase->selectRecordsetArray(
            "SELECT firstname, name FROM observers WHERE id = \""
            . $id . "\""
        );
        $name = $names[0];
        return $name["firstname"] . " " . $name["name"];
    }
    /**
     * Shows a page with the top observers.
     *
     * @param string $catalog The catalog to sort on.
     * @param string $rank    The rank to use.
     *
     * @return string The page with the top observers.
     */
    public function showTopObservers($catalog, $rank)
    {
        global $baseURL, $objObservation, $objUtil, $objObserver;
        global $objObject, $DSOcatalogsLists;
        $outputtable = "";
        if ($catalog != "") {
            if (!strcmp($catalog, "-----------")) {
                echo "<div>"
                    . "<table class=\"table sort-table table-condensed "
                    . "table-striped table-hover tablesorter custom-popup\">";
                $catalog = "M";
            } else {
                echo "<div><table data-sortlist=\"[[6,1]]\" "
                . "class=\"table sort-table table-condensed table-striped "
                . "table-hover tablesorter custom-popup\">";
            }
        } else {
            echo "<div><table class=\"table sort-table table-condensed "
                . "table-striped table-hover tablesorter custom-popup\">";
            $catalog = "M";
        }

        $objectsInCatalog = $objObject->getNumberOfObjectsInCatalog($catalog);

        echo "<thead>";
        echo "<tr>";
        echo "<th>" . _("Rank") . "</th>";
        echo "<th>" . _("Observer") . "</th>";
        echo "<th>" . _("Number of observations") . "</th>";
        echo "<th>" . _("Number of drawings") . "</th>";
        echo "<th>" . _("Observations last year") . "</th>";
        echo "<th>" . _("Drawings last year") . "</th>";
        echo "<th class=\"filter-false columnSelector-disable\">";
        echo "<select class=\"form-control\" "
            . "onchange=\"location = this.options[this.selectedIndex].value;\" "
            . "name=\"catalog\">";
        foreach ($DSOcatalogsLists as $key=>$value) {
            if (!($value)) {
                $value = "-----------";
            }
            if ($value == stripslashes($catalog)) {
                echo "<option selected=\"selected\" value=\"" . $baseURL
                    . "index.php?indexAction=rank_observers&amp;catalog="
                    . urlencode($value) . "\">" . $value . "</option>";
            } else {
                echo "<option value=\"" . $baseURL
                    . "index.php?indexAction=rank_observers&amp;catalog="
                    . urlencode($value) . "\">" . $value . "</option>";
            }
        }
        echo "</select>";
        echo "</th>";
        echo "<th>" . _("Different objects") . "</td>";
        echo "</tr>";
        $numberOfObservations = $objObservation->getNumberOfDsObservations();
        $numberOfDrawings = $objObservation->getNumberOfDsDrawings();
        $numberOfObservationsThisYear
            = $objObservation->getObservationsLastYear('%');
        $numberOfDrawingsThisYear = $objObservation->getDrawingsLastYear('%');
        $numberOfDifferentObjects
            = $objObservation->getNumberOfDifferentObservedDSObjects();
        echo "</thead>";
        echo "<tfoot>";
        echo "<tr><td>" . _("Total") . "</td><td></td>"
            . "<td class=\"centered\">$numberOfObservations</td>"
            . "<td class=\"centered\">$numberOfDrawings</td>"
            . "<td class=\"centered\">$numberOfObservationsThisYear</td>"
            . "<td class=\"centered\">$numberOfDrawingsThisYear</td>"
            . "<td class=\"centered\">" . $objectsInCatalog . "</td>"
            . "<td class=\"centered\">" . $numberOfDifferentObjects . "</td></tr>";
        echo "</tfoot>";
        echo "<tbody id=\"topobs_list\" class=\"tbody_obs\">";
        $count = 0;
        // We get the full list of observers and observations from sql,
        // don't loop over the observers and do a mysql query always!
        $allDrawings = $objObservation->getDsDrawingsCount();
        $allObservationsLastYear
            = $objObservation->getAllObservationsLastYearCount();
        $allDrawingsLastYear = $objObservation->getAllDrawingsLastYearCount();
        $allObjects = $objObservation->getNumberOfObjectsCount();
        $allObjectsCount
            = $objObservation->getAllObservedCountFromCatalogOrList($catalog);

        foreach ($rank as $value) {
            $outputtable .= "<tr>";
            $outputtable .= "<td>" . ($count + 1)
                . "</td><td> <a href=\"" . $baseURL
                . "index.php?indexAction=detail_observer&amp;user="
                . urlencode($value["observerid"]) . "\">"
                . $value["observername"] . "</a> </td>";
            $outputtable .= "<td>" . $value["Cnt"] . "&nbsp;&nbsp;&nbsp;&nbsp;("
                . sprintf("%.2f", (($value["Cnt"] / $numberOfObservations) * 100))
                . "%)</td>";
            if (array_key_exists($value["observerid"], $allDrawings)) {
                $value2 = $allDrawings [ $value["observerid"] ];
            } else {
                $value2 = 0;
            }
            $outputtable .= "<td> $value2 &nbsp;&nbsp;&nbsp;&nbsp;("
                . sprintf("%.2f", (($value2 / $numberOfDrawings) * 100)) . "%)</td>";

            if (array_key_exists($value["observerid"], $allObservationsLastYear)) {
                $observationsThisYear
                    = $allObservationsLastYear[$value["observerid"]];
            } else {
                $observationsThisYear = 0;
            }
            if ($numberOfObservationsThisYear != 0) {
                $percentObservations = ($observationsThisYear
                    / $numberOfObservationsThisYear) * 100;
            } else {
                $percentObservations = 0;
            }
            $outputtable .= "<td>" . $observationsThisYear
                . "&nbsp;&nbsp;&nbsp;&nbsp;("
                . sprintf("%.2f", $percentObservations) . "%)</td>";

            if (array_key_exists($value["observerid"], $allDrawingsLastYear)) {
                $drawingsThisYear = $allDrawingsLastYear[$value["observerid"]];
            } else {
                $drawingsThisYear = 0;
            }
            if ($numberOfDrawingsThisYear != 0) {
                $percentDrawings = ($drawingsThisYear
                    / $numberOfDrawingsThisYear) * 100;
            } else {
                $percentDrawings = 0;
            }
            $outputtable .= "<td>" . $drawingsThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;("
                . sprintf("%.2f", $percentDrawings) . "%)</td>";

            if (array_key_exists($value["observerid"], $allObjectsCount)) {
                $objectsCount = $allObjectsCount[ $value["observerid"]];
            } else {
                $objectsCount = 0;
            }
            $outputtable .= "<td> <a href=\"" . $baseURL
                . "index.php?indexAction=view_observer_catalog&amp;catalog="
                . urlencode($catalog) . "&amp;user="
                . urlencode($value["observerid"]) . "\">" . $objectsCount . "</a> ("
                . sprintf("%.2f", (($objectsCount / $objectsInCatalog) * 100))
                . "%)</td>";

            if (array_key_exists($value["observerid"], $allObjects)) {
                $numberOfObjects = $allObjects [ $value["observerid"] ];
            } else {
                $numberOfObjects = 0;
            }
            $outputtable .= "<td>" . $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;("
                . sprintf(
                    "%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100)
                ) . "%)</td>";
            $outputtable .= "</tr>";
            $count ++;
        }
        $outputtable .= "</tbody>";
        $outputtable .= "</table>";
        echo $outputtable;

        $objUtil->addPager("", $count);

        echo "</div><hr />";
    }
    /**
     * Validates the account.
     *
     * @return None
     */
    public function valideAccount()
    {
        global $entryMessage, $objUtil, $objLanguage, $objMessages;
        global $developversion, $loggedUser, $allLanguages, $mailTo;
        global $mailFrom, $objMessages, $baseURL, $instDir;

        if (!$_POST['email'] || !$_POST['firstname'] || !$_POST['name']) {
            $entryMessage .= _("Please, fill in all fields!");
            if ($objUtil->checkPostKey('change')) {
                $_GET['indexAction'] = 'change_account';
            } else {
                if (!$_POST['passwd'] || !$_POST['passwd_again']) {
                    $_GET['indexAction'] = 'subscribe';
                }
            }
        } elseif (!$objUtil->checkPostKey('change')
            && ($_POST['passwd'] != $_POST['passwd_again'])
        ) {
            $entryMessage .= _("Password not confirmed!");
            $_GET ['indexAction'] = 'subscribe';
        } elseif ($_POST ['firstname'] == $_POST ['name']) {
            $entryMessage .= _("Your name and / or first name are not correct.");
            if ($objUtil->checkPostKey('change')) {
                $_GET['indexAction'] = 'change_account';
            } else {
                $_GET['indexAction'] = 'subscribe';
            }
        } elseif (array_key_exists('motivation', $_POST)
            && $_POST['motivation'] == '' && !$loggedUser
        ) {
            $entryMessage .= _("The field 'Motivation' is not filled in.");
            if ($objUtil->checkPostKey('change')) {
                $_GET['indexAction'] = 'change_account';
            } else {
                $_GET['indexAction'] = 'subscribe';
            }
        } elseif (!preg_match("/.*@.*..*/", $_POST['email']) | preg_match("/(<|>)/", $_POST['email'])) {
            // check if email address is legal (contains @ symbol)
            $entryMessage .= _("Wrong email address!");
            if ($objUtil->checkPostKey('change')) {
                $_GET ['indexAction'] = 'change_account';
            } else {
                $_GET ['indexAction'] = 'subscribe';
            }
        } elseif (array_key_exists('register', $_POST)
            && array_key_exists('deepskylog_id', $_POST)
            && $_POST['register'] && $_POST['deepskylog_id']
        ) {
            // user doesn't exist yet
            if ($this->getObserverProperty($_POST ['deepskylog_id'], 'name')) {
                $entryMessage .= _("There is already someone with this account name, please choose another one!");
                if ($objUtil->checkPostKey('change')) {
                    $_GET['indexAction'] = 'change_account';
                } else {
                    $_GET['indexAction'] = 'subscribe';
                }
            } else {
                $this->addObserver(
                    $_POST['deepskylog_id'], $_POST['name'], $_POST['firstname'],
                    $_POST['email'], md5($_POST['passwd'])
                );
                // READ ALL THE LANGUAGES FROM THE CHECKBOXES
                $allLanguages = $objLanguage->getAllLanguages($_SESSION['lang']);
                foreach ($allLanguages as $key=>$value) {
                    if (array_key_exists($key, $_POST)) {
                        $usedLanguages[] = $key;
                    }
                }
                $this->_setUsedLanguages(
                    $_POST['deepskylog_id'], $usedLanguages
                );
                $this->setObserverProperty(
                    $_POST['deepskylog_id'], 'copyright', $this->getPostedLicense()
                );
                $this->setObserverProperty(
                    $_POST['deepskylog_id'], 'observationlanguage',
                    $_POST['description_language']
                );
                $this->setObserverProperty(
                    $_POST['deepskylog_id'], 'language', $_POST['language']
                );
                $this->setObserverProperty(
                    $_POST['deepskylog_id'], 'registrationDate', date("Ymd H:i")
                );
                $body = _("Details deepskylog account") . ": <br /><br />" .                 // send mail to administrator
                                "<table><tr><td><strong>" . _("Account name")
                    . "</strong></td><td>" . $_POST['deepskylog_id'] . "</td></tr>"
                    . "<tr><td><strong>" . _("Email") . "</strong></td><td>"
                    . $_POST['email'] . "</td></tr>"
                    . "<tr><td><strong>" . _("Name") . "</strong></td><td>"
                    . html_entity_decode($_POST['firstname']) . " "
                    . html_entity_decode($_POST['name']) . "</td></tr>"
                    . "<tr><td><strong>" . _("Motivation") . "</strong></td><td>"
                    . html_entity_decode($_POST['motivation'])
                    . "</td></tr></table><br />"
                    . _("This email has automatically been sent by the DeepskyLog application")
                    . "<br /><br />";

                if (isset($developversion) && ($developversion == true)) {
                    $entryMessage .= "On the live server, a mail would be sent with the subject: "
                        . _("DeepskyLog - registration") . ".<p>";
                } else {
                    $objMessages->sendEmail(
                        _("DeepskyLog - registration"), $body, "developers"
                    );
                }
                $entryMessage = _(
                    "Your DeepskyLog account has been created. One of our developers will validate your account as soon as possible. You will receive an email confirmation when this happens.
                Please remember that DeepskyLog is the work of only a very small group of volunteers, and that it can take up to a day or so to get validated.
                On very rare occasions, all developers are on an astronomical observing session for a week. Normally, there is a backup person in these periods.
                If your account is not validated within 24 hours, you can send an email to developers at deepskylog.be to be sure."
                );
                $_GET['user'] = $_POST['deepskylog_id'];
                $_GET['indexAction'] = 'detail_observer';
            }
        } elseif ($objUtil->checkPostKey('change')) {
            // pressed change button
            if (! $loggedUser) {
                // extra control on login
                $entryMessage .= _("Please, fill in all fields!");
                $_GET['indexAction'] = 'change_account';
            } else {
                $usedLanguages = array();
                foreach ($allLanguages as $key=>$value) {
                    if (array_key_exists($key, $_POST)) {
                        $usedLanguages[] = $key;
                    }
                }
                $this->_setUsedLanguages($loggedUser, $usedLanguages);
                $this->setObserverProperty($loggedUser, 'name', $_POST['name']);
                $this->setObserverProperty(
                    $loggedUser, 'firstname', $_POST['firstname']
                );
                $this->setObserverProperty($loggedUser, 'email', $_POST['email']);
                $this->setObserverProperty(
                    $loggedUser, 'language', $_POST['language']
                );
                $this->setObserverProperty(
                    $loggedUser, 'observationlanguage', $_POST['description_language']
                );
                $this->setObserverProperty(
                    $loggedUser, 'stdlocation', $_POST['site']
                );
                $this->setObserverProperty(
                    $loggedUser, 'stdtelescope', $_POST['instrument']
                );
                $this->setObserverProperty(
                    $loggedUser, 'standardAtlasCode', $_POST['atlas']
                );
                $this->setObserverProperty(
                    $loggedUser, 'showInches', $_POST['showInches']
                );
                $this->setObserverProperty(
                    $loggedUser, 'fstOffset', $_POST['fstOffset']
                );
                $this->setObserverProperty(
                    $loggedUser, 'overviewFoV', $_POST['overviewFoV']
                );
                $this->setObserverProperty(
                    $loggedUser, 'lookupFoV', $_POST['lookupFoV']
                );
                $this->setObserverProperty(
                    $loggedUser, 'detailFoV', $_POST['detailFoV']
                );
                $this->setObserverProperty(
                    $loggedUser, 'overviewdsos', $_POST['overviewdsos']
                );
                $this->setObserverProperty(
                    $loggedUser, 'lookupdsos', $_POST['lookupdsos']
                );
                $this->setObserverProperty(
                    $loggedUser, 'detaildsos', $_POST['detaildsos']
                );
                $this->setObserverProperty(
                    $loggedUser, 'overviewstars', $_POST['overviewstars']
                );
                $this->setObserverProperty(
                    $loggedUser, 'lookupstars', $_POST['lookupstars']
                );
                $this->setObserverProperty(
                    $loggedUser, 'detailstars', $_POST['detailstars']
                );
                $this->setObserverProperty(
                    $loggedUser, 'atlaspagefont', $_POST['atlaspagefont']
                );
                $this->setObserverProperty(
                    $loggedUser, 'photosize1', $_POST['photosize1']
                );
                $this->setObserverProperty(
                    $loggedUser, 'photosize2', $_POST['photosize2']
                );
                $this->setObserverProperty(
                    $loggedUser, 'copyright', $this->getPostedLicense()
                );
                $this->setObserverProperty(
                    $loggedUser, 'UT',
                    ((array_key_exists('local_time', $_POST)
                    && ($_POST['local_time'] == "on")) ? "0" : "1")
                );
                $this->setObserverProperty(
                    $loggedUser, 'sendMail',
                    ((array_key_exists('send_mail', $_POST)
                    && ($_POST['send_mail'] == "on")) ? "1" : "0")
                );
                if ($_POST ['icq_name'] != "") {
                    $this->setObserverProperty(
                        $loggedUser, 'icqname', $_POST['icq_name']
                    );
                }
                $_SESSION['lang'] = $_POST['language'];
                if ($_FILES['image']['tmp_name'] != "") {
                    if ($_POST['oldFile'] != '') {
                        unlink($_POST['oldFile']);
                    }
                    $upload_dir = 'common/observer_pics';
                    $dir = opendir($upload_dir);
                    // resize code
                    include_once $instDir . "common/control/resize.php";
                    $original_image = $_FILES['image']['tmp_name'];
                    $destination_image = $upload_dir . "/" . $loggedUser . ".jpg";
                    $new_image = image_createThumb(
                        $original_image, $destination_image, 300, 300, 75
                    );
                }

                $entryMessage .= _("Your account has been successfully updated!");
                $_GET ['user'] = $loggedUser;
                $_GET ['indexAction'] = 'change_account';
            }
        }
    }
    /**
     * Returns the text string for the license the given observer has selected.
     * In case of one of the Creative Commons licenses, a picture and a link to the
     * license is returned.
     *
     * @param string $observerid The observer for which the license should
     *                           be retrieved.
     *
     * @return string The text for the license.
     */
    public function getCopyright($observerid)
    {
        $text = $this->getObserverProperty($observerid, 'copyright');

        if (strcmp($text, "Attribution-NoDerivs CC BY-ND") == 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nd/4.0/">Creative Commons Attribution-NoDerivatives 4.0 International License</a>.';
        } else if (strcmp($text, "Attribution CC BY") == 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Attribution 4.0 International License</a>.';
        } else if (strcmp($text, "Attribution-ShareAlike CC BY-SA") == 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.';
        } else if (strcmp($text, "Attribution-NonCommercial CC BY-NC") == 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/4.0/">Creative Commons Attribution-NonCommercial 4.0 International License</a>.';
        } else if (strcmp($text, "Attribution-NonCommercial-ShareAlike CC BY-NC-SA") == 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/4.0/">Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License</a>.';
        } else if (strcmp($text, "Attribution-NonCommercial-NoDerivs CC BY-NC-ND") == 0) {
            $copyright = '<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/4.0/">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.';
        } else {
            $copyright = $text;
        }

        return $copyright;
    }
    /**
     * Returns the text string that is posted using the form to change the
     * settings of the observer or to register. The returned string is one of the
     * Creative Common strings, empty or the copyright message the observer has
     * written himself.
     *
     * @return string The text for the license.
     */
    public function getPostedLicense()
    {
        switch ($_POST['cclicense']) {
        case 0:
            $license = 'Attribution CC BY';
            break;
        case 1:
            $license = 'Attribution-ShareAlike CC BY-SA';
            break;
        case 2:
            $license = 'Attribution-NoDerivs CC BY-ND';
            break;
        case 3:
            $license = 'Attribution-NonCommercial CC BY-NC';
            break;
        case 4:
            $license = 'Attribution-NonCommercial-ShareAlike CC BY-NC-SA';
            break;
        case 5:
            $license = 'Attribution-NonCommercial-NoDerivs CC BY-NC-ND';
            break;
        case 6:
            $license = '';
            break;
        case 7:
            $license = $_POST['copyright'];
            break;
        }
        return $license;
    }
    /**
     * Deletes the user.
     *
     * @return string A message the the user was deleted.
     */
    public function validateDeleteObserver()
    {
        global $objDatabase, $objUtil, $entryMessage, $loggedUser;
        global $developversion, $mailTo, $mailFrom, $objMessages, $objObserver, $objAccomplishments;

        if (!($objUtil->checkSessionKey('admin') == 'yes')) {
            throw new Exception(
                _("You need to be logged in as an administrator to execute these operations.")
            );
        }
        $objDatabase->execSQL(
            "DELETE FROM observers WHERE id=\""
            . ($id = $objUtil->checkGetKey('validateDelete')) . "\""
        );
        $id = html_entity_decode($id, ENT_QUOTES, "UTF-8");
        if (isset($developversion) && ($developversion == 1)) {
            $entryMessage .= "On the live server, a mail would be sent with the subject: Deepskylog account deleted.<br />";
        } else {
            $objMessages->sendEmail(
                "Deepskylog account deleted",
                "The account for " . $id . " was deleted by "
                . $objObserver->getFullName($loggedUser) . "<br /><br />",
                "developers"
            );
        }
        $objAccomplishments->deleteObserver($id);
        return "The user has been erased.";
    }
    /**
     * Validates the user with the given id and gives the user the given role.
     *
     * @return string A message that the user is validated.
     */
    public function validateObserver()
    {
        global $objDatabase, $objUtil, $entryMessage, $developversion;
        global $mailTo, $mailFrom, $objMessages, $objAccomplishments;
        if (! ($objUtil->checkSessionKey('admin') == 'yes')) {
            throw new Exception(
                _("You need to be logged in as an administrator to execute these operations.")
            );
        }
        $objDatabase->execSQL(
            "UPDATE observers SET role = \"" . ($role = ROLEUSER)
            . "\" WHERE id=\"" . ($id = $objUtil->checkGetKey('validate')) . "\""
        );
        if ($role == ROLEADMIN) {
            $ad = "<br /><br />"
                . _("One of the administrators made you a new administrator.");
        } else {
            $ad = "";
        }

        $body = sprintf(
            _(
                "Dear %s, <br /><br />Your application for a DeepskyLog account is approved."
            ),
            html_entity_decode($this->getObserverProperty($id, 'firstname')) . ' ' .
            html_entity_decode($this->getObserverProperty($id, 'name'))
        ) . "<br /><br />"
        . sprintf(
            _("You can now log in using your userid %s and password."),
            "<strong>" . $id . "</strong>"
        ) . sprintf(
            _("Read the %sPrivacy Policy%s"),
            "<a href='http://www.deepskylog.org/index.php?indexAction=privacy'>", "</a>"
        ) . "<br /><br /> " . $ad
        . _("Enjoy using <a href=\"http://www.deepskylog.org/\">DeepskyLog</a>. Greetings,")
        . "<br /><br />" . _("The DeepskyLog Team") . "<br /><br />";

        if (isset($developversion) && ($developversion == 1)) {
            $entryMessage .= "On the live server, a mail would be sent with the subject: "
                . _("DeepskyLog - account application approved") . ".<br />";
        } else {
            $objMessages->sendEmail(_("DeepskyLog - account application approved"), $body, $id, true);
        }

        // After registration, all old messages are removed
        $objMessages->removeAllMessages($id);
        // After registration, a welcome message is sent
        $objMessages->sendMessage(
            "DeepskyLog",
            $id,
            sprintf(
                _('Welcome in DeepskyLog, %s!'),
                $this->getObserverProperty($id, 'firstname')
            ),
            sprintf(
                _('Welcome in DeepskyLog, %s!') ."<br /><br />",
                $this->getObserverProperty($id, 'firstname')
            )
            . _('We hope you will have a lot of fun using DeepskyLog. You can already find some interesting links to get you started :')
            . '<br /><br />'
            . "<a href=\"http://www.deepskylog.org/index.php?indexAction=add_instrument\">"
            . _('Add an instrument') . '</a><br /><br />'
            . "<a href=\"http://www.deepskylog.org/index.php?indexAction=add_location\">"
            . _('Add an observing site') . '</a><br />'
            . _('After entering a typical limiting magnitude or a typical SQM-value, DeepskyLog will calculate visibility of all objects! Do not forget to select a standard observation site!')
            . '<br /><br />'
            . "<a href=\"http://www.deepskylog.org/index.php?indexAction=change_account\">"
            . _('Set your standard observing atlas and set a picture of yourself.')
            . '</a><br /><br />'
            . _('A lot of fun using DeepskyLog!') . '<br /><br />'
            . _('The DeepskyLog developers')
        );

        return _("The user has successfully been updated!")
            . ' <br />' . _("User updated.");
    }
    /**
     * Updates the password for the given user.
     *
     * @param string $login              The observerid.
     * @param string $passwd             The current password.
     * @param string $newPassword        The new password.
     * @param string $confirmNewPassword The confirmation of the new password.
     *
     * @return None
     */
    public function updatePassword(
        $login, $passwd, $newPassword, $confirmNewPassword
    ) {
        global $entryMessage, $loggedUser;
        $passwd_db = $this->getObserverPropertyCS($login, "password");

        if (strcmp($login, $loggedUser) == 0) {
            // We check if we can change the password
            if (strcmp($passwd_db, $passwd) == 0) {
                if (strcmp($newPassword, $confirmNewPassword) != 0) {
                    $entryMessage = _("The new password and the confirmed password are not the same. Unable to change the password.");
                } else {
                    $this->setObserverProperty(
                        $loggedUser, 'password', $newPassword
                    );

                    $entryMessage = _("The password is successfully changed.");

                    // Make sure we are still logged in.
                    session_regenerate_id(true);
                    $cookietime = time() + (365 * 24 * 60 * 60); // 1 year
                    setcookie(
                        "deepskylogsec", $newPassword . $login,
                        $cookietime, "/", "", false
                    );

                    $_GET['user'] = $loggedUser;
                }
            } else {
                // Current password is not correct, show an error message
                $entryMessage = _("The current password you entered is incorrect. Unable to change the password.");
            }
        }
        // Return to the change account page.
        $_GET['indexAction'] = 'change_account';
    }
    /**
     * Updates the password of the observer using a token.
     *
     * @param string $login              The observerid.
     * @param string $newPassword        The new password.
     * @param string $confirmNewPassword The confirmation of the new password.
     *
     * @return None
     */
    public function updatePasswordToken($login, $newPassword, $confirmNewPassword)
    {
        global $entryMessage, $loggedUser;
        $passwd_db = $this->getObserverPropertyCS($login, "password");

        // We check if we can change the password
        if (strcmp($newPassword, $confirmNewPassword) != 0) {
            $entryMessage = _("The new password and the confirmed password are not the same. Unable to change the password.");
        } else {
            $this->setObserverProperty($login, 'password', $newPassword);

            $entryMessage = _("The password is successfully changed.");
        }
        // Return to the change account page.
        $_GET['indexAction'] = 'main';
    }
    /**
     * Request a new password. A mail will be send to the observer which
     * includes a token to change the password.
     *
     * @return None
     */
    public function requestNewPassword()
    {
        global $entryMessage, $objUtil, $mailFrom, $baseURL;
        global $instDir, $objMessages;

        // First check if we are indeed using the correct indexAction
        if (strcmp($objUtil->checkPostKey('indexAction'), "requestPassword") == 0) {
            // Check for the userid or the mail address
            $userid = $objUtil->checkPostKey('deepskylog_id');
            $email = $objUtil->checkPostKey('mail');

            if ($userid != "") {
                // Check if the userid exists in the database, if this is not the case,
                // show a message that the userid is not known by DeepskyLog.
                $email = $this->getObserverProperty($userid, 'email');

                // If mail is empty, show message that the userid is not correct.
                if (strcmp($email, "") == 0) {
                    $entryMessage = sprintf(
                        _("The username %s is not known by DeepskyLog. Impossible to request a new password."),
                        "<strong>" . $userid . "</strong>"
                    );
                    return;
                }
            } elseif ($email != "") {
                // We have a mail address, but no username. Get the userid which belongs
                // to the mailaddress.
                $userid = $this->getUserIdFromEmail($email);

                if (strcmp($userid, "") == 0) {
                    $entryMessage = sprintf(
                        _("The mail address %s is not known by DeepskyLog. Impossible to request a new password."),
                        "<strong>" . $email . "</strong>"
                    );
                    return;
                }
            } else {
                $entryMessage = _("The given username and mail address are not known by DeepskyLog. Impossible to request a new password.");
                return;
            }

            $token = bin2hex(openssl_random_pseudo_bytes(10));

            include_once $instDir . "/lib/password.php";
            $pass = new Password();
            $pass->storeToken($userid, $token);

            $confirmLink = $baseURL . "index.php?indexAction=changeToken&amp;t="
                . $token;
            $cancelLink = $baseURL . "index.php?indexAction=removeToken&amp;t="
                . $token;

            // Send nice looking mail
            $subject = _("DeepskyLog Change Password Request");
            $message = "\n"
                . sprintf(
                    _(
                        "You have (or someone impersonating you has) requested to change your %s password.
                    <br />To complete the change, visit the following link:"
                    ),
                    "<a href=\"" . $baseURL . "\">DeepskyLog</a>"
                ) . "<br /><br />";
            $message .= "<a href=\"" . $confirmLink . "\">" . $confirmLink . "</a>";
            $message .= "<br /><br />"
                . _("If you are not the person who made this request, or you wish to cancel this request, visit the following link:")
                . "<br /><br />";
            $message .= "<a href=\"" . $cancelLink . "\">" . $cancelLink . "</a>";

            // Get correct date (in all languages)
            include_once $instDir . "/lib/setup/language.php";
            // Get the date in the correct locale
            $lang = new Language();
            $lang->setLocale();

            $message .= "<br /><br />"
                . sprintf(
                    _("If you do nothing, the request will lapse after 24 hours (on %s) or when you log in successfully."),
                    iconv('ISO-8859-1', 'UTF-8', strftime('%A %d %B %Y, %R UTC', time() + 24*60*60))
                );

            $message .= "<br /><h2><a href=\"mailto:deepskylog@groups.io\">"
                . _("The DeepskyLog team") . "</a></h2>";

            // Send the mail
            $objMessages->sendEmail($subject, $message, $userid);

            // Show message
            // Show which username and which email we use for requesting the new password
            $entryMessage = sprintf(
                _("A token for changing the password of %s has been emailed to %s. Follow the instructions in that email to change your password."),
                "<strong>" . $userid . "</strong>",
                "<strong>" . $email . "</strong>"
            );
        }
    }
}
?>
