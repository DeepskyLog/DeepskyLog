<?php
// top_objects.php
// generates an overview of all observed objects and their rank
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	top_objects ();
function top_objects() {
	global $baseURL, $objObject, $objObservation, $objPresentations, $objUtil, $objDatabase;
	echo "<div id=\"main\">";
	
	$run = $objDatabase->selectRecordsetArray ( "select objectname,COUNT(*) as count from observations group by objectname order by count DESC;" );
	$run2 = $objDatabase->selectRecordsetArray ( "select objectname,COUNT(*) as count from observations where hasDrawing=\"1\" group by objectname order by count DESC;" );
	
	// Objects seen
	echo "<h4>" . LangTopObjectsTitle . "</h4>";
	echo "<hr />";
	
	// We make some tabs.
	echo "<ul id=\"tabs\" class=\"nav nav-tabs\" data-tabs=\"tabs\">
          <li class=\"active\"><a href=\"#seen\" data-toggle=\"tab\">" . LangTopObjectsTitle . "</a></li>
          <li><a href=\"#drawings\" data-toggle=\"tab\">" . LangTopObjectsDrawnTitle . "</a></li>
        </ul>";
	
	echo "<div id=\"my-tab-content\" class=\"tab-content\">";
	echo "<div class=\"tab-pane active\" id=\"seen\">";
	
	echo "<table class=\"table sort-tableobjectlist table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead>";
	echo "<tr>";
	echo "<th>" . LangOverviewObjectsHeader1 . "</th>";
	echo "<th>" . GraphObservations . "</th>";
	echo "</thead>";
	
	$count = 0;
	while ( $count < sizeof ( $run ) ) {
		echo "<tr>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $run [$count] ['objectname'] ) . "\" >" . $run [$count] ['objectname'] . "</a></td>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=" . urlencode ( $run [$count] ['objectname'] ) . "&searchObservationsQuickPick=Zoek%C2%A0waarnemingen\">" . $run [$count] ['count'] . "</a></td>";
		echo "</tr>";
		$count ++;
	}
	echo "</table>";
	
	$objUtil->addPager ( "objectlist", $count );
	
	echo "</div>";
	
	echo "<div class=\"tab-pane\" id=\"drawings\">";
	
	// Objects drawn
	echo "<table class=\"table sort-tableobjectsdrawn table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead>";
	echo "<tr>";
	echo "<th>" . LangOverviewObjectsHeader1 . "</th>";
	echo "<th>" . GraphObservations . "</th>";
	echo "</thead>";
	
	$count = 0;
	while ( $count < sizeof ( $run2 ) ) {
		echo "<tr>";
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $run2 [$count] ['objectname'] ) . "\" >" . $run2 [$count] ['objectname'] . "</a></td>";
		$run3 = $objDatabase->selectRecordsetArray ( "select catalog, catindex from objectnames where objectname=\"" . $run2 [$count] ['objectname'] . "\";" );
		
		echo "<td><a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&title=Overzicht+geselecteerde+waarnemingen&myLanguages=true&query=Zoek+waarnemingen&seen=A&catalog=" . urlencode ( $run2 [0] ['catalog'] ) . "&number=" . urlencode ( $run2 [0] ['catindex'] ) . "&drawings=on\">" . $run2 [$count] ['count'] . "</a></td>";
		echo "</tr>";
		$count ++;
	}
	echo "</table>";
	
	$objUtil->addPager ( "objectsdrawn", $count );
	
	echo "<hr />";
	echo "</div></div></div>";
}

?>
