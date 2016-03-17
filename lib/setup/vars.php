<?php
// vars.php
// contains a series of defines for the project
global $inIndex, $copyright, $versionInfo, $copyrightInfo;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";

$copyright = "&copy;2004&nbsp;-&nbsp;2016";
$versionInfo = "5.0.38";
$copyrightInfo = $copyright . ",&nbsp;DeepskyLog developers";

define ( "AtlasOverviewZoom", 17 );
define ( "AtlasLookupZoom", 18 );
define ( "AtlasDetailZoom", 20 );

define ( "RoleAdmin", 0 );
define ( "RoleUser", 1 );
define ( "RoleWaitlist", 2 );
define ( "RoleRemoved", 3 );
define ( "RoleCometAdmin", 4 );

define ( "InstrumentOther", - 1 );
define ( "InstrumentNakedEye", 0 );
define ( "InstrumentBinoculars", 1 );
define ( "InstrumentRefractor", 2 );
define ( "InstrumentReflector", 3 );
define ( "InstrumentFinderscope", 4 );
define ( "InstrumentRest", 5 );
define ( "InstrumentCassegrain", 6 );
define ( "InstrumentKutter", 7 );
define ( "InstrumentMaksutov", 8 );
define ( "InstrumentSchmidtCassegrain", 9 );
define ( "FilterOther", 0 );
define ( "FilterBroadBand", 1 );
define ( "FilterNarrowBand", 2 );
define ( "FilterOIII", 3 );
define ( "FilterHBeta", 4 );
define ( "FilterHAlpha", 5 );
define ( "FilterColor", 6 );
define ( "FilterNeutral", 7 );
define ( "FilterCorrective", 8 );
define ( "FilterColorLightRed", "1" );
define ( "FilterColorRed", "2" );
define ( "FilterColorDeepRed", "3" );
define ( "FilterColorOrange", "4" );
define ( "FilterColorLightYellow", "5" );
define ( "FilterColorDeepYellow", "6" );
define ( "FilterColorYellow", "7" );
define ( "FilterColorYellowGreen", "8" );
define ( "FilterColorLightGreen", "9" );
define ( "FilterColorGreen", "10" );
define ( "FilterColorMediumBlue", "11" );
define ( "FilterColorPaleBlue", "12" );
define ( "FilterColorBlue", "13" );
define ( "FilterColorDeepBlue", "14" );
define ( "FilterColorDeepViolet", "15" );

?>
