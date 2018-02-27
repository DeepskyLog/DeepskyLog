<?php
// view_observer.php
// shows information of an observer
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($user = $objUtil->checkGetKey ( 'user' )))
	throw new Exception ( LangException015b );
else
	view_observer ();
function view_observer() {
	global $user, $modules, $deepsky, $comets, $baseURL, $instDir, $loggedUser, $objDatabase, $objAccomplishments, $objInstrument, $objPresentations, $objObservation, $objUtil, $objCometObservation, $objObserver, $objLocation;
	$name = $objObserver->getObserverProperty ( $user, 'name' );
	$firstname = $objObserver->getObserverProperty ( $user, 'firstname' );
	$location_id = $objObserver->getObserverProperty ( $user, 'stdlocation' );
	$location_name = $objLocation->getLocationPropertyFromId ( $location_id, 'name' );
	$instrumentname = $objInstrument->getInstrumentPropertyFromId ( $objObserver->getObserverProperty ( $user, 'stdtelescope' ), 'name' );
	$userDSobservation = $objObserver->getNumberOfDsObservations ( $user );
	$totalDSObservations = $objObservation->getNumberOfDsObservations ();
	$userDSYearObservations = $objObservation->getObservationsLastYear ( $user );
	$totalDSYearObservations = $objObservation->getObservationsLastYear ( '%' );
	$userDSObjects = $objObservation->getNumberOfObjects ( $user );
	$totalDSobjects = $objObservation->getNumberOfDifferentObservedDSObjects ();
	$userMobjects = $objObservation->getObservedCountFromCatalogOrList ( $user, "M" );
	$userCaldwellObjects = $objObservation->getObservedCountFromCatalogOrList ( $user, "Caldwell" );
	$userH400objects = $objObservation->getObservedCountFromCatalogOrList ( $user, "H400" );
	$userHIIobjects = $objObservation->getObservedCountFromCatalogOrList ( $user, "HII" );
	$userDSrank = $objObserver->getDsRank ( $user );
	if ($userDSrank === false)
		$userDSrank = "-";
	else
		$userDSrank ++;
	$userCometobservation = $objObserver->getNumberOfCometObservations ( $user );
	$totalCometObservations = $objCometObservation->getNumberOfObservations ();
	$userCometYearObservations = $objCometObservation->getObservationsThisYear ( $user );
	$totalCometYearObservations = $objCometObservation->getNumberOfObservationsThisYear ();
	$userCometObjects = $objCometObservation->getNumberOfObjects ( $user );
	$totalCometobjects = $objCometObservation->getNumberOfDifferentObjects ();
	$cometrank = $objObserver->getCometRank ( $user );
	if ($cometrank === false)
		$cometrank = "-";
	else
		$cometrank ++;

	for($i = 0; $i < count ( $modules ); $i ++) {
		if (strcmp ( ${$modules [$i]}, $deepsky ) == 0) {
			$key = $i;
			$information [$i] [0] = $userDSobservation . " / " . $totalDSObservations . "&nbsp;(" . sprintf ( "%.2f", ($userDSobservation / $totalDSObservations) * 100 ) . "%)";
			$information [$i] [1] = $userDSYearObservations . " / " . $totalDSYearObservations . "&nbsp;(" . sprintf ( "%.2f", $userDSYearObservations / $totalDSYearObservations * 100 ) . "%)";
			$information [$i] [2] = $userDSObjects . " / " . $totalDSobjects . "&nbsp;(" . sprintf ( "%.2f", $userDSObjects / $totalDSobjects * 100 ) . "%)";
			$information [$i] [4] = $userDSrank;
		}
		if (strcmp ( ${$modules [$i]}, $comets ) == 0) {
			$information [$i] [0] = $userCometobservation . " / " . $totalCometObservations . " (" . sprintf ( "%.2f", $userCometobservation / $totalCometObservations * 100 ) . "%)";
			$information [$i] [1] = $userCometYearObservations . " / " . $totalCometYearObservations . "&nbsp;(" . sprintf ( "%.2f", $userCometYearObservations / ($totalCometYearObservations ? $totalCometYearObservations : 1) * 100 ) . "%)";
			$information [$i] [2] = $userCometObjects . " / " . $totalCometobjects . " (" . sprintf ( "%.2f", $userCometObjects / $totalCometobjects * 100 ) . "%)";
			$information [$i] [4] = $cometrank;
		}
	}
	echo "<div>";
	echo "<h4>" . $firstname . ' ' . $name . "</h4>";
	echo "<hr />";
	// We make some tabs.
	echo "<ul id=\"tabs\" class=\"nav nav-tabs\" data-tabs=\"tabs\">
          <li class=\"active\"><a href=\"#info\" data-toggle=\"tab\">" . GraphInfo . "</a></li>
          <li><a href=\"#observationsPerYear\" data-toggle=\"tab\">" . GraphObservationsTitle . "</a></li>
					<li><a href=\"#observationsPerMonth\" data-toggle=\"tab\">" . GraphObservationsMonthTitle . "</a></li>
          <li><a href=\"#objectTypes\" data-toggle=\"tab\">" . GraphObservationsType . "</a></li>
					<li><a href=\"#countries\" data-toggle=\"tab\">" . GraphObservationsPerCountry . "</a></li>
          <li><a href=\"#stars\" data-toggle=\"tab\">" . GraphAccomplishments . "</a></li>
        </ul>";

	echo "<div id=\"my-tab-content\" class=\"tab-content\">";
	echo "<div class=\"tab-pane active\" id=\"info\">";
	if (array_key_exists ( 'admin', $_SESSION ) && ($_SESSION ['admin'] == "yes")) {
		// admin logged in
		echo "<br />";
		echo "<form class=\"form-horizontal\" role=\"form\" action=\"" . $baseURL . "index.php\" >";

		echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_emailNameFirstname_Password\" />";
		echo "<input type=\"hidden\" name=\"user\" value=\"" . $user . "\" />";
		echo "<div class=\"form-group\">";
		echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField1 . "</label>";
		echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">" . $objObserver->getObserverProperty ( $user, 'id' ) . "</p>";
		echo "</div></div>";
		echo "<div class=\"form-group\">
	         <label for=\"email\" class=\"col-sm-2 control-label\">" . LangChangeAccountField2 . "</label>
	         <div class=\"col-sm-5\">
	          <input type=\"email\" name=\"email\" class=\"form-control\" id=\"email\" value=\"" . $objObserver->getObserverProperty ( $user, 'email' ) . "\">
           </div>
	        </div>";
		echo "<div class=\"form-group\">
	         <label for=\"firstname\" class=\"col-sm-2 control-label\">" . LangChangeAccountField3 . "</label>
	         <div class=\"col-sm-5\">
	          <input type=\"text\" name=\"firstname\" class=\"form-control\" id=\"firstname\" value=\"" . $objObserver->getObserverProperty ( $user, 'firstname' ) . "\">
					 </div>
						<input type=\"submit\" class=\"btn btn-danger\" name=\"change_email_name_firstname\" value=\"".LangViewObserverChangeNameFirstname."\" />
	        </div>";
		echo "<div class=\"form-group\">
	         <label for=\"name\" class=\"col-sm-2 control-label\">" . LangChangeAccountField4 . "</label>
	         <div class=\"col-sm-5\">
	          <input type=\"text\" name=\"name\" class=\"form-control\" id=\"name\" value=\"" . $objObserver->getObserverProperty ( $user, 'name' ) . "\">
           </div>
	        </div>";
		echo "<div class=\"form-group\">
	         <label for=\"password\" class=\"col-sm-2 control-label\">" . LangChangeAccountField5 . "</label>
	         <div class=\"col-sm-3\">
	          <input type=\"text\" name=\"password\" class=\"form-control\" id=\"password\" value=\"\" />
           </div>
	         <div class=\"col-sm-2\">
	         	<input type=\"submit\" class=\"btn btn-primary\" name=\"change_password\" value=\"" . "Change password" . "\" />
	         </div>
	        </div>";
		echo "<div class=\"form-group\">";
		echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField7 . "</label>";
		echo "<div class=\"col-sm-5\"><p class=\"form-control-static\"><a href=\"" . $baseURL . "index.php?indexAction=detail_location&amp;location=" . urlencode ( $location_id ) . "\">" . $location_name . "</a></p>";
		echo "</div></div>";
		echo "<div class=\"form-group\">";
		echo "<label class=\"col-sm-2 control-label\">" . LangChangeAccountField8 . "</label>";
		// Here, we set the name of the default instrument. For the current user, we need to make it possible to change the default instrument.
		echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">";
		if ($instrumentname) {
			echo "<a href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $objObserver->getObserverProperty ( $user, 'stdtelescope' ) ) . "\">" . (($instrumentname == "Naked eye") ? InstrumentsNakedEye : $instrumentname) . "</a>";
		} else {
			echo "";
		}
		echo "</p></div></div>";
		echo "</form>";
	} else {
		echo "<table class=\"table table-striped\">";
		echo " <tr>
		        <td>" . LangChangeAccountField3 . "</td>
		        <td>" . $objObserver->getObserverProperty ( $user, 'firstname' ) . "</td>
		       </tr>";

		echo " <tr>
		        <td>" . LangChangeAccountField4 . "</td>
		        <td>" . $objObserver->getObserverProperty ( $user, 'name' ) . "</td>
		       </tr>";
		// Setting the default location
		echo " <tr>
				<td>" . LangChangeAccountField7 . "</td>";
		echo "<td>";
		if ($loggedUser == $user) {
			if (array_key_exists ( 'activeLocationId', $_GET ) && $_GET ['activeLocationId']) {
				$objObserver->setObserverProperty ( $loggedUser, 'stdlocation', $_GET ['activeLocationId'] );
				if (array_key_exists ( 'Qobj', $_SESSION ))
					$_SESSION ['Qobj'] = $objObject->getObjectVisibilities ( $_SESSION ['Qobj'] );
			}
			$result = $objLocation->getSortedLocations ( 'name', $loggedUser, 1 );
			$loc = $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' );

			if ($result) {
				echo "<div class=\"btn-group\">
			      <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\">
					" . $objLocation->getLocationPropertyFromId ( $loc, 'name' ) . "&nbsp;<span class=\"caret\"></span>";
				echo "</button> <ul class=\"dropdown-menu\">";

				$url = $baseURL . "index.php?indexAction=detail_observer&user=" . $loggedUser;
				while ( list ( $key, $value ) = each ( $result ) ) {
					echo "  <li><a href=\"" . $url . "&amp;activeLocationId=" . $value . "\">" . $objLocation->getLocationPropertyFromId ( $value, 'name' ) . "</a></li>";
				}

				echo " </ul>";
				echo "</li>
			          </div>";
			}
			echo "</td>";
		} else {
			echo "<a href=\"" . $baseURL . "index.php?indexAction=detail_location&amp;location=" . urlencode ( $location_id ) . "\">" . $location_name . "</a>
	          </td>
	         </tr>";
		}
		// Setting the default instrument
		echo " <tr>
	          <td>" . LangChangeAccountField8 . "</td>";
		echo "<td>";
		if ($loggedUser == $user) {
			if (array_key_exists ( 'activeTelescopeId', $_GET ) && $_GET ['activeTelescopeId']) {
				$objObserver->setObserverProperty ( $loggedUser, 'stdtelescope', $_GET ['activeTelescopeId'] );
				if (array_key_exists ( 'Qobj', $_SESSION ))
					$_SESSION ['Qobj'] = $objObject->getObjectVisibilities ( $_SESSION ['Qobj'] );
			}
			$result = $objInstrument->getSortedInstruments ( 'name', $loggedUser, 1 );
			$inst = $objObserver->getObserverProperty ( $loggedUser, 'stdtelescope' );

			if ($result) {
				echo "<div class=\"btn-group\">
			      <button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\">
					" . $objInstrument->getInstrumentPropertyFromId ( $inst, 'name' ) . "&nbsp;<span class=\"caret\"></span>";
				echo "</button> <ul class=\"dropdown-menu\">";

				$url = $baseURL . "index.php?indexAction=detail_observer&user=" . $loggedUser;
				while ( list ( $key, $value ) = each ( $result ) ) {
					echo "  <li><a href=\"" . $url . "&amp;activeTelescopeId=" . $value . "\">" . $objInstrument->getInstrumentPropertyFromId ( $value, 'name' ) . "</a></li>";
				}

				echo " </ul>";
				echo "</li>
			          </div>";
			}
			echo "</td>";
		} else {
			echo ($instrumentname ? "<a href=\"" . $baseURL . "index.php?indexAction=detail_instrument&amp;instrument=" . urlencode ( $objObserver->getObserverProperty ( $user, 'stdtelescope' ) ) . "\">" . (($instrumentname == "Naked eye") ? InstrumentsNakedEye : $instrumentname) . "</a>" : "") . "</td>
 	         </tr>";
		}
		echo '<tr>
						<td>';
		echo LangChangeAccountCopyright;
		echo '  </td>
						<td>';
		echo $objObserver->getCopyright( $user );

		echo '	</td>
					</tr>';
	}
	if ($objUtil->checkSessionKey ( 'admin' ) == "yes") {
		echo "<form class=\"form-horizontal\" role=\"form\" action=\"" . $baseURL . "index.php\" >";
		echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_role\" />";
		echo "<input type=\"hidden\" name=\"user\" value=\"" . $user . "\" />";
		echo "<div class=\"form-group\">";
		$content = '';
		if ($user != "admin") {
			echo "<div class=\"form-group\">
	         <label for=\"role\" class=\"col-sm-2 control-label\">" . LangViewObserverRole . "</label>
	         <div class=\"col-sm-3\">
	         		<select name=\"role\" class=\"form-control\">
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == ROLEADMIN) ? "selected=\"selected\"" : "") . " value=\"0\">" . LangViewObserverAdmin . "</option>
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == ROLEUSER) ? "selected=\"selected\"" : "") . " value=\"1\">" . LangViewObserverUser . "</option>
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == ROLECOMETADMIN) ? "selected=\"selected\"" : "") . " value=\"4\">" . LangViewObserverCometAdmin . "</option>
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == ROLEWAITLIST) ? "selected=\"selected\"" : "") . " value=\"2\">" . LangViewObserverWaitlist . "</option>
 	          </select>&nbsp;
           </div>
           <div class=\"col-sm-2\">
                <button type=\"submit\" class=\"btn btn-default\" name=\"change\">" . LangViewObserverChange . "</button>
           </div>
	        </div>";
		} elseif ($objObserver->getObserverProperty ( $user, 'role', 2 ) == ROLEWAITLIST) {
			echo "<div class=\"form-group\">";
			echo "<label class=\"col-sm-2 control-label\">" . LangViewObserverRole . "</label>";
			echo "<div class=\"col-sm-5\">" . LangViewObserverWaitlist;
			echo "</div></div>";
		} else // fixed admin role
{
			echo "<div class=\"form-group\">";
			echo "<label class=\"col-sm-2 control-label\">" . LangViewObserverRole . "</label>";
			echo "<div class=\"col-sm-5\">" . LangViewObserverAdmin;
			echo "</div></div>";
		}
		echo "</div></form>";
	}
	echo "</table>";
	echo "<hr />";
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

	echo " <tr>";
	echo "  <td>" . LangTopObserversHeader5 . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . (($key == $i) ? $userMobjects . " / 110" : "-");
		echo " </td>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangTopObserversHeader5b . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . (($key == $i) ? $userCaldwellObjects . " / 110" : "-");
		echo " </td>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangTopObserversHeader5c . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . (($key == $i) ? $userH400objects . " / 400" : "-");
		echo " </td>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangTopObserversHeader5d . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . (($key == $i) ? $userHIIobjects . " / 400" : "-");
		echo " </td>";
	}
	echo " </tr>";

	echo " <tr>";
	echo "  <td>" . LangViewObserverRank . "</td>";
	for($i = 0; $i < count ( $modules ); $i ++) {
		echo " <td>" . $information [$i] [4];
		echo " </td>";
	}
	echo " </tr>";

	echo "</table>";

	if ($loggedUser != "") {
		if ($user != $loggedUser) {
			echo "<br />";
			echo "<a class=\"btn btn-primary\" href=\"" . $baseURL . "index.php?indexAction=new_message&amp;receiver=" . $user . "\">";
			echo "<span class=\"glyphicon glyphicon-envelope\"></span> " . LangMessagePublicList5 . $firstname . "</a>";
		}
	}

	echo "<hr />";
	$dir = opendir ( $instDir . 'common/observer_pics' );
	while ( FALSE !== ($file = readdir ( $dir )) ) {
		if (("." == $file) or (".." == $file))
			continue; // skip current directory and directory above
		if (fnmatch ( $user . ".gif", $file ) || fnmatch ( $user . ".jpg", $file ) || fnmatch ( $user . ".png", $file )) {
			echo "<div>";
			echo "<a href=\"" . $baseURL . "common/observer_pics/" . $file . "\" data-lightbox=\"image-1\" data-title=\"\">";
			echo "<img class=\"viewobserver\" src=\"" . $baseURL . "common/observer_pics/" . $file . "\" alt=\"" . $firstname . "&nbsp;" . $name . "\"></img>
	          </a></div>";
			echo "<hr />";
		}
	}

	echo "</div>";

	// The observations per year page
	echo "<div class=\"tab-pane\" id=\"observationsPerYear\">";
	// GRAPH
	// Check the date of the first observation
	$currentYear = date ( "Y" );
	$sql = $objDatabase->selectKeyValueArray ("select YEAR(date),count(*) from observations where observerid=\"" . $user . "\" group by YEAR(date)", "YEAR(date)", "count(*)");
	$sql2 = $objDatabase->selectKeyValueArray ( "select YEAR(date),count(*) from cometobservations where observerid=\"" . $user . "\" group by YEAR(date);", "YEAR(date)", "count(*)" );

	if (sizeof($sql) == 0) {
		$startYear = min(array_keys($sql2));
	} else if (sizeof($sql2 == 0)) {
		$startYear = min(array_keys($sql));
	} else {
		$startYear = min ( [min(array_keys($sql)), min(array_keys ( $sql2 ) )] );
	}
	// Add the JavaScript to initialize the chart on document ready
	echo "<script type=\"text/javascript\">

	  	      var chart;
						var dataYear = [";
						if ($startYear < 1900) {
							$startYear = $currentYear;
						}
						for($i = $startYear; $i <= $currentYear; $i ++) {
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
  					for($i = $startYear; $i <= $currentYear; $i ++) {
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
	  	          marginBottom: 40
	  	        },
	  	        title: {
	  	          text: \"" . GraphTitle1 . ": " . html_entity_decode ( $firstname, ENT_QUOTES, "UTF-8" ) . " " . html_entity_decode ( $name, ENT_QUOTES, "UTF-8" ) . "\",
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
	$sql = $objDatabase->selectKeyValueArray ("select MONTH(date),count(*) from observations where observerid=\"" . $user . "\" group by MONTH(date)", "MONTH(date)", "count(*)");
	$sql2 = $objDatabase->selectKeyValueArray ( "select MONTH(date),count(*) from cometobservations where observerid=\"" . $user . "\" group by MONTH(date);", "MONTH(date)", "count(*)" );

	echo "<script type=\"text/javascript\">
	  	      var chart;
						var data = [";
						for($i = 1; $i <= 12; $i ++) {
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
							for($i = 1; $i <= 12; $i ++) {
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
	  	          text: \"" . GraphTitleMonths . ": " . html_entity_decode ( $firstname, ENT_QUOTES, "UTF-8" ) . " " . html_entity_decode ( $name, ENT_QUOTES, "UTF-8" ) . "\",
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
	$deepskyobservations = $objDatabase->selectKeyValueArray ("select objects.type,count(*) from observations JOIN objects on observations.objectname=objects.name where observerid=\"" . $user . "\" group by objects.type;", "type", "count(*)");
	$cometobservations = count ( $objDatabase->selectRecordsetArray ( "select * from cometobservations where observerid=\"" . $user . "\"" ) );

	$objectsArray = array ();
	$colors = Array ();

	// Correct the deepskyobservations array. Make sure that all the entries are available.
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
						text: \"" . ObjectsSeenGraph . ": " . html_entity_decode ( $firstname, ENT_QUOTES, "UTF-8" ) . " " . html_entity_decode ( $name, ENT_QUOTES, "UTF-8" ) . "\"
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
	$countriesArray = array ();

	// First find a list of all countries
	$all = array_count_values($objDatabase->selectSingleArray ( "select locations.country from observations join locations on observations.locationid=locations.id where ((observations.observerid=\"" . $user . "\"))", "country"));
	$allComets = array_count_values($objDatabase->selectSingleArray ( "select locations.country from cometobservations join locations on cometobservations.locationid=locations.id where ((cometobservations.observerid=\"" . $user . "\"))", "country"));

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
						text: \"" . GraphObservationsPerCountry . ": " . html_entity_decode ( $firstname, ENT_QUOTES, "UTF-8" ) . " " . html_entity_decode ( $name, ENT_QUOTES, "UTF-8" ) . "\"
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

	foreach ( $countriesArray as $key => $value ) {
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

	// Draw the stars
	echo "<div class=\"tab-pane\" id=\"stars\">";

	// Messier
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangMessier . "</h4>";

	$accomplishments = $objAccomplishments->getAllAccomplishments ( $user );

	drawStar ( $accomplishments['messierBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsMessierBronze, LangMessierBronzeToAccomplish );
	drawStar ( $accomplishments['messierSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsMessierSilver, LangMessierSilverToAccomplish );
	drawStar ( $accomplishments['messierGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsMessierGold, LangMessierGoldToAccomplish );

	echo "</div>";

	// Messier Drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangMessierDrawings . "</h4>";

	drawStar ( $accomplishments['messierDrawingsBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsMessierBronzeDr, LangMessierBronzeToAccomplishDr );
	drawStar ( $accomplishments['messierDrawingsSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsMessierSilverDr, LangMessierSilverToAccomplishDr );
	drawStar ( $accomplishments['messierDrawingsGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsMessierGoldDr, LangMessierGoldToAccomplishDr );
	echo "</div>";

	// Caldwell
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangCaldwell . "</h4>";

	drawStar ( $accomplishments['caldwellBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsCaldwellBronze, LangCaldwellBronzeToAccomplish );
	drawStar ( $accomplishments['caldwellSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsCaldwellSilver, LangCaldwellSilverToAccomplish );
	drawStar ( $accomplishments['caldwellGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsCaldwellGold, LangCaldwellGoldToAccomplish );
	echo "</div>";

	// Caldwell drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangCaldwellDrawings . "</h4>";

	drawStar ( $accomplishments['caldwellDrawingsBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsCaldwellBronzeDr, LangCaldwellBronzeToAccomplishDr );
	drawStar ( $accomplishments['caldwellDrawingsSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsCaldwellSilverDr, LangCaldwellSilverToAccomplishDr );
	drawStar ( $accomplishments['caldwelldrawingsGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsCaldwellGoldDr, LangCaldwellGoldToAccomplishDr );
	echo "</div>";

	// Herschel - 400
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschel400 . "</h4>";

	drawStar ( $accomplishments['herschelBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsH400Bronze, LangH400BronzeToAccomplish );
	drawStar ( $accomplishments['herschelSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsH400Silver, LangH400SilverToAccomplish );
	drawStar ( $accomplishments['herschelGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsH400Gold, LangH400GoldToAccomplish );
	drawStar ( $accomplishments['herschelDiamond'], LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsH400Diamond, LangH400DiamondToAccomplish );
	drawStar ( $accomplishments['herschelPlatina'], LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsH400Platina, LangH400PlatinaToAccomplish );
	echo "</div>";

	// Herschel 400 drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschel400Drawings . "</h4>";

	drawStar ( $accomplishments['herschelDrawingsBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsH400BronzeDr, LangH400BronzeToAccomplishDr );
	drawStar ( $accomplishments['herschelDrawingsSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsH400SilverDr, LangH400SilverToAccomplishDr );
	drawStar ( $accomplishments['herschelDrawingsGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsH400GoldDr, LangH400GoldToAccomplishDr );
	drawStar ( $accomplishments['herschelDrawingsDiamond'], LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsH400DiamondDr, LangH400DiamondToAccomplishDr );
	drawStar ( $accomplishments['herschelDrawingsPlatina'], LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsH400PlatinaDr, LangH400PlatinaToAccomplishDr );
	echo "</div>";

	// Herschel II
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschelII . "</h4>";

	drawStar ( $accomplishments['herschelIIBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsHIIBronze, LangHIIBronzeToAccomplish );
	drawStar ( $accomplishments['herschelIISilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsHIISilver, LangHIISilverToAccomplish );
	drawStar ( $accomplishments['herschelIIGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsHIIGold, LangHIIGoldToAccomplish );
	drawStar ( $accomplishments['herschelIIDiamond'], LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsHIIDiamond, LangHIIDiamondToAccomplish );
	drawStar ( $accomplishments['herschelIIPlatina'], LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsHIIPlatina, LangHIIPlatinaToAccomplish );
	echo "</div>";

	// Herschel II drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschelIIDrawings . "</h4>";

	drawStar ( $accomplishments['herschelIIDrawingsBronze'], LangAccomplishmentsBronze, "bronze", LangAccomplishmentsHIIBronzeDr, LangHIIBronzeToAccomplishDr );
	drawStar ( $accomplishments['herschelIIDrawingsSilver'], LangAccomplishmentsSilver, "silver", LangAccomplishmentsHIISilverDr, LangHIISilverToAccomplishDr );
	drawStar ( $accomplishments['herschelIIDrawingsGold'], LangAccomplishmentsGold, "gold", LangAccomplishmentsHIIGoldDr, LangHIIGoldToAccomplishDr );
	drawStar ( $accomplishments['herschelIIDrawingsDiamond'], LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsHIIDiamondDr, LangHIIDiamondToAccomplishDr );
	drawStar ( $accomplishments['herschelIIDrawingsPlatina'], LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsHIIPlatinaDr, LangHIIPlatinaToAccomplishDr );
	echo "</div>";

	// Total number of drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangTotalDrawings . "</h4>";

	// TODO: Use the method getNumberOfObjectsInCatalog in accomplishments to calculate the number of objects in the catalog.
	// TODO: Refactor to use less code ;-)
	drawStar ( $accomplishments['drawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['drawingsRookie'], 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $accomplishments['drawingsBeginner'], 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $accomplishments['drawingsTalented'], 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $accomplishments['drawingsSkilled'], 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $accomplishments['drawingsIntermediate'], 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $accomplishments['drawingsExperienced'], 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $accomplishments['drawingsAdvanced'], 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['drawingsSenior'], 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['drawingsExpert'], 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of open clusters
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangOpenClusters . "</h4>";

	drawStar ( $accomplishments['openClusterNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['openClusterRookie'], ( int ) (1700 / 500), "rookie", $objUtil->getSeenAccomplishment ( 1700 / 500 ), $objUtil->getSeenToAccomplish ( 1700 / 500 ) );
	drawStar ( $accomplishments['openClusterBeginner'], ( int ) (1700 / 200), "beginner", $objUtil->getSeenAccomplishment ( 1700 / 200 ), $objUtil->getSeenToAccomplish ( 1700 / 200 ) );
	drawStar ( $accomplishments['openClusterTalented'], ( int ) (1700 / 100), "talented", $objUtil->getSeenAccomplishment ( 1700 / 100 ), $objUtil->getSeenToAccomplish ( 1700 / 100 ) );
	drawStar ( $accomplishments['openClusterSkilled'], ( int ) (1700 / 50), "skilled", $objUtil->getSeenAccomplishment ( 1700 / 50 ), $objUtil->getSeenToAccomplish ( 1700 / 50 ) );
	drawStar ( $accomplishments['openClusterIntermediate'], ( int ) (1700 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 1700 / 20 ), $objUtil->getSeenToAccomplish ( 1700 / 20 ) );
	drawStar ( $accomplishments['openClusterExperienced'], ( int ) (1700 / 10), "experienced", $objUtil->getSeenAccomplishment ( 1700 / 10 ), $objUtil->getSeenToAccomplish ( 1700 / 10 ) );
	drawStar ( $accomplishments['openClusterAdvanced'], ( int ) (1700 / 5), "advanced", $objUtil->getSeenAccomplishment ( 1700 / 5 ), $objUtil->getSeenToAccomplish ( 1700 / 5 ) );
	drawStar ( $accomplishments['openClusterSenior'], ( int ) (1700 / 2), "senior", $objUtil->getSeenAccomplishment ( 1700 / 2 ), $objUtil->getSeenToAccomplish ( 1700 / 2 ) );
	drawStar ( $accomplishments['openClusterExpert'], 1700, "expert", $objUtil->getSeenAccomplishment ( 1700 ), $objUtil->getSeenToAccomplish ( 1700 ) );
	echo "</div>";

	// Total number of open clusters drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangOpenClusterDrawings . "</h4>";

	drawStar ( $accomplishments['openClusterDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['openClusterDrawingsRookie'], ( int ) (1700 / 500), "rookie", $objUtil->getDrawAccomplishment ( 1700 / 500 ), $objUtil->getDrawToAccomplish ( 1700 / 500 ) );
	drawStar ( $accomplishments['openClusterDrawingsBeginner'], ( int ) (1700 / 200), "beginner", $objUtil->getDrawAccomplishment ( 1700 / 200 ), $objUtil->getDrawToAccomplish ( 1700 / 200 ) );
	drawStar ( $accomplishments['openClusterDrawingsTalented'], ( int ) (1700 / 100), "talented", $objUtil->getDrawAccomplishment ( 1700 / 100 ), $objUtil->getDrawToAccomplish ( 1700 / 100 ) );
	drawStar ( $accomplishments['openClusterDrawingsSkilled'], ( int ) (1700 / 50), "skilled", $objUtil->getDrawAccomplishment ( 1700 / 50 ), $objUtil->getDrawToAccomplish ( 1700 / 50 ) );
	drawStar ( $accomplishments['openClusterDrawingsIntermediate'], ( int ) (1700 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 1700 / 20 ), $objUtil->getDrawToAccomplish ( 1700 / 20 ) );
	drawStar ( $accomplishments['openClusterDrawingsExperienced'], ( int ) (1700 / 10), "experienced", $objUtil->getDrawAccomplishment ( 1700 / 10 ), $objUtil->getDrawToAccomplish ( 1700 / 10 ) );
	drawStar ( $accomplishments['openClusterDrawingsAdvanced'], ( int ) (1700 / 5), "advanced", $objUtil->getDrawAccomplishment ( 1700 / 5 ), $objUtil->getDrawToAccomplish ( 1700 / 5 ) );
	drawStar ( $accomplishments['openClusterDrawingsSenior'], ( int ) (1700 / 2), "senior", $objUtil->getDrawAccomplishment ( 1700 / 2 ), $objUtil->getDrawToAccomplish ( 1700 / 2 ) );
	drawStar ( $accomplishments['openClusterDrawingsExpert'], 1700, "expert", $objUtil->getDrawAccomplishment ( 1700 ), $objUtil->getDrawToAccomplish ( 1700 ) );

	echo "</div>";

	// Total number of globular clusters
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGlobularClusters . "</h4>";

	drawStar ( $accomplishments['globularClusterNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['globularClusterRookie'], 2, "rookie", $objUtil->getSeenAccomplishment ( 2 ), $objUtil->getSeenToAccomplish ( 2 ) );
	drawStar ( $accomplishments['globularClusterBeginner'], 3, "beginner", $objUtil->getSeenAccomplishment ( 3 ), $objUtil->getSeenToAccomplish ( 3 ) );
	drawStar ( $accomplishments['globularClusterTalented'], 4, "talented", $objUtil->getSeenAccomplishment ( 4 ), $objUtil->getSeenToAccomplish ( 4 ) );
	drawStar ( $accomplishments['globularClusterSkilled'], 5, "skilled", $objUtil->getSeenAccomplishment ( 5 ), $objUtil->getSeenToAccomplish ( 5 ) );
	drawStar ( $accomplishments['globularClusterIntermediate'], ( int ) (152 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 152 / 20 ), $objUtil->getSeenToAccomplish ( 152 / 20 ) );
	drawStar ( $accomplishments['globularClusterExperienced'], ( int ) (152 / 10), "experienced", $objUtil->getSeenAccomplishment ( 152 / 10 ), $objUtil->getSeenToAccomplish ( 152 / 10 ) );
	drawStar ( $accomplishments['globularClusterAdvanced'], ( int ) (152 / 5), "advanced", $objUtil->getSeenAccomplishment ( 152 / 5 ), $objUtil->getSeenToAccomplish ( 152 / 5 ) );
	drawStar ( $accomplishments['globularClusterSenior'], ( int ) (152 / 2), "senior", $objUtil->getSeenAccomplishment ( 1700 / 2 ), $objUtil->getSeenToAccomplish ( 152 / 2 ) );
	drawStar ( $accomplishments['globularClusterExpert'], 152, "expert", $objUtil->getSeenAccomplishment ( 152 ), $objUtil->getSeenToAccomplish ( 152 ) );
	echo "</div>";

	// Total number of globular clusters drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGlobularClusterDrawings . "</h4>";

	drawStar ( $accomplishments['globularClusterDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['globularClusterDrawingsRookie'], 2, "rookie", $objUtil->getDrawAccomplishment ( 2 ), $objUtil->getDrawToAccomplish ( 2 ) );
	drawStar ( $accomplishments['globularClusterDrawingsBeginner'], 3, "beginner", $objUtil->getDrawAccomplishment ( 3 ), $objUtil->getDrawToAccomplish ( 3 ) );
	drawStar ( $accomplishments['globularClusterDrawingsTalented'], 4, "talented", $objUtil->getDrawAccomplishment ( 4 ), $objUtil->getDrawToAccomplish ( 4 ) );
	drawStar ( $accomplishments['globularClusterDrawingsSkilled'], 5, "skilled", $objUtil->getDrawAccomplishment ( 5 ), $objUtil->getDrawToAccomplish ( 5 ) );
	drawStar ( $accomplishments['globularClusterDrawingsIntermediate'], ( int ) (152 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 152 / 20 ), $objUtil->getDrawToAccomplish ( 152 / 20 ) );
	drawStar ( $accomplishments['globularClusterDrawingsExperienced'], ( int ) (152 / 10), "experienced", $objUtil->getDrawAccomplishment ( 152 / 10 ), $objUtil->getDrawToAccomplish ( 152 / 10 ) );
	drawStar ( $accomplishments['globularClusterDrawingsAdvanced'], ( int ) (152 / 5), "advanced", $objUtil->getDrawAccomplishment ( 152 / 5 ), $objUtil->getDrawToAccomplish ( 152 / 5 ) );
	drawStar ( $accomplishments['globularClusterDrawingsSenior'], ( int ) (152 / 2), "senior", $objUtil->getDrawAccomplishment ( 152 / 2 ), $objUtil->getDrawToAccomplish ( 152 / 2 ) );
	drawStar ( $accomplishments['globularClusterDrawingsExpert'], 152, "expert", $objUtil->getDrawAccomplishment ( 152 ), $objUtil->getDrawToAccomplish ( 152 ) );

	echo "</div>";

	// Total number of planetary nebulae
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangPlanetaryNebulaeSeen . "</h4>";

	drawStar ( $accomplishments['planetaryNebulaNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['planetaryNebulaRookie'], ( int ) (1023 / 500), "rookie", $objUtil->getSeenAccomplishment ( 1023 / 500 ), $objUtil->getSeenToAccomplish ( 1023 / 500 ) );
	drawStar ( $accomplishments['planetaryNebulaBeginner'], ( int ) (1023 / 200), "beginner", $objUtil->getSeenAccomplishment ( 1023 / 200 ), $objUtil->getSeenToAccomplish ( 1023 / 200 ) );
	drawStar ( $accomplishments['planetaryNebulaTalented'], ( int ) (1023 / 100), "talented", $objUtil->getSeenAccomplishment ( 1023 / 100 ), $objUtil->getSeenToAccomplish ( 1023 / 100 ) );
	drawStar ( $accomplishments['planetaryNebulaSkilled'], ( int ) (1023 / 50), "skilled", $objUtil->getSeenAccomplishment ( 1023 / 50 ), $objUtil->getSeenToAccomplish ( 1023 / 50 ) );
	drawStar ( $accomplishments['planetaryNebulaIntermediate'], ( int ) (1023 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 1023 / 20 ), $objUtil->getSeenToAccomplish ( 1023 / 20 ) );
	drawStar ( $accomplishments['planetaryNebulaExperienced'], ( int ) (1023 / 10), "experienced", $objUtil->getSeenAccomplishment ( 1023 / 10 ), $objUtil->getSeenToAccomplish ( 1023 / 10 ) );
	drawStar ( $accomplishments['planetaryNebulaAdvanced'], ( int ) (1023 / 5), "advanced", $objUtil->getSeenAccomplishment ( 1023 / 5 ), $objUtil->getSeenToAccomplish ( 1023 / 5 ) );
	drawStar ( $accomplishments['planetaryNebulaSenior'], ( int ) (1023 / 2), "senior", $objUtil->getSeenAccomplishment ( 1023 / 2 ), $objUtil->getSeenToAccomplish ( 1023 / 2 ) );
	drawStar ( $accomplishments['planetaryNebulaExpert'], 1023, "expert", $objUtil->getSeenAccomplishment ( 1023 ), $objUtil->getSeenToAccomplish ( 1023 ) );
	echo "</div>";

	// Total number of planetary nebulae drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangPlanetaryNebulaDrawings . "</h4>";

	drawStar ( $accomplishments['planetaryNebulaDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsRookie'], ( int ) (1023 / 500), "rookie", $objUtil->getDrawAccomplishment ( 1023 / 500 ), $objUtil->getDrawToAccomplish ( 1023 / 500 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsBeginner'], ( int ) (1023 / 200), "beginner", $objUtil->getDrawAccomplishment ( 1023 / 200 ), $objUtil->getDrawToAccomplish ( 1023 / 200 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsTalented'], ( int ) (1023 / 100), "talented", $objUtil->getDrawAccomplishment ( 1023 / 100 ), $objUtil->getDrawToAccomplish ( 1023 / 100 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsSkilled'], ( int ) (1023 / 50), "skilled", $objUtil->getDrawAccomplishment ( 1023 / 50 ), $objUtil->getDrawToAccomplish ( 1023 / 50 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsIntermediate'], ( int ) (1023 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 1023 / 20 ), $objUtil->getDrawToAccomplish ( 1023 / 20 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsExperienced'], ( int ) (1023 / 10), "experienced", $objUtil->getDrawAccomplishment ( 1023 / 10 ), $objUtil->getDrawToAccomplish ( 1023 / 10 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsAdvanced'], ( int ) (1023 / 5), "advanced", $objUtil->getDrawAccomplishment ( 1023 / 5 ), $objUtil->getDrawToAccomplish ( 1023 / 5 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsSenior'], ( int ) (1023 / 2), "senior", $objUtil->getDrawAccomplishment ( 1023 / 2 ), $objUtil->getDrawToAccomplish ( 1023 / 2 ) );
	drawStar ( $accomplishments['planetaryNebulaDrawingsExpert'], 1023, "expert", $objUtil->getDrawAccomplishment ( 1023 ), $objUtil->getDrawToAccomplish ( 1023 ) );

	echo "</div>";

	// Total number of galaxies
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGalaxiesSeen . "</h4>";

	drawStar ( $accomplishments['galaxyNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['galaxyRookie'], 10, "rookie", $objUtil->getSeenAccomplishment ( 5000 / 500 ), $objUtil->getSeenToAccomplish ( 10 ) );
	drawStar ( $accomplishments['galaxyBeginner'], 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $accomplishments['galaxyTalented'], 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $accomplishments['galaxySkilled'], 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $accomplishments['galaxyIntermediate'], 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $accomplishments['galaxyExperienced'], 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $accomplishments['galaxyAdvanced'], 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['galaxySenior'], 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['galaxyExpert'], 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );
	echo "</div>";

	// Total number of galaxies drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGalaxyDrawings . "</h4>";

	drawStar ( $accomplishments['galaxyDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['galaxyDrawingsRookie'], 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $accomplishments['galaxyDrawingsBeginner'], 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $accomplishments['galaxyDrawingsTalented'], 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $accomplishments['galaxyDrawingsSkilled'], 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $accomplishments['galaxyDrawingsIntermediate'], 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $accomplishments['galaxyDrawingsExperienced'], 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $accomplishments['galaxyDrawingsAdvanced'], 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['galaxyDrawingsSenior'], 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['galaxyDrawingsExpert'], 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of nebulae
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangNebulaeSeen . "</h4>";

	drawStar ( $accomplishments['nebulaNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['nebulaRookie'], 2, "rookie", $objUtil->getSeenAccomplishment ( 2 ), $objUtil->getSeenToAccomplish ( 2 ) );
	drawStar ( $accomplishments['nebulaBeginner'], 3, "beginner", $objUtil->getSeenAccomplishment ( 3 ), $objUtil->getSeenToAccomplish ( 3 ) );
	drawStar ( $accomplishments['nebulaTalented'], 4, "talented", $objUtil->getSeenAccomplishment ( 4 ), $objUtil->getSeenToAccomplish ( 4 ) );
	drawStar ( $accomplishments['nebulaSkilled'], ( int ) (384 / 50), "skilled", $objUtil->getSeenAccomplishment ( 384 / 50 ), $objUtil->getSeenToAccomplish ( 384 / 50 ) );
	drawStar ( $accomplishments['nebulaIntermediate'], ( int ) (384 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 384 / 20 ), $objUtil->getSeenToAccomplish ( 384 / 20 ) );
	drawStar ( $accomplishments['nebulaExperienced'], ( int ) (384 / 10), "experienced", $objUtil->getSeenAccomplishment ( 384 / 10 ), $objUtil->getSeenToAccomplish ( 384 / 10 ) );
	drawStar ( $accomplishments['nebulaAdvanced'], ( int ) (384 / 5), "advanced", $objUtil->getSeenAccomplishment ( 384 / 5 ), $objUtil->getSeenToAccomplish ( 384 / 5 ) );
	drawStar ( $accomplishments['nebulaSenior'], ( int ) (384 / 2), "senior", $objUtil->getSeenAccomplishment ( 384 / 2 ), $objUtil->getSeenToAccomplish ( 384 / 2 ) );
	drawStar ( $accomplishments['nebulaExpert'], 384, "expert", $objUtil->getSeenAccomplishment ( 384 ), $objUtil->getSeenToAccomplish ( 384 ) );
	echo "</div>";

	// Total number of nebulae drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangNebulaeDrawings . "</h4>";

	drawStar ( $accomplishments['nebulaDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['nebulaDrawingsRookie'], 2, "rookie", $objUtil->getDrawAccomplishment ( 2 ), $objUtil->getDrawToAccomplish ( 2 ) );
	drawStar ( $accomplishments['nebulaDrawingsBeginner'], 3, "beginner", $objUtil->getDrawAccomplishment ( 3 ), $objUtil->getDrawToAccomplish ( 3 ) );
	drawStar ( $accomplishments['nebulaDrawingsTalented'], 4, "talented", $objUtil->getDrawAccomplishment ( 4 ), $objUtil->getDrawToAccomplish ( 4 ) );
	drawStar ( $accomplishments['nebulaDrawingsSkilled'], ( int ) (384 / 50), "skilled", $objUtil->getDrawAccomplishment ( 384 / 50 ), $objUtil->getDrawToAccomplish ( 384 / 50 ) );
	drawStar ( $accomplishments['nebulaDrawingsIntermediate'], ( int ) (384 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 384 / 20 ), $objUtil->getDrawToAccomplish ( 384 / 20 ) );
	drawStar ( $accomplishments['nebulaDrawingsExperienced'], ( int ) (384 / 10), "experienced", $objUtil->getDrawAccomplishment ( 384 / 10 ), $objUtil->getDrawToAccomplish ( 384 / 10 ) );
	drawStar ( $accomplishments['nebulaDrawingsAdvanced'], ( int ) (384 / 5), "advanced", $objUtil->getDrawAccomplishment ( 384 / 5 ), $objUtil->getDrawToAccomplish ( 384 / 5 ) );
	drawStar ( $accomplishments['nebulaDrawingsSenior'], ( int ) (384 / 2), "senior", $objUtil->getDrawAccomplishment ( 384 / 2 ), $objUtil->getDrawToAccomplish ( 384 / 2 ) );
	drawStar ( $accomplishments['nebulaDrawingsExpert'], 384, "expert", $objUtil->getDrawAccomplishment ( 384 ), $objUtil->getDrawToAccomplish ( 384 ) );

	echo "</div>";

	// Total number of different objects
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangDifferentObjectsSeen . "</h4>";

	drawStar ( $accomplishments['objectsNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['objectsRookie'], ( int ) (5000 / 500), "rookie", $objUtil->getSeenAccomplishment ( 5000 / 500 ), $objUtil->getSeenToAccomplish ( 5000 / 500 ) );
	drawStar ( $accomplishments['objectsBeginner'], 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $accomplishments['objectsTalented'], 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $accomplishments['objectsSkilled'], 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $accomplishments['objectsIntermediate'], 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $accomplishments['objectsExperienced'], 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $accomplishments['objectsAdvanced'], 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['objectsSenior'], 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['objectsExpert'], 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );
	echo "</div>";

	// Total number of nebulae drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangDifferentObjectsDrawings . "</h4>";

	drawStar ( $accomplishments['objectsDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['objectsDrawingsRookie'], 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $accomplishments['objectsDrawingsBeginner'], 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $accomplishments['objectsDrawingsTalented'], 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $accomplishments['objectsDrawingsSkilled'], 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $accomplishments['objectsDrawingsIntermediate'], 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $accomplishments['objectsDrawingsExperienced'], 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $accomplishments['objectsDrawingsAdvanced'], 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['objectsDrawingsSenior'], 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['objectsDrawingsExpert'], 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of comet observations
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangTotalCometsSeen . "</h4>";

	drawStar ( $accomplishments['cometObservationsNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['cometObservationsRookie'], ( int ) (5000 / 500), "rookie", $objUtil->getSeenAccomplishment ( 5000 / 500 ), $objUtil->getSeenToAccomplish ( 5000 / 500 ) );
	drawStar ( $accomplishments['cometObservationsBeginner'], 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $accomplishments['cometObservationsTalented'], 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $accomplishments['cometObservationsSkilled'], 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $accomplishments['cometObservationsIntermediate'], 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $accomplishments['cometObservationsExperienced'], 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $accomplishments['cometObservationsAdvanced'], 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['cometObservationsSenior'], 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['cometObservationsExpert'], 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );
	echo "</div>";

	// Total number of different comets seen
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangDifferentCometsSeen . "</h4>";

	drawStar ( $accomplishments['cometsObservedNewbie'], 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $accomplishments['cometsObservedRookie'], 10, "rookie", $objUtil->getSeenAccomplishment ( 10 ), $objUtil->getSeenToAccomplish ( 10 ) );
	drawStar ( $accomplishments['cometsObservedBeginner'], 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $accomplishments['cometsObservedTalented'], 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $accomplishments['cometsObservedSkilled'], 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $accomplishments['cometsObservedIntermediate'], 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $accomplishments['cometsObservedExperienced'], 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $accomplishments['cometsObservedAdvanced'], 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['cometsObservedSenior'], 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['cometsObservedExpert'], 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of different comet drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangCometDrawings . "</h4>";

	drawStar ( $accomplishments['cometDrawingsNewbie'], 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $accomplishments['cometDrawingsRookie'], 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $accomplishments['cometDrawingsBeginner'], 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $accomplishments['cometDrawingsTalented'], 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $accomplishments['cometDrawingsSkilled'], 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $accomplishments['cometDrawingsIntermediate'], 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $accomplishments['cometDrawingsExperienced'], 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $accomplishments['cometDrawingsAdvanced'], 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $accomplishments['cometDrawingsSenior'], 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $accomplishments['cometDrawingsExpert'], 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	echo "</div>";
	echo "<br />";

	echo "</div>";
	echo "</div>";
}
function drawStar($done, $text, $color, $tooltip, $tooltipToDo) {
	global $baseURL;

	if ($done) {
		print "<div class=\"star\" id=\"" . $color . "\">";
		print "<div class=\"accomplishmentText\" title=\"" . $tooltip . "\">" . ucfirst ( $text ) . "</div>";
		print "</div>";
	} else {
		print "<div class=\"star notAccomplished\" id=\"" . $color . "\">";
		print "<div class=\"accomplishmentText notAccomplished\" title=\"" . $tooltipToDo . "\">" . ucfirst ( $text ) . "</div>";
		print "</div>";
	}
}

?>
