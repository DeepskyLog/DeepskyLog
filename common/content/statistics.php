<?php
/**
 * Shows statistics per country.
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
} else {
    statistics();
}

/**
 * Shows the page with all information, and statistics of DeepskyLog.
 * Both for separate countries and for everything.
 *
 * @return None
 */
function statistics()
{
    global $modules, $deepsky, $comets, $baseURL, $instDir, $loggedUser;
    global $objDatabase, $objAccomplishments, $objInstrument, $objPresentations;
    global $objObservation, $objUtil, $objCometObservation, $objObserver;
    global $objLocation;

    if (array_key_exists("country", $_POST)) {
        $selectedCountry = $_POST["country"];
    } else {
        $selectedCountry = "All";
    }
    $totalDSObservations = $objObservation->getNumberOfDsObservations();
    $totalDSYearObservations = $objObservation->getObservationsLastYear('%');
    $totalDSobjects = $objObservation->getNumberOfDifferentObservedDSObjects();
    $totalCometObservations = $objCometObservation->getNumberOfObservations();
    $totalCometYearObservations
        = $objCometObservation->getNumberOfObservationsThisYear();
    $totalCometobjects = $objCometObservation->getNumberOfDifferentObjects();
    $totalDSDrawings = $objObservation->getNumberOfDsDrawings();
    $totalDSYearDrawings = $objObservation->getDrawingsLastYear('%');
    $totalCometDrawings = $objCometObservation->getNumberOfDrawings();
    $totalCometYearDrawings = $objCometObservation->getNumberOfDrawingsLastYear();

    if (strcmp($selectedCountry, "All") != 0) {
        $totalCountryDSObservations = $objObservation->getNumberOfDsObservations(
            $selectedCountry
        );
        $totalCountryDSYearObservations = $objObservation->getObservationsLastYear(
            '%',
            $selectedCountry
        );
        $totalCountryDSobjects
            = $objObservation->getNumberOfDifferentObservedDSObjects(
                $selectedCountry
            );
        $totalCountryCometObservations
            = $objCometObservation->getNumberOfObservations(
                $selectedCountry
            );
        $totalCountryCometYearObservations
            = $objCometObservation->getNumberOfObservationsThisYear(
                $selectedCountry
            );
        $totalCountryCometobjects
            = $objCometObservation->getNumberOfDifferentObjects(
                $selectedCountry
            );
        $totalCountryDSDrawings = $objObservation->getNumberOfDsDrawings(
            $selectedCountry
        );
        $totalCountryDSYearDrawings = $objObservation->getDrawingsLastYear(
            '%',
            $selectedCountry
        );
        $totalCountryCometDrawings = $objCometObservation->getNumberOfDrawings(
            $selectedCountry
        );
        $totalCountryCometYearDrawings
            = $objCometObservation->getNumberOfDrawingsLastYear(
                $selectedCountry
            );

    }

    for ($i = 0; $i < count($modules); $i++) {
        if (strcmp(${$modules[$i]}, $deepsky) == 0) {
            $key = $i;
            if (strcmp($selectedCountry, "All") == 0) {
                $information[$i][0] = $totalDSObservations;
                $information[$i][1] = $totalDSYearObservations;
                $information[$i][2] = $totalDSobjects;
                $information[$i][3] = $totalDSDrawings;
                $information[$i][4] = $totalDSYearDrawings;
            } else {
                $information[$i][0] = $totalCountryDSObservations . " / "
                    . $totalDSObservations . "&nbsp;(" . sprintf(
                        "%.2f",
                        ($totalCountryDSObservations / $totalDSObservations) * 100
                    ) . "%)";
                $information[$i][1] = $totalCountryDSYearObservations . " / "
                    . $totalDSYearObservations . "&nbsp;(" . sprintf(
                        "%.2f",
                        $totalCountryDSYearObservations
                        / $totalDSYearObservations * 100
                    ) . "%)";
                $information[$i][2] = $totalCountryDSobjects . " / "
                    . $totalDSobjects . "&nbsp;(" . sprintf(
                        "%.2f",
                        $totalCountryDSobjects / $totalDSobjects * 100
                    ) . "%)";
                $information[$i][3] = $totalCountryDSDrawings . " / "
                    . $totalDSDrawings . "&nbsp;(" . sprintf(
                        "%.2f",
                        ($totalCountryDSDrawings / $totalDSDrawings) * 100
                    ) . "%)";
                $information[$i][4] = $totalCountryDSYearDrawings . " / "
                    . $totalDSYearDrawings . "&nbsp;(" . sprintf(
                        "%.2f",
                        $totalCountryDSYearDrawings
                        / $totalDSYearDrawings * 100
                    ) . "%)";
            }
        }
        if (strcmp(${$modules[$i]}, $comets) == 0) {
            if (strcmp($selectedCountry, "All") == 0) {
                $information[$i][0] = $totalCometObservations;
                $information[$i][1] = $totalCometYearObservations;
                $information[$i][2] = $totalCometobjects;
                $information[$i][3] = $totalCometDrawings;
                $information[$i][4] = $totalCometYearDrawings;
            } else {
                $information[$i][0] = $totalCountryCometObservations . " / "
                    . $totalCometObservations . " ("
                    . sprintf(
                        "%.2f",
                        $totalCountryCometObservations / $totalCometObservations
                        * 100
                    ) . "%)";
                $information[$i][1] = $totalCountryCometYearObservations . " / "
                    . $totalCometYearObservations . "&nbsp;("
                    . sprintf(
                        "%.2f",
                        $totalCountryCometYearObservations
                        / ($totalCometYearObservations
                        ? $totalCometYearObservations : 1)
                        * 100
                    ) . "%)";
                $information[$i][2] = $totalCountryCometobjects . " / "
                    . $totalCometobjects . " (" . sprintf(
                        "%.2f",
                        $totalCountryCometobjects / $totalCometobjects * 100
                    ) . "%)";
                $information[$i][3] = $totalCountryCometDrawings . " / "
                    . $totalCometDrawings . " ("
                    . sprintf(
                        "%.2f",
                        $totalCountryCometDrawings / $totalCometDrawings
                        * 100
                    ) . "%)";
                $information[$i][4] = $totalCountryCometYearDrawings . " / "
                    . $totalCometYearDrawings . "&nbsp;("
                    . sprintf(
                        "%.2f",
                        $totalCountryCometYearDrawings
                        / ($totalCometYearDrawings
                        ? $totalCometYearDrawings : 1)
                        * 100
                    ) . "%)";
            }
        }
    }

    $information[count($modules)][0] = 0;
    $information[count($modules)][1] = 0;
    $information[count($modules)][2] = 0;
    $information[count($modules)][3] = 0;
    $information[count($modules)][4] = 0;

    for ($i = 0; $i < count($modules); $i++) {
        $information[count($modules)][0] += intval($information[$i][0]);
        $information[count($modules)][1] += intval($information[$i][1]);
        $information[count($modules)][2] += intval($information[$i][2]);
        $information[count($modules)][3] += intval($information[$i][3]);
        $information[count($modules)][4] += intval($information[$i][4]);
    }

    echo "<div>";
    // Create a drop-down to select the country.
    $countriesArray = array();

    // First find a list of all countries
    $all = array_count_values(
        $objDatabase->selectSingleArray(
            "select locations.country from observations "
            . "join locations on observations.locationid=locations.id",
            "country"
        )
    );
    $allComets = array_count_values(
        $objDatabase->selectSingleArray(
            "select locations.country from cometobservations "
            . "join locations on cometobservations.locationid=locations.id",
            "country"
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
        $countriesArray[$country] = $obs;
    }
    ksort($countriesArray);

    // Use select2 to let the user type in the drop down.
    echo '<form action="' . $baseURL . 'index.php" method="post" class="form-inline">
                    <input type="hidden" name="indexAction" value="statistics" />
                  <div class="form-group">
                        <label class="control-label">'
        . _("Select country") . '&nbsp;&nbsp;</label>';

    echo " <select  name=\"country\" class=\"form-control\" onchange=\"submit();\">";
    echo "    <option value=\"All\">All</option>";
    echo "    <option disabled>──────────</option>";

    foreach ($countriesArray as $key => $value) {
        if ($key != "") {
            if ($key == $selectedCountry) {
                $select = " selected";
            } else {
                $select = "";
            }
            echo "<option" . $select . " value=\"" . $key . "\">"
                . $key . "</option>";
        }
    }
    echo "</select>";
    echo '    </div>
                </form><br />';

    // We make some tabs.
    echo "<ul id=\"tabs\" class=\"nav nav-tabs\" data-tabs=\"tabs\">
          <li class=\"active\"><a href=\"#info\" data-toggle=\"tab\">"
        . _("Info") . "</a></li>
          <li><a href=\"#observationsPerYear\" data-toggle=\"tab\">"
        . _("Observations per year") . "</a></li>
                    <li><a href=\"#observationsPerMonth\" data-toggle=\"tab\">"
        . _("Observations per month") . "</a></li>
          <li><a href=\"#objectTypes\" data-toggle=\"tab\">"
        . _("Object types observed") . "</a></li>";
    if (strcmp($selectedCountry, "All") == 0) {
        echo "<li><a href=\"#countries\" data-toggle=\"tab\">"
           . _("Observations per country") . "</a></li>";
    }
    echo "</ul>";

    echo "<div id=\"my-tab-content\" class=\"tab-content\">";
    echo "<div class=\"tab-pane active\" id=\"info\">";

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
    echo "  <td>" . $information[count($modules)][0] . "</td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][0];
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Observations last year") . "</td>";
    echo "  <td>" . $information[count($modules)][1] . "</td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][1];
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Number of drawings") . "</td>";
    echo "  <td>" . $information[count($modules)][3] . "</td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][3];
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Drawings last year") . "</td>";
    echo "  <td>" . $information[count($modules)][4] . "</td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][4];
        echo " </td>";
    }
    echo " </tr>";

    echo " <tr>";
    echo "  <td>" . _("Different objects") . "</td>";
    echo "  <td>" . $information[count($modules)][2] . "</td>";
    for ($i = 0; $i < count($modules); $i++) {
        echo " <td>" . $information[$i][2];
        echo " </td>";
    }
    echo " </tr>";

    echo "</table>";

    echo "</div>";

    // The observations per year page
    echo "<div class=\"tab-pane\" id=\"observationsPerYear\">";
    // GRAPH
    // Check the date of the first observation
    $currentYear = date("Y");

    if (strcmp($selectedCountry, "All") == 0) {
        $sql = $objDatabase->selectKeyValueArray(
            "select YEAR(date),count(*) from observations group by YEAR(date)",
            "YEAR(date)",
            "count(*)"
        );
        $sql2 = $objDatabase->selectKeyValueArray(
            "select YEAR(date),count(*) from cometobservations group by YEAR(date);",
            "YEAR(date)",
            "count(*)"
        );
    } else {
        $sql = $objDatabase->selectKeyValueArray(
            "select YEAR(date),count(*) from observations "
            . "JOIN locations ON observations.locationid=locations.id "
            . "WHERE locations.country = \"" . $selectedCountry
            . "\" group by YEAR(date)",
            "YEAR(date)",
            "count(*)"
        );
        $sql2 = $objDatabase->selectKeyValueArray(
            "select YEAR(date),count(*) from cometobservations "
            . "JOIN locations ON cometobservations.locationid=locations.id "
            . "WHERE locations.country = \"" . $selectedCountry
            . "\" group by YEAR(date);",
            "YEAR(date)",
            "count(*)"
        );
    }
    if (sizeof($sql) == 0) {
        $startYear = min(array_keys($sql2));
    } elseif (sizeof($sql2) == 0) {
        $startYear = min(array_keys($sql));
    } else {
        $startYear = min([min(array_keys($sql)), min(array_keys($sql2))]);
    }

    // Add the JavaScript to initialize the chart on document ready
    echo "<script type=\"text/javascript\">

                var chart;
                        var DSdataYear = [";
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
    for ($i = $startYear; $i <= $currentYear; $i++) {
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
                    text: \"" . _("Number of observations per year") . "\",
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
    echo "<div id=\"container\" "
        . "style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
    echo "</div>";

    // The observations per month page
    echo "<div class=\"tab-pane\" id=\"observationsPerMonth\">";
    // GRAPH
    // Add the JavaScript to initialize the chart on document ready
    if (strcmp($selectedCountry, "All") == 0) {
        $sql = $objDatabase->selectKeyValueArray(
            "select MONTH(date),count(*) from observations group by MONTH(date)",
            "MONTH(date)",
            "count(*)"
        );
        $sql2 = $objDatabase->selectKeyValueArray(
            "select MONTH(date),count(*) from cometobservations "
            . "group by MONTH(date);",
            "MONTH(date)",
            "count(*)"
        );
    } else {
        $sql = $objDatabase->selectKeyValueArray(
            "select MONTH(date),count(*) from observations "
            . "JOIN locations ON observations.locationid=locations.id "
            . "WHERE locations.country = \"" . $selectedCountry
            . "\" group by MONTH(date)",
            "MONTH(date)",
            "count(*)"
        );
        $sql2 = $objDatabase->selectKeyValueArray(
            "select MONTH(date),count(*) from cometobservations "
            . "JOIN locations ON cometobservations.locationid=locations.id "
            . "WHERE locations.country = \"" . $selectedCountry
            . "\" group by MONTH(date);",
            "MONTH(date)",
            "count(*)"
        );
    }
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
    for ($i = 1; $i <= 12; $i++) {
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
                    text: \"" . _("Number of observations per month") . "\",
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
                            Highcharts.numberFormat(this.y / cometdataSum * 100) +
                            '%)';
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
                            . _("Deepsky") . "',
                                  data: data
                                }, {
                              name: '"
                            . _("Comets") . "',
                                data: cometdata
                                                           }]
                                });
                                });

                                </script>";

    // Show graph
    echo "<div id=\"container3\" "
        . "style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
    echo "</div>";

    // The tab with the object types
    echo "<div class=\"tab-pane\" id=\"objectTypes\">";
    // Pie chart
    if (strcmp($selectedCountry, "All") == 0) {
        $deepskyobservations = $objDatabase->selectKeyValueArray(
            "select objects.type,count(*) from observations"
            . " JOIN objects on observations.objectname=objects.name"
            . " group by objects.type;",
            "type",
            "count(*)"
        );
        $cometobservations = count(
            $objDatabase->selectRecordsetArray("select * from cometobservations")
        );
    } else {
        $deepskyobservations = $objDatabase->selectKeyValueArray(
            "select objects.type,count(*) from observations"
            . " JOIN objects on observations.objectname=objects.name"
            . " JOIN locations on observations.locationid=locations.id"
            . " where locations.country=\"" . $selectedCountry
            . "\" group by objects.type;",
            "type",
            "count(*)"
        );
        $cometobservations = count(
            $objDatabase->selectRecordsetArray(
                "select * from cometobservations"
                . " JOIN locations ON cometobservations.locationid=locations.id"
                . " WHERE locations.country = \"" . $selectedCountry . "\""
            )
        );
    }

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

    $objectsArray = array();
    $colors = array();

    $all = array_sum($deepskyobservations) + $cometobservations;
    if ($all == 0) {
        $all = 1;
    }
    $rest = 0;

    if (($cometobservations / $all) >= 0.01) {
        $objectsArray["comets"] = $cometobservations;
    } else {
        $rest += $cometobservations;
    }
    $colors["comets"] = "#4572A7";

    $aster = $deepskyobservations["ASTER"];
    $aster += $deepskyobservations["AA8STAR"];
    $aster += $deepskyobservations["AA4STAR"];
    $aster += $deepskyobservations["AA3STAR"];

    if (($aster / $all) >= 0.01) {
        $objectsArray["ASTER"] = $aster;
    } else {
        $rest += $aster;
    }
    $colors["ASTER"] = "#AA4643";

    $brtnb = $deepskyobservations["BRTNB"];

    if (($brtnb / $all) >= 0.01) {
        $objectsArray["BRTNB"] = $brtnb;
    } else {
        $rest += $brtnb;
    }
    $colors["BRTNB"] = "#89A54E";

    $ds = $deepskyobservations["DS"];

    if (($ds / $all) >= 0.01) {
        $objectsArray["DS"] = $ds;
    } else {
        $rest += $ds;
    }
    $colors["DS"] = "#80699B";

    $star = $deepskyobservations["AA1STAR"];

    if (($star / $all) >= 0.01) {
        $objectsArray["AA1STAR"] = $star;
    } else {
        $rest += $star;
    }
    $colors["AA1STAR"] = "#3D96AE";

    $drknb = $deepskyobservations["DRKNB"];

    if (($drknb / $all) >= 0.01) {
        $objectsArray["DRKNB"] = $drknb;
    } else {
        $rest += $drknb;
    }
    $colors["DRKNB"] = "#DB843D";

    $galcl = $deepskyobservations["GALCL"];

    if (($galcl / $all) >= 0.01) {
        $objectsArray["GALCL"] = $galcl;
    } else {
        $rest += $galcl;
    }
    $colors["GALCL"] = "#92A8CD";

    $galxy = $deepskyobservations["GALXY"];

    if (($galxy / $all) >= 0.01) {
        $objectsArray["GALXY"] = $galxy;
    } else {
        $rest += $galxy;
    }
    $colors["GALXY"] = "#68302F";

    $plnnb = $deepskyobservations["PLNNB"];

    if (($plnnb / $all) >= 0.01) {
        $objectsArray["PLNNB"] = $plnnb;
    } else {
        $rest += $plnnb;
    }
    $colors["PLNNB"] = "#A47D7C";

    $opncl = $deepskyobservations["OPNCL"];
    $opncl += $deepskyobservations["CLANB"];

    if (($opncl / $all) >= 0.01) {
        $objectsArray["OPNCL"] = $opncl;
    } else {
        $rest += $opncl;
    }
    $colors["OPNCL"] = "#B5CA92";

    $glocl = $deepskyobservations["GLOCL"];

    if (($glocl / $all) >= 0.01) {
        $objectsArray["GLOCL"] = $glocl;
    } else {
        $rest += $glocl;
    }
    $colors["GLOCL"] = "#00FF00";

    $eminb = $deepskyobservations["EMINB"];
    $eminb += $deepskyobservations["ENRNN"];
    $eminb += $deepskyobservations["ENSTR"];

    if (($eminb / $all) >= 0.01) {
        $objectsArray["EMINB"] = $eminb;
    } else {
        $rest += $eminb;
    }
    $colors["EMINB"] = "#C0FFC0";

    $refnb = $deepskyobservations["REFNB"];
    $refnb += $deepskyobservations["RNHII"];
    $refnb += $deepskyobservations["HII"];

    if (($refnb / $all) >= 0.01) {
        $objectsArray["REFNB"] = $refnb;
    } else {
        $rest += $refnb;
    }
    $colors["REFNB"] = "#0000C0";

    $nonex = $deepskyobservations["NONEX"];

    if (($nonex / $all) >= 0.01) {
        $objectsArray["NONEX"] = $nonex;
    } else {
        $rest += $nonex;
    }
    $colors["NONEX"] = "#C0C0FF";

    $snrem = $deepskyobservations["SNREM"];

    if (($snrem / $all) >= 0.01) {
        $objectsArray["SNREM"] = $snrem;
    } else {
        $rest += $snrem;
    }
    $colors["SNREM"] = "#808000";

    $quasr = $deepskyobservations["QUASR"];

    if (($quasr / $all) >= 0.01) {
        $objectsArray["QUASR"] = $quasr;
    } else {
        $rest += $quasr;
    }
    $colors["QUASR"] = "#C0C000";

    $wrneb = $deepskyobservations["WRNEB"];

    if (($wrneb / $all) >= 0.01) {
        $objectsArray["WRNEB"] = $wrneb;
    } else {
        $rest += $wrneb;
    }
    $colors["WRNEB"] = "#008080";

    $objectsArray["REST"] = $rest;
    $colors["REST"] = "#00FFFF";
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
                        text: \"" . _("Object types seen") . "\"
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
    echo "<div id=\"container2\" "
        . "style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";

    echo "</div>";

    if (strcmp($selectedCountry, "All") == 0) {
        // The tab with the observations per country
        echo "<div class=\"tab-pane\" id=\"countries\">";
        // Pie chart

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
                    text: \"" . _("Observations per country") . "\"
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

        // We only want to see the countries with at least 1% of the observations
        $rest = 0;
        foreach ($countriesArray as $key => $value) {
            if (($value / $all) >= 0.01) {
                $correctedCountries[$key] = $value;
            } else {
                $rest += $value;
            }
        }
        $correctedCountries["Rest"] = $rest;

        foreach ($correctedCountries as $key => $value) {
            print "{name: \"" . $key . "\", y: " . $value . "},";
        }
        echo "
                ]
            }]
        });
    });

    </script>";
        echo "<div id=\"containerCountry\" "
            . "style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";

        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
}
