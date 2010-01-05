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
//====================== the remainder of the pages formats the page output and calls showObject (if necessary) and showObservations
//=============================================== IF IT CONCERNS THE OBSERVATIONS OF 1 SPECIFIC OBJECT, SHOW THE OBJECT BEFORE SHOWING ITS OBSERVATIONS =====================================================================================
if ($object && $objObject->getExactDsObject($object)) 
{ if((array_key_exists('steps',$_SESSION))&&(array_key_exists("selObjObs".$_SESSION['lco'],$_SESSION['steps'])))
	  $step=$_SESSION['steps']["selObjObs".$_SESSION['lco']];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
	$object_ss = stripslashes($object);
  $seen=$objObject->getDSOseenLink($object);
	$objPresentations->line(array("<h4>".LangViewObjectTitle."&nbsp;-&nbsp;".$object_ss."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen."</h4>",$objPresentations->getDSSDeepskyLiveLinks1($object)),
	                        "LR",array(50,50),30);
  $topline="&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."\">".LangViewObjectViewNearbyObject."</a>";
//  if(substr($objObject->getSeen($object),0,1)!='-')
//    $topline.= "&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\">".LangViewObjectObservations."</a>";
//  if($loggedUser)
//		$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;object=" . urlencode($object) . "\">" . LangViewObjectAddObservation."</a>";
  if ($myList) 
	{ if ($objList->checkObjectInMyActiveList($object))
			$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
		else
			$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
	}
	$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=atlaspage&amp;object=" . urlencode($object) . "\">" . LangAtlasPage . "</a>";
	$objPresentations->line(array(substr($topline,13),$objPresentations->getDSSDeepskyLiveLinks2($object)),"LR",array(70,30),20);
	echo "<hr />";
	$objObject->showObject($object);
}
else
{ if((array_key_exists('steps',$_SESSION))&&(array_key_exists("selObs".$_SESSION['lco'],$_SESSION['steps'])))
	  $step=$_SESSION['steps']["selObs".$_SESSION['lco']];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
}
if (count($_SESSION['Qobs']) == 0) //================================================================================================== no reult present =======================================================================================
{	$objPresentations->line(array("<h4>".LangObservationNoResults.(($objUtil->checkGetKey('myLanguages'))?(" (".LangSelectedObservationsSelectedLanguagesIndication.")"):(" (".LangSelectedObservationsAllLanguagesIndication.")"))."</h4>"),
                          "L",array(100),30);
	if ($objUtil->checkGetKey('myLanguages'))
		echo "<p>"."<a href=\"" . $link2 . "\">" . LangSearchAllLanguages . "</a>&nbsp;</p>";
	echo "<p>"."<a href=\"" . $baseURL . "index.php?indexAction=query_observations\">" . LangSearchDetailPage . "</a>"."</p>";
}
else 
{ //=============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
	echo "<div id=\"main\">";
	$theDate = date('Ymd', strtotime('-1 year'));
	$content1 ="<h4>";
	if (array_key_exists('minyear', $_GET) && ($_GET['minyear'] == substr($theDate, 0, 4)) && array_key_exists('minmonth', $_GET) && ($_GET['minmonth'] == substr($theDate, 4, 2)) && array_key_exists('minday', $_GET) && ($_GET['minday'] == substr($theDate, 6, 2)))
		$content1.=LangSelectedObservationsTitle3;
	elseif ($object) 
	  $content1.=LangSelectedObservationsTitle . $object;
	else
		$content1.=LangSelectedObservationsTitle2;
	$content1.="</h4>";
	$link3 = $link;
	$content3 ="<h4>";
	if ($objUtil->checkGetKey('myLanguages')) 
	{ $content3.=" (".LangSelectedLanguagesShown.")";
	  $link .= "&amp;myLanguages=true";
	  $link2 .= "&amp;myLanguages=true";
	} 
	else
	  $content3.=" (".LangAllLanguagesShown.")";
	$content3.="</h4>";
	list($min, $max,$content2,$pageleft,$pageright,$pagemax)=$objUtil->printNewListHeader4($_SESSION['Qobs'], $link, $min, $step, $_SESSION['QobsTotal']);
	$objPresentations->line(array($content1,$content2),"LR",array(50,50),30);
	if($object)
    $content4=$objUtil->printStepsPerPage3($link,"selObjObs".$_SESSION['lco'],$step);
	else
    $content4=$objUtil->printStepsPerPage3($link,"selObs".$_SESSION['lco'],$step);
	$objPresentations->line(array($content3,$content4),"LR",array(50,50),25);
 	$content5="";
	if(($objUtil->checkSessionKey('lco','')!="L"))
		$content5.="&nbsp;-&nbsp;<a href=\"" . $link . "&amp;lco=L" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangOverviewObservationTitle . "\">" . LangOverviewObservations . "</a>";
	if(($objUtil->checkSessionKey('lco','')!="C"))
		$content5.="&nbsp;-&nbsp;<a href=\"" . $link . "&amp;lco=C" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangCompactObservationsTitle . "\">" . LangCompactObservations . "</a>";
	if($loggedUser&&($objUtil->checkSessionKey('lco','')!= "O"))
		$content5.="&nbsp;-&nbsp;<a href=\"" . $link . "&amp;lco=O" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangCompactObservationsLOTitle . "\">" . LangCompactObservationsLO . "</a>";
	if($loggedUser&&(!($objUtil->checkGetKey('noOwnColor')))&&(($objUtil->checkSessionKey('lco','')=="L")))
	  $content5.="&nbsp;-&nbsp;"."<a href=\"".$link."&amp;noOwnColor=yes\">".LangNoOwnColor."</a>";
  $content5=substr($content5,13);
 	if ($objUtil->checkGetKey('myLanguages'))
		$content6="<a href=\"" . $link3 . "\">" . LangShowAllLanguages . "</a>";
	elseif ($loggedUser) 
	  $content6="<a href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowMyLanguages . "</a>";
	else
		$content6= "<a href=\"" . $link3 . "&amp;myLanguages=true\">" . LangShowInterfaceLanguage . "</a>";
  $objPresentations->line(array($content5,$content6),"LR",array(50,50),20);
  echo "<hr />";
  
	$_GET['min']=$min;
	$_GET['max']=$max;
	if(($FF)&&($_SESSION['lco'] == "O"))
	{ echo "<script type=\"text/javascript\">";
    echo "theResizeElement='obs_list';";
    echo "theResizeSize=100;";
    echo "</script>";
	}
	elseif(($FF))
	{ echo "<script type=\"text/javascript\">";
    echo "theResizeElement='obs_list';";
    if($object)
      echo "theResizeSize=70;";
    else
      echo "theResizeSize=70;";
    echo "</script>";
	}
	$objObservation->showListObservation($link . "&amp;min=" . $min,$link2,$_SESSION['lco'],$step);
	echo "<hr />";
	if ($_SESSION['lco'] == "O")
		  $objPresentations->line(array(LangOverviewObservationsHeader5a),"R",array(100),25);
	$content1 ="<a href=\"" . $baseURL . "index.php?indexAction=query_objects&amp;source=observation_query\">" . LangExecuteQueryObjectsMessage9 . "</a> - ";
	$content1.=LangExecuteQueryObjectsMessage4."&nbsp;";
	$content1.=$objPresentations->promptWithLinkText(LangOverviewObservations10, LangOverviewObservations11, $baseURL . "observations.pdf?SID=Qobs", LangExecuteQueryObjectsMessage4a);
	$content1.=" - ";
	$content1.="<a href=\"" . $baseURL . "observations.csv\" rel=\"external\">" . LangExecuteQueryObjectsMessage5 . "</a> - ";
	$content1.="<a href=\"" . $baseURL . "observations.xml\" rel=\"external\">" . LangExecuteQueryObjectsMessage10 . "</a>";
	$objPresentations->line(array($content1),"L",array(100),25);
  echo "</div>";
  echo "<script type=\"text/javascript\">";
  echo "
  function pageOnKeyDown(event)
  { if(event.keyCode==37)
      if(event.shiftKey)
        location=html_entity_decode('".$link."&amp;multiplepagenr=0"."');    
      else
        location=html_entity_decode('".$link."&amp;multiplepagenr=".$pageleft."');
    if(event.keyCode==39)
      if(event.shiftKey) 
        location=html_entity_decode('".$link."&amp;multiplepagenr=".$pagemax."');
      else  
        location=html_entity_decode('".$link."&amp;multiplepagenr=".$pageright."');
  }
  this.onKeyDownFns[this.onKeyDownFns.length] = pageOnKeyDown;
  ";
  echo "</script>";
}
?>
