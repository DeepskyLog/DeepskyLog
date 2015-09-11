<?php
global $baseURL, $objObservations, $objObserver, $objUtil;

echo "<div class=\"container-fluid\">
		<img class=\"img-responsive img-rounded\" src=\"" . $baseURL . "images/logo.png\">";
echo "</div>";
echo "<br />";

if (! $loggedUser) {
	echo IntroText;
	echo "<br /><br />";
}

// Show the icons.
// Add icon for 'Search observations'
echo "<div class=\"row\">";
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObservationsQuickPick=Zoek�waarnemingen\">";
echo "    <img title=\"" . LangSearchMenuItem3 . "\" src=\"" . $baseURL . "images/findObservation.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangSearchMenuItem3 . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'Search objects'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObjectQuickPickQuickPick=Zoek%C2%A0object\">";
echo "    <img title=\"" . LangSearchMenuItem5 . "\" src=\"" . $baseURL . "images/findObject.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangSearchMenuItem5 . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'See new observations'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";

$theDate = date ( 'Ymd', strtotime ( '-1 year' ) );
$lastMinYear = substr ( $theDate, 0, 4 );
$lastMinMonth = substr ( $theDate, 4, 2 );
$lastMinDay = substr ( $theDate, 6, 2 );
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;myLanguages=true&amp;catalog=%&amp;minyear=$lastMinYear&amp;minmonth=$lastMinMonth&amp;minday=$lastMinDay&amp;newobservations=true\">";
echo "    <img title=\"" . LangSearchMenuItem9 . "\" src=\"" . $baseURL . "images/new_badge.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangSearchMenuItem9 . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
// Add icon for 'Download atlases'
echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL . "index.php?indexAction=view_atlaspages\">";
echo "    <img title=\"" . LangDownloadAtlasses . "\" src=\"" . $baseURL . "images/downloadAtlas.png\">";
echo "    <div class=\"caption\">";
echo "     <h4 class=\"text-center\">" . LangDownloadAtlasses . "</h4>";
echo "    </div>";
echo "   </a>";
echo " </div>";
if ($loggedUser) {
    // Add icon for 'Add observation'
	echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
	echo "   <a class=\"thumbnail thumbnail-no-border\" href=\"" . $baseURL . "index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&newObservationQuickPick=Nieuwe%C2%A0waarneming\">";
	echo "    <img title=\"" . LangViewObservationButton1 . "\" src=\"" . $baseURL . "images/pencil.png\">";
	echo "    <div class=\"caption\">";
	echo "     <h4 class=\"text-center\">" . LangViewObservationButton1 . "</h4>";
	echo "    </div>";
	echo "   </a>";
	echo " </div>";

	// Add icon for 'Create list'
	echo " <div class=\"col-xs-4 col-sm-2 col-md-2\">";
	echo "   <a class=\"thumbnail thumbnail-no-border\" data-toggle=\"modal\" data-target=\"#addList\">";
	echo "    <img title=\"" . LangAddList . "\" src=\"" . $baseURL . "images/clipboard.png\">";
	echo "    <div class=\"caption\">";
	echo "     <h4 class=\"text-center\">" . LangAddList . "</h4>";
	echo "    </div>";
	echo "   </a>";
	echo " </div>";
}
echo "</div>";

echo "<h2>" . LangNewDrawings . "</h2>";
$drawings = $objObservation->getLastObservationsWithDrawing ();
echo "<div class=\"row\">";
foreach ( $drawings as $drawing => $key ) {
	echo " <div class=\"col-xs-3 col-sm-3 col-md-3\">";
	echo "  <div class=\"thumbnail\">";
	echo "   <a href=\"" . $baseURL . "index.php?indexAction=detail_observation&observation=" . $key ["id"] . "&amp;dalm=D\">";
	echo "    <img class=\"img-rounded\" src=\"" . $baseURL . "deepsky/drawings/" . $key ["id"] . ".jpg\">";
	echo "    <div class=\"caption\">";
	echo "     " . $objObserver->getFullName ( $key ["observerid"] ) . "<br />";
	echo "     " . $key ["objectname"] . "<br />";
	echo "     " . $objUtil->getLocalizedDate ( $key ["date"] );
	echo "    </div>";
	echo "   </a>";
	echo "  </div>";
	echo " </div>";
}
echo "</div>";

// echo "<div id=\"main\">";
// echo "<h2>" . LangSearchMenuItem9 . "</h2>";

// echo "<hr />";

// $link = $baseURL . "index.php?indexAction=main&amp;lco=" . urlencode ( $_SESSION ['lco'] ) . "&amp;newobservations=true";

// $objObservation->showListObservation ( $link, $_SESSION ['lco'] );
// echo "<hr />";
// if ($_SESSION ['lco'] == "O") {
// echo LangOverviewObservationsHeader5a;
// echo "<br /><br />";
// }
// $content1 = "<a class=\"btn btn-primary\" href=\"" . $baseURL . "index.php?indexAction=query_objects&amp;source=observation_query\">" . LangExecuteQueryObjectsMessage9 . "</a> ";
// $content1 .= $objPresentations->promptWithLinkText ( LangOverviewObservations10, LangOverviewObservations11, $baseURL . "observations.pdf.php?SID=Qobs", LangExecuteQueryObjectsMessage4a );
// $content1 .= " ";
// $content1 .= "<a class=\"btn btn-primary\" href=\"" . $baseURL . "observations.csv\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage5 . "</span></a> ";
// $content1 .= "<a class=\"btn btn-primary\" href=\"" . $baseURL . "observations.xml\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage10 . "</span></a> ";
// echo $content1;
// echo "<hr />";
// echo "</div>";

// echo "<script type=\"text/javascript\">";
// echo "
// function pageOnKeyDown(event)
// { if(event.keyCode==37)
// if(event.shiftKey)
// if(event.ctrlKey)
// location=html_entity_decode('" . $link . "&amp;multiplepagenr=0" . "');
// else
// location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pageleft . "');
// if(event.keyCode==39)
// if(event.shiftKey)
// if(event.ctrlKey)
// location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pagemax . "');
// else
// location=html_entity_decode('" . $link . "&amp;multiplepagenr=" . $pageright . "');
// }
// this.onKeyDownFns[this.onKeyDownFns.length] = pageOnKeyDown;
// ";
// echo "</script>";
// if (($object && $objObject->getExactDsObject ( $object )) && ($collapsed)) {
// echo "<h4>" . $collapsedtext . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "</h4>";
// echo $objPresentations->getDSSDeepskyLiveLinks1 ( $object );
// $topline = "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\">" . LangViewObjectViewNearbyObject . "</a>";
// }

?>
