<?php
/**
 * The location class collects all functions needed to enter, retrieve and adapt
 * location data from the database.
 *
 * PHP Version 7
 *
 * @category Utilities/Common
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
 * The location class collects all functions needed to enter, retrieve and adapt
 * location data from the database.
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
class Locations
{
    /**
     * Adds a new location to the database. The name, longitude, latitude and
     * country should be given as parameters.
     *
     * @param string $name      The name of the new location.
     * @param float  $longitude The longitude of the new location.
     * @param float  $latitude  The latitude of the new location.
     * @param string $country   The country of the new location.
     * @param string $timezone  The timezone of the new location.
     * @param float  $elevation The elevation of the new location.
     *
     * @return integer The id of the new location
     */
    public function addLocation(
        $name,
        $longitude,
        $latitude,
        $country,
        $timezone,
        $elevation
    ) {
        global $objDatabase;
        $objDatabase->execSQL(
            "INSERT INTO locations ("
            . "name, longitude, latitude, country, timezone, elevation, checked"
            . ") VALUES (\"$name\", \"$longitude\", \"$latitude\", "
            . "\"$country\", \"$timezone\", \"$elevation\", 1)"
        );
        return $objDatabase->selectSingleValue(
            "SELECT id FROM locations ORDER BY id DESC LIMIT 1",
            'id'
        );
    }
    /**
     * Returns a list with all id's which have the same name as the name
     * of the given id.
     *
     * @param integer $id The id of the location.
     *
     * @return array An array with all the ids with the same name as
     *               the location with the given id.
     */
    public function getAllLocationsIds($id)
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray(
            "SELECT id FROM locations WHERE name = \""
            . $objDatabase->selectSingleValue(
                "SELECT name FROM locations WHERE id = \"" . $id . "\"",
                'name'
            ) . "\"",
            'id'
        );
    }

    /**
     * Returns the id for this location.
     *
     * @param string $name     The name of the location.
     * @param string $observer The observer for the location.
     *
     * @return integer The id for the location.
     */
    public function getLocationId($name, $observer)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT id FROM locations where name=\""
            . ($name) . "\" and observer=\"" . $observer . "\"",
            'id',
            -1
        );
    }

    /**
     * Returns a property from the id of this location.
     *
     * @param integer $id           The id of the location.
     * @param string  $property     The property we are interested in.
     * @param string  $defaultValue The default value to return.
     *
     * @return string The value of the property.
     */
    public function getLocationPropertyFromId($id, $property, $defaultValue = '')
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT " . $property . " FROM locations WHERE id = \""
            . $id . "\"",
            $property,
            $defaultValue
        );
    }

    /**
     * Returns an array with all locations.
     *
     * @return array An array with all locations.
     */
    public function getLocations()
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray("SELECT id FROM locations", 'id');
    }

    /**
     * Returns the number of times the location is used in observations.
     *
     * @param integer $id The id of the location.
     *
     * @return integer The number of locations for an observation.
     */
    public function getLocationUsedFromId($id)
    {
        global $objDatabase;
        return $objDatabase->selectSingleValue(
            "SELECT count(id) as ObsCnt FROM observations WHERE locationid=\""
            . $id . "\"",
            'ObsCnt',
            0
        ) + $objDatabase->selectSingleValue(
            "SELECT count(id) as ObsCnt FROM cometobservations WHERE locationid=\""
            . $id . "\"",
            'ObsCnt',
            0
        );
    }
    /**
     * Returns an array with the ids of all locations, sorted by the
     * column specified in $sort.
     *
     * @param string $sort     The column to sort on.
     * @param string $observer The observer for which the locations
     *                         should be returned.
     * @param bool   $active   True if only the active locations
     *                         should be returned.
     *
     * @return array The ids of all locations, sorted by $sort.
     */
    public function getSortedLocations($sort, $observer = "", $active = '')
    {
        global $objDatabase;
        return $objDatabase->selectSingleArray(
            "SELECT " . ($observer ? "" : "MAX(id)") . " id, name FROM locations "
            . ($observer ? "WHERE observer LIKE \"" . $observer . "\" "
            . ($active ? " AND locationactive=" . $active : "") : " GROUP BY name")
            . " ORDER BY " . $sort . ", name",
            'id'
        );
    }

    /**
     * Returns an array with the ids of all locations,
     * sorted by the column specified in $sort.
     *
     * @param string $sort     The column to sort on.
     * @param string $observer The observer for which the locations
     *                         should be returned.
     * @param bool   $active   True if only the active locations
     *                         should be returned.
     *
     * @return array The ids of all locations, sorted by $sort as a list.
     */
    public function getSortedLocationsList($sort, $observer = "", $active = '')
    {
        global $objDatabase;
        $new_sites = array();
        $sites = $objDatabase->selectRecordsetArray(
            "SELECT id, name FROM locations "
            . ($observer ? "WHERE observer LIKE \"" . $observer . "\" "
            . ($active ? " AND locationactive=" . $active : "") : " GROUP BY name")
            . " ORDER BY " . $sort . ",name",
            'id'
        );
        $previous = "fdgsdg";
        for ($i = 0; $i < count($sites); $i++) {
            $adapt[$i] = 0;
            if ($sites[$i]['name'] == $previous) {
                $adapt[$i] = 1;
                $adapt[$i - 1] = 1;
            }
            $previous = $sites[$i]['name'];
        }
        for ($i = 0; $i < count($sites); $i++) {
            $new_sites[$i][0] = $sites[$i]['id'];
            $new_sites[$i][1] = $sites[$i]['name'];
        }
        return $new_sites;
    }
    /**
     * Sets the property to the specified value for the given location.
     *
     * @param integer $id            The id of the location.
     * @param string  $property      The property to set.
     * @param string  $propertyValue The new value for the property.
     *
     * @return string The return value of the database operation.
     */
    public function setLocationProperty($id, $property, $propertyValue)
    {
        global $objDatabase;
        return $objDatabase->execSQL(
            "UPDATE locations SET " . $property . " = \""
            . $propertyValue . "\" WHERE id = \"" . $id . "\""
        );
    }

    /**
     * Returns a list with locations that are not checked.
     *
     * @param string $observer The observer for which the not checked locations
     *                         should be returned.
     *
     * @return array An array with the not checked locations for the given observer.
     */
    public function getNotcheckedLocations($observer)
    {
        global $objDatabase;
        $sites = $objDatabase->selectRecordsetArray(
            "SELECT id FROM locations where observer = \""
            . $observer . "\" AND checked=\"0\"",
            'id'
        );

        return $sites;
    }

    /**
     * Show the locations for the logged in observer.
     *
     * @return None
     */
    public function showLocationsObserver()
    {
        global $baseURL, $loggedUser, $objObserver, $objUtil, $objLocation;
        global $objPresentations, $loggedUserName, $objContrast, $locationid, $sites;
        if ($sites != null) {
            // First check if there are not checked locations
            $locationsToCheck = $objLocation->getNotCheckedLocations($loggedUser);
            if (sizeof($locationsToCheck) > 0) {
                foreach ($locationsToCheck as $location) {
                    // We adapt the timezone, elevation and country
                    $latitude = $objLocation->getLocationPropertyFromId(
                        $location ['id'],
                        "latitude"
                    );
                    $longitude = $objLocation->getLocationPropertyFromId(
                        $location ['id'],
                        "longitude"
                    );

                    $url = "https://maps.googleapis.com/maps/"
                        . "api/timezone/json"
                        . "?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&location="
                        . $latitude . "," . $longitude . "&timestamp=0";
                    $json = file_get_contents($url);
                    $obj = json_decode($json);
                    if ($obj->status == "OK") {
                        $objLocation->setLocationProperty(
                            $location['id'],
                            "timezone",
                            $obj->timeZoneId
                        );

                        // Get the elevation
                        $url = "https://maps.googleapis.com/maps/"
                            . "api/elevation/json"
                            . "?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE"
                            . "&locations="
                            . $latitude . "," . $longitude;
                        $json = file_get_contents($url);
                        $obj = json_decode($json);
                        if ($obj->status == "OK") {
                            $results = $obj->results[0];
                            $objLocation->setLocationProperty(
                                $location['id'],
                                "elevation",
                                ((int) $results->elevation)
                            );

                            // Get the country
                            $url = "https://maps.googleapis.com/maps/"
                                . "api/geocode/json"
                                . "?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE"
                                . "&latlng="
                                . $latitude . "," . $longitude
                                . "&language=en";
                            $json = file_get_contents($url);
                            $obj = json_decode($json);
                            if ($obj->status == "OK") {
                                $results = $obj->results[0];
                                $components = $results->address_components;
                                for ($ac = 0; $ac < sizeof($components); $ac++) {
                                    if ($components [$ac]->types [0] == "country") {
                                        $objLocation->setLocationProperty(
                                            $location['id'],
                                            "country",
                                            $components[$ac]->long_name
                                        );
                                        $objLocation->setLocationProperty(
                                            $location['id'],
                                            "checked",
                                            1
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            echo "<form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
            echo "<input type=\"hidden\" name=\"indexAction\""
                . " value=\"validate_site\" />";
            echo "<input type=\"hidden\" name=\"adaptStandardLocation\""
                . " value=\"1\" />";

            // Add the button to select which columns to show
            $objUtil->addTableColumSelector();

            echo "<table class=\"table sort-table table-condensed table-striped"
                . " table-hover tablesorter custom-popup\">";
            echo "<thead><tr>";
            echo "<th>" . _("Active") . "</td>";

            echo "<th data-priority=\"critical\">"
                . _("Location") . "</th>";
            echo "<th>" . _("Weather forecast") . "</th>";
            echo "<th>" . _("Country") . "</th>";
            echo "<th>" . _("Elevation") . "</th>";
            echo "<th>" . _("NELM") . "</th>";
            echo "<th>" . _("SQM") . "</th>";
            echo "<th>" . _("Bortle Scale") . "</th>";
            echo "<th class=\"filter-false columnSelector-disable\""
                . " data-sorter=\"false\">"
                . _("Std location") . "</th>";
            echo "<th>" . _("Delete") . "</th>";
            echo "<th>" . _("Number of observations") . "</th>";
            echo "</tr></thead>";
            $count = 0;
            foreach ($sites as $key => $value) {
                $sitename = stripslashes(
                    $objLocation->getLocationPropertyFromId($value, 'name')
                );
                $country = $objLocation->getLocationPropertyFromId(
                    $value,
                    'country'
                );
                $long = $objLocation->getLocationPropertyFromId($value, 'longitude');
                if ($long > 0) {
                    $longitude = "&nbsp;"
                        . $objPresentations->decToString(
                            $objLocation->getLocationPropertyFromId(
                                $value,
                                'longitude'
                            )
                        );
                } else {
                    $longitude = $objPresentations->decToString(
                        $objLocation->getLocationPropertyFromId(
                            $value,
                            'longitude'
                        )
                    );
                }
                $lat = $objLocation->getLocationPropertyFromId($value, 'latitude');
                if ($lat > 0) {
                    $latitude = "&nbsp;"
                        . $objPresentations->decToString(
                            $objLocation->getLocationPropertyFromId(
                                $value,
                                'latitude'
                            )
                        );
                } else {
                    $latitude = $objPresentations->decToString(
                        $objLocation->getLocationPropertyFromId(
                            $value,
                            'latitude'
                        )
                    );
                }
                $elevation = $objLocation->getLocationPropertyFromId(
                    $value,
                    'elevation'
                );
                $timezone = $objLocation->getLocationPropertyFromId(
                    $value,
                    'timezone'
                );
                $observer = $objLocation->getLocationPropertyFromId(
                    $value,
                    'observer'
                );
                $limmag = $objLocation->getLocationPropertyFromId(
                    $value,
                    'limitingMagnitude'
                );
                $sb = $objLocation->getLocationPropertyFromId(
                    $value,
                    'skyBackground'
                );
                if (($limmag < -900) && ($sb > 0)) {
                    $limmag = sprintf(
                        "%.1f",
                        $objContrast->calculateLimitingMagnitudeFromSkyBackground(
                            $sb
                        )
                    );
                } elseif (($limmag < -900) && ($sb < -900)) {
                    $limmag = "&nbsp;";
                    $sb = "&nbsp;";
                } else {
                    $sb = sprintf(
                        "%.1f",
                        $objContrast->calculateSkyBackgroundFromLimitingMagnitude(
                            $limmag
                        )
                    );
                }
                if ($sb > 0) {
                    $bortle = $objContrast->calculateBortleFromSQM($sb);
                }
                if ($value != "1") {
                    echo "<tr>";

                    echo "<td>" . "<span class=\"hidden\">"
                        . $objLocation->getLocationPropertyFromId(
                            $value,
                            'locationactive'
                        ) . "</span><input id=\"locationactive" . $value
                        . "\" type=\"checkbox\" "
                        . ($objLocation->getLocationPropertyFromId(
                            $value,
                            'locationactive'
                        ) ? " checked=\"checked\" " : "")
                        . " onclick=\"setactivation('location'," . $value
                        . ");var order = this.checked ? '1' : '0';"
                        . " $(this).prev().html(order);"
                        . "$(this).parents('table').trigger('update');\" />"
                        . "</td>";
                    echo "<td><a href=\"" . $baseURL
                        . "index.php?indexAction=adapt_site&amp;location="
                        . urlencode($value) . "\">" . $sitename . "</a></td>";
                    echo "<td><a href=\"http://clearoutside.com/forecast/"
                        . round(
                            $objLocation->getLocationPropertyFromId(
                                $value,
                                'latitude'
                            ),
                            2
                        ) . "/"
                        . round(
                            $objLocation->getLocationPropertyFromId(
                                $value,
                                'longitude'
                            ),
                            2
                        ) . "\">"
                        . "<img src=\"http://clearoutside.com/forecast_image_small/"
                        . round(
                            $objLocation->getLocationPropertyFromId(
                                $value,
                                'latitude'
                            ),
                            2
                        ) . "/"
                        . round(
                            $objLocation->getLocationPropertyFromId(
                                $value,
                                'longitude'
                            ),
                            2
                        ) . "/forecast.png\" /></a></td>";
                    echo "<td>" . $country . "</td>";
                    echo "<td>" . $elevation . "m</td>";
                    echo "<td>" . $limmag . "</td>";
                    echo "<td>" . $sb . "</td>";
                    echo "<td>" . $bortle . "</td>";
                    echo "<td><input type=\"radio\" name=\"stdlocation\" value=\""
                        . $value
                        . "\""
                        . ($value == $objObserver->getObserverProperty(
                            $loggedUser,
                            'stdlocation'
                        )
                        ? " checked=\"checked\" " : "") . " onclick=\"submit();\""
                        . " />&nbsp;<br /></td>";
                    // Make it possible to delete the lenses
                    echo "<td>";
                    if (!($obsCnt = $objLocation->getLocationUsedFromId($value))) {
                        echo "<a href=\"" . $baseURL
                            . "index.php?indexAction=validate_delete_location"
                            . "&amp;locationid="
                            . urlencode($value)
                            . "\"><span class=\"glyphicon glyphicon-trash\" "
                            . "aria-hidden=\"true\"></span></a>";
                    }
                    echo "</td>";
                    // Show the number of observations for this location.
                    echo "<td>";
                    echo "<a href=\"" . $baseURL
                        . "index.php?indexAction="
                        . "result_selected_observations&amp;observer="
                        . $loggedUser . "&amp;site=" . $value
                        . "&amp;exactinstrumentlocation=true\">";
                    if ($obsCnt != 1) {
                        echo $obsCnt . ' ' . _("observations") . "</a>";
                    } else {
                        echo $obsCnt . ' ' . _("observation") . "</a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                $count++;
            }
            echo "</table>";

            $objUtil->addPager("", $count);

            echo "</div></form>";
        }
    }

    /**
     * Validates and deletes a location (set in the locationid Get keyword)
     *
     * @return string The message that the location was deleted.
     */
    public function validateDeleteLocation()
    {
        global $loggedUser, $objUtil, $objDatabase, $objObserver;
        if (($locationid = $objUtil->checkGetKey('locationid'))
            && $objUtil->checkAdminOrUserID(
                $this->getLocationPropertyFromId($locationid, 'observer')
            )
            && (!($this->getLocationUsedFromId($locationid)))
        ) {
            if ($loggedUser
                && $objObserver->getObserverProperty(
                    $loggedUser,
                    'stdlocation'
                ) == $locationid
            ) {
                $objObserver->setObserverProperty($loggedUser, 'stdlocation', 0);
            }
            $objDatabase->execSQL(
                "DELETE FROM locations WHERE id=\"" . $locationid . "\""
            );
            return _("The location is removed from your list");
        }
    }

    /**
     * Validates and saves a location (set in the locationid Get keyword)
     *
     * @return string The message that the location was saved.
     */
    public function validateSaveLocation()
    {
        global $objPresentations, $objUtil, $objDatabase, $objObserver, $loggedUser;
        if (($objUtil->checkPostKey('adaptStandardLocation') == 1)
            && $objUtil->checkUserID(
                $this->getLocationPropertyFromId(
                    $objUtil->checkPostKey('stdlocation'),
                    'observer'
                )
            )
        ) {
            $objObserver->setObserverProperty(
                $loggedUser,
                'stdlocation',
                $_POST ['stdlocation']
            );
        } elseif ($objUtil->checkPostKey('locationname')
            && $objUtil->checkPostKey('country')
        ) {
            $latitude = $objUtil->checkPostKey('latitude', 0);
            $longitude = $objUtil->checkPostKey('longitude', 0);
            // Get the timezone
            $timezone = $objUtil->checkPostKey('timezone');
            $locationname = $objUtil->checkPostKey('locationname');
            $country = $objUtil->checkPostKey('country');
            $elevation = $objUtil->checkPostKey('elevation');

            if ($objUtil->checkPostKey('add')) {
                $id = $this->addLocation(
                    $locationname,
                    $longitude,
                    $latitude,
                    $country,
                    $timezone,
                    $elevation
                );
                if (array_key_exists('sb', $_POST) && $_POST['sb']) {
                    $this->setLocationProperty($id, 'skyBackground', $_POST['sb']);
                    $this->setLocationProperty($id, 'limitingMagnitude', -999);
                } elseif (array_key_exists('lm', $_POST) && $_POST['lm']) {
                    $this->setLocationProperty(
                        $id,
                        'limitingMagnitude',
                        $_POST['lm']
                    );
                    $this->setLocationProperty($id, 'skyBackground', -999);
                } else {
                    $this->setLocationProperty($id, 'skyBackground', -999);
                    $this->setLocationProperty($id, 'limitingMagnitude', -999);
                }
                $this->setLocationProperty($id, 'observer', $loggedUser);

                return _("The location is added to the database");
            }
            if ($objUtil->checkPostKey('change')
                && $objUtil->checkAdminOrUserID(
                    $this->getLocationPropertyFromId(
                        $objUtil->checkPostKey('id'),
                        'observer'
                    )
                )
            ) {
                $this->setLocationProperty(
                    $_POST['id'],
                    'name',
                    $_POST['locationname']
                );
                $this->setLocationProperty(
                    $_POST['id'],
                    'country',
                    $_POST['country']
                );
                $this->setLocationProperty($_POST['id'], 'longitude', $longitude);
                $this->setLocationProperty($_POST['id'], 'latitude', $latitude);
                $this->setLocationProperty($_POST['id'], 'timezone', $timezone);
                $this->setLocationProperty($_POST['id'], 'elevation', $elevation);
                $this->setLocationProperty($_POST['id'], 'observer', $loggedUser);
                $this->setLocationProperty($_POST['id'], 'checked', 1);
                if ($objUtil->checkPostKey('sb')) {
                    $this->setLocationProperty(
                        $_POST['id'],
                        'skyBackground',
                        $_POST['sb']
                    );
                    $this->setLocationProperty(
                        $_POST['id'],
                        'limitingMagnitude',
                        -999
                    );
                } elseif ($objUtil->checkPostKey('lm')) {
                    $this->setLocationProperty(
                        $_POST['id'],
                        'limitingMagnitude',
                        $_POST['lm']
                    );
                    $this->setLocationProperty($_POST['id'], 'skyBackground', -999);
                } else {
                    $this->setLocationProperty($_POST['id'], 'skyBackground', -999);
                    $this->setLocationProperty(
                        $_POST['id'],
                        'limitingMagnitude',
                        -999
                    );
                }
                return _("The location is changed in the database");
            }
        } else {
            return _("All required fields must be filled in!");
        }
    }
}
