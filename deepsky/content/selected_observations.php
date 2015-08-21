<?php
// selected_observations.php
// generates an overview of selected observations in the database
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	selected_observations ();
function selected_observations() {
	global $baseURL, $FF, $loggedUser, $object, $myList, $step, $objObject, $objObservation, $objSession, $objPresentations, $objUtil;
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/presentation.js\"></script>";
	$link2 = $baseURL . "index.php?indexAction=result_selected_observations&amp;lco=" . urlencode ( $_SESSION ['lco'] );
	reset ( $_GET );
	if (array_key_exists ( 'sessionid', $_GET )) {
		$sessionid = $_GET ['sessionid'];
		$_SESSION ['Qobs'] = $objSession->getObservations ( $sessionid );
	}
	while ( list ( $key, $value ) = each ( $_GET ) )
		if (! in_array ( $key, array (
				'indexAction',
				'lco',
				'sessionid',
				'sortdirection',
				'sort',
				'myLanguages',
				'collapsed' 
		) ))
			$link2 .= "&amp;" . $key . "=" . urlencode ( $value );
		// while(list($key,$value)=each($usedLanguages))
		// $link2=$link2.'&amp;'.$value.'='.$value;
	$link = $link2;

	// ====================== the remainder of the pages formats the page output and calls showObject (if necessary) and showObservations
	// =============================================== IF IT CONCERNS THE OBSERVATIONS OF 1 SPECIFIC OBJECT, SHOW THE OBJECT BEFORE SHOWING ITS OBSERVATIONS =====================================================================================
	if ($object && $objObject->getExactDsObject ( $object )) {
		$object_ss = stripslashes ( $object );
		$seen = $objObject->getDSOseenLink ( $object );
		$collapsedtext = "<a href=\"" . $link2 . "&amp;collapsed=collapsed\" title=\"" . LangHideObjectDetails . "\">-</a>&nbsp;";
		$collapsed = false;
		if ($objUtil->checkRequestKey ( 'collapsed' ) == 'collapsed') {
			$collapsedtext = "<a href=\"" . $link2 . "\" title=\"" . LangShowObjectDetails . "\">+</a>&nbsp;";
			$collapsed = true;
			$link .= "&amp;collapsed=collapsed";
		}
		if (! ($collapsed)) {
			echo "<h4>" . $collapsedtext . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "</h4>";
			echo $objPresentations->getDSSDeepskyLiveLinks1 ( $object );
			$topline = "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\">" . LangViewObjectViewNearbyObject . "</a>";
		}
		// if(substr($objObject->getSeen($object),0,1)!='-')
		// $topline.= "&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\">".LangViewObjectObservations."</a>";
		// if($loggedUser)
		// $topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;object=" . urlencode($object) . "\">" . LangViewObjectAddObservation."</a>";
		if (! ($collapsed)) {
			if ($myList) {
				if ($objList->checkObjectInMyActiveList ( $object ))
					$topline .= "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "&amp;removeObjectFromList=" . urlencode ( $object ) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
				else
					$topline .= "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode ( $object ) . "&amp;addObjectToList=" . urlencode ( $object ) . "&amp;showname=" . urlencode ( $object ) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
			}
			$topline .= "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=atlaspage&amp;object=" . urlencode ( $object ) . "\">" . LangAtlasPage . "</a>";
			echo substr ( $topline, 13 );
			echo $objPresentations->getDSSDeepskyLiveLinks2 ( $object );
			echo "<hr />";
			$objObject->showObject ( $object );
		}
	}
	if ((! (array_key_exists ( 'Qobs', $_SESSION ))) || count ( $_SESSION ['Qobs'] ) == 0) 	// ================================================================================================== no reult present =======================================================================================
	{
		echo LangObservationNoResults . (($objUtil->checkGetKey ( 'myLanguages' )) ? (" (" . LangSelectedObservationsSelectedLanguagesIndication . ")") : (" (" . LangSelectedObservationsAllLanguagesIndication . ")")) . "</h4>";
		if ($objUtil->checkGetKey ( 'myLanguages' ))
			echo "<p>" . "<a href=\"" . $link2 . "\">" . LangSearchAllLanguages . "</a>&nbsp;</p>";
		echo "<p>" . "<a href=\"" . $baseURL . "index.php?indexAction=query_observations\">" . LangSearchDetailPage . "</a>" . "</p>";
	} else { // =============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
		echo "<div id=\"main\">";
		$theDate = date ( 'Ymd', strtotime ( '-1 year' ) );
		$content1 = "<h4>";
		if (array_key_exists ( 'minyear', $_GET ) && ($_GET ['minyear'] == substr ( $theDate, 0, 4 )) && array_key_exists ( 'minmonth', $_GET ) && ($_GET ['minmonth'] == substr ( $theDate, 4, 2 )) && array_key_exists ( 'minday', $_GET ) && ($_GET ['minday'] == substr ( $theDate, 6, 2 )))
			$content1 .= LangSelectedObservationsTitle3;
		elseif ($object)
			$content1 .= LangSelectedObservationsTitle . $object;
		else
			$content1 .= LangSelectedObservationsTitle2;
		$content1 .= "</h4>";
		$link3 = $link;
		$content3 = "<h4>";
		if ($objUtil->checkGetKey ( 'myLanguages' )) {
			$content3 .= " (" . LangSelectedLanguagesShown . ")";
			$link .= "&amp;myLanguages=true";
			$link2 .= "&amp;myLanguages=true";
		} else
			$content3 .= " (" . LangAllLanguagesShown . ")";
		$content3 .= "</h4>";
		echo $content1;
		$content5 = "<span class=\"pull-right\">";
		if (($objUtil->checkSessionKey ( 'lco', '' ) != "L"))
			$content5 .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $link . "&amp;lco=L\" title=\"" . LangOverviewObservationTitle . "\">" . LangOverviewObservations . "</a>";
		if (($objUtil->checkSessionKey ( 'lco', '' ) != "C"))
			$content5 .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $link . "&amp;lco=C\" title=\"" . LangCompactObservationsTitle . "\">" . LangCompactObservations . "</a>";
		if ($loggedUser && ($objUtil->checkSessionKey ( 'lco', '' ) != "O"))
			$content5 .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $link . "&amp;lco=O\" title=\"" . LangCompactObservationsLOTitle . "\">" . LangCompactObservationsLO . "</a>";
		if ($loggedUser && $objUtil->checkSessionKey ( 'lco', '' ) == "L") {
			$toAdd = "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $link . "&amp;noOwnColor=no\">" . LangOwnColor . "</a>";
			if ($objUtil->checkGetKey ( 'noOwnColor' )) {
				if ($objUtil->checkGetKey ( 'noOwnColor' ) == "no") {
					$toAdd = "&nbsp;&nbsp;" . "<a class=\"btn btn-success\" href=\"" . $link . "&amp;noOwnColor=yes\">" . LangNoOwnColor . "</a>";
				}
			}
			$content5 .= $toAdd;
		}
		$content5 .= "</span>";
		
		if ($objUtil->checkGetKey ( 'myLanguages' ))
			$content6 = "<a class=\"btn btn-success\" href=\"" . $link3 . "\">" . LangShowAllLanguages . "</a>";
		elseif ($loggedUser)
			$content6 = "<a class=\"btn btn-success\" href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowMyLanguages . "</a>";
		else
			$content6 = "<a class=\"btn btn-success\" href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowInterfaceLanguage . "</a>";
		echo $content5;
		echo $content6;
		echo "<hr />";

		$objObservation->showListObservation ( $link, $_SESSION ['lco'] );
		echo "<hr />";
		if ($_SESSION ['lco'] == "O") {
			echo LangOverviewObservationsHeader5a;
			echo "<br /><br />";
		}
		$content1 = "<a class=\"btn btn-primary\" href=\"" . $baseURL . "index.php?indexAction=query_objects&amp;source=observation_query\">" . LangExecuteQueryObjectsMessage9 . "</a> ";
		$content1 .= $objPresentations->promptWithLinkText ( LangOverviewObservations10, LangOverviewObservations11, $baseURL . "observations.pdf.php?SID=Qobs", LangExecuteQueryObjectsMessage4a );
		$content1 .= "  ";
		$content1 .= "<a class=\"btn btn-primary\" href=\"" . $baseURL . "observations.csv\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage5 . "</span></a> ";
		$content1 .= "<a class=\"btn btn-primary\" href=\"" . $baseURL . "observations.xml\" rel=\"external\"><span class=\"glyphicon glyphicon-download\"></span> " . LangExecuteQueryObjectsMessage10 . "</span></a> ";
		echo $content1;
		echo "<hr />";
		echo "</div>";
		
		if (($object && $objObject->getExactDsObject ( $object )) && ($collapsed)) {
			echo "<h4>" . $collapsedtext . LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss . "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen . "</h4>";
			echo $objPresentations->getDSSDeepskyLiveLinks1 ( $object ); 
			$topline = "&nbsp;-&nbsp;" . "<a href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode ( $object ) . "\">" . LangViewObjectViewNearbyObject . "</a>";
		}
	}
}
?>
