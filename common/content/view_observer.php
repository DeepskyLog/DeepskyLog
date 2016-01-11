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
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == RoleAdmin) ? "selected=\"selected\"" : "") . " value=\"0\">" . LangViewObserverAdmin . "</option>
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == RoleUser) ? "selected=\"selected\"" : "") . " value=\"1\">" . LangViewObserverUser . "</option>
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == RoleCometAdmin) ? "selected=\"selected\"" : "") . " value=\"4\">" . LangViewObserverCometAdmin . "</option>
 	            <option " . (($objObserver->getObserverProperty ( $user, 'role', 2 ) == RoleWaitlist) ? "selected=\"selected\"" : "") . " value=\"2\">" . LangViewObserverWaitlist . "</option>
 	          </select>&nbsp;
           </div>
           <div class=\"col-sm-2\">
                <button type=\"submit\" class=\"btn btn-default\" name=\"change\">" . LangViewObserverChange . "</button>
           </div>
	        </div>";
		} elseif ($objObserver->getObserverProperty ( $user, 'role', 2 ) == RoleWaitlist) {
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
	$startYear = min ( [min(array_keys($sql)), min(array_keys ( $sql2 ) )] );
	// Add the JavaScript to initialize the chart on document ready
	echo "<script type=\"text/javascript\">

	  	      var chart;
						var dataYear = [";
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
	  	          marginBottom: 25
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
	echo "<script type=\"text/javascript\">
	  	      var chart;
						var data = [";
						for($i = 1; $i <= 12; $i ++) {
							$obs = $objDatabase->selectSingleValue ( "select COUNT(date) from observations where observerid=\"" . $user . "\" and MONTH(date) = \"" . $i . "\";", "COUNT(date)", "0" );
							if ($i != 12) {
								echo $obs . ", ";
							} else {
								echo $obs;
							}
						}
						echo "];
						var cometdata = [";
							for($i = 1; $i <= 12; $i ++) {
								$obs = $objDatabase->selectSingleValue ( "select COUNT(date) from cometobservations where observerid=\"" . $user . "\" and MONTH(date) = \"" . $i . "\";", "COUNT(date)", "0" );
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
	$objectsArray = array ();
	$colors = Array ();

	$all = count ( $objDatabase->selectRecordsetArray ( "select * from observations where observerid=\"" . $user . "\"" ) );
	if ($all == 0) {
		$all = 1;
	}
	$rest = 0;

	$cometobservations = count ( $objDatabase->selectRecordsetArray ( "select * from cometobservations where observerid = \"" . $user . "\"" ) );
	$all += $cometobservations;

	if (($cometobservations / $all) >= 0.01) {
		$objectsArray ["comets"] = $cometobservations;
	} else {
		$rest += $cometobservations;
	}
	$colors ["comets"] = "#4572A7";

	$aster = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ASTER\" and observations.observerid = \"" . $user . "\"" ) );
	$aster += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA8STAR\" and observations.observerid = \"" . $user . "\"" ) );
	$aster += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA4STAR\" and observations.observerid = \"" . $user . "\"" ) );
	$aster += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA3STAR\" and observations.observerid = \"" . $user . "\"" ) );

	if (($aster / $all) >= 0.01) {
		$objectsArray ["ASTER"] = $aster;
	} else {
		$rest += $aster;
	}
	$colors ["ASTER"] = "#AA4643";

	$brtnb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"BRTNB\" and observations.observerid = \"" . $user . "\"" ) );

	if (($brtnb / $all) >= 0.01) {
		$objectsArray ["BRTNB"] = $brtnb;
	} else {
		$rest += $brtnb;
	}
	$colors ["BRTNB"] = "#89A54E";

	$ds = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"DS\" and observations.observerid = \"" . $user . "\"" ) );
	$ds += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA2STAR\" and observations.observerid = \"" . $user . "\"" ) );

	if (($ds / $all) >= 0.01) {
		$objectsArray ["DS"] = $ds;
	} else {
		$rest += $ds;
	}
	$colors ["DS"] = "#80699B";

	$star = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"AA1STAR\" and observations.observerid = \"" . $user . "\"" ) );

	if (($star / $all) >= 0.01) {
		$objectsArray ["AA1STAR"] = $star;
	} else {
		$rest += $star;
	}
	$colors ["AA1STAR"] = "#3D96AE";

	$drknb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"DRKNB\" and observations.observerid = \"" . $user . "\"" ) );

	if (($drknb / $all) >= 0.01) {
		$objectsArray ["DRKNB"] = $drknb;
	} else {
		$rest += $drknb;
	}
	$colors ["DRKNB"] = "#DB843D";

	$galcl = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GALCL\" and observations.observerid = \"" . $user . "\"" ) );

	if (($galcl / $all) >= 0.01) {
		$objectsArray ["GALCL"] = $galcl;
	} else {
		$rest += $galcl;
	}
	$colors ["GALCL"] = "#92A8CD";

	$galxy = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GALXY\" and observations.observerid = \"" . $user . "\"" ) );

	if (($galxy / $all) >= 0.01) {
		$objectsArray ["GALXY"] = $galxy;
	} else {
		$rest += $galxy;
	}
	$colors ["GALXY"] = "#68302F";

	$plnnb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"PLNNB\" and observations.observerid = \"" . $user . "\"" ) );

	if (($plnnb / $all) >= 0.01) {
		$objectsArray ["PLNNB"] = $plnnb;
	} else {
		$rest += $plnnb;
	}
	$colors ["PLNNB"] = "#A47D7C";

	$opncl = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"OPNCL\" and observations.observerid = \"" . $user . "\"" ) );
	$opncl += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"CLANB\" and observations.observerid = \"" . $user . "\"" ) );

	if (($opncl / $all) >= 0.01) {
		$objectsArray ["OPNCL"] = $opncl;
	} else {
		$rest += $opncl;
	}
	$colors ["OPNCL"] = "#B5CA92";

	$glocl = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"GLOCL\" and observations.observerid = \"" . $user . "\"" ) );

	if (($glocl / $all) >= 0.01) {
		$objectsArray ["GLOCL"] = $glocl;
	} else {
		$rest += $glocl;
	}
	$colors ["GLOCL"] = "#00FF00";

	$eminb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"EMINB\" and observations.observerid = \"" . $user . "\"" ) );
	$eminb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ENRNN\" and observations.observerid = \"" . $user . "\"" ) );
	$eminb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"ENSTR\" and observations.observerid = \"" . $user . "\"" ) );

	if (($eminb / $all) >= 0.01) {
		$objectsArray ["EMINB"] = $eminb;
	} else {
		$rest += $eminb;
	}
	$colors ["EMINB"] = "#C0FFC0";

	$refnb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"REFNB\" and observations.observerid = \"" . $user . "\"" ) );
	$refnb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"RNHII\" and observations.observerid = \"" . $user . "\"" ) );
	$refnb += count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"HII\" and observations.observerid = \"" . $user . "\"" ) );

	if (($refnb / $all) >= 0.01) {
		$objectsArray ["REFNB"] = $refnb;
	} else {
		$rest += $refnb;
	}
	$colors ["REFNB"] = "#0000C0";

	$nonex = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"NONEX\" and observations.observerid = \"" . $user . "\"" ) );

	if (($nonex / $all) >= 0.01) {
		$objectsArray ["NONEX"] = $nonex;
	} else {
		$rest += $nonex;
	}
	$colors ["NONEX"] = "#C0C0FF";

	$snrem = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"SNREM\" and observations.observerid = \"" . $user . "\"" ) );

	if (($snrem / $all) >= 0.01) {
		$objectsArray ["SNREM"] = $snrem;
	} else {
		$rest += $snrem;
	}
	$colors ["SNREM"] = "#808000";

	$quasr = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"QUASR\" and observations.observerid = \"" . $user . "\"" ) );

	if (($quasr / $all) >= 0.01) {
		$objectsArray ["QUASR"] = $quasr;
	} else {
		$rest += $quasr;
	}
	$colors ["QUASR"] = "#C0C000";

	$wrneb = count ( $objDatabase->selectRecordsetArray ( "select objects.* from objects,observations where objects.name = observations.objectname and objects.type = \"WRNEB\" and observations.observerid = \"" . $user . "\"" ) );

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

	drawStar ( $objAccomplishments->getMessierBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsMessierBronze, LangMessierBronzeToAccomplish );
	drawStar ( $objAccomplishments->getMessierSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsMessierSilver, LangMessierSilverToAccomplish );
	drawStar ( $objAccomplishments->getMessierGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsMessierGold, LangMessierGoldToAccomplish );

	echo "</div>";

	// Messier Drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangMessierDrawings . "</h4>";

	drawStar ( $objAccomplishments->getMessierDrawingsBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsMessierBronzeDr, LangMessierBronzeToAccomplishDr );
	drawStar ( $objAccomplishments->getMessierDrawingsSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsMessierSilverDr, LangMessierSilverToAccomplishDr );
	drawStar ( $objAccomplishments->getMessierDrawingsGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsMessierGoldDr, LangMessierGoldToAccomplishDr );
	echo "</div>";

	// Caldwell
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangCaldwell . "</h4>";

	drawStar ( $objAccomplishments->getCaldwellBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsCaldwellBronze, LangCaldwellBronzeToAccomplish );
	drawStar ( $objAccomplishments->getCaldwellSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsCaldwellSilver, LangCaldwellSilverToAccomplish );
	drawStar ( $objAccomplishments->getCaldwellGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsCaldwellGold, LangCaldwellGoldToAccomplish );
	echo "</div>";

	// Caldwell drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangCaldwellDrawings . "</h4>";

	drawStar ( $objAccomplishments->getCaldwellDrawingsBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsCaldwellBronzeDr, LangCaldwellBronzeToAccomplishDr );
	drawStar ( $objAccomplishments->getCaldwellDrawingsSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsCaldwellSilverDr, LangCaldwellSilverToAccomplishDr );
	drawStar ( $objAccomplishments->getCaldwellDrawingsGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsCaldwellGoldDr, LangCaldwellGoldToAccomplishDr );
	echo "</div>";

	// Herschel - 400
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschel400 . "</h4>";

	drawStar ( $objAccomplishments->getHerschelBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsH400Bronze, LangH400BronzeToAccomplish );
	drawStar ( $objAccomplishments->getHerschelSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsH400Silver, LangH400SilverToAccomplish );
	drawStar ( $objAccomplishments->getHerschelGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsH400Gold, LangH400GoldToAccomplish );
	drawStar ( $objAccomplishments->getHerschelDiamond ( $user ), LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsH400Diamond, LangH400DiamondToAccomplish );
	drawStar ( $objAccomplishments->getHerschelPlatina ( $user ), LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsH400Platina, LangH400PlatinaToAccomplish );
	echo "</div>";

	// Herschel 400 drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschel400Drawings . "</h4>";

	drawStar ( $objAccomplishments->getHerschelDrawingsBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsH400BronzeDr, LangH400BronzeToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelDrawingsSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsH400SilverDr, LangH400SilverToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelDrawingsGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsH400GoldDr, LangH400GoldToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelDrawingsDiamond ( $user ), LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsH400DiamondDr, LangH400DiamondToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelDrawingsPlatina ( $user ), LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsH400PlatinaDr, LangH400PlatinaToAccomplishDr );
	echo "</div>";

	// Herschel II
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschelII . "</h4>";

	drawStar ( $objAccomplishments->getHerschelIIBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsHIIBronze, LangHIIBronzeToAccomplish );
	drawStar ( $objAccomplishments->getHerschelIISilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsHIISilver, LangHIISilverToAccomplish );
	drawStar ( $objAccomplishments->getHerschelIIGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsHIIGold, LangHIIGoldToAccomplish );
	drawStar ( $objAccomplishments->getHerschelIIDiamond ( $user ), LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsHIIDiamond, LangHIIDiamondToAccomplish );
	drawStar ( $objAccomplishments->getHerschelIIPlatina ( $user ), LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsHIIPlatina, LangHIIPlatinaToAccomplish );
	echo "</div>";

	// Herschel II drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangHerschelIIDrawings . "</h4>";

	drawStar ( $objAccomplishments->getHerschelIIDrawingsBronze ( $user ), LangAccomplishmentsBronze, "bronze", LangAccomplishmentsHIIBronzeDr, LangHIIBronzeToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelIIDrawingsSilver ( $user ), LangAccomplishmentsSilver, "silver", LangAccomplishmentsHIISilverDr, LangHIISilverToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelIIDrawingsGold ( $user ), LangAccomplishmentsGold, "gold", LangAccomplishmentsHIIGoldDr, LangHIIGoldToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelIIDrawingsDiamond ( $user ), LangAccomplishmentsDiamond, "diamond", LangAccomplishmentsHIIDiamondDr, LangHIIDiamondToAccomplishDr );
	drawStar ( $objAccomplishments->getHerschelIIDrawingsPlatina ( $user ), LangAccomplishmentsPlatina, "platinum", LangAccomplishmentsHIIPlatinaDr, LangHIIPlatinaToAccomplishDr );
	echo "</div>";

	// Total number of drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangTotalDrawings . "</h4>";

	drawStar ( $objAccomplishments->getDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getDrawingsRookie ( $user ), 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $objAccomplishments->getDrawingsBeginner ( $user ), 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getDrawingsTalented ( $user ), 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getDrawingsSkilled ( $user ), 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getDrawingsIntermediate ( $user ), 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getDrawingsExperienced ( $user ), 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getDrawingsAdvanced ( $user ), 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getDrawingsSenior ( $user ), 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getDrawingsExpert ( $user ), 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of open clusters
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangOpenClusters . "</h4>";

	drawStar ( $objAccomplishments->getOpenClustersNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getOpenClustersRookie ( $user ), ( int ) (1700 / 500), "rookie", $objUtil->getSeenAccomplishment ( 1700 / 500 ), $objUtil->getSeenToAccomplish ( 1700 / 500 ) );
	drawStar ( $objAccomplishments->getOpenClustersBeginner ( $user ), ( int ) (1700 / 200), "beginner", $objUtil->getSeenAccomplishment ( 1700 / 200 ), $objUtil->getSeenToAccomplish ( 1700 / 200 ) );
	drawStar ( $objAccomplishments->getOpenClustersTalented ( $user ), ( int ) (1700 / 100), "talented", $objUtil->getSeenAccomplishment ( 1700 / 100 ), $objUtil->getSeenToAccomplish ( 1700 / 100 ) );
	drawStar ( $objAccomplishments->getOpenClustersSkilled ( $user ), ( int ) (1700 / 50), "skilled", $objUtil->getSeenAccomplishment ( 1700 / 50 ), $objUtil->getSeenToAccomplish ( 1700 / 50 ) );
	drawStar ( $objAccomplishments->getOpenClustersIntermediate ( $user ), ( int ) (1700 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 1700 / 20 ), $objUtil->getSeenToAccomplish ( 1700 / 20 ) );
	drawStar ( $objAccomplishments->getOpenClustersExperienced ( $user ), ( int ) (1700 / 10), "experienced", $objUtil->getSeenAccomplishment ( 1700 / 10 ), $objUtil->getSeenToAccomplish ( 1700 / 10 ) );
	drawStar ( $objAccomplishments->getOpenClustersAdvanced ( $user ), ( int ) (1700 / 5), "advanced", $objUtil->getSeenAccomplishment ( 1700 / 5 ), $objUtil->getSeenToAccomplish ( 1700 / 5 ) );
	drawStar ( $objAccomplishments->getOpenClustersSenior ( $user ), ( int ) (1700 / 2), "senior", $objUtil->getSeenAccomplishment ( 1700 / 2 ), $objUtil->getSeenToAccomplish ( 1700 / 2 ) );
	drawStar ( $objAccomplishments->getOpenClustersExpert ( $user ), 1700, "expert", $objUtil->getSeenAccomplishment ( 1700 ), $objUtil->getSeenToAccomplish ( 1700 ) );
	echo "</div>";

	// Total number of open clusters drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangOpenClusterDrawings . "</h4>";

	drawStar ( $objAccomplishments->getOpenClusterDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsRookie ( $user ), ( int ) (1700 / 500), "rookie", $objUtil->getDrawAccomplishment ( 1700 / 500 ), $objUtil->getDrawToAccomplish ( 1700 / 500 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsBeginner ( $user ), ( int ) (1700 / 200), "beginner", $objUtil->getDrawAccomplishment ( 1700 / 200 ), $objUtil->getDrawToAccomplish ( 1700 / 200 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsTalented ( $user ), ( int ) (1700 / 100), "talented", $objUtil->getDrawAccomplishment ( 1700 / 100 ), $objUtil->getDrawToAccomplish ( 1700 / 100 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsSkilled ( $user ), ( int ) (1700 / 50), "skilled", $objUtil->getDrawAccomplishment ( 1700 / 50 ), $objUtil->getDrawToAccomplish ( 1700 / 50 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsIntermediate ( $user ), ( int ) (1700 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 1700 / 20 ), $objUtil->getDrawToAccomplish ( 1700 / 20 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsExperienced ( $user ), ( int ) (1700 / 10), "experienced", $objUtil->getDrawAccomplishment ( 1700 / 10 ), $objUtil->getDrawToAccomplish ( 1700 / 10 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsAdvanced ( $user ), ( int ) (1700 / 5), "advanced", $objUtil->getDrawAccomplishment ( 1700 / 5 ), $objUtil->getDrawToAccomplish ( 1700 / 5 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsSenior ( $user ), ( int ) (1700 / 2), "senior", $objUtil->getDrawAccomplishment ( 1700 / 2 ), $objUtil->getDrawToAccomplish ( 1700 / 2 ) );
	drawStar ( $objAccomplishments->getOpenClusterDrawingsExpert ( $user ), 1700, "expert", $objUtil->getDrawAccomplishment ( 1700 ), $objUtil->getDrawToAccomplish ( 1700 ) );

	echo "</div>";

	// Total number of globular clusters
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGlobularClusters . "</h4>";

	drawStar ( $objAccomplishments->getGlobularClustersNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getGlobularClustersRookie ( $user ), 2, "rookie", $objUtil->getSeenAccomplishment ( 2 ), $objUtil->getSeenToAccomplish ( 2 ) );
	drawStar ( $objAccomplishments->getGlobularClustersBeginner ( $user ), 3, "beginner", $objUtil->getSeenAccomplishment ( 3 ), $objUtil->getSeenToAccomplish ( 3 ) );
	drawStar ( $objAccomplishments->getGlobularClustersTalented ( $user ), 4, "talented", $objUtil->getSeenAccomplishment ( 4 ), $objUtil->getSeenToAccomplish ( 4 ) );
	drawStar ( $objAccomplishments->getGlobularClustersSkilled ( $user ), 5, "skilled", $objUtil->getSeenAccomplishment ( 5 ), $objUtil->getSeenToAccomplish ( 5 ) );
	drawStar ( $objAccomplishments->getGlobularClustersIntermediate ( $user ), ( int ) (152 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 152 / 20 ), $objUtil->getSeenToAccomplish ( 152 / 20 ) );
	drawStar ( $objAccomplishments->getGlobularClustersExperienced ( $user ), ( int ) (152 / 10), "experienced", $objUtil->getSeenAccomplishment ( 152 / 10 ), $objUtil->getSeenToAccomplish ( 152 / 10 ) );
	drawStar ( $objAccomplishments->getGlobularClustersAdvanced ( $user ), ( int ) (152 / 5), "advanced", $objUtil->getSeenAccomplishment ( 152 / 5 ), $objUtil->getSeenToAccomplish ( 152 / 5 ) );
	drawStar ( $objAccomplishments->getGlobularClustersSenior ( $user ), ( int ) (152 / 2), "senior", $objUtil->getSeenAccomplishment ( 1700 / 2 ), $objUtil->getSeenToAccomplish ( 152 / 2 ) );
	drawStar ( $objAccomplishments->getGlobularClustersExpert ( $user ), 152, "expert", $objUtil->getSeenAccomplishment ( 152 ), $objUtil->getSeenToAccomplish ( 152 ) );
	echo "</div>";

	// Total number of globular clusters drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGlobularClusterDrawings . "</h4>";

	drawStar ( $objAccomplishments->getGlobularClusterDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsRookie ( $user ), 2, "rookie", $objUtil->getDrawAccomplishment ( 2 ), $objUtil->getDrawToAccomplish ( 2 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsBeginner ( $user ), 3, "beginner", $objUtil->getDrawAccomplishment ( 3 ), $objUtil->getDrawToAccomplish ( 3 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsTalented ( $user ), 4, "talented", $objUtil->getDrawAccomplishment ( 4 ), $objUtil->getDrawToAccomplish ( 4 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsSkilled ( $user ), 5, "skilled", $objUtil->getDrawAccomplishment ( 5 ), $objUtil->getDrawToAccomplish ( 5 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsIntermediate ( $user ), ( int ) (152 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 152 / 20 ), $objUtil->getDrawToAccomplish ( 152 / 20 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsExperienced ( $user ), ( int ) (152 / 10), "experienced", $objUtil->getDrawAccomplishment ( 152 / 10 ), $objUtil->getDrawToAccomplish ( 152 / 10 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsAdvanced ( $user ), ( int ) (152 / 5), "advanced", $objUtil->getDrawAccomplishment ( 152 / 5 ), $objUtil->getDrawToAccomplish ( 152 / 5 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsSenior ( $user ), ( int ) (152 / 2), "senior", $objUtil->getDrawAccomplishment ( 152 / 2 ), $objUtil->getDrawToAccomplish ( 152 / 2 ) );
	drawStar ( $objAccomplishments->getGlobularClusterDrawingsExpert ( $user ), 152, "expert", $objUtil->getDrawAccomplishment ( 152 ), $objUtil->getDrawToAccomplish ( 152 ) );

	echo "</div>";

	// Total number of planetary nebulae
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangPlanetaryNebulaeSeen . "</h4>";

	drawStar ( $objAccomplishments->getPlanetaryNebulaNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaRookie ( $user ), ( int ) (1023 / 500), "rookie", $objUtil->getSeenAccomplishment ( 1023 / 500 ), $objUtil->getSeenToAccomplish ( 1023 / 500 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaBeginner ( $user ), ( int ) (1023 / 200), "beginner", $objUtil->getSeenAccomplishment ( 1023 / 200 ), $objUtil->getSeenToAccomplish ( 1023 / 200 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaTalented ( $user ), ( int ) (1023 / 100), "talented", $objUtil->getSeenAccomplishment ( 1023 / 100 ), $objUtil->getSeenToAccomplish ( 1023 / 100 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaSkilled ( $user ), ( int ) (1023 / 50), "skilled", $objUtil->getSeenAccomplishment ( 1023 / 50 ), $objUtil->getSeenToAccomplish ( 1023 / 50 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaIntermediate ( $user ), ( int ) (1023 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 1023 / 20 ), $objUtil->getSeenToAccomplish ( 1023 / 20 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaExperienced ( $user ), ( int ) (1023 / 10), "experienced", $objUtil->getSeenAccomplishment ( 1023 / 10 ), $objUtil->getSeenToAccomplish ( 1023 / 10 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaAdvanced ( $user ), ( int ) (1023 / 5), "advanced", $objUtil->getSeenAccomplishment ( 1023 / 5 ), $objUtil->getSeenToAccomplish ( 1023 / 5 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaSenior ( $user ), ( int ) (1023 / 2), "senior", $objUtil->getSeenAccomplishment ( 1023 / 2 ), $objUtil->getSeenToAccomplish ( 1023 / 2 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaExpert ( $user ), 1023, "expert", $objUtil->getSeenAccomplishment ( 1023 ), $objUtil->getSeenToAccomplish ( 1023 ) );
	echo "</div>";

	// Total number of planetary nebulae drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangPlanetaryNebulaDrawings . "</h4>";

	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsRookie ( $user ), ( int ) (1023 / 500), "rookie", $objUtil->getDrawAccomplishment ( 1023 / 500 ), $objUtil->getDrawToAccomplish ( 1023 / 500 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsBeginner ( $user ), ( int ) (1023 / 200), "beginner", $objUtil->getDrawAccomplishment ( 1023 / 200 ), $objUtil->getDrawToAccomplish ( 1023 / 200 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsTalented ( $user ), ( int ) (1023 / 100), "talented", $objUtil->getDrawAccomplishment ( 1023 / 100 ), $objUtil->getDrawToAccomplish ( 1023 / 100 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsSkilled ( $user ), ( int ) (1023 / 50), "skilled", $objUtil->getDrawAccomplishment ( 1023 / 50 ), $objUtil->getDrawToAccomplish ( 1023 / 50 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsIntermediate ( $user ), ( int ) (1023 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 1023 / 20 ), $objUtil->getDrawToAccomplish ( 1023 / 20 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsExperienced ( $user ), ( int ) (1023 / 10), "experienced", $objUtil->getDrawAccomplishment ( 1023 / 10 ), $objUtil->getDrawToAccomplish ( 1023 / 10 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsAdvanced ( $user ), ( int ) (1023 / 5), "advanced", $objUtil->getDrawAccomplishment ( 1023 / 5 ), $objUtil->getDrawToAccomplish ( 1023 / 5 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsSenior ( $user ), ( int ) (1023 / 2), "senior", $objUtil->getDrawAccomplishment ( 1023 / 2 ), $objUtil->getDrawToAccomplish ( 1023 / 2 ) );
	drawStar ( $objAccomplishments->getPlanetaryNebulaDrawingsExpert ( $user ), 1023, "expert", $objUtil->getDrawAccomplishment ( 1023 ), $objUtil->getDrawToAccomplish ( 1023 ) );

	echo "</div>";

	// Total number of galaxies
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGalaxiesSeen . "</h4>";

	drawStar ( $objAccomplishments->getGalaxyNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getGalaxyRookie ( $user ), 10, "rookie", $objUtil->getSeenAccomplishment ( 5000 / 500 ), $objUtil->getSeenToAccomplish ( 10 ) );
	drawStar ( $objAccomplishments->getGalaxyBeginner ( $user ), 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getGalaxyTalented ( $user ), 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getGalaxySkilled ( $user ), 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getGalaxyIntermediate ( $user ), 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getGalaxyExperienced ( $user ), 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getGalaxyAdvanced ( $user ), 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getGalaxySenior ( $user ), 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getGalaxyExpert ( $user ), 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );
	echo "</div>";

	// Total number of galaxies drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangGalaxyDrawings . "</h4>";

	drawStar ( $objAccomplishments->getGalaxyDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsRookie ( $user ), 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsBeginner ( $user ), 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsTalented ( $user ), 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsSkilled ( $user ), 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsIntermediate ( $user ), 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsExperienced ( $user ), 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsAdvanced ( $user ), 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsSenior ( $user ), 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getGalaxyDrawingsExpert ( $user ), 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of nebulae
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangNebulaeSeen . "</h4>";

	drawStar ( $objAccomplishments->getNebulaNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getNebulaRookie ( $user ), 2, "rookie", $objUtil->getSeenAccomplishment ( 2 ), $objUtil->getSeenToAccomplish ( 2 ) );
	drawStar ( $objAccomplishments->getNebulaBeginner ( $user ), 3, "beginner", $objUtil->getSeenAccomplishment ( 3 ), $objUtil->getSeenToAccomplish ( 3 ) );
	drawStar ( $objAccomplishments->getNebulaTalented ( $user ), 4, "talented", $objUtil->getSeenAccomplishment ( 4 ), $objUtil->getSeenToAccomplish ( 4 ) );
	drawStar ( $objAccomplishments->getNebulaSkilled ( $user ), ( int ) (384 / 50), "skilled", $objUtil->getSeenAccomplishment ( 384 / 50 ), $objUtil->getSeenToAccomplish ( 384 / 50 ) );
	drawStar ( $objAccomplishments->getNebulaIntermediate ( $user ), ( int ) (384 / 20), "intermediate", $objUtil->getSeenAccomplishment ( 384 / 20 ), $objUtil->getSeenToAccomplish ( 384 / 20 ) );
	drawStar ( $objAccomplishments->getNebulaExperienced ( $user ), ( int ) (384 / 10), "experienced", $objUtil->getSeenAccomplishment ( 384 / 10 ), $objUtil->getSeenToAccomplish ( 384 / 10 ) );
	drawStar ( $objAccomplishments->getNebulaAdvanced ( $user ), ( int ) (384 / 5), "advanced", $objUtil->getSeenAccomplishment ( 384 / 5 ), $objUtil->getSeenToAccomplish ( 384 / 5 ) );
	drawStar ( $objAccomplishments->getNebulaSenior ( $user ), ( int ) (384 / 2), "senior", $objUtil->getSeenAccomplishment ( 384 / 2 ), $objUtil->getSeenToAccomplish ( 384 / 2 ) );
	drawStar ( $objAccomplishments->getNebulaExpert ( $user ), 384, "expert", $objUtil->getSeenAccomplishment ( 384 ), $objUtil->getSeenToAccomplish ( 384 ) );
	echo "</div>";

	// Total number of nebulae drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangNebulaeDrawings . "</h4>";

	drawStar ( $objAccomplishments->getNebulaDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsRookie ( $user ), 2, "rookie", $objUtil->getDrawAccomplishment ( 2 ), $objUtil->getDrawToAccomplish ( 2 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsBeginner ( $user ), 3, "beginner", $objUtil->getDrawAccomplishment ( 3 ), $objUtil->getDrawToAccomplish ( 3 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsTalented ( $user ), 4, "talented", $objUtil->getDrawAccomplishment ( 4 ), $objUtil->getDrawToAccomplish ( 4 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsSkilled ( $user ), ( int ) (384 / 50), "skilled", $objUtil->getDrawAccomplishment ( 384 / 50 ), $objUtil->getDrawToAccomplish ( 384 / 50 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsIntermediate ( $user ), ( int ) (384 / 20), "intermediate", $objUtil->getDrawAccomplishment ( 384 / 20 ), $objUtil->getDrawToAccomplish ( 384 / 20 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsExperienced ( $user ), ( int ) (384 / 10), "experienced", $objUtil->getDrawAccomplishment ( 384 / 10 ), $objUtil->getDrawToAccomplish ( 384 / 10 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsAdvanced ( $user ), ( int ) (384 / 5), "advanced", $objUtil->getDrawAccomplishment ( 384 / 5 ), $objUtil->getDrawToAccomplish ( 384 / 5 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsSenior ( $user ), ( int ) (384 / 2), "senior", $objUtil->getDrawAccomplishment ( 384 / 2 ), $objUtil->getDrawToAccomplish ( 384 / 2 ) );
	drawStar ( $objAccomplishments->getNebulaDrawingsExpert ( $user ), 384, "expert", $objUtil->getDrawAccomplishment ( 384 ), $objUtil->getDrawToAccomplish ( 384 ) );

	echo "</div>";

	// Total number of different objects
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangDifferentObjectsSeen . "</h4>";

	drawStar ( $objAccomplishments->getObjectsNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getObjectsRookie ( $user ), ( int ) (5000 / 500), "rookie", $objUtil->getSeenAccomplishment ( 5000 / 500 ), $objUtil->getSeenToAccomplish ( 5000 / 500 ) );
	drawStar ( $objAccomplishments->getObjectsBeginner ( $user ), 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getObjectsTalented ( $user ), 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getObjectsSkilled ( $user ), 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getObjectsIntermediate ( $user ), 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getObjectsExperienced ( $user ), 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getObjectsAdvanced ( $user ), 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getObjectsSenior ( $user ), 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getObjectsExpert ( $user ), 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );
	echo "</div>";

	// Total number of nebulae drawn
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangDifferentObjectsDrawings . "</h4>";

	drawStar ( $objAccomplishments->getObjectsDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsRookie ( $user ), 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsBeginner ( $user ), 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsTalented ( $user ), 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsSkilled ( $user ), 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsIntermediate ( $user ), 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsExperienced ( $user ), 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsAdvanced ( $user ), 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsSenior ( $user ), 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getObjectsDrawingsExpert ( $user ), 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of comet observations
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangTotalCometsSeen . "</h4>";

	drawStar ( $objAccomplishments->getCometObservationsNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getCometObservationsRookie ( $user ), ( int ) (5000 / 500), "rookie", $objUtil->getSeenAccomplishment ( 5000 / 500 ), $objUtil->getSeenToAccomplish ( 5000 / 500 ) );
	drawStar ( $objAccomplishments->getCometObservationsBeginner ( $user ), 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getCometObservationsTalented ( $user ), 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getCometObservationsSkilled ( $user ), 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getCometObservationsIntermediate ( $user ), 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getCometObservationsExperienced ( $user ), 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getCometObservationsAdvanced ( $user ), 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getCometObservationsSenior ( $user ), 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getCometObservationsExpert ( $user ), 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );
	echo "</div>";

	// Total number of different comets seen
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangDifferentCometsSeen . "</h4>";

	drawStar ( $objAccomplishments->getCometsObservedNewbie ( $user ), 1, "newbie", $objUtil->getSeenAccomplishment ( 1 ), $objUtil->getSeenToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getCometsObservedRookie ( $user ), 10, "rookie", $objUtil->getSeenAccomplishment ( 10 ), $objUtil->getSeenToAccomplish ( 10 ) );
	drawStar ( $objAccomplishments->getCometsObservedBeginner ( $user ), 25, "beginner", $objUtil->getSeenAccomplishment ( 25 ), $objUtil->getSeenToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getCometsObservedTalented ( $user ), 50, "talented", $objUtil->getSeenAccomplishment ( 50 ), $objUtil->getSeenToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getCometsObservedSkilled ( $user ), 100, "skilled", $objUtil->getSeenAccomplishment ( 100 ), $objUtil->getSeenToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getCometsObservedIntermediate ( $user ), 250, "intermediate", $objUtil->getSeenAccomplishment ( 250 ), $objUtil->getSeenToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getCometsObservedExperienced ( $user ), 500, "experienced", $objUtil->getSeenAccomplishment ( 500 ), $objUtil->getSeenToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getCometsObservedAdvanced ( $user ), 1000, "advanced", $objUtil->getSeenAccomplishment ( 1000 ), $objUtil->getSeenToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getCometsObservedSenior ( $user ), 2500, "senior", $objUtil->getSeenAccomplishment ( 2500 ), $objUtil->getSeenToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getCometsObservedExpert ( $user ), 5000, "expert", $objUtil->getSeenAccomplishment ( 5000 ), $objUtil->getSeenToAccomplish ( 5000 ) );

	echo "</div>";

	// Total number of different comet drawings
	echo "<div class=\"accomplishmentRow\">";
	echo "<h4>" . LangCometDrawings . "</h4>";

	drawStar ( $objAccomplishments->getCometDrawingsNewbie ( $user ), 1, "newbie", $objUtil->getDrawAccomplishment ( 1 ), $objUtil->getDrawToAccomplish ( 1 ) );
	drawStar ( $objAccomplishments->getCometDrawingsRookie ( $user ), 10, "rookie", $objUtil->getDrawAccomplishment ( 10 ), $objUtil->getDrawToAccomplish ( 10 ) );
	drawStar ( $objAccomplishments->getCometDrawingsBeginner ( $user ), 25, "beginner", $objUtil->getDrawAccomplishment ( 25 ), $objUtil->getDrawToAccomplish ( 25 ) );
	drawStar ( $objAccomplishments->getCometDrawingsTalented ( $user ), 50, "talented", $objUtil->getDrawAccomplishment ( 50 ), $objUtil->getDrawToAccomplish ( 50 ) );
	drawStar ( $objAccomplishments->getCometDrawingsSkilled ( $user ), 100, "skilled", $objUtil->getDrawAccomplishment ( 100 ), $objUtil->getDrawToAccomplish ( 100 ) );
	drawStar ( $objAccomplishments->getCometDrawingsIntermediate ( $user ), 250, "intermediate", $objUtil->getDrawAccomplishment ( 250 ), $objUtil->getDrawToAccomplish ( 250 ) );
	drawStar ( $objAccomplishments->getCometDrawingsExperienced ( $user ), 500, "experienced", $objUtil->getDrawAccomplishment ( 500 ), $objUtil->getDrawToAccomplish ( 500 ) );
	drawStar ( $objAccomplishments->getCometDrawingsAdvanced ( $user ), 1000, "advanced", $objUtil->getDrawAccomplishment ( 1000 ), $objUtil->getDrawToAccomplish ( 1000 ) );
	drawStar ( $objAccomplishments->getCometDrawingsSenior ( $user ), 2500, "senior", $objUtil->getDrawAccomplishment ( 2500 ), $objUtil->getDrawToAccomplish ( 2500 ) );
	drawStar ( $objAccomplishments->getCometDrawingsExpert ( $user ), 5000, "expert", $objUtil->getDrawAccomplishment ( 5000 ), $objUtil->getDrawToAccomplish ( 5000 ) );

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
