<?php
// details_observer_catalog.php
// shows information of number of catalog objects seen by user
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	details_observer_catalog ();
function details_observer_catalog() {
	global $baseURL, $instDir, $objObject, $objObservation, $objPresentations, $objObserver, $objUtil;
	if (! $objUtil->checkGetKey ( 'user' ))
		throw new Exception ( "No user supplied in GET in details_observer_catalog." );
	$firstname = $objObserver->getObserverProperty ( $_GET ['user'], 'firstname' );
	$name = $objObserver->getObserverProperty ( $_GET ['user'], 'name' );
	$partof = $objUtil->checkGetKey ( 'partof', 0 );
	
	echo "<div id=\"main\">";
	echo "<h4>" . $firstname . "&nbsp;" . $name . "</h4>";
	echo "<hr />";
	$upload_dir = 'common/observer_pics';
	$dir = opendir ( $instDir . $upload_dir );
	while ( FALSE !== ($file = readdir ( $dir )) ) {
		if (("." == $file) or (".." == $file))
			continue; // skip current directory and directory above
		if (fnmatch ( html_entity_decode ( $_GET ['user'] ) . ".gif", $file ) || fnmatch ( html_entity_decode ( $_GET ['user'] ) . ".jpg", $file ) || fnmatch ( html_entity_decode ( $_GET ['user'] ) . ".png", $file ))
			echo "<p><img class=\"viewobserver\" src=\"" . $baseURL . $upload_dir . "/" . $file . "\" alt=\"" . $firstname . "&nbsp;" . $name . "\"></img></p>";
	}
	$cat = $objUtil->checkGetKey ( 'catalog', 'M' );
	$observedObjectsFromCatalog = $objObservation->getObservedFromCatalog ( $_GET ['user'], $cat ); // number of objects observed by this observer
	if ($partof)
		$observedObjectsFromCatalogPartOf = $objObservation->getObservedFromCatalogPartOf ( html_entity_decode ( $_GET ['user'] ), $cat ); // number of objects observed by this observer
	$numberOfObjects = $objObject->getNumberOfObjectsInCatalog ( $cat ); // number of objects in catalog
	echo LangTopObserversMessierHeader2 . " " . $cat . " " . LangTopObserversMessierHeader3 . (($partof) ? LangOrPartOfs : LangNoPartOfsBrackets) . ":&nbsp;" . count ( $observedObjectsFromCatalog ) . " / " . $numberOfObjects;
	if ($partof)
		$content = "<a class=\"btn btn-success pull-right\" href=\"" . $baseURL . "index.php?indexAction=view_observer_catalog&amp;catalog=" . urlencode ( $cat ) . "&amp;user=" . urlencode ( $_GET ['user'] ) . "&amp;partof=0\">" . LangShowWithoutPartOfs . "</a>";
	else
		$content = "<a class=\"btn btn-success pull-right\" href=\"" . $baseURL . "index.php?indexAction=view_observer_catalog&amp;catalog=" . urlencode ( $cat ) . "&amp;user=" . urlencode ( $_GET ['user'] ) . "&amp;partof=1\">" . LangShowWithPartOfs . "</a>";
	echo $content;
	$resultarray = $objObject->getObjectsFromCatalog ( $cat );
	echo "<br /><br /><table class=\"table\">";
	for($i = 1; $i <= $numberOfObjects; $i ++) {
		if ((($i - 1) % 100) == 0) {
			echo "<thead>";
			echo "<th>";
			echo "&nbsp;";
			echo "</th>";
			for($j = 1; $j <= 5; $j ++) {
				echo "<th class=\"text-center\">";
				echo "$j";
				echo "</th>";
			}
			echo "<th>";
			echo "&nbsp;";
			echo "</th>";
			for($j = 6; $j <= 10; $j ++) {
				echo "<th class=\"text-center\">";
				echo "$j";
				echo "</th>";
			}
			echo "</thead>";
		}
		if ((($i - 1) % 10) == 0) {
			echo "<tr>";
			echo "<td class=\"observercatalogbackground\">";
			echo $i;
			echo '-';
			echo $i + 9;
			echo "</td>";
		} elseif ((($i - 1) % 5) == 0) {
			echo "<td> &nbsp; </td>";
		}
		$index = key ( $resultarray );
		list ( $object, $altname ) = current ( $resultarray );
		if (($cat . " " . $index) != $object)
			$ref = $cat . " " . $index;
		else
			$ref = $object;
		if (in_array ( $object, $observedObjectsFromCatalog )) {
			echo "<td class=\"observercataloggreen\">";
			echo "<a class=\"observercatalog\" title=\"" . $ref . "\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "&amp;observer=" . urlencode ( $_GET ['user'] ) . "\" >" . $object . "</a>";
			echo "</td>";
		} else if ($partof && in_array ( $object, $observedObjectsFromCatalogPartOf )) {
			echo "<td class=\"observercatalogyellow\">";
			echo "<a class=\"observercatalog\" title=\"" . $ref . "\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\" >" . $object . "</a>";
			echo "</td>";
		} else {
			echo "<td class=\"observercatalogred\">";
			echo "<a class=\"observercatalog\" title=\"" . $ref . "\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\" >" . $object . "</a>";
			echo "</td>";
		}
		if (($i % 10) == 0)
			echo "</tr>";
		next ( $resultarray );
	}
	if (((-- $i) % 10) != 0)
		echo "</tr>";
	echo "</table>";
	echo "</div>";
}
?>
