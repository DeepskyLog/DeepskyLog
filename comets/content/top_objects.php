<?php
// top_objects.php
// generates an overview of all observed objects and their rank
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	top_objects ();
function top_objects() {
	global $baseURL, $step, $objCometObject, $objCometObservation, $objPresentations, $objUtil;
	echo "<div id=\"main\">";
	$rank = $objCometObservation->getPopularObservations ();
	$link = $baseURL . "index.php?indexAction=comets_rank_objects";
	echo "<h4>" . LangTopObjectsTitle . "</h4>";
	
	echo "<hr />";
	echo "<table class=\"table sort-tablecometobjects table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th class=\"filter-false columnSelector-disable\">" . LangTopObjectsHeader1 . "</th>";
	echo "<th>" . LangTopObjectsHeader2 . "</th>";
	echo "<th>" . LangTopObjectsHeader5 . "</th>";
	echo "</tr></thead>";
	$count = 0;
	while ( list ( $key, $value ) = each ( $rank ) ) {
		echo "<tr>
				<td>" . ($count + 1) . "</td>
				<td> <a href=\"" . $baseURL . "index.php?indexAction=comets_detail_object&amp;object=" . urlencode ( $key ) . "\">" . $objCometObject->getName ( $key ) . "</a> </td>";
		echo "<td> $value </td>";
		echo "</tr>";
		$count++;
	}
	echo "</table>";

	echo $objUtil->addTablePager ( "cometobjects" );
	
	echo $objUtil->addTableJavascript ( "cometobjects" );
	
	echo "<hr />";
	echo "</div>";
}
?>
