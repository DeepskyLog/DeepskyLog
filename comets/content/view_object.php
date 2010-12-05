<?php 
// view_object.php
// view all information of one object

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else view_object();

function view_object()
{ global $baseURL,$loggedUser,
         $objObserver,$objPresentations,$objCometObject;
	if(!$_GET['object']) // no object defined in url 
	  header("Location: ../index.php");
	echo "<div id=\"main\">";
	
	// Let's test for the observer... If cometadministrator (or normal administrator), we can change the object)
	$admin = false;
	
	// Check if there is an observer
	if(array_key_exists('deepskylog_id', $_SESSION)) {
	  // Check if this observer is cometadministrator
	  if  ($objObserver->getObserverProperty($_SESSION['deepskylog_id'], "role") == RoleCometAdmin ||
	        $objObserver->getObserverProperty($_SESSION['deepskylog_id'], "role") == RoleAdmin) {
	          $admin = true;
	  } 
	}
	
	if ($admin) {
	  echo "<form action=\"".$baseURL."index.php?indexAction=comets_validate_change_object\" method=\"post\"><div>";
	  echo "<input type=\"hidden\" name=\"object\" value=\"" . $_GET['object'] . "\" />";
	  $content="<input type=\"submit\" name=\"newobject\" value=\"" . LangChangeAccountButton . "\" />";
	  $objPresentations->line(array("<h4>".LangChangeObject. " " . $objCometObject->getName($_GET['object']) . "</h4>",$content),"LR",array(60,40),30);
	  echo "<hr />";
	  $content="<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"" . 
	    $objCometObject->getName($_GET['object']) . "\" />";
	  $objPresentations->line(array(LangViewObjectField1."&nbsp;*",$content),"RL",array(20,80),30,array("fieldname"));
	  
	  if ($objCometObject->getIcqName($_GET['object'])) {
	    $icqname = $objCometObject->getIcqName($_GET['object']);
	  } else {
	    $icqname = "";
	  }
	  $content="<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"" . $icqname . "\" />";
	  $objPresentations->line(array(LangNewObjectIcqname."&nbsp;*",$content),"RL",array(20,80),30,array("fieldname"));
	  echo "<hr />";
	  echo "</div></form>";
	} else {
	  $objPresentations->line(array("<h4>".LangViewObjectTitle."&nbsp;-&nbsp;".$objCometObject->getName($_GET['object'])."</h4>"),"L",array(),30);
	  echo "<hr />";
	  
	  $objPresentations->line(array(LangViewObjectField1,$objCometObject->getName($_GET['object'])),"RL",array(20,80),20);
	  if ($objCometObject->getIcqName($_GET['object']))
	    $objPresentations->line(array(LangNewObjectIcqname,$objCometObject->getIcqName($_GET['object'])),"RL",array(20,80),20);
	  echo "<hr />";
	}
	
	// LINK TO OBSERVATIONS OF OBJECT
	$observations = new CometObservations();
	$queries = array("object" => $objCometObject->getName($_GET['object']));
	$content="";
	if (count($observations->getObservationFromQuery($queries)) > 0)
	  $content.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=".urlencode($_GET['object'])."\">" . LangViewObjectObservations . " " . $objCometObject->getName($_GET['object']) . "</a>";
	# extra link to add observation of this object
	if($loggedUser)
	{ $_SESSION['observedobject'] = $_GET['object'];
	  $_SESSION['result'] = $objCometObject->getExactObject($_SESSION['observedobject']);
	  //$_SESSION['observedobject'] = $_SESSION['result'][0]; // use name in database
	  $_SESSION['found'] = "yes";
	  $_SESSION['backlink'] = "validate_search_object.php";
	  $content.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=comets_add_observation&amp;observedobject=" . urlencode($_GET['object']) . "\">" . LangViewObjectAddObservation ."&nbsp;". $objCometObject->getName($_GET['object']) . "</a>";
	}
	$objPresentations->line(array(substr($content,13)),"L",array(),20);
	echo("</div>");
}
?>
