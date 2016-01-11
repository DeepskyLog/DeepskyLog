<?php
// view_observer.php
// shows information of an observer
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	statistics ();
function statistics() {
	global $modules, $deepsky, $comets, $baseURL, $instDir, $loggedUser, $objDatabase, $objAccomplishments, $objInstrument, $objPresentations, $objObservation, $objUtil, $objCometObservation, $objObserver, $objLocation;
	$totalDSObservations = $objObservation->getNumberOfDsObservations ();
	$totalDSYearObservations = $objObservation->getObservationsLastYear ( '%' );
	$totalDSobjects = $objObservation->getNumberOfDifferentObservedDSObjects ();
	$totalCometObservations = $objCometObservation->getNumberOfObservations ();
	$totalCometYearObservations = $objCometObservation->getNumberOfObservationsThisYear ();
	$totalCometobjects = $objCometObservation->getNumberOfDifferentObjects ();

	for($i = 0; $i < count ( $modules ); $i ++) {
		if (strcmp ( ${$modules [$i]}, $deepsky ) == 0) {
			$key = $i;
			$information [$i] [0] = $totalDSObservations;
			$information [$i] [1] = $totalDSYearObservations;
			$information [$i] [2] = $totalDSobjects;
		}
		if (strcmp ( ${$modules [$i]}, $comets ) == 0) {
			$information [$i] [0] = $totalCometObservations;
			$information [$i] [1] = $totalCometYearObservations;
			$information [$i] [2] = $totalCometobjects;
		}
	}
	echo "<div>";
	// Create a drop-down to select the country.
	$countriesArray = array ();

	// First find a list of all countries
	$all = array_count_values($objDatabase->selectSingleArray ( "select locations.country from observations join locations on observations.locationid=locations.id", "country"));
	$allComets = array_count_values($objDatabase->selectSingleArray ( "select locations.country from cometobservations join locations on cometobservations.locationid=locations.id", "country"));

	// We loop over the countries (we merge the deepsky and comet observations)
	$countryList = array_unique(array_merge(array_keys($all), array_keys($allComets)));
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
	ksort($countriesArray);

	// TODO: The following line just breaks all the charts...
	// echo "<script>
	// 				$(document).ready(function() {
  // 					$(\".countrySelection\").select2();
	// 				});
	// 			</script>";

	echo "<select class=\"form-control countrySelection\">";
	echo "<option value=\"All\">All</option>";
	echo "<option disabled>──────────</option>";

	foreach ( $countriesArray as $key => $value ) {
		if ($key != "") {
			echo "<option value=\"" . $key . "\">" . $key . "</option>";
		}
	}
	echo "</select><br />";

	// We make some tabs.
	echo "<ul id=\"tabs\" class=\"nav nav-tabs\" data-tabs=\"tabs\">
          <li class=\"active\"><a href=\"#info\" data-toggle=\"tab\">" . GraphInfo . "</a></li>
          <li><a href=\"#observationsPerYear\" data-toggle=\"tab\">" . GraphObservationsTitle . "</a></li>
					<li><a href=\"#observationsPerMonth\" data-toggle=\"tab\">" . GraphObservationsMonthTitle . "</a></li>
          <li><a href=\"#objectTypes\" data-toggle=\"tab\">" . GraphObservationsType . "</a></li>
          <li><a href=\"#countries\" data-toggle=\"tab\">" . GraphObservationsPerCountry . "</a></li>
        </ul>";

	echo "<div id=\"my-tab-content\" class=\"tab-content\">";
	echo "<div class=\"tab-pane active\" id=\"info\">";
	echo "<table class=\"table table-striped\">";
	echo " <tr>";
	echo "  <th></th>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <th>" . $GLOBALS [$modules [$i]];
		echo " </th>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangViewObserverNumberOfObservations . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . $information [$i] [0];
		echo " </td>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangTopObserversHeader4 . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . $information [$i] [1];
		echo " </td>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangTopObserversHeader6 . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . $information [$i] [2];
		echo " </td>";
	}
	echo " </tr>";

	echo "</table>";

	echo "</div>";

	// The observations per year page
	echo "<div class=\"tab-pane\" id=\"observationsPerYear\">";
	// GRAPH
	// Check the date of the first observation
	$currentYear = date ( "Y" );
	$sql = $objDatabase->selectSingleValue ( "select MIN(date) from observations;", "MIN(date)", $currentYear . "0606" );
	$sql2 = $objDatabase->selectSingleValue ( "select MIN(date) from cometobservations;", "MIN(date)", $currentYear . "0606" );
	$startYear = min ( floor ( $sql / 10000 ), floor ( $sql2 / 10000 ) );
	// Add the JavaScript to initialize the chart on document ready
	echo "<script type=\"text/javascript\">

	  	      var chart;
						var dataYear = [";
						for($i = $startYear; $i <= $currentYear; $i ++) {
							$obs = $objDatabase->selectSingleValue ( "select COUNT(date) from observations where date >= \"" . $i . "0101\" and date <= \"" . $i . "1231\";", "COUNT(date)", "0" );
							if ($i != $currentYear) {
								echo $obs . ", ";
							} else {
								echo $obs;
							}
						}
						echo "];
						var cometdataYear = [";
						for($i = $startYear; $i <= $currentYear; $i ++) {
							$obs = $objDatabase->selectSingleValue ( "select COUNT(date) from cometobservations where date >= \"" . $i . "0101\" and date <= \"" . $i . "1231\";", "COUNT(date)", "0" );
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
	  	          marginBottom: 25
	  	        },
	  	        title: {
	  	          text: \"" . GraphTitle1 . "\",
	  	          x: -20 //center
	  	        },
	  	        subtitle: {
	  	          text: '" . GraphSource . $baseURL . "',
	  	          x: -20
	  	        },
	  	        xAxis: {
	  	          categories: [";

	for($i = $startYear; $i <= $currentYear; $i ++) {
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
	  	            text: '" . GraphObservations . "'
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
														this.x +': '+ this.y + ' (' + Highcharts.numberFormat(this.y / dataYearSum * 100) + '%)';
								} else {
									return '<b>'+ this.series.name +'</b><br/>'+
														this.x +': '+ this.y + ' (' + Highcharts.numberFormat(this.y / cometdataYearSum * 100) + '%)';
								}
	  	        }
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
	  	                      name: '" . html_entity_decode ( $deepsky, ENT_QUOTES, "UTF-8" ) . "',
	  	                        data: dataYear
	  	                      }, {
                              name: '" . html_entity_decode ( $comets, ENT_QUOTES, "UTF-8" ) . "',
                                data: cometdataYear }]
	  	                      });
	  	                      });

	  	                      </script>";

	// Show graph
	echo "<div id=\"container\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
	echo "</div>";


	// The observations per month page
	echo "<div class=\"tab-pane\" id=\"observationsPerMonth\">";
	// GRAPH
	// Add the JavaScript to initialize the chart on document ready
	echo "<script type=\"text/javascript\">
	  	      var chart;
						var data = [";
						for($i = 1; $i <= 12; $i ++) {
							$obs = $objDatabase->selectSingleValue ( "select COUNT(date) from observations where MONTH(date) = \"" . $i . "\";", "COUNT(date)", "0" );
							if ($i != 12) {
								echo $obs . ", ";
							} else {
								echo $obs;
							}
						}
						echo "];
						var cometdata = [";
							for($i = 1; $i <= 12; $i ++) {
								$obs = $objDatabase->selectSingleValue ( "select COUNT(date) from cometobservations where MONTH(date) = \"" . $i . "\";", "COUNT(date)", "0" );
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
	  	          text: \"" . GraphTitleMonths . "\",
	  	          x: -20 //center
	  	        },
	  	        subtitle: {
	  	          text: '" . GraphSource . $baseURL . "',
	  	          x: -20
	  	        },
	  	        xAxis: {
	  	          categories: [ ";

								global $Month1Short, $Month2Short, $Month3Short, $Month4Short, $Month5Short, $Month6Short, $Month7Short, $Month8Short, $Month9Short, $Month10Short, $Month11Short, $Month12Short;
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
	  	            text: '" . GraphObservations . "'
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
														this.x +': '+ this.y + ' (' + Highcharts.numberFormat(this.y / dataSum * 100) + '%)';
								} else {
									return '<b>'+ this.series.name +'</b><br/>'+
														this.x +': '+ this.y + ' (' + Highcharts.numberFormat(this.y / cometdataSum * 100) + '%)';
								}
	  	        }
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
	  	                      name: '" . html_entity_decode ( $deepsky, ENT_QUOTES, "UTF-8" ) . "',
	  	                        data: data
	  	                      }, {
                              name: '" . html_entity_decode ( $comets, ENT_QUOTES, "UTF-8" ) . "',
                                data: cometdata
 														  }]
	  	                      });
	  	                      });

	  	                      </script>";

	// Show graph
	echo "<div id=\"container3\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";
	echo "</div>";

	// The tab with the object types
	echo "<div class=\"tab-pane\" id=\"objectTypes\">";
	// Pie chart
	$objectsArray = array ();
	$colors = Array ();

	$all = count ( $objDatabase->selectRecordsetArray ( "select * from observations" ) );
	if ($all == 0) {
		$all = 1;
	}
	$rest = 0;

	$cometobservations = count ( $objDatabase->selectRecordsetArray ( "select * from cometobservations" ) );
	$all += $cometobservations;

	if (($cometobservations / $all) >= 0.01) {
		$objectsArray ["comets"] = $cometobservations;
	} else {
		$rest += $cometobservations;
	}
	$colors ["comets"] = "#4572A7";

	$aster = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ASTER\"" ) );
	$aster += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA8STAR\"" ) );
	$aster += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA4STAR\"" ) );
	$aster += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA3STAR\"" ) );

	if (($aster / $all) >= 0.01) {
		$objectsArray ["ASTER"] = $aster;
	} else {
		$rest += $aster;
	}
	$colors ["ASTER"] = "#AA4643";

	$brtnb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"BRTNB\"" ) );

	if (($brtnb / $all) >= 0.01) {
		$objectsArray ["BRTNB"] = $brtnb;
	} else {
		$rest += $brtnb;
	}
	$colors ["BRTNB"] = "#89A54E";

	$ds = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"DS\"" ) );
	$ds += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA2STAR\"" ) );

	if (($ds / $all) >= 0.01) {
		$objectsArray ["DS"] = $ds;
	} else {
		$rest += $ds;
	}
	$colors ["DS"] = "#80699B";

	$star = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA1STAR\"" ) );

	if (($star / $all) >= 0.01) {
		$objectsArray ["AA1STAR"] = $star;
	} else {
		$rest += $star;
	}
	$colors ["AA1STAR"] = "#3D96AE";

	$drknb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"DRKNB\"" ) );

	if (($drknb / $all) >= 0.01) {
		$objectsArray ["DRKNB"] = $drknb;
	} else {
		$rest += $drknb;
	}
	$colors ["DRKNB"] = "#DB843D";

	$galcl = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GALCL\"" ) );

	if (($galcl / $all) >= 0.01) {
		$objectsArray ["GALCL"] = $galcl;
	} else {
		$rest += $galcl;
	}
	$colors ["GALCL"] = "#92A8CD";

	$galxy = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\"" ) );

	if (($galxy / $all) >= 0.01) {
		$objectsArray ["GALXY"] = $galxy;
	} else {
		$rest += $galxy;
	}
	$colors ["GALXY"] = "#68302F";

	$plnnb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\"" ) );

	if (($plnnb / $all) >= 0.01) {
		$objectsArray ["PLNNB"] = $plnnb;
	} else {
		$rest += $plnnb;
	}
	$colors ["PLNNB"] = "#A47D7C";

	$opncl = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\"" ) );
	$opncl += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\"" ) );

	if (($opncl / $all) >= 0.01) {
		$objectsArray ["OPNCL"] = $opncl;
	} else {
		$rest += $opncl;
	}
	$colors ["OPNCL"] = "#B5CA92";

	$glocl = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\"" ) );

	if (($glocl / $all) >= 0.01) {
		$objectsArray ["GLOCL"] = $glocl;
	} else {
		$rest += $glocl;
	}
	$colors ["GLOCL"] = "#00FF00";

	$eminb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\"" ) );
	$eminb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\"" ) );
	$eminb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\"" ) );

	if (($eminb / $all) >= 0.01) {
		$objectsArray ["EMINB"] = $eminb;
	} else {
		$rest += $eminb;
	}
	$colors ["EMINB"] = "#C0FFC0";

	$refnb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\"" ) );
	$refnb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\"" ) );
	$refnb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"HII\"" ) );

	if (($refnb / $all) >= 0.01) {
		$objectsArray ["REFNB"] = $refnb;
	} else {
		$rest += $refnb;
	}
	$colors ["REFNB"] = "#0000C0";

	$nonex = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"NONEX\"" ) );

	if (($nonex / $all) >= 0.01) {
		$objectsArray ["NONEX"] = $nonex;
	} else {
		$rest += $nonex;
	}
	$colors ["NONEX"] = "#C0C0FF";

	$snrem = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\"" ) );

	if (($snrem / $all) >= 0.01) {
		$objectsArray ["SNREM"] = $snrem;
	} else {
		$rest += $snrem;
	}
	$colors ["SNREM"] = "#808000";

	$quasr = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"QUASR\"" ) );

	if (($quasr / $all) >= 0.01) {
		$objectsArray ["QUASR"] = $quasr;
	} else {
		$rest += $quasr;
	}
	$colors ["QUASR"] = "#C0C000";

	$wrneb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\"" ) );

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
						text: \"" . ObjectsSeenGraph . "\"
					},
                subtitle: {
                  text: '" . GraphSource . $baseURL . "'
                },
					tooltip: {
						formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage * 100) / 100 + '%';
						}
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

	foreach ( $objectsArray as $key => $value ) {
		if ($key != "REST") {
			print "{name: \"" . html_entity_decode ( $GLOBALS [$key], ENT_QUOTES, "UTF-8" ) . "\", color: '" . $colors [$key] . "', y: " . $value . "}, ";
		} else {
			print "{name: \"" . html_entity_decode ( $GLOBALS [$key], ENT_QUOTES, "UTF-8" ) . "\", color: '" . $colors [$key] . "', y: " . $value . "}";
		}
	}
	echo "
						]
					}]
				});
			});

		</script>";
	echo "<div id=\"container2\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";

	echo "</div>";

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
						text: \"" . GraphObservationsPerCountry . "\"
					},
                subtitle: {
                  text: '" . GraphSource . $baseURL . "'
                },
					tooltip: {
						formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage * 100) / 100 + '%';
						}
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
	foreach ( $countriesArray as $key => $value ) {
		if (($value / $all) >= 0.01) {
			$correctedCountries [$key] = $value;
		} else {
			$rest += $value;
		}
	}
	$correctedCountries["Rest"] = $rest;

	foreach ( $correctedCountries as $key => $value ) {
		print "{name: \"" . $key . "\", y: " . $value . "},";
	}
	echo "
						]
					}]
				});
			});

		</script>";
	echo "<div id=\"containerCountry\" style=\"width: 800px; height: 400px; margin: 0 auto\"></div>";

	echo "</div>";

	echo "</div>";
	echo "</div>";
}
?>
