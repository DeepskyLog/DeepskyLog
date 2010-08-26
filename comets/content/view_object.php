<?php // view_object.php - view all information of one object - version 0.5: 2005/09/21, WDM
if(!$_GET['object']) // no object defined in url 
  header("Location: ../index.php");
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".LangViewObjectTitle."&nbsp;-&nbsp;".$objCometObject->getName($_GET['object'])."</h4>"),"L",array(),30);
echo "<hr />";
$objPresentations->line(array(LangViewObjectField1,$objCometObject->getName($_GET['object'])),"RL",array(20,80),20);
if ($objCometObject->getIcqName($_GET['object']))
  $objPresentations->line(array(LangNewObjectIcqname,$objCometObject->getIcqName($_GET['object'])),"RL",array(20,80),20);
echo "<hr />";
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
?>
