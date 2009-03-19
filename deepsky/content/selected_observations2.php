<?php // selected_observations2.php - generates an overview of selected observations in the database
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
$link2 = $baseURL . "index.php?indexAction=result_selected_observations&amp;lco=" . urlencode($_SESSION['lco']);
reset($_GET);
while (list ($key, $value) = each($_GET))
	if (!in_array($key, array (
			'indexAction',
			'lco',
			'sortdirection',
			'sort',
			'multiplepagenr',
			'min',
			'myLanguages'
		)))
		$link2 .= "&amp;" .
		$key . "=" . urlencode($value);
//  while(list($key,$value)=each($usedLanguages))
//	  $link2=$link2.'&amp;'.$value.'='.$value; 
$link = $link2 . '&amp;sort=' . $_GET['sort'] . '&amp;sortdirection=' . $_GET['sortdirection'];

$step = 25;
$offset=210;
//====================== the remainder of the pages formats the page output and calls showObject (if necessary) and showObservations
//=============================================== IF IT CONCERNS THE OBSERVATIONS OF 1 SPECIFIC OBJECT, SHOW THE OBJECT BEFORE SHOWING ITS OBSERVATIONS =====================================================================================
if ($object && $objObject->getExactDsObject($object)) 
{ $offset=400; 
	$object_ss = stripslashes($object);
	$seen = "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode($object) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
	$seenDetails = $objObject->getSeen($object);
	if (substr($seenDetails, 0, 1) == "X")
		$seen = "<a target=\"_top\" href=\"" .
		$baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
	if (array_key_exists("deepskylog_id", $_SESSION) && $_SESSION["deepskylog_id"])
		if (substr($seenDetails, 0, 1) == "Y")
			$seen = "<a target=\"_top\" href=\"" .
			$baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
	echo "<div id=\"main\">";
	echo "<h6 class=\"h2header\">".LangViewObjectTitle."&nbsp;-&nbsp;".$object_ss."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen."</h6>";
	echo "<table width=\"100%\">";
	echo "<tr>";
	echo "<td width=\"25%\" align=\"left\">";
	echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode($object) . "\">" . LangViewObjectViewNearbyObject . " " . $object_ss;
	echo "</td><td width=\"25%\" align=\"center\">";
	if($loggedUser)
		echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=add_observation&object=" . urlencode($object) . "\">" . LangViewObjectAddObservation . $object_ss . "</a>";
	echo "</td>";
	if ($myList) 
	{ echo "<td width=\"25%\" align=\"center\">";
		if ($objList->checkObjectInMyActiveList($object))
			echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
		else
			echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
		echo "</td>";
	}
	echo "</tr>";
	echo "</table>";
	echo "</div>";
	$objObject->showObject($object);
}
if (count($_SESSION['Qobs']) == 0) //================================================================================================== no reult present =======================================================================================
{	echo "<h2>";
	echo LangObservationNoResults;
	if ($objUtil->checkGetKey('myLanguages'))
		echo " (".LangSelectedObservationsSelectedLanguagesIndication.")";
	else
		echo " (".LangSelectedObservationsAllLanguagesIndication.")";
	echo "</h2>";
	echo "<p>";
	if ($objUtil->checkGetKey('myLanguages'))
		echo "<a target=\"_top\" href=\"" . $link2 . "\">" . LangSearchAllLanguages . "</a><p />";
	echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=query_observations\">" . LangSearchDetailPage . "</a>";
}
else 
{ //=============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
	echo "<div id=\"main\">";
  echo "<table width=\"100%\">";
	echo "<tr height=\"20px\">";
	echo "<td class=\"h2header\">";
	$theDate = date('Ymd', strtotime('-1 year'));
	if (array_key_exists('minyear', $_GET) && ($_GET['minyear'] == substr($theDate, 0, 4)) && array_key_exists('minmonth', $_GET) && ($_GET['minmonth'] == substr($theDate, 4, 2)) && array_key_exists('minday', $_GET) && ($_GET['minday'] == substr($theDate, 6, 2)))
		echo (LangSelectedObservationsTitle3);
	//elseif ($catalog=="*")
	//  echo (LangOverviewObservationsTitle); 
	elseif ($object) echo LangSelectedObservationsTitle . $object;
	else
		echo LangSelectedObservationsTitle2;
	$link3 = $link;
	if ($objUtil->checkGetKey('myLanguages')) {
		echo " (" . LangSelectedLanguagesShown . ")";
		$link .= "&amp;myLanguages=true";
		$link2 .= "&amp;myLanguages=true";
	} else
		echo " (" . LangAllLanguagesShown . ")";
	echo "</td>";
	echo "<td style=\"vertical-algin:middle\" align=\"right\">";
	list ($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobs'], $link, $min, $step, $_SESSION['QobsTotal']);
	echo "</td>";
	echo "</tr>";
  echo "<tr>";
  echo "<td>";
	if (($_SESSION['lco'] != "L"))
		echo ("<a target=\"_top\" href=\"" . $link . "&amp;lco=L" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangOverviewObservationTitle . "\">" . LangOverviewObservations . "</a>");
	if (($_SESSION['lco'] != "C"))
		echo (" <a target=\"_top\" href=\"" . $link . "&amp;lco=C" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangCompactObservationsTitle . "\">" . LangCompactObservations . "</a>");
	if ($loggedUser && ($_SESSION['lco'] != "O"))
		echo (" <a target=\"_top\" href=\"" . $link . "&amp;lco=O" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangCompactObservationsLOTitle . "\">" . LangCompactObservationsLO . "</a>");
  echo "</td>";
  echo "<td align=\"right\">";
 	if ($objUtil->checkGetKey('myLanguages'))
		echo "<a target=\"_top\" href=\"" . $link3 . "\">" . LangShowAllLanguages . "</a>";
	elseif ($loggedUser) 
	  echo "<a target=\"_top\" href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowMyLanguages . "</a>";
	else
		echo "<a target=\"_top\" href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowInterfaceLanguage . "</a>";
  echo "</td>";
  echo "</tr>";
	echo "</table>";
	echo "<hr />";
	$_GET['min']=$min;
	$_GET['max']=$max;
  if($FF)
	  $objObservation->showListObservation($link . "&amp;min=" . $min,$link2,$_SESSION['lco']);
	else
	{ $_SESSION['ifrm']="deepsky/content/ifrm_observations.php";
		echo "<iframe name=\"obs_list\" id=\"obs_list\" src=\"".$baseURL."ifrm_holder.php?link=".urlencode($link)."&amp;link2=".urlencode($link2)."&amp;min=".$min."&amp;max=".$max."&amp;expand=".$objUtil->checkGetKey('expand')."\" frameborder=\"0\" width=\"100%\" style=\"heigth:200px\">";
	  $objObservation->showListObservation($link . "&amp;min=" . $min,$link2,$_SESSION['lco']);
		echo "</iframe>";
	}
  echo "<script>resizeElement('obs_list',".$offset.");</script>";
	echo "<hr />";
	if ($_SESSION['lco'] == "O")
		echo "<p align=\"right\">" . LangOverviewObservationsHeader5a;
	echo "<table width=\"100%\">";
	echo "<tr>";
	echo "<td>";
	$objPresentations->promptWithLink(LangOverviewObservations10, LangOverviewObservations11, $baseURL . "observations.pdf?SID=Qobs", LangExecuteQueryObjectsMessage4);
	echo " - ";
	echo "<a target=\"_top\" href=\"" . $baseURL . "observations.csv\" target=\"new_window\">" . LangExecuteQueryObjectsMessage5 . "</a> - ";
	echo "<a target=\"_top\" href=\"" . $baseURL . "observations.xml\" target=\"new_window\">" . LangExecuteQueryObjectsMessage10 . "</a> - ";
	echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=query_objects&amp;source=observation_query\">" . LangExecuteQueryObjectsMessage9 . "</a>";
	//==================================================================================================== PAGE FOOTER - MAKE NEW QUERY ===================================================================================== 
//	echo "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>";
  echo "</td>";
  echo "<td align=\"right\">";
//  list ($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobs'], $link, $min, $step, $_SESSION['QobsTotal']);
  echo "</td>";
  echo "</tr>";
  echo "</table>";
  echo "</div>";
}
?>
