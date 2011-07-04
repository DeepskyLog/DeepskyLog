<?php
// selected_sessions.php
// generates an overview of selected observations in the database

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else selected_sessions();

function selected_sessions()
{ global $baseURL,$FF,$loggedUser,$object,$myList,$step,
         $objObject,$objObserver,$objSession,$objPresentations,$objUtil;
	echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
  $link2 = $baseURL . "index.php?indexAction=result_selected_sessions";
	reset($_GET);
	while (list ($key, $value) = each($_GET))
	  if (!in_array($key, array (
				'indexAction',
				'sortdirection',
				'sort',
				'multiplepagenr',
				'min',
	      'collapsed'
		  )))
	    $link2 .= "&amp;" . $key . "=" . urlencode($value);

	$link = $link2 . '&amp;sort=' . $objUtil->checkGetKey('sort','') . '&amp;sortdirection=' . $objUtil->checkGetKey('sortdirection','');
	// Show the list of sessions
	if((array_key_exists('steps',$_SESSION))&&(array_key_exists("sessions",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["sessions"];
	if(array_key_exists('multiplepagenr',$_GET))
		  $min = ($_GET['multiplepagenr']-1)*$step;
		elseif(array_key_exists('multiplepagenr',$_POST))
		  $min = ($_POST['multiplepagenr']-1)*$step;
		elseif(array_key_exists('min',$_GET))
		  $min=$_GET['min'];
		else
		  $min = 0;

	// First check the number of sessions for the observer
	if (array_key_exists('observer', $_GET)) {
	  $observer = $_GET['observer'];
	} else {
	  $observer = "-1";
	}
	// Get the number of sessions
	if (array_key_exists("observer", $_SESSION)) {
	  $sessions = $objSession->getListWithActiveSessions($observer);
	} else {
	  $sessions = $objSession->getListWithAllActiveSessions();
	}
	if (count($sessions) == 0) //================================================================================================== no result present =======================================================================================
	{	$objPresentations->line(array("<h4>".LangSessionNoResults . " " . $objObserver->getObserverProperty($observer, "firstname") . " " .
		       $objObserver->getObserverProperty($observer, "name")."!</h4>"),
	                          "L",array(100),30);
	}
	else 
	{ //=============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
		echo "<div id=\"main\">";
		if ($observer == "-1") {
		  $content1 ="<h4>" . LangSearchMenuItem12;
		} else {
		  $content1 ="<h4>" . LangOverviewSessionTitle . $objObserver->getObserverProperty($observer, "firstname") . " " .
		                $objObserver->getObserverProperty($observer, "name");
		}
		$content1.="</h4>";
		
		list($min, $max,$content2,$pageleft,$pageright,$pagemax)=$objUtil->printNewListHeader4($sessions, $link2, $min, $step);
		$objPresentations->line(array($content1,$content2),"LR",array(50,50),30);
    $content4=$objUtil->printStepsPerPage3($link2,"sessions",$step);
		$objPresentations->line(array("",$content4),"LR",array(50,50),25);

	  $objSession->showListSessions($sessions, $min, $max, $link, $link2);
	}
}
?>  
