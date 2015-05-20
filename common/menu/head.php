<?php
// head.php
// prints the html headers
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	head ();
function head() {
	global $baseURL, $includeFile, $topmenu, $leftmenu, $theDate, $object, $listname, $objObserver, $objUtil, $googleAnalytics, $domainName;
	echo "<head>";
	echo "<meta charset=\"utf-8\" />";
	echo "<meta name=\"revisit-after\" content=\"1 day\" />";
	echo "<meta name=\"author\" content=\"DeepskyLog - VVS\" />";
	echo "<meta name=\"keywords\" content=\"VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, Deepsky, waarnemingen, kometen\" />";
	echo "<base href=\"" . $baseURL . "\" />";
	echo "<link rel=\"shortcut icon\" href=\"" . $baseURL . "styles/images/favicon.png\" />";
	echo "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"DeepskyLog - latest observations\" href=\"observations.rss\" />";
	echo "<link href=\"https://plus.google.com/105963409869875462537/\" rel=\"publisher\" />";
	echo "<script type=\"text/javascript\" src=\"https://apis.google.com/js/plusone.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/phpjs.js\"></script>";
	echo "<script src=\"" . $baseURL . "lib/javascript/jquery-1.11.2.min.js\" type=\"text/javascript\"></script>
        <link rel=\"stylesheet\" href=\"" . $baseURL . "styles/lightbox.css\" type=\"text/css\" media=\"screen\" charset=\"utf-8\" />
        <script src=\"" . $baseURL . "lib/javascript/lightbox.min.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
	echo "<link href='http://fonts.googleapis.com/css?family=Yellowtail' rel='stylesheet' type='text/css'>";
	echo "<script src=\"" . $baseURL . "lib/javascript/jquery-ui.min.js\" type=\"text/javascript\"></script>";
	echo "<link rel=\"stylesheet\" href=\"" . $baseURL . "styles/jquery-ui.min.css\">";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/highcharts.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/modules/exporting.js\"></script>";
	echo "<script src=\"" . $baseURL . "lib/javascript/bootstrap.min.js\"></script>";
	echo "<script src=\"" . $baseURL . "lib/javascript/bootstrap-tour.min.js\"></script>";
	echo "<link href=\"styles/bootstrap.min.css\" rel=\"stylesheet\">";
	echo "<link href=\"styles/bootstrap-tour.min.css\" rel=\"stylesheet\">";
	echo "<link rel=\"stylesheet\" href=\"styles/bootstrap-theme.min.css\">";
	echo "<script src=\"lib/javascript/jquery.tablesorter.min.js\"></script>
        <script src=\"lib/javascript/jquery.tablesorter.widgets.min.js\"></script>
        <script src=\"lib/javascript/jquery.tablesorter.pager.min.js\"></script>
        <script src=\"lib/javascript/widget-columnSelector.js\"></script>
        <script src=\"lib/javascript/widget-reorder.js\"></script>
		<link href=\"styles/tablesorter.theme.bootstrap.css\" rel=\"stylesheet\">";
	echo "<link href=\"" . $baseURL . "styles/deepskylog.css\" rel=\"stylesheet\" type=\"text/css\" />";
	
	$TitleText = "";
	$theDispatch = $objUtil->checkRequestKey ( 'indexAction' );
	$theObject = $objUtil->checkRequestKey ( 'object' );
	$theObject = ($theObject ? " - " . $theObject : "");
	if ($includeFile == 'deepsky/content/new_observationcsv.php')
		$TitleText = LangCSVTitle;
	elseif ($includeFile == 'deepsky/content/new_observationxml.php')
		$TitleText = LangXMLTitle;
	elseif ($includeFile == 'deepsky/content/new_object.php')
		$TitleText = LangNewObjectTitle;
	elseif ($includeFile == 'deepsky/content/new_observation.php')
		$TitleText = LangNewObservationTitle . $theObject;
	elseif ($includeFile == 'deepsky/content/view_object.php')
		$TitleText = LangViewObjectTitle . $theObject;
	elseif ($includeFile == 'deepsky/content/view_observation.php')
		$TitleText = LangViewObservationTitle . $theObject;
	elseif ($includeFile == 'deepsky/content/dsatlas.php')
		$TitleText = LangAtlasPage . $theObject;
	elseif ($includeFile == 'deepsky/content/new_listdatacsv.php')
		$TitleText = LangCSVListTitle;
	elseif ($includeFile == 'deepsky/content/tolist.php')
		$TitleText = $listname;
	elseif ($includeFile == 'deepsky/content/manage_objects_csv.php')
		$TitleText = LangCSVObjectTitle;
	elseif ($includeFile == 'deepsky/content/setup_objects_query.php')
		$TitleText = LangQueryObjectsTitle;
	elseif ($includeFile == 'deepsky/content/view_object.php')
		$TitleText = LangSelectedObjectsTitle . $theObject;
	elseif ($includeFile == 'deepsky/content/setup_observations_query.php')
		$TitleText = LangQueryObservationsTitle;
	elseif ($includeFile == 'deepsky/content/top_objects.php')
		$TitleText = LangTopObjectsTitle;
	elseif ($includeFile == 'deepsky/content/top_observers.php')
		$TitleText = LangTopObserversTitle;
	elseif ($includeFile == 'deepsky/content/selected_objects.php')
		$TitleText = LangSelectedObjectsTitle;
	elseif ($includeFile == 'deepsky/content/selected_observations.php') {
		if (array_key_exists ( 'minyear', $_GET ) && ($_GET ['minyear'] == substr ( $theDate, 0, 4 )) && array_key_exists ( 'minmonth', $_GET ) && ($_GET ['minmonth'] == substr ( $theDate, 4, 2 )) && array_key_exists ( 'minday', $_GET ) && ($_GET ['minday'] == substr ( $theDate, 6, 2 )))
			$TitleText = LangSelectedObservationsTitle3;
		elseif ($object)
			$TitleText = LangSelectedObservationsTitle . $object;
		else
			$TitleText = LangSelectedObservationsTitle2;
	} elseif ($includeFile == 'deepsky/content/details_observer_catalog.php')
		$TitleText = LangTopObserversMessierHeader2 . " " . $objUtil->checkGetKey ( 'catalog', 'M' ) . " " . LangTopObserversMessierHeader3 . " - " . $objObserver->getObserverProperty ( $objUtil->checkGetKey ( 'user' ), 'firstname' ) . " " . $objObserver->getObserverProperty ( $objUtil->checkGetKey ( 'user' ), 'name' );
	elseif ($theDispatch == 'detail_observer')
		$TitleText = LangDetailObserver;
	elseif ($includeFile == 'common/content/change_account.php')
		$TitleText = LangChangeAccountTitle;
	elseif ($theDispatch == 'detail_eyepiece')
		$TitleText = LangDetailEyepiece;
	elseif ($includeFile == 'common/content/change_eyepiece.php')
		$TitleText = LangAddEyepieceButton2;
	elseif ($theDispatch == 'detail_filter')
		$TitleText = LangDetailFilter;
	elseif ($includeFile == 'common/content/change_filter.php')
		$TitleText = LangChangeFilterButton;
	elseif ($theDispatch == 'detail_instrument')
		$TitleText = LangDetailInstrument;
	elseif ($includeFile == 'common/content/change_instrument.php')
		$TitleText = LangChangeInstrumentButton;
	elseif ($theDispatch == 'detail_lens')
		$TitleText = LangDetailLens;
	elseif ($includeFile == 'common/content/change_lens.php')
		$TitleText = LangChangeLensButton;
	elseif ($theDispatch == 'detail_location')
		$TitleText = LangDetailSite;
	elseif ($includeFile == 'common/content/change_site.php')
		$TitleText = LangAddSiteButton2;
	elseif ($includeFile == 'common/content/new_eyepiece.php')
		$TitleText = LangAddEyepieceButton;
	elseif ($includeFile == 'common/content/new_filter.php')
		$TitleText = LangAddFilterButton;
	elseif ($includeFile == 'common/content/new_instrument.php')
		$TitleText = LangAddInstrumentAdd;
	elseif ($includeFile == 'common/content/new_lens.php')
		$TitleText = LangAddLensButton;
	elseif ($includeFile == 'common/content/message.php')
		$TitleText = "";
	elseif ($includeFile == 'common/content/search_locations.php')
		$TitleText = LangSearchLocations0;
	elseif ($includeFile == 'common/content/getLocation.php')
		$TitleText = LangGetLocation1;
	elseif ($includeFile == 'common/content/register.php')
		$TitleText = LangRegisterNewTitle;
	elseif ($includeFile == 'common/content/overview_eyepieces.php')
		$TitleText = LangViewEyepieceTitle;
	elseif ($includeFile == 'common/content/overview_filters.php')
		$TitleText = LangOverviewFilterTitle;
	elseif ($includeFile == 'common/content/overview_instruments.php')
		$TitleText = LangOverviewInstrumentsTitle;
	elseif ($includeFile == 'common/content/overview_lenses.php')
		$TitleText = LangOverviewLensTitle;
	elseif ($includeFile == 'common/content/overview_locations.php')
		$TitleText = LangViewLocationTitle;
	elseif ($includeFile == 'common/content/overview_observers.php')
		$TitleText = LangViewObserverTitle;
	
	elseif ($includeFile == 'deepsky/control/admincheckobjects.php')
		$TitleText = "Checking objects";
	
	elseif ($includeFile == 'comets/content/overview_observations.php')
		$TitleText = LangOverviewObservationsTitle;
	elseif ($includeFile == 'comets/content/view_object.php')
		$TitleText = LangViewObjectTitle;
	elseif ($includeFile == 'comets/content/view_observation.php')
		$TitleText = LangViewObservationTitle;
	elseif ($includeFile == 'comets/content/new_observation.php')
		$TitleText = LangNewObservationTitle;
	elseif ($includeFile == 'comets/content/selected_observations.php')
		$TitleText = LangSelectedObservationsTitle;
	elseif ($includeFile == 'comets/content/view_observation.php')
		$TitleText = LangViewObservationTitle;
	elseif ($includeFile == 'comets/content/new_object.php')
		$TitleText = LangNewObjectTitle;
	elseif ($includeFile == 'comets/content/view_object.php')
		$TitleText = LangViewObjectTitle;
	elseif ($includeFile == 'comets/content/overview_objects.php')
		$TitleText = LangOverviewObjectsTitle;
	elseif ($includeFile == 'comets/content/overview_observations.php')
		$TitleText = LangOverviewObservationsTitle;
	elseif ($includeFile == 'comets/content/execute_query_objects.php')
		$TitleText = LangSelectedObjectsTitle;
	elseif ($includeFile == 'comets/content/selected_observations2.php')
		$TitleText = LangSelectedObservationsTitle2;
	elseif ($includeFile == 'comets/content/top_observers.php')
		$TitleText = LangTopObserversTitle;
	elseif ($includeFile == 'comets/content/top_objects.php')
		$TitleText = LangTopObjectsTitle;
	elseif ($includeFile == 'comets/content/setup_observations_query.php')
		$TitleText = LangQueryObservationsTitle;
	elseif ($includeFile == 'comets/content/setup_objects_query.php')
		$TitleText = LangQueryObjectsTitle;
	elseif ($includeFile == 'common/content/view_instruments.php')
		$TitleText = LangViewInstruments;
	elseif ($includeFile == 'common/content/view_eyepieces.php')
		$TitleText = LangViewEyepieces;
	elseif ($includeFile == 'common/content/view_filters.php')
		$TitleText = LangViewFilters;
	elseif ($includeFile == 'common/content/view_lenses.php')
		$TitleText = LangViewLenses;
	elseif ($includeFile == 'common/content/new_location.php')
		$TitleText = LangAddSiteButton;
	elseif ($includeFile == 'common/content/locations.php')
		$TitleText = LangViewLocations;
	elseif ($objUtil->checkRequestKey ( 'title' ))
		$TitleText = $objUtil->checkRequestKey ( 'title', '' ); // 20081209 Here should come a better solution, see bug report 44
	elseif ($objUtil->checkRequestKey ( ('titleobject') ))
		$TitleText = $objUtil->checkRequestKey ( 'titleobject', '' ) . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
	elseif ($objUtil->checkRequestKey ( ('titleobjectaction') )) {
		if ($objUtil->checkRequestKey ( 'searchObjectQuickPickQuickPick', '' ))
			$TitleText = LangSelectedObjectsTitle . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
		elseif ($objUtil->checkRequestKey ( 'searchObservationsQuickPick', '' ))
			$TitleText = LangSelectedObservationsTitle2 . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
		elseif ($objUtil->checkRequestKey ( 'newObservationQuickPick', '' ))
			$TitleText = LangQuickPickNewObservation . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
	}
	if ($TitleText == "" || $TitleText == "Home") {
		$TitleText = "DeepskyLog";
	}
	echo "<title>" . $TitleText . "</title>";
	echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
	
	echo "<script type=\"text/javascript\">

     var _gaq = _gaq || [];
     _gaq.push(['_setAccount', '" . $googleAnalytics . "']);
     _gaq.push(['_setDomainName', '" . $domainName . "']);
     _gaq.push(['_setAllowHash', 'false']);
     _gaq.push(['_setAllowLinker', true]);
     _gaq.push(['_trackPageview']);

     (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
     })();

     </script>";
	echo "</head>";
}
?>
