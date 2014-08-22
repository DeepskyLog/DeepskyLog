<?php
// overview_filters.php
// generates an overview of all filters (admin only)
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
elseif ($_SESSION ['admin'] != "yes")
	throw new Exception ( LangException001 );
else
	overview_filters ();
function overview_filters() {
	global $baseURL, $step, $objFilter, $objPresentations, $objUtil;
	$filts = $objFilter->getSortedFilters ( "name", '%' );
	$min = 0;
	echo "<div id=\"main\">";
	echo "<h4>" . LangOverviewFilterTitle1 . "</h4>";
	echo "<hr />";
	echo "<table class=\"table table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th>" . LangViewFilterName . "</th>";
	echo "<th>" . LangViewFilterType . "</th>";
	echo "<th>" . LangViewFilterColor . "</th>";
	echo "<th>" . LangViewFilterWratten . "</th>";
	echo "<th>" . LangViewFilterSchott . "</th>";
	echo "<th>" . LangViewObservationField2 . "</th>";
	echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>";
	echo "</tr></thead>";
	$count = 0;
	while ( list ( $key, $value ) = each ( $filts ) ) {
		if ($value != "1") {
			echo "<tr class=\"type" . (2 - ($count % 2)) . "\">";
			echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_filter&amp;filter=" . urlencode ( $value ) . "\">" . stripslashes ( $objFilter->getFilterPropertyFromId ( $value, 'name' ) ) . "</a></td>";
			echo "<td>" . $objFilter->getEchoType ( $objFilter->getFilterPropertyFromId ( $value, 'type' ) ) . "</td>";
			echo "<td>" . $objFilter->getEchoColor ( $objFilter->getFilterPropertyFromId ( $value, 'color' ) ) . "</td>";
			echo "<td>" . (($wratten = $objFilter->getFilterPropertyFromId ( $value, 'wratten' )) ? $wratten : "-") . "</td>";
			echo "<td>" . (($schott = $objFilter->getFilterPropertyFromId ( $value, 'schott' )) ? $schott : "-") . "</td>";
			echo "<td>" . $objFilter->getFilterPropertyFromId ( $value, 'observer' ) . "</td>";
			echo "<td>";
			if (! ($objFilter->getFilterUsedFromId ( $value )))
				echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_filter&amp;filterid=" . urlencode ( $value ) . "\">" . LangRemove . "</a>";
			echo "</td>";
			echo "</tr>";
		}
		$count ++;
	}
	echo "</table>";
	echo "<hr />";
	echo "</div>";
	echo $objUtil->addTablePager ();
	
	echo $objUtil->addTableJavascript ();
}
?>
