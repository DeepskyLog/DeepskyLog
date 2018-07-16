<?php
// overview_eyepieces.php
// generates an overview of all eyepieces (admin only)
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
	throw new Exception ( LangException002 );
elseif ($_SESSION ['admin'] != "yes")
	throw new Exception ( LangException001 );
else
	overview_eyepieces ();
function overview_eyepieces() {
	global $baseURL, $step, $objEyepiece, $objPresentations, $objUtil;
	$eyeps = $objEyepiece->getSortedEyepieces ( "name", '%' );
	echo "<div id=\"main\">";
	echo "<h4>" . LangViewEyepieceTitle . "</h4>";
	echo "<hr />";
	echo "<table class=\"table sort-table table-condensed table-striped table-hover tablesorter custom-popup\">";
	echo "<thead><tr>";
	echo "<th>" . LangViewEyepieceName . "</th>";
	echo "<th>" . LangViewEyepieceFocalLength . "</th>";
	echo "<th>" . LangViewEyepieceMaxFocalLength . "</th>";
	echo "<th>" . LangViewEyepieceApparentFieldOfView . "</th>";
	echo "<th>" . LangViewObservationField2 . "</th>";
	echo "<th class=\"filter-false columnSelector-disable\" data-sorter=\"false\"></th>";
	echo "</tr></thead>";
	$count = 0;
	while ( list ( $key, $value ) = each ( $eyeps ) ) {
		$eyepieceProperties = $objEyepiece->getEyepiecePropertiesFromId ( $value );
		if ($value != "1") {
			echo "<tr>";
			echo "<td><a href=\"" . $baseURL . "index.php?indexAction=adapt_eyepiece&amp;eyepiece=" . urlencode ( $value ) . "\">" . stripslashes ( $eyepieceProperties ['name'] ) . "</a></td>";
			echo "<td align=\"center\">" . $eyepieceProperties ['focalLength'] . "</td>";
			echo "<td align=\"center\">" . (($eyepieceProperties ['maxFocalLength'] != - 1) ? $eyepieceProperties ['maxFocalLength'] : "-") . "</td>";
			echo "<td align=\"center\">" . $eyepieceProperties ['apparentFOV'] . "</td>";
			echo "<td>" . $eyepieceProperties ['observer'] . "</td>";
			echo "<td>";
			if (! ($objEyepiece->getEyepieceUsedFromId ( $value )))
				echo "<a href=\"" . $baseURL . "index.php?indexAction=validate_delete_eyepiece&amp;eyepieceid=" . urlencode ( $value ) . "\">" . _("Delete") . "</a>";
			echo "</td>";
			echo "</tr>";
		}
		$count ++;
	}
	echo "</table>";
	echo "<hr />";
	echo "</div>";

	$objUtil->addPager ( "", $count );
}
?>
