<?php
// execute_query_objects.php
// executes the comet query passed by setup_query_objects.php
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	comets_execute_query_objects ();
function comets_execute_query_objects() {
	global $baseURL, $step, $loggedUser, $objCometObject, $objPresentations, $objUtil, $objCometObservation;
	echo "<div id=\"main\">";
	if ($_GET ['name'] || $_GET ['icqname']) 	// at least one search field filled in
	{
		$name = $objUtil->checkGetKey ( 'name' );
		$icqname = $objUtil->checkGetKey ( 'icqname' );
		// SEARCH ON OBJECT NAME
		// SETUP SEARCH QUERY
		$query = array (
				"name" => $name,
				"icqname" => $icqname
		);
		$sort = "name"; // standard sort on name
		                // SELECT OBJECTS
		$result = $objCometObject->getObjectFromQuery ( $query, $sort );
		if ($result) {
			$count = 0; // counter for altering table colors
			$link = $baseURL . "index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode ( $_GET ['name'] );
			// OUTPUT RESULT
			$rank = $objCometObservation->getPopularObservations ();
			echo "<h4>" . _("Overview selected objects") . "</h4>";
			echo "<hr />";
			echo "<table class=\"table sort-tablecometobjects table-condensed table-striped table-hover tablesorter custom-popup\">";
			echo "<thead><tr>";
			echo "<th>" . _("Name") . "</th>";
			echo "<th>" . _("ICQ name") . "</th>";
			// Check the number of objects. If there are less than 500 objects, we
			// enable the sorting on seen.
				echo "<th>" . _("Seen") . "</th>";
			echo "</tr></thead>";
			foreach ($result as $key => $value) {
				// NAME
				$name = $value;
				$icqname = $objCometObject->getIcqname ( $objCometObject->getId ( $value ) );
				// SEEN
				$seen = "-";
				$see = $objCometObject->getObserved ( $name );
				if ($see == 1) 				// object has been seen already
				{
					$seen = "<a href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode ( $objCometObject->getId ( $value ) ) . "\">X</a>";
				}
				if ($loggedUser) {
					$see = $objCometObject->getObservedbyUser ( $name, $loggedUser );
					if ($see == 1) 					// object has been seen by the observer logged in
					{
						$seen = "<a href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode ( $objCometObject->getId ( $value ) ) . "\">Y</a>";
					}
				}
				echo "<tr>";
				echo "<td><a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $objCometObject->getId ( $value ) ) . "\">$value</a></td>";
				echo "<td>$icqname</td>";
				echo "<td class=\"seen\">$seen</td>";
				echo "</tr>";
				$count ++; // increase line counter
			}
			$_SESSION ['object_query'] = $result;
			echo "</table>";

			$objUtil->addPager ( "cometobjects", $count );

			echo "<hr />";
		} else 		// no results found
		{
			echo "<h4>" . _("Overview selected objects") . "</h4>";
			echo "<hr />";
			echo _("Sorry, no objects found!");
		}
	} else 	// no query fields filled in
	{
		echo "<h4>" . _("Overview selected objects") . "</h4>";
		echo "<hr />";
		echo _("You didn't specify any parameters to search on.");
	}
	echo "</div>";
}
?>
