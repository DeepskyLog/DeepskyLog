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
	// Load the javascript for using php functions in javascript.
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/phpjs.js\"></script>";
	// Load the javascript for jquery.
	echo "<script src=\"" . $baseURL . "lib/javascript/jquery-2.2.4.min.js\" type=\"text/javascript\"></script>";
	// Load Lightbox to show nice pictures when clicking on the images.
	echo "<link href='https://fonts.googleapis.com/css?family=Yellowtail' rel='stylesheet' type='text/css'>";
	// Load the javascript for jquery-ui.
	echo "<script src=\"" . $baseURL . "lib/javascript/jquery-ui.min.js\" type=\"text/javascript\"></script>";
	echo "<link rel=\"stylesheet\" href=\"" . $baseURL . "styles/jquery-ui.min.css\">";
	// Load highcharts
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/highcharts.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/modules/exporting.js\"></script>";
        echo "<link href=\"styles/highcharts.css\" rel=\"stylesheet\">";
	// Load bootstrap
	echo "<script src=\"" . $baseURL . "lib/javascript/bootstrap.min.js\"></script>";
	echo "<script src=\"" . $baseURL . "lib/javascript/bootstrap-tour.min.js\"></script>";
	echo "<link href=\"styles/bootstrap.min.css\" rel=\"stylesheet\">";
	echo "<link href=\"styles/bootstrap-tour.min.css\" rel=\"stylesheet\">";
	// Load select2 javascript to be able to type in select boxes in html.
	echo "<link href=\"styles/select2.min.css\" rel=\"stylesheet\">";
	echo "<script src=\"" . $baseURL . "lib/javascript/select2.min.js\"></script>";
	// Load the tablesorter
	echo "<link rel=\"stylesheet\" href=\"styles/bootstrap-theme.min.css\">";
	echo "<script src=\"lib/javascript/jquery.tablesorter.min.js\"></script>
        <script src=\"lib/javascript/jquery.tablesorter.widgets.min.js\"></script>
        <script src=\"lib/javascript/jquery.tablesorter.pager.min.js\"></script>
        <script src=\"lib/javascript/widget-columnSelector.js\"></script>
        <script src=\"lib/javascript/widget-reorder.js\"></script>
		<link href=\"styles/tablesorter.theme.bootstrap.css\" rel=\"stylesheet\">";
	echo "<link href=\"" . $baseURL . "styles/deepskylog5030.css\" rel=\"stylesheet\" type=\"text/css\" />";
	// Load the needed javascript file for bootstrap-fileinput
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/fileinput.min.js\"></script>";
	// Load the needed javascript file for bootstrap-strengtmeter
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/strength-meter.min.js\"></script>";
	// Load the needed css file for bootstrap-fileinput
	echo "<link rel=\"stylesheet\" href=\"styles/fileinput.min.css\" />";
  // Load the needed css file for bootstrap-strengthmeter
	echo "<link rel=\"stylesheet\" href=\"styles/strength-meter.min.css\" />";
	// Load toastr
	echo "<link rel=\"stylesheet\" href=\"styles/toastr.min.css\" />";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/toastr.min.js\"></script>";
  // Load bootstrap-maxlength
	echo '<script type="text/javascript" src="' . $baseURL . 'lib/javascript/bootstrap-maxlength.js"></script>';

	$TitleText = "";
	$theDispatch = $objUtil->checkRequestKey ( 'indexAction' );
	$theObject = $objUtil->checkRequestKey ( 'object' );
	$theObject = ($theObject ? " - " . $theObject : "");
	if ($includeFile == 'deepsky/content/new_observationcsv.php')
		$TitleText = _("Import observations from a CSV file");
	elseif ($includeFile == 'deepsky/content/newObservationXml.php')
		$TitleText = _("Import observations from an XML file");
	elseif ($includeFile == 'deepsky/content/new_object.php')
		$TitleText = _("Add new object");
	elseif ($includeFile == 'deepsky/content/NewObservation.php')
		$TitleText = _("New observation") . $theObject;
	elseif ($includeFile == 'deepsky/content/view_object.php')
		$TitleText = _("Object details") . $theObject;
	elseif ($includeFile == 'deepsky/content/view_observation.php')
		$TitleText = _("Observation details") . $theObject;
	elseif ($includeFile == 'deepsky/content/dsatlas.php')
		$TitleText = _("Interactive Atlas") . $theObject;
	elseif ($includeFile == 'deepsky/content/new_listdatacsv.php')
		$TitleText = _("Import objects from a CSV file to your list");
	elseif ($includeFile == 'deepsky/content/tolist.php')
		$TitleText = $listname;
	elseif ($includeFile == 'deepsky/content/manage_objects_csv.php')
		$TitleText = _("Manage objects from CSV file");
	elseif ($includeFile == 'deepsky/content/setup_objects_query.php')
		$TitleText = _("Search objects");
	elseif ($includeFile == 'deepsky/content/view_object.php')
		$TitleText = _("Overview selected objects") . $theObject;
	elseif ($includeFile == 'deepsky/content/setup_observations_query.php')
		$TitleText = _("Search observations");
	elseif ($includeFile == 'deepsky/content/top_objects.php')
		$TitleText = _("Most popular objects");
	elseif ($includeFile == 'deepsky/content/top_observers.php')
		$TitleText = _("Most active observers");
	elseif ($includeFile == 'deepsky/content/selected_objects.php')
		$TitleText = _("Overview selected objects");
	elseif ($includeFile == 'deepsky/content/selected_observations.php') {
		if (array_key_exists('minyear', $_GET) && ($_GET ['minyear'] == substr ( $theDate, 0, 4 )) && array_key_exists ( 'minmonth', $_GET ) && ($_GET ['minmonth'] == substr ( $theDate, 4, 2 )) && array_key_exists ( 'minday', $_GET ) && ($_GET ['minday'] == substr ( $theDate, 6, 2 )))
			$TitleText = _("Overview of last year's observations");
		elseif ($object) {
			$TitleText = sprintf(_("Overview of all observations of %s"), $object);
        } else {
            $TitleText = _("Overview selected observations");
        }
	} elseif ($includeFile == 'deepsky/content/details_observer_catalog.php')
		$TitleText = _("Overview observed") . " " . $objUtil->checkGetKey ( 'catalog', 'M' ) . " " . _("objects") . " - " . $objObserver->getObserverProperty ( $objUtil->checkGetKey ( 'user' ), 'firstname' ) . " " . $objObserver->getObserverProperty ( $objUtil->checkGetKey ( 'user' ), 'name' );
	elseif ($theDispatch == 'detail_observer')
		$TitleText = _("Details observer");
	elseif ($theDispatch == 'statistics')
	$TitleText = _("Statistics");
	elseif ($includeFile == 'common/content/change_account.php')
		$TitleText = _("Settings");
	elseif ($theDispatch == 'detail_eyepiece')
		$TitleText = _("Details eyepiece");
	elseif ($includeFile == 'common/content/change_eyepiece.php')
		$TitleText = _("Adapt eyepiece");
	elseif ($theDispatch == 'detail_filter')
		$TitleText = _("Details filter");
	elseif ($includeFile == 'common/content/change_filter.php')
		$TitleText = _("Change filter");
	elseif ($theDispatch == 'detail_instrument')
		$TitleText = _("Details instrument");
	elseif ($includeFile == 'common/content/change_instrument.php')
		$TitleText = _("Change instrument");
	elseif ($theDispatch == 'detail_lens')
		$TitleText = _("Details lens");
	elseif ($includeFile == 'common/content/change_lens.php')
		$TitleText = _("Change lens");
	elseif ($theDispatch == 'detail_location')
		$TitleText = _("Details location");
	elseif ($includeFile == 'common/content/change_site.php')
		$TitleText = _("Change site");
	elseif ($includeFile == 'common/content/new_eyepiece.php')
		$TitleText = _("Add eyepiece");
	elseif ($includeFile == 'common/content/new_filter.php')
		$TitleText = _("Add filter");
	elseif ($includeFile == 'common/content/new_instrument.php')
		$TitleText = _("Add instrument");
	elseif ($includeFile == 'common/content/new_lens.php')
		$TitleText = _("Add lens");
	elseif ($includeFile == 'common/content/message.php')
		$TitleText = "";
	elseif ($includeFile == 'common/content/register.php')
		$TitleText = _("Register");
	elseif ($includeFile == 'common/content/overview_eyepieces.php')
		$TitleText = _("Eyepiece overview");
	elseif ($includeFile == 'common/content/overview_filters.php')
		$TitleText = _("Filters of");
	elseif ($includeFile == 'common/content/overview_instruments.php')
		$TitleText = _("Instruments of");
	elseif ($includeFile == 'common/content/overview_lenses.php')
		$TitleText = _("Lenses of");
	elseif ($includeFile == 'common/content/overview_locations.php')
		$TitleText = _("Locations overview");
	elseif ($includeFile == 'common/content/overview_observers.php')
		$TitleText = _("Observers overview");

	elseif ($includeFile == 'deepsky/control/admincheckobjects.php')
		$TitleText = "Checking objects";

	elseif ($includeFile == 'comets/content/overview_observations.php')
		$TitleText = _("Overview all observations");
	elseif ($includeFile == 'comets/content/view_object.php')
		$TitleText = _("Object details");
	elseif ($includeFile == 'comets/content/view_observation.php')
		$TitleText = _("Observation details");
	elseif ($includeFile == 'comets/content/new_observation.php')
		$TitleText = _("New observation");
	elseif ($includeFile == 'comets/content/selected_observations.php')
		$TitleText = _("Overview of all observations of ");
	elseif ($includeFile == 'comets/content/view_observation.php')
		$TitleText = _("Observation details");
	elseif ($includeFile == 'comets/content/new_object.php')
		$TitleText = _("Add new object");
	elseif ($includeFile == 'comets/content/view_object.php')
		$TitleText = _("Object details");
	elseif ($includeFile == 'comets/content/overview_objects.php')
		$TitleText = _("Overview all objects");
	elseif ($includeFile == 'comets/content/overview_observations.php')
		$TitleText = _("Overview all observations");
	elseif ($includeFile == 'comets/content/execute_query_objects.php')
		$TitleText = _("Overview selected objects");
	elseif ($includeFile == 'comets/content/selected_observations2.php')
		$TitleText = _("Overview selected observations");
	elseif ($includeFile == 'comets/content/top_observers.php')
		$TitleText = _("Most active observers");
	elseif ($includeFile == 'comets/content/top_objects.php')
		$TitleText = _("Most popular objects");
	elseif ($includeFile == 'comets/content/setup_observations_query.php')
		$TitleText = _("Search observations");
	elseif ($includeFile == 'comets/content/setup_objects_query.php')
		$TitleText = _("Search objects");
	elseif ($includeFile == 'common/content/view_instruments.php')
		$TitleText = _("My instruments");
	elseif ($includeFile == 'common/content/view_eyepieces.php')
		$TitleText = _("My eyepieces");
	elseif ($includeFile == 'common/content/view_filters.php')
		$TitleText = _("My filters");
	elseif ($includeFile == 'common/content/view_lenses.php')
		$TitleText = _("My lenses");
	elseif ($includeFile == 'common/content/new_location.php')
		$TitleText = _("Add site");
	elseif ($includeFile == 'common/content/locations.php')
		$TitleText = _("My locations");
	elseif ($objUtil->checkRequestKey ( 'title' ))
		$TitleText = $objUtil->checkRequestKey ( 'title', '' ); // 20081209 Here should come a better solution, see bug report 44
	elseif ($objUtil->checkRequestKey ( ('titleobject') ))
		$TitleText = $objUtil->checkRequestKey ( 'titleobject', '' ) . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
	elseif ($objUtil->checkRequestKey ( ('titleobjectaction') )) {
		if ($objUtil->checkRequestKey ( 'searchObjectQuickPickQuickPick', '' ))
			$TitleText = _("Overview selected objects") . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
		elseif ($objUtil->checkRequestKey ( 'searchObservationsQuickPick', '' ))
			$TitleText = _("Overview selected observations") . " - " . $objUtil->checkGetKey ( 'object' ); // 20081209 Here should come a better solution, see bug report 44
		elseif ($objUtil->checkRequestKey ( 'newObservationQuickPick', '' ))
			$TitleText = _("New Observation") . " - " . $objUtil->checkGetKey('object'); // 20081209 Here should come a better solution, see bug report 44
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
	echo '<link rel="stylesheet" href="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.css" />';

	echo "</head>";
}
?>
